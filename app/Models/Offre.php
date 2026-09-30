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

    /**
     * Applique les filtres de recherche communs (mot-clé, département, salaire minimum, tri)
     * utilisés à la fois par la page d'accueil publique des offres et le dashboard candidat.
     */
    public function scopeFiltrer($query, array $filtres)
    {
        return $query
            ->when(!empty($filtres['recherche'] ?? null), function ($q) use ($filtres) {
                $mot = $filtres['recherche'];
                $q->where(function ($qq) use ($mot) {
                    $qq->where('intitule', 'like', "%{$mot}%")
                       ->orWhere('description', 'like', "%{$mot}%");
                });
            })
            ->when(!empty($filtres['departement'] ?? null), function ($q) use ($filtres) {
                $q->where('departement', $filtres['departement']);
            })
            ->when(!empty($filtres['salaire_min'] ?? null), function ($q) use ($filtres) {
                $q->where('salaire', '>=', $filtres['salaire_min']);
            })
            ->when(($filtres['tri'] ?? null) === 'salaire_desc', function ($q) {
                $q->orderByDesc('salaire');
            }, function ($q) {
                $q->orderByDesc('datePublication')->orderByDesc('id');
            });
    }

    /**
     * Liste des départements distincts parmi les offres ouvertes, pour peupler le filtre.
     */
    public static function departementsDisponibles()
    {
        return static::where('statut', 'ouvert')
            ->whereNotNull('departement')
            ->distinct()
            ->orderBy('departement')
            ->pluck('departement');
    }
}