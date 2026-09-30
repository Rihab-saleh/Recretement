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

            @include('offres._filtre', ['action' => route('candidat.dashboard'), 'departements' => $departements, 'filtres' => $filtres])

            @forelse($offres as $offre)
                @include('offres._carte', [
                    'offre' => $offre,
                    'entreprisesSuivies' => $entreprisesSuivies,
                ])
            @empty
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-10 text-center text-gray-400">
                    Aucune offre ne correspond à votre recherche pour le moment.
                </div>
            @endforelse
        </div>
    @endunless

    </div>
</x-app-shell>