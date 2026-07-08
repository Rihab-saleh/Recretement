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
        $request->validate(
            array_merge(
                [
                    'nom'    => 'required|string|max:255',
                    'prenom' => 'required|string|max:255',
                    'email'  => 'required|email|unique:personnes,email',
                    'role'   => 'required|in:manager,candidat,rh',
                ],
                Personne::passwordRules()
            ),
            Personne::passwordMessages()
        );

        $personne = Personne::create([
    'nom'         => $request->nom,
    'prenom'      => $request->prenom,
    'email'       => $request->email,
    'password'    => Hash::make($request->password),
    'role'        => $request->role,
    'departement' => $request->role === 'manager' ? $request->departement : null,
]);

        return redirect()->route('personnes.index')
                         ->with('success', 'Personne créée avec succès.');
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
        $personne = Personne::findOrFail($id);

        $rules = [
            'nom'    => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email'  => 'required|email|unique:personnes,email,' . $id,
            'role'   => 'required|in:manager,candidat,rh',
        ];

        if ($request->filled('password')) {
            $rules = array_merge($rules, Personne::passwordRules());
        }

        $request->validate($rules, Personne::passwordMessages());

        $data = [
            'nom'    => $request->nom,
            'prenom' => $request->prenom,
            'email'  => $request->email,
            'role'   => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $personne->update($data);

        return redirect()->route('personnes.index')
                         ->with('success', 'Personne mise à jour avec succès.');
    }

    public function destroy($id)
    {
        $personne = Personne::findOrFail($id);
        $personne->delete();

        return redirect()->route('personnes.index')
                         ->with('success', 'Personne supprimée avec succès.');
    }
}