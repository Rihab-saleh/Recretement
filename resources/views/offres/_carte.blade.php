{{--
    Carte d'affichage d'une offre, réutilisée par :
    - la page d'accueil publique des offres (offres.index)
    - le dashboard candidat (dashboards.candidat)

    Attend en entrée :
    - $offre
    - $dejaPostule (bool, optionnel) : le candidat connecté a déjà postulé à cette offre
    - $estAccepte (bool, optionnel) : le candidat connecté a déjà été accepté ailleurs
    - $entreprisesSuivies (array, optionnel)
--}}
@php
    $entreprise = $offre->personne?->entreprise;
    $dejaPostule = $dejaPostule ?? false;
    $estAccepte = $estAccepte ?? false;
    $suit = isset($entreprise) && in_array($entreprise->id, $entreprisesSuivies ?? []);

    $joursRestants = $offre->date_fin ? now()->startOfDay()->diffInDays($offre->date_fin, false) : null;
@endphp

<div class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:border-blue-100 transition-all duration-200 p-6 mb-5">
    <div class="flex items-start gap-4">

        {{-- Logo / initiale entreprise --}}
        <div class="shrink-0">
            @if($entreprise?->logo)
                <img src="{{ Storage::disk('public')->url($entreprise->logo) }}" alt="{{ $entreprise->nom }}"
                     class="h-14 w-14 rounded-xl object-cover border border-gray-100">
            @else
                <div class="h-14 w-14 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-lg font-bold">
                    {{ strtoupper(substr($entreprise->nom ?? $offre->intitule, 0, 1)) }}
                </div>
            @endif
        </div>

        <div class="min-w-0 flex-1">
            {{-- Ligne entreprise + suivre --}}
            <div class="flex items-center justify-between gap-2">
                <span class="text-sm font-semibold text-gray-500 truncate">
                    {{ $entreprise->nom ?? 'Entreprise' }}
                </span>

                @auth
                    @if(Auth::user()->role === 'candidat' && $entreprise)
                        <form method="POST" action="{{ route('entreprises.suivre', $entreprise->id) }}" class="shrink-0">
                            @csrf
                            <button type="submit"
                                    title="{{ $suit ? 'Ne plus suivre cette entreprise' : 'Suivre cette entreprise' }}"
                                    class="flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded-full transition
                                           {{ $suit ? 'text-amber-600 bg-amber-50' : 'text-gray-400 hover:text-amber-600 hover:bg-amber-50' }}">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="{{ $suit ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 2l2.39 4.84 5.34.78-3.86 3.76.91 5.32L10 14.27l-4.78 2.43.91-5.32L2.27 7.62l5.34-.78L10 2z" />
                                </svg>
                                {{ $suit ? 'Suivi' : 'Suivre' }}
                            </button>
                        </form>
                    @endif
                @endauth
            </div>

            {{-- Titre --}}
            <a href="{{ route('offres.details', $offre->id) }}" class="block mt-0.5">
                <h3 class="text-lg font-bold text-gray-900 group-hover:text-blue-700 transition truncate">
                    {{ $offre->intitule }}
                </h3>
            </a>

            {{-- Badges info --}}
            <div class="flex flex-wrap items-center gap-2 mt-2">
                <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-600 bg-gray-50 border border-gray-200 rounded-full px-2.5 py-1">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.783M17 20H7m10 0v-2c0-1.656-1.343-3-3-3H10c-1.657 0-3 1.344-3 3v2m10 0H7m10 0a3 3 0 00-3-3H10a3 3 0 00-3 3" /></svg>
                    {{ $offre->departement ?? 'N/A' }}
                </span>
                <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-full px-2.5 py-1">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 8v2m0-10a9 9 0 110 18 9 9 0 010-18z" /></svg>
                    {{ number_format($offre->salaire, 0) }} DT
                </span>
                @if($joursRestants !== null)
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-700 bg-amber-50 border border-amber-100 rounded-full px-2.5 py-1">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        {{ $joursRestants > 0 ? $joursRestants.' j restants' : 'Dernier jour' }}
                    </span>
                @endif
            </div>

            {{-- Description --}}
            <p class="text-sm text-gray-500 mt-3 leading-relaxed">{{ Str::limit($offre->description, 160) }}</p>
        </div>

        {{-- Colonne action --}}
        <div class="shrink-0 flex flex-col items-end gap-3 pl-2">
            <a href="{{ route('offres.details', $offre->id) }}"
               class="text-sm font-semibold text-blue-600 hover:text-blue-800 whitespace-nowrap">
                Voir détails →
            </a>

            @guest
                <a href="{{ route('login') }}"
                   title="Connectez-vous en tant que candidat pour postuler à cette offre"
                   class="inline-block bg-gray-900 text-white text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-gray-800 transition whitespace-nowrap">
                    Se connecter pour postuler
                </a>
            @else
                @if(Auth::user()->role !== 'candidat')
                    {{-- Connecté mais pas avec un compte candidat : pas de bouton postuler --}}
                @elseif($dejaPostule)
                    <span class="inline-block bg-gray-100 text-gray-500 text-sm font-semibold px-4 py-2.5 rounded-xl whitespace-nowrap">
                        Déjà postulé
                    </span>
                @elseif($estAccepte)
                    <span class="inline-block bg-gray-100 text-gray-500 text-sm font-semibold px-4 py-2.5 rounded-xl whitespace-nowrap"
                          title="Vous avez déjà été accepté à une offre">
                        Indisponible
                    </span>
                @else
                    <a href="{{ route('candidature.create', $offre->id) }}"
                       class="inline-block bg-blue-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl hover:bg-blue-700 shadow-sm shadow-blue-200 transition whitespace-nowrap">
                        Postuler
                    </a>
                @endif
            @endguest
        </div>
    </div>
</div>