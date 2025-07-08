<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppSetting;
use App\Models\Organization;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        // Crear una organización ejemplo si no existe
        $organization = Organization::firstOrCreate(
            ['slug' => 'demo-coop'],
            [
                'name' => 'Demo Cooperativa',
                'domain' => 'demo.local',
                'contact_email' => 'contacto@demo.local',
                'contact_phone' => '123456789',
                'primary_color' => '#0d6efd',
                'secondary_color' => '#6610f2',
                'css_files' => null,
                'active' => true,
            ]
        );

        // Crear AppSetting para la organización
        AppSetting::updateOrCreate(
            ['organization_id' => $organization->id],
            [
                'name' => 'OpenEnergyCoop Demo',
                'slogan' => 'La cooperativa energética online',
                'primary_color' => '#0d6efd',
                'secondary_color' => '#6610f2',
                'locale' => 'es',
                'custom_js' => null,
            ]
        );
    }
}
