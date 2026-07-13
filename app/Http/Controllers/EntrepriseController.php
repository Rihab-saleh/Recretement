<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use App\Models\Personne;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EntrepriseController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(
            array_merge(
                [
                    'entreprise_nom'       => 'required|string|max:255',
                    'entreprise_email'     => 'nullable|email|max:255',
                    'entreprise_telephone' => 'nullable|string|max:50',
                    'entreprise_adresse'   => 'nullable|string|max:255',
                    'entreprise_ville'     => 'nullable|string|max:255',
                    'entreprise_pays'      => 'nullable|string|max:255',
                    'logo'                 => 'nullable|image|max:2048',

                    'admin_nom'    => 'required|string|max:255',
                    'admin_prenom' => 'required|string|max:255',
                    'admin_email'  => 'required|email|unique:personnes,email',
                ],
                Personne::passwordRules()
            ),
            Personne::passwordMessages()
        );

        $logoPath = $request->hasFile('logo')
            ? $request->file('logo')->store('logos', 'public')
            : null;

        $entreprise = Entreprise::create([
            'nom'       => $request->entreprise_nom,
            'email'     => $request->entreprise_email,
            'telephone' => $request->entreprise_telephone,
            'adresse'   => $request->entreprise_adresse,
            'ville'     => $request->entreprise_ville,
            'pays'      => $request->entreprise_pays,
            'logo'      => $logoPath,
        ]);

        Personne::create([
            'nom'           => $request->admin_nom,
            'prenom'        => $request->admin_prenom,
            'email'         => $request->admin_email,
            'password'      => Hash::make($request->password),
            'role'          => 'admin',
            'entreprise_id' => $entreprise->id,
        ]);

        return redirect()->route('super-admin.dashboard')
                         ->with('success', "Entreprise « {$entreprise->nom} » créée avec son compte admin.");
    }

    public function destroy($id)
    {
        $entreprise = Entreprise::findOrFail($id);

        $entreprise->delete();

        return redirect()->route('super-admin.dashboard')
                         ->with('success', "Entreprise « {$entreprise->nom} » supprimée.");
    }
}