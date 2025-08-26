<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EnergyCooperative;
use App\Models\User;
use App\Models\Organization;
use Carbon\Carbon;

class EnergyCooperativeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('⚡ Creando cooperativas energéticas españolas de Aragón...');

        // Obtener usuarios y organizaciones disponibles
        $users = User::all();
        $organizations = Organization::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles. Ejecuta RolesAndAdminSeeder primero.');
            return;
        }

        if ($organizations->isEmpty()) {
            $this->command->error('❌ No hay organizaciones disponibles. Ejecuta AppSettingSeeder primero.');
            return;
        }

        // 1. Cooperativas Principales de Aragón
        $this->createMainAragonCooperatives($users, $organizations);

        // 2. Cooperativas por Provincia
        $this->createProvincialCooperatives($users, $organizations);

        // 3. Cooperativas Especializadas
        $this->createSpecializedCooperatives($users, $organizations);

        // 4. Cooperativas Comunitarias
        $this->createCommunityCooperatives($users, $organizations);

        $this->command->info('✅ EnergyCooperativeSeeder completado. Se crearon ' . EnergyCooperative::count() . ' cooperativas energéticas españolas.');
    }

    /**
     * Crear cooperativas principales de Aragón
     */
    private function createMainAragonCooperatives($users, $organizations): void
    {
        $this->command->info('🏔️ Creando cooperativas principales de Aragón...');

        $mainCooperatives = [
            [
                'name' => 'Cooperativa Energética del Ebro',
                'code' => 'EBRO001',
                'description' => 'Cooperativa líder en energía renovable del Valle del Ebro, especializada en energía solar y eólica.',
                'mission_statement' => 'Democratizar el acceso a la energía renovable en el Valle del Ebro, promoviendo la sostenibilidad y la autonomía energética.',
                'vision_statement' => 'Ser la referencia en energía cooperativa en Aragón, liderando la transición energética hacia un futuro 100% renovable.',
                'city' => 'Zaragoza',
                'state_province' => 'Zaragoza',
                'postal_code' => '50001',
                'latitude' => 41.6488,
                'longitude' => -0.8891,
                'energy_types' => ['solar', 'eolic', 'hydro'],
                'total_capacity_kw' => 2500,
                'available_capacity_kw' => 800,
                'max_members' => 500,
                'current_members' => 320,
                'status' => 'active',
                'open_enrollment' => true,
                'allows_energy_sharing' => true,
                'allows_trading' => true,
                'sharing_fee_percentage' => 2.5,
                'membership_fee' => 50.00,
                'membership_fee_frequency' => 'annual',
                'currency' => 'EUR',
                'payment_methods' => ['bank_transfer', 'credit_card', 'paypal'],
                'timezone' => 'Europe/Madrid',
                'language' => 'es',
                'is_featured' => true,
                'visibility_level' => 5,
            ],
            [
                'name' => 'Cooperativa Solar de las Tres Provincias',
                'code' => 'TRI001',
                'description' => 'Cooperativa que une a las tres provincias aragonesas en un proyecto común de energía solar.',
                'mission_statement' => 'Unir a Aragón en la transición energética a través de proyectos solares cooperativos.',
                'vision_statement' => 'Crear una red solar cooperativa que cubra todo Aragón.',
                'city' => 'Huesca',
                'state_province' => 'Huesca',
                'postal_code' => '22001',
                'latitude' => 42.1401,
                'longitude' => -0.4087,
                'energy_types' => ['solar'],
                'total_capacity_kw' => 1800,
                'available_capacity_kw' => 600,
                'max_members' => 400,
                'current_members' => 280,
                'status' => 'active',
                'open_enrollment' => true,
                'allows_energy_sharing' => true,
                'allows_trading' => false,
                'sharing_fee_percentage' => 1.8,
                'membership_fee' => 45.00,
                'membership_fee_frequency' => 'annual',
                'currency' => 'EUR',
                'payment_methods' => ['bank_transfer', 'credit_card'],
                'timezone' => 'Europe/Madrid',
                'language' => 'es',
                'is_featured' => true,
                'visibility_level' => 4,
            ],
        ];

        foreach ($mainCooperatives as $coopData) {
            $founder = $users->random();
            $admin = $users->random();
            $organization = $organizations->random();

            EnergyCooperative::updateOrCreate(
                ['code' => $coopData['code']],
                array_merge($coopData, [
                    'founder_id' => $founder->id,
                    'administrator_id' => $admin->id,
                    'founded_date' => Carbon::now()->subYears(rand(2, 5)),
                    'registration_date' => Carbon::now()->subYears(rand(1, 4)),
                    'activation_date' => Carbon::now()->subYears(rand(1, 3)),
                    'verified_at' => Carbon::now()->subMonths(rand(1, 12)),
                    'verified_by' => $users->random()->id,
                    'last_activity_at' => Carbon::now()->subDays(rand(1, 30)),
                ])
            );
        }
    }

    /**
     * Crear cooperativas por provincia
     */
    private function createProvincialCooperatives($users, $organizations): void
    {
        $this->command->info('🏘️ Creando cooperativas por provincia...');

        $provincialCooperatives = [
            [
                'name' => 'Cooperativa Solar de Zaragoza',
                'code' => 'ZARA001',
                'description' => 'Cooperativa solar especializada en instalaciones residenciales y comerciales en Zaragoza.',
                'city' => 'Zaragoza',
                'state_province' => 'Zaragoza',
                'postal_code' => '50002',
                'latitude' => 41.6488,
                'longitude' => -0.8891,
                'energy_types' => ['solar'],
                'total_capacity_kw' => 1200,
                'available_capacity_kw' => 400,
                'max_members' => 300,
                'current_members' => 180,
                'status' => 'active',
                'open_enrollment' => true,
                'allows_energy_sharing' => true,
                'allows_trading' => true,
                'sharing_fee_percentage' => 2.0,
                'membership_fee' => 40.00,
                'membership_fee_frequency' => 'annual',
            ],
            [
                'name' => 'Cooperativa Eólica de Huesca',
                'code' => 'HUES001',
                'description' => 'Cooperativa especializada en energía eólica en la provincia de Huesca.',
                'city' => 'Huesca',
                'state_province' => 'Huesca',
                'postal_code' => '22002',
                'latitude' => 42.1401,
                'longitude' => -0.4087,
                'energy_types' => ['eolic'],
                'total_capacity_kw' => 800,
                'available_capacity_kw' => 250,
                'max_members' => 200,
                'current_members' => 120,
                'status' => 'active',
                'open_enrollment' => true,
                'allows_energy_sharing' => true,
                'allows_trading' => false,
                'sharing_fee_percentage' => 1.5,
                'membership_fee' => 35.00,
                'membership_fee_frequency' => 'annual',
            ],
            [
                'name' => 'Cooperativa Hidroeléctrica de Teruel',
                'code' => 'TERU001',
                'description' => 'Cooperativa especializada en energía hidroeléctrica en la provincia de Teruel.',
                'city' => 'Teruel',
                'state_province' => 'Teruel',
                'postal_code' => '44001',
                'latitude' => 40.3456,
                'longitude' => -1.1065,
                'energy_types' => ['hydro'],
                'total_capacity_kw' => 600,
                'available_capacity_kw' => 200,
                'max_members' => 150,
                'current_members' => 90,
                'status' => 'active',
                'open_enrollment' => true,
                'allows_energy_sharing' => true,
                'allows_trading' => false,
                'sharing_fee_percentage' => 1.2,
                'membership_fee' => 30.00,
                'membership_frequency' => 'annual',
            ],
        ];

        foreach ($provincialCooperatives as $coopData) {
            $founder = $users->random();
            $admin = $users->random();

            EnergyCooperative::updateOrCreate(
                ['code' => $coopData['code']],
                array_merge($coopData, [
                    'founder_id' => $founder->id,
                    'administrator_id' => $admin->id,
                    'founded_date' => Carbon::now()->subYears(rand(1, 3)),
                    'registration_date' => Carbon::now()->subYears(rand(1, 2)),
                    'activation_date' => Carbon::now()->subYears(rand(1, 2)),
                    'verified_at' => Carbon::now()->subMonths(rand(1, 6)),
                    'verified_by' => $users->random()->id,
                    'last_activity_at' => Carbon::now()->subDays(rand(1, 15)),
                    'currency' => 'EUR',
                    'payment_methods' => ['bank_transfer', 'credit_card'],
                    'timezone' => 'Europe/Madrid',
                    'language' => 'es',
                    'is_featured' => false,
                    'visibility_level' => 3,
                ])
            );
        }
    }

    /**
     * Crear cooperativas especializadas
     */
    private function createSpecializedCooperatives($users, $organizations): void
    {
        $this->command->info('🎯 Creando cooperativas especializadas...');

        $specializedCooperatives = [
            [
                'name' => 'Cooperativa de Autoconsumo Solar',
                'code' => 'AUTO001',
                'description' => 'Cooperativa especializada en sistemas de autoconsumo solar residencial.',
                'city' => 'Zaragoza',
                'state_province' => 'Zaragoza',
                'postal_code' => '50003',
                'latitude' => 41.6488,
                'longitude' => -0.8891,
                'energy_types' => ['solar'],
                'total_capacity_kw' => 900,
                'available_capacity_kw' => 300,
                'max_members' => 250,
                'current_members' => 160,
                'status' => 'active',
                'open_enrollment' => true,
                'allows_energy_sharing' => false,
                'allows_trading' => false,
                'membership_fee' => 60.00,
                'membership_fee_frequency' => 'annual',
            ],
            [
                'name' => 'Cooperativa de Energía Comunitaria',
                'code' => 'COMU001',
                'description' => 'Cooperativa que promueve la energía comunitaria y el empoderamiento energético.',
                'city' => 'Huesca',
                'state_province' => 'Huesca',
                'postal_code' => '22003',
                'latitude' => 42.1401,
                'longitude' => -0.4087,
                'energy_types' => ['solar', 'eolic'],
                'total_capacity_kw' => 700,
                'available_capacity_kw' => 250,
                'max_members' => 180,
                'current_members' => 110,
                'status' => 'active',
                'open_enrollment' => true,
                'allows_energy_sharing' => true,
                'allows_trading' => true,
                'sharing_fee_percentage' => 1.0,
                'membership_fee' => 25.00,
                'membership_fee_frequency' => 'annual',
            ],
        ];

        foreach ($specializedCooperatives as $coopData) {
            $founder = $users->random();
            $admin = $users->random();

            EnergyCooperative::updateOrCreate(
                ['code' => $coopData['code']],
                array_merge($coopData, [
                    'founder_id' => $founder->id,
                    'administrator_id' => $admin->id,
                    'founded_date' => Carbon::now()->subYears(rand(1, 2)),
                    'registration_date' => Carbon::now()->subYears(rand(1, 2)),
                    'activation_date' => Carbon::now()->subYears(rand(1, 2)),
                    'verified_at' => Carbon::now()->subMonths(rand(1, 3)),
                    'verified_by' => $users->random()->id,
                    'last_activity_at' => Carbon::now()->subDays(rand(1, 10)),
                    'currency' => 'EUR',
                    'payment_methods' => ['bank_transfer'],
                    'timezone' => 'Europe/Madrid',
                    'language' => 'es',
                    'is_featured' => false,
                    'visibility_level' => 2,
                ])
            );
        }
    }

    /**
     * Crear cooperativas comunitarias
     */
    private function createCommunityCooperatives($users, $organizations): void
    {
        $this->command->info('🤝 Creando cooperativas comunitarias...');

        $communityCooperatives = [
            [
                'name' => 'Cooperativa del Barrio Verde',
                'code' => 'BARV001',
                'description' => 'Cooperativa de barrio que promueve la energía verde a nivel local.',
                'city' => 'Zaragoza',
                'state_province' => 'Zaragoza',
                'postal_code' => '50004',
                'latitude' => 41.6488,
                'longitude' => -0.8891,
                'energy_types' => ['solar'],
                'total_capacity_kw' => 400,
                'available_capacity_kw' => 150,
                'max_members' => 100,
                'current_members' => 65,
                'status' => 'active',
                'open_enrollment' => true,
                'allows_energy_sharing' => true,
                'allows_trading' => false,
                'sharing_fee_percentage' => 0.5,
                'membership_fee' => 20.00,
                'membership_fee_frequency' => 'annual',
            ],
            [
                'name' => 'Cooperativa Rural de Energía',
                'code' => 'RURA001',
                'description' => 'Cooperativa que une a comunidades rurales en proyectos energéticos.',
                'city' => 'Teruel',
                'state_province' => 'Teruel',
                'postal_code' => '44002',
                'latitude' => 40.3456,
                'longitude' => -1.1065,
                'energy_types' => ['solar', 'biomass'],
                'total_capacity_kw' => 300,
                'available_capacity_kw' => 100,
                'max_members' => 80,
                'current_members' => 45,
                'status' => 'active',
                'open_enrollment' => true,
                'allows_energy_sharing' => true,
                'allows_trading' => false,
                'sharing_fee_percentage' => 0.8,
                'membership_fee' => 15.00,
                'membership_fee_frequency' => 'annual',
            ],
        ];

        foreach ($communityCooperatives as $coopData) {
            $founder = $users->random();
            $admin = $users->random();

            EnergyCooperative::updateOrCreate(
                ['code' => $coopData['code']],
                array_merge($coopData, [
                    'founder_id' => $founder->id,
                    'administrator_id' => $admin->id,
                    'founded_date' => Carbon::now()->subYears(rand(1, 2)),
                    'registration_date' => Carbon::now()->subYears(rand(1, 2)),
                    'activation_date' => Carbon::now()->subYears(rand(1, 2)),
                    'verified_at' => Carbon::now()->subMonths(rand(1, 2)),
                    'verified_by' => $users->random()->id,
                    'last_activity_at' => Carbon::now()->subDays(rand(1, 7)),
                    'currency' => 'EUR',
                    'payment_methods' => ['bank_transfer'],
                    'timezone' => 'Europe/Madrid',
                    'language' => 'es',
                    'is_featured' => false,
                    'visibility_level' => 1,
                ])
            );
        }
    }
}
