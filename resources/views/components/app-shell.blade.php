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

    <style>
        [x-cloak] { display: none !important; }

        @media (min-width: 1024px) {
            html.sidebar-collapsed-init .app-sidebar { width: 5rem !important; }
            html.sidebar-collapsed-init .app-main { margin-left: 5rem !important; }
        }
    </style>

    {{-- Applies the saved sidebar state immediately (before Alpine loads) so the
         sidebar never flashes from expanded to collapsed on page load. --}}
    <script>
        (function () {
            try {
                if (localStorage.getItem('sidebarCollapsed') === 'true') {
                    document.documentElement.classList.add('sidebar-collapsed-init');
                }
            } catch (e) {}
        })();
    </script>
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
        'admin' => [
            ['Dashboard', 'admin.dashboard', 'M3 12l9-9 9 9M4 10v10a1 1 0 001 1h5v-6h4v6h5a1 1 0 001-1V10'],
            ['Dossiers employés', 'rh.employes', 'M17 20h5v-2a3 3 0 00-5.356-1.783M17 20H7m10 0v-2c0-1.656-1.343-3-3-3H10c-1.657 0-3 1.344-3 3v2m10 0H7m10 0a3 3 0 00-3-3H10a3 3 0 00-3 3'],
            ['Paiement', 'rh.paiement', 'M9 7h6m-6 4h6m-7 8h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14l3-2 3 2z'],
        ],
        'super_admin' => [
            ['Entreprises', 'super-admin.dashboard', 'M3 21h18M5 21V7l8-4v18M13 21V11l6 4v6M9 9h.01M9 13h.01M9 17h.01'],
        ],
    ];
    $items = $menus[$role] ?? $menus['candidat'];
    $roleLabel = ['manager' => 'Manager', 'candidat' => 'Candidat', 'rh' => 'RH', 'admin' => 'Admin', 'super_admin' => 'Super Admin'][$role] ?? ucfirst($role);
    
    if ($role === 'candidat' && $estAccepte) {
        $roleLabel = 'Employé';
    }

    $notifCount = 0;
    $notifs = collect();

    if ($user && in_array($role, ['candidat', 'rh'])) {
        $notifCount = \App\Models\Notification::where('personne_id', $user->id)->where('lu', false)->count();
        $notifs = \App\Models\Notification::where('personne_id', $user->id)->orderByDesc('created_at')->limit(8)->get();
    }

    $entreprise = $user?->entreprise;
    $brandName = $entreprise?->nom ?: config('app.name', 'Recrutement');
    $brandLogoUrl = $entreprise?->logo ? Storage::disk('public')->url($entreprise->logo) : null;
@endphp

