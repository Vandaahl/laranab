<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Can't filter by decade and year at the same time.
        if ($request->filled('decade')) {
            $request->query->remove('startYear');
            $request->query->remove('endYear');
        }

        $validated = $request->validate([
            'director' => ['nullable', 'string', 'max:255'],
            'actor' => ['nullable', 'string', 'max:255'],
            'startYear' => ['nullable', 'integer'],
            'endYear' => ['nullable', 'integer'],
            'decade' => ['nullable', 'integer', 'min:1900', 'multiple_of:10'],
        ]);

        // Get movies ordered by latest NZBs that are attached to them.
        $movies = Movie::whereHas('nzbs')
            ->filterByDirector($validated['director'] ?? null)
            ->filterByActor($validated['actor'] ?? null)
            ->filterByYear($validated['startYear'] ?? null, $validated['endYear'] ?? null)
            ->filterByDecade($validated['decade'] ?? null)
            ->withMax('nzbs', 'published_at')
            ->orderByDesc('nzbs_max_published_at')
            ->with(['nzbs' => fn ($query) => $query->latest(), 'directors', 'actors', 'genres'])
            ->paginate(32)
            ->appends([
                'director' => $validated['director'] ?? null,
                'actor'=> $validated['actor'] ?? null,
                'startYear' => $validated['startYear'] ?? null,
                'endYear' => $validated['endYear'] ?? null,
            ]);

        return view('welcome', [
            'movies' => $movies,
            'heading' => 'Recent Movies',
            'showFilters' => true
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
