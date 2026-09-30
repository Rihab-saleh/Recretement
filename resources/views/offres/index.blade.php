<x-app-shell>
    <div class="max-w-5xl mx-auto px-6 py-8">

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Offres disponibles</h1>
            <p class="mt-2 text-gray-500">
                Parcourez les offres ouvertes et filtrez-les selon vos critères.
                @guest
                    Connectez-vous en tant que candidat pour pouvoir postuler.
                @endguest
            </p>
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

        @include('offres._filtre', ['action' => route('offres.index'), 'departements' => $departements, 'filtres' => $filtres])

        <div>
            @forelse($offres as $offre)
                @include('offres._carte', [
                    'offre' => $offre,
                    'dejaPostule' => in_array($offre->id, $appliedOfferIds ?? []),
                    'estAccepte' => $estAccepte ?? false,
                ])
            @empty
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-10 text-center text-gray-400">
                    Aucune offre ne correspond à votre recherche pour le moment.
                </div>
            @endforelse
        </div>

    </div>
</x-app-shell>