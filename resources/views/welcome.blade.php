<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="La plateforme qui centralise le recrutement et la gestion RH de plusieurs entreprises : offres, candidatures, congés, pointages et fiches de paie, réunis en un seul espace.">
    <title>Recrutement — La plateforme RH multi-entreprises</title>

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
<body class="font-sans antialiased bg-[#F1F5FB] text-[#13224B]">

    {{-- ==================== NAVBAR ==================== --}}
    <header class="sticky top-0 z-40 bg-[#F1F5FB]/90 backdrop-blur border-b border-[#DCE6F5]">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex items-center justify-between h-16">
                <a href="{{ url('/') }}" class="flex items-center gap-2 shrink-0">
                    <x-application-logo class="h-7 w-7 fill-current text-[#1D4ED8]" />
                    <span class="font-serif font-semibold text-lg tracking-tight">Recrutement</span>
                </a>

                <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-[#13224B]/70">
                    <a href="{{ route('offres.index') }}" class="hover:text-[#13224B] transition">Offres d'emploi</a>
                    <a href="#comment-ca-marche" class="hover:text-[#13224B] transition">Comment ça marche</a>
                </nav>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                           class="text-sm font-semibold bg-[#13224B] text-white px-4 py-2 rounded-lg hover:bg-[#1B2E63] transition">
                            Tableau de bord
                        </a>
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}"
                               class="text-sm font-medium text-[#13224B]/80 hover:text-[#13224B] px-3 py-2 rounded-lg hover:bg-white transition">
                                Se connecter
                            </a>
                        @endif
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="text-sm font-semibold bg-[#1D4ED8] text-white px-4 py-2 rounded-lg hover:bg-[#1741B8] transition">
                                Créer un compte
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </header>

    {{-- ==================== HERO ==================== --}}
    <section class="max-w-6xl mx-auto px-6 pt-16 pb-20 lg:pt-24 lg:pb-28">
        <div class="grid lg:grid-cols-2 gap-14 items-center">
            <div>
                <p class="font-mono text-xs font-medium tracking-widest uppercase text-[#1D4ED8] mb-4">
                    Plateforme RH multi-entreprises
                </p>
                <h1 class="font-serif text-4xl sm:text-5xl font-semibold leading-[1.1] tracking-tight text-[#13224B]">
                    Le recrutement et la gestion RH de vos entreprises,
                    <span class="text-[#1D4ED8]">réunis en un seul endroit.</span>
                </h1>
                <p class="mt-6 text-lg text-[#13224B]/70 leading-relaxed max-w-xl">
                    De la publication d'une offre d'emploi à la fiche de paie, en passant par le suivi des candidatures,
                    les congés et les pointages : chaque entreprise cliente dispose de son propre espace, avec des rôles
                    adaptés à chaque utilisateur.
                </p>

                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <a href="{{ route('offres.index') }}"
                       class="inline-flex items-center gap-2 text-sm font-semibold bg-[#13224B] text-white px-6 py-3 rounded-lg hover:bg-[#1B2E63] transition">
                        Voir les offres d'emploi
                    </a>
                    @guest
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center gap-2 text-sm font-semibold text-[#1D4ED8] px-6 py-3 rounded-lg border border-[#1D4ED8]/30 hover:bg-white transition">
                            Créer un compte candidat
                        </a>
                    @endguest
                </div>

                <dl class="mt-12 grid grid-cols-3 gap-6 max-w-md border-t border-[#DCE6F5] pt-6">
                    <div>
                        <dt class="font-mono text-[11px] uppercase tracking-wide text-[#13224B]/45">Espaces</dt>
                        <dd class="font-serif text-2xl font-semibold text-[#13224B]">5</dd>
                    </div>
                    <div>
                        <dt class="font-mono text-[11px] uppercase tracking-wide text-[#13224B]/45">Entreprises</dt>
                        <dd class="font-serif text-2xl font-semibold text-[#13224B]">Multi</dd>
                    </div>
                    <div>
                        <dt class="font-mono text-[11px] uppercase tracking-wide text-[#13224B]/45">Du recrutement à la</dt>
                        <dd class="font-serif text-2xl font-semibold text-[#13224B]">Paie</dd>
                    </div>
                </dl>
            </div>

            {{-- Signature element: the candidate-to-payroll pipeline, as a real process --}}
            <div class="bg-[#13224B] rounded-2xl p-8 shadow-xl">
                <p class="font-mono text-[11px] uppercase tracking-widest text-[#60A5FA] mb-6">Le parcours d'une candidature</p>
                <ol class="space-y-0">
                    @php
                        $etapes = [
                            ['num' => '01', 'titre' => 'Le candidat postule', 'texte' => 'Consultation des offres et candidature en ligne.'],
                            ['num' => '02', 'titre' => 'Le RH présélectionne', 'texte' => 'Filtrage des candidatures avant transmission au manager.'],
                            ['num' => '03', 'titre' => 'Le manager décide', 'texte' => 'Acceptation ou refus, notification automatique du candidat.'],
                            ['num' => '04', 'titre' => 'Le RH affecte et suit', 'texte' => 'Contrat, congés, pointages et fiche de paie centralisés.'],
                        ];
                    @endphp
                    @foreach ($etapes as $i => $etape)
                        <li class="flex gap-4 {{ $loop->last ? '' : 'pb-6 mb-6 border-b border-white/10' }}">
                            <span class="font-mono text-sm text-[#60A5FA] shrink-0 pt-0.5">{{ $etape['num'] }}</span>
                            <div>
                                <p class="text-white font-medium">{{ $etape['titre'] }}</p>
                                <p class="text-[#F1F5FB]/60 text-sm mt-1">{{ $etape['texte'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    {{-- ==================== COMMENT CA MARCHE ==================== --}}
    <section id="comment-ca-marche" class="bg-white border-y border-[#DCE6F5]">
        <div class="max-w-6xl mx-auto px-6 py-20">
            <p class="font-mono text-xs font-medium tracking-widest uppercase text-[#1D4ED8] mb-3">Comment ça marche</p>
            <h2 class="font-serif text-3xl font-semibold text-[#13224B] max-w-2xl">
                Une seule plateforme, du premier clic à la première fiche de paie.
            </h2>

            <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
                @php
                    $blocs = [
                        [
                            'titre' => 'Publier une offre',
                            'texte' => "Le manager crée et publie ses offres d'emploi, visibles par tous les candidats de la plateforme.",
                        ],
                        [
                            'titre' => 'Recevoir les candidatures',
                            'texte' => 'Chaque candidature est centralisée, filtrée par le RH puis transmise au manager pour décision.',
                        ],
                        [
                            'titre' => 'Intégrer le candidat',
                            'texte' => 'Une fois accepté, le RH affecte le candidat à un poste et génère automatiquement son contrat.',
                        ],
                        [
                            'titre' => "Suivre l'employé",
                            'texte' => 'Pointages, demandes de congés et fiches de paie sont ensuite gérés depuis le même espace.',
                        ],
                    ];
                @endphp
                @foreach ($blocs as $i => $bloc)
                    <div>
                        <p class="font-mono text-sm text-[#1D4ED8] mb-2">0{{ $i + 1 }}</p>
                        <h3 class="font-serif text-lg font-semibold text-[#13224B]">{{ $bloc['titre'] }}</h3>
                        <p class="mt-2 text-sm text-[#13224B]/65 leading-relaxed">{{ $bloc['texte'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ==================== UN ESPACE PAR ACTEUR ==================== --}}
    <section id="espaces" class="max-w-6xl mx-auto px-6 py-20">
        <p class="font-mono text-xs font-medium tracking-widest uppercase text-[#1D4ED8] mb-3">Cinq espaces dédiés</p>
        <h2 class="font-serif text-3xl font-semibold text-[#13224B] max-w-2xl">
            Un espace pensé pour chaque rôle.
        </h2>

        <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
                $roles = [
                    [
                        'nom' => 'Candidat',
                        'texte' => "Consulte les offres, postule en ligne et suit l'état de sa candidature en temps réel.",
                        'points' => ['Suivi de candidature', 'Congés & pointages une fois recruté'],
                    ],
                    [
                        'nom' => 'Manager',
                        'texte' => "Publie ses offres d'emploi et décide de l'acceptation ou du refus de chaque candidature.",
                        'points' => ['Gestion des offres', 'Décision sur les candidatures'],
                    ],
                    [
                        'nom' => 'Professionnel RH',
                        'texte' => 'Présélectionne les candidatures, gère les affectations, les congés et les fiches de paie.',
                        'points' => ['Contrats & affectations', 'Congés, pointages, paie'],
                    ],
                    [
                        'nom' => 'Administrateur',
                        'texte' => 'Gère les comptes managers et RH de son entreprise, et supervise ses effectifs.',
                        'points' => ['Comptes managers & RH', 'Employés par département'],
                    ],
                    [
                        'nom' => 'Super-Administrateur',
                        'texte' => "Supervise l'ensemble des entreprises clientes de la plateforme et leurs abonnements.",
                        'points' => ['Gestion des entreprises', 'Vue globale de la plateforme'],
                    ],
                ];
            @endphp
            @foreach ($roles as $role)
                <div class="bg-white rounded-xl border border-[#DCE6F5] p-6 hover:shadow-md transition">
                    <h3 class="font-serif text-lg font-semibold text-[#13224B]">{{ $role['nom'] }}</h3>
                    <p class="mt-2 text-sm text-[#13224B]/65 leading-relaxed">{{ $role['texte'] }}</p>
                    <ul class="mt-4 space-y-1.5">
                        @foreach ($role['points'] as $point)
                            <li class="flex items-center gap-2 text-xs font-mono text-[#13224B]/55">
                                <span class="h-1 w-1 rounded-full bg-[#1D4ED8] shrink-0"></span>
                                {{ $point }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

            {{-- CTA card closing the grid --}}
            <div class="rounded-xl border border-dashed border-[#1D4ED8]/40 p-6 flex flex-col justify-center bg-[#EFF6FF]">
                <p class="font-serif text-lg font-semibold text-[#13224B]">Vous cherchez un poste ?</p>
                <p class="mt-2 text-sm text-[#13224B]/65">Créez votre compte candidat et postulez dès aujourd'hui.</p>
                @guest
                    <a href="{{ route('register') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-[#1D4ED8] hover:underline">
                        Créer mon compte →
                    </a>
                @else
                    <a href="{{ route('offres.index') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-[#1D4ED8] hover:underline">
                        Voir les offres →
                    </a>
                @endguest
            </div>
        </div>
    </section>

    {{-- ==================== CTA BAND ==================== --}}
    @guest
    <section class="bg-[#13224B]">
        <div class="max-w-6xl mx-auto px-6 py-16 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div>
                <h2 class="font-serif text-2xl font-semibold text-white">Prêt à centraliser votre recrutement ?</h2>
                <p class="mt-2 text-[#F1F5FB]/60 text-sm">Créez votre compte candidat en quelques secondes.</p>
            </div>
            <a href="{{ route('register') }}"
               class="shrink-0 inline-flex items-center gap-2 text-sm font-semibold bg-[#60A5FA] text-[#13224B] px-6 py-3 rounded-lg hover:bg-[#7DB4FB] transition">
                Créer un compte
            </a>
        </div>
    </section>
    @endguest

    {{-- ==================== FOOTER ==================== --}}
    <footer class="border-t border-[#DCE6F5]">
        <div class="max-w-6xl mx-auto px-6 py-10 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <x-application-logo class="h-5 w-5 fill-current text-[#13224B]/50" />
                <span class="font-serif font-semibold text-sm text-[#13224B]/70">Recrutement</span>
            </div>
            <p class="font-mono text-xs text-[#13224B]/45 text-center">
                Solution développée par Phylia Technologie — &copy; {{ date('Y') }}
            </p>
        </div>
    </footer>

</body>
</html>