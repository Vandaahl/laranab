@php
    use App\Models\Movie;

    /** @var Movie $movie */
@endphp

@props(['movie'])

<ul class="list-none list-inside mt-8">
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
        <li class="border-b border-gray-200 pt-1 pb-2">
            {{ $nzb->title }} -
            <div class="badge badge-sm">{{ round($size, 2) }} {{ $unit }}</div>
            <span class="text-sm text-gray-500">{{ $nzb->published_at->diffForHumans() }}</span>
            <div>
                <a class="btn btn-secondary btn-xs gap-x-1" href="{{ $nzb->nzb }}">
                    <svg class="size-[1em]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path fill="#fff" d="m12 16l-5-5l1.4-1.45l2.6 2.6V4h2v8.15l2.6-2.6L17 11zm-6 4q-.825 0-1.412-.587T4 18v-3h2v3h12v-3h2v3q0 .825-.587 1.413T18 20z" />
                    </svg>
                    download nzb
                </a>
                @if($nzb->nfo)
                    <a href="{{ route('nzb.nfo', $nzb) }}" target="_blank" rel="noopener" class="btn btn-xs">nfo</a>
                @endif
            </div>
        </li>
    @endforeach
</ul>
