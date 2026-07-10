<?php

namespace App\Http\Controllers;

use App\Models\Personne;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PersonneController extends Controller
{
    public function index()
    {
        $personnes = Personne::all();
        return view('personnes.index', compact('personnes'));
    }

    public function create()
    {
        return view('personnes.create');
    }

    public function store(Request $request)
    {
        // Le RH crée déjà les comptes RH/candidat via le processus de recrutement/inscription ;
        // l'admin ne crée depuis cette page QUE des comptes manager.
        $request->validate(
            array_merge(
                [
                    'nom'         => 'required|string|max:255',
                    'prenom'      => 'required|string|max:255',
                    'email'       => 'required|email|unique:personnes,email',
                    'departement' => 'required|string|max:255',
                ],
                Personne::passwordRules()
            ),
            Personne::passwordMessages()
        );

        Personne::create([
            'nom'         => $request->nom,
            'prenom'      => $request->prenom,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'role'        => 'manager',
            'departement' => $request->departement,
        ]);

        return redirect()->route('admin.dashboard')
                         ->with('success', 'Manager créé avec succès.');
    }

    public function show($id)
    {
        $personne = Personne::findOrFail($id);
        return view('personnes.show', compact('personne'));
    }

    public function edit($id)
    {
        $personne = Personne::findOrFail($id);
        return view('personnes.edit', compact('personne'));
    }

    /**
     * Modification désactivée : depuis cette page, l'admin peut seulement créer
     * ou supprimer des comptes manager, pas les modifier ni toucher aux autres rôles.
     */
    public function update(Request $request, $id)
    {
        abort(403, "La modification de compte n'est pas autorisée depuis cette page.");
    }

    public function destroy($id)
    {
        $personne = Personne::findOrFail($id);

        if ($personne->id === auth()->id()) {
            return redirect()->route('admin.dashboard')
                             ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        // L'admin ne peut supprimer que des comptes manager (RH/candidat/admin sont protégés
        // depuis cette page, même si l'ID est deviné/forcé dans l'URL).
        if ($personne->role !== 'manager') {
            return redirect()->route('admin.dashboard')
                             ->with('error', "Seuls les comptes manager peuvent être supprimés depuis cette page.");
        }

        $personne->delete();

        return redirect()->route('admin.dashboard')
                         ->with('success', 'Manager supprimé avec succès.');
    }
}