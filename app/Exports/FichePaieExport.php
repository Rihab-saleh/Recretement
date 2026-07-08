<?php

namespace App\Exports;

use App\Models\FichePaie;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;


class FichePaieExport implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle
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
        return 'Fiches de paie';
    }

    public function headings(): array
    {
        return [
            'Employé',
            'Département',
            'Salaire de base (DT)',
            'Jours ouvrés',
            'Jours travaillés',
            'Jours d\'absence',
            'Jours de congé',
            'Jours de retard',
            'Retard cumulé (min)',
            'Retenue absence (DT)',
            'Retenue congé (DT)',
            'Retenue retard (DT)',
            'Retenue CNSS (DT)',
            '% CNSS appliqué',
            'Total retenues (DT)',
            'Salaire net (DT)',
            'Bulletin généré le',
        ];
    }

    public function collection(): Collection
    {
        $fiches = FichePaie::with(['personne.candidat'])
            ->where('mois', $this->mois)
            ->where('annee', $this->annee)
            ->get()
            ->sortBy(fn ($f) => $f->personne->nom ?? '');

        return $fiches->map(function (FichePaie $fiche) {
            $personne = $fiche->personne;
            $candidat = $personne?->candidat;

            return [
                trim(($personne->prenom ?? '') . ' ' . ($personne->nom ?? '')),
                $candidat->affectation ?? '',
                number_format($fiche->salaireBase, 2, '.', ''),
                $fiche->joursOuvres,
                $fiche->joursTravailles,
                $fiche->joursAbsence,
                $fiche->joursConge,
                $fiche->joursRetard,
                $fiche->totalRetardMinutes,
                number_format($fiche->deductionAbsence, 2, '.', ''),
                number_format($fiche->deductionConge, 2, '.', ''),
                number_format($fiche->deductionRetard, 2, '.', ''),
                number_format($fiche->deductionCnss, 2, '.', ''),
                number_format($fiche->pourcentageCnss, 2, '.', '') . ' %',
                number_format($fiche->totalDeductions, 2, '.', ''),
                number_format($fiche->salaireNet, 2, '.', ''),
                $fiche->updated_at?->format('d/m/Y H:i') ?? '',
            ];
        })->values();
    }
}