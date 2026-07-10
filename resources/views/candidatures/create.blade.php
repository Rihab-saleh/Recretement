<x-app-shell>
    <div class="max-w-3xl mx-auto px-6 py-8">

        <div class="mb-8">
            <a href="{{ route('candidat.dashboard') }}" class="text-blue-600 hover:underline text-sm">
                ← Retour au dashboard
            </a>
            <h1 class="text-3xl font-bold text-gray-800 mt-2">Postuler : {{ $offre->intitule }}</h1>
            <p class="text-sm text-gray-500 mt-1">Département : {{ $offre->departement ?? 'N/A' }} — Salaire : {{ number_format($offre->salaire, 2) }} DT</p>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-8">
            <form method="POST" action="{{ route('candidature.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <input type="hidden" name="offre_id" value="{{ $offre->id }}" />

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                        <input type="text" value="{{ Auth::user()->nom }}" disabled
                            class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm bg-gray-100" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                        <input type="text" value="{{ Auth::user()->prenom }}" disabled
                            class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm bg-gray-100" />
                    </div>
                </div>

                <div>
                    <label for="telephone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                    <input id="telephone" type="text" name="telephone"
                        value="{{ old('telephone') }}" required autocomplete="tel"
                        inputmode="numeric" pattern="[0-9]+" maxlength="20"
                        title="Le numéro de téléphone ne doit contenir que des chiffres."
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @error('telephone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="diplome" class="block text-sm font-medium text-gray-700 mb-1">Diplôme</label>
                    <input id="diplome" type="text" name="diplome"
                        value="{{ old('diplome') }}" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @error('diplome') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="experience" class="block text-sm font-medium text-gray-700 mb-1">Expérience (années)</label>
                    <input id="experience" type="number" name="experience"
                        value="{{ old('experience') }}" min="0" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @error('experience') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="cv" class="block text-sm font-medium text-gray-700 mb-1">CV (PDF uniquement)</label>
                    <input id="cv" type="file" name="cv" accept=".pdf"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @error('cv') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="lettre_motivation" class="block text-sm font-medium text-gray-700 mb-1">Lettre de motivation</label>
                    <textarea id="lettre_motivation" name="lettre_motivation" rows="5"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('lettre_motivation') }}</textarea>
                    @error('lettre_motivation') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex gap-4 pt-2">
                    <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                        Envoyer ma candidature
                    </button>
                    <a href="{{ route('candidat.dashboard') }}"
                        class="border border-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-100 transition text-sm">
                        Annuler
                    </a>
                </div>

            </form>
        </div>
    </div>
</x-app-shell>