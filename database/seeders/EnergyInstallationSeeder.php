<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EnergyInstallation;
use App\Models\User;
use App\Models\EnergySource;
use Carbon\Carbon;

class EnergyInstallationSeeder extends Seeder
{
    private $installationCounter = 1;

    public function run(): void
    {
        $this->command->info('🏗️ Creando instalaciones energéticas...');

        $users = User::take(8)->get();
        $energySources = EnergySource::take(5)->get();

        if ($users->isEmpty()) {
            $this->command->warn('   ⚠️ No hay usuarios disponibles. Creando usuarios de prueba...');
            $users = User::factory(8)->create();
        }

        if ($energySources->isEmpty()) {
            $this->command->warn('   ⚠️ No hay fuentes de energía disponibles. Saltando fuentes...');
        }

        // Crear instalaciones con diferentes tipos y estados
        $installationData = [
            [
                'installation_type' => 'residential',
                'name' => 'Instalación Solar Residencial Premium',
                'status' => 'operational',
                'priority' => 'high',
                'energy_source' => 'solar',
                'installed_capacity_kw' => 5.5,
                'operational_capacity_kw' => 5.2,
                'efficiency_rating' => 94.5,
                'annual_production_kwh' => 7500,
                'monthly_production_kwh' => 625,
                'daily_production_kwh' => 20.5,
                'location_address' => 'Calle Mayor 123, Madrid',
                'latitude' => 40.4168,
                'longitude' => -3.7038,
            ],
            [
                'installation_type' => 'commercial',
                'name' => 'Instalación Eólica Comercial',
                'status' => 'operational',
                'priority' => 'medium',
                'energy_source' => 'wind',
                'installed_capacity_kw' => 25.0,
                'operational_capacity_kw' => 23.8,
                'efficiency_rating' => 95.2,
                'annual_production_kwh' => 45000,
                'monthly_production_kwh' => 3750,
                'daily_production_kwh' => 123.3,
                'location_address' => 'Polígono Industrial Norte, Barcelona',
                'latitude' => 41.3851,
                'longitude' => 2.1734,
            ],
            [
                'installation_type' => 'industrial',
                'name' => 'Instalación Hidráulica Industrial',
                'status' => 'in_progress',
                'priority' => 'high',
                'energy_source' => 'hydro',
                'installed_capacity_kw' => 100.0,
                'operational_capacity_kw' => 0.0,
                'efficiency_rating' => 0.0,
                'annual_production_kwh' => 0,
                'monthly_production_kwh' => 0,
                'daily_production_kwh' => 0,
                'location_address' => 'Río Ebro, Zaragoza',
                'latitude' => 41.6488,
                'longitude' => -0.8891,
            ],
            [
                'installation_type' => 'utility_scale',
                'name' => 'Parque Solar de Gran Escala',
                'status' => 'approved',
                'priority' => 'high',
                'energy_source' => 'solar',
                'installed_capacity_kw' => 5000.0,
                'operational_capacity_kw' => 0.0,
                'efficiency_rating' => 0.0,
                'annual_production_kwh' => 0,
                'monthly_production_kwh' => 0,
                'daily_production_kwh' => 0,
                'location_address' => 'Desierto de Tabernas, Almería',
                'latitude' => 37.0000,
                'longitude' => -2.4000,
            ],
            [
                'installation_type' => 'community',
                'name' => 'Instalación Comunitaria de Biomasa',
                'status' => 'operational',
                'priority' => 'medium',
                'energy_source' => 'biomass',
                'installed_capacity_kw' => 15.0,
                'operational_capacity_kw' => 14.2,
                'efficiency_rating' => 94.7,
                'annual_production_kwh' => 27000,
                'monthly_production_kwh' => 2250,
                'daily_production_kwh' => 74.0,
                'location_address' => 'Centro Comunitario, Valencia',
                'latitude' => 39.4699,
                'longitude' => -0.3763,
            ],
            [
                'installation_type' => 'microgrid',
                'name' => 'Microgrid Inteligente',
                'status' => 'maintenance',
                'priority' => 'high',
                'energy_source' => 'mixed',
                'installed_capacity_kw' => 50.0,
                'operational_capacity_kw' => 35.0,
                'efficiency_rating' => 70.0,
                'annual_production_kwh' => 63000,
                'monthly_production_kwh' => 5250,
                'daily_production_kwh' => 172.6,
                'location_address' => 'Campus Universitario, Sevilla',
                'latitude' => 37.3891,
                'longitude' => -5.9845,
            ],
            [
                'installation_type' => 'off_grid',
                'name' => 'Instalación Autónoma Off-Grid',
                'status' => 'operational',
                'priority' => 'low',
                'energy_source' => 'solar',
                'installed_capacity_kw' => 3.0,
                'operational_capacity_kw' => 2.8,
                'efficiency_rating' => 93.3,
                'annual_production_kwh' => 4100,
                'monthly_production_kwh' => 342,
                'daily_production_kwh' => 11.2,
                'location_address' => 'Casa Rural Aislada, Granada',
                'latitude' => 37.1765,
                'longitude' => -3.5976,
            ],
            [
                'installation_type' => 'grid_tied',
                'name' => 'Instalación Conectada a Red',
                'status' => 'planned',
                'priority' => 'medium',
                'energy_source' => 'solar',
                'installed_capacity_kw' => 10.0,
                'operational_capacity_kw' => 0.0,
                'efficiency_rating' => 0.0,
                'annual_production_kwh' => 0,
                'monthly_production_kwh' => 0,
                'daily_production_kwh' => 0,
                'location_address' => 'Nave Industrial, Bilbao',
                'latitude' => 43.2627,
                'longitude' => -2.9253,
            ],
        ];

        foreach ($installationData as $index => $data) {
            $installationDate = Carbon::now()->subMonths(rand(1, 24));
            $commissioningDate = $data['status'] === 'operational' ? $installationDate->copy()->addDays(rand(7, 30)) : null;
            $warrantyExpiryDate = $installationDate->copy()->addYears(rand(5, 25));
            
            EnergyInstallation::create([
                'installation_number' => 'INST-' . strtoupper($data['installation_type']) . '-' . str_pad($this->installationCounter++, 4, '0', STR_PAD_LEFT),
                'name' => $data['name'],
                'description' => fake()->paragraph(),
                'installation_type' => $data['installation_type'],
                'status' => $data['status'],
                'priority' => $data['priority'],
                'energy_source_id' => $energySources->isNotEmpty() ? $energySources->random()->id : null,
                'customer_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'project_id' => null,
                'installed_capacity_kw' => $data['installed_capacity_kw'],
                'operational_capacity_kw' => $data['operational_capacity_kw'],
                'efficiency_rating' => $data['efficiency_rating'],
                'annual_production_kwh' => $data['annual_production_kwh'],
                'monthly_production_kwh' => $data['monthly_production_kwh'],
                'daily_production_kwh' => $data['daily_production_kwh'],
                'location_address' => $data['location_address'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'installation_date' => $installationDate,
                'commissioning_date' => $commissioningDate,
                'warranty_expiry_date' => $warrantyExpiryDate,
                'installation_cost' => $data['installed_capacity_kw'] * fake()->randomFloat(2, 800, 2000),
                'operational_cost_per_kwh' => fake()->randomFloat(4, 0.02, 0.08),
                'maintenance_cost_per_kwh' => fake()->randomFloat(4, 0.01, 0.05),
                'technical_specifications' => json_encode([
                    'inverter_type' => fake()->randomElement(['string', 'central', 'micro']),
                    'panel_type' => fake()->randomElement(['monocrystalline', 'polycrystalline', 'thin_film']),
                    'battery_system' => fake()->optional()->randomElement(['lithium_ion', 'lead_acid', 'flow_battery']),
                    'monitoring_system' => fake()->randomElement(['basic', 'advanced', 'smart']),
                    'grid_connection' => fake()->randomElement(['single_phase', 'three_phase']),
                ]),
                'warranty_terms' => json_encode([
                    'equipment_warranty' => rand(5, 25) . ' años',
                    'performance_warranty' => rand(20, 30) . ' años',
                    'workmanship_warranty' => rand(2, 5) . ' años',
                    'inverter_warranty' => rand(5, 15) . ' años',
                ]),
                'maintenance_requirements' => json_encode([
                    'frequency' => fake()->randomElement(['monthly', 'quarterly', 'semi_annual', 'annual']),
                    'cleaning_required' => fake()->boolean(80),
                    'inspection_required' => fake()->boolean(90),
                    'calibration_required' => fake()->boolean(60),
                ]),
                'safety_features' => json_encode([
                    'emergency_shutdown' => true,
                    'ground_fault_protection' => true,
                    'overcurrent_protection' => true,
                    'lightning_protection' => fake()->boolean(70),
                    'fire_suppression' => fake()->boolean(50),
                ]),
                'equipment_details' => json_encode([
                    'panels_count' => rand(10, 500),
                    'inverter_count' => rand(1, 10),
                    'battery_count' => fake()->optional()->numberBetween(1, 50),
                    'mounting_system' => fake()->randomElement(['roof_mount', 'ground_mount', 'tracking']),
                    'cable_length' => fake()->randomFloat(1, 50, 500),
                ]),
                'maintenance_schedule' => json_encode([
                    'next_maintenance' => Carbon::now()->addDays(rand(30, 180)),
                    'last_maintenance' => Carbon::now()->subDays(rand(0, 365)),
                    'maintenance_company' => fake()->company(),
                    'maintenance_contact' => fake()->phoneNumber(),
                ]),
                'performance_metrics' => json_encode([
                    'availability_factor' => fake()->randomFloat(2, 95, 99.5),
                    'capacity_factor' => fake()->randomFloat(2, 15, 35),
                    'performance_ratio' => fake()->randomFloat(2, 75, 95),
                    'energy_yield' => fake()->randomFloat(2, 1000, 1500) . ' kWh/kWp/year',
                ]),
                'warranty_documents' => json_encode([
                    'equipment_warranty_pdf' => fake()->optional()->url(),
                    'performance_warranty_pdf' => fake()->optional()->url(),
                    'installation_certificate' => fake()->optional()->url(),
                    'grid_connection_approval' => fake()->optional()->url(),
                ]),
                'installation_photos' => json_encode([
                    'before_installation' => fake()->optional()->imageUrl(),
                    'during_installation' => fake()->optional()->imageUrl(),
                    'after_installation' => fake()->optional()->imageUrl(),
                    'equipment_closeup' => fake()->optional()->imageUrl(),
                ]),
                'tags' => json_encode([
                    'renewable_energy',
                    $data['installation_type'],
                    $data['energy_source'],
                    'sustainable',
                    'green_energy',
                ]),
                'installed_by' => $users->isNotEmpty() ? $users->random()->id : null,
                'managed_by' => $users->isNotEmpty() ? $users->random()->id : null,
                'created_by' => $users->isNotEmpty() ? $users->random()->id : null,
                'approved_by' => $data['status'] === 'approved' ? ($users->isNotEmpty() ? $users->random()->id : null) : null,
                'approved_at' => $data['status'] === 'approved' ? Carbon::now()->subDays(rand(1, 30)) : null,
                'notes' => fake()->optional()->paragraph(),
                'created_at' => $installationDate,
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),
            ]);
            
            $this->command->info("   ✅ Instalación {$data['status']} creada: {$data['name']}");
        }

        $this->command->info("   ✅ Instalaciones energéticas creadas exitosamente");
    }
}
