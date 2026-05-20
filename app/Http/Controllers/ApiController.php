<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ApiController extends Controller
{
    public function profiles(Request $request): JsonResponse
    {
        $page = (int) $request->get('page', 1);
        $cacheVersion = (int) Cache::get('api_cache_version', 1);

        $profiles = Cache::remember("api_profiles_v{$cacheVersion}_{$page}", 60, function () {
            return User::with(['photos', 'interests', 'artists'])
                ->paginate(10)
                ->through(fn($u) => [
                    'id'         => $u->id,
                    'name'       => $u->name,
                    'age'        => $u->age,
                    'city'       => $u->city,
                    'subculture' => $u->subculture,
                    'bio'        => $u->bio,
                    'avatar'     => $u->getAvatarUrl(),
                    'interests'  => $u->interests->pluck('name'),
                    'artists'    => $u->artists->pluck('name'),
                ]);
        });

        return response()->json($profiles);
    }

    public function profile(int $id): JsonResponse
    {
        $cacheVersion = (int) Cache::get('api_cache_version', 1);
        $u = Cache::remember("api_profile_v{$cacheVersion}_{$id}", 120, function () use ($id) {
            return User::with(['photos', 'interests', 'artists'])->findOrFail($id);
        });

        return response()->json([
            'id'         => $u->id,
            'name'       => $u->name,
            'age'        => $u->age,
            'city'       => $u->city,
            'subculture' => $u->subculture,
            'bio'        => $u->bio,
            'avatar'     => $u->getAvatarUrl(),
            'photos'     => $u->photos->map(fn($p) => '/storage/' . $p->path),
            'interests'  => $u->interests->pluck('name'),
            'artists'    => $u->artists->pluck('name'),
        ]);
    }

    public function subcultures(): JsonResponse
    {
        return response()->json([
            ['key' => 'emo',       'label' => 'Emo',       'icon' => '🖤'],
            ['key' => 'scene',     'label' => 'Scene',     'icon' => '🌈'],
            ['key' => 'goth',      'label' => 'Goth',      'icon' => '🦇'],
            ['key' => 'punk',      'label' => 'Punk',      'icon' => '⚡'],
            ['key' => 'metalhead', 'label' => 'Metalhead', 'icon' => '🤘'],
        ]);
    }
}
