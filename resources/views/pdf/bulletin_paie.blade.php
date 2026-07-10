<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 24px 28px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #1A1A1A; }

        /* -------- En-tête -------- */
        table.entete-globale { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.entete-globale td { border: none; vertical-align: top; padding: 0; }

        .bloc-entreprise {
            border: 1.5px solid #9AA5B8; border-radius: 10px; padding: 10px 14px;
            width: 300px;
        }
        .nom-entreprise { font-size: 13px; font-weight: bold; color: #13224B; margin-bottom: 4px; }
        .adresse-entreprise { font-size: 9px; color: #333; margin-bottom: 6px; }
        .info-entreprise { font-size: 8px; color: #555; line-height: 1.5; }
        .info-entreprise strong { color: #13224B; }

        .titre-doc { text-align: right; }
        .titre-doc h1 { font-size: 26px; color: #13224B; margin: 0 0 8px 0; letter-spacing: 0.5px; }
        .titre-doc .ligne-periode { font-size: 10px; margin: 2px 0; }
        .titre-doc .ligne-periode strong { color: #13224B; }

        /* -------- Bloc salarié + infos poste -------- */
        table.bloc-identites { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.bloc-identites td { vertical-align: top; border: none; padding: 0; }
        .colonne-poste { width: 46%; font-size: 9.5px; line-height: 1.65; }
        .colonne-poste strong { display: inline-block; min-width: 128px; color: #13224B; }
        .colonne-salarie { width: 50%; }
        .nom-salarie-titre { font-size: 9px; letter-spacing: 1px; color: #7A8296; margin-bottom: 3px; }
        .nom-salarie { font-size: 13px; font-weight: bold; color: #13224B; margin-bottom: 3px; }
        .adresse-salarie { font-size: 9.5px; color: #333; }

        /* -------- Table Rubriques -------- */
        table.rubriques { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.rubriques thead th {
            background: #E7ECF6; color: #13224B; font-weight: bold;
            padding: 7px 8px; font-size: 9.5px; text-align: left; border-bottom: 1px solid #13224B;
        }
        table.rubriques thead th.montant, table.rubriques td.montant { text-align: right; }
        table.rubriques tbody td { padding: 6px 8px; font-size: 9.5px; border-bottom: 1px solid #EEF0F5; }
        table.rubriques tbody tr.paire td { background: #F6F8FC; }
        table.rubriques tr.section-titre td {
            font-weight: bold; color: #13224B; background: #DCE4F4; padding: 5px 8px;
            font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.3px;
        }
        table.rubriques tr.sous-total td {
            font-weight: bold; background: #EDF1FA; border-top: 1px solid #13224B; border-bottom: 1px solid #13224B;
        }
        table.rubriques tr.total-retenues td {
            font-weight: bold; background: #F5F6FA; border-top: 1.5px solid #13224B;
        }
        .deduction { color: #B4472B; }
        .gain { color: #1E7A46; }

        /* -------- Montant net social / Net à payer -------- */
        table.ligne-resume { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        table.ligne-resume td {
            border: 1px solid #CBD3E3; padding: 6px 10px; font-size: 9.5px;
        }
        table.ligne-resume .libelle { width: 78%; }
        table.ligne-resume .montant { text-align: right; font-weight: bold; }

        table.net-a-payer { width: 100%; border-collapse: collapse; margin: 10px 0 14px 0; }
        table.net-a-payer td {
            border: 1.5px solid #13224B; padding: 9px 12px; font-size: 12px; font-weight: bold;
        }
        table.net-a-payer .libelle { background: #13224B; color: #fff; width: 70%; }
        table.net-a-payer .montant { text-align: right; font-size: 15px; color: #13224B; }

        /* -------- Bas de page -------- */
        table.bas-page { width: 100%; border-collapse: collapse; }
        table.bas-page td { vertical-align: top; border: none; padding: 0; }
        .cadre-bas {
            border: 1.5px solid #9AA5B8; border-radius: 10px; padding: 8px 10px; width: 48%;
        }
        .titre-cadre { font-weight: bold; color: #13224B; margin-bottom: 5px; font-size: 9.5px; }
        table.mini { width: 100%; border-collapse: collapse; }
        table.mini th { background: #EEF1F8; padding: 4px 5px; font-size: 8.5px; text-align: center; }
        table.mini td { padding: 4px 5px; font-size: 9px; text-align: center; border: 1px solid #EEE; }

        .footer-doc {
            margin-top: 14px; font-size: 7.5px; color: #999; text-align: center;
        }
    </style>
</head>
<body>

    {{-- En-tête : entreprise / titre + période --}}
    <table class="entete-globale">
        <tr>
            <td>
                <div class="bloc-entreprise">
                    <div class="nom-entreprise">{{ config('app.name', 'Entreprise') }}</div>
                    <div class="adresse-entreprise">Fiche de paie générée automatiquement</div>
                    <div class="info-entreprise">
                        <strong>Établissement :</strong> {{ $candidat->affectation ?? '—' }}<br>
                    </div>
                </div>
            </td>
            <td class="titre-doc">
                <h1>Fiche de paie</h1>
                <div class="ligne-periode">
                    <strong>Période du</strong> 01/{{ str_pad($mois, 2, '0', STR_PAD_LEFT) }}/{{ $annee }}
                    <strong>au</strong> {{ \Illuminate\Support\Carbon::create($annee, $mois, 1)->endOfMonth()->format('d/m/Y') }}
                </div>
                <div class="ligne-periode">
                    <strong>Généré le :</strong> {{ now()->format('d/m/Y') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Identité salarié / poste --}}
    <table class="bloc-identites">
        <tr>
            <td class="colonne-poste">
                <strong>Matricule</strong> {{ $candidat->personne_id }}<br>
                <strong>Poste / Département</strong> {{ $candidat->affectation ?? '—' }}<br>
                <strong>Responsable</strong> {{ $candidat->responsable_nom ?? '—' }}<br>
                <strong>Affecté le</strong> {{ $candidat->date_affectation ? $candidat->date_affectation->format('d/m/Y') : '—' }}
            </td>
            <td class="colonne-salarie">
                <div class="nom-salarie-titre">VOTRE SALARIÉ</div>
                <div class="nom-salarie">{{ $candidat->personne->prenom ?? '' }} {{ $candidat->personne->nom ?? '' }}</div>
                <div class="adresse-salarie">{{ $candidat->personne->email ?? '' }}</div>
            </td>
        </tr>
    </table>

    {{-- Table unique des rubriques --}}
    <table class="rubriques">
        <thead>
            <tr>
                <th>Rubriques</th>
                <th class="montant">Base</th>
                <th class="montant">Taux</th>
                <th class="montant">Montant</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>SALAIRE DE BASE ({{ $typeSalaire === 'journalier' ? 'taux journalier' : 'mensuel fixe' }})</td>
                <td class="montant">{{ number_format($salaireBase, 2) }} DT</td>
                <td class="montant">—</td>
                <td class="montant">{{ number_format($salaireBase, 2) }} DT</td>
            </tr>

            <tr class="section-titre"><td colspan="4">CNSS</td></tr>
            <tr>
                <td>Cotisation CNSS (salariale)</td>
                <td class="montant">{{ number_format($baseCnss, 2) }} DT</td>
                <td class="montant">{{ number_format($pourcentages['cnss'], 2) }} %</td>
                <td class="montant deduction">- {{ number_format($deductionCnss, 2) }} DT</td>
            </tr>
            <tr class="sous-total">
                <td colspan="3">SALAIRE DE BASE NET (après CNSS){{ $typeSalaire === 'journalier' ? ' — par jour' : ' — par mois' }}</td>
                <td class="montant">{{ number_format($salaireBaseNet, 2) }} DT</td>
            </tr>

            <tr class="section-titre"><td colspan="4">GAINS DU MOIS</td></tr>
            <tr>
                <td>Salaire gagné ({{ $joursTravailles }} j travaillés sur {{ $joursOuvres }} ouvrés, net CNSS)</td>
                <td class="montant">{{ $joursTravailles }} j</td>
                <td class="montant">{{ number_format($salaireJournalier, 2) }} DT/j</td>
                <td class="montant gain">{{ number_format($salaireGagne, 2) }} DT</td>
            </tr>

            <tr class="section-titre"><td colspan="4">RETENUES</td></tr>
            <tr>
                <td>Pointage — Absences ({{ $joursAbsence }} j non pointés)</td>
                <td class="montant">{{ $joursAbsence }} j</td>
                <td class="montant">{{ number_format($pourcentages['absence'], 2) }} %</td>
                <td class="montant deduction">- {{ number_format($deductionAbsence, 2) }} DT</td>
            </tr>
            <tr>
                <td>Congés pris — prix des jours ({{ $joursConge }} j × {{ number_format($salaireJournalier, 2) }})</td>
                <td class="montant">{{ $joursConge }} j</td>
                <td class="montant">—</td>
                <td class="montant deduction">- {{ number_format($deductionConge, 2) }} DT</td>
            </tr>
            <tr>
                <td>Retards pointés ({{ $joursRetard }} j / {{ $totalRetardMinutes }} min cumulées)</td>
                <td class="montant">{{ $totalRetardMinutes }} min</td>
                <td class="montant">{{ number_format($pourcentages['retard'], 2) }} %</td>
                <td class="montant deduction">- {{ number_format($deductionRetard, 2) }} DT</td>
            </tr>
            <tr class="total-retenues">
                <td colspan="3">TOTAL DES RETENUES (CNSS incluse)</td>
                <td class="montant deduction">- {{ number_format($totalDeductions, 2) }} DT</td>
            </tr>
        </tbody>
    </table>

    {{-- Montant net social (= salaire gagné, déjà net de CNSS, avant les autres retenues) --}}
    <table class="ligne-resume">
        <tr>
            <td class="libelle">SALAIRE NET (après CNSS, avant absences / congés / retard)</td>
            <td class="montant">{{ number_format($salaireGagne, 2) }} DT</td>
        </tr>
    </table>

    {{-- Net à payer final --}}
    <table class="net-a-payer">
        <tr>
            <td class="libelle">NET À PAYER AU SALARIÉ</td>
            <td class="montant">{{ number_format($salaireNet, 2) }} DT</td>
        </tr>
    </table>

    {{-- Bas de page : congés + récapitulatif pointage --}}
    <table class="bas-page">
        <tr>
            <td class="cadre-bas">
                <div class="titre-cadre">Congés (mois en cours)</div>
                <table class="mini">
                    <thead><tr><th>Jours pris ce mois</th><th>Jours d'absence</th></tr></thead>
                    <tbody><tr><td>{{ $joursConge }}</td><td>{{ $joursAbsence }}</td></tr></tbody>
                </table>
            </td>
            <td style="width: 4%;"></td>
            <td class="cadre-bas">
                <div class="titre-cadre">Récapitulatif pointage</div>
                <table class="mini">
                    <thead>
                        <tr>
                            <th>Ouvrés</th>
                            <th>Travaillés</th>
                            <th>Retard (j / min)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $joursOuvres }}</td>
                            <td>{{ $joursTravailles }}</td>
                            <td>{{ $joursRetard }} / {{ $totalRetardMinutes }}</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <div class="footer-doc">
        Bulletin de paie simplifié — document généré automatiquement le {{ now()->format('d/m/Y') }} — {{ config('app.name', 'Entreprise') }}
    </div>

</body>
</html>