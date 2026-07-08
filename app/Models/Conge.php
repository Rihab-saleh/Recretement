<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conge extends Model
{
    protected $fillable = [
        'datDebut',
        'dateFin',
        'statut',
        'commentaire',
        'motif_refus',
        'personne_id',
    ];

    protected $casts = [
        'datDebut' => 'date',
        'dateFin' => 'date',
    ];

    public function personne()
    {
        return $this->belongsTo(Personne::class);
    }
}