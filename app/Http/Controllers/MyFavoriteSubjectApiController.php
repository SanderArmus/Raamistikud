<?php

namespace App\Http\Controllers;

use App\Models\MyFavoriteSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MyFavoriteSubjectApiController extends Controller
{
    /**
     * Documented JSON API endpoint.
     *
     * Query params:
     * - limit: integer (1..100), default 30
     * - search: string (searches in title)
     * - director: string (exact match)
     * - genre: string (exact match)
     * - year_from: integer (min release_year)
     * - year_to: integer (max release_year)
     * - sort: one of: created_at, title, release_year (default created_at)
     * - direction: asc|desc (default desc)
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:200'],
            'director' => ['nullable', 'string', 'max:255'],
            'genre' => ['nullable', 'string', 'max:255'],
            'year_from' => ['nullable', 'integer', 'min:1800', 'max:2500'],
            'year_to' => ['nullable', 'integer', 'min:1800', 'max:2500'],
            'sort' => ['nullable', 'in:created_at,title,release_year'],
            'direction' => ['nullable', 'in:asc,desc'],
        ]);

        $limit = (int) ($validated['limit'] ?? 30);
        $sort = (string) ($validated['sort'] ?? 'created_at');
        $direction = (string) ($validated['direction'] ?? 'desc');

        // Cache key based on query params.
        $cacheKey = 'api:my-favorite-subjects:' . md5(json_encode([
            'limit' => $limit,
            'search' => $validated['search'] ?? null,
            'director' => $validated['director'] ?? null,
            'genre' => $validated['genre'] ?? null,
            'year_from' => $validated['year_from'] ?? null,
            'year_to' => $validated['year_to'] ?? null,
            'sort' => $sort,
            'direction' => $direction,
        ]));

        $ttlSeconds = 60;

        $payload = Cache::remember($cacheKey, $ttlSeconds, function () use ($validated, $limit, $sort, $direction) {
            $query = MyFavoriteSubject::query();

            if (($validated['search'] ?? '') !== '') {
                $search = (string) $validated['search'];
                $query->where('title', 'like', '%' . $search . '%');
            }

            if (($validated['director'] ?? '') !== '') {
                $query->where('director', (string) $validated['director']);
            }

            if (($validated['genre'] ?? '') !== '') {
                $query->where('genre', (string) $validated['genre']);
            }

            if (! empty($validated['year_from'])) {
                $query->where('release_year', '>=', (int) $validated['year_from']);
            }

            if (! empty($validated['year_to'])) {
                $query->where('release_year', '<=', (int) $validated['year_to']);
            }

            $query->orderBy($sort, $direction)->limit($limit);

            $items = $query->get([
                'id',
                'title',
                'image',
                'description',
                'director',
                'release_year',
                'genre',
                'created_at',
                'updated_at',
            ]);

            return [
                'data' => $items,
                'meta' => [
                    'limit' => $limit,
                    'sort' => $sort,
                    'direction' => $direction,
                    'cached_ttl_seconds' => 60,
                ],
            ];
        });

        return response()->json($payload);
    }
}

