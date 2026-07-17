<?php

namespace App\Http\Controllers;

use App\Models\Offre;
use App\Models\Candidature;
use App\Models\Notification;
use App\Models\Personne;
use App\Models\Abonnement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OffreController extends Controller
{
    
    public function create()
    {
        return view('offres.create');
    }

  
    public function details(Offre $offre)
    {
        $offre->load('personne.entreprise');
        $offre->fermerSiNecessaire();

        $dejaPostule = false;
        if (Auth::check() && Auth::user()->role === 'candidat') {
            $dejaPostule = Candidature::where('personne_id', Auth::id())
                ->where('offre_id', $offre->id)
                ->exists();
        }

        $placesRestantes = $offre->nombre_candidats_max !== null
            ? max(0, $offre->nombre_candidats_max - $offre->candidatures()->where('statut', 'accepté')->count())
            : null;

        return view('offres.details', compact('offre', 'dejaPostule', 'placesRestantes'));
    }

    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'intitule'             => 'required|string|max:255',
            'description'          => 'nullable|string',
            'diplome'              => 'nullable|string|max:255',
            'experience'           => 'nullable|integer|min:0',
            'salaire'              => 'nullable|numeric|min:0',
            'date_fin'             => 'nullable|date|after_or_equal:today',
            'nombre_candidats_max' => 'nullable|integer|min:1',
        ]);

        
        $manager = Auth::user()->fresh();

        $validated['personne_id'] = Auth::id();
        $validated['statut'] = 'ouvert';
        $validated['datePublication'] = now();
        $validated['departement'] = ($manager && $manager->role === 'manager')
            ? $manager->departement
            : null;

        if (!array_key_exists('salaire', $validated) || $validated['salaire'] === null) {
            $validated['salaire'] = 0;
        }

        $offre = Offre::create($validated);

        if ($manager?->entreprise_id) {
            Abonnement::where('entreprise_id', $manager->entreprise_id)
                ->pluck('personne_id')
                ->each(function ($personneId) use ($offre, $manager) {
                    Notification::envoyer(
                        $personneId,
                        "{$manager->entreprise?->nom} vient de publier une nouvelle offre : « {$offre->intitule} ». Consultez-la dans vos offres disponibles.",
                        'nouvelle_offre'
                    );
                });
        }

        return redirect()->route('manager.dashboard')->with('success', 'Offre créée avec succès.');
    }

    public function destroy($id)
    {
        $offre = Offre::where('id', $id)
            ->where('personne_id', Auth::id())
            ->firstOrFail();

        $offre->delete();

        return redirect()->back()->with('success', 'Offre supprimée avec succès.');
    }

    public function index()
    {
        $offres = Offre::where('statut', 'ouvert')
            ->latest()
            ->get()
            ->filter(function ($offre) {
                return ! $offre->estExpiree() && ! $offre->estSaturee();
            });

        return view('offres.index', compact('offres'));
    }

    public function candidatures($offre_id)
    {
        $offre = Offre::where('id', $offre_id)
            ->where('personne_id', Auth::id())
            ->firstOrFail();

        $candidatures = Candidature::where('offre_id', $offre_id)
            ->whereIn('statut', ['en_attente'])
            ->where('valide_rh', true)
            ->with('personne')
            ->latest()
            ->get();

        return view('offres.candidatures', compact('offre', 'candidatures'));
    }

  
    public function deciderCandidat(Request $request, $id)
    {
        $request->validate([
            'statut'     => 'required|in:accepté,refusé',
            'note_refus' => 'required_if:statut,refusé|nullable|string|max:1000',
        ]);

        $candidature = Candidature::with('offre')->findOrFail($id);

        if ($candidature->offre->personne_id !== Auth::id()) {
            abort(403);
        }

        if (!$candidature->valide_rh) {
            abort(403, 'Cette candidature doit d\'abord être validée par le RH.');
        }

        $candidature->statut = $request->statut;

        if ($request->statut === 'refusé') {
            $candidature->note_refus = $request->note_refus;
        }

        $candidature->save();

        if ($request->statut === 'accepté') {
            $motifAutoRefus = 'Candidature retirée car vous avez été accepté à une autre offre.';

            $autresCandidatures = Candidature::with('offre')
                ->where('personne_id', $candidature->personne_id)
                ->where('statut', 'en_attente')
                ->where('id', '<>', $candidature->id)
                ->get();

            foreach ($autresCandidatures as $autre) {
                $autre->update([
                    'statut' => 'refusé',
                    'note_refus' => $motifAutoRefus,
                ]);

                Notification::create([
                    'personne_id' => $autre->personne_id,
                    'message' => 'Votre candidature pour le poste "' . ($autre->offre->intitule ?? '')
                        . '" a été refusée. Motif : ' . $motifAutoRefus,
                    'type' => 'error',
                    'dateEnvoi' => now(),
                    'lu' => false,
                ]);
            }
        }

        $offre = $candidature->offre;
        $offre->refresh();
        $offre->fermerSiNecessaire();

        Notification::create([
            'personne_id' => $candidature->personne_id,
            'message' => $request->statut === 'accepté'
                ? 'Félicitations, votre candidature a été acceptée pour le poste "' . $candidature->offre->intitule . '".'
                : 'Votre candidature pour le poste "' . $candidature->offre->intitule . '" a été refusée.',
            'type' => $request->statut === 'accepté' ? 'success' : 'error',
            'dateEnvoi' => now(),
            'lu' => false,
        ]);

        return redirect()->back()->with(
            'success',
            $request->statut === 'accepté'
                ? 'Candidat accepté avec succès.'
                : 'Candidat refusé avec succès.'
        );
    }

    public function supprimerCandidature($id)
    {
        $candidature = Candidature::with('offre')->findOrFail($id);

        if ($candidature->offre->personne_id !== Auth::id()) {
            abort(403);
        }

        $candidature->delete();

        return redirect()->back()->with('success', 'Candidature supprimée avec succès.');
    }

    public function candidatsAcceptes()
    {
        $departement = Auth::user()->departement;


        $candidatures = Candidature::with(['personne.candidat', 'offre'])
            ->where('statut', 'accepté')
            ->whereHas('personne.candidat', function ($query) use ($departement) {
                $query->where('affectation', $departement);
            })
            ->latest()
            ->get();

        return view('offres.candidats', compact('candidatures'));
    }


    public function signalerCandidat(Request $request, $candidatureId)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $candidature = Candidature::with(['personne.candidat', 'offre'])->findOrFail($candidatureId);

   
        $departementCandidat = $candidature->personne->candidat->affectation ?? null;
        if ($candidature->statut !== 'accepté' || $departementCandidat !== Auth::user()->departement) {
            abort(403);
        }

        $manager = Auth::user();
        $nomCandidat = trim(($candidature->personne->prenom ?? '') . ' ' . ($candidature->personne->nom ?? ''));

        $destinatairesRH = Personne::where('role', 'rh')->get();

        foreach ($destinatairesRH as $rh) {
            Notification::create([
                'personne_id' => $rh->id,
                'message' => "Message de {$manager->prenom} {$manager->nom} à propos de {$nomCandidat} : {$request->message}",
                'type' => 'info',
                'dateEnvoi' => now(),
                'lu' => false,
            ]);
        }

        return redirect()->back()->with('success', 'Votre message a bien été envoyé au RH.');
    }
}