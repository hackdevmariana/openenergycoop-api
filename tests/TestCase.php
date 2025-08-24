<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Comentado temporalmente para evitar problemas con la base de datos de testing
        // $this->seed([
        //     \Database\Seeders\RolesAndPermissionsSeeder::class,
        //     \Database\Seeders\RolesAndAdminSeeder::class,
        //     \Database\Seeders\AppSettingSeeder::class,
        // ]);
    }
}
