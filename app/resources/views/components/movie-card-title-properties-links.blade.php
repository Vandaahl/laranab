@php
    use App\Models\Movie;

    /** @var Movie $movie */
    /** @var string $runtimeDisplay */
@endphp

@props(['runtimeDisplay', 'movie', 'actorLimit' => 0])

<h3 class="text-lg font-bold">{{ $movie->title }} ({{ $movie->year }})</h3>
<ul class="text-xs/4">
    <li>Director: {{ $movie->directors->pluck('name')->join(', ') }}</li>
    <li class="{{ $actorLimit ? 'line-clamp-1' : '' }}">
        Actors:
        {{ ($actorLimit ? $movie->actors->take($actorLimit) : $movie->actors)
            ->pluck('name')
            ->join(', ') }}
    </li>
    <li>Genres: {{ $movie->genres->pluck('name')->join(', ') }}</li>
    <li>Runtime: {{ $runtimeDisplay }}</li>
    <li class="mt-1">
        <div class="badge badge-ghost badge-xs">
            <a href="https://www.imdb.com/title/{{ $movie->imdb_id }}" target="_blank" rel="noopener">IMDb {{ $movie->imdb_score }}</a>
        </div>
        <div class="badge badge-ghost badge-xs">
            <a href="https://www.themoviedb.org/movie/{{ $movie->tmdb_id }}" target="_blank" rel="noopener">TMDB</a>
        </div>
    </li>
</ul>
<p class="{{ $actorLimit ? 'line-clamp-3' : 'py-4' }}">{{ $movie->overview }}</p>
