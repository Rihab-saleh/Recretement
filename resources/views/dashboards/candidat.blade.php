<x-app-shell>
    <div class="max-w-5xl mx-auto px-6 py-8">

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">
                Bienvenue, {{ Auth::user()->nom }} 
            </h1>
            @if(!$estAccepte)
                <p class="mt-2 text-gray-500">Consultez les offres disponibles et suivez vos candidatures.</p>
            @elseif($estAccepte)
                <p class="mt-2 text-gray-500">Consultez votre tableau de pointage et suivez vos heures de travail ainsi que votre présence.</p>
            @endif

        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-6">
                {{ session('error') }}
            </div>
        @endif

        @if($estAccepte)
            

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
                    <h2 class="text-xl font-semibold text-gray-700">Mes pointages</h2>

                    <div class="flex items-center gap-3 flex-wrap">
                        <form method="POST" action="{{ route('pointages.pointer') }}">
                            @csrf
                            <button type="submit"
                                    @disabled($pointeAujourdhui)
                                    class="px-4 py-2 rounded-lg text-sm font-semibold shadow transition
                                           {{ $pointeAujourdhui
                                                ? 'bg-gray-200 text-gray-500 cursor-not-allowed'
                                                : 'bg-indigo-600 text-white hover:bg-indigo-700' }}">
                                {{ $pointeAujourdhui ? 'Déjà pointé aujourd\'hui' : 'Pointer l\'arrivée' }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('pointages.sortir') }}">
                            @csrf
                            <button type="submit"
                                    @disabled(!$pointeAujourdhui || $sortiAujourdhui)
                                    class="px-4 py-2 rounded-lg text-sm font-semibold shadow transition
                                           {{ (!$pointeAujourdhui || $sortiAujourdhui)
                                                ? 'bg-gray-200 text-gray-500 cursor-not-allowed'
                                                : 'bg-rose-600 text-white hover:bg-rose-700' }}">
                                {{ $sortiAujourdhui ? 'Départ déjà enregistré' : 'Pointer le départ' }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr class="text-left text-gray-500">
                                <th class="py-2 pr-4 font-medium">Date</th>
                                <th class="py-2 pr-4 font-medium">Heure d'entrée</th>
                                <th class="py-2 pr-4 font-medium">Heure de départ</th>
                                <th class="py-2 pr-4 font-medium">Heures travaillées</th>
                                <th class="py-2 pr-4 font-medium">Retard (min)</th>
                                <th class="py-2 pr-4 font-medium">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($pointages as $pointage)
                                <tr>
                                    <td class="py-2 pr-4 text-gray-700">{{ $pointage->date->format('d/m/Y') }}</td>
                                    <td class="py-2 pr-4 text-gray-700">{{ $pointage->heureEntree }}</td>
                                    <td class="py-2 pr-4 text-gray-700">{{ $pointage->heureSortie ?? '—' }}</td>
                                    <td class="py-2 pr-4 text-gray-700">{{ $pointage->nbHeures ?? 0 }} h</td>
                                    <td class="py-2 pr-4 text-gray-700">{{ $pointage->retardMinutes ?? 0 }}</td>
                                    <td class="py-2 pr-4">
                                        <span @class([
                                            'px-2 py-1 rounded-full text-xs font-semibold',
                                            'bg-emerald-100 text-emerald-700' => $pointage->statut === "à l'heure",
                                            'bg-amber-100 text-amber-700' => $pointage->statut === 'en retard',
                                        ])>
                                            {{ ucfirst($pointage->statut) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-gray-400">
                                        Aucun pointage enregistré pour le moment.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @unless($estAccepte)
        <div class="mb-10">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Offres disponibles</h2>

            
                @forelse($offres as $offre)
                    @php $entreprise = $offre->personne?->entreprise; @endphp
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-4">
                        @if($entreprise)
                            <div class="flex items-center justify-between gap-2 mb-3 pb-3 border-b border-gray-100">
                                <div class="flex items-center gap-2 min-w-0">
                                    @if($entreprise->logo)
                                        <img src="{{ Storage::disk('public')->url($entreprise->logo) }}" alt="{{ $entreprise->nom }}"
                                             class="h-7 w-7 rounded-md object-cover shrink-0 border border-gray-200">
                                    @else
                                        <span class="h-7 w-7 rounded-md bg-gray-100 border border-gray-200 shrink-0 flex items-center justify-center text-[11px] font-semibold text-gray-400">
                                            {{ strtoupper(substr($entreprise->nom, 0, 1)) }}
                                        </span>
                                    @endif
                                    <span class="text-sm font-semibold text-gray-600 truncate">{{ $entreprise->nom }}</span>
                                </div>

                                @php $suit = in_array($entreprise->id, $entreprisesSuivies ?? []); @endphp
                                <form method="POST" action="{{ route('entreprises.suivre', $entreprise->id) }}" class="shrink-0">
                                    @csrf
                                    <button type="submit"
                                            title="{{ $suit ? 'Ne plus suivre cette entreprise' : 'Suivre cette entreprise pour être notifié de ses nouvelles offres' }}"
                                            class="flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded-lg transition
                                                   {{ $suit ? 'text-amber-600 bg-amber-50 hover:bg-amber-100' : 'text-gray-400 hover:text-amber-600 hover:bg-amber-50' }}">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="{{ $suit ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 2l2.39 4.84 5.34.78-3.86 3.76.91 5.32L10 14.27l-4.78 2.43.91-5.32L2.27 7.62l5.34-.78L10 2z" />
                                        </svg>
                                        {{ $suit ? 'Suivi' : 'Suivre' }}
                                    </button>
                                </form>
                            </div>
                        @endif
                        <div class="flex justify-between items-start">
                            <div>
                                <a href="{{ route('offres.details', $offre->id) }}" class="hover:underline">
                                    <h3 class="text-lg font-bold text-gray-800">{{ $offre->intitule }}</h3>
                                </a>
                                <p class="text-sm text-gray-500 mt-1">Département : {{ $offre->departement ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-500">Salaire : {{ number_format($offre->salaire, 2) }} DT</p>
                                <p class="text-sm text-gray-600 mt-2">{{ Str::limit($offre->description, 150) }}</p>
                                <a href="{{ route('offres.details', $offre->id) }}" class="inline-block mt-2 text-sm font-semibold text-blue-600 hover:underline">
                                    Voir les détails →
                                </a>
                            </div>
                            <div class="text-right">
                                <span class="inline-block bg-green-100 text-green-700 text-xs px-3 py-1 rounded-full mb-3">
                                    {{ ucfirst($offre->statut) }}
                                </span>
                                <br>
                                <a href="{{ route('candidature.create', $offre->id) }}"
                                    class="inline-block bg-blue-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-700 transition mt-2">
                                    Postuler
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500">Aucune offre disponible pour le moment.</p>
                @endforelse
            @endunless
        </div>

    </div>
</x-app-shell>