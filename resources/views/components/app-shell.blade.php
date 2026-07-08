<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Recrutement') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

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

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="font-sans antialiased bg-[#F1F5FB] text-[#13224B]">
@php
    $user = auth()->user();
    $role = $user?->role ?? 'candidat';
    $estAccepte = false;
    if ($user && $role === 'candidat') {
        $estAccepte = \App\Models\Candidature::where('personne_id', $user->id)
            ->where('statut', 'accepté')
            ->exists();
    }

    $menus = [
        'manager' => [
            ['Dashboard',    'manager.dashboard', 'M3 12l9-9 9 9M4 10v10a1 1 0 001 1h5v-6h4v6h5a1 1 0 001-1V10'],
            ['Créer offre',  'offres.create',     'M12 4v16m8-8H4'],
            ['Candidats',    'candidats.index',   'M17 20h5v-2a3 3 0 00-5.356-1.783M17 20H7m10 0v-2c0-1.656-1.343-3-3-3H10c-1.657 0-3 1.344-3 3v2m10 0H7m10 0a3 3 0 00-3-3H10a3 3 0 00-3 3'],
        ],
        'candidat' => array_values(array_filter([
            ['Dashboard',        'candidat.dashboard', 'M3 12l9-9 9 9M4 10v10a1 1 0 001 1h5v-6h4v6h5a1 1 0 001-1V10'],
            
            $estAccepte ? ['Mes congés', 'conges.index', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'] : null,
            !$estAccepte ? ['Mes candidatures', 'candidature.index',  'M5 13l4 4L19 7'] : null,
        ])),
        'rh' => [
            ['Dashboard',         'rh.dashboard', 'M3 12l9-9 9 9M4 10v10a1 1 0 001 1h5v-6h4v6h5a1 1 0 001-1V10'],
            ['Candidatures à valider', 'rh.candidatures.en-attente', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['Dossiers employés', 'rh.employes',  'M17 20h5v-2a3 3 0 00-5.356-1.783M17 20H7m10 0v-2c0-1.656-1.343-3-3-3H10c-1.657 0-3 1.344-3 3v2m10 0H7m10 0a3 3 0 00-3-3H10a3 3 0 00-3 3'],
            ['Congés',       'conges.manager',    'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['Calendrier',   'rh.calendrier',     'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['Paiement',     'rh.paiement',       'M9 7h6m-6 4h6m-7 8h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14l3-2 3 2z'],

        ],
    ];
    $items = $menus[$role] ?? $menus['candidat'];
    $roleLabel = ['manager' => 'Manager', 'candidat' => 'Candidat', 'rh' => 'RH'][$role] ?? ucfirst($role);
    
    if ($role === 'candidat' && $estAccepte) {
        $roleLabel = 'Employé';
    }

    $notifCount = 0;
    $notifs = collect();

    if ($user && in_array($role, ['candidat', 'rh'])) {
        $notifCount = \App\Models\Notification::where('personne_id', $user->id)->where('lu', false)->count();
        $notifs = \App\Models\Notification::where('personne_id', $user->id)->orderByDesc('created_at')->limit(8)->get();
    }
@endphp

<div class="min-h-screen bg-[#F1F5FB]" x-data="{ mobileMenuOpen: false }">

    <header class="sticky top-0 z-30 bg-[#13224B] text-[#F1F5FB] shadow-md">
        <div class="flex h-16 items-center justify-between px-4 sm:px-6">

            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2">
                    <x-application-logo class="h-8 w-8 fill-current text-[#60A5FA]" />
                    <span class="font-serif text-lg font-semibold tracking-tight">Recrutement</span>
                </div>

                <nav class="hidden lg:flex items-center gap-1">
                    @foreach ($items as [$label, $routeName, $icon])
                        @php $active = request()->routeIs($routeName); @endphp
                        <a href="{{ route($routeName) }}"
                           class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition
                                  {{ $active ? 'bg-[#1B2E63] text-white' : 'text-[#F1F5FB]/70 hover:bg-[#1B2E63]/60 hover:text-white' }}">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}" />
                            </svg>
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="flex items-center gap-2">

                <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-[#F1F5FB]/70 lg:hidden p-2">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                @if(in_array($role, ['candidat', 'rh']))
                    <div x-data="{ openNotif: false }" class="relative">
                        <button @click="openNotif = !openNotif; if (openNotif) { fetch('{{ route('notifications.marquer-lu') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }) }"
                                class="relative text-[#F1F5FB]/70 hover:text-white p-2 rounded-full hover:bg-[#1B2E63]/60">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if($notifCount > 0)
                                <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-[#B4472B] text-[10px] font-mono font-bold text-white">
                                    {{ $notifCount > 9 ? '9+' : $notifCount }}
                                </span>
                            @endif
                        </button>

                        <div x-show="openNotif" x-cloak @click.outside="openNotif = false"
                             x-transition
                             class="absolute right-0 mt-2 w-80 rounded-xl border border-[#DCE6F5] bg-white py-2 shadow-lg max-h-96 overflow-y-auto">
                            <div class="px-4 py-2 border-b border-[#DCE6F5] font-serif font-semibold text-[#13224B] text-sm">
                                Notifications
                            </div>

                            @forelse($notifs as $notif)
                                <div @class([
                                    'px-4 py-3 text-sm border-b border-[#F1F5FB] last:border-0',
                                    'bg-[#EFF6FF]' => !$notif->lu,
                                ])>
                                    <p class="text-[#13224B]">{{ $notif->message }}</p>
                                    @if($notif->fichier)
                                        <a href="{{ Storage::url($notif->fichier) }}" target="_blank"
                                           class="inline-block mt-1 text-xs font-semibold text-[#2563EB] hover:underline">
                                            📄 Télécharger le PDF
                                        </a>
                                    @endif
                                    <p class="text-xs font-mono text-[#64748B] mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                                </div>
                            @empty
                                <div class="px-4 py-6 text-center text-sm font-mono text-[#64748B]">
                                    Aucune notification
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif

                {{-- Dropdown utilisateur --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center gap-2 rounded-full py-1 pl-1 pr-3 hover:bg-[#1B2E63]/60">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#60A5FA] text-sm font-serif font-semibold text-[#13224B]">
                            {{ strtoupper(substr($user?->prenom ?? $user?->nom ?? 'U', 0, 1)) }}
                        </span>
                        <span class="hidden text-left sm:block">
                            <span class="block text-sm font-medium text-[#F1F5FB]">{{ $user?->prenom }} {{ $user?->nom }}</span>
                            <span class="block text-[11px] font-mono uppercase tracking-wide text-[#F1F5FB]/50">{{ $roleLabel }}</span>
                        </span>
                        <svg class="h-4 w-4 text-[#F1F5FB]/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" x-cloak @click.outside="open = false"
                         x-transition
                         class="absolute right-0 mt-2 w-48 rounded-xl border border-[#DCE6F5] bg-white py-1 shadow-lg">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-[#13224B] hover:bg-[#F1F5FB]">Profil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-[#13224B] hover:bg-[#F1F5FB]">
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        <nav x-show="mobileMenuOpen" x-cloak x-transition class="lg:hidden px-4 pb-4 space-y-1 border-t border-white/10 pt-3">
            @foreach ($items as [$label, $routeName, $icon])
                @php $active = request()->routeIs($routeName); @endphp
                <a href="{{ route($routeName) }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition
                          {{ $active ? 'bg-[#1B2E63] text-white' : 'text-[#F1F5FB]/70 hover:bg-[#1B2E63]/60 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}" />
                    </svg>
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </header>

    {{-- PAGE --}}
    <main class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto">
        {{ $slot }}
    </main>
</div>

</body>
</html>