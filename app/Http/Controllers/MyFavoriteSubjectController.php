<?php

namespace App\Http\Controllers;

use App\Models\MyFavoriteSubject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MyFavoriteSubjectController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'director' => ['nullable', 'string', 'max:255'],
            'genre' => ['nullable', 'string', 'max:255'],
            'year_from' => ['nullable', 'integer', 'min:1800', 'max:2500'],
            'year_to' => ['nullable', 'integer', 'min:1800', 'max:2500'],
            'sort' => ['nullable', 'in:created_at,title,release_year'],
            'direction' => ['nullable', 'in:asc,desc'],
        ]);

        $sort = (string) ($validated['sort'] ?? 'created_at');
        $direction = (string) ($validated['direction'] ?? 'desc');

        $query = MyFavoriteSubject::query();

        if (($validated['search'] ?? '') !== '') {
            $query->where('title', 'like', '%' . (string) $validated['search'] . '%');
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

        $subjects = $query
            ->orderBy($sort, $direction)
            ->paginate(24)
            ->withQueryString();

        return Inertia::render('favorites/Index', [
            'subjects' => $subjects,
            'filters' => [
                'search' => $validated['search'] ?? '',
                'director' => $validated['director'] ?? '',
                'genre' => $validated['genre'] ?? '',
                'year_from' => $validated['year_from'] ?? null,
                'year_to' => $validated['year_to'] ?? null,
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('favorites/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'string', 'max:2000'],
            'description' => ['required', 'string'],
            'director' => ['required', 'string', 'max:255'],
            'release_year' => ['required', 'integer', 'min:1800', 'max:2500'],
            'genre' => ['nullable', 'string', 'max:255'],
        ]);

        MyFavoriteSubject::query()->create($validated);

        return redirect()->route('favorites.index')->with('success', 'Added!');
    }
}

