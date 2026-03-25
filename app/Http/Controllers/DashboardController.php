<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $city = trim((string) $request->query('city'));
        $country = trim((string) $request->query('country'));

        // OpenWeather's `q` expects at least a city (optionally with country code).
        $q = ! empty($city) ? $city : '';
        if (! empty($q) && ! empty($country)) {
            $q = $q . ',' . $country;
        }

        // Default location when user doesn't provide a city.
        $q = ! empty($q) ? $q : 'Tallinn';

        $cacheKey = 'weather:' . md5($q);

        $value = Cache::has($cacheKey) ? Cache::get($cacheKey) : null;

        if ($value === null) {
            $apiKey = trim((string) config('services.weather.key'));
            if (empty($apiKey)) {
                $value = null;
                Cache::forget($cacheKey);
            } else {
                $response = Http::get('https://api.openweathermap.org/data/2.5/weather', [
                    'q' => $q,
                    'appid' => $apiKey,
                    'units' => 'metric',
                ]);

                if (! $response->successful()) {
                    $value = null;
                } else {
                    $json = $response->json();
                    $value = is_array($json) ? $json : null;
                }
            }

            // Only cache successful responses (avoid caching failures as "null").
            if ($value !== null) {
                Cache::put($cacheKey, $value, now()->addHour());
            }
        }

        return Inertia::render('Dashboard', [
            'weather' => $value,
        ]);
    }
}
