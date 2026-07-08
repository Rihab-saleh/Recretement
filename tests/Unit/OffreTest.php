<?php

namespace Tests\Unit;

use App\Models\Offre;
use PHPUnit\Framework\TestCase;

class OffreTest extends TestCase
{
    public function test_offre_accepts_department_during_mass_assignment(): void
    {
        $offre = new Offre();
        $offre->fill([
            'intitule' => 'Développeur',
            'description' => 'Poste backend',
            'salaire' => 2500,
            'statut' => 'ouvert',
            'datePublication' => now(),
            'personne_id' => 1,
            'departement' => 'Informatique',
        ]);

        $this->assertSame('Informatique', $offre->departement);
    }
}
