<?php

namespace App\Console\Commands;

use App\Models\Candidat;
use App\Models\Conge;
use App\Models\Pointage;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenererPointagesConges extends Command
{
    protected $signature = 'pointages:generer
        {--mois= : Mois à générer (1-12), mois courant par défaut}
        {--annee= : Année à générer, année courante par défaut}
        {--force : Régénère même si des pointages existent déjà pour ce mois}';

    protected $description = "Génère les pointages et congés du mois pour chaque employé affecté à un département (pour test / démo)";

    public function handle(): int
    {
        $mois  = (int) ($this->option('mois') ?: now()->month);
        $annee = (int) ($this->option('annee') ?: now()->year);
        $force = (bool) $this->option('force');

        $debutMois = Carbon::create($annee, $mois, 1)->startOfDay();
        $finMois   = $debutMois->copy()->endOfMonth();
        $aujourdhui = now()->endOfDay();

        $derniereDate = $finMois->greaterThan($aujourdhui) ? $aujourdhui->copy()->startOfDay() : $finMois;

        $candidats = Candidat::where('statutCandidature', 'affecté')
            ->whereNotNull('date_affectation')
            ->where('date_affectation', '<=', $finMois->toDateString())
            ->get();

        if ($candidats->isEmpty()) {
            $this->warn('Aucun employé affecté à un département trouvé.');
            return self::SUCCESS;
        }

        $totalPointages = 0;
        $totalConges = 0;

        foreach ($candidats as $candidat) {
            $personneId = $candidat->personne_id;

            $dateAffectation = $candidat->date_affectation->copy()->startOfDay();
            $debutGeneration = $dateAffectation->greaterThan($debutMois) ? $dateAffectation->copy() : $debutMois->copy();

            if ($debutGeneration->greaterThan($derniereDate)) {
                continue;
            }

            $dejaGenere = Pointage::where('personne_id', $personneId)
                ->whereBetween('date', [$debutGeneration->toDateString(), $derniereDate->toDateString()])
                ->exists();

            if ($dejaGenere && !$force) {
                $this->line("Employé #{$personneId} : déjà des pointages ce mois-ci, ignoré (utilisez --force pour régénérer).");
                continue;
            }

            if ($force) {
                Pointage::where('personne_id', $personneId)
                    ->whereBetween('date', [$debutGeneration->toDateString(), $finMois->toDateString()])
                    ->delete();
                Conge::where('personne_id', $personneId)
                    ->whereBetween('datDebut', [$debutGeneration->toDateString(), $finMois->toDateString()])
                    ->delete();
            }

            // 0 à 2 périodes de congé aléatoires dans la période (2 à 4 jours ouvrés)
            $joursEnConge = collect();
            $nbJoursDispo = $debutGeneration->diffInDays($derniereDate);
            $nbConges = $nbJoursDispo >= 7 ? random_int(0, 2) : 0;

            for ($i = 0; $i < $nbConges; $i++) {
                $decalageMax = max(1, $nbJoursDispo - 4);
                $debutConge = $debutGeneration->copy()->addDays(random_int(0, $decalageMax));
                $dureeConge = random_int(2, 4);
                $finConge = $debutConge->copy()->addDays($dureeConge - 1);

                if ($finConge->greaterThan($derniereDate)) {
                    continue;
                }

                Conge::create([
                    'datDebut' => $debutConge->toDateString(),
                    'dateFin' => $finConge->toDateString(),
                    'statut' => 'accepté',
                    'commentaire' => 'Congé généré automatiquement (démo).',
                    'personne_id' => $personneId,
                ]);
                $totalConges++;

                for ($d = $debutConge->copy(); $d->lessThanOrEqualTo($finConge); $d->addDay()) {
                    $joursEnConge->push($d->toDateString());
                }
            }

            // Pointages jour par jour, à partir de la date d'affectation (jours ouvrés uniquement, hors congés)
            for ($jour = $debutGeneration->copy(); $jour->lessThanOrEqualTo($derniereDate); $jour->addDay()) {

                if ($jour->isWeekend()) {
                    continue;
                }

                if ($joursEnConge->contains($jour->toDateString())) {
                    continue;
                }

                if (random_int(1, 100) > 88) {
                    continue;
                }

                $heureEntree = $jour->copy()->setTime(7, random_int(45, 59), random_int(0, 59));
                if (random_int(1, 100) > 70) {
                    $heureEntree = $jour->copy()->setTime(8, random_int(0, 45), random_int(0, 59));
                }

                $heureLimite = $jour->copy()->setTime(8, 0);
                $retard = $heureEntree->greaterThan($heureLimite)
                    ? $heureLimite->diffInMinutes($heureEntree)
                    : 0;

                $dureeTravailMinutes = random_int(7 * 60 + 30, 9 * 60);
                $heureSortie = $heureEntree->copy()->addMinutes($dureeTravailMinutes);
                $nbHeures = round($heureEntree->diffInMinutes($heureSortie) / 60, 2);

                Pointage::create([
                    'date' => $jour->toDateString(),
                    'heureEntree' => $heureEntree->format('H:i:s'),
                    'heureSortie' => $heureSortie->format('H:i:s'),
                    'retardMinutes' => $retard,
                    'nbHeures' => $nbHeures,
                    'statut' => $retard > 0 ? 'en retard' : "à l'heure",
                    'personne_id' => $personneId,
                ]);
                $totalPointages++;
            }
        }

        $this->info("Terminé : {$totalPointages} pointage(s) et {$totalConges} congé(s) généré(s) pour {$debutMois->translatedFormat('F Y')}.");

        return self::SUCCESS;
    }
}