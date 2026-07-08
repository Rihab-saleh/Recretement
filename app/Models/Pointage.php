<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pointage extends Model
{
    protected $fillable = [
        'date', 'heureEntree', 'heureSortie', 'retardMinutes',
        'nbHeures', 'statut', 'personne_id'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function personne()
    {
        return $this->belongsTo(Personne::class);
    }
}