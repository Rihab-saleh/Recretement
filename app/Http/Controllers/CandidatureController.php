<?php

namespace App\Http\Controllers;

use App\Models\Candidature;
use App\Models\Offre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidatureController extends Controller
{
    public function create($offre_id)
    {
        $offre = Offre::findOrFail($offre_id);

        $dejaPosule = Candidature::where('personne_id', Auth::id())
                                  ->where('offre_id', $offre_id)
                                  ->exists();

        if ($dejaPosule) {
            return redirect()->back()->with('error', 'Vous avez déjà postulé à cette offre.');
        }

        if (Candidature::where('personne_id', Auth::id())->where('statut', 'accepté')->exists()) {
            return redirect()->back()->with('error', 'Vous avez déjà été accepté à une offre et ne pouvez plus postuler.');
        }

        if ($offre->statut !== 'ouvert' || $offre->estExpiree() || $offre->estSaturee()) {
            $offre->fermerSiNecessaire();

            return redirect()->back()->with('error', 'Cette offre n\'est plus disponible pour de nouvelles candidatures.');
        }

        return view('candidatures.create', compact('offre'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'offre_id'          => 'required|exists:offres,id',
            'telephone'         => 'required|string|max:20|regex:/^[0-9]+$/',
            'cv'                => 'nullable|file|mimes:pdf|max:2048',
            'lettre_motivation' => 'nullable|string|max:2000',
            'experience'        => 'required|integer|min:0',
            'diplome'           => 'required|string|max:255',
        ], [
            'telephone.regex' => 'Le numéro de téléphone ne doit contenir que des chiffres.',
        ]);

        $offre = Offre::findOrFail($request->offre_id);

        if (Candidature::where('personne_id', Auth::id())->where('statut', 'accepté')->exists()) {
            return redirect()->back()->with('error', 'Vous avez déjà été accepté à une offre et ne pouvez plus postuler.');
        }

        if ($offre->statut !== 'ouvert' || $offre->estExpiree() || $offre->estSaturee()) {
            $offre->fermerSiNecessaire();

            return redirect()->back()->with('error', 'Cette offre n\'est plus disponible pour de nouvelles candidatures.');
        }

        $cvPath = null;
        if ($request->hasFile('cv') && $request->file('cv')->isValid()) {
            $cvPath = $request->file('cv')->store('cvs', 'public');
        }

        $candidature = Candidature::create([
            'personne_id'       => Auth::id(),
            'offre_id'          => $request->offre_id,
            'statut'            => 'en_attente',
            'datePostulation'   => now(),
            'telephone'         => $request->telephone,
            'cv'                => $cvPath,
            'lettre_motivation' => $request->lettre_motivation,
            'experience'        => $request->experience,
            'diplome'           => $request->diplome,
        ]);

        $offre->refresh();
        $offre->fermerSiNecessaire();

        return redirect()->route('candidat.dashboard')
                         ->with('success', 'Candidature envoyée avec succès !');
    }

    public function index()
    {
        $candidatures = Candidature::where('personne_id', Auth::id())
                                    ->with('offre')
                                    ->get();

        return view('candidatures.index', compact('candidatures'));
    }
}