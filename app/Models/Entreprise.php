<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entreprise extends Model
{
    protected $fillable = [
        'nom',
        'email',
        'telephone',
        'adresse',
        'ville',
        'pays',
        'logo',
    ];

    public function personnes()
    {
        return $this->hasMany(Personne::class);
    }

    public function admins()
    {
        return $this->hasMany(Personne::class)->where('role', 'admin');
    }

    public function managers()
    {
        return $this->hasMany(Personne::class)->where('role', 'manager');
    }

    public function rh()
    {
        return $this->hasMany(Personne::class)->where('role', 'rh');
    }

    public function abonnements()
    {
        return $this->hasMany(Abonnement::class);
    }

    public function abonnes()
    {
        return $this->belongsToMany(Personne::class, 'abonnements', 'entreprise_id', 'personne_id');
    }
}