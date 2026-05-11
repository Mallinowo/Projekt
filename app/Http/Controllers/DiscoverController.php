<?php

namespace App\Http\Controllers;

use App\Models\UserMatch;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DiscoverController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $discoverFilters = [
            'age_min' => $user->preferredAgeMin(),
            'age_max' => $user->preferredAgeMax(),
            'max_distance_km' => $user->preferredMaxDistanceKm(),
            'subcultures' => $user->preferredSubculturesOrAll(),
            'has_coords' => !is_null($user->city_lat) && !is_null($user->city_lng),
        ];

        // Cache (punkt 4) - cache profiles for 5 minutes per user
        $cacheVersion = (int) Cache::get('discover_cache_version', 1);
        $cacheKey = "discover_profiles_{$user->id}_v{$cacheVersion}";
        $profiles = Cache::remember($cacheKey, 300, function () use ($user) {
            $matchedUserIds = $this->getMatchedUserIds($user);
            $swipedIds = $user->swipesMade()->pluck('swiped_id')->toArray();
            $excludedIds = array_values(array_unique(array_merge($swipedIds, $matchedUserIds, [$user->id])));

            $candidates = $this->fetchDiscoverCandidates($user, $excludedIds);

            // Rewind fallback: when user swiped everyone, show profiles again.
            if ($candidates->isEmpty()) {
                $candidates = $this->fetchDiscoverCandidates($user, array_values(array_unique(array_merge($matchedUserIds, [$user->id]))));
            }

            return $candidates
                ->map(function ($u) use ($user) {
                    $distanceKm = $this->distanceInKm($user, $u);

                    return [
                        'id'          => $u->id,
                        'name'        => $u->name,
                        'age'         => $u->age,
                        'city'        => $u->city,
                        'distance_km' => $distanceKm !== null ? (int) round($distanceKm) : null,
                        'subculture'  => $u->subculture,
                        'gender'      => $u->gender,
                        'orientation' => $u->orientation,
                        'bio'         => $u->bio,
                        'avatar'      => $u->getAvatarUrl(),
                        'photos'      => $u->photos->map(fn($p) => '/storage/' . $p->path)->values(),
                        'interests'   => $u->interests->pluck('name'),
                        'artists'     => $u->artists->pluck('name'),
                    ];
                });
        });

        $matches = $this->getUserMatches($user);

        return view('discover.index', compact('profiles', 'matches', 'discoverFilters'));
    }

    public function matchUpdates(Request $request): JsonResponse
    {
        $user = Auth::user();
        $afterId = max(0, (int) $request->query('after_id', 0));

        $matches = $this->getUserMatches($user, $afterId)->values();
        $latestMatchId = (int) (UserMatch::query()
            ->where(function ($q) use ($user) {
                $q->where('user1_id', $user->id)->orWhere('user2_id', $user->id);
            })
            ->max('id') ?? 0);

        return response()->json([
            'matches' => $matches,
            'latest_match_id' => $latestMatchId,
        ]);
    }

    private function getMatchedUserIds(User $user): array
    {
        return UserMatch::query()
            ->where(function ($q) use ($user) {
                $q->where('user1_id', $user->id)->orWhere('user2_id', $user->id);
            })
            ->get(['user1_id', 'user2_id'])
            ->map(fn(UserMatch $match) => $match->user1_id === $user->id ? (int) $match->user2_id : (int) $match->user1_id)
            ->unique()
            ->values()
            ->all();
    }

    private function getUserMatches($user, int $afterId = 0): \Illuminate\Support\Collection
    {
        $query = UserMatch::with(['user1.photos', 'user2.photos'])
            ->with('latestMessage')
            ->withCount([
                'messages as unread_for_user_count' => function ($q) use ($user) {
                    $q->where('sender_id', '!=', $user->id)->whereNull('read_at');
                },
            ])
            ->where(function ($q) use ($user) {
                $q->where('user1_id', $user->id)->orWhere('user2_id', $user->id);
            });

        if ($afterId > 0) {
            $query->where('id', '>', $afterId);
        }

        return $query
            ->latest('id')
            ->get()
            ->map(function ($match) use ($user) {
                $other = $match->getOtherUser($user->id);
                $last  = $match->latestMessage;
                return [
                    'match_id'   => $match->id,
                    'user_id'    => $other->id,
                    'name'       => $other->name,
                    'avatar'     => $other->getAvatarUrl(),
                    'last_msg'   => $last?->body,
                    'unread'     => (int) ($match->unread_for_user_count ?? 0),
                ];
            });
    }

    private function fetchDiscoverCandidates(User $user, array $excludedIds, int $limit = 20): Collection
    {
        $sampleSize = max(120, $limit * 12);
        $preferredSubcultures = $user->preferredSubculturesOrAll();
        $preferredAgeMin = $user->preferredAgeMin();
        $preferredAgeMax = $user->preferredAgeMax();

        $query = User::query()
            ->select(['id', 'name', 'age', 'city', 'city_lat', 'city_lng', 'subculture', 'gender', 'orientation', 'bio', 'avatar'])
            ->with([
                'photos:id,user_id,path,order',
                'interests:id,user_id,name',
                'artists:id,user_id,name',
            ])
            ->whereNotIn('id', $excludedIds)
            ->whereBetween('age', [$preferredAgeMin, $preferredAgeMax])
            ->whereIn('subculture', $preferredSubcultures)
            ->inRandomOrder()
            ->limit($sampleSize);

        return $query
            ->get()
            ->filter(fn(User $candidate) => $this->matchesViewerFilters($user, $candidate) && $this->isMutualPreferenceMatch($user, $candidate))
            ->sortBy(fn(User $candidate) => $this->distanceInKm($user, $candidate) ?? PHP_FLOAT_MAX)
            ->take($limit)
            ->values();
    }

    private function isMutualPreferenceMatch(User $viewer, User $candidate): bool
    {
        return $viewer->acceptsUser($candidate) && $candidate->acceptsUser($viewer);
    }

    private function matchesViewerFilters(User $viewer, User $candidate): bool
    {
        $viewerHasCoords = !is_null($viewer->city_lat) && !is_null($viewer->city_lng);
        if (!$viewerHasCoords) {
            return true;
        }

        $distance = $this->distanceInKm($viewer, $candidate);
        // Candidate without coordinates should still be discoverable.
        if ($distance === null) {
            return true;
        }

        if ($distance > $viewer->preferredMaxDistanceKm()) {
            return false;
        }

        return true;
    }

    private function distanceInKm(User $first, User $second): ?float
    {
        if (is_null($first->city_lat) || is_null($first->city_lng) || is_null($second->city_lat) || is_null($second->city_lng)) {
            return null;
        }

        $lat1 = deg2rad((float) $first->city_lat);
        $lon1 = deg2rad((float) $first->city_lng);
        $lat2 = deg2rad((float) $second->city_lat);
        $lon2 = deg2rad((float) $second->city_lng);

        $deltaLat = $lat2 - $lat1;
        $deltaLon = $lon2 - $lon1;

        $a = sin($deltaLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($deltaLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return 6371 * $c;
    }
}
