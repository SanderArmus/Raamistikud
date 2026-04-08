<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class FriendMoviesController extends Controller
{
    public function index(Request $request)
    {
        $url = 'https://ralfiharjutus.ta24siim.itmajakas.ee/api/movies';

        $ttlSeconds = 60;
        $payload = Cache::remember('friend_api:ralfi_movies', $ttlSeconds, function () use ($url) {
            $res = Http::timeout(10)->acceptJson()->get($url);

            if (! $res->successful()) {
                return [
                    'success' => false,
                    'error' => 'Friend API request failed (HTTP ' . $res->status() . ').',
                    'data' => [],
                    'count' => 0,
                ];
            }

            $json = $res->json();
            if (! is_array($json)) {
                return [
                    'success' => false,
                    'error' => 'Friend API returned invalid JSON.',
                    'data' => [],
                    'count' => 0,
                ];
            }

            return $json;
        });

        return Inertia::render('favorites/FriendApi', [
            'source_url' => $url,
            'payload' => $payload,
            'cached_ttl_seconds' => $ttlSeconds,
        ]);
    }
}

