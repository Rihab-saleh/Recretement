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
            // Part que l'État (CNSS) retient sur le salaire de base. 9.68% = taux salarié standard en Tunisie,
            // modifiable depuis le formulaire si besoin.
            'cnss' => (float) $request->input('pourcentage_cnss', 9.68),
        ];
    }

    /**
     * Calcule, pour un candidat donné et un mois donné, les jours travaillés,
     * absences, congés, retard cumulé et le détail des retenues salariales.
     * Ne modifie rien en base ; retourne juste les chiffres calculés.
     */
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
            // salaire_propose est ici un taux JOURNALIER (brut). On retire d'abord la CNSS de ce taux
            // de base pour obtenir un taux journalier NET, et c'est ce taux net qui sert ensuite à
            // calculer tout le reste (heures de retard, salaire gagné...). L'employé n'est payé que
            // pour les jours réellement travaillés : un jour de congé n'est donc déjà pas rémunéré.
            $deductionCnssParJour = $salaireBase * ($pourcentages['cnss'] / 100);
            $salaireJournalier = max(0, $salaireBase - $deductionCnssParJour);
            $salaireHoraire = $salaireJournalier / 8;

            $salaireGagne = $joursTravailles * $salaireJournalier;
            $deductionCnss = $joursTravailles * $deductionCnssParJour;
            // Base sur laquelle la CNSS a été appliquée (affichage) : le brut des jours travaillés.
            $baseCnss = $joursTravailles * $salaireBase;
            // Taux journalier déjà net de CNSS (affichage du "salaire de base après CNSS").
            $salaireBaseNet = $salaireJournalier;

            $deductionAbsence = 0.0;
            $deductionConge = 0.0;
            $deductionRetard = ($totalRetardMinutes / 60) * $salaireHoraire * ($pourcentages['retard'] / 100);
        } else {
            // 'mensuel' : salaire_propose est un salaire fixe MENSUEL brut. On retire la CNSS de ce
            // salaire de base EN PREMIER (elle ne dépend pas des jours déjà écoulés dans le mois), ce
            // qui donne le salaire déjà net de cotisations sociales. C'est CE salaire (net de CNSS)
            // qui est ensuite divisé par les jours ouvrés du MOIS COMPLET (23 jours par exemple en
            // juillet 2026, pas les jours de la période partielle) pour obtenir un taux journalier NET,
            // stable tout au long du mois, et utilisé pour toutes les autres retenues (absence, congé,
            // retard) ainsi que pour le salaire réellement gagné.
            $deductionCnss = $salaireBase * ($pourcentages['cnss'] / 100);
            $baseCnss = $salaireBase;
            $salaireBaseApresCnss = max(0, $salaireBase - $deductionCnss);
            // Salaire de base mensuel déjà net de CNSS (affichage).
            $salaireBaseNet = $salaireBaseApresCnss;

            $joursOuvresMoisComplet = 0;
            for ($jour = $debutMois->copy(); $jour->lessThanOrEqualTo($finMois); $jour->addDay()) {
                if (! $jour->isWeekend()) {
                    $joursOuvresMoisComplet++;
                }
            }

            $salaireJournalier = $joursOuvresMoisComplet > 0 ? $salaireBaseApresCnss / $joursOuvresMoisComplet : 0;
            $salaireHoraire = $salaireJournalier / 8;

            // Jours réellement payés ce mois-ci : jours pointés comme travaillés + jours de congé.
            // Le congé reste inclus ici (le salaire de base couvre le mois entier) ; c'est la retenue
            // "Congé" ci-dessous qui en retire ensuite exactement le prix, jour par jour (déjà net de CNSS).
            $joursPayes = $joursTravailles + $joursConge;
            $salaireGagne = $joursPayes * $salaireJournalier;

            // Une absence n'est déjà pas payée (le jour n'entre pas dans $joursPayes) ; le pourcentage
            // absence ne sert ici qu'à appliquer une pénalité supplémentaire, si configurée (0 par défaut).
            $deductionAbsence = $joursAbsence * $salaireJournalier * ($pourcentages['absence'] / 100);
            // Congé : plus de pourcentage configurable, on retire automatiquement le prix exact des
            // jours de congé (jours de congé x taux journalier net).
            $deductionConge = $joursConge * $salaireJournalier;
            $deductionRetard = ($totalRetardMinutes / 60) * $salaireHoraire * ($pourcentages['retard'] / 100);
        }

        // La CNSS a déjà été retirée ci-dessus (du salaire de base, en premier) : $salaireGagne est
        // donc déjà net de CNSS. Les autres retenues (absence, congé, retard) sont ensuite retirées
        // de ce montant pour obtenir le salaire net final.
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

    /**
     * Page RH : tableau des employés affectés avec le calcul de leur salaire
     * net du mois, et un bouton pour générer/envoyer le bulletin de chacun.
     */
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

        // Fiches déjà générées ce mois-ci (pour proposer "Télécharger" plutôt que "Générer")
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

    /**
     * Génère (ou régénère) le PDF du bulletin de paie d'un employé,
     * l'enregistre dans fiche_paies, et notifie l'employé.
     */
    public function generer(Request $request, $personneId)
    {
        // Protection serveur : l'admin ne peut pas générer/régénérer un bulletin, même en
        // forçant la requête directement (la vue ne fait que masquer le bouton).
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

    /**
     * Génère et envoie le bulletin de tous les employés affectés pour le mois affiché.
     */
    public function genererTout(Request $request)
    {
        // Protection serveur : idem, l'admin ne peut pas déclencher l'envoi groupé des bulletins.
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

    /**
     * Télécharge le PDF d'une fiche de paie déjà générée.
     */
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

    /**
     * Télécharge un fichier Excel listant toutes les fiches de paie générées
     * pour le mois affiché (une ligne par employé), à destination du service RH.
     */
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