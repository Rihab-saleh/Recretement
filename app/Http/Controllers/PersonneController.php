<?php

namespace App\Http\Controllers;

use App\Models\Personne;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PersonneController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $personnes = $user->isAdmin()
            ? Personne::where('entreprise_id', $user->entreprise_id)->get()
            : Personne::all();

        return view('personnes.index', compact('personnes'));
    }

    public function create()
    {
        return view('personnes.create');
    }

    public function store(Request $request)
    {
        $request->validate(
            array_merge(
                [
                    'nom'         => 'required|string|max:255',
                    'prenom'      => 'required|string|max:255',
                    'email'       => 'required|email|unique:personnes,email',
                    'departement' => 'required_if:role,manager|nullable|string|max:255',
                    'role'        => 'required|in:manager,rh',
                ],
                Personne::passwordRules()
            ),
            Personne::passwordMessages()
        );

        Personne::create([
            'nom'           => $request->nom,
            'prenom'        => $request->prenom,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'role'          => $request->role,
            'departement'   => $request->role === 'manager' ? $request->departement : null,
            'entreprise_id' => auth()->user()->entreprise_id,
        ]);

        $libelle = $request->role === 'rh' ? 'Compte RH créé avec succès.' : 'Manager créé avec succès.';

        return redirect()->route('admin.dashboard')
                         ->with('success', $libelle);
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

    public function update(Request $request, $id)
    {
        abort(403, "La modification de compte n'est pas autorisée depuis cette page.");
    }

    public function destroy($id)
    {
        $personne = Personne::findOrFail($id);
        $user = auth()->user();

        if ($personne->id === $user->id) {
            return redirect()->route('admin.dashboard')
                             ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        if (! in_array($personne->role, ['manager', 'rh'], true)) {
            return redirect()->route('admin.dashboard')
                             ->with('error', "Seuls les comptes manager ou RH peuvent être supprimés depuis cette page.");
        }

        if ($user->isAdmin() && $personne->entreprise_id !== $user->entreprise_id) {
            abort(403, "Ce compte n'appartient pas à votre entreprise.");
        }

        $personne->delete();

        return redirect()->route('admin.dashboard')
                         ->with('success', ($personne->role === 'rh' ? 'Compte RH' : 'Manager') . ' supprimé avec succès.');
    }
}