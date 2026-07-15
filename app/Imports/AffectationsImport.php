<?php

namespace App\Imports;

use App\Models\Candidat;
use App\Models\Notification;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Lit le fichier Excel exporté par AffectationsExport (éventuellement modifié
 * par le RH) et applique les changements de département / responsable / salaire
 * aux employés correspondants, en se basant sur la colonne "ID".
 *
 * Toute ligne dont l'ID ne correspond à aucun employé affecté est ignorée
 * (elle est comptée dans getLignesIgnorees()).
 */
class AffectationsImport implements ToCollection, WithHeadingRow
{
    private int $misesAJour = 0;
    private array $lignesIgnorees = [];

    public function __construct(private ?int $entrepriseId = null)
    {
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $numero => $row) {
            $id = $row['id'] ?? null;

            if (! $id) {
                continue;
            }

            $candidat = Candidat::whereHas('personne', function ($q) {
                    $q->where('entreprise_id', $this->entrepriseId);
                })
                ->find($id);

            if (! $candidat) {
                $this->lignesIgnorees[] = $numero + 2; // +2 : ligne d'en-tête + index base 0
                continue;
            }

            $departement = trim((string) ($row['departement'] ?? $candidat->affectation));
            $responsable = trim((string) ($row['responsable'] ?? $candidat->responsable_nom));
            $salaire = $row['salaire_propose_dt'] ?? $row['salaire_propose'] ?? $candidat->salaire_propose;

            $ancienDepartement = $candidat->affectation;
            $ancienResponsable = $candidat->responsable_nom;
            $ancienSalaire = $candidat->salaire_propose;

            $candidat->update([
                'affectation' => $departement !== '' ? $departement : $candidat->affectation,
                'responsable_nom' => $responsable !== '' ? $responsable : $candidat->responsable_nom,
                'salaire_propose' => is_numeric($salaire) ? $salaire : $candidat->salaire_propose,
            ]);

            $aChange = $ancienDepartement !== $candidat->affectation
                || $ancienResponsable !== $candidat->responsable_nom
                || (float) $ancienSalaire !== (float) $candidat->salaire_propose;

            if ($aChange) {
                $this->misesAJour++;

                Notification::create([
                    'personne_id' => $candidat->personne_id,
                    'message' => "Vos informations d'affectation ont été mises à jour : département '{$candidat->affectation}', "
                        . "responsable '{$candidat->responsable_nom}', salaire proposé " . number_format($candidat->salaire_propose, 2) . ' DT.',
                    'type' => 'info',
                    'dateEnvoi' => now(),
                    'lu' => false,
                ]);
            }
        }
    }

    public function getMisesAJour(): int
    {
        return $this->misesAJour;
    }

    public function getLignesIgnorees(): array
    {
        return $this->lignesIgnorees;
    }
}