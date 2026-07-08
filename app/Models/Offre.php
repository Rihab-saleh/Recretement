<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offre extends Model
{
    use HasFactory;

    protected $fillable = [
        'intitule',
        'departement',
        'description',
        'salaire',
        'statut',
        'datePublication',
        'date_fin',
        'nombre_candidats_max',
        'personne_id',
    ];

    protected $casts = [
        'datePublication' => 'date',
        'date_fin' => 'date',
    ];

    public function personne()
    {
        return $this->belongsTo(Personne::class);
    }

    public function candidatures()
    {
        return $this->hasMany(Candidature::class);
    }

    public function estExpiree(): bool
    {
        return $this->date_fin !== null
            && now()->startOfDay()->greaterThanOrEqualTo($this->date_fin);
    }

    public function estSaturee(): bool
    {
        if ($this->nombre_candidats_max === null) {
            return false;
        }

        return $this->candidatures()
            ->where('statut', 'accepté')
            ->count() >= $this->nombre_candidats_max;
    }

    public function fermerSiNecessaire(): void
    {
        if ($this->statut !== 'ouvert') {
            return;
        }

        if ($this->estExpiree() || $this->estSaturee()) {
            $this->update(['statut' => 'fermé']);
        }
    }
}