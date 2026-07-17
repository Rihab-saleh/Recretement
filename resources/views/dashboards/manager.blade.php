<x-app-shell>
    <div class="max-w-6xl mx-auto px-6 py-8">

        <!-- Header -->
        <div class="flex justify-between items-center mb-8">

    <div>
        <h1 class="text-3xl font-bold text-gray-800">
            Bienvenue, {{ Auth::user()->nom }} 
        </h1>

        <p class="mt-2 text-gray-500">
            Département :
            <strong>{{ $personne->departement ?? $departement ?? 'Non défini' }}</strong>
        </p>
    </div>

    <a href="{{ route('offres.create') }}"
       class="px-5 py-3 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700">
        Créer une offre
    </a>

</div>
        @if(session('success'))
            <div class="mb-6 p-4 rounded-lg bg-green-100 border border-green-300 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <h2 class="text-2xl font-semibold text-gray-800 mb-6">
            Mes offres
        </h2>

        @forelse($offres as $offre)
            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6 mb-6">

                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">
                            {{ $offre->intitule }}
                        </h3>


                        <p class="text-sm text-gray-500">Département :
                            {{ $offre->personne?->departement ?? $departement ?? 'Non défini' }}</p>


                        <p class="text-gray-600">
                            <span class="font-semibold">Salaire :</span>
                            {{ number_format($offre->salaire, 2) }} DT
                        </p>

                        <div class="mt-3 flex gap-3 flex-wrap">

                            <span class="px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-700">
                                {{ ucfirst($offre->statut) }}
                            </span>

                            <span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-700">
                                {{ $offre->candidatures_count }} candidature(s)
                            </span>

                            <span class="px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-700">
                                {{ $offre->candidatures_acceptees ?? 0 }} acceptée(s)
                            </span>

                            <span class="px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-700">
                                {{ $offre->candidatures_refusees ?? 0 }} refusée(s)
                            </span>

                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">

                    <a href="{{ route('offres.candidatures', $offre->id) }}"
                        class="px-4 py-2 bg-indigo-900 text-white font-semibold rounded-lg shadow-md border border-indigo-900 hover:bg-indigo-800 transition duration-200">
                        Voir les candidatures
                    </a>

                    <form method="POST" action="{{ route('offres.destroy', $offre->id) }}">
                        @csrf
                        @method('DELETE')

                        <button type="submit" onclick="return confirm('Supprimer cette offre ?')"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                            Supprimer
                        </button>
                    </form>

                </div>

            </div>

        @empty
            <div class="bg-white rounded-xl shadow border border-gray-200 p-10 text-center">
                <div class="text-5xl mb-3">📄</div>

                <h3 class="text-xl font-semibold text-gray-700">
                    Aucune offre créée
                </h3>

                <p class="text-gray-500 mt-2">
                    Commencez par publier votre première offre d'emploi.
                </p>

                <a href="{{ route('offres.create') }}"
                    class="inline-block mt-5 px-5 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Créer une offre
                </a>
            </div>
        @endforelse

    </div>
</x-app-shell>