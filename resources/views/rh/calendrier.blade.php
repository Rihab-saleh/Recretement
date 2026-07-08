<x-app-shell>
    <div class="max-w-7xl mx-auto px-6 py-8">

        <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
            <h1 class="text-3xl font-bold text-gray-800">Calendrier des employés</h1>

            <div class="flex items-center gap-3 flex-wrap">
                <a href="{{ route('rh.calendrier', ['mois' => $moisPrecedent, 'annee' => $anneePrecedente]) }}"
                   class="px-3 py-2 rounded-lg bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 shadow-sm">
                    ← Mois précédent
                </a>
                <span class="text-lg font-semibold text-gray-700 capitalize min-w-[160px] text-center">
                    {{ $titreMois }}
                </span>
                <a href="{{ route('rh.calendrier', ['mois' => $moisSuivant, 'annee' => $anneeSuivante]) }}"
                   class="px-3 py-2 rounded-lg bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 shadow-sm">
                    Mois suivant →
                </a>
                <a href="{{ route('rh.calendrier.export', ['mois' => $mois, 'annee' => $annee]) }}"
                   class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700 shadow transition">
                    ⭳ Exporter en Excel
                </a>
            </div>
        </div>

        {{-- Filtre par statut --}}
        <div class="flex items-center gap-2 mb-4">
            <label for="filtreStatut" class="text-sm font-medium text-gray-600">Filtrer par statut :</label>
            <select id="filtreStatut"
                    class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 bg-white text-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="tous">Tous</option>
                <option value="present">Présent</option>
                <option value="retard">Présent (en retard)</option>
                <option value="conge">Congé</option>
                <option value="absent">Absent</option>
                <option value="repos">Week-end / repos</option>
            </select>
            <span id="filtreInfo" class="text-xs text-gray-400"></span>
        </div>

        {{-- Légende --}}
        <div class="flex items-center gap-4 text-xs font-medium text-gray-500 mb-4 flex-wrap">
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full" style="background:#22C55E"></span> Présent</span>
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full" style="background:#F59E0B"></span> Présent (en retard)</span>
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full" style="background:#3B82F6"></span> Congé</span>
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full" style="background:#EF4444"></span> Absent</span>
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full" style="background:#D1D5DB"></span> Week-end / repos</span>
        </div>

        @if($employes->isEmpty())
            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-8 text-center text-gray-500">
                Aucun employé accepté pour le moment.
            </div>
        @else
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-x-auto">
                <table id="tableCalendrier" class="min-w-full text-xs">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="sticky left-0 bg-white px-4 py-2 text-left font-semibold text-gray-600 whitespace-nowrap">
                                Employé
                            </th>
                            @foreach($jours as $j)
                                <th class="px-1 py-2 text-center font-medium {{ $j['weekend'] ? 'text-gray-300' : 'text-gray-500' }}" title="{{ \Illuminate\Support\Carbon::parse($j['date'])->translatedFormat('l d/m/Y') }}">
                                    <span class="block text-[9px] uppercase tracking-wide">{{ $j['jourAbrege'] }}</span>
                                    <span>{{ $j['numero'] }}</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employes as $employe)
                            <tr class="ligne-employe border-b border-gray-100 hover:bg-gray-50">
                                <td class="sticky left-0 bg-white px-4 py-2 font-medium text-gray-700 whitespace-nowrap">
                                    {{ $employe['nom'] }}
                                    <span class="block text-[10px] font-normal text-gray-400">
                                        @if(!empty($employe['departement'])){{ $employe['departement'] }} · @endif
                                        @if(!empty($employe['dateAffectation']))affecté le {{ $employe['dateAffectation'] }}@endif
                                    </span>
                                </td>
                                @foreach($jours as $j)
                                    @php
                                        $info = $employe['jours'][$j['date']] ?? ['statut' => 'futur'];
                                        $couleur = match($info['statut']) {
                                            'present' => (!empty($info['enRetard']) ? '#F59E0B' : '#22C55E'),
                                            'conge' => '#3B82F6',
                                            'absent' => '#EF4444',
                                            'repos' => '#D1D5DB',
                                            'avant_affectation' => '#F9FAFB',
                                            default => '#F3F4F6',
                                        };
                                        $titre = match($info['statut']) {
                                            'present' => 'Présent'
                                                . (!empty($info['heureEntree']) ? ' — entrée ' . $info['heureEntree'] : '')
                                                . (!empty($info['heureSortie']) ? ' / sortie ' . $info['heureSortie'] : '')
                                                . (!empty($info['nbHeures']) ? ' (' . $info['nbHeures'] . ' h)' : ''),
                                            'conge' => 'Congé',
                                            'absent' => 'Absent',
                                            'repos' => 'Week-end',
                                            'avant_affectation' => 'Pas encore affecté au département',
                                            default => '',
                                        };
                                    @endphp
                                    <td class="px-1 py-2 text-center cellule-jour"
                                        data-statut="{{ $info['statut'] }}"
                                        data-retard="{{ !empty($info['enRetard']) ? '1' : '0' }}">
                                        <span title="{{ $titre }}"
                                              class="inline-block h-4 w-4 rounded-sm"
                                              style="background:{{ $couleur }}"></span>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const select = document.getElementById('filtreStatut');
            const info = document.getElementById('filtreInfo');
            const lignes = document.querySelectorAll('.ligne-employe');

            function celluleCorrespond(cellule, valeur) {
                if (valeur === 'tous') return true;
                if (valeur === 'retard') {
                    return cellule.dataset.statut === 'present' && cellule.dataset.retard === '1';
                }
                return cellule.dataset.statut === valeur;
            }

            function appliquerFiltre() {
                const valeur = select.value;
                let employesVisibles = 0;

                lignes.forEach(function (ligne) {
                    const cellules = ligne.querySelectorAll('.cellule-jour');
                    let auMoinsUneCorrespondance = false;

                    cellules.forEach(function (cellule) {
                        const correspond = celluleCorrespond(cellule, valeur);
                        cellule.style.opacity = (valeur === 'tous' || correspond) ? '1' : '0.12';
                        if (correspond) auMoinsUneCorrespondance = true;
                    });

                    if (valeur === 'tous' || auMoinsUneCorrespondance) {
                        ligne.style.display = '';
                        employesVisibles++;
                    } else {
                        ligne.style.display = 'none';
                    }
                });

                info.textContent = valeur === 'tous'
                    ? ''
                    : employesVisibles + ' employé(s) concerné(s) ce mois-ci';
            }

            select.addEventListener('change', appliquerFiltre);
        });
    </script>
</x-app-shell>