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

        // Crear puntos residenciales con números únicos
        for ($i = 0; $i < 17; $i++) {
            $pointNumber = 'RES-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            
            // Verificar si ya existe un punto con este número
            if (ConsumptionPoint::where('point_number', $pointNumber)->exists()) {
                $this->command->line("   ⚠️ Punto ya existe con número: {$pointNumber}");
                continue;
            }
            
            $point = ConsumptionPoint::create([
                'point_number' => $pointNumber,
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

        // Crear puntos comerciales con números únicos
        for ($i = 0; $i < 11; $i++) {
            $pointNumber = 'COM-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            
            // Verificar si ya existe un punto con este número
            if (ConsumptionPoint::where('point_number', $pointNumber)->exists()) {
                $this->command->line("   ⚠️ Punto ya existe con número: {$pointNumber}");
                continue;
            }
            
            $point = ConsumptionPoint::create([
                'point_number' => $pointNumber,
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

        // Crear puntos industriales con números únicos
        for ($i = 0; $i < 7; $i++) {
            $pointNumber = 'IND-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            
            // Verificar si ya existe un punto con este número
            if (ConsumptionPoint::where('point_number', $pointNumber)->exists()) {
                $this->command->line("   ⚠️ Punto ya existe con número: {$pointNumber}");
                continue;
            }
            
            $point = ConsumptionPoint::create([
                'point_number' => $pointNumber,
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

        // Crear puntos públicos con números únicos
        for ($i = 0; $i < 9; $i++) {
            $pointNumber = 'PUB-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            
            // Verificar si ya existe un punto con este número
            if (ConsumptionPoint::where('point_number', $pointNumber)->exists()) {
                $this->command->line("   ⚠️ Punto ya existe con número: {$pointNumber}");
                continue;
            }
            
            $point = ConsumptionPoint::create([
                'point_number' => $pointNumber,
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

        // Crear puntos de alumbrado con números únicos
        for ($i = 0; $i < 12; $i++) {
            $pointNumber = 'STL-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            
            // Verificar si ya existe un punto con este número
            if (ConsumptionPoint::where('point_number', $pointNumber)->exists()) {
                $this->command->line("   ⚠️ Punto ya existe con número: {$pointNumber}");
                continue;
            }
            
            $point = ConsumptionPoint::create([
                'point_number' => $pointNumber,
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

        // Crear estaciones de carga con números únicos
        for ($i = 0; $i < 8; $i++) {
            $pointNumber = 'CHG-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            
            // Verificar si ya existe un punto con este número
            if (ConsumptionPoint::where('point_number', $pointNumber)->exists()) {
                $this->command->line("   ⚠️ Punto ya existe con número: {$pointNumber}");
                continue;
            }
            
            $point = ConsumptionPoint::create([
                'point_number' => $pointNumber,
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

}
