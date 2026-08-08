@php
    $start = 1900;
    $end = date('Y');
    $years = [];
    for ($i = $start; $i <= $end; $i++) {
        $years[] = $i;
    }
    $years = array_reverse($years);
    $currentDecade = intdiv(now()->year, 10) * 10;
    $decades = array_reverse(range(1900, $currentDecade, 10));
@endphp

<form method="GET" action="{{ url()->current() }}" class="py-4 flex gap-5 flex-col md:flex-row">
    <input
        type="text"
        name="director"
        value="{{ request('director') }}"
        placeholder="Director (e.g. Nolan)"
        class="input"
    >

    <input
        type="text"
        name="actor"
        value="{{ request('actor') }}"
        placeholder="Actor (e.g. Stallone)"
        class="input"
    >

    <div class="flex flex-nowrap gap-2">
        <select name="startYear" class="select w-auto">
            <option value="">Start year</option>
            @foreach($years as $y)
                <option value="{{ $y }}" @selected((string)request('startYear') === (string)$y)>
                    {{ $y }}
                </option>
            @endforeach
        </select>

        <select name="endYear" class="select w-auto">
            <option value="">End year</option>
            @foreach($years as $y)
                <option value="{{ $y }}" @selected((string)request('endYear') === (string)$y)>
                    {{ $y }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="flex flex-nowrap gap-2">
        <select name="decade" class="select w-auto">
            <option value="">All decades</option>
            @foreach($decades as $d)
                <option value="{{ $d }}" @selected((string)request('decade') === (string)$d)>
                    {{ $d }}s
                </option>
            @endforeach
        </select>
    </div>

    <button type="submit" class="btn w-24">Filter</button>
</form>
