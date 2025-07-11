<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSomeCondition
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user() || ! $request->user()->hasPermissionTo('acceso-especial')) {
            abort(403, 'No autorizado.');
        }

        return $next($request);
    }
}
