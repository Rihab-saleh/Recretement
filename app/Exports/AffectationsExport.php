<?php

namespace App\Exports;

use App\Models\Candidat;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Export Excel des employés affectés, destiné à être modifié par le RH
 * (département, responsable, salaire) puis ré-importé via AffectationsImport.
 *
 * La colonne "ID" est indispensable : elle sert à retrouver quel employé
 * mettre à jour lors du ré-import. Le RH ne doit pas la modifier ni la supprimer.
 */
class AffectationsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'Affectations';
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nom',
            'Prénom',
            'Email',
            'Département',
            'Responsable',
            'Salaire proposé (DT)',
        ];
    }

    public function collection(): Collection
    {
        $candidats = Candidat::with('personne')
            ->where('statutCandidature', 'affecté')
            ->whereHas('personne')
            ->get()
            ->sortBy(fn ($c) => $c->personne->nom ?? '');

        return $candidats->map(function (Candidat $candidat) {
            $personne = $candidat->personne;

            return [
                $candidat->id,
                $personne->nom ?? '',
                $personne->prenom ?? '',
                $personne->email ?? '',
                $candidat->affectation ?? '',
                $candidat->responsable_nom ?? '',
                $candidat->salaire_propose ?? 0,
            ];
        })->values();
    }
}