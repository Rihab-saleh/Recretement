<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    protected $table = 'managers';

    protected $fillable = [
        'personne_id',
        'departement',
    ];

    public function personne()
    {
        return $this->belongsTo(Personne::class, 'personne_id');
    }

    public function offres()
    {
        return $this->hasMany(Offre::class, 'personne_id');
    }

    public function conges()
    {
        return $this->hasMany(Conge::class, 'personne_id');
    }
}