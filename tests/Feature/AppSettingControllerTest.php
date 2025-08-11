<?php

declare(strict_types=1);

use App\Models\AppSetting;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(\Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\AppSettingSeeder::class);
});

test('GET /api/v1/app-settings retorna la configuración global', function () {
    $response = $this->getJson('/api/v1/app-settings');
    $response->assertOk();
    $response->assertJsonStructure([
        'data' => [
            'id', 'name', 'slogan', 'primary_color', 'secondary_color', 'locale', 'custom_js', 'organization', 'logo_url', 'favicon_url'
        ]
    ]);
});

test('GET /api/v1/app-settings/{id} retorna la configuración por ID', function () {
    $setting = AppSetting::first();
    $response = $this->getJson('/api/v1/app-settings/' . $setting->id);
    $response->assertOk();
    $response->assertJson(['data' => ['id' => $setting->id]]);
});

test('GET /api/v1/app-settings/{id} retorna 404 si no existe', function () {
    $response = $this->getJson('/api/v1/app-settings/999999');
    $response->assertNotFound();
});
