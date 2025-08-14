<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ejecutar seeders después de las migraciones
        $this->seed([
            \Database\Seeders\RolesAndPermissionsSeeder::class,
            \Database\Seeders\RolesAndAdminSeeder::class,
            \Database\Seeders\AppSettingSeeder::class,
        ]);
    }
}
