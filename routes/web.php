<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CandidatureController;
use App\Http\Controllers\OffreController;
use App\Http\Controllers\CongeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PointageController;
use App\Http\Controllers\ProfessionnelRHController;
use App\Http\Controllers\FichePaieController;
use App\Http\Controllers\RhCalendrierController;



Route::get('/offres/create', [OffreController::class, 'create'])->name('offres.create');
Route::post('/offres', [OffreController::class, 'store'])->name('offres.store');
Route::delete('/offres/{id}', [OffreController::class, 'destroy'])->name('offres.destroy');
Route::get('/offres/{offre_id}/candidatures', [OffreController::class, 'candidatures'])->name('offres.candidatures');
Route::post('/candidatures/{id}/decider', [OffreController::class, 'deciderCandidat'])->name('candidatures.decider');
Route::delete('/candidatures/{id}', [OffreController::class, 'supprimerCandidature'])->name('candidatures.supprimer');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/postuler/{offre_id}', [CandidatureController::class, 'create'])->name('candidature.create');
Route::post('/postuler', [CandidatureController::class, 'store'])->name('candidature.store');
Route::get('/mes-candidatures', [CandidatureController::class, 'index'])->name('candidature.index');

Route::get('/offres', [OffreController::class, 'index'])->name('offres.index');

Route::get('/conges', [CongeController::class, 'index'])->name('conges.index');
Route::get('/conges/events', [CongeController::class, 'events'])->name('conges.events');
Route::get('/conges/create', [CongeController::class, 'create'])->name('conges.create');
Route::post('/conges', [CongeController::class, 'store'])->name('conges.store');

Route::get('/manager/conges', [CongeController::class, 'manager'])->name('conges.manager');
Route::put('/conges/{id}/decider', [CongeController::class, 'decider'])->name('conges.decider');

Route::post('/notifications/marquer-lu', [NotificationController::class, 'marquerLu'])
    ->name('notifications.marquer-lu');
Route::middleware('auth')->group(function () {
    Route::get('/profile', function () {
        return redirect()->back();
    })->name('profile.edit');
    Route::post('/pointages/pointer', [PointageController::class, 'pointer'])
        ->name('pointages.pointer');
    Route::post('/pointages/sortir', [PointageController::class, 'sortir'])
        ->name('pointages.sortir');
    Route::post('/notifications/marquer-lu', [NotificationController::class, 'marquerLu'])
        ->name('notifications.marquer-lu');
    Route::get('/manager/dashboard', [DashboardController::class, 'manager'])->name('manager.dashboard');
    Route::get('/candidat/dashboard', [DashboardController::class, 'candidat'])->name('candidat.dashboard');
    Route::get('/rh/dashboard', [DashboardController::class, 'rh'])->name('rh.dashboard');

    // Liste des candidats acceptés (manager)
    Route::get('/manager/candidats', [OffreController::class, 'candidatsAcceptes'])->name('candidats.index');
    Route::post('/manager/candidats/{id}/signaler', [OffreController::class, 'signalerCandidat'])->name('candidats.signaler');

    // Contrôle RH des candidatures avant transmission au manager
    Route::get('/rh/candidatures-en-attente', [ProfessionnelRHController::class, 'candidaturesEnAttente'])->name('rh.candidatures.en-attente');
    Route::post('/rh/candidatures/{id}/valider', [ProfessionnelRHController::class, 'validerPourManager'])->name('rh.candidatures.valider');
    Route::post('/rh/candidatures/{id}/rejeter', [ProfessionnelRHController::class, 'rejeterAvantManager'])->name('rh.candidatures.rejeter');

    // Gestion et filtrage des dossiers employés (RH)
    Route::get('/rh/employes', [ProfessionnelRHController::class, 'index'])->name('rh.employes');
    Route::post('/rh/candidatures/{id}/refuser', [ProfessionnelRHController::class, 'refuser'])->name('rh.candidatures.refuser');

    // Affectation administrative (RH)
    Route::post('/rh/candidatures/{id}/affecter', [ProfessionnelRHController::class, 'affecter'])->name('rh.candidatures.affecter');
    Route::delete('/rh/candidatures/{id}/supprimer', [ProfessionnelRHController::class, 'supprimer'])->name('rh.candidatures.supprimer');
    Route::get('/rh/affectations/export', [ProfessionnelRHController::class, 'exporterAffectations'])->name('rh.affectations.export');
    Route::post('/rh/affectations/import', [ProfessionnelRHController::class, 'importerAffectations'])->name('rh.affectations.import');
    Route::post('/rh/affectations/{personneId}/renvoyer-contrat', [ProfessionnelRHController::class, 'renvoyerContrat'])->name('rh.affectations.renvoyer-contrat');
    // Calendrier global des employés (RH) : pointages + congés du mois, et export Excel
    Route::get('/rh/calendrier', [RhCalendrierController::class, 'index'])->name('rh.calendrier');
    Route::get('/rh/calendrier/export', [RhCalendrierController::class, 'export'])->name('rh.calendrier.export');

    // Paiement des salaires (RH)
    Route::get('/rh/paiement/export', [FichePaieController::class, 'exporterExcel'])->name('rh.paiement.export');
    Route::get('/rh/paiement', [FichePaieController::class, 'index'])->name('rh.paiement');
    Route::post('/rh/paiement/{personneId}/generer', [FichePaieController::class, 'generer'])->name('rh.paiement.generer');
    Route::post('/rh/paiement/generer-tout', [FichePaieController::class, 'genererTout'])->name('rh.paiement.generer-tout');
    Route::get('/fiche-paie/{fichePaie}/telecharger', [FichePaieController::class, 'telecharger'])->name('fiche-paie.telecharger');
    Route::resource('personnes', \App\Http\Controllers\PersonneController::class);
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
    Route::get('/admin/departements/{departement}', [DashboardController::class, 'departementEmployes'])->name('admin.departement.employes');
});

require __DIR__ . '/auth.php';