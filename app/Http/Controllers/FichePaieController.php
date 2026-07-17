<?php

namespace App\Http\Controllers;

use App\Models\Candidat;
use App\Models\Conge;
use App\Models\FichePaie;
use App\Models\Notification;
use App\Models\Pointage;
use App\Exports\FichePaieExport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class FichePaieController extends Controller
{
    
    protected function pourcentages(Request $request): array
    {
        return [
            'absence' => (float) $request->input('pourcentage_absence', 5),
            'retard' => (float) $request->input('pourcentage_retard',5),
            'cnss' => (float) $request->input('pourcentage_cnss', 9.68),
        ];
    }


    protected function calculer(Candidat $candidat, int $mois, int $annee, array $pourcentages): array
    {
        $debutMois = Carbon::create($annee, $mois, 1)->startOfDay();
        $finMois = $debutMois->copy()->endOfMonth();
        $aujourdhui = now()->endOfDay();
        $derniereDate = $finMois->greaterThan($aujourdhui) ? $aujourdhui->copy()->startOfDay() : $finMois;

        $dateAffectation = $candidat->date_affectation ? $candidat->date_affectation->copy()->startOfDay() : $debutMois->copy();
        $debutPeriode = $dateAffectation->greaterThan($debutMois) ? $dateAffectation->copy() : $debutMois->copy();

        $pointages = Pointage::where('personne_id', $candidat->personne_id)
            ->whereBetween('date', [$debutPeriode->toDateString(), $derniereDate->toDateString()])
            ->get()
            ->keyBy(fn ($p) => $p->date->toDateString());

        $conges = Conge::where('personne_id', $candidat->personne_id)
            ->where('statut', 'accepté')
            ->where('datDebut', '<=', $finMois->toDateString())
            ->where('dateFin', '>=', $debutMois->toDateString())
            ->get();

        $joursOuvres = 0;
        $joursTravailles = 0;
        $joursAbsence = 0;
        $joursConge = 0;
        $joursRetard = 0;
        $totalRetardMinutes = 0;

        if ($debutPeriode->lessThanOrEqualTo($derniereDate)) {
            for ($jour = $debutPeriode->copy(); $jour->lessThanOrEqualTo($derniereDate); $jour->addDay()) {
                if ($jour->isWeekend()) {
                    continue;
                }

                $joursOuvres++;
                $dateStr = $jour->toDateString();

                $enConge = $conges->first(fn ($c) => $jour->between($c->datDebut, $c->dateFin));

                if ($enConge) {
                    $joursConge++;
                    continue;
                }

                $pointage = $pointages->get($dateStr);

                if ($pointage) {
                    $joursTravailles++;
                    if ($pointage->retardMinutes > 0) {
                        $joursRetard++;
                        $totalRetardMinutes += $pointage->retardMinutes;
                    }
                } else {
                    $joursAbsence++;
                }
            }
        }

        $salaireBase = (float) $candidat->salaire_propose;
        $typeSalaire = $candidat->type_salaire ?: 'mensuel';

        if ($typeSalaire === 'journalier') {
            $deductionCnssParJour = $salaireBase * ($pourcentages['cnss'] / 100);
            $salaireJournalier = max(0, $salaireBase - $deductionCnssParJour);
            $salaireHoraire = $salaireJournalier / 8;

            $salaireGagne = $joursTravailles * $salaireJournalier;
            $deductionCnss = $joursTravailles * $deductionCnssParJour;
            $baseCnss = $joursTravailles * $salaireBase;
            $salaireBaseNet = $salaireJournalier;

            $deductionAbsence = 0.0;
            $deductionConge = 0.0;
            $deductionRetard = ($totalRetardMinutes / 60) * $salaireHoraire * ($pourcentages['retard'] / 100);
        } else {

            $deductionCnss = $salaireBase * ($pourcentages['cnss'] / 100);
            $baseCnss = $salaireBase;
            $salaireBaseApresCnss = max(0, $salaireBase - $deductionCnss);
            $salaireBaseNet = $salaireBaseApresCnss;

            $joursOuvresMoisComplet = 0;
            for ($jour = $debutMois->copy(); $jour->lessThanOrEqualTo($finMois); $jour->addDay()) {
                if (! $jour->isWeekend()) {
                    $joursOuvresMoisComplet++;
                }
            }

            $salaireJournalier = $joursOuvresMoisComplet > 0 ? $salaireBaseApresCnss / $joursOuvresMoisComplet : 0;
            $salaireHoraire = $salaireJournalier / 8;

            $joursPayes = $joursTravailles + $joursConge;
            $salaireGagne = $joursPayes * $salaireJournalier;

            $deductionAbsence = $joursAbsence * $salaireJournalier * ($pourcentages['absence'] / 100);

            $deductionConge = $joursConge * $salaireJournalier;
            $deductionRetard = ($totalRetardMinutes / 60) * $salaireHoraire * ($pourcentages['retard'] / 100);
        }

   
        $salaireNetApresCnss = max(0, $salaireGagne);
        $totalAutresRetenues = $deductionAbsence + $deductionConge + $deductionRetard;
        $totalDeductions = $deductionCnss + $totalAutresRetenues;
        $salaireNet = max(0, $salaireGagne - $totalAutresRetenues);

        return [
            'candidat' => $candidat,
            'entreprise' => $candidat->personne?->entreprise,
            'mois' => $mois,
            'annee' => $annee,
            'periode' => $debutMois->translatedFormat('F Y'),
            'typeSalaire' => $typeSalaire,
            'joursOuvres' => $joursOuvres,
            'joursTravailles' => $joursTravailles,
            'joursAbsence' => $joursAbsence,
            'joursConge' => $joursConge,
            'joursRetard' => $joursRetard,
            'totalRetardMinutes' => $totalRetardMinutes,
            'salaireBase' => $salaireBase,
            'salaireBaseNet' => $salaireBaseNet,
            'salaireGagne' => $salaireGagne,
            'salaireJournalier' => $salaireJournalier,
            'deductionAbsence' => $deductionAbsence,
            'deductionConge' => $deductionConge,
            'deductionRetard' => $deductionRetard,
            'deductionCnss' => $deductionCnss,
            'baseCnss' => $baseCnss,
            'salaireNetApresCnss' => $salaireNetApresCnss,
            'totalDeductions' => $totalDeductions,
            'salaireNet' => $salaireNet,
            'pourcentages' => $pourcentages,
        ];
    }


    public function index(Request $request)
    {
        $mois = (int) $request->query('mois', now()->month);
        $annee = (int) $request->query('annee', now()->year);
        $pourcentages = $this->pourcentages($request);

        $candidats = Candidat::with('personne')
            ->where('statutCandidature', 'affecté')
            ->whereNotNull('date_affectation')
            ->whereHas('personne', function ($q) {
                $q->where('entreprise_id', Auth::user()?->entreprise_id);
            })
            ->get()
            ->sortBy(fn ($c) => $c->personne->nom ?? '');

        $bulletins = $candidats->map(fn ($candidat) => $this->calculer($candidat, $mois, $annee, $pourcentages));

        $fichesExistantes = FichePaie::where('mois', $mois)
            ->where('annee', $annee)
            ->whereIn('personne_id', $candidats->pluck('personne_id'))
            ->get()
            ->keyBy('personne_id');

        $debutMois = Carbon::create($annee, $mois, 1);
        $moisPrecedent = $debutMois->copy()->subMonth();
        $moisSuivant = $debutMois->copy()->addMonth();

        return view('rh.paiement', [
            'bulletins' => $bulletins,
            'fichesExistantes' => $fichesExistantes,
            'mois' => $mois,
            'annee' => $annee,
            'pourcentages' => $pourcentages,
            'titreMois' => $debutMois->translatedFormat('F Y'),
            'moisPrecedent' => $moisPrecedent->month,
            'anneePrecedente' => $moisPrecedent->year,
            'moisSuivant' => $moisSuivant->month,
            'anneeSuivante' => $moisSuivant->year,
        ]);
    }


    public function generer(Request $request, $personneId)
    {

        abort_if(Auth::user()?->isAdmin(), 403, "L'administrateur ne peut pas générer ni envoyer les bulletins de paie.");

        $mois = (int) $request->input('mois', now()->month);
        $annee = (int) $request->input('annee', now()->year);
        $pourcentages = $this->pourcentages($request);

        $candidat = Candidat::with('personne')->where('personne_id', $personneId)->firstOrFail();

        abort_unless($candidat->personne?->entreprise_id === Auth::user()?->entreprise_id, 403);

        $this->genererPourCandidat($candidat, $mois, $annee, $pourcentages);

        return redirect()->back()->with(
            'success',
            'Bulletin de paie généré et envoyé à ' . ($candidat->personne->prenom ?? '') . ' ' . ($candidat->personne->nom ?? '') . '.'
        );
    }
    public function genererTout(Request $request)
    {
        abort_if(Auth::user()?->isAdmin(), 403, "L'administrateur ne peut pas générer ni envoyer les bulletins de paie.");

        $mois = (int) $request->input('mois', now()->month);
        $annee = (int) $request->input('annee', now()->year);
        $pourcentages = $this->pourcentages($request);

        $candidats = Candidat::with('personne')
            ->where('statutCandidature', 'affecté')
            ->whereNotNull('date_affectation')
            ->whereHas('personne', function ($q) {
                $q->where('entreprise_id', Auth::user()?->entreprise_id);
            })
            ->get();

        foreach ($candidats as $candidat) {
            $this->genererPourCandidat($candidat, $mois, $annee, $pourcentages);
        }

        return redirect()->back()->with('success', $candidats->count() . ' bulletin(s) de paie généré(s) et envoyé(s).');
    }

    public function telecharger(FichePaie $fichePaie)
    {
        $user = Auth::user();
        $estProprietaire = $fichePaie->personne_id === $user?->id;
        $estRhDeLEntreprise = in_array($user?->role, ['rh', 'admin'])
            && $user?->entreprise_id === $fichePaie->personne?->entreprise_id;

        abort_unless($estProprietaire || $estRhDeLEntreprise, 403);

        abort_unless($fichePaie->fichierPDF && Storage::disk('public')->exists($fichePaie->fichierPDF), 404);

        return Storage::disk('public')->download($fichePaie->fichierPDF, 'bulletin_' . $fichePaie->periode . '.pdf');
    }

    public function exporterExcel(Request $request)
    {
        $mois = (int) $request->query('mois', now()->month);
        $annee = (int) $request->query('annee', now()->year);

        $nomFichier = 'fiches_paie_' . str_pad($mois, 2, '0', STR_PAD_LEFT) . '_' . $annee . '.xlsx';

        return Excel::download(new FichePaieExport($mois, $annee), $nomFichier);
    }

    private function genererPourCandidat(Candidat $candidat, int $mois, int $annee, array $pourcentages): FichePaie
    {
        $donnees = $this->calculer($candidat, $mois, $annee, $pourcentages);

        $pdf = Pdf::loadView('pdf.bulletin_paie', $donnees);

        $nomFichier = 'bulletins/bulletin_' . $candidat->personne_id . '_' . str_pad($mois, 2, '0', STR_PAD_LEFT) . '_' . $annee . '.pdf';
        Storage::disk('public')->put($nomFichier, $pdf->output());

        $fiche = FichePaie::updateOrCreate(
            [
                'personne_id' => $candidat->personne_id,
                'mois' => $mois,
                'annee' => $annee,
            ],
            [
                'salaireBase' => $donnees['salaireBase'],
                'joursConge' => $donnees['joursConge'],
                'salaireNet' => $donnees['salaireNet'],
                'fichierPDF' => $nomFichier,
                'joursOuvres' => $donnees['joursOuvres'],
                'joursTravailles' => $donnees['joursTravailles'],
                'joursAbsence' => $donnees['joursAbsence'],
                'joursRetard' => $donnees['joursRetard'],
                'totalRetardMinutes' => $donnees['totalRetardMinutes'],
                'deductionAbsence' => $donnees['deductionAbsence'],
                'deductionConge' => $donnees['deductionConge'],
                'deductionRetard' => $donnees['deductionRetard'],
                'deductionCnss' => $donnees['deductionCnss'],
                'pourcentageAbsence' => $pourcentages['absence'],
                'pourcentageRetard' => $pourcentages['retard'],
                'pourcentageCnss' => $pourcentages['cnss'],
            ]
        );

        Notification::create([
            'personne_id' => $candidat->personne_id,
            'message' => 'Votre bulletin de paie de ' . $donnees['periode'] . ' est disponible (salaire net : '
                . number_format($donnees['salaireNet'], 2) . ' DT).',
            'type' => 'success',
            'dateEnvoi' => now(),
            'lu' => false,
            'fichier' => $nomFichier,
        ]);

        return $fiche;
    }
}