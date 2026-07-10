<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();

        $personne = Auth::user();

        if ($personne->isManager()) {
            return redirect()->route('manager.dashboard');
        }
        if ($personne->isCandidat()) {
            return redirect()->route('candidat.dashboard');
        }
        if ($personne->isRH()) {
            return redirect()->route('rh.dashboard');
        }
        if ($personne->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect('/');
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}