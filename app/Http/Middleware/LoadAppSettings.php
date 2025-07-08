<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AppSetting;

class LoadAppSettings
{
    public function handle(Request $request, Closure $next): Response
    {
        // Solo si hay un AppSetting (puedes adaptar esto si es multitenant)
        $settings = AppSetting::with('organization')->first();

        if ($settings) {
            config()->set('app.name', $settings->name ?? config('app.name'));
            config()->set('app.locale', $settings->locale ?? config('app.locale'));
            config()->set('app.primary_color', $settings->primary_color);
            config()->set('app.secondary_color', $settings->secondary_color);

            // Opcional: compartir con vistas
            view()->share('appSettings', $settings);
        }

        return $next($request);
    }
}
