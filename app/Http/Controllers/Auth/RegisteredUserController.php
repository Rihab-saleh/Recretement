<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Personne;
use App\Models\Candidat;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $rules = [
            'nom'    => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email'  => 'required|email|unique:personnes,email',
        ];

        $request->validate(
            array_merge($rules, Personne::passwordRules()),
            Personne::passwordMessages()
        );

        // L'inscription publique ne crée que des comptes candidat.
        // Les comptes manager / rh / admin sont créés depuis les
        // tableaux de bord admin / super-admin, jamais via ce formulaire.
        $personne = Personne::create([
            'nom'      => $request->nom,
            'prenom'   => $request->prenom,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'candidat',
        ]);

        event(new Registered($personne));

        Candidat::create([
            'personne_id' => $personne->id,
        ]);

        Auth::login($personne);

        return redirect()->route('candidat.dashboard');
    }
}