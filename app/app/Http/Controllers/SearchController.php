<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $term = trim((string) ($data['q'] ?? ''));

        $moviesQuery = Movie::query()
            ->select('movies.*')
            ->with(['nzbs' => fn ($query) => $query->latest(), 'directors', 'actors', 'genres'])
            ->withMax('nzbs', 'published_at')
            ->orderByDesc('nzbs_max_published_at');

        if ($term !== '') {
            $like = '%' . $term . '%';

            $moviesQuery->where(function ($query) use ($like) {
                $query->where('movies.title', 'like', $like)
                    ->orWhere('movies.original_title', 'like', $like)
                    ->orWhere('movies.overview', 'like', $like)
                    ->orWhereHas('actors', function ($q) use ($like) {
                        $q->where('name', 'like', $like);
                    })
                    ->orWhereHas('directors', function ($q) use ($like) {
                        $q->where('name', 'like', $like);
                    });
            });
        }

        $movies = $moviesQuery
            ->paginate(32)
            ->appends([
                'q' => $term,
            ]);

        return view('welcome', [
            'movies' => $movies,
            'heading' => 'Search results for "' . $term . '"'
        ]);
    }
}
