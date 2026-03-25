<?php

namespace App\Http\Controllers;

use App\Models\Marker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MarkerController extends Controller
{
    public function index()
    {
        return Inertia::render('markers/Index', [
            'markers' => Marker::query()
                ->orderByDesc('added')
                ->get(['id', 'name', 'latitude', 'longitude', 'description', 'added', 'edited']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'description' => ['required', 'string', 'max:5000'],
        ]);

        $now = now();
        $marker = Marker::create([
            ...$validated,
            'added' => $now,
            'edited' => $now,
        ]);

        return redirect()->route('markers.show', $marker)->with('success', 'Marker created.');
    }

    public function show(Marker $marker)
    {
        return Inertia::render('markers/View', [
            'marker' => $marker->only(['id', 'name', 'latitude', 'longitude', 'description', 'added', 'edited']),
        ]);
    }

    public function update(Request $request, Marker $marker): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'description' => ['required', 'string', 'max:5000'],
        ]);

        $marker->update([
            ...$validated,
            'edited' => now(),
        ]);

        return redirect()->route('markers.show', $marker)->with('success', 'Marker updated.');
    }

    public function destroy(Marker $marker): RedirectResponse
    {
        $marker->delete();

        return redirect()->route('markers.index')->with('success', 'Marker deleted.');
    }
}

