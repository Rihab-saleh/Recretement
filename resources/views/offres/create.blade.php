<x-app-shell>
    <div class="max-w-3xl mx-auto px-6 py-8">

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Créer une nouvelle offre</h1>
            <p class="mt-2 text-gray-500">Département : <strong>{{ Auth::user()->departement }}</strong></p>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-8">
            <form method="POST" action="{{ route('offres.store') }}" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Intitulé du poste</label>
                    <input
                        type="text" name="intitule"
                        value="{{ old('intitule') }}" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    >
                    @error('intitule') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea
                        name="description" rows="6" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    >{{ old('description') }}</textarea>
                    @error('description') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Salaire (DT)</label>
                    <input
                        type="number" name="salaire"
                        value="{{ old('salaire') }}" min="0" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    >
                    @error('salaire') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                    <input
                        type="date" name="date_fin"
                        value="{{ old('date_fin') }}" required
                        min="{{ now()->addDay()->format('Y-m-d') }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    >
                    @error('date_fin') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre maximum de candidats</label>
                    <input
                        type="number" name="nombre_candidats_max"
                        value="{{ old('nombre_candidats_max') }}" min="1"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        placeholder="Ex. : 20"
                    >
                    <p class="text-sm text-gray-500 mt-1">Laissez vide pour ne pas limiter le nombre de candidatures.</p>
                    @error('nombre_candidats_max') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                    <select
                        name="statut" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    >
                        <option value="ouvert" {{ old('statut') == 'ouvert' ? 'selected' : '' }}>Ouvert</option>
                        <option value="fermé"  {{ old('statut') == 'fermé'  ? 'selected' : '' }}>Fermé</option>
                    </select>
                    @error('statut') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-4 pt-4">
                    <a href="{{ route('manager.dashboard') }}"
                        class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition"
                    >
                        Annuler
                    </a>
                    <button
                        type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow transition"
                    >
                        Publier l'offre
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-app-shell>