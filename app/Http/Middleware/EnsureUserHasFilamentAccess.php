<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureUserHasFilamentAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        // Si el usuario no ha iniciado sesión, permitir el acceso para que vea el login
        if (! Auth::check()) {
            return $next($request);
        }

        // Si está autenticado, pero no tiene permiso, negar el acceso
        if (! Auth::user()->hasPermissionTo('access filament')) {
            abort(403, 'No tienes acceso al panel de administración.');
        }

        return $next($request);
    }
}
