<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 30px 40px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; line-height: 1.5; }

        .titre { text-align: center; margin-bottom: 22px; }
        .titre h1 { font-size: 20px; letter-spacing: 1px; margin: 0; color: #13224B; }
        .titre .sous-titre { font-size: 11px; color: #555; margin-top: 4px; }

        .bloc {
            border: 1px solid #ccc; border-radius: 6px; padding: 12px 16px; margin-bottom: 16px;
        }
        .bloc h2 {
            font-size: 12px; color: #13224B; margin: 0 0 8px 0;
            border-bottom: 1px solid #E5E7EB; padding-bottom: 4px;
        }
        .ligne { margin: 3px 0; }
        .ligne strong { display: inline-block; min-width: 160px; color: #333; }

        .paragraphe { margin: 10px 0; text-align: justify; }

        table.recap { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.recap td { padding: 5px 8px; border: 1px solid #E5E7EB; font-size: 10.5px; }
        table.recap td.label { width: 45%; font-weight: bold; color: #13224B; background: #F5F6FA; }

        .signatures { width: 100%; margin-top: 40px; }
        .signatures td { width: 50%; vertical-align: top; padding-top: 30px; border-top: 1px solid #999; font-size: 10.5px; text-align: center; }

        .footer-doc { margin-top: 26px; font-size: 8.5px; color: #999; text-align: center; }
    </style>
</head>
<body>

    <div class="titre">
        @if($entreprise?->logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($entreprise->logo))
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->path($entreprise->logo) }}" style="height:40px; margin-bottom:8px;">
        @endif
        <h1>CONTRAT DE TRAVAIL</h1>
        <div class="sous-titre">{{ $entreprise->nom ?? config('app.name', 'Entreprise') }}</div>
    </div>

    <div class="bloc">
        <h2>Entre les soussignés</h2>
        <div class="ligne"><strong>L'employeur :</strong> {{ $responsableNom }}</div>
        <div class="ligne"><strong>Représenté par :</strong> {{ $entreprise->nom ?? config('app.name', 'Entreprise') }}</div>
    </div>

    <div class="bloc">
        <h2>Et le salarié</h2>
        <div class="ligne"><strong>Nom et prénom :</strong> {{ $personne->prenom ?? '' }} {{ $personne->nom ?? '' }}</div>
        <div class="ligne"><strong>Email :</strong> {{ $personne->email ?? '—' }}</div>
    </div>

    <div class="paragraphe">
        Il a été convenu et arrêté ce qui suit : le salarié est engagé au sein de l'entreprise
        {{ $entreprise->nom ?? config('app.name', 'Entreprise') }} à compter du <strong>{{ $dateAffectation }}</strong>, aux
        conditions décrites ci-dessous.
    </div>

    <div class="bloc">
        <h2>Conditions du contrat</h2>
        <table class="recap">
            <tr>
                <td class="label">Département / Affectation</td>
                <td>{{ $departement }}</td>
            </tr>
            <tr>
                <td class="label">Date de prise de fonction</td>
                <td>{{ $dateAffectation }}</td>
            </tr>
            
            <tr>
                <td class="label">Salaire proposé</td>
                <td>{{ number_format($salaireOffre, 2) }} DT</td>
            </tr>
            <tr>
                <td class="label">Responsable direct</td>
                <td>{{ $responsableNom }}</td>
            </tr>
        </table>
    </div>

    <div class="paragraphe">
        Ce contrat est régi par les dispositions légales et conventionnelles en vigueur. Le présent document
        vaut confirmation d'affectation et devra être complété, le cas échéant, par un contrat signé selon
        les modalités habituelles de l'entreprise.
    </div>

    <table class="signatures">
        <tr>
            <td>L'employeur<br>{{ $responsableNom }}</td>
            <td>Le salarié<br>{{ $personne->prenom ?? '' }} {{ $personne->nom ?? '' }}</td>
        </tr>
    </table>

    <div class="footer-doc">
        Document généré automatiquement le {{ now()->format('d/m/Y') }} — {{ $entreprise->nom ?? config('app.name', 'Entreprise') }}
    </div>

</body>
</html>