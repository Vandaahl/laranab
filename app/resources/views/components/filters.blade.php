@php
    $start = 1900;
    $end = date('Y');
    $years = [];
    for ($i = $start; $i <= $end; $i++) {
        $years[] = $i;
    }
    $years = array_reverse($years);
@endphp

<form method="GET" action="{{ url()->current() }}" class="py-4">
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

    <button type="submit" class="btn">Filter</button>
</form>
