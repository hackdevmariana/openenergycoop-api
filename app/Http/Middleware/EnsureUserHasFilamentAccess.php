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

        // Si está autenticado, verificar acceso al panel
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Usuario no autenticado.');
        }

        // Verificación simple: solo usuarios con email admin@demo.com o admin@aragon.es
        $allowedEmails = ['admin@demo.com', 'admin@aragon.es'];
        
        if (!in_array($user->email, $allowedEmails)) {
            abort(403, 'No tienes acceso al panel de administración.');
        }

        // Debug: Log que el middleware pasó correctamente
        \Log::info('EnsureUserHasFilamentAccess: Usuario autorizado', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        return $next($request);
    }
}