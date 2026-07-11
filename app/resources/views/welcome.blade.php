@php use Carbon\CarbonInterval; @endphp

<x-layout title="Home">
    <h1>Recent Movies with NZBs</h1>

    <ul>
        @foreach($categories as $category)
            <li>
                <a href="{{ route('categories.show', $category) }}">
                    {{ $category->name }}
                </a>
            </li>
            @if($category->children->isNotEmpty())
                <li>
                    <ul>
                        @foreach($category->children as $child)
                            <li>
                                <a href="{{ route('categories.show', $child) }}">
                                    {{ $child->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endif
        @endforeach
    </ul>

    @if($movies->count())
        <p class="mb-4">Found {{ $movies->count() }} movies with NZB's</p>

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

                <div class="masonry-item w-full sm:w-1/2 md:w-1/3 lg:w-1/4 p-2">
                    <div class="card bg-base-100 shadow-sm h-full">
                        <figure class="aspect-2/3 overflow-visible bg-gray-200 relative">
                            @if($movie->poster)
                                <img
                                    src="{{ asset('storage/' . $movie->poster) }}"
                                    alt="{{ $movie->title }} ({{ $movie->year }})"
                                    class="w-full h-full object-cover"
                                    loading="lazy"/>
                            @else
                                <div class="flex items-center justify-center bg-gray-200 h-64 w-full">
                                    <span class="text-gray-400 italic">No image</span>
                                </div>
                            @endif
                            <div class="radial-progress {{ $colorClasses['bg'] }} {{ $colorClasses['text'] }} {{ $colorClasses['border'] }} border-4 text-xs absolute -bottom-5 -right-3" style="--value:{{ $score }}; --size:1.8rem;" aria-valuenow="{{ $score }}" role="progressbar">{{ $score }}</div>
                        </figure>
                        <div class="card-body">
                            <x-movie-card-title-properties-links :movie="$movie" :runtime-display="$runtimeDisplay" actor-limit="3" />

                            <div class="card-actions justify-end items-center">
                                <div class="badge badge-ghost badge-sm">Updated {{ $movie->nzbs->first()?->published_at->diffForHumans() }}</div>
                                <button class="btn btn-xs btn-info" onclick="movie{{ $movie->id }}.showModal()">Show Details</button>
                            </div>
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
                                    <div class="flex items-center justify-center bg-gray-200 h-64 w-full">
                                        <span class="text-gray-400 italic">No image</span>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <x-movie-card-title-properties-links :movie="$movie" :runtime-display="$runtimeDisplay" />
                                <ul class="list-disc list-inside">
                                    @foreach($movie->nzbs as $nzb)
                                        @php
                                            $bytes = (int) $nzb->size;
                                            $gib = 1024 ** 3;
                                            $mib = 1024 ** 2;

                                            if ($bytes < $gib) {
                                                $size = $bytes / $mib;
                                                $unit = 'MB';
                                            } else {
                                                $size = $bytes / $gib;
                                                $unit = 'GiB';
                                            }
                                        @endphp
                                        <li>
                                            {{ $nzb->title }} - <span class="text-sm text-gray-500">{{ $nzb->published_at->diffForHumans() }}</span>
                                            <div>
                                                <div class="badge badge-sm">{{ round($size, 2) }} {{ $unit }}</div>
                                                @if($nzb->nfo)
                                                    <div class="badge badge-sm"><a href="{{ route('nzb.nfo', $nzb) }}" target="_blank" rel="noopener">nfo</a></div>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <form method="dialog" class="modal-backdrop">
                        <button>close</button>
                    </form>
                </dialog>
            @endforeach
        </div>

        {{-- Pagination --}}
        {{ $movies->links() }}
    @else
        <p>There are no movies with NZBs</p>
    @endif
</x-layout>
