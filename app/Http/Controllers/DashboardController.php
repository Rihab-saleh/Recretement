<?php

namespace App\Http\Controllers;

use App\Models\Offre;
use App\Models\Candidature;
use App\Models\Candidat;
use App\Models\Personne;
use App\Models\Entreprise;
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

        $offres = Offre::where('personne_id', Auth::id())->get();

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
        $candidatures = Candidature::where('personne_id', Auth::id())->with('offre')->get();

        $appliedOfferIds = $candidatures->pluck('offre_id')->all();
        $estAccepte = $candidatures->where('statut', 'accepté')->count() > 0;

        if ($estAccepte) {
            $offres = collect();
        } else {
            $offres = Offre::where('statut', 'ouvert')
                ->with('personne.entreprise')
                ->when(!empty($appliedOfferIds), function ($query) use ($appliedOfferIds) {
                    return $query->whereNotIn('id', $appliedOfferIds);
                })
                ->orderByDesc('datePublication')
                ->orderByDesc('id')
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

        $entreprisesSuivies = \App\Models\Abonnement::where('personne_id', Auth::id())->pluck('entreprise_id')->all();

        return view('dashboards.candidat', compact(
            'offres', 'candidatures', 'estAccepte', 'pointages', 'pointeAujourdhui', 'sortiAujourdhui', 'entreprisesSuivies'
        ));
    }

    public function rh()
    {
        $entrepriseId = Auth::user()?->entreprise_id;

        $candidatsEnAttente = Candidature::with(['personne', 'offre'])
            ->where('statut', 'accepté')
            ->whereHas('offre.personne', function ($q) use ($entrepriseId) {
                $q->where('entreprise_id', $entrepriseId);
            })
            ->whereDoesntHave('personne.candidat', function ($q) {
                $q->where('statutCandidature', 'affecté');
            })
            ->latest()
            ->get();

        $enAttente = $candidatsEnAttente->count();

        $candidatsAffectes = Candidature::with(['personne.candidat', 'offre.personne.entreprise'])
            ->where('statut', 'accepté')
            ->whereHas('offre.personne', function ($q) use ($entrepriseId) {
                $q->where('entreprise_id', $entrepriseId);
            })
            ->whereHas('personne.candidat', function ($q) {
                $q->where('statutCandidature', 'affecté');
            })
            ->latest()
            ->get();

        foreach ($candidatsAffectes as $candidature) {
            $c = $candidature->personne?->candidat;

            if ($c) {
                $fichierContrat = ContratService::dernierContratExistant($c->personne_id);

                if (! $fichierContrat) {
                    $fichierContrat = ContratService::genererEtNotifier(
                        $c->personne,
                        $c,
                        "Voici votre contrat de travail (département : {$c->affectation}, responsable : {$c->responsable_nom}).",
                        'info',
                        $candidature->offre?->personne?->entreprise
                    );
                }

                $c->contrat_url = Storage::url($fichierContrat);
            }
        }

        $affectes = $candidatsAffectes->count();

        return view('dashboards.rh', compact('enAttente', 'affectes', 'candidatsAffectes', 'candidatsEnAttente'));
    }

    public function admin()
    {
        $entrepriseId = Auth::user()->entreprise_id;
        $entreprise = Auth::user()->entreprise;

        $personnesEntreprise = Personne::where('entreprise_id', $entrepriseId);

        $effectifs = [
            'managers'   => (clone $personnesEntreprise)->where('role', 'manager')->count(),
            'rh'         => (clone $personnesEntreprise)->where('role', 'rh')->count(),
            'candidats'  => (clone $personnesEntreprise)->where('role', 'candidat')->count(),
            'employes'   => Candidat::whereHas('personne', function ($q) use ($entrepriseId) {
                    $q->where('entreprise_id', $entrepriseId);
                })
                ->where('statutCandidature', 'affecté')
                ->count(),
        ];

        $offresEntreprise = Offre::whereHas('personne', function ($q) use ($entrepriseId) {
            $q->where('entreprise_id', $entrepriseId);
        });

        $offresOuvertes = (clone $offresEntreprise)->where('statut', 'ouvert')->count();
        $offresFermees = (clone $offresEntreprise)->where('statut', '!=', 'ouvert')->count();

        $candidaturesEnAttente = Candidature::where('statut', 'en_attente')
            ->whereHas('offre.personne', function ($q) use ($entrepriseId) {
                $q->where('entreprise_id', $entrepriseId);
            })
            ->count();

        $mois = now()->month;
        $annee = now()->year;

        $fichesDuMois = FichePaie::where('mois', $mois)
            ->where('annee', $annee)
            ->whereHas('personne', function ($q) use ($entrepriseId) {
                $q->where('entreprise_id', $entrepriseId);
            })
            ->get();
        $masseSalariale = $fichesDuMois->sum('salaireNet');
        $bulletinsGeneres = $fichesDuMois->count();

        $derniersEmployesAffectes = Candidat::with('personne')
            ->whereHas('personne', function ($q) use ($entrepriseId) {
                $q->where('entreprise_id', $entrepriseId);
            })
            ->where('statutCandidature', 'affecté')
            ->latest('date_affectation')
            ->limit(6)
            ->get();

        $departements = Candidat::whereHas('personne', function ($q) use ($entrepriseId) {
                $q->where('entreprise_id', $entrepriseId);
            })
            ->where('statutCandidature', 'affecté')
            ->whereNotNull('affectation')
            ->selectRaw('affectation, count(*) as total')
            ->groupBy('affectation')
            ->orderByDesc('total')
            ->get();

        $personnes = Personne::where('entreprise_id', $entrepriseId)
            ->orderBy('role')
            ->orderBy('nom')
            ->get();

        $employesPromouvables = Personne::where('entreprise_id', $entrepriseId)
            ->where('role', 'candidat')
            ->whereHas('candidat', function ($q) {
                $q->where('statutCandidature', 'affecté');
            })
            ->with('candidat')
            ->orderBy('nom')
            ->get();

        return view('dashboards.admin', compact(
            'entreprise',
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
            'personnes',
            'employesPromouvables'
        ));
    }

    public function departementEmployes(string $departement)
    {
        $entrepriseId = Auth::user()->entreprise_id;

        $employes = Candidat::with('personne')
            ->whereHas('personne', function ($q) use ($entrepriseId) {
                $q->where('entreprise_id', $entrepriseId);
            })
            ->where('statutCandidature', 'affecté')
            ->where('affectation', $departement)
            ->get();

        return view('dashboards.admin-departement', compact('departement', 'employes'));
    }

    public function superAdmin()
    {
        $entreprises = Entreprise::withCount(['managers', 'rh'])
            ->with(['admins' => function ($q) {
                $q->select('id', 'nom', 'prenom', 'email', 'entreprise_id');
            }])
            ->latest()
            ->get();

        $totalEntreprises = $entreprises->count();
        $totalAdmins = Personne::where('role', 'admin')->count();
        $totalManagers = Personne::where('role', 'manager')->count();
        $totalRh = Personne::where('role', 'rh')->count();

        return view('dashboards.super-admin', compact(
            'entreprises', 'totalEntreprises', 'totalAdmins', 'totalManagers', 'totalRh'
        ));
    }
}