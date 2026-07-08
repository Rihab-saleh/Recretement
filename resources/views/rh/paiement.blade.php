<x-app-shell>
    <div class="max-w-7xl mx-auto px-6 py-8">

        <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
            <h1 class="text-3xl font-bold text-gray-800">Paiement des salaires</h1>

            <div class="flex items-center gap-3">
                <a href="{{ route('rh.paiement', array_merge(['mois' => $moisPrecedent, 'annee' => $anneePrecedente], request()->only('pourcentage_absence', 'pourcentage_retard', 'pourcentage_cnss'))) }}"
                   class="px-3 py-2 rounded-lg bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 shadow-sm">
                    ← Mois précédent
                </a>
                <span class="text-lg font-semibold text-gray-700 capitalize min-w-[160px] text-center">
                    {{ $titreMois }}
                </span>
                <a href="{{ route('rh.paiement', array_merge(['mois' => $moisSuivant, 'annee' => $anneeSuivante], request()->only('pourcentage_absence', 'pourcentage_retard', 'pourcentage_cnss'))) }}"
                   class="px-3 py-2 rounded-lg bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 shadow-sm">
                    Mois suivant →
                </a>
                <a href="{{ route('rh.paiement.export', ['mois' => $mois, 'annee' => $annee]) }}"
                   class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700 shadow transition">
                    ⭳ Exporter en Excel
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-lg bg-green-100 border border-green-300 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- Pourcentages de retenue --}}
        <form method="GET" action="{{ route('rh.paiement') }}" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
            <input type="hidden" name="mois" value="{{ $mois }}">
            <input type="hidden" name="annee" value="{{ $annee }}">

            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Pourcentages de retenue</h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Par jour d'absence (%)</label>
                    <input type="number" name="pourcentage_absence" min="0" max="200" step="1"
                           value="{{ $pourcentages['absence'] }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Sur le temps de retard (%)</label>
                    <input type="number" name="pourcentage_retard" min="0" max="200" step="1"
                           value="{{ $pourcentages['retard'] }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">CNSS - part État (%)</label>
                    <input type="number" name="pourcentage_cnss" min="0" max="100" step="0.01"
                           value="{{ $pourcentages['cnss'] }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
                        Recalculer
                    </button>
                </div>
            </div>
        </form>

        @if($bulletins->isEmpty())
            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-8 text-center text-gray-500">
                Aucun employé affecté pour le moment.
            </div>
        @else
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-x-auto mb-6">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-gray-500">
                            <th class="px-4 py-3 font-medium">Employé</th>
                            <th class="px-4 py-3 font-medium">Salaire de base</th>
                            <th class="px-4 py-3 font-medium">Absences</th>
                            <th class="px-4 py-3 font-medium">Congés</th>
                            <th class="px-4 py-3 font-medium">Retard cumulé</th>
                            <th class="px-4 py-3 font-medium">CNSS</th>
                            <th class="px-4 py-3 font-medium">Retenues</th>
                            <th class="px-4 py-3 font-medium">Salaire net</th>
                            <th class="px-4 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bulletins as $b)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-700 whitespace-nowrap">
                                    {{ $b['candidat']->personne->prenom ?? '' }} {{ $b['candidat']->personne->nom ?? '' }}
                                    <span class="block text-xs font-normal text-gray-400">{{ $b['candidat']->affectation }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ number_format($b['salaireBase'], 2) }} DT
                                    <span class="block text-[10px] uppercase tracking-wide text-gray-400">
                                        {{ $b['typeSalaire'] === 'journalier' ? 'taux / jour' : 'mensuel fixe' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $b['joursAbsence'] }} j</td>
                                <td class="px-4 py-3 text-gray-600">{{ $b['joursConge'] }} j</td>
                                <td class="px-4 py-3 text-gray-600">{{ $b['totalRetardMinutes'] }} min</td>
                                <td class="px-4 py-3 text-rose-600">- {{ number_format($b['deductionCnss'], 2) }} DT</td>
                                <td class="px-4 py-3 text-rose-600 font-medium">- {{ number_format($b['totalDeductions'], 2) }} DT</td>
                                <td class="px-4 py-3 text-emerald-700 font-bold">{{ number_format($b['salaireNet'], 2) }} DT</td>
                                <td class="px-4 py-3">
                                    @php $ficheExistante = $fichesExistantes->get($b['candidat']->personne_id); @endphp
                                    <div class="flex items-center gap-2">
                                        @if($ficheExistante)
                                            <a href="{{ route('fiche-paie.telecharger', $ficheExistante->id) }}"
                                               class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-medium hover:bg-emerald-700 transition whitespace-nowrap">
                                                📄 Télécharger
                                            </a>
                                        @endif
                                        <form method="POST" action="{{ route('rh.paiement.generer', $b['candidat']->personne_id) }}">
                                            @csrf
                                            <input type="hidden" name="mois" value="{{ $mois }}">
                                            <input type="hidden" name="annee" value="{{ $annee }}">
                                            <input type="hidden" name="pourcentage_absence" value="{{ $pourcentages['absence'] }}">
                                            <input type="hidden" name="pourcentage_retard" value="{{ $pourcentages['retard'] }}">
                                            <input type="hidden" name="pourcentage_cnss" value="{{ $pourcentages['cnss'] }}">
                                            <button type="submit" class="px-3 py-1.5 {{ $ficheExistante ? 'bg-gray-100 text-gray-700 hover:bg-gray-200' : 'bg-indigo-600 text-white hover:bg-indigo-700' }} rounded-lg text-xs font-medium transition whitespace-nowrap">
                                                {{ $ficheExistante ? '↻ Régénérer' : '📄 Générer le bulletin' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <form method="POST" action="{{ route('rh.paiement.generer-tout') }}"
                  onsubmit="return confirm('Générer et envoyer le bulletin de paie de tous les employés affichés ?');">
                @csrf
                <input type="hidden" name="mois" value="{{ $mois }}">
                <input type="hidden" name="annee" value="{{ $annee }}">
                <input type="hidden" name="pourcentage_absence" value="{{ $pourcentages['absence'] }}">
                <input type="hidden" name="pourcentage_retard" value="{{ $pourcentages['retard'] }}">
                <input type="hidden" name="pourcentage_cnss" value="{{ $pourcentages['cnss'] }}">
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 transition shadow">
                    📄 Générer et envoyer tous les bulletins de {{ $titreMois }}
                </button>
            </form>
        @endif
    </div>
</x-app-shell>