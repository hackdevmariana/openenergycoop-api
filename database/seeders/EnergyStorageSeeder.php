<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EnergyStorage;
use App\Models\User;
use App\Models\EnergyInstallation;
use Carbon\Carbon;

class EnergyStorageSeeder extends Seeder
{
    private $storageCounter = 1;

    public function run(): void
    {
        $this->command->info('🔋 Creando sistemas de almacenamiento de energía...');

        $users = User::take(6)->get();
        $installations = EnergyInstallation::take(5)->get();

        if ($users->isEmpty()) {
            $this->command->warn('   ⚠️ No hay usuarios disponibles. Creando usuarios de prueba...');
            $users = User::factory(6)->create();
        }

        if ($installations->isEmpty()) {
            $this->command->warn('   ⚠️ No hay instalaciones disponibles. Saltando instalaciones...');
        }

        // Crear sistemas de almacenamiento con diferentes tipos y estados
        $storageData = [
            [
                'storage_type' => 'battery_lithium',
                'name' => 'Sistema de Baterías de Litio Residencial',
                'status' => 'online',
                'capacity_kwh' => 13.5,
                'charge_level_percentage' => 85,
                'current_health_percentage' => 95,
                'round_trip_efficiency' => 92.5,
                'max_charge_rate_kw' => 5.0,
                'max_discharge_rate_kw' => 5.0,
                'depth_of_discharge_percentage' => 90,
                'cycle_count' => 1250,
                'max_cycles' => 4000,
                'temperature_celsius' => 22.5,
                'voltage_v' => 48.0,
                'current_a' => 15.2,
                'power_factor' => 0.98,
                'location_address' => 'Calle Mayor 123, Madrid',
                'latitude' => 40.4168,
                'longitude' => -3.7038,
            ],
            [
                'storage_type' => 'battery_lead_acid',
                'name' => 'Sistema de Baterías de Plomo Comercial',
                'status' => 'charging',
                'capacity_kwh' => 50.0,
                'charge_level_percentage' => 65,
                'current_health_percentage' => 78,
                'round_trip_efficiency' => 75.0,
                'max_charge_rate_kw' => 15.0,
                'max_discharge_rate_kw' => 15.0,
                'depth_of_discharge_percentage' => 70,
                'cycle_count' => 850,
                'max_cycles' => 1500,
                'temperature_celsius' => 25.0,
                'voltage_v' => 240.0,
                'current_a' => 62.5,
                'power_factor' => 0.95,
                'location_address' => 'Polígono Industrial Norte, Barcelona',
                'latitude' => 41.3851,
                'longitude' => 2.1734,
            ],
            [
                'storage_type' => 'battery_flow',
                'name' => 'Sistema de Baterías de Flujo Industrial',
                'status' => 'standby',
                'capacity_kwh' => 200.0,
                'charge_level_percentage' => 45,
                'current_health_percentage' => 88,
                'round_trip_efficiency' => 85.0,
                'max_charge_rate_kw' => 50.0,
                'max_discharge_rate_kw' => 50.0,
                'depth_of_discharge_percentage' => 95,
                'cycle_count' => 3200,
                'max_cycles' => 10000,
                'temperature_celsius' => 30.0,
                'voltage_v' => 400.0,
                'current_a' => 125.0,
                'power_factor' => 0.92,
                'location_address' => 'Río Ebro, Zaragoza',
                'latitude' => 41.6488,
                'longitude' => -0.8891,
            ],
            [
                'storage_type' => 'pumped_hydro',
                'name' => 'Sistema de Bombeo Hidráulico',
                'status' => 'discharging',
                'capacity_kwh' => 1000.0,
                'charge_level_percentage' => 72,
                'current_health_percentage' => 92,
                'round_trip_efficiency' => 80.0,
                'max_charge_rate_kw' => 200.0,
                'max_discharge_rate_kw' => 200.0,
                'depth_of_discharge_percentage' => 85,
                'cycle_count' => 150,
                'max_cycles' => 500,
                'temperature_celsius' => 18.0,
                'voltage_v' => 11000.0,
                'current_a' => 18.2,
                'power_factor' => 0.85,
                'location_address' => 'Embalse de La Serena, Badajoz',
                'latitude' => 38.7223,
                'longitude' => -5.1445,
            ],
            [
                'storage_type' => 'compressed_air',
                'name' => 'Sistema de Aire Comprimido',
                'status' => 'maintenance',
                'capacity_kwh' => 150.0,
                'charge_level_percentage' => 30,
                'current_health_percentage' => 65,
                'round_trip_efficiency' => 70.0,
                'max_charge_rate_kw' => 30.0,
                'max_discharge_rate_kw' => 30.0,
                'depth_of_discharge_percentage' => 80,
                'cycle_count' => 450,
                'max_cycles' => 2000,
                'temperature_celsius' => 35.0,
                'voltage_v' => 400.0,
                'current_a' => 93.8,
                'power_factor' => 0.88,
                'location_address' => 'Centro Comunitario, Valencia',
                'latitude' => 39.4699,
                'longitude' => -0.3763,
            ],
            [
                'storage_type' => 'flywheel',
                'name' => 'Sistema de Volante de Inercia',
                'status' => 'online',
                'capacity_kwh' => 25.0,
                'charge_level_percentage' => 90,
                'current_health_percentage' => 96,
                'round_trip_efficiency' => 95.0,
                'max_charge_rate_kw' => 25.0,
                'max_discharge_rate_kw' => 25.0,
                'depth_of_discharge_percentage' => 95,
                'cycle_count' => 15000,
                'max_cycles' => 50000,
                'temperature_celsius' => 28.0,
                'voltage_v' => 400.0,
                'current_a' => 15.6,
                'power_factor' => 0.99,
                'location_address' => 'Campus Universitario, Sevilla',
                'latitude' => 37.3891,
                'longitude' => -5.9845,
            ],
            [
                'storage_type' => 'thermal',
                'name' => 'Sistema de Almacenamiento Térmico',
                'status' => 'offline',
                'capacity_kwh' => 75.0,
                'charge_level_percentage' => 0,
                'current_health_percentage' => 82,
                'round_trip_efficiency' => 75.0,
                'max_charge_rate_kw' => 20.0,
                'max_discharge_rate_kw' => 20.0,
                'depth_of_discharge_percentage' => 90,
                'cycle_count' => 1200,
                'max_cycles' => 3000,
                'temperature_celsius' => 45.0,
                'voltage_v' => 400.0,
                'current_a' => 46.9,
                'power_factor' => 0.90,
                'location_address' => 'Casa Rural Aislada, Granada',
                'latitude' => 37.1765,
                'longitude' => -3.5976,
            ],
            [
                'storage_type' => 'hydrogen',
                'name' => 'Sistema de Almacenamiento de Hidrógeno',
                'status' => 'error',
                'capacity_kwh' => 500.0,
                'charge_level_percentage' => 15,
                'current_health_percentage' => 45,
                'round_trip_efficiency' => 60.0,
                'max_charge_rate_kw' => 100.0,
                'max_discharge_rate_kw' => 100.0,
                'depth_of_discharge_percentage' => 95,
                'cycle_count' => 250,
                'max_cycles' => 1000,
                'temperature_celsius' => 20.0,
                'voltage_v' => 400.0,
                'current_a' => 312.5,
                'power_factor' => 0.85,
                'location_address' => 'Nave Industrial, Bilbao',
                'latitude' => 43.2627,
                'longitude' => -2.9253,
            ],
        ];

        foreach ($storageData as $index => $data) {
            $installationDate = Carbon::now()->subMonths(rand(1, 24));
            $lastMaintenanceDate = Carbon::now()->subDays(rand(30, 365));
            $nextMaintenanceDate = $lastMaintenanceDate->copy()->addMonths(rand(6, 18));
            
            $systemId = 'STOR-' . strtoupper($data['storage_type']) . '-' . str_pad($this->storageCounter++, 4, '0', STR_PAD_LEFT);
            
            EnergyStorage::firstOrCreate(
                ['system_id' => $systemId],
                [
                'user_id' => $users->isNotEmpty() ? $users->random()->id : 1,
                'provider_id' => 1, // Usar un provider por defecto
                'system_id' => $systemId,
                'name' => $data['name'],
                'description' => fake()->paragraph(),
                'storage_type' => $data['storage_type'],
                'status' => $data['status'],
                'capacity_kwh' => $data['capacity_kwh'],
                'usable_capacity_kwh' => $data['capacity_kwh'] * 0.9,
                'current_charge_kwh' => $data['capacity_kwh'] * ($data['charge_level_percentage'] / 100),
                'charge_level_percentage' => $data['charge_level_percentage'],
                'current_health_percentage' => $data['current_health_percentage'],
                'round_trip_efficiency' => $data['round_trip_efficiency'],
                'max_charge_power_kw' => $data['max_charge_rate_kw'],
                'max_discharge_power_kw' => $data['max_discharge_rate_kw'],
                'cycle_count' => $data['cycle_count'],
                'expected_cycles' => $data['max_cycles'],
                'current_temperature' => $data['temperature_celsius'],
                'location_description' => $data['location_address'],
                'last_maintenance_date' => $lastMaintenanceDate,
                'next_maintenance_date' => $nextMaintenanceDate,
                'maintenance_interval_months' => rand(6, 24),
                'warranty_end_date' => $installationDate->copy()->addYears(rand(5, 15)),
                'installation_cost' => $data['capacity_kwh'] * fake()->randomFloat(2, 500, 2000),
                'maintenance_cost_annual' => $data['capacity_kwh'] * fake()->randomFloat(2, 50, 200),
                'technical_specifications' => json_encode([
                    'battery_chemistry' => $data['storage_type'] === 'battery_lithium' ? 'LiFePO4' : ($data['storage_type'] === 'battery_lead_acid' ? 'Lead-Acid' : 'Vanadium Flow'),
                    'cell_count' => rand(100, 1000),
                    'rated_capacity' => $data['capacity_kwh'] . ' kWh',
                    'charge_cycles' => $data['cycle_count'],
                    'max_cycles' => $data['max_cycles'],
                ]),
                'safety_systems' => json_encode([
                    'overcharge_protection' => true,
                    'overdischarge_protection' => true,
                    'overcurrent_protection' => true,
                    'temperature_monitoring' => true,
                    'fire_suppression' => fake()->boolean(80),
                    'emergency_shutdown' => true,
                ]),
                'custom_fields' => json_encode([
                    'availability_factor' => fake()->randomFloat(2, 95, 99.5),
                    'capacity_factor' => fake()->randomFloat(2, 20, 40),
                    'response_time_ms' => fake()->randomFloat(1, 10, 100),
                    'efficiency_curve' => [
                        'low_load' => fake()->randomFloat(2, 70, 85),
                        'medium_load' => fake()->randomFloat(2, 85, 95),
                        'high_load' => fake()->randomFloat(2, 80, 90),
                    ],
                ]),
                'co2_savings_annual_kg' => $data['capacity_kwh'] * fake()->randomFloat(2, 0.3, 0.8),
                'notes' => fake()->optional()->paragraph(),
                'is_active' => true,
                'commissioned_at' => $installationDate,
                'created_at' => $installationDate,
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),
                ]
            );
            
            $this->command->info("   ✅ Almacenamiento {$data['status']} creado: {$data['name']}");
        }

        $this->command->info("   ✅ Sistemas de almacenamiento de energía creados exitosamente");
    }
}
