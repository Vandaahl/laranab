<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get movies ordered by latest NZBs that are attached to them.
        $movies = Movie::whereHas('nzbs')
            ->withMax('nzbs', 'published_at')
            ->orderByDesc('nzbs_max_published_at')
            ->with(['nzbs' => fn ($query) => $query->latest(), 'directors', 'actors', 'genres'])
            ->paginate(32);

        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->get();

        return view('welcome', [
            'categories' => $categories,
            'movies' => $movies
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
