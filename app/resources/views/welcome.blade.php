<x-layout title="Home">
    <h1>{{ $heading }}</h1>

    @if($movies->total())
        <p class="mb-4">Found {{ $movies->total() }} movies</p>

        <x-movie-grid :movies="$movies" />

        {{-- Pagination --}}
        {{ $movies->links() }}
    @else
        <p>There are no movies</p>
    @endif
</x-layout>
