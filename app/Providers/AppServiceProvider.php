<?php

namespace App\Providers;

use App\Http\Middleware\LoadAppSettings;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }

    public function configureMiddleware(Middleware $middleware): void
    {
        $middleware->appendToGroup('web', LoadAppSettings::class);
    }
}
