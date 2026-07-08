<?php

namespace App\Http\Controllers;

use App\Models\Candidature;
use App\Models\Conge;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CongeController extends Controller
{

    public function index()
    {
        $this->verifierAccesCandidat();

        $conges = Conge::where('personne_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('conges.index', compact('conges'));
    }

    // Candidat : ses congés au format JSON, pour le calendrier (FullCalendar)
    public function events()
    {
        $this->verifierAccesCandidat();

        $conges = Conge::where('personne_id', Auth::id())->get();

        $couleurs = [
            'en attente' => '#60A5FA',
            'accepté'    => '#1D4ED8',
            'refusé'     => '#B4472B',
        ];

        $events = $conges->map(function ($conge) use ($couleurs) {
            return [
                'title'  => ucfirst($conge->statut),
                // FullCalendar traite "end" comme exclusif pour les événements
                // sur journée entière : on ajoute un jour pour inclure dateFin.
                'start'  => $conge->datDebut->format('Y-m-d'),
                'end'    => $conge->dateFin->copy()->addDay()->format('Y-m-d'),
                'color'  => $couleurs[$conge->statut] ?? '#64748B',
                'allDay' => true,
            ];
        });

        return response()->json($events);
    }

    // Candidat : formulaire de demande
    public function create(Request $request)
    {
        $this->verifierAccesCandidat();

        $dateDebut = $request->query('start');
        $dateFin = $request->query('end');

        return view('conges.create', compact('dateDebut', 'dateFin'));
    }

    // Candidat : enregistrer la demande
    public function store(Request $request)
    {
        $this->verifierAccesCandidat();

        $request->validate([
            'datDebut' => 'required|date|after_or_equal:today',
            'dateFin' => 'required|date|after_or_equal:datDebut',
            'commentaire' => 'nullable|string|max:1000',
        ]);

        Conge::create([
            'datDebut' => $request->datDebut,
            'dateFin' => $request->dateFin,
            'commentaire' => $request->commentaire,
            'statut' => 'en attente',
            'personne_id' => Auth::id(),
        ]);

        return redirect()->route('conges.index')
            ->with('success', 'Demande de congé envoyée avec succès !');
    }

    public function manager()
    {
        $conges = Conge::with('personne')
            ->orderByDesc('created_at')
            ->get();

        return view('conges.manager', compact('conges'));
    }

    // Manager : accepter ou refuser
    public function decider(Request $request, $id)
    {
        $request->validate([
            'statut' => 'required|in:accepté,refusé',
            'motif_refus' => 'required_if:statut,refusé|nullable|string|max:1000',
        ]);

        $conge = Conge::findOrFail($id);

        $conge->update([
            'statut' => $request->statut,
            'motif_refus' => $request->statut === 'refusé' ? $request->motif_refus : null,
        ]);

        $periode = $conge->datDebut->format('d/m/Y') . ' au ' . $conge->dateFin->format('d/m/Y');

        if ($request->statut === 'accepté') {
            Notification::create([
                'message' => "Votre demande de congé du {$periode} a été acceptée !",
                'type' => 'success',
                'dateEnvoi' => now(),
                'lu' => false,
                'personne_id' => $conge->personne_id,
            ]);
        } else {
            Notification::create([
                'message' => "Votre demande de congé du {$periode} a été refusée. Motif : {$request->motif_refus}",
                'type' => 'error',
                'dateEnvoi' => now(),
                'lu' => false,
                'personne_id' => $conge->personne_id,
            ]);
        }

        return redirect()->back()
            ->with('success', 'Décision enregistrée avec succès !');
    }

    // Vérifie que le candidat connecté a bien été accepté à une offre
    private function verifierAccesCandidat(): void
    {
        $estAccepte = Candidature::where('personne_id', Auth::id())
            ->where('statut', 'accepté')
            ->exists();

        if (!$estAccepte) {
            abort(403, 'Vous devez être accepté à une offre pour accéder aux congés.');
        }
    }
}