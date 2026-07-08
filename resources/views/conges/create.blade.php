<x-app-shell>
    <div class="max-w-2xl mx-auto px-6 py-8">

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Demander un congé</h1>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-8">
            <form method="POST" action="{{ route('conges.store') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                        <input
                            type="date" name="datDebut"
                            value="{{ old('datDebut', $dateDebut ?? '') }}" required
                            min="{{ now()->format('Y-m-d') }}"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                        >
                        @error('datDebut') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                        <input
                            type="date" name="dateFin"
                            value="{{ old('dateFin', $dateFin ?? '') }}" required
                            min="{{ now()->format('Y-m-d') }}"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                        >
                        @error('dateFin') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Commentaire (optionnel)</label>
                    <textarea
                        name="commentaire" rows="4"
                        placeholder="Précisez le motif de votre demande..."
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                    >{{ old('commentaire') }}</textarea>
                    @error('commentaire') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-4 pt-4">
                    <a href="{{ route('conges.index') }}"
                       class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition">
                        Annuler
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow transition">
                        Envoyer la demande
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-shell>