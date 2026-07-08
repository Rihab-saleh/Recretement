<?php

namespace App\Http\Controllers;

use App\Models\Offre;
use App\Models\Candidature;
use App\Models\Pointage;
use Illuminate\Support\Facades\Auth;

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

        $affectes = $candidatsAffectes->count();

        return view('dashboards.rh', compact('enAttente', 'affectes', 'candidatsAffectes', 'candidatsEnAttente'));
    }
}