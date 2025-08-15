<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AppSetting;
use App\Models\Organization;

class LoadAppSettings
{
    public function handle(Request $request, Closure $next): Response
    {
        // Detect organization from request
        $organizationId = $this->detectOrganization($request);
        
        // Load settings for the detected organization
        AppSetting::loadIntoConfig($organizationId);
        
        // Get the settings collection for view sharing
        $settings = AppSetting::forOrg($organizationId);
        
        // Set commonly used config values
        config()->set('app.name', $settings->get('name', config('app.name')));
        config()->set('app.locale', $settings->get('locale', config('app.locale')));
        
        // Share settings with views
        view()->share('appSettings', $settings);
        
        return $next($request);
    }
    
    /**
     * Detect organization from various sources
     */
    private function detectOrganization(Request $request): ?int
    {
        // Method 1: From subdomain
        $host = $request->getHost();
        if ($host !== config('app.main_domain', 'localhost')) {
            $subdomain = explode('.', $host)[0];
            $org = Organization::where('slug', $subdomain)->first();
            if ($org) {
                return $org->id;
            }
        }
        
        // Method 2: From domain mapping
        $org = Organization::where('domain', $host)->first();
        if ($org) {
            return $org->id;
        }
        
        // Method 3: From URL parameter
        if ($request->has('org')) {
            $orgSlug = $request->get('org');
            $org = Organization::where('slug', $orgSlug)->first();
            if ($org) {
                return $org->id;
            }
        }
        
        // Method 4: From authenticated user's organization
        if ($request->user() && $request->user()->organization_id) {
            return $request->user()->organization_id;
        }
        
        // Method 5: From session
        if ($request->session()->has('organization_id')) {
            return $request->session()->get('organization_id');
        }
        
        return null; // Global settings
    }
}
