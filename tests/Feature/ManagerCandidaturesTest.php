<?php

namespace Tests\Feature;

use App\Models\Candidature;
use App\Models\Offre;
use App\Models\Personne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagerCandidaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_view_and_manage_candidatures_for_his_offer(): void
    {
        $manager = Personne::create([
            'nom' => 'Manager',
            'prenom' => 'Test',
            'email' => 'manager@example.com',
            'password' => bcrypt('password123'),
            'role' => 'manager',
            'departement' => 'Informatique',
        ]);

        $candidate = Personne::create([
            'nom' => 'Candidate',
            'prenom' => 'Test',
            'email' => 'candidate@example.com',
            'password' => bcrypt('password123'),
            'role' => 'candidat',
            'departement' => null,
        ]);

        $offre = Offre::create([
            'intitule' => 'Développeur Laravel',
            'description' => 'Description',
            'salaire' => 3000,
            'statut' => 'ouvert',
            'datePublication' => now(),
            'personne_id' => $manager->id,
            'departement' => 'Informatique',
        ]);

        $candidature = Candidature::create([
            'personne_id' => $candidate->id,
            'offre_id' => $offre->id,
            'statut' => 'en_attente',
            'datePostulation' => now(),
            'telephone' => '12345678',
            'cv' => null,
            'lettre_motivation' => null,
            'experience' => 2,
            'diplome' => 'Licence',
        ]);

        $this->actingAs($manager);

        $response = $this->get(route('offres.candidatures', $offre->id));

        $response->assertOk();
        $response->assertSee('Candidatures');
        $response->assertSee($candidate->nom);
        $response->assertSee('Accepter');
        $response->assertSee('Refuser');
    }

    public function test_accepting_a_candidate_removes_their_other_pending_applications(): void
    {
        $manager = Personne::create([
            'nom' => 'Manager',
            'prenom' => 'Test',
            'email' => 'manager4@example.com',
            'password' => bcrypt('password123'),
            'role' => 'manager',
            'departement' => 'Informatique',
        ]);

        $candidate = Personne::create([
            'nom' => 'Candidate',
            'prenom' => 'Test',
            'email' => 'candidate4@example.com',
            'password' => bcrypt('password123'),
            'role' => 'candidat',
            'departement' => null,
        ]);

        $offreOne = Offre::create([
            'intitule' => 'Développeur Frontend',
            'description' => 'Description',
            'salaire' => 3000,
            'statut' => 'ouvert',
            'datePublication' => now(),
            'personne_id' => $manager->id,
            'departement' => 'Informatique',
        ]);

        $offreTwo = Offre::create([
            'intitule' => 'Développeur Backend',
            'description' => 'Description',
            'salaire' => 3200,
            'statut' => 'ouvert',
            'datePublication' => now(),
            'personne_id' => $manager->id,
            'departement' => 'Informatique',
        ]);

        $candidatureOne = Candidature::create([
            'personne_id' => $candidate->id,
            'offre_id' => $offreOne->id,
            'statut' => 'en_attente',
            'datePostulation' => now(),
            'telephone' => '12345678',
            'cv' => null,
            'lettre_motivation' => null,
            'experience' => 2,
            'diplome' => 'Licence',
        ]);

        Candidature::create([
            'personne_id' => $candidate->id,
            'offre_id' => $offreTwo->id,
            'statut' => 'en_attente',
            'datePostulation' => now(),
            'telephone' => '87654321',
            'cv' => null,
            'lettre_motivation' => null,
            'experience' => 3,
            'diplome' => 'Master',
        ]);

        $this->actingAs($manager);

        $response = $this->post(route('candidatures.decider', $candidatureOne->id), [
            'statut' => 'accepté',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('candidatures', [
            'offre_id' => $offreOne->id,
            'personne_id' => $candidate->id,
            'statut' => 'accepté',
        ]);

        $this->assertDatabaseHas('candidatures', [
            'offre_id' => $offreTwo->id,
            'personne_id' => $candidate->id,
            'statut' => 'refusé',
            'note_refus' => 'Candidature retirée car vous avez été accepté à une autre offre.',
        ]);
    }

    public function test_candidate_cannot_apply_to_other_offres_after_being_accepted(): void
    {
        $manager = Personne::create([
            'nom' => 'Manager',
            'prenom' => 'Test',
            'email' => 'manager5@example.com',
            'password' => bcrypt('password123'),
            'role' => 'manager',
            'departement' => 'Informatique',
        ]);

        $candidate = Personne::create([
            'nom' => 'Candidate',
            'prenom' => 'Test',
            'email' => 'candidate5@example.com',
            'password' => bcrypt('password123'),
            'role' => 'candidat',
            'departement' => null,
        ]);

        $offreOne = Offre::create([
            'intitule' => 'Développeur Frontend',
            'description' => 'Description',
            'salaire' => 3000,
            'statut' => 'ouvert',
            'datePublication' => now(),
            'personne_id' => $manager->id,
            'departement' => 'Informatique',
        ]);

        $offreTwo = Offre::create([
            'intitule' => 'Développeur Backend',
            'description' => 'Description',
            'salaire' => 3200,
            'statut' => 'ouvert',
            'datePublication' => now(),
            'personne_id' => $manager->id,
            'departement' => 'Informatique',
        ]);

        Candidature::create([
            'personne_id' => $candidate->id,
            'offre_id' => $offreOne->id,
            'statut' => 'accepté',
            'datePostulation' => now(),
            'telephone' => '12345678',
            'cv' => null,
            'lettre_motivation' => null,
            'experience' => 2,
            'diplome' => 'Licence',
        ]);

        $this->actingAs($candidate);

        $response = $this->post(route('candidature.store'), [
            'offre_id' => $offreTwo->id,
            'telephone' => '87654321',
            'experience' => 3,
            'diplome' => 'Master',
            'lettre_motivation' => 'Intéressé',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Vous avez déjà été accepté à une offre et ne pouvez plus postuler.');

        $this->assertDatabaseMissing('candidatures', [
            'offre_id' => $offreTwo->id,
            'personne_id' => $candidate->id,
            'statut' => 'en_attente',
        ]);
    }

    public function test_offer_is_closed_when_the_maximum_number_of_candidates_is_reached(): void
    {
        $manager = Personne::create([
            'nom' => 'Manager',
            'prenom' => 'Test',
            'email' => 'manager2@example.com',
            'password' => bcrypt('password123'),
            'role' => 'manager',
            'departement' => 'Informatique',
        ]);

        $candidateOne = Personne::create([
            'nom' => 'Candidate',
            'prenom' => 'One',
            'email' => 'candidate1@example.com',
            'password' => bcrypt('password123'),
            'role' => 'candidat',
            'departement' => null,
        ]);

        $candidateTwo = Personne::create([
            'nom' => 'Candidate',
            'prenom' => 'Two',
            'email' => 'candidate2@example.com',
            'password' => bcrypt('password123'),
            'role' => 'candidat',
            'departement' => null,
        ]);

        $offre = Offre::create([
            'intitule' => 'Développeur Backend',
            'description' => 'Description',
            'salaire' => 3200,
            'statut' => 'ouvert',
            'datePublication' => now(),
            'date_fin' => now()->addDays(10),
            'nombre_candidats_max' => 1,
            'personne_id' => $manager->id,
            'departement' => 'Informatique',
        ]);

        Candidature::create([
            'personne_id' => $candidateOne->id,
            'offre_id' => $offre->id,
            'statut' => 'accepté',
            'datePostulation' => now(),
            'telephone' => '12345678',
            'cv' => null,
            'lettre_motivation' => null,
            'experience' => 2,
            'diplome' => 'Licence',
        ]);

        $this->actingAs($candidateTwo);

        $response = $this->post(route('candidature.store'), [
            'offre_id' => $offre->id,
            'telephone' => '87654321',
            'experience' => 3,
            'diplome' => 'Master',
            'lettre_motivation' => 'Intéressé',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Cette offre n\'est plus disponible pour de nouvelles candidatures.');

        $offre->refresh();
        $this->assertSame('fermé', $offre->statut);
        $this->assertSame(1, $offre->candidatures()->count());
    }
}
