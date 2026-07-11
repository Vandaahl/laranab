@props([
    "title" => config('app.name')
])

    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>
    <script src="https://unpkg.com/imagesloaded@5/imagesloaded.pkgd.min.js"></script>
</head>
<body class="antialiased">
<div class="min-h-screen flex flex-col">
    <x-nav></x-nav>

    <main class="container mx-auto px-4 py-8">
        {{ $slot }}
    </main>

    <x-footer></x-footer>
</div>
</body>
</html>
