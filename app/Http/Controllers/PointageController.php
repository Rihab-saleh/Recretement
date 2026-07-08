<?php

namespace App\Http\Controllers;

use App\Models\Candidature;
use App\Models\Pointage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PointageController extends Controller
{
    public function pointer(Request $request)
    {
        $this->verifierAccesCandidat();

        $aujourdHui = now()->toDateString();

        // Vérifier si le candidat a déjà pointé aujourd'hui
        $dejaPointe = Pointage::where('personne_id', Auth::id())
            ->whereDate('date', $aujourdHui)
            ->exists();

        if ($dejaPointe) {
            return redirect()->back()->with('error', 'Vous avez déjà pointé aujourd\'hui.');
        }

        $heureEntree = now();

        $heureDebut = now()->copy()->setTime(8, 0, 0);

        $heureLimite = now()->copy()->setTime(9, 0, 0);

        if ($heureEntree->greaterThan($heureLimite)) {

            return redirect()->back()->with(
                'error',
                'Vous êtes considéré absent. Le pointage est fermé après 09:00.'
            );
        }

        if ($heureEntree->greaterThan($heureDebut)) {
            $retard = $heureDebut->diffInMinutes($heureEntree);
            $statut = 'en retard';
        } else {
            $retard = 0;
            $statut = 'à l\'heure';
        }

        // Enregistrer le pointage
        Pointage::create([
            'date' => $aujourdHui,
            'heureEntree' => $heureEntree->format('H:i:s'),
            'heureSortie' => null,
            'retardMinutes' => $retard,
            'nbHeures' => null,
            'statut' => $statut,
            'personne_id' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Pointage enregistré avec succès !');
    }

    public function sortir(Request $request)
    {
        $this->verifierAccesCandidat();

        $aujourdHui = now()->toDateString();

        $pointage = Pointage::where('personne_id', Auth::id())
            ->whereDate('date', $aujourdHui)
            ->first();

        if (!$pointage) {
            return redirect()->back()->with('error', 'Vous devez d\'abord pointer votre arrivée aujourd\'hui.');
        }

        if ($pointage->heureSortie != null) {
            return redirect()->back()->with('error', 'Vous avez déjà enregistré votre départ aujourd\'hui.');
        }

        $heureSortie = now();
        $heureEntree = now()->copy()->setTimeFromTimeString($pointage->heureEntree);

        $nbHeures = round($heureEntree->diffInMinutes($heureSortie) / 60, 2);

        $pointage->update([
            'heureSortie' => $heureSortie->format('H:i:s'),
            'nbHeures' => $nbHeures,
        ]);

        return redirect()->back()->with('success', 'Départ enregistré avec succès !');
    }

    private function verifierAccesCandidat(): void
    {
        $estAccepte = Candidature::where('personne_id', Auth::id())
            ->where('statut', 'accepté')
            ->exists();

        if (!$estAccepte) {
            abort(403, 'Vous devez être accepté à une offre pour pointer.');
        }
    }
}