{{--
    Formulaire de filtre des offres, partagé entre :
    - la page d'accueil publique des offres (offres.index)
    - le dashboard candidat (dashboards.candidat)

    Attend en entrée :
    - $action : route vers laquelle soumettre le filtre (GET)
    - $departements : liste des départements disponibles
    - $filtres : tableau des filtres actuellement appliqués (recherche, departement, salaire_min, tri)
--}}
<form method="GET" action="{{ $action }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="md:col-span-2">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Mot-clé</label>
            <input type="text" name="recherche" value="{{ $filtres['recherche'] ?? '' }}"
                   placeholder="Titre ou description du poste..."
                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Département</label>
            <select name="departement" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">Tous les départements</option>
                @foreach($departements as $dep)
                    <option value="{{ $dep }}" @selected(($filtres['departement'] ?? '') === $dep)>{{ $dep }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Salaire minimum (DT)</label>
            <input type="number" min="0" step="50" name="salaire_min" value="{{ $filtres['salaire_min'] ?? '' }}"
                   placeholder="Ex : 800"
                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
    </div>

    <div class="flex items-center justify-between mt-3 flex-wrap gap-2">
        <div>
            <label class="text-xs font-semibold text-gray-500 mr-2">Trier par</label>
            <select name="tri" onchange="this.form.submit()"
                    class="rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="recent" @selected(($filtres['tri'] ?? 'recent') === 'recent')>Plus récentes</option>
                <option value="salaire_desc" @selected(($filtres['tri'] ?? '') === 'salaire_desc')>Salaire décroissant</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            @if(array_filter($filtres ?? []))
                <a href="{{ $action }}" class="text-sm text-gray-500 hover:text-gray-700 hover:underline">
                    Réinitialiser
                </a>
            @endif
            <button type="submit" class="bg-blue-600 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                Filtrer
            </button>
        </div>
    </div>
</form>