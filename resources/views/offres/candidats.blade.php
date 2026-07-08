<x-app-shell>
    <div class="max-w-5xl mx-auto px-6 py-8">

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">
                Bienvenue, {{ Auth::user()->nom }} 👋
            </h1>
            <p class="mt-2 text-gray-500">Voici la liste des candidats acceptés sur vos offres.</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-6">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-4">
            <p class="text-sm text-gray-500">{{ $candidatures->count() }} candidat(s) accepté(s)</p>
        </div>

        <div class="space-y-5">
            @forelse($candidatures as $candidature)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 border-l-4 border-l-green-500 p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold shrink-0">
                                {{ strtoupper(substr($candidature->personne?->prenom ?? '?', 0, 1)) }}
                            </div>
                            <h2 class="text-lg font-bold text-gray-800">
                                {{ $candidature->personne?->prenom ?? '' }} {{ $candidature->personne?->nom ?? '' }}
                            </h2>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">● Accepté</span>
                        </div>

                        @if($candidature->cv)
                            <a href="{{ Storage::url($candidature->cv) }}" target="_blank"
                               class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700 transition">
                                📄 Voir le CV
                            </a>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2 text-sm text-gray-600">
                        <p><span class="font-medium text-gray-800">Email :</span> {{ $candidature->personne?->email ?? 'Non renseigné' }}</p>
                        <p><span class="font-medium text-gray-800">Téléphone :</span> {{ $candidature->telephone ?? 'Non renseigné' }}</p>
                        <p><span class="font-medium text-gray-800">Offre :</span> {{ $candidature->offre?->intitule ?? '—' }}</p>
                        <p><span class="font-medium text-gray-800">Département :</span> {{ $candidature->offre?->departement ?? 'N/A' }}</p>
                        <p><span class="font-medium text-gray-800">Diplôme :</span> {{ $candidature->diplome ?? 'Non renseigné' }}</p>
                        <p><span class="font-medium text-gray-800">Expérience :</span> {{ $candidature->experience ?? 0 }} an(s)</p>
                    </div>

                    <details class="mt-4">
                        <summary class="inline-block cursor-pointer text-xs font-medium text-rose-600 hover:text-rose-700 select-none">
                            🚩 Signaler cet employé au RH
                        </summary>
                        <form method="POST" action="{{ route('candidats.signaler', $candidature->id) }}" class="mt-3 flex flex-col gap-2 max-w-lg">
                            @csrf
                            <textarea name="message" rows="3" required maxlength="1000"
                                      placeholder="Décrivez le problème à signaler au RH..."
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-rose-400 focus:border-rose-400"></textarea>
                            <button type="submit"
                                    class="self-start px-4 py-1.5 bg-rose-600 text-white rounded-lg text-xs font-semibold hover:bg-rose-700 transition">
                                Envoyer le signalement
                            </button>
                        </form>
                    </details>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
                    <div class="text-5xl mb-3">🗂️</div>
                    <h3 class="text-xl font-semibold text-gray-700">Aucun candidat accepté pour le moment</h3>
                    <p class="text-gray-500 mt-2">
                        Les candidats que vous acceptez sur vos offres apparaîtront ici.
                    </p>
                </div>
            @endforelse
        </div>

    </div>
</x-app-shell>