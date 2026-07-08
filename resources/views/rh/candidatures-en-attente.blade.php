<x-app-shell>
    <div class="max-w-6xl mx-auto px-6 py-8">

        <div class="mb-10 bg-slate-900 rounded-2xl p-8 text-white shadow-lg flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white">Candidatures à valider</h1>
                <p class="mt-2 text-slate-300">
                    Contrôlez les nouveaux dossiers avant de les transmettre au manager.
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

        <div class="space-y-4">
            @forelse($candidatures as $candidature)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6" x-data="{ showRefus: false }">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">
                                {{ $candidature->personne?->prenom }} {{ $candidature->personne?->nom }}
                            </h3>
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2 text-sm text-gray-600">
                                <p><span class="font-medium text-gray-800">Email :</span> {{ $candidature->personne?->email ?? 'Non renseigné' }}</p>
                                <p><span class="font-medium text-gray-800">Téléphone :</span> {{ $candidature->telephone ?? 'Non renseigné' }}</p>
                                <p><span class="font-medium text-gray-800">Offre :</span> {{ $candidature->offre?->intitule ?? '—' }}</p>
                                <p><span class="font-medium text-gray-800">Diplôme :</span> {{ $candidature->diplome ?? 'Non renseigné' }}</p>
                                <p><span class="font-medium text-gray-800">Expérience :</span> {{ $candidature->experience ?? 0 }} an(s)</p>
                            </div>
                            <div
                            @if(!empty($candidature->pieces_manquantes))
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach($candidature->pieces_manquantes as $manquant)
                                        <span class="text-xs px-2 py-1 rounded-full bg-amber-100 text-amber-700">
                                            {{ $manquant }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="inline-block mt-3 text-xs px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">
                                    Dossier complet
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
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 shrink-0 w-full lg:w-auto">
                            <form method="POST" action="{{ route('rh.candidatures.valider', $candidature->id) }}">
                                @csrf
                                <button type="submit"
                                        class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                                    Valider et transmettre au manager
                                </button>
                            </form>

                            <button type="button" @click="showRefus = !showRefus"
                                    class="px-4 py-2 border border-red-300 text-red-600 rounded-lg text-sm font-semibold hover:bg-red-50 transition">
                                Rejeter le dossier
                            </button>
                        </div>
                    </div>

                    <div x-show="showRefus" x-cloak x-transition class="mt-4">
                        <form method="POST" action="{{ route('rh.candidatures.rejeter', $candidature->id) }}"
                              class="bg-red-50 border border-red-200 rounded-xl p-4 flex flex-col gap-3">
                            @csrf
                            <label class="text-xs font-medium text-red-700 uppercase tracking-wide">
                                Motif du rejet (optionnel)
                            </label>
                            <textarea name="note_refus" rows="2"
                                      class="w-full border border-red-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 focus:outline-none"
                                      placeholder="Dossier incomplet, non conforme au poste, etc."></textarea>
                            <div class="flex gap-2">
                                <button type="submit"
                                        class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition">
                                    Confirmer le rejet
                                </button>
                                <button type="button" @click="showRefus = false"
                                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-100 transition">
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl border border-dashed border-gray-300 p-10 text-center">
                    <p class="text-gray-400">Aucune candidature en attente de contrôle RH pour le moment.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-shell>