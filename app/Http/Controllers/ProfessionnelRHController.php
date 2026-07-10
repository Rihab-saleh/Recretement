<?php

namespace App\Http\Controllers;

use App\Models\Candidat;
use App\Models\Candidature;
use App\Models\Notification;
use App\Models\Offre;
use App\Exports\AffectationsExport;
use App\Imports\AffectationsImport;
use App\Services\ContratService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProfessionnelRHController extends Controller
{
   
    public function candidaturesEnAttente(Request $request)
    {
        $candidatures = Candidature::with(['personne', 'offre'])
            ->where('statut', 'en_attente')
            ->where('valide_rh', false)
            ->latest()
            ->get()
            ->map(function ($candidature) {
                $candidature->pieces_manquantes = $candidature->piecesManquantes();

                return $candidature;
            });

        return view('rh.candidatures-en-attente', compact('candidatures'));
    }

   
    public function validerPourManager($candidatureId)
    {
        $candidature = Candidature::findOrFail($candidatureId);

        if ($candidature->statut !== 'en_attente') {
            return redirect()->back()->with('error', 'Seules les candidatures en attente peuvent être validées.');
        }

        $candidature->update([
            'valide_rh' => true,
            'date_validation_rh' => now(),
        ]);

        return redirect()->back()->with('success', 'Candidature validée et transmise au manager.');
    }

    public function rejeterAvantManager(Request $request, $candidatureId)
    {
        $request->validate([
            'note_refus' => 'nullable|string|max:1000',
        ]);

        $candidature = Candidature::with('offre')->findOrFail($candidatureId);

        if ($candidature->statut !== 'en_attente' || $candidature->valide_rh) {
            return redirect()->back()->with('error', 'Cette candidature ne peut plus être rejetée à ce stade.');
        }

        $candidature->update([
            'statut' => 'refusé',
            'note_refus' => $request->note_refus ?: 'Dossier non conforme, rejeté par le service RH.',
        ]);

        Notification::create([
            'personne_id' => $candidature->personne_id,
            'message' => 'Votre candidature pour le poste "' . ($candidature->offre->intitule ?? '')
                . '" a été refusée par le service RH.',
            'type' => 'error',
            'dateEnvoi' => now(),
            'lu' => false,
        ]);

        return redirect()->back()->with('success', 'Candidature rejetée avant transmission au manager.');
    }

    public function index(Request $request)
    {
        $query = Candidature::with(['personne.candidat', 'offre'])
            ->where('statut', 'accepté')
            ->where('valide_rh', true)
            ->whereHas('personne.candidat', function ($q) {
                $q->where('statutCandidature', 'affecté');
            });

        if ($request->filled('departement')) {
            $departement = $request->departement;
            $query->whereHas('offre', function ($q) use ($departement) {
                $q->where('departement', $departement);
            });
        }

        if ($request->filled('poste')) {
            $poste = $request->poste;
            $query->whereHas('offre', function ($q) use ($poste) {
                $q->where('intitule', $poste);
            });
        }

        if ($request->filled('nom')) {
            $recherche = $request->nom;
            $query->whereHas('personne', function ($q) use ($recherche) {
                $q->where('nom', 'like', "%{$recherche}%")
                  ->orWhere('prenom', 'like', "%{$recherche}%")
                  ->orWhereRaw("CONCAT(prenom, ' ', nom) like ?", ["%{$recherche}%"])
                  ->orWhereRaw("CONCAT(nom, ' ', prenom) like ?", ["%{$recherche}%"]);
            });
        }

        $candidatures = $query->latest()->get()->map(function ($candidature) {
            $candidature->statut_rh = $candidature->personne?->candidat?->statutCandidature === 'affecté'
                ? 'affecte'
                : 'en_attente';

            $candidature->pieces_manquantes = $candidature->piecesManquantes();

            return $candidature;
        });

        $departements = Offre::whereNotNull('departement')
            ->distinct()
            ->orderBy('departement')
            ->pluck('departement');

        $postes = Offre::whereNotNull('intitule')
            ->distinct()
            ->orderBy('intitule')
            ->pluck('intitule');

        return view('rh.employes', compact('candidatures', 'departements', 'postes'));
    }

    public function affecter(Request $request, $candidatureId)
    {
        $request->validate([
            'departement' => 'required|string|max:255',
            'salaire_propose' => 'required|numeric|min:0',
            'responsable_nom' => 'required|string|max:255',
        ]);

        $candidature = Candidature::findOrFail($candidatureId);

        if ($candidature->statut !== 'accepté') {
            return redirect()->back()->with('error', 'Seules les candidatures acceptées peuvent être affectées.');
        }

        $candidat = $candidature->personne->candidat;

        if (!$candidat) {
            $candidat = $candidature->personne->candidat()->create([
                'personne_id' => $candidature->personne_id,
                'statutCandidature' => 'affecté',
                'affectation' => $request->departement,
                'salaire_propose' => $request->salaire_propose,
                'responsable_nom' => $request->responsable_nom,
                'date_affectation' => now()->toDateString(),
            ]);
        } else {
            $candidat->update([
                'statutCandidature' => 'affecté',
                'affectation' => $request->departement,
                'salaire_propose' => $request->salaire_propose,
                'responsable_nom' => $request->responsable_nom,
                'date_affectation' => now()->toDateString(),
            ]);
        }

        ContratService::genererEtNotifier(
            $candidature->personne,
            $candidat,
            "Vous avez été affecté(e) au département '{$request->departement}' avec un salaire proposé de {$request->salaire_propose} DT. Responsable: {$request->responsable_nom}. Veuillez trouver ci-joint votre contrat de travail.",
            'success'
        );

        return redirect()->back()->with('success', 'Candidat affecté avec succès, notifié et contrat transmis !');
    }

    
    public function refuser(Request $request, $candidatureId)
    {
        $request->validate([
            'note_refus' => 'nullable|string|max:1000',
        ]);

        $candidature = Candidature::findOrFail($candidatureId);

        // Vérifier que la candidature est acceptée
        if ($candidature->statut !== 'accepté') {
            return redirect()->back()->with('error', 'Seules les candidatures acceptées peuvent être refusées.');
        }

        $candidature->update([
            'statut' => 'refusé',
            'note_refus' => $request->note_refus,
        ]);

        // Créer une notification pour le candidat
        $message = "Votre candidature a été refusée";
        if ($request->note_refus) {
            $message .= ": {$request->note_refus}";
        }

        Notification::create([
            'personne_id' => $candidature->personne_id,
            'message' => $message,
            'type' => 'error',
            'dateEnvoi' => now(),
            'lu' => false,
        ]);

        return redirect()->back()->with('success', 'Candidat refusé et notifié.');
    }

    
    public function supprimer($candidatureId)
    {
        $candidature = Candidature::findOrFail($candidatureId);

       
        if (!in_array($candidature->statut, ['accepté', 'refusé'])) {
            return redirect()->back()->with('error', 'Seuls les dossiers acceptés ou refusés peuvent être supprimés.');
        }

        $candidature->delete();

        return redirect()->back()->with('success', 'Dossier supprimé avec succès.');
    }


    public function exporterAffectations()
    {
        return Excel::download(new AffectationsExport(), 'affectations_employes.xlsx');
    }

    public function importerAffectations(Request $request)
    {
        $request->validate([
            'fichier_affectations' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $import = new AffectationsImport();
        Excel::import($import, $request->file('fichier_affectations'));

        $misesAJour = $import->getMisesAJour();
        $ignorees = $import->getLignesIgnorees();

        $message = $misesAJour . ' employé(s) mis à jour avec succès.';
        if (! empty($ignorees)) {
            $message .= ' Lignes ignorées (ID introuvable) : ' . implode(', ', $ignorees) . '.';
        }

        return redirect()->back()->with('success', $message);
    }

    public function renvoyerContrat($personneId)
    {
        $candidat = Candidat::with('personne')->where('personne_id', $personneId)->firstOrFail();

        ContratService::genererEtNotifier(
            $candidat->personne,
            $candidat,
            "Voici à nouveau votre contrat de travail (département : {$candidat->affectation}, responsable : {$candidat->responsable_nom}).",
            'info'
        );

        return redirect()->back()->with(
            'success',
            'Contrat renvoyé à ' . ($candidat->personne->prenom ?? '') . ' ' . ($candidat->personne->nom ?? '') . '.'
        );
    }
}