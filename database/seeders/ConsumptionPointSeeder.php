<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConsumptionPoint;
use App\Models\User;
use App\Models\EnergyInstallation;
use Carbon\Carbon;

class ConsumptionPointSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('⚡ Creando puntos de consumo energético...');

        $users = User::all();
        $installations = EnergyInstallation::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles.');
            return;
        }

        // 1. Puntos Residenciales
        $this->createResidentialPoints($users, $installations);

        // 2. Puntos Comerciales
        $this->createCommercialPoints($users, $installations);

        // 3. Puntos Industriales
        $this->createIndustrialPoints($users, $installations);

        // 4. Puntos Públicos
        $this->createPublicPoints($users, $installations);

        // 5. Puntos de Alumbrado
        $this->createStreetLightingPoints($users, $installations);

        // 6. Estaciones de Carga
        $this->createChargingStations($users, $installations);

        $this->command->info('✅ Seeder completado. Total: ' . ConsumptionPoint::count() . ' puntos.');
    }

    private function createResidentialPoints($users, $installations): void
    {
        $this->command->info('🏠 Creando puntos residenciales...');

        $points = [
            [
                'point_number' => 'RES-001',
                'name' => 'Residencia Villa del Sol',
                'description' => 'Punto de consumo para vivienda unifamiliar',
                'point_type' => 'residential',
                'status' => 'active',
                'location_address' => 'Calle del Sol 123, Valencia',
                'latitude' => 39.4699,
                'longitude' => -0.3763,
                'peak_demand_kw' => 8.5,
                'average_demand_kw' => 4.2,
                'annual_consumption_kwh' => 3500.00,
                'connection_date' => Carbon::now()->subMonths(6),
                'meter_number' => 'MTR-RES-001',
                'meter_type' => 'smart',
                'voltage_level' => 230.00,
                'consumption_type' => 'basic',
                'supply_type' => 'single_phase',
                'connection_type' => 'underground',
                'remote_reading_enabled' => true,
            ],
            [
                'point_number' => 'RES-002',
                'name' => 'Apartamento Centro',
                'description' => 'Punto de consumo para apartamento céntrico',
                'point_type' => 'residential',
                'status' => 'active',
                'location_address' => 'Plaza Mayor 45, Madrid',
                'latitude' => 40.4168,
                'longitude' => -3.7038,
                'peak_demand_kw' => 5.5,
                'average_demand_kw' => 2.8,
                'annual_consumption_kwh' => 2400.00,
                'connection_date' => Carbon::now()->subMonths(12),
                'meter_number' => 'MTR-RES-002',
                'meter_type' => 'digital',
                'voltage_level' => 220.00,
                'consumption_type' => 'basic',
                'supply_type' => 'single_phase',
                'connection_type' => 'underground',
                'remote_reading_enabled' => false,
            ],
        ];

        foreach ($points as $pointData) {
            $this->createPoint($pointData, $users, $installations);
        }

        // Crear puntos adicionales con factory
        for ($i = 0; $i < 15; $i++) {
            $point = ConsumptionPoint::create([
                'point_number' => 'RES-' . str_pad($i + 3, 3, '0', STR_PAD_LEFT),
                'name' => 'Residencia ' . fake()->streetName,
                'description' => fake()->optional(0.7)->sentence,
                'point_type' => 'residential',
                'status' => fake()->randomElement(['active', 'inactive', 'maintenance']),
                'customer_id' => $users->random()->id,
                'installation_id' => $installations->isEmpty() ? null : $installations->random()->id,
                'location_address' => fake()->address,
                'latitude' => fake()->latitude(35, 45),
                'longitude' => fake()->longitude(-10, 5),
                'peak_demand_kw' => fake()->randomFloat(1, 3, 15),
                'average_demand_kw' => fake()->randomFloat(1, 1.5, 8),
                'annual_consumption_kwh' => fake()->randomFloat(0, 1500, 6000),
                'connection_date' => Carbon::now()->subDays(rand(30, 730)),
                'meter_number' => 'MTR-RES-' . fake()->unique()->numberBetween(1000, 9999),
                'meter_type' => fake()->randomElement(['smart', 'digital', 'analog']),
                'voltage_level' => fake()->randomElement([220, 230]),
                'consumption_type' => fake()->randomElement(['basic', 'intermediate']),
                'supply_type' => 'single_phase',
                'connection_type' => fake()->randomElement(['underground', 'overhead']),
                'remote_reading_enabled' => fake()->boolean(60),
                'managed_by' => $users->random()->id,
                'created_by' => $users->random()->id,
            ]);
            $this->command->line("   ✅ Punto residencial: {$point->name}");
        }
    }

    private function createCommercialPoints($users, $installations): void
    {
        $this->command->info('🏢 Creando puntos comerciales...');

        $points = [
            [
                'point_number' => 'COM-001',
                'name' => 'Centro Comercial Plaza Norte',
                'description' => 'Punto de consumo para centro comercial',
                'point_type' => 'commercial',
                'status' => 'active',
                'location_address' => 'Avenida Comercial 200, Barcelona',
                'latitude' => 41.3851,
                'longitude' => 2.1734,
                'peak_demand_kw' => 85.0,
                'average_demand_kw' => 42.5,
                'annual_consumption_kwh' => 125000.00,
                'connection_date' => Carbon::now()->subYear(),
                'meter_number' => 'MTR-COM-001',
                'meter_type' => 'smart',
                'voltage_level' => 400.00,
                'consumption_type' => 'high',
                'supply_type' => 'three_phase',
                'connection_type' => 'underground',
                'remote_reading_enabled' => true,
            ],
        ];

        foreach ($points as $pointData) {
            $this->createPoint($pointData, $users, $installations);
        }

        // Crear puntos comerciales adicionales
        for ($i = 0; $i < 10; $i++) {
            $point = ConsumptionPoint::create([
                'point_number' => 'COM-' . str_pad($i + 2, 3, '0', STR_PAD_LEFT),
                'name' => 'Comercio ' . fake()->company,
                'description' => fake()->optional(0.8)->sentence,
                'point_type' => 'commercial',
                'status' => fake()->randomElement(['active', 'inactive', 'maintenance']),
                'customer_id' => $users->random()->id,
                'installation_id' => $installations->isEmpty() ? null : $installations->random()->id,
                'location_address' => fake()->address,
                'latitude' => fake()->latitude(35, 45),
                'longitude' => fake()->longitude(-10, 5),
                'peak_demand_kw' => fake()->randomFloat(1, 20, 120),
                'average_demand_kw' => fake()->randomFloat(1, 10, 60),
                'annual_consumption_kwh' => fake()->randomFloat(0, 15000, 80000),
                'connection_date' => Carbon::now()->subDays(rand(30, 1095)),
                'meter_number' => 'MTR-COM-' . fake()->unique()->numberBetween(1000, 9999),
                'meter_type' => fake()->randomElement(['smart', 'digital']),
                'voltage_level' => fake()->randomElement([230, 380, 400]),
                'consumption_type' => fake()->randomElement(['intermediate', 'high']),
                'supply_type' => fake()->randomElement(['single_phase', 'three_phase']),
                'connection_type' => fake()->randomElement(['underground', 'overhead']),
                'remote_reading_enabled' => fake()->boolean(80),
                'managed_by' => $users->random()->id,
                'created_by' => $users->random()->id,
            ]);
            $this->command->line("   ✅ Punto comercial: {$point->name}");
        }
    }

    private function createIndustrialPoints($users, $installations): void
    {
        $this->command->info('🏭 Creando puntos industriales...');

        $points = [
            [
                'point_number' => 'IND-001',
                'name' => 'Planta Industrial MetalTech',
                'description' => 'Punto de consumo para planta de manufactura',
                'point_type' => 'industrial',
                'status' => 'active',
                'location_address' => 'Polígono Industrial Norte, Sector 5',
                'latitude' => 40.5000,
                'longitude' => -3.5000,
                'peak_demand_kw' => 450.0,
                'average_demand_kw' => 280.0,
                'annual_consumption_kwh' => 850000.00,
                'connection_date' => Carbon::now()->subYears(2),
                'meter_number' => 'MTR-IND-001',
                'meter_type' => 'smart',
                'voltage_level' => 660.00,
                'consumption_type' => 'industrial',
                'supply_type' => 'three_phase',
                'connection_type' => 'overhead',
                'remote_reading_enabled' => true,
            ],
        ];

        foreach ($points as $pointData) {
            $this->createPoint($pointData, $users, $installations);
        }

        // Crear puntos industriales adicionales
        for ($i = 0; $i < 6; $i++) {
            $point = ConsumptionPoint::create([
                'point_number' => 'IND-' . str_pad($i + 2, 3, '0', STR_PAD_LEFT),
                'name' => 'Industria ' . fake()->company,
                'description' => fake()->optional(0.9)->sentence,
                'point_type' => 'industrial',
                'status' => fake()->randomElement(['active', 'maintenance', 'planned']),
                'customer_id' => $users->random()->id,
                'installation_id' => $installations->isEmpty() ? null : $installations->random()->id,
                'location_address' => fake()->address,
                'latitude' => fake()->latitude(35, 45),
                'longitude' => fake()->longitude(-10, 5),
                'peak_demand_kw' => fake()->randomFloat(1, 100, 800),
                'average_demand_kw' => fake()->randomFloat(1, 60, 500),
                'annual_consumption_kwh' => fake()->randomFloat(0, 200000, 1200000),
                'connection_date' => Carbon::now()->subDays(rand(90, 1825)),
                'meter_number' => 'MTR-IND-' . fake()->unique()->numberBetween(1000, 9999),
                'meter_type' => 'smart',
                'voltage_level' => fake()->randomElement([380, 400, 660]),
                'consumption_type' => 'industrial',
                'supply_type' => 'three_phase',
                'connection_type' => fake()->randomElement(['overhead', 'underground']),
                'remote_reading_enabled' => true,
                'managed_by' => $users->random()->id,
                'created_by' => $users->random()->id,
            ]);
            $this->command->line("   ✅ Punto industrial: {$point->name}");
        }
    }

    private function createPublicPoints($users, $installations): void
    {
        $this->command->info('🏛️ Creando puntos públicos...');

        $points = [
            [
                'point_number' => 'PUB-001',
                'name' => 'Ayuntamiento de Valencia',
                'description' => 'Punto de consumo para edificio municipal',
                'point_type' => 'public',
                'status' => 'active',
                'location_address' => 'Plaza del Ayuntamiento 1, Valencia',
                'latitude' => 39.4699,
                'longitude' => -0.3763,
                'peak_demand_kw' => 65.0,
                'average_demand_kw' => 35.0,
                'annual_consumption_kwh' => 95000.00,
                'connection_date' => Carbon::now()->subYears(5),
                'meter_number' => 'MTR-PUB-001',
                'meter_type' => 'digital',
                'voltage_level' => 400.00,
                'consumption_type' => 'high',
                'supply_type' => 'three_phase',
                'connection_type' => 'underground',
                'remote_reading_enabled' => true,
            ],
        ];

        foreach ($points as $pointData) {
            $this->createPoint($pointData, $users, $installations);
        }

        // Crear puntos públicos adicionales
        for ($i = 0; $i < 8; $i++) {
            $point = ConsumptionPoint::create([
                'point_number' => 'PUB-' . str_pad($i + 2, 3, '0', STR_PAD_LEFT),
                'name' => fake()->randomElement(['Hospital', 'Escuela', 'Biblioteca', 'Centro Deportivo', 'Parque']) . ' ' . fake()->lastName,
                'description' => fake()->optional(0.8)->sentence,
                'point_type' => 'public',
                'status' => fake()->randomElement(['active', 'maintenance']),
                'customer_id' => $users->random()->id,
                'installation_id' => $installations->isEmpty() ? null : $installations->random()->id,
                'location_address' => fake()->address,
                'latitude' => fake()->latitude(35, 45),
                'longitude' => fake()->longitude(-10, 5),
                'peak_demand_kw' => fake()->randomFloat(1, 30, 150),
                'average_demand_kw' => fake()->randomFloat(1, 15, 75),
                'annual_consumption_kwh' => fake()->randomFloat(0, 25000, 120000),
                'connection_date' => Carbon::now()->subDays(rand(365, 2555)),
                'meter_number' => 'MTR-PUB-' . fake()->unique()->numberBetween(1000, 9999),
                'meter_type' => fake()->randomElement(['smart', 'digital']),
                'voltage_level' => fake()->randomElement([230, 380, 400]),
                'consumption_type' => fake()->randomElement(['intermediate', 'high']),
                'supply_type' => fake()->randomElement(['single_phase', 'three_phase']),
                'connection_type' => 'underground',
                'remote_reading_enabled' => fake()->boolean(70),
                'managed_by' => $users->random()->id,
                'created_by' => $users->random()->id,
            ]);
            $this->command->line("   ✅ Punto público: {$point->name}");
        }
    }

    private function createStreetLightingPoints($users, $installations): void
    {
        $this->command->info('💡 Creando puntos de alumbrado...');

        for ($i = 0; $i < 12; $i++) {
            $point = ConsumptionPoint::create([
                'point_number' => 'STL-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'name' => 'Alumbrado ' . fake()->streetName,
                'description' => 'Punto de consumo para alumbrado público',
                'point_type' => 'street_lighting',
                'status' => fake()->randomElement(['active', 'maintenance', 'inactive']),
                'customer_id' => $users->random()->id,
                'installation_id' => $installations->isEmpty() ? null : $installations->random()->id,
                'location_address' => fake()->streetAddress,
                'latitude' => fake()->latitude(35, 45),
                'longitude' => fake()->longitude(-10, 5),
                'peak_demand_kw' => fake()->randomFloat(1, 5, 25),
                'average_demand_kw' => fake()->randomFloat(1, 2, 12),
                'annual_consumption_kwh' => fake()->randomFloat(0, 3000, 15000),
                'connection_date' => Carbon::now()->subDays(rand(30, 1095)),
                'meter_number' => 'MTR-STL-' . fake()->unique()->numberBetween(1000, 9999),
                'meter_type' => fake()->randomElement(['digital', 'analog']),
                'voltage_level' => fake()->randomElement([220, 230]),
                'consumption_type' => 'basic',
                'supply_type' => 'single_phase',
                'connection_type' => fake()->randomElement(['overhead', 'underground']),
                'remote_reading_enabled' => fake()->boolean(40),
                'managed_by' => $users->random()->id,
                'created_by' => $users->random()->id,
            ]);
            $this->command->line("   ✅ Punto alumbrado: {$point->name}");
        }
    }

    private function createChargingStations($users, $installations): void
    {
        $this->command->info('🔌 Creando estaciones de carga...');

        for ($i = 0; $i < 8; $i++) {
            $point = ConsumptionPoint::create([
                'point_number' => 'CHG-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'name' => 'Estación de Carga ' . fake()->lastName,
                'description' => 'Punto de consumo para vehículos eléctricos',
                'point_type' => 'charging_station',
                'status' => fake()->randomElement(['active', 'planned', 'maintenance']),
                'customer_id' => $users->random()->id,
                'installation_id' => $installations->isEmpty() ? null : $installations->random()->id,
                'location_address' => fake()->address,
                'latitude' => fake()->latitude(35, 45),
                'longitude' => fake()->longitude(-10, 5),
                'peak_demand_kw' => fake()->randomFloat(1, 22, 150),
                'average_demand_kw' => fake()->randomFloat(1, 10, 75),
                'annual_consumption_kwh' => fake()->randomFloat(0, 15000, 85000),
                'connection_date' => Carbon::now()->subDays(rand(30, 365)),
                'meter_number' => 'MTR-CHG-' . fake()->unique()->numberBetween(1000, 9999),
                'meter_type' => 'smart',
                'voltage_level' => fake()->randomElement([230, 400]),
                'consumption_type' => fake()->randomElement(['intermediate', 'high']),
                'supply_type' => fake()->randomElement(['single_phase', 'three_phase']),
                'connection_type' => 'underground',
                'remote_reading_enabled' => true,
                'managed_by' => $users->random()->id,
                'created_by' => $users->random()->id,
            ]);
            $this->command->line("   ✅ Estación de carga: {$point->name}");
        }
    }

    private function createPoint(array $data, $users, $installations): void
    {
        $data['customer_id'] = $users->random()->id;
        $data['installation_id'] = $installations->isEmpty() ? null : $installations->random()->id;
        $data['managed_by'] = $users->random()->id;
        $data['created_by'] = $users->random()->id;

        $point = ConsumptionPoint::create($data);
        $this->command->line("   ✅ Punto creado: {$point->name} ({$point->point_type})");
    }
}
