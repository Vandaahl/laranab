@php
    use App\Models\Movie;

    /** @var Movie $movie */
    /** @var string $runtimeDisplay */
@endphp

@props(['runtimeDisplay', 'movie', 'actorLimit' => 0, 'condensed' => false])

<h3 class="text-lg font-bold">{{ $movie->title }} <span class="text-gray-500">({{ $movie->year }})</span></h3>

<ul class="text-xs/4">
    <li>
        Director:
        {!! $movie->directors
            ->map(fn ($director) =>
                $condensed ? e($director->name) : '<a href="'.route('credits.show', $director).'" class="link">'.e($director->name).'</a>'
            )
            ->implode(', ') !!}
    </li>
    <li class="{{ $condensed ? 'line-clamp-1' : '' }}">
        Actors:
        {!! ($actorLimit ? $movie->actors->take($actorLimit) : $movie->actors)
            ->map(fn ($actor) =>
                $actorLimit ? e($actor->name) : '<a href="'.route('credits.show', $actor).'" class="link">'.e($actor->name).'</a>'
            )
            ->implode(', ') !!}
    </li>
    <li>Genres: {{ $movie->genres->pluck('name')->join(', ') }}</li>
    <li>Runtime: {{ $runtimeDisplay }}</li>
    <li class="mt-1">
        <a href="https://www.imdb.com/title/{{ $movie->imdb_id }}" target="_blank" rel="noopener" class="btn btn-xs">IMDb {{ $movie->imdb_score }}</a>
        <a href="https://www.themoviedb.org/movie/{{ $movie->tmdb_id }}" target="_blank" rel="noopener" class="btn btn-xs">TMDB</a>
    </li>
</ul>

<p class="{{ $condensed ? 'line-clamp-3' : 'py-4' }}">{{ $movie->overview }}</p>

@if($condensed)
    <div class="card-actions justify-end items-center">
        <div class="badge badge-ghost badge-sm">
            Updated {{ $movie->nzbs->first()?->published_at->diffForHumans(short: true) }}</div>
        <button class="btn btn-xs btn-primary" onclick="movie{{ $movie->id }}.showModal()">Show Details</button>
    </div>
@endif
