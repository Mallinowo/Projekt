<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CityLocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct(private readonly CityLocationService $cityLocationService)
    {
    }

    public function index()
    {
        $user = Auth::user()->load(['photos', 'interests', 'artists']);
        $spotifyEnabled = filled(env('SPOTIFY_CLIENT_ID')) && filled(env('SPOTIFY_CLIENT_SECRET'));

        return view('profile.index', compact('user', 'spotifyEnabled'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name'        => 'required|string|max:30|unique:users,name,' . $user->id,
            'age'         => 'required|integer|min:18|max:99',
            'city'        => 'required|string|max:60',
            'preferred_age_min' => 'required|integer|min:18|max:99',
            'preferred_age_max' => 'required|integer|min:18|max:99|gte:preferred_age_min',
            'preferred_max_distance_km' => 'required|integer|min:5|max:500',
            'preferred_subcultures' => 'nullable|array|max:5',
            'preferred_subcultures.*' => ['required', Rule::in(User::SUBCULTURE_VALUES)],
            'gender'      => ['required', Rule::in(User::GENDER_VALUES)],
            'orientation' => ['required', Rule::in(User::ORIENTATION_VALUES)],
            'subculture'  => ['required', Rule::in(User::SUBCULTURE_VALUES)],
            'bio'         => 'nullable|string|max:500',
        ]);

        $data['preferred_subcultures'] = collect($data['preferred_subcultures'] ?? [])
            ->map(fn ($value) => (string) $value)
            ->filter(fn ($value) => in_array($value, User::SUBCULTURE_VALUES, true))
            ->unique()
            ->take(5)
            ->values()
            ->all();

        $cityChanged = mb_strtolower(trim((string) $data['city'])) !== mb_strtolower(trim((string) $user->city));
        if ($cityChanged || is_null($user->city_lat) || is_null($user->city_lng)) {
            $coords = $this->cityLocationService->resolve((string) $data['city']);
            if ($coords) {
                $data['city_lat'] = $coords['lat'];
                $data['city_lng'] = $coords['lng'];
            }
        }

        $user->update($data);

        if ($request->has('interests')) {
            $user->interests()->delete();
            foreach (array_slice((array)$request->interests, 0, 15) as $interest) {
                $user->interests()->create(['name' => trim($interest)]);
            }
        }

        if ($request->has('artists')) {
            $user->artists()->delete();
            foreach (array_slice((array)$request->artists, 0, 10) as $artist) {
                $user->artists()->create(['name' => trim($artist)]);
            }
        }

        Cache::add('discover_cache_version', 1);
        Cache::increment('discover_cache_version');
        Cache::add('api_cache_version', 1);
        Cache::increment('api_cache_version');

        Log::info('Profile updated', ['user_id' => $user->id]);

        return back()->with('success', __('profile.saved'));
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate(['photo' => 'required|image|max:5120']);
        $user = Auth::user();

        if ($user->photos()->count() >= 6) {
            return response()->json(['error' => __('profile.max_photos')], 422);
        }

        $path = $request->file('photo')->store('photos/' . $user->id, 'public');
        $order = $user->photos()->max('order') + 1;
        $photo = $user->photos()->create(['path' => $path, 'order' => $order]);

        if ($user->photos()->count() === 1) {
            $user->update(['avatar' => $path]);
        }

        Log::info('Photo uploaded', ['user_id' => $user->id, 'path' => $path]);

        return response()->json(['id' => $photo->id, 'url' => '/storage/' . $path]);
    }

    public function deletePhoto(int $photoId)
    {
        $user  = Auth::user();
        $photo = $user->photos()->findOrFail($photoId);

        Storage::disk('public')->delete($photo->path);

        if ($user->avatar === $photo->path) {
            $next = $user->photos()->where('id', '!=', $photoId)->first();
            $user->update(['avatar' => $next?->path]);
        }

        $photo->delete();

        return response()->json(['success' => true]);
    }
}
