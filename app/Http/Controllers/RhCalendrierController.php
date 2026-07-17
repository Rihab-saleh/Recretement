<?php

namespace App\Http\Controllers;

use App\Exports\PointagesCongesExport;
use App\Models\Candidat;
use App\Models\Conge;
use App\Models\Personne;
use App\Models\Pointage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class RhCalendrierController extends Controller
{
    public function index(Request $request)
    {
        $entrepriseId = Auth::user()?->entreprise_id;

        $mois = (int) $request->query('mois', now()->month);
        $annee = (int) $request->query('annee', now()->year);

        $debutMois = Carbon::create($annee, $mois, 1)->startOfDay();
        $finMois = $debutMois->copy()->endOfMonth();
        $aujourdhui = now()->endOfDay();
        $derniereDate = $finMois->greaterThan($aujourdhui) ? $aujourdhui->copy()->startOfDay() : $finMois;

        $candidats = Candidat::where('statutCandidature', 'affecté')
            ->whereNotNull('date_affectation')
            ->where('date_affectation', '<=', $finMois->toDateString())
            ->whereHas('personne', function ($q) use ($entrepriseId) {
                $q->where('entreprise_id', $entrepriseId);
            })
            ->get()
            ->keyBy('personne_id');

        $employesIds = $candidats->keys();

        $employes = Personne::whereIn('id', $employesIds)
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        $pointagesParEmploye = Pointage::whereIn('personne_id', $employesIds)
            ->whereBetween('date', [$debutMois->toDateString(), $finMois->toDateString()])
            ->get()
            ->groupBy('personne_id');

        $congesParEmploye = Conge::whereIn('personne_id', $employesIds)
            ->where('statut', 'accepté')
            ->where('datDebut', '<=', $finMois->toDateString())
            ->where('dateFin', '>=', $debutMois->toDateString())
            ->get()
            ->groupBy('personne_id');

        $jours = [];
        for ($jour = $debutMois->copy(); $jour->lessThanOrEqualTo($finMois); $jour->addDay()) {
            $jours[] = [
                'numero' => $jour->day,
                'date' => $jour->toDateString(),
                'jourAbrege' => $jour->translatedFormat('D'),
                'weekend' => $jour->isWeekend(),
                'futur' => $jour->greaterThan($derniereDate),
            ];
        }

        $lignes = $employes->map(function ($employe) use ($debutMois, $finMois, $derniereDate, $pointagesParEmploye, $congesParEmploye, $candidats) {
            $pointagesEmploye = ($pointagesParEmploye->get($employe->id) ?? collect())
                ->keyBy(fn ($p) => $p->date->toDateString());

            $congesEmploye = $congesParEmploye->get($employe->id) ?? collect();
            $dateAffectation = $candidats->get($employe->id)?->date_affectation?->copy()->startOfDay();
            $departement = $candidats->get($employe->id)?->affectation;

            $statutsParJour = [];

            for ($jour = $debutMois->copy(); $jour->lessThanOrEqualTo($finMois); $jour->addDay()) {
                $dateStr = $jour->toDateString();

                if ($dateAffectation && $jour->lessThan($dateAffectation)) {
                    $statutsParJour[$dateStr] = ['statut' => 'avant_affectation'];
                    continue;
                }

                if ($jour->greaterThan($derniereDate)) {
                    $statutsParJour[$dateStr] = ['statut' => 'futur'];
                    continue;
                }

                $pointage = $pointagesEmploye->get($dateStr);

                $enConge = $congesEmploye->first(function ($conge) use ($jour) {
                    return $jour->between($conge->datDebut, $conge->dateFin);
                });

                if ($enConge) {
                    $statutsParJour[$dateStr] = ['statut' => 'conge'];
                } elseif ($pointage) {
                    $statutsParJour[$dateStr] = [
                        'statut' => 'present',
                        'heureEntree' => substr((string) $pointage->heureEntree, 0, 5),
                        'heureSortie' => $pointage->heureSortie ? substr((string) $pointage->heureSortie, 0, 5) : null,
                        'nbHeures' => $pointage->nbHeures,
                        'enRetard' => $pointage->retardMinutes > 0,
                    ];
                } elseif ($jour->isWeekend()) {
                    $statutsParJour[$dateStr] = ['statut' => 'repos'];
                } else {
                    $statutsParJour[$dateStr] = ['statut' => 'absent'];
                }
            }

            return [
                'id' => $employe->id,
                'nom' => trim($employe->prenom . ' ' . $employe->nom),
                'departement' => $departement,
                'dateAffectation' => $dateAffectation?->format('d/m/Y'),
                'jours' => $statutsParJour,
            ];
        });

        $moisPrecedent = $debutMois->copy()->subMonth();
        $moisSuivant = $debutMois->copy()->addMonth();

        return view('rh.calendrier', [
            'employes' => $lignes,
            'jours' => $jours,
            'mois' => $mois,
            'annee' => $annee,
            'titreMois' => $debutMois->translatedFormat('F Y'),
            'moisPrecedent' => $moisPrecedent->month,
            'anneePrecedente' => $moisPrecedent->year,
            'moisSuivant' => $moisSuivant->month,
            'anneeSuivante' => $moisSuivant->year,
        ]);
    }

    
    public function export(Request $request)
    {
        $mois = (int) $request->query('mois', now()->month);
        $annee = (int) $request->query('annee', now()->year);

        $nomFichier = 'pointages_conges_' . str_pad($mois, 2, '0', STR_PAD_LEFT) . '_' . $annee . '.xlsx';

        return Excel::download(new PointagesCongesExport($mois, $annee, Auth::user()?->entreprise_id), $nomFichier);
    }
}