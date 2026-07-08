<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidature extends Model
{
    protected $fillable = [
        'statut',
        'valide_rh',
        'date_validation_rh',
        'datePostulation',
        'personne_id',
        'offre_id',
        'telephone',
        'cv',
        'lettre_motivation',
        'experience',
        'diplome',
        'note_refus',
    ];

    protected $casts = [
        'valide_rh' => 'boolean',
        'date_validation_rh' => 'datetime',
    ];

    public function personne()
    {
        return $this->belongsTo(Personne::class, 'personne_id');
    }

    public function offre()
    {
        return $this->belongsTo(Offre::class);
    }

    /**
     * Retourne la liste des éléments manquants dans le dossier du candidat.
     * Utilisé par le RH pour identifier les dossiers incomplets.
     */
    public function piecesManquantes(): array
    {
        $manquants = [];

        if (empty($this->cv)) {
            $manquants[] = 'CV absent';
        }

        if (empty($this->telephone)) {
            $manquants[] = 'Coordonnées manquantes (téléphone)';
        }

        if (empty($this->lettre_motivation) || empty($this->diplome)) {
            $manquants[] = 'Documents insuffisants';
        }

        return $manquants;
    }

    public function estComplet(): bool
    {
        return empty($this->piecesManquantes());
    }
}