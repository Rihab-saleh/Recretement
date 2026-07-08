<?php

namespace Tests\Feature;

use App\Models\Candidature;
use App\Models\Offre;
use App\Models\Personne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CandidateDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_dashboard_hides_offers_already_applied_to(): void
    {
        $candidate = Personne::create([
            'nom' => 'Test',
            'prenom' => 'Candidate',
            'email' => 'candidate@example.com',
            'password' => bcrypt('password123'),
            'role' => 'candidat',
            'departement' => null,
        ]);

        $appliedOffer = Offre::create([
            'intitule' => 'Offre postulée',
            'description' => 'Description',
            'salaire' => 2000,
            'statut' => 'ouvert',
            'datePublication' => now(),
            'personne_id' => $candidate->id,
            'departement' => 'Informatique',
        ]);

        $otherOffer = Offre::create([
            'intitule' => 'Offre disponible',
            'description' => 'Description',
            'salaire' => 2200,
            'statut' => 'ouvert',
            'datePublication' => now(),
            'personne_id' => $candidate->id,
            'departement' => 'RH',
        ]);

        Candidature::create([
            'personne_id' => $candidate->id,
            'offre_id' => $appliedOffer->id,
            'statut' => 'en_attente',
            'datePostulation' => now(),
            'telephone' => '12345678',
            'cv' => null,
            'lettre_motivation' => null,
            'experience' => 1,
            'diplome' => 'Licence',
        ]);

        $this->actingAs($candidate);

        $response = $this->get(route('candidat.dashboard'));

        $response->assertOk();
        $response->assertDontSee($appliedOffer->intitule);
        $response->assertSee($otherOffer->intitule);
    }
}
