<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    public function show()
    {
        $user = Auth::user()->load(['photos', 'interests', 'artists']);

        if ($user->onboarding_completed) {
            return redirect()->route('discover');
        }

        $spotifyEnabled = filled(env('SPOTIFY_CLIENT_ID')) && filled(env('SPOTIFY_CLIENT_SECRET'));

        return view('onboarding.index', compact('user', 'spotifyEnabled'));
    }

    public function complete(Request $request)
    {
        $user = Auth::user()->load(['photos', 'interests', 'artists']);

        if ($user->onboarding_completed) {
            return redirect()->route('discover');
        }

        $data = $request->validate([
            'bio' => 'required|string|min:20|max:500',
            'gender' => ['required', Rule::in(User::GENDER_VALUES)],
            'orientation' => ['required', Rule::in(User::ORIENTATION_VALUES)],
            'interests' => 'required|array|min:5|max:15',
            'interests.*' => 'required|string|max:30',
            'artists' => 'nullable|array|max:10',
            'artists.*' => 'nullable|string|max:100',
        ]);

        if ($user->photos()->count() < 1) {
            return back()
                ->withErrors(['photos' => __('onboarding.need_photo')])
                ->withInput();
        }

        $interests = collect($data['interests'] ?? [])
            ->map(fn($item) => trim((string) $item))
            ->filter()
            ->unique(fn($item) => mb_strtolower($item))
            ->values();

        if ($interests->count() < 5) {
            return back()
                ->withErrors(['interests' => __('onboarding.min_interests')])
                ->withInput();
        }

        $artists = collect($data['artists'] ?? [])
            ->map(fn($item) => trim((string) $item))
            ->filter()
            ->unique(fn($item) => mb_strtolower($item))
            ->take(10)
            ->values();

        $user->update([
            'bio' => trim((string) $data['bio']),
            'gender' => (string) $data['gender'],
            'orientation' => (string) $data['orientation'],
            'onboarding_completed' => true,
        ]);

        $user->interests()->delete();
        foreach ($interests->take(15) as $interest) {
            $user->interests()->create(['name' => $interest]);
        }

        $user->artists()->delete();
        foreach ($artists as $artist) {
            $user->artists()->create(['name' => $artist]);
        }

        Cache::add('discover_cache_version', 1);
        Cache::increment('discover_cache_version');
        Cache::add('api_cache_version', 1);
        Cache::increment('api_cache_version');

        Log::info('Onboarding completed', ['user_id' => $user->id]);

        return redirect()
            ->route('discover')
            ->with('success', __('onboarding.done'));
    }
}
