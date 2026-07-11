<x-layout title="{{ $category->name }}">
    <h1>Recent Movies with NZBs</h1>

    @if($movies->count())
        <p class="mb-4">Found {{ $movies->count() }} movies with NZBs</p>
        <div class="space-y-6">
            @foreach($movies as $movie)
                <div class="movie-item">
                    <h2 class="text-l font-bold">{{ $movie->title }} ({{ $movie->year }})</h2>
                    <ul class="list-disc ml-6 mt-2">
                        @foreach($movie->nzbs as $nzb)
                            <li>{{ $nzb->title }} - <span class="text-sm text-gray-500">{{ $nzb->created_at->diffForHumans() }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @else
        <p>There are no movies with NZBs</p>
    @endif
</x-layout>
