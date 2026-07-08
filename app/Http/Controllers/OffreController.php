<?php

namespace App\Http\Controllers;

use App\Models\Offre;
use App\Models\Candidature;
use App\Models\Notification;
use App\Models\Personne;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OffreController extends Controller
{
    /**
     * Formulaire de création d'une offre (manager).
     */
    public function create()
    {
        return view('offres.create');
    }

    /**
     * Enregistre une nouvelle offre.
     */
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
            // Ajoutez ici les autres champs réels de votre formulaire si besoin
        ]);

        // Récupère une copie fraîche du manager connecté pour éviter de lire
        // un modèle Auth::user() mis en cache/périmé dans la session.
        $manager = Auth::user()->fresh();

        $validated['personne_id'] = Auth::id();
        $validated['statut'] = 'ouvert';
        $validated['datePublication'] = now();
        $validated['departement'] = ($manager && $manager->role === 'manager')
            ? $manager->departement
            : null;

        // Ensure salaire has a value to avoid DB errors when the column is not nullable
        if (!array_key_exists('salaire', $validated) || $validated['salaire'] === null) {
            $validated['salaire'] = 0;
        }

        Offre::create($validated);

        return redirect()->route('manager.dashboard')->with('success', 'Offre créée avec succès.');
    }

    /**
     * Supprime une offre.
     */
    public function destroy($id)
    {
        $offre = Offre::where('id', $id)
            ->where('personne_id', Auth::id())
            ->firstOrFail();

        $offre->delete();

        return redirect()->back()->with('success', 'Offre supprimée avec succès.');
    }

    /**
     * Liste des offres ouvertes (côté candidat).
     */
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

    /**
     * Liste des candidatures pour une offre donnée (manager).
     * N'affiche que les candidatures en attente ET validées par le RH :
     * le manager ne doit jamais voir un dossier que le RH n'a pas encore
     * contrôlé et transmis.
     */
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

    /**
     * Le manager accepte ou refuse une candidature.
     */
    public function deciderCandidat(Request $request, $id)
    {
        $request->validate([
            'statut'     => 'required|in:accepté,refusé',
            'note_refus' => 'required_if:statut,refusé|nullable|string|max:1000',
        ]);

        $candidature = Candidature::with('offre')->findOrFail($id);

        // Vérifie que l'offre appartient bien au manager connecté
        if ($candidature->offre->personne_id !== Auth::id()) {
            abort(403);
        }

        // Le manager ne peut décider que sur une candidature validée par le RH.
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

                // Le candidat doit être notifié de ce refus automatique,
                // au même titre qu'une acceptation ou un refus manuel.
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
        $candidatures = Candidature::with(['personne', 'offre'])
            ->whereHas('offre', function ($query) {
                $query->where('personne_id', Auth::id());
            })
            ->where('statut', 'accepté')
            ->latest()
            ->get();

        return view('offres.candidats', compact('candidatures'));
    }

    /**
     * Le manager envoie un simple message/signalement au RH à propos
     * d'un candidat accepté (reçu comme une notification classique).
     */
    public function signalerCandidat(Request $request, $candidatureId)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $candidature = Candidature::with(['personne', 'offre'])->findOrFail($candidatureId);

        // Le manager ne peut signaler que les candidats acceptés sur ses propres offres
        if (!$candidature->offre || $candidature->offre->personne_id !== Auth::id()) {
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