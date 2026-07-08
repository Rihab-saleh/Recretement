<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfessionnelRH extends Model
{
    protected $table = 'professionnel_r_h_s';

    protected $fillable = [
        'personne_id',
    ];

    public function personne()
    {
        return $this->belongsTo(Personne::class, 'personne_id');
    }

    public function pointages()
    {
        return $this->hasMany(Pointage::class, 'personne_id');
    }

    public function fichePaies()
    {
        return $this->hasMany(FichePaie::class, 'personne_id');
    }
}