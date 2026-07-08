<?php

namespace App\Console\Commands;

use App\Models\Offre;
use Illuminate\Console\Command;

class FermerOffresExpirees extends Command
{
    protected $signature = 'offres:fermer-expirees';
    protected $description = 'Ferme automatiquement les offres dont la date de fin est dépassée';

    public function handle()
    {
        $offres = Offre::where('statut', 'ouvert')->get();

        $count = 0;
        foreach ($offres as $offre) {
            if ($offre->estExpiree() || $offre->estSaturee()) {
                $offre->update(['statut' => 'fermé']);
                $count++;
            }
        }

        $this->info("{$count} offre(s) fermée(s) automatiquement.");
    }
}