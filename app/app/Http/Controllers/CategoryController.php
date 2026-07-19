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
    public function show(Category $category)
    {
        // Get movies ordered by latest NZBs that are attached to them.
        $movies = Movie::whereHas('nzbs', fn ($query) => $query->inCategory($category))
            ->withMax([
                'nzbs as latest_category_nzb' => fn ($query) => $query->inCategory($category)
            ], 'published_at')
            ->orderByDesc('latest_category_nzb')
            ->with(['nzbs' => fn ($query) => $query->inCategory($category)->latest(), 'directors', 'actors', 'genres'])
            ->paginate(32);

        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->get();

        $heading = "Browsing movies in the {$category->name} category";

        return view('welcome', compact('category', 'movies', 'categories', 'heading'));
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
