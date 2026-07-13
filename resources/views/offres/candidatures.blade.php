<x-app-shell>
    <div class="min-h-screen bg-gradient-to-br from-slate-100 via-blue-50 to-indigo-100 py-10">
        <div class="max-w-5xl mx-auto px-6">
            <div class="mb-8">
                <h1 class="text-4xl font-extrabold text-gray-800">📋 Candidatures pour : {{ $offre->intitule }}</h1>
                <p class="mt-2 text-gray-500 text-lg">Gérez les personnes qui ont postulé à cette offre.</p>
            </div>

            @if(session('success'))
                <div class="mb-6 rounded-lg bg-green-100 border border-green-300 text-green-700 px-4 py-3">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white/90 backdrop-blur rounded-2xl shadow-2xl border border-gray-200 p-8">
                @forelse($candidatures as $candidature)
                    <div class="border border-gray-200 rounded-xl p-6 mb-4">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800">
                                    {{ $candidature->personne->nom ?? 'Candidat' }}
                                    {{ $candidature->personne->prenom ?? '' }}
                                </h2>
                                <p class="text-sm text-gray-500 mt-1">Email : {{ $candidature->personne->email ?? 'N/A' }}
                                </p>
                                <p class="text-sm text-gray-500">Téléphone : {{ $candidature->telephone ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-500">Diplôme : {{ $candidature->diplome ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-500">Expérience : {{ $candidature->experience ?? 0 }} an(s)</p>
                                <p class="text-sm text-gray-500">Statut : {{ ucfirst($candidature->statut) }}</p>

                                @if($candidature->cv)
                                    <a href="{{ asset('storage/' . $candidature->cv) }}" target="_blank"
                                        class="inline-flex items-center gap-1 mt-2 text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline">
                                        📄 Voir le CV (PDF)
                                    </a>
                                @else
                                    <p class="text-sm text-gray-400 mt-2 italic">Aucun CV fourni</p>
                                @endif
                            </div>

                            <div x-data="{ showRefus: false }" class="flex flex-col items-stretch gap-2 md:w-72">
                                <div class="flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('candidatures.decider', $candidature->id) }}">
                                        @csrf
                                        <input type="hidden" name="statut" value="accepté">
                                        <button type="submit"
                                            class="px-4 py-2 rounded-lg bg-emerald-800 text-white font-semibold shadow-md border border-emerald-900 hover:bg-emerald-900 transition duration-200">
                                            Accepter
                                        </button>
                                    </form>

                                    <button type="button" @click="showRefus = !showRefus"
                                        class="px-4 py-2 rounded-lg bg-red-600 text-white font-semibold shadow-md border border-red-700 hover:bg-red-700 transition duration-200">
                                        Refuser
                                    </button>
                                </div>

                                <div x-show="showRefus" x-cloak x-transition class="mt-2">
                                    <form method="POST" action="{{ route('candidatures.decider', $candidature->id) }}"
                                        class="bg-red-50 border border-red-200 rounded-lg p-3 space-y-2">
                                        @csrf
                                        <input type="hidden" name="statut" value="refusé">
                                        <label class="block text-sm font-medium text-red-700">Motif du refus</label>
                                        <textarea name="note_refus" rows="3" required
                                            placeholder="Expliquez pourquoi cette candidature est refusée..."
                                            class="w-full border border-red-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400"></textarea>
                                        <div class="flex gap-2">
                                            <button type="submit"
                                                class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition">
                                                Confirmer le refus
                                            </button>
                                            <button type="button" @click="showRefus = false"
                                                class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 text-sm hover:bg-gray-100 transition">
                                                Annuler
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                @if($candidature->statut === 'refusé' && $candidature->note_refus)
                                    <p class="text-xs text-red-600 mt-1 italic">
                                        Motif enregistré : {{ $candidature->note_refus }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-500 py-10">
                        <p>Aucune candidature pour cette offre pour le moment.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-shell>