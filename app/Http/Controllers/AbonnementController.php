<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\Entreprise;
use Illuminate\Support\Facades\Auth;

class AbonnementController extends Controller
{
    
    public function toggle(Entreprise $entreprise)
    {
        $user = Auth::user();

        abort_unless($user?->role === 'candidat', 403);

        $abonnement = Abonnement::where('personne_id', $user->id)
            ->where('entreprise_id', $entreprise->id)
            ->first();

        if ($abonnement) {
            $abonnement->delete();
            $message = "Vous ne suivez plus {$entreprise->nom}.";
        } else {
            Abonnement::create([
                'personne_id'   => $user->id,
                'entreprise_id' => $entreprise->id,
            ]);
            $message = "Vous suivez désormais {$entreprise->nom}. Vous recevrez une notification à chaque nouvelle offre.";
        }

        return redirect()->back()->with('success', $message);
    }
}