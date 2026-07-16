<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abonnement extends Model
{
    protected $fillable = [
        'personne_id',
        'entreprise_id',
    ];

    public function personne()
    {
        return $this->belongsTo(Personne::class);
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }
}