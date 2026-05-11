<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SpotifyService
{
    private const AUTHORIZE_URL = 'https://accounts.spotify.com/authorize';
    private const TOKEN_URL = 'https://accounts.spotify.com/api/token';
    private const API_BASE = 'https://api.spotify.com/v1';

    public function isConfigured(): bool
    {
        return $this->clientId() !== '' && $this->clientSecret() !== '';
    }

    public function redirectUri(): string
    {
        $fallback = rtrim((string) env('APP_URL', ''), '/') . '/spotify/callback';
        return (string) env('SPOTIFY_REDIRECT_URI', $fallback);
    }

    public function authorizeUrl(string $state): string
    {
        $query = http_build_query([
            'client_id' => $this->clientId(),
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri(),
            'scope' => 'user-top-read',
            'state' => $state,
            'show_dialog' => 'true',
        ], '', '&', PHP_QUERY_RFC3986);

        return self::AUTHORIZE_URL . '?' . $query;
    }

    public function exchangeAuthorizationCode(string $code): array
    {
        return $this->requestToken([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri(),
        ]);
    }

    public function refreshUserToken(string $refreshToken): array
    {
        return $this->requestToken([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);
    }

    public function searchArtists(string $query, int $limit = 8): array
    {
        $accessToken = $this->clientCredentialsToken();
        if ($accessToken === '') {
            return [];
        }

        $response = Http::timeout(10)
            ->acceptJson()
            ->withToken($accessToken)
            ->get(self::API_BASE . '/search', [
                'q' => $query,
                'type' => 'artist',
                'limit' => max(1, min(20, $limit)),
            ]);

        if (!$response->ok()) {
            return [];
        }

        return collect($response->json('artists.items', []))
            ->map(function ($item) {
                return [
                    'id' => (string) ($item['id'] ?? ''),
                    'name' => trim((string) ($item['name'] ?? '')),
                    'image' => data_get($item, 'images.0.url'),
                    'url' => data_get($item, 'external_urls.spotify'),
                ];
            })
            ->filter(fn ($item) => $item['name'] !== '')
            ->values()
            ->all();
    }

    public function topArtists(string $userAccessToken, int $limit = 10): array
    {
        $response = Http::timeout(10)
            ->acceptJson()
            ->withToken($userAccessToken)
            ->get(self::API_BASE . '/me/top/artists', [
                'time_range' => 'medium_term',
                'limit' => max(1, min(50, $limit)),
            ]);

        if (!$response->ok()) {
            return [];
        }

        return collect($response->json('items', []))
            ->map(function ($item) {
                return [
                    'id' => (string) ($item['id'] ?? ''),
                    'name' => trim((string) ($item['name'] ?? '')),
                    'image' => data_get($item, 'images.0.url'),
                    'url' => data_get($item, 'external_urls.spotify'),
                ];
            })
            ->filter(fn ($item) => $item['name'] !== '')
            ->values()
            ->all();
    }

    private function clientCredentialsToken(): string
    {
        if (!$this->isConfigured()) {
            return '';
        }

        $cached = Cache::get('spotify.client_credentials_token');
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $token = $this->requestToken([
            'grant_type' => 'client_credentials',
        ]);

        $accessToken = (string) ($token['access_token'] ?? '');
        $expiresIn = (int) ($token['expires_in'] ?? 3600);

        if ($accessToken !== '') {
            Cache::put('spotify.client_credentials_token', $accessToken, now()->addSeconds(max(60, $expiresIn - 90)));
        }

        return $accessToken;
    }

    private function requestToken(array $payload): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Spotify credentials are not configured.');
        }

        $response = Http::timeout(10)
            ->acceptJson()
            ->asForm()
            ->withBasicAuth($this->clientId(), $this->clientSecret())
            ->post(self::TOKEN_URL, $payload);

        if (!$response->ok()) {
            throw new RuntimeException('Spotify token request failed with status ' . $response->status());
        }

        $json = $response->json();

        return [
            'access_token' => (string) ($json['access_token'] ?? ''),
            'refresh_token' => (string) ($json['refresh_token'] ?? ''),
            'expires_in' => (int) ($json['expires_in'] ?? 3600),
        ];
    }

    private function clientId(): string
    {
        return trim((string) env('SPOTIFY_CLIENT_ID', ''));
    }

    private function clientSecret(): string
    {
        return trim((string) env('SPOTIFY_CLIENT_SECRET', ''));
    }
}
