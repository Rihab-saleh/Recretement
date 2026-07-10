<?php

namespace App\Http\Controllers;

use App\Models\Offre;
use App\Models\Candidature;
use App\Models\Candidat;
use App\Models\Personne;
use App\Models\FichePaie;
use App\Models\Pointage;
use App\Models\Notification;
use App\Services\ContratService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function manager()
    {
        $personne = Auth::user();
        $departement = $personne?->departement ?? 'Non défini';

        $offres = Offre::where('personne_id', Auth::id())
            ->get();

        foreach ($offres as $offre) {
            $offre->fermerSiNecessaire();

            
            $offre->candidatures_count = Candidature::where('offre_id', $offre->id)
                ->where('statut', 'en_attente')
                ->where('valide_rh', true)
                ->count();

            $offre->candidatures_acceptees = Candidature::where('offre_id', $offre->id)
                ->where('statut', 'accepté')
                ->count();

            $offre->candidatures_refusees = Candidature::where('offre_id', $offre->id)
                ->where('statut', 'refusé')
                ->count();
        }

        return view('dashboards.manager', compact('offres', 'departement', 'personne'));
    }

    public function candidat()
    {
        $candidatures = Candidature::where('personne_id', Auth::id())
            ->with('offre')
            ->get();

        $appliedOfferIds = $candidatures->pluck('offre_id')->all();
        $estAccepte = $candidatures->where('statut', 'accepté')->count() > 0;

        if ($estAccepte) {
            $offres = collect();
        } else {
            $offres = Offre::where('statut', 'ouvert')
                ->when(!empty($appliedOfferIds), function ($query) use ($appliedOfferIds) {
                    return $query->whereNotIn('id', $appliedOfferIds);
                })
                ->get()
                ->filter(function ($offre) {
                    return ! $offre->estExpiree() && ! $offre->estSaturee();
                });
        }

        $pointages = collect();
        $pointeAujourdhui = false;
        $sortiAujourdhui = false;

        if ($estAccepte) {
            $pointages = Pointage::where('personne_id', Auth::id())
                ->orderByDesc('date')
                ->limit(15)
                ->get();

            $pointageDuJour = Pointage::where('personne_id', Auth::id())
                ->where('date', now()->toDateString())
                ->first();

            $pointeAujourdhui = (bool) $pointageDuJour;
            $sortiAujourdhui = $pointageDuJour ? !empty($pointageDuJour->heureSortie) : false;
        }

        return view('dashboards.candidat', compact(
            'offres', 'candidatures', 'estAccepte', 'pointages', 'pointeAujourdhui', 'sortiAujourdhui'
        ));
    }

    public function rh()
    {
        $candidatsEnAttente = Candidature::with(['personne', 'offre'])
            ->where('statut', 'accepté')
            ->whereDoesntHave('personne.candidat', function ($q) {
                $q->where('statutCandidature', 'affecté');
            })
            ->latest()
            ->get();

        $enAttente = $candidatsEnAttente->count();

        $candidatsAffectes = Candidature::with(['personne.candidat', 'offre'])
            ->where('statut', 'accepté')
            ->whereHas('personne.candidat', function ($q) {
                $q->where('statutCandidature', 'affecté');
            })
            ->latest()
            ->get();

        foreach ($candidatsAffectes as $candidature) {
            $c = $candidature->personne?->candidat;

            if ($c) {
                $fichierContrat = ContratService::dernierContratExistant($c->personne_id);

                // Certains employés ont été affectés avant l'ajout de la génération
                // automatique du contrat (colonne "fichier"/notification). Plutôt que
                // d'exiger un clic sur "Renvoyer le contrat" pour que le bouton "Voir
                // le contrat" apparaisse, on génère ce contrat manquant à la volée.
                if (! $fichierContrat) {
                    $fichierContrat = ContratService::genererEtNotifier(
                        $c->personne,
                        $c,
                        "Voici votre contrat de travail (département : {$c->affectation}, responsable : {$c->responsable_nom}).",
                        'info'
                    );
                }

                $c->contrat_url = Storage::url($fichierContrat);
            }
        }

        $affectes = $candidatsAffectes->count();

        return view('dashboards.rh', compact('enAttente', 'affectes', 'candidatsAffectes', 'candidatsEnAttente'));
    }

    /**
     * Vue d'ensemble globale de l'entreprise, à destination de l'administrateur :
     * effectifs par rôle, activité de recrutement, masse salariale du mois en cours.
     */
    public function admin()
    {
        $effectifs = [
            'managers' => Personne::where('role', 'manager')->count(),
            'rh' => Personne::where('role', 'rh')->count(),
            'candidats' => Personne::where('role', 'candidat')->count(),
            'employes' => Candidat::where('statutCandidature', 'affecté')->count(),
        ];

        $offresOuvertes = Offre::where('statut', 'ouvert')->count();
        $offresFermees = Offre::where('statut', '!=', 'ouvert')->count();
        $candidaturesEnAttente = Candidature::where('statut', 'en_attente')->count();

        $mois = now()->month;
        $annee = now()->year;

        $fichesDuMois = FichePaie::where('mois', $mois)->where('annee', $annee)->get();
        $masseSalariale = $fichesDuMois->sum('salaireNet');
        $bulletinsGeneres = $fichesDuMois->count();

        $derniersEmployesAffectes = Candidat::with('personne')
            ->where('statutCandidature', 'affecté')
            ->latest('date_affectation')
            ->limit(6)
            ->get();

        $departements = Candidat::where('statutCandidature', 'affecté')
            ->whereNotNull('affectation')
            ->selectRaw('affectation, count(*) as total')
            ->groupBy('affectation')
            ->orderByDesc('total')
            ->get();

        $personnes = Personne::orderBy('role')->orderBy('nom')->get();

        return view('dashboards.admin', compact(
            'effectifs',
            'offresOuvertes',
            'offresFermees',
            'candidaturesEnAttente',
            'masseSalariale',
            'bulletinsGeneres',
            'mois',
            'annee',
            'derniersEmployesAffectes',
            'departements',
            'personnes'
        ));
    }

    public function departementEmployes(string $departement)
    {
        $employes = Candidat::with('personne')
            ->where('statutCandidature', 'affecté')
            ->where('affectation', $departement)
            ->get();

        return view('dashboards.admin-departement', compact('departement', 'employes'));
    }
}