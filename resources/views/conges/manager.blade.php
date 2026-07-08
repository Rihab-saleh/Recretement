<x-app-shell>
    <div class="max-w-5xl mx-auto px-6 py-8">

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Demandes de congé</h1>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-lg bg-green-100 border border-green-300 text-green-700 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-4">
            @forelse($conges as $conge)
                <div x-data="{ showRefus: false }" class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h2 class="font-semibold text-gray-800">
                                {{ $conge->personne->prenom ?? '' }} {{ $conge->personne->nom ?? 'Candidat' }}
                            </h2>
                            <p class="text-sm text-gray-500 mt-1">
                                Du {{ $conge->datDebut->format('d/m/Y') }} au {{ $conge->dateFin->format('d/m/Y') }}
                            </p>
                            @if($conge->commentaire)
                                <p class="text-sm text-gray-500 mt-1">{{ $conge->commentaire }}</p>
                            @endif
                            <span @class([
                                'inline-block mt-2 px-3 py-1 rounded-full text-xs font-semibold',
                                'bg-yellow-100 text-yellow-700' => $conge->statut === 'en attente',
                                'bg-emerald-100 text-emerald-700' => $conge->statut === 'accepté',
                                'bg-red-100 text-red-700' => $conge->statut === 'refusé',
                            ])>
                                {{ ucfirst($conge->statut) }}
                            </span>

                            @if($conge->statut === 'refusé' && $conge->motif_refus)
                                <p class="text-xs text-red-600 mt-2 italic">
                                    Motif enregistré : {{ $conge->motif_refus }}
                                </p>
                            @endif
                        </div>

                        @if($conge->statut === 'en attente')
                            <div class="flex flex-col items-stretch gap-2 md:w-72">
                                <div class="flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('conges.decider', $conge->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="statut" value="accepté">
                                        <button type="submit"
                                                class="px-4 py-2 rounded-lg bg-emerald-700 text-white font-semibold shadow hover:bg-emerald-800 transition">
                                            Accepter
                                        </button>
                                    </form>

                                    <button type="button" @click="showRefus = !showRefus"
                                            class="px-4 py-2 rounded-lg bg-red-600 text-white font-semibold shadow hover:bg-red-700 transition">
                                        Refuser
                                    </button>
                                </div>

                                <div x-show="showRefus" x-cloak x-transition class="mt-2">
                                    <form method="POST" action="{{ route('conges.decider', $conge->id) }}"
                                          class="bg-red-50 border border-red-200 rounded-lg p-3 space-y-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="statut" value="refusé">
                                        <label class="block text-sm font-medium text-red-700">Motif du refus</label>
                                        <textarea name="motif_refus" rows="3" required
                                                  placeholder="Expliquez le motif du refus..."
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
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-500 py-10 bg-white rounded-xl shadow-md border border-gray-200">
                    Aucune demande de congé pour le moment.
                </div>
            @endforelse
        </div>
    </div>
</x-app-shell>