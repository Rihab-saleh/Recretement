<x-app-shell>
    <div class="max-w-3xl mx-auto px-6 py-8">

        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-6">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Retour aux offres
        </a>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-6">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-6">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

            @php $entreprise = $offre->personne?->entreprise; @endphp
            @if($entreprise)
                <div class="flex items-center justify-between gap-3 px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <div class="flex items-center gap-3 min-w-0">
                        @if($entreprise->logo)
                            <img src="{{ Storage::disk('public')->url($entreprise->logo) }}" alt="{{ $entreprise->nom }}"
                                 class="h-10 w-10 rounded-lg object-cover shrink-0 border border-gray-200">
                        @else
                            <span class="h-10 w-10 rounded-lg bg-white border border-gray-200 shrink-0 flex items-center justify-center text-sm font-semibold text-gray-400">
                                {{ strtoupper(substr($entreprise->nom, 0, 1)) }}
                            </span>
                        @endif
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-800 truncate">{{ $entreprise->nom }}</p>
                            @if($entreprise->ville || $entreprise->pays)
                                <p class="text-xs text-gray-500 truncate">
                                    {{ collect([$entreprise->ville, $entreprise->pays])->filter()->implode(', ') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    @auth
                        @if(Auth::user()->role === 'candidat')
                            @php $suit = in_array($entreprise->id, \App\Models\Abonnement::where('personne_id', Auth::id())->pluck('entreprise_id')->all()); @endphp
                            <form method="POST" action="{{ route('entreprises.suivre', $entreprise->id) }}" class="shrink-0">
                                @csrf
                                <button type="submit"
                                        title="{{ $suit ? 'Ne plus suivre cette entreprise' : 'Suivre cette entreprise pour être notifié de ses nouvelles offres' }}"
                                        class="flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded-lg transition
                                               {{ $suit ? 'text-amber-600 bg-amber-50 hover:bg-amber-100' : 'text-gray-400 hover:text-amber-600 hover:bg-white' }}">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="{{ $suit ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 2l2.39 4.84 5.34.78-3.86 3.76.91 5.32L10 14.27l-4.78 2.43.91-5.32L2.27 7.62l5.34-.78L10 2z" />
                                    </svg>
                                    {{ $suit ? 'Suivi' : 'Suivre' }}
                                </button>
                            </form>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="p-6">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <h1 class="text-2xl font-bold text-gray-800">{{ $offre->intitule }}</h1>
                    <span @class([
                        'shrink-0 text-xs font-semibold px-3 py-1 rounded-full',
                        'bg-green-100 text-green-700' => $offre->statut === 'ouvert',
                        'bg-gray-200 text-gray-600' => $offre->statut !== 'ouvert',
                    ])>
                        {{ ucfirst($offre->statut) }}
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6 text-sm">
                    <div>
                        <p class="text-gray-400 text-xs uppercase tracking-wide mb-1">Département</p>
                        <p class="text-gray-800 font-medium">{{ $offre->departement ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs uppercase tracking-wide mb-1">Salaire</p>
                        <p class="text-gray-800 font-medium">{{ number_format($offre->salaire, 2) }} DT</p>
                    </div>
                    @if($offre->datePublication)
                        <div>
                            <p class="text-gray-400 text-xs uppercase tracking-wide mb-1">Publiée le</p>
                            <p class="text-gray-800 font-medium">{{ $offre->datePublication->format('d/m/Y') }}</p>
                        </div>
                    @endif
                    @if($offre->date_fin)
                        <div>
                            <p class="text-gray-400 text-xs uppercase tracking-wide mb-1">Date limite</p>
                            <p class="text-gray-800 font-medium">{{ $offre->date_fin->format('d/m/Y') }}</p>
                        </div>
                    @endif
                    @if($offre->nombre_candidats_max !== null)
                        <div>
                            <p class="text-gray-400 text-xs uppercase tracking-wide mb-1">Places disponibles</p>
                            <p class="text-gray-800 font-medium">{{ $placesRestantes }} / {{ $offre->nombre_candidats_max }}</p>
                        </div>
                    @endif
                </div>

                <div class="mb-8">
                    <p class="text-gray-400 text-xs uppercase tracking-wide mb-2">Description du poste</p>
                    <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $offre->description }}</p>
                </div>

                @auth
                    @if(Auth::user()->role === 'candidat')
                        @if($dejaPostule)
                            <span class="inline-block bg-gray-100 text-gray-500 text-sm font-semibold px-5 py-2.5 rounded-lg">
                                Vous avez déjà postulé à cette offre
                            </span>
                        @elseif($offre->statut !== 'ouvert' || $offre->estExpiree() || $offre->estSaturee())
                            <span class="inline-block bg-gray-100 text-gray-500 text-sm font-semibold px-5 py-2.5 rounded-lg">
                                Cette offre n'accepte plus de candidatures
                            </span>
                        @else
                            <a href="{{ route('candidature.create', $offre->id) }}"
                               class="inline-block bg-blue-600 text-white text-sm font-semibold px-5 py-2.5 rounded-lg hover:bg-blue-700 transition">
                                Postuler à cette offre
                            </a>
                        @endif
                    @endif
                @else
                    <a href="{{ route('login') }}"
                       class="inline-block bg-blue-600 text-white text-sm font-semibold px-5 py-2.5 rounded-lg hover:bg-blue-700 transition">
                        Se connecter pour postuler
                    </a>
                @endauth
            </div>
        </div>
    </div>
</x-app-shell>