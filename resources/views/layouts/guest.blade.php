<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Recrutement</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @php $manifestPath = public_path('build/manifest.json'); @endphp

        @if (file_exists($manifestPath))
            @php $manifest = json_decode(file_get_contents($manifestPath), true); @endphp
            @php
                $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
                $jsFile = $manifest['resources/js/app.js']['file'] ?? null;
                if (!$cssFile && isset($manifest['resources/js/app.js']['css'])) {
                    $cssFile = $manifest['resources/js/app.js']['css'][0] ?? null;
                }
            @endphp

            @if($cssFile)
                <link rel="stylesheet" href="{{ asset('build/' . ltrim($cssFile, '/')) }}">
            @else
                <link rel="stylesheet" href="{{ asset('build/assets/app.css') }}">
            @endif

            @if($jsFile)
                <script src="{{ asset('build/' . ltrim($jsFile, '/')) }}" defer></script>
            @else
                <script src="{{ asset('build/assets/app.js') }}" defer></script>
            @endif
        @else
            <link rel="stylesheet" href="{{ asset('build/assets/app.css') }}">
            <script src="{{ asset('build/assets/app.js') }}" defer></script>
        @endif
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>