<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GifService
{
    private const TENOR_SEARCH_URL = 'https://tenor.googleapis.com/v2/search';
    private const TENOR_FEATURED_URL = 'https://tenor.googleapis.com/v2/featured';
    private const TENOR_LEGACY_SEARCH_URL = 'https://g.tenor.com/v1/search';
    private const TENOR_LEGACY_TRENDING_URL = 'https://g.tenor.com/v1/trending';
    private const GIPHY_SEARCH_URL = 'https://api.giphy.com/v1/gifs/search';
    private const GIPHY_TRENDING_URL = 'https://api.giphy.com/v1/gifs/trending';

    public function search(string $query, int $limit = 18): array
    {
        $query = trim($query);
        $limit = max(1, min(30, $limit));
        $cacheKey = 'gif.search.' . md5(mb_strtolower($query) . '|' . $limit);

        $cached = Cache::get($cacheKey);
        if (is_array($cached) && !empty($cached)) {
            return $cached;
        }

        $tenor = $this->searchTenor($query, $limit);
        if (!empty($tenor)) {
            Cache::put($cacheKey, $tenor, now()->addMinutes(10));
            return $tenor;
        }

        $giphy = $this->searchGiphy($query, $limit);
        if (!empty($giphy)) {
            Cache::put($cacheKey, $giphy, now()->addMinutes(10));
        }

        return $giphy;
    }

    private function searchTenor(string $query, int $limit): array
    {
        $apiKey = trim((string) env('TENOR_API_KEY', 'LIVDSRZULELA'));
        if ($apiKey === '') {
            return [];
        }

        $v2 = $this->searchTenorV2($apiKey, $query, $limit);
        if (!empty($v2)) {
            return $v2;
        }

        return $this->searchTenorLegacy($apiKey, $query, $limit);
    }

    private function searchTenorV2(string $apiKey, string $query, int $limit): array
    {
        try {
            $endpoint = $query === '' ? self::TENOR_FEATURED_URL : self::TENOR_SEARCH_URL;
            $params = [
                'key' => $apiKey,
                'limit' => $limit,
                'media_filter' => 'gif,tinygif',
                'contentfilter' => 'medium',
            ];

            if ($query !== '') {
                $params['q'] = $query;
            }

            $response = Http::timeout(6)->acceptJson()->get($endpoint, $params);
            if (!$response->ok()) {
                Log::warning('Tenor API error', [
                    'status' => $response->status(),
                    'query' => $query,
                ]);
                return [];
            }

            return collect($response->json('results', []))
                ->map(function ($item) {
                    return data_get($item, 'media_formats.gif.url')
                        ?? data_get($item, 'media_formats.tinygif.url')
                        ?? data_get($item, 'media_formats.nanogif.url');
                })
                ->filter(fn ($url) => is_string($url) && $url !== '')
                ->unique()
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::warning('Tenor v2 request failed', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    private function searchTenorLegacy(string $apiKey, string $query, int $limit): array
    {
        try {
            $endpoint = $query === '' ? self::TENOR_LEGACY_TRENDING_URL : self::TENOR_LEGACY_SEARCH_URL;
            $params = [
                'key' => $apiKey,
                'limit' => $limit,
            ];

            if ($query !== '') {
                $params['q'] = $query;
            }

            $response = Http::timeout(6)->acceptJson()->get($endpoint, $params);
            if (!$response->ok()) {
                Log::warning('Tenor legacy API error', [
                    'status' => $response->status(),
                    'query' => $query,
                ]);
                return [];
            }

            return collect($response->json('results', []))
                ->map(function ($item) {
                    return data_get($item, 'media.0.gif.url')
                        ?? data_get($item, 'media.0.tinygif.url')
                        ?? data_get($item, 'media.0.nanogif.url');
                })
                ->filter(fn ($url) => is_string($url) && $url !== '')
                ->unique()
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::warning('Tenor legacy request failed', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    private function searchGiphy(string $query, int $limit): array
    {
        $apiKey = trim((string) env('GIPHY_API_KEY', ''));
        if ($apiKey === '') {
            return [];
        }

        try {
            $endpoint = $query === '' ? self::GIPHY_TRENDING_URL : self::GIPHY_SEARCH_URL;
            $params = [
                'api_key' => $apiKey,
                'limit' => $limit,
                'rating' => 'pg-13',
            ];

            if ($query !== '') {
                $params['q'] = $query;
                $params['lang'] = 'pl';
            }

            $response = Http::timeout(6)->acceptJson()->get($endpoint, $params);
            if (!$response->ok()) {
                Log::warning('GIPHY API error', [
                    'status' => $response->status(),
                    'query' => $query,
                ]);
                return [];
            }

            return collect($response->json('data', []))
                ->map(function ($item) {
                    return data_get($item, 'images.fixed_width.url')
                        ?? data_get($item, 'images.original.url');
                })
                ->filter(fn ($url) => is_string($url) && $url !== '')
                ->unique()
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::warning('GIPHY request failed', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
}
