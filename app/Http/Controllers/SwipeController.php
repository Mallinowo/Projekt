<?php

namespace App\Http\Controllers;

use App\Mail\MatchMail;
use App\Models\UserMatch;
use App\Models\Swipe;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SwipeController extends Controller
{
    public function swipe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'swiped_id' => 'required|integer|exists:users,id',
            'direction' => 'required|in:like,dislike,superlike',
        ]);

        $user    = Auth::user();
        $swipedId = $data['swiped_id'];

        if ($user->id === $swipedId) {
            return response()->json(['error' => 'Cannot swipe yourself'], 422);
        }

        // Record swipe (or update existing one in rewind mode)
        Swipe::updateOrCreate(
            [
                'swiper_id' => $user->id,
                'swiped_id' => $swipedId,
            ],
            [
                'direction' => $data['direction'],
            ]
        );

        // Invalidate cache so next discover load is fresh
        $cacheVersion = (int) Cache::get('discover_cache_version', 1);
        Cache::forget("discover_profiles_{$user->id}_v{$cacheVersion}");

        Log::info('Swipe recorded', [
            'swiper'    => $user->id,
            'swiped'    => $swipedId,
            'direction' => $data['direction'],
        ]);

        // Check for mutual like → create match
        $matched = false;
        $matchData = null;

        if (in_array($data['direction'], ['like', 'superlike'])) {
            $otherUser = User::find($swipedId);
            if ($otherUser && $otherUser->hasLiked($user->id)) {
                // Both liked each other → match!
                $existing = $user->getMatchWith($swipedId);
                if (!$existing) {
                    $match = UserMatch::create([
                        'user1_id' => min($user->id, $swipedId),
                        'user2_id' => max($user->id, $swipedId),
                    ]);

                    Log::info('New match created', [
                        'match_id' => $match->id,
                        'user1'    => $user->id,
                        'user2'    => $swipedId,
                    ]);

                    // Send match notification emails (punkt 13)
                    try {
                        Mail::to($user->email)->send(new MatchMail($user, $otherUser));
                        Mail::to($otherUser->email)->send(new MatchMail($otherUser, $user));
                    } catch (\Exception $e) {
                        Log::warning('Match email failed', ['error' => $e->getMessage()]);
                    }

                    $matched = true;
                    $matchData = [
                        'match_id'   => $match->id,
                        'user_id'    => $otherUser->id,
                        'name'       => $otherUser->name,
                        'avatar'     => $otherUser->getAvatarUrl(),
                    ];

                    // Match must disappear from discover for both users immediately.
                    Cache::forget("discover_profiles_{$swipedId}_v{$cacheVersion}");
                }
            }
        }

        return response()->json([
            'success' => true,
            'matched' => $matched,
            'match'   => $matchData,
        ]);
    }
}
