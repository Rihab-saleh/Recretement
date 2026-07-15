<?php

namespace App\Console\Commands;

use App\Models\Candidat;
use Illuminate\Console\Command;

/**
 * Commande de rattrapage à exécuter UNE SEULE FOIS après le déploiement du
 * correctif de cloisonnement multi-entreprise.
 *
 * Avant ce correctif, la fiche Personne d'un candidat affecté ne recevait
 * jamais d'entreprise_id (seuls manager/rh/admin l'avaient). Les pages
 * Paiement et Calendrier RH filtrent directement sur personne.entreprise_id,
 * donc tous les employés déjà affectés avant le correctif y sont invisibles
 * tant que cette commande n'a pas été exécutée.
 *
 * Elle déduit l'entreprise de chaque candidat affecté à partir de sa
 * candidature acceptée -> offre -> manager -> entreprise, puis met à jour
 * sa fiche Personne. Sans effet sur les candidats déjà à jour.
 */
class BackfillEntrepriseIdCandidats extends Command
{
    protected $signature = 'candidats:backfill-entreprise-id';
    protected $description = "Rattache l'entreprise_id des employés déjà affectés avant le correctif multi-entreprise";

    public function handle()
    {
        $candidats = Candidat::where('statutCandidature', 'affecté')
            ->with('personne')
            ->get();

        $misAJour = 0;
        $ignores = 0;

        foreach ($candidats as $candidat) {
            $personne = $candidat->personne;

            if (! $personne) {
                $ignores++;
                continue;
            }

            if ($personne->entreprise_id) {
                continue; // déjà correct
            }

            $entrepriseId = \App\Models\Candidature::where('personne_id', $personne->id)
                ->where('statut', 'accepté')
                ->with('offre.personne')
                ->latest()
                ->get()
                ->map(fn ($c) => $c->offre?->personne?->entreprise_id)
                ->filter()
                ->first();

            if (! $entrepriseId) {
                $this->warn("Aucune entreprise trouvée pour {$personne->prenom} {$personne->nom} (personne_id {$personne->id}) — ignoré.");
                $ignores++;
                continue;
            }

            $personne->update(['entreprise_id' => $entrepriseId]);
            $misAJour++;
        }

        $this->info("{$misAJour} employé(s) mis à jour. {$ignores} ignoré(s) (voir avertissements ci-dessus).");
    }
}