<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidat extends Model
{
    protected $table = 'candidats';

    protected $fillable = [
        'personne_id',
        'statutCandidature',
        'affectation',
        'salaire_propose',
        'type_salaire',
        'responsable_nom',
        'date_affectation',
    ];

    protected $casts = [
        'date_affectation' => 'date',
    ];

    /**
     * true si l'employé est payé au jour réellement travaillé plutôt qu'avec un salaire mensuel fixe.
     */
    public function estPayeAuJournalier(): bool
    {
        return $this->type_salaire === 'journalier';
    }

    public function personne()
    {
        return $this->belongsTo(Personne::class, 'personne_id');
    }

    public function candidatures()
    {
        return $this->hasMany(Candidature::class, 'personne_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'personne_id');
    }
}