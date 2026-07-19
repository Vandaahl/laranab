@props(['categories'])

<div class="navbar bg-base-100 shadow-sm">
    <div class="navbar-start">
        <div class="dropdown">
            <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
            </div>
            <ul
                tabindex="-1"
                class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow">
                <li><a href="{{ route('home') }}">Homepage</a></li>
                @foreach($categories as $category)
                    <li>
                        <a href="{{ route('categories.show', $category) }}">
                            {{ ucfirst($category->name) }}
                        </a>
                    </li>
                    @if($category->children->isNotEmpty())
                        <li>
                            <ul>
                                @foreach($category->children as $child)
                                    <li>
                                        <a href="{{ route('categories.show', $child) }}">
                                            @if(mb_strlen($child->name) <= 3)
                                                {{ mb_strtoupper($child->name) }}
                                            @else
                                                {{ ucfirst($child->name) }}
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
    <div class="navbar-center">
        <a class="btn btn-ghost text-xl" href="{{ route('home') }}">Newznabber</a>
    </div>
    <div class="navbar-end">
        <form method="GET" action="{{ route('search') }}" class="join">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Search movies..."
                class="input input-bordered w-auto"
            >
            <button type="submit" class="btn btn-neutral join-item">Search</button>
        </form>
    </div>
</div>
