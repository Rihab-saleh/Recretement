<x-app-shell>
    <div class="max-w-5xl mx-auto px-6 py-8">
 
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Mes candidatures</h1>
            <p class="mt-2 text-gray-500">Suivez l'état de toutes vos candidatures.</p>
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
 
        @forelse($candidatures as $c)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">{{ $c->offre->intitule }}</h3>
                        <p class="text-sm text-gray-500 mt-1">Département : {{ $c->offre->departement ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-500">
                            Postulé le : {{ \Carbon\Carbon::parse($c->datePostulation)->format('d/m/Y') }}
                        </p>
                    </div>
                    <div>
                        @if($c->statut === 'accepté')
                            <span class="bg-green-100 text-green-700 text-sm px-4 py-2 rounded-full font-medium">
                                ✅ Accepté
                            </span>
                        @elseif($c->statut === 'refusé')
                            <span class="bg-red-100 text-red-700 text-sm px-4 py-2 rounded-full font-medium">
                                ❌ Refusé
                            </span>
                        @else
                            <span class="bg-yellow-100 text-yellow-700 text-sm px-4 py-2 rounded-full font-medium">
                                ⏳ En attente
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-gray-500">Vous n'avez pas encore postulé à une offre.</p>
        @endforelse
 
    </div>
</x-app-shell>