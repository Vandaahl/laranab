@php
use App\Models\Movie;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Collection;

/** @var \Illuminate\Support\Collection<Movie> $movies */
@endphp

@props(['movies'])

<div id="masonry-grid" class="flex flex-wrap mb-8">
    @foreach($movies as $movie)
        @php
            $interval = CarbonInterval::minutes($movie->runtime)->cascade();
            $runtimeDisplay = $interval->hours . 'h ' . $interval->minutes . 'm'; // "1h 30m"
            $score = round($movie->imdb_score * 10);
            $colorClasses = match(true) {
                $score > 69 => ['bg' => 'bg-success', 'text' => 'text-success-content', 'border' => 'border-success'],
                $score < 70 && $score > 49 => ['bg' => 'bg-warning', 'text' => 'text-warning-content', 'border' => 'border-warning'],
                default => ['bg' => 'bg-error', 'text' => 'text-error-content', 'border' => 'border-error']
            }
        @endphp

        <div class="masonry-item w-full sm:w-1/2 md:w-1/3 lg:w-1/4 2xl:w-1/8 p-2">
            <div class="card bg-base-100 shadow-sm h-full">
                <figure class="aspect-2/3 overflow-visible bg-gray-200 relative">
                    @if($movie->poster)
                        <img
                            src="{{ asset('storage/' . $movie->poster) }}"
                            alt="{{ $movie->title }} ({{ $movie->year }})"
                            class="w-full h-full object-cover"
                            loading="lazy"/>
                    @else
                        <x-movie-card.missing-image/>
                    @endif
                    <div
                        class="radial-progress {{ $colorClasses['bg'] }} {{ $colorClasses['text'] }} {{ $colorClasses['border'] }} border-4 text-xs absolute -bottom-5 -right-3"
                        style="--value:{{ $score }}; --size:1.8rem;" aria-valuenow="{{ $score }}"
                        role="progressbar">
                        {{ $score }}
                    </div>
                </figure>
                <div class="card-body">
                    <x-movie-card.body :movie="$movie" :runtime-display="$runtimeDisplay" actor-limit="3" condensed="true"/>
                </div>
            </div>
        </div>

        <dialog id="movie{{ $movie->id }}" class="modal">
            <div class="modal-box w-11/12 max-w-5xl">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-none">
                        @if($movie->poster)
                            <img
                                src="{{ asset('storage/' . $movie->poster) }}"
                                alt="{{ $movie->title }} ({{ $movie->year }})"
                                class="w-48"
                                loading="lazy"/>
                        @else
                            <x-movie-card.missing-image/>
                        @endif
                    </div>
                    <div>
                        <x-movie-card.body :movie="$movie" :runtime-display="$runtimeDisplay"/>
                    </div>
                </div>
                <x-movie-card.nzb-list :movie="$movie"/>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>close</button>
            </form>
        </dialog>
    @endforeach
</div>
