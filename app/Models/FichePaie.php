<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FichePaie extends Model
{
    protected $fillable = [
        'salaireBase', 'joursConge', 'salaireNet',
        'fichierPDF', 'personne_id',
        'mois', 'annee',
        'joursOuvres', 'joursTravailles', 'joursAbsence', 'joursRetard', 'totalRetardMinutes',
        'deductionAbsence', 'deductionConge', 'deductionRetard', 'deductionCnss',
        'pourcentageAbsence', 'pourcentageConge', 'pourcentageRetard', 'pourcentageCnss',
    ];

    protected $casts = [
        'salaireBase' => 'float',
        'salaireNet' => 'float',
        'deductionAbsence' => 'float',
        'deductionConge' => 'float',
        'deductionRetard' => 'float',
        'deductionCnss' => 'float',
        'pourcentageAbsence' => 'float',
        'pourcentageConge' => 'float',
        'pourcentageRetard' => 'float',
        'pourcentageCnss' => 'float',
    ];

    public function personne()
    {
        return $this->belongsTo(Personne::class);
    }

    public function getTotalDeductionsAttribute(): float
    {
        return $this->deductionAbsence + $this->deductionConge + $this->deductionRetard + $this->deductionCnss;
    }

    public function getPeriodeAttribute(): string
    {
        if (!$this->mois || !$this->annee) {
            return '';
        }

        return \Illuminate\Support\Carbon::create($this->annee, $this->mois, 1)->translatedFormat('F Y');
    }
}