<div class="min-h-screen bg-[#F1F5FB]"
     x-data="{ mobileMenuOpen: false, sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' }"
     x-init="
        $watch('sidebarCollapsed', value => localStorage.setItem('sidebarCollapsed', value));
        document.documentElement.classList.remove('sidebar-collapsed-init');
     ">

    {{-- Sidebar (desktop, fixed — stays in place while the page scrolls) --}}
    <aside :class="sidebarCollapsed ? 'lg:w-20' : 'lg:w-64'"
           class="app-sidebar hidden lg:flex lg:flex-col lg:fixed lg:inset-y-0 lg:left-0 lg:z-40 bg-[#13224B] text-[#F1F5FB] transition-all duration-200 relative">

        {{-- Collapse / expand toggle --}}
        <button @click="sidebarCollapsed = !sidebarCollapsed"
                class="hidden lg:flex absolute -right-3 top-6 h-6 w-6 items-center justify-center rounded-full bg-[#1D4ED8] text-white shadow-md hover:bg-[#1741B8] transition z-50">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <rect x="3" y="5" width="18" height="14" rx="2" stroke-width="2" />
                <line x1="9" y1="5" x2="9" y2="19" stroke-width="2" />
            </svg>
        </button>

        <div class="flex items-center gap-2 h-16 px-6 border-b border-white/10 shrink-0" :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
            @if($brandLogoUrl)
                <img src="{{ $brandLogoUrl }}" alt="{{ $brandName }}" class="h-8 w-8 rounded-md object-cover shrink-0">
            @else
                <x-application-logo class="h-8 w-8 fill-current text-[#60A5FA] shrink-0" />
            @endif
            <span class="font-serif text-lg font-semibold tracking-tight truncate" x-show="!sidebarCollapsed" x-cloak x-transition.opacity>{{ $brandName }}</span>
        </div>

        <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto overflow-x-hidden">
            @foreach ($items as [$label, $routeName, $icon])
                @php $active = request()->routeIs($routeName); @endphp
                <a href="{{ route($routeName) }}"
                   :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                   :title="sidebarCollapsed ? '{{ $label }}' : ''"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                          {{ $active ? 'bg-[#1B2E63] text-white' : 'text-[#F1F5FB]/70 hover:bg-[#1B2E63]/60 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak x-transition.opacity class="whitespace-nowrap">{{ $label }}</span>
                </a>
            @endforeach
        </nav>

        <div class="px-6 py-4 border-t border-white/10 text-[11px] font-mono text-[#F1F5FB]/40 shrink-0 whitespace-nowrap overflow-hidden"
             x-show="!sidebarCollapsed" x-cloak x-transition.opacity>
            &copy; {{ date('Y') }} Recrutement
        </div>
    </aside>


    {{-- Sidebar (mobile, off-canvas) --}}
    <div x-show="mobileMenuOpen" x-cloak class="lg:hidden fixed inset-0 z-40">
        <div class="absolute inset-0 bg-black/40" @click="mobileMenuOpen = false" x-transition.opacity></div>
        <aside class="relative flex flex-col w-64 h-full bg-[#13224B] text-[#F1F5FB] shadow-xl"
               x-show="mobileMenuOpen"
               x-transition:enter="transition ease-out duration-200"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-150"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full">
            <div class="flex items-center justify-between h-16 px-6 border-b border-white/10 shrink-0">
                <div class="flex items-center gap-2 min-w-0">
                    @if($brandLogoUrl)
                        <img src="{{ $brandLogoUrl }}" alt="{{ $brandName }}" class="h-8 w-8 rounded-md object-cover shrink-0">
                    @else
                        <x-application-logo class="h-8 w-8 fill-current text-[#60A5FA] shrink-0" />
                    @endif
                    <span class="font-serif text-lg font-semibold tracking-tight truncate">{{ $brandName }}</span>
                </div>
                <button @click="mobileMenuOpen = false" class="text-[#F1F5FB]/70 p-1">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">
                @foreach ($items as [$label, $routeName, $icon])
                    @php $active = request()->routeIs($routeName); @endphp
                    <a href="{{ route($routeName) }}" @click="mobileMenuOpen = false"
                       class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                              {{ $active ? 'bg-[#1B2E63] text-white' : 'text-[#F1F5FB]/70 hover:bg-[#1B2E63]/60 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}" />
                        </svg>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </aside>
    </div>

    {{-- Right column: top bar + page content --}}
    <div class="app-main min-w-0 flex flex-col transition-all duration-200" :class="sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64'">

        <header class="sticky top-0 z-30 bg-[#13224B] text-[#F1F5FB] shadow-md">
            <div class="flex h-16 items-center justify-between px-4 sm:px-6">

                {{-- Left: mobile menu trigger + compact logo (mobile only) --}}
                <div class="flex items-center gap-2 lg:hidden min-w-0">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-[#F1F5FB]/70 p-2 -ml-2 shrink-0">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    @if($brandLogoUrl)
                        <img src="{{ $brandLogoUrl }}" alt="{{ $brandName }}" class="h-7 w-7 rounded-md object-cover shrink-0">
                    @else
                        <x-application-logo class="h-7 w-7 fill-current text-[#60A5FA] shrink-0" />
                    @endif
                    <span class="font-serif text-base font-semibold tracking-tight truncate">{{ $brandName }}</span>
                </div>

                <div class="hidden lg:block"></div>

                {{-- Right: notifications + user --}}
                <div class="flex items-center gap-2">

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
        </header>

        {{-- PAGE --}}
        <main class="p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
            {{ $slot }}
        </main>
    </div>
</div>

</body>
</html>