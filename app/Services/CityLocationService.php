<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CityLocationService
{
    private const GEOCODE_URL = 'https://geocoding-api.open-meteo.com/v1/search';

    public function resolve(string $city): ?array
    {
        $city = trim($city);
        if ($city === '') {
            return null;
        }

        $cacheKey = 'city.coords.' . md5(mb_strtolower($city));
        $cached = Cache::get($cacheKey);
        if (is_array($cached) && isset($cached['lat'], $cached['lng'])) {
            return $cached;
        }

        try {
            $response = Http::timeout(6)
                ->acceptJson()
                ->withHeaders(['User-Agent' => 'Altermatch/1.0'])
                ->get(self::GEOCODE_URL, [
                    'name' => $city,
                    'count' => 1,
                    'language' => 'pl',
                    'format' => 'json',
                ]);

            if (!$response->ok()) {
                return null;
            }

            $result = $response->json('results.0');
            $lat = data_get($result, 'latitude');
            $lng = data_get($result, 'longitude');

            if (!is_numeric($lat) || !is_numeric($lng)) {
                return null;
            }

            $coords = [
                'lat' => round((float) $lat, 7),
                'lng' => round((float) $lng, 7),
            ];

            Cache::put($cacheKey, $coords, now()->addDays(30));

            return $coords;
        } catch (\Throwable $e) {
            Log::warning('City geocoding failed', [
                'city' => $city,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
