<x-app-shell>
    <div class="max-w-7xl mx-auto px-6 py-8">

        {{-- Header --}}
        <div class="mb-12 bg-slate-900 rounded-2xl p-8 text-white shadow-lg flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white">Dossiers employés</h1>
                <p class="mt-2 text-slate-300">
                    Consultez, filtrez et gérez les dossiers des candidats acceptés.
                </p>
            </div>
            <a href="{{ route('rh.dashboard') }}"
               class="shrink-0 px-4 py-2 bg-blue-800 hover:bg-blue-900 border border-blue-400 rounded-lg text-sm font-medium text-white transition">
                ← Retour au tableau de bord
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-lg bg-green-100 border border-green-300 text-green-700 flex items-center gap-2">
                <span>✅</span> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-lg bg-red-100 border border-red-300 text-red-700 flex items-center gap-2">
                <span>⚠️</span> {{ session('error') }}
            </div>
        @endif

        {{-- Filtres --}}
        <form method="GET" action="{{ route('rh.employes') }}"
              class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center gap-2 mb-4">
                <svg class="h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Filtres</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nom du candidat</label>
                    <input type="text" name="nom" value="{{ request('nom') }}" placeholder="Rechercher un nom..."
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Département</label>
                    <select name="departement" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                        <option value="">Tous</option>
                        @foreach($departements as $dep)
                            <option value="{{ $dep }}" @selected(request('departement') === $dep)>{{ $dep }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Poste</label>
                    <select name="poste" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                        <option value="">Tous</option>
                        @foreach($postes as $poste)
                            <option value="{{ $poste }}" @selected(request('poste') === $poste)>{{ $poste }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
                        Filtrer
                    </button>
                    <a href="{{ route('rh.employes') }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">
                        Réinitialiser
                    </a>
                </div>
            </div>
        </form>

        <div class="mb-4 flex items-center justify-between">
            <p class="text-sm text-gray-500">{{ $candidatures->count() }} dossier(s) trouvé(s)</p>
        </div>

        {{-- Liste --}}
        <div class="space-y-5">
            @forelse($candidatures as $candidature)
                @php
                    $borderColor = $candidature->statut_rh === 'affecte' ? 'border-l-green-500' : 'border-l-yellow-400';
                @endphp
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 {{ $borderColor }} border-l-4 p-6 hover:shadow-md transition">
                    <div class="flex flex-col lg:flex-row lg:justify-between gap-5">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="flex flex-wrap items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold shrink-0">
                                        {{ strtoupper(substr($candidature->personne?->prenom ?? '?', 0, 1)) }}
                                    </div>
                                    <h2 class="text-lg font-bold text-gray-800">
                                        {{ $candidature->personne?->prenom ?? '' }} {{ $candidature->personne?->nom ?? '' }}
                                    </h2>

                                    @if($candidature->statut_rh === 'affecte')
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">● Affecté</span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">● En attente d'affectation</span>
                                    @endif

                                    @if(!empty($candidature->pieces_manquantes))
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-700">
                                            ⚠ Dossier incomplet
                                        </span>
                                    @endif
                                </div>

                                <div class="flex flex-col gap-2 shrink-0">
                                    @if($candidature->cv)
                                        <a href="{{ Storage::url($candidature->cv) }}" target="_blank"
                                           class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-center text-xs font-medium hover:bg-blue-700 transition">
                                            📄 Voir le CV
                                        </a>
                                    @else
                                        <span class="px-3 py-1.5 bg-gray-100 text-gray-500 rounded-lg text-center text-xs">
                                            CV non fourni
                                        </span>
                                    @endif

                                    <form method="POST" action="{{ route('rh.candidatures.supprimer', $candidature->id) }}"
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce dossier ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs font-medium hover:bg-red-700 transition">
                                            🗑️ Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2 text-sm text-gray-600">
                                <p><span class="font-medium text-gray-800">Email :</span> {{ $candidature->personne?->email ?? 'Non renseigné' }}</p>
                                <p><span class="font-medium text-gray-800">Téléphone :</span> {{ $candidature->telephone ?? 'Non renseigné' }}</p>
                                <p><span class="font-medium text-gray-800">Offre :</span> {{ $candidature->offre?->intitule ?? '—' }}</p>
                                <p><span class="font-medium text-gray-800">Diplôme :</span> {{ $candidature->diplome ?? 'Non renseigné' }}</p>
                                <p><span class="font-medium text-gray-800">Expérience :</span> {{ $candidature->experience ?? 0 }} an(s)</p>
                            </div>

                            @if(!empty($candidature->pieces_manquantes))
                                <div class="mt-3 bg-orange-50 border border-orange-200 rounded-lg p-3">
                                    <p class="text-xs font-semibold text-orange-700 mb-1">Éléments manquants :</p>
                                    <ul class="list-disc list-inside text-xs text-orange-700 space-y-0.5">
                                        @foreach($candidature->pieces_manquantes as $manquant)
                                            <li>{{ $manquant }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if($candidature->statut_rh === 'affecte' && $candidature->personne?->candidat)
                                @php $c = $candidature->personne->candidat; @endphp
                                <div class="mt-3 bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-800 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1">
                                    <p><span class="font-semibold">Département :</span> {{ $c->affectation }}</p>
                                    <p><span class="font-semibold">Salaire proposé :</span> {{ $c->salaire_propose }} DT</p>
                                    <p><span class="font-semibold">Responsable :</span> {{ $c->responsable_nom }}</p>
                                    <p><span class="font-semibold">Date d'affectation :</span> {{ optional($c->date_affectation)->format('d/m/Y') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
                    <div class="text-5xl mb-3">🗂️</div>
                    <h3 class="text-xl font-semibold text-gray-700">Aucun dossier ne correspond aux filtres</h3>
                    <p class="text-gray-500 mt-2">
                        Les candidats acceptés par les managers apparaîtront ici.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-shell>