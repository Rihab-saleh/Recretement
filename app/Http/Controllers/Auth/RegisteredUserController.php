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
            'role'   => 'required|in:manager,candidat,rh',
        ];

        if ($request->role === 'manager') {
            $rules['departement'] = 'required|string|max:255';
        }

        $request->validate(
            array_merge($rules, Personne::passwordRules()),
            Personne::passwordMessages()
        );

        // Debug — remove after testing
        // dd($request->all());

        $personne = Personne::create([
            'nom'         => $request->nom,
            'prenom'      => $request->prenom,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'role'        => $request->role,
            'departement' => $request->role === 'manager' ? $request->departement : null,
        ]);

        event(new Registered($personne));

        if ($personne->role === 'manager') {
            Auth::login($personne);
            return redirect()->route('manager.dashboard');
        }

        if ($personne->role === 'candidat') {
            Candidat::create([
                'personne_id' => $personne->id,
            ]);
            Auth::login($personne);
            return redirect()->route('candidat.dashboard');
        }

        if ($personne->role === 'rh') {
            Auth::login($personne);
            return redirect()->route('rh.dashboard');
        }
    }
}