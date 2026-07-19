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
        $director = $request->query('director');
        $actor = $request->query('actor');
        $startYear = $request->query('startYear');
        $endYear = $request->query('endYear');

        // Get movies ordered by latest NZBs that are attached to them.
        $movies = Movie::whereHas('nzbs')
            ->filterByDirector($director)
            ->filterByActor($actor)
            ->filterByYear($startYear, $endYear)
            ->withMax('nzbs', 'published_at')
            ->orderByDesc('nzbs_max_published_at')
            ->with(['nzbs' => fn ($query) => $query->latest(), 'directors', 'actors', 'genres'])
            ->paginate(32)
            ->appends([
                'director' => $director,
                'actor'=> $actor,
                'startYear' => $startYear,
                'endYear' => $endYear,
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
