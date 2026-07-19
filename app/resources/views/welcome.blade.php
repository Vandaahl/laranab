<x-layout title="Home">
    <h1>{{ $heading }}</h1>

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

    @if($movies->total())
        <p class="mb-4">Found {{ $movies->total() }} movies</p>

        <x-movie-grid :movies="$movies" />

        {{-- Pagination --}}
        {{ $movies->links() }}
    @else
        <p>There are no movies</p>
    @endif
</x-layout>
