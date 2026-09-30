<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Recrutement</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Figtree', 'sans-serif'],
                        serif: ['Fraunces', 'serif'],
                        mono: ['IBM Plex Mono', 'monospace'],
                    },
                }
            }
        }
    </script>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex lg:grid lg:grid-cols-2">

        {{-- ==================== LEFT PANEL — BRAND ==================== --}}
        <div class="hidden lg:flex flex-col justify-between bg-[#13224B] text-white p-12 relative overflow-hidden">
            <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-[#1D4ED8]/20 blur-3xl"></div>
            <div class="absolute -left-16 bottom-0 h-72 w-72 rounded-full bg-[#60A5FA]/10 blur-3xl"></div>

            <a href="{{ url('/') }}" class="flex items-center gap-2 relative">
                <x-application-logo class="h-8 w-8 fill-current text-[#60A5FA]" />
                <span class="font-serif font-semibold text-xl">Recrutement</span>
            </a>

            <div class="relative">
                <p class="font-mono text-xs uppercase tracking-widest text-[#60A5FA] mb-4">Plateforme RH multi-entreprises</p>
                <h1 class="font-serif text-3xl font-semibold leading-tight max-w-sm">
                    Le recrutement et la gestion RH de vos entreprises, réunis en un seul endroit.
                </h1>
                <ul class="mt-8 space-y-4">
                    <li class="flex items-start gap-3">
                        <span class="mt-1 h-1.5 w-1.5 rounded-full bg-[#60A5FA] shrink-0"></span>
                        <span class="text-sm text-white/70">Suivi de candidature en temps réel</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1 h-1.5 w-1.5 rounded-full bg-[#60A5FA] shrink-0"></span>
                        <span class="text-sm text-white/70">Congés et pointages simplifiés</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1 h-1.5 w-1.5 rounded-full bg-[#60A5FA] shrink-0"></span>
                        <span class="text-sm text-white/70">Contrats et fiches de paie en un clic</span>
                    </li>
                </ul>
            </div>

            <p class="relative font-mono text-xs text-white/40">Une solution Phylia Technologie</p>
        </div>

        {{-- ==================== RIGHT PANEL — FORM ==================== --}}
        <div class="flex-1 flex flex-col justify-center items-center bg-[#F1F5FB] px-6 py-12">
            {{-- Logo shown only when the brand panel is hidden (mobile) --}}
            <a href="{{ url('/') }}" class="lg:hidden flex items-center gap-2 mb-8">
                <x-application-logo class="h-8 w-8 fill-current text-[#1D4ED8]" />
                <span class="font-serif font-semibold text-xl text-[#13224B]">Recrutement</span>
            </a>

            <div class="w-full sm:max-w-sm bg-white rounded-2xl border border-[#DCE6F5] shadow-sm p-8">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>