<?php

namespace App\Http\Controllers;

use App\Services\SpotifyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SpotifyController extends Controller
{
    private const DEFAULT_RETURN_ROUTE = 'onboarding.show';
    private const ALLOWED_RETURN_ROUTES = ['onboarding.show', 'profile'];

    public function __construct(private readonly SpotifyService $spotify)
    {
    }

    public function connect(Request $request)
    {
        $returnRoute = $this->resolveReturnRouteFromQuery($request);
        $messagePrefix = $this->messagePrefixForRoute($returnRoute);

        if (!$this->spotify->isConfigured()) {
            return redirect()->route($returnRoute)
                ->with('spotify_error', __($messagePrefix . '.spotify_not_configured'));
        }

        $state = Str::random(40);
        $request->session()->put('spotify_oauth_state', $state);
        $request->session()->put('spotify_oauth_return_to', $returnRoute);

        return redirect()->away($this->spotify->authorizeUrl($state));
    }

    public function callback(Request $request)
    {
        $returnRoute = $this->resolveReturnRouteFromSession($request);
        $messagePrefix = $this->messagePrefixForRoute($returnRoute);

        if (!$this->spotify->isConfigured()) {
            return redirect()->route($returnRoute)
                ->with('spotify_error', __($messagePrefix . '.spotify_not_configured'));
        }

        if ($request->filled('error')) {
            return redirect()->route($returnRoute)
                ->with('spotify_error', __($messagePrefix . '.spotify_error'));
        }

        $state = (string) $request->query('state', '');
        $expectedState = (string) $request->session()->pull('spotify_oauth_state', '');

        if ($state === '' || $expectedState === '' || !hash_equals($expectedState, $state)) {
            return redirect()->route($returnRoute)
                ->with('spotify_error', __($messagePrefix . '.spotify_error'));
        }

        $code = (string) $request->query('code', '');
        if ($code === '') {
            return redirect()->route($returnRoute)
                ->with('spotify_error', __($messagePrefix . '.spotify_error'));
        }

        try {
            $token = $this->spotify->exchangeAuthorizationCode($code);
        } catch (\Throwable $e) {
            Log::warning('Spotify authorization code exchange failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return redirect()->route($returnRoute)
                ->with('spotify_error', __($messagePrefix . '.spotify_error'));
        }

        $accessToken = (string) ($token['access_token'] ?? '');
        $refreshToken = (string) ($token['refresh_token'] ?? '');
        $expiresIn = (int) ($token['expires_in'] ?? 3600);

        if ($accessToken === '') {
            return redirect()->route($returnRoute)
                ->with('spotify_error', __($messagePrefix . '.spotify_error'));
        }

        $request->session()->put('spotify_user_access_token', $accessToken);
        $request->session()->put('spotify_user_token_expires_at', now()->addSeconds(max(60, $expiresIn - 30))->timestamp);

        if ($refreshToken !== '') {
            $request->session()->put('spotify_user_refresh_token', $refreshToken);
        }

        return redirect()->route($returnRoute)
            ->with('spotify_success', __($messagePrefix . '.spotify_connected'));
    }

    public function disconnect(Request $request): JsonResponse
    {
        $request->session()->forget([
            'spotify_user_access_token',
            'spotify_user_refresh_token',
            'spotify_user_token_expires_at',
            'spotify_oauth_state',
        ]);

        return response()->json(['ok' => true]);
    }

    public function searchArtists(Request $request): JsonResponse
    {
        if (!$this->spotify->isConfigured()) {
            return response()->json([
                'message' => __('onboarding.spotify_not_configured'),
                'artists' => [],
            ], 503);
        }

        $query = trim((string) $request->query('q', ''));
        if (mb_strlen($query) < 2) {
            return response()->json(['artists' => []]);
        }

        try {
            $artists = $this->spotify->searchArtists($query, 8);
        } catch (\Throwable $e) {
            Log::warning('Spotify artist search failed', [
                'error' => $e->getMessage(),
                'query' => $query,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => __('onboarding.spotify_error'),
                'artists' => [],
            ], 502);
        }

        return response()->json(['artists' => $artists]);
    }

    public function topArtists(Request $request): JsonResponse
    {
        if (!$this->spotify->isConfigured()) {
            return response()->json([
                'message' => __('onboarding.spotify_not_configured'),
                'artists' => [],
            ], 503);
        }

        $accessToken = (string) $request->session()->get('spotify_user_access_token', '');
        $refreshToken = (string) $request->session()->get('spotify_user_refresh_token', '');
        $expiresAt = (int) $request->session()->get('spotify_user_token_expires_at', 0);

        if ($accessToken === '' && $refreshToken === '') {
            return response()->json([
                'message' => __('onboarding.spotify_connect_first'),
                'artists' => [],
            ], 401);
        }

        if ($accessToken === '' || ($expiresAt > 0 && now()->timestamp >= $expiresAt)) {
            if ($refreshToken === '') {
                return response()->json([
                    'message' => __('onboarding.spotify_connect_first'),
                    'artists' => [],
                ], 401);
            }

            try {
                $token = $this->spotify->refreshUserToken($refreshToken);
            } catch (\Throwable $e) {
                Log::warning('Spotify refresh token failed', [
                    'error' => $e->getMessage(),
                    'user_id' => auth()->id(),
                ]);

                $request->session()->forget([
                    'spotify_user_access_token',
                    'spotify_user_refresh_token',
                    'spotify_user_token_expires_at',
                ]);

                return response()->json([
                    'message' => __('onboarding.spotify_connect_first'),
                    'artists' => [],
                ], 401);
            }

            $accessToken = (string) ($token['access_token'] ?? '');
            $newRefreshToken = (string) ($token['refresh_token'] ?? '');
            $expiresIn = (int) ($token['expires_in'] ?? 3600);

            if ($accessToken === '') {
                return response()->json([
                    'message' => __('onboarding.spotify_connect_first'),
                    'artists' => [],
                ], 401);
            }

            $request->session()->put('spotify_user_access_token', $accessToken);
            $request->session()->put('spotify_user_token_expires_at', now()->addSeconds(max(60, $expiresIn - 30))->timestamp);
            if ($newRefreshToken !== '') {
                $request->session()->put('spotify_user_refresh_token', $newRefreshToken);
            }
        }

        try {
            $artists = $this->spotify->topArtists($accessToken, 10);
        } catch (\Throwable $e) {
            Log::warning('Spotify top artists fetch failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => __('onboarding.spotify_error'),
                'artists' => [],
            ], 502);
        }

        return response()->json(['artists' => $artists]);
    }

    private function resolveReturnRouteFromQuery(Request $request): string
    {
        $route = (string) $request->query('return_to', self::DEFAULT_RETURN_ROUTE);
        if (!in_array($route, self::ALLOWED_RETURN_ROUTES, true)) {
            return self::DEFAULT_RETURN_ROUTE;
        }

        return $route;
    }

    private function resolveReturnRouteFromSession(Request $request): string
    {
        $route = (string) $request->session()->pull('spotify_oauth_return_to', self::DEFAULT_RETURN_ROUTE);
        if (!in_array($route, self::ALLOWED_RETURN_ROUTES, true)) {
            return self::DEFAULT_RETURN_ROUTE;
        }

        return $route;
    }

    private function messagePrefixForRoute(string $route): string
    {
        return $route === 'profile' ? 'profile' : 'onboarding';
    }
}
