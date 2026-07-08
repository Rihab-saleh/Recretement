<?php

namespace App\Exports;

use App\Models\Candidat;
use App\Models\Conge;
use App\Models\Personne;
use App\Models\Pointage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;


class PointagesCongesExport implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle
{
    protected int $mois;
    protected int $annee;

    public function __construct(int $mois, int $annee)
    {
        $this->mois = $mois;
        $this->annee = $annee;
    }

    public function title(): string
    {
        return 'Pointages et congés';
    }

    public function headings(): array
    {
        return [
            'Employé',
            'Département',
            'Date',
            'Jour',
            'Statut',
            'Heure entrée',
            'Heure sortie',
            'Heures travaillées',
            'Retard (min)',
        ];
    }

    public function collection(): Collection
    {
        $debutMois = Carbon::create($this->annee, $this->mois, 1)->startOfDay();
        $finMois = $debutMois->copy()->endOfMonth();
        $aujourdhui = now()->endOfDay();
        $derniereDate = $finMois->greaterThan($aujourdhui) ? $aujourdhui->copy()->startOfDay() : $finMois;

        $candidats = Candidat::where('statutCandidature', 'affecté')
            ->whereNotNull('date_affectation')
            ->where('date_affectation', '<=', $finMois->toDateString())
            ->get()
            ->keyBy('personne_id');

        $employesIds = $candidats->keys();

        $employes = Personne::whereIn('id', $employesIds)
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        $pointagesParEmploye = Pointage::whereIn('personne_id', $employesIds)
            ->whereBetween('date', [$debutMois->toDateString(), $finMois->toDateString()])
            ->get()
            ->groupBy('personne_id');

        $congesParEmploye = Conge::whereIn('personne_id', $employesIds)
            ->where('statut', 'accepté')
            ->where('datDebut', '<=', $finMois->toDateString())
            ->where('dateFin', '>=', $debutMois->toDateString())
            ->get()
            ->groupBy('personne_id');

        $lignes = collect();

        foreach ($employes as $employe) {
            $nomComplet = trim($employe->prenom . ' ' . $employe->nom);
            $candidat = $candidats->get($employe->id);
            $dateAffectation = $candidat?->date_affectation?->copy()->startOfDay();
            $departement = $candidat?->affectation ?? '';

            $pointagesEmploye = ($pointagesParEmploye->get($employe->id) ?? collect())
                ->keyBy(fn ($p) => $p->date->toDateString());

            $congesEmploye = $congesParEmploye->get($employe->id) ?? collect();

            // On démarre au mois affiché, ou à la date d'affectation si elle tombe dans ce mois
            $debutAffichage = $dateAffectation && $dateAffectation->greaterThan($debutMois)
                ? $dateAffectation->copy()
                : $debutMois->copy();

            for ($jour = $debutAffichage->copy(); $jour->lessThanOrEqualTo($derniereDate); $jour->addDay()) {
                $dateStr = $jour->toDateString();
                $pointage = $pointagesEmploye->get($dateStr);

                $enConge = $congesEmploye->first(function ($conge) use ($jour) {
                    return $jour->between($conge->datDebut, $conge->dateFin);
                });

                if ($enConge) {
                    $statut = 'Congé';
                } elseif ($pointage) {
                    $statut = 'Présent';
                } elseif ($jour->isWeekend()) {
                    $statut = 'Repos';
                } else {
                    $statut = 'Absent';
                }

                $lignes->push([
                    $nomComplet,
                    $departement,
                    $jour->format('d/m/Y'),
                    $jour->translatedFormat('l'),
                    $statut,
                    $pointage?->heureEntree ?? '',
                    $pointage?->heureSortie ?? '',
                    $pointage?->nbHeures ?? '',
                    $pointage?->retardMinutes ?? '',
                ]);
            }
        }

        return $lignes;
    }
}