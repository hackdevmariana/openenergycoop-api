<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ImpactMetrics;
use App\Models\User;
use App\Models\PlantGroup;
use App\Models\Plant;
use Carbon\Carbon;

class ImpactMetricsSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener o crear usuarios
        $users = User::take(5)->get();
        if ($users->isEmpty()) {
            $users = collect();
            for ($i = 1; $i <= 5; $i++) {
                $users->push(User::create([
                    'name' => "Usuario {$i}",
                    'email' => "usuario{$i}@example.com",
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]));
            }
        }

        // Obtener o crear plantas
        $plants = Plant::take(3)->get();
        if ($plants->isEmpty()) {
            $plants = collect();
            for ($i = 1; $i <= 3; $i++) {
                $plants->push(Plant::create([
                    'name' => "Planta Solar {$i}",
                    'type' => 'solar',
                    'capacity_kw' => 100 + ($i * 50),
                    'is_active' => true,
                ]));
            }
        }

        // Obtener o crear grupos de plantas
        $plantGroups = PlantGroup::take(3)->get();
        if ($plantGroups->isEmpty()) {
            $plantGroups = collect();
            foreach ($plants as $index => $plant) {
                $plantGroups->push(PlantGroup::create([
                    'user_id' => $users->first()->id,
                    'name' => "Grupo de Plantas {$plant->name}",
                    'plant_id' => $plant->id,
                    'number_of_plants' => 2 + $index,
                    'co2_avoided_total' => 0,
                    'custom_label' => "Grupo Solar " . ($index + 1),
                    'is_active' => true,
                ]));
            }
        }

        $impactMetrics = [
            // Métricas globales (sin usuario específico)
            [
                'user_id' => null,
                'total_kwh_produced' => 125000.50,
                'total_co2_avoided_kg' => 62500.25,
                'plant_group_id' => $plantGroups->first()?->id,
                'generated_at' => Carbon::now()->subDays(1),
            ],
            [
                'user_id' => null,
                'total_kwh_produced' => 98000.75,
                'total_co2_avoided_kg' => 49000.38,
                'plant_group_id' => $plantGroups->skip(1)->first()?->id,
                'generated_at' => Carbon::now()->subDays(2),
            ],

            // Métricas individuales por usuario
            [
                'user_id' => $users->first()?->id,
                'total_kwh_produced' => 2500.25,
                'total_co2_avoided_kg' => 1250.13,
                'plant_group_id' => $plantGroups->first()?->id,
                'generated_at' => Carbon::now()->subHours(6),
            ],
            [
                'user_id' => $users->first()?->id,
                'total_kwh_produced' => 3200.75,
                'total_co2_avoided_kg' => 1600.38,
                'plant_group_id' => $plantGroups->skip(1)->first()?->id,
                'generated_at' => Carbon::now()->subHours(12),
            ],
            [
                'user_id' => $users->skip(1)->first()?->id,
                'total_kwh_produced' => 1800.50,
                'total_co2_avoided_kg' => 900.25,
                'plant_group_id' => $plantGroups->first()?->id,
                'generated_at' => Carbon::now()->subHours(18),
            ],
            [
                'user_id' => $users->skip(1)->first()?->id,
                'total_kwh_produced' => 4200.00,
                'total_co2_avoided_kg' => 2100.00,
                'plant_group_id' => $plantGroups->skip(2)->first()?->id,
                'generated_at' => Carbon::now()->subDays(1),
            ],
            [
                'user_id' => $users->skip(2)->first()?->id,
                'total_kwh_produced' => 1500.25,
                'total_co2_avoided_kg' => 750.13,
                'plant_group_id' => $plantGroups->skip(1)->first()?->id,
                'generated_at' => Carbon::now()->subDays(2),
            ],
            [
                'user_id' => $users->skip(2)->first()?->id,
                'total_kwh_produced' => 2800.75,
                'total_co2_avoided_kg' => 1400.38,
                'plant_group_id' => $plantGroups->skip(2)->first()?->id,
                'generated_at' => Carbon::now()->subDays(3),
            ],
            [
                'user_id' => $users->skip(3)->first()?->id,
                'total_kwh_produced' => 3600.50,
                'total_co2_avoided_kg' => 1800.25,
                'plant_group_id' => $plantGroups->first()?->id,
                'generated_at' => Carbon::now()->subDays(4),
            ],
            [
                'user_id' => $users->skip(3)->first()?->id,
                'total_kwh_produced' => 2100.00,
                'total_co2_avoided_kg' => 1050.00,
                'plant_group_id' => $plantGroups->skip(1)->first()?->id,
                'generated_at' => Carbon::now()->subDays(5),
            ],
            [
                'user_id' => $users->skip(4)->first()?->id,
                'total_kwh_produced' => 4800.25,
                'total_co2_avoided_kg' => 2400.13,
                'plant_group_id' => $plantGroups->skip(2)->first()?->id,
                'generated_at' => Carbon::now()->subDays(6),
            ],
            [
                'user_id' => $users->skip(4)->first()?->id,
                'total_kwh_produced' => 1900.75,
                'total_co2_avoided_kg' => 950.38,
                'plant_group_id' => $plantGroups->first()?->id,
                'generated_at' => Carbon::now()->subDays(7),
            ],

            // Métricas históricas (más antiguas)
            [
                'user_id' => $users->first()?->id,
                'total_kwh_produced' => 1500.00,
                'total_co2_avoided_kg' => 750.00,
                'plant_group_id' => $plantGroups->first()?->id,
                'generated_at' => Carbon::now()->subDays(15),
            ],
            [
                'user_id' => $users->skip(1)->first()?->id,
                'total_kwh_produced' => 2200.50,
                'total_co2_avoided_kg' => 1100.25,
                'plant_group_id' => $plantGroups->skip(1)->first()?->id,
                'generated_at' => Carbon::now()->subDays(20),
            ],
            [
                'user_id' => null,
                'total_kwh_produced' => 75000.00,
                'total_co2_avoided_kg' => 37500.00,
                'plant_group_id' => $plantGroups->skip(2)->first()?->id,
                'generated_at' => Carbon::now()->subDays(30),
            ],
        ];

        foreach ($impactMetrics as $metric) {
            ImpactMetrics::create($metric);
        }

        $this->command->info('✅ ImpactMetricsSeeder ejecutado correctamente');
        $this->command->info('📊 Métricas de impacto creadas: ' . count($impactMetrics));
        $this->command->info('👥 Usuarios utilizados: ' . $users->count());
        $this->command->info('🏭 Grupos de plantas utilizados: ' . $plantGroups->count());
        $this->command->info('📈 Tipos de métricas: Globales (3) e Individuales (12)');
        $this->command->info('⚡ Total kWh producidos: ' . number_format(collect($impactMetrics)->sum('total_kwh_produced'), 2) . ' kWh');
        $this->command->info('🌱 Total CO₂ evitado: ' . number_format(collect($impactMetrics)->sum('total_co2_avoided_kg'), 2) . ' kg');
        $this->command->info('📅 Período: Últimos 30 días con datos variados');
        $this->command->info('🎯 Datos realistas: Producción solar y cálculos de CO₂ evitado');
    }
}
