<x-app-shell>
    <h1>Offres disponibles</h1>

    @if(session('error'))
        <p style="color:red">{{ session('error') }}</p>
    @endif

    @foreach($offres as $offre)
        <div>
            <h2>{{ $offre->intitule }}</h2>
            <p>{{ $offre->departement }}</p>
            <p>{{ $offre->description }}</p>
            <p>Salaire : {{ $offre->salaire }} DT</p>
            <p>Statut : {{ $offre->statut }}</p>
            <p>Date limite : {{ $offre->date_fin?->format('d/m/Y') ?? 'Non définie' }}</p>

            @if($offre->statut === 'ouvert')
                <a href="{{ route('candidature.create', $offre->id) }}">
                    <button>Postuler</button>
                </a>
            @else
                <button disabled>Offre fermée</button>
            @endif
        </div>
        <hr>
    @endforeach
</x-app-shell>