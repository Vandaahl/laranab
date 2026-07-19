<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Movie;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function show(Category $category, Request $request)
    {
        $director = $request->query('director');
        $actor = $request->query('actor');
        $startYear = $request->query('startYear');
        $endYear = $request->query('endYear');

        // Get movies ordered by latest NZBs that are attached to them.
        $movies = Movie::whereHas('nzbs', fn ($query) => $query->inCategory($category))
            ->filterByDirector($director)
            ->filterByActor($actor)
            ->filterByYear($startYear, $endYear)
            ->withMax([
                'nzbs as latest_category_nzb' => fn ($query) => $query->inCategory($category)
            ], 'published_at')
            ->orderByDesc('latest_category_nzb')
            ->with(['nzbs' => fn ($query) => $query->inCategory($category)->latest(), 'directors', 'actors', 'genres'])
            ->paginate(32)
            ->appends([
                'director' => $director,
                'actor'=> $actor,
                'startYear' => $startYear,
                'endYear' => $endYear,
            ]);

        $label = strlen($category->name) <= 3 ? strtoupper($category->name) : ucfirst($category->name);
        $heading = "Browsing movies in the {$label} category";
        $showFilters = true;

        return view('welcome', compact('category', 'movies', 'heading', 'showFilters'));
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
