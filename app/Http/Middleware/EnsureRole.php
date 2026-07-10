<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloque l'accès à une route si l'utilisateur connecté n'a pas l'un des rôles autorisés.
 * Usage dans les routes : ->middleware('role:admin') ou ->middleware('role:admin,rh')
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $personne = $request->user();

        if (! $personne || ! in_array($personne->role, $roles, true)) {
            abort(403, "Vous n'avez pas accès à cette page.");
        }

        return $next($request);
    }
}