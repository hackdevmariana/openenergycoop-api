<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\AppSetting;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        // Crear organización demo
        $organization = Organization::updateOrCreate(
            ['slug' => 'demo-coop'],
            [
                'name' => 'Demo Cooperativa',
                'domain' => 'demo.local',
                'contact_email' => 'contacto@demo.local',
                'contact_phone' => '123456789',
                'css_files' => null,
                'active' => true,
            ]
        );

        // Crear configuración de la app
        AppSetting::updateOrCreate(
            ['organization_id' => $organization->id],
            [
                'name' => 'Demo Cooperativa',
                'slogan' => 'Energía cooperativa para todos',
                'primary_color' => '#0d6efd', // Estos campos van en AppSettings
                'secondary_color' => '#6610f2', // Estos campos van en AppSettings
                'locale' => 'es',
                'custom_js' => null,
            ]
        );
    }
}
