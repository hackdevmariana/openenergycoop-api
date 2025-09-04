<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SustainabilityMetric;
use App\Models\User;
use App\Models\EnergyCooperative;
use App\Models\EnergyReport;
use Carbon\Carbon;

class SustainabilityMetricSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Creando métricas de sostenibilidad...');

        $users = User::all();
        $energyCooperatives = EnergyCooperative::all();
        $energyReports = EnergyReport::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles.');
            return;
        }

        // Limpiar datos existentes
        SustainabilityMetric::query()->delete();

        $this->command->info("👥 Usuarios disponibles: {$users->count()}");
        $this->command->info("🏭 Cooperativas disponibles: {$energyCooperatives->count()}");
        $this->command->info("📊 Reportes disponibles: {$energyReports->count()}");

        // Crear diferentes tipos de métricas
        $this->createCarbonFootprintMetrics($users, $energyCooperatives, $energyReports);
        $this->createRenewableEnergyMetrics($users, $energyCooperatives, $energyReports);
        $this->createEnergyEfficiencyMetrics($users, $energyCooperatives, $energyReports);
        $this->createWasteReductionMetrics($users, $energyCooperatives, $energyReports);
        $this->createWaterUsageMetrics($users, $energyCooperatives, $energyReports);
        $this->createSocialImpactMetrics($users, $energyCooperatives, $energyReports);
        $this->createEconomicImpactMetrics($users, $energyCooperatives, $energyReports);

        $this->command->info('✅ SustainabilityMetricSeeder completado. Se crearon ' . SustainabilityMetric::count() . ' métricas.');
    }

    private function createCarbonFootprintMetrics($users, $energyCooperatives, $energyReports): void
    {
        $this->command->info('🌍 Creando métricas de huella de carbono...');

        for ($i = 0; $i < 25; $i++) {
            $user = $users->random();
            $cooperative = $energyCooperatives->isEmpty() ? null : $energyCooperatives->random();
            $energyReport = $energyReports->isEmpty() ? null : $energyReports->random();
            $measurementDate = Carbon::now()->subDays(rand(1, 90));
            $periodStart = $measurementDate->copy()->subDays(rand(7, 30));
            $periodEnd = $measurementDate;

            $value = fake()->randomFloat(2, 100, 2000);
            $baselineValue = $value * fake()->randomFloat(2, 0.8, 1.2);
            $previousValue = $value * fake()->randomFloat(2, 0.9, 1.1);
            $changeAbsolute = $value - $previousValue;
            $changePercentage = ($changeAbsolute / $previousValue) * 100;

            SustainabilityMetric::create([
                'metric_name' => fake()->randomElement([
                    'Huella de Carbono Mensual',
                    'Emisiones de CO2 Anuales',
                    'Impacto de Carbono Trimestral',
                    'Emisiones de Gases de Efecto Invernadero',
                    'Huella de Carbono por Usuario'
                ]),
                'metric_code' => 'SUST-CAR-' . strtoupper(fake()->bothify('####-??')),
                'description' => fake()->sentence,
                'metric_type' => 'carbon_footprint',
                'metric_category' => 'environmental',
                'entity_type' => fake()->randomElement(['user', 'cooperative', 'system']),
                'entity_id' => $user->id,
                'entity_name' => $user->name,
                'measurement_date' => $measurementDate,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'period_type' => fake()->randomElement(['monthly', 'quarterly']),
                'value' => $value,
                'unit' => 'kg CO2',
                'baseline_value' => $baselineValue,
                'target_value' => $value * fake()->randomFloat(2, 0.7, 0.9),
                'previous_period_value' => $previousValue,
                'change_absolute' => $changeAbsolute,
                'change_percentage' => $changePercentage,
                'trend' => $changePercentage > 0 ? 'declining' : 'improving',
                'trend_score' => fake()->randomFloat(2, -20, 20),
                'calculation_method' => json_encode([
                    'method' => 'emission_factors',
                    'formula' => 'energy_consumption * emission_factor',
                    'data_sources' => ['energy_readings', 'emission_factors_db']
                ]),
                'data_sources' => json_encode(['energy_readings', 'emission_factors', 'user_profiles']),
                'calculation_details' => json_encode([
                    'energy_consumption_kwh' => fake()->randomFloat(2, 500, 3000),
                    'emission_factor_kg_co2_per_kwh' => fake()->randomFloat(4, 0.2, 0.8),
                    'calculation_date' => $measurementDate->toDateString()
                ]),
                'data_quality_score' => fake()->numberBetween(85, 100),
                'assumptions' => json_encode([
                    'grid_mix_assumption' => 'national_average',
                    'emission_factor_source' => 'official_database',
                    'calculation_boundary' => 'operational_emissions'
                ]),
                'co2_emissions_kg' => $value,
                'co2_avoided_kg' => fake()->randomFloat(2, 50, 500),
                'carbon_offset_kg' => fake()->optional(0.3)->randomFloat(2, 10, 200),
                'carbon_intensity' => fake()->randomFloat(4, 0.1, 0.8),
                'is_certified' => fake()->boolean(20),
                'verification_status' => fake()->randomElement(['unverified', 'self_reported', 'third_party', 'certified']),
                'performance_rating' => fake()->randomElement(['poor', 'below_average', 'average', 'above_average', 'excellent']),
                'contributes_to_sdg' => fake()->boolean(80),
                'sdg_targets' => json_encode(['13.1', '13.2', '7.2']),
                'paris_agreement_aligned' => fake()->boolean(70),
                'sustainability_goals' => json_encode([
                    'reduce_carbon_footprint_by_20_percent',
                    'achieve_carbon_neutrality_by_2030'
                ]),
                'include_in_reports' => true,
                'is_public' => fake()->boolean(60),
                'public_description' => fake()->optional(0.6)->paragraph,
                'visualization_config' => json_encode([
                    'chart_type' => 'line',
                    'show_trend' => true,
                    'show_targets' => true
                ]),
                'report_priority' => fake()->numberBetween(1, 5),
                'energy_cooperative_id' => $cooperative?->id,
                'user_id' => $user->id,
                'energy_report_id' => $energyReport?->id,
                'calculated_by_id' => $user->id,
                'verified_by_id' => fake()->optional(0.3)->randomElement($users->pluck('id')->toArray()),
                'calculated_at' => $measurementDate,
                'verified_at' => fake()->optional(0.3)->dateTimeBetween($measurementDate, 'now'),
                'audit_trail' => json_encode([
                    'created_by' => $user->id,
                    'calculation_date' => $measurementDate->toDateString(),
                    'data_sources_verified' => true
                ]),
                'notes' => fake()->optional(0.4)->sentence,
                'metadata' => json_encode([
                    'version' => '1.0',
                    'calculation_standard' => 'ISO_14064',
                    'last_updated' => now()->toISOString()
                ]),
                'alert_enabled' => fake()->boolean(50),
                'alert_threshold_min' => fake()->optional(0.5)->randomFloat(2, 0, $value * 0.8),
                'alert_threshold_max' => fake()->optional(0.5)->randomFloat(2, $value * 1.2, $value * 2),
                'alert_status' => fake()->randomElement(['normal', 'warning', 'critical']),
                'last_alert_sent_at' => fake()->optional(0.2)->dateTimeBetween('-1 week', 'now'),
            ]);
        }
    }

    private function createRenewableEnergyMetrics($users, $energyCooperatives, $energyReports): void
    {
        $this->command->info('☀️ Creando métricas de energía renovable...');

        for ($i = 0; $i < 20; $i++) {
            $user = $users->random();
            $cooperative = $energyCooperatives->isEmpty() ? null : $energyCooperatives->random();
            $energyReport = $energyReports->isEmpty() ? null : $energyReports->random();
            $measurementDate = Carbon::now()->subDays(rand(1, 90));
            $periodStart = $measurementDate->copy()->subDays(rand(7, 30));
            $periodEnd = $measurementDate;

            $renewableEnergyKwh = fake()->randomFloat(2, 1000, 5000);
            $totalEnergyKwh = $renewableEnergyKwh + fake()->randomFloat(2, 200, 2000);
            $renewablePercentage = ($renewableEnergyKwh / $totalEnergyKwh) * 100;

            SustainabilityMetric::create([
                'metric_name' => fake()->randomElement([
                    'Porcentaje de Energía Renovable',
                    'Generación de Energía Verde',
                    'Mix Energético Renovable',
                    'Energía Solar y Eólica',
                    'Energía Renovable por Usuario'
                ]),
                'metric_code' => 'SUST-REN-' . strtoupper(fake()->bothify('####-??')),
                'description' => fake()->sentence,
                'metric_type' => 'renewable_percentage',
                'metric_category' => 'environmental',
                'entity_type' => fake()->randomElement(['user', 'cooperative', 'system']),
                'entity_id' => $user->id,
                'entity_name' => $user->name,
                'measurement_date' => $measurementDate,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'period_type' => fake()->randomElement(['monthly', 'quarterly']),
                'value' => $renewablePercentage,
                'unit' => '%',
                'baseline_value' => $renewablePercentage * fake()->randomFloat(2, 0.8, 1.2),
                'target_value' => $renewablePercentage * fake()->randomFloat(2, 1.1, 1.3),
                'previous_period_value' => $renewablePercentage * fake()->randomFloat(2, 0.9, 1.1),
                'change_absolute' => fake()->randomFloat(2, -10, 10),
                'change_percentage' => fake()->randomFloat(2, -15, 15),
                'trend' => fake()->randomElement(['improving', 'declining', 'stable']),
                'trend_score' => fake()->randomFloat(2, -20, 20),
                'renewable_energy_kwh' => $renewableEnergyKwh,
                'total_energy_kwh' => $totalEnergyKwh,
                'renewable_percentage' => $renewablePercentage,
                'fossil_fuel_displacement_kwh' => fake()->randomFloat(2, 500, 2000),
                'is_certified' => fake()->boolean(30),
                'verification_status' => fake()->randomElement(['unverified', 'self_reported', 'third_party', 'certified']),
                'performance_rating' => fake()->randomElement(['poor', 'below_average', 'average', 'above_average', 'excellent']),
                'contributes_to_sdg' => fake()->boolean(90),
                'sdg_targets' => json_encode(['7.1', '7.2', '13.1']),
                'paris_agreement_aligned' => fake()->boolean(85),
                'include_in_reports' => true,
                'is_public' => fake()->boolean(70),
                'energy_cooperative_id' => $cooperative?->id,
                'user_id' => $user->id,
                'energy_report_id' => $energyReport?->id,
                'calculated_by_id' => $user->id,
                'alert_enabled' => fake()->boolean(60),
                'alert_status' => fake()->randomElement(['normal', 'warning', 'critical']),
            ]);
        }
    }

    private function createEnergyEfficiencyMetrics($users, $energyCooperatives, $energyReports): void
    {
        $this->command->info('⚡ Creando métricas de eficiencia energética...');

        for ($i = 0; $i < 15; $i++) {
            $user = $users->random();
            $cooperative = $energyCooperatives->isEmpty() ? null : $energyCooperatives->random();
            $energyReport = $energyReports->isEmpty() ? null : $energyReports->random();
            $measurementDate = Carbon::now()->subDays(rand(1, 90));
            $periodStart = $measurementDate->copy()->subDays(rand(7, 30));
            $periodEnd = $measurementDate;

            $value = fake()->randomFloat(2, 60, 95);

            SustainabilityMetric::create([
                'metric_name' => fake()->randomElement([
                    'Eficiencia Energética General',
                    'Rendimiento de Instalaciones',
                    'Eficiencia de Equipos',
                    'Optimización Energética',
                    'Eficiencia por Unidad de Producción'
                ]),
                'metric_code' => 'SUST-EFF-' . strtoupper(fake()->bothify('####-??')),
                'description' => fake()->sentence,
                'metric_type' => 'energy_efficiency',
                'metric_category' => 'operational',
                'entity_type' => fake()->randomElement(['user', 'cooperative', 'system']),
                'entity_id' => $user->id,
                'entity_name' => $user->name,
                'measurement_date' => $measurementDate,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'period_type' => fake()->randomElement(['monthly', 'quarterly']),
                'value' => $value,
                'unit' => '%',
                'baseline_value' => $value * fake()->randomFloat(2, 0.8, 1.2),
                'target_value' => $value * fake()->randomFloat(2, 1.05, 1.15),
                'previous_period_value' => $value * fake()->randomFloat(2, 0.9, 1.1),
                'change_absolute' => fake()->randomFloat(2, -5, 5),
                'change_percentage' => fake()->randomFloat(2, -10, 10),
                'trend' => fake()->randomElement(['improving', 'declining', 'stable']),
                'trend_score' => fake()->randomFloat(2, -15, 15),
                'cost_savings_eur' => fake()->randomFloat(2, 100, 2000),
                'investment_recovery_eur' => fake()->optional(0.4)->randomFloat(2, 500, 5000),
                'economic_impact_eur' => fake()->optional(0.3)->randomFloat(2, 1000, 10000),
                'is_certified' => fake()->boolean(25),
                'verification_status' => fake()->randomElement(['unverified', 'self_reported', 'third_party', 'certified']),
                'performance_rating' => fake()->randomElement(['poor', 'below_average', 'average', 'above_average', 'excellent']),
                'contributes_to_sdg' => fake()->boolean(75),
                'sdg_targets' => json_encode(['7.3', '12.2', '13.1']),
                'include_in_reports' => true,
                'is_public' => fake()->boolean(65),
                'energy_cooperative_id' => $cooperative?->id,
                'user_id' => $user->id,
                'energy_report_id' => $energyReport?->id,
                'calculated_by_id' => $user->id,
                'alert_enabled' => fake()->boolean(55),
                'alert_status' => fake()->randomElement(['normal', 'warning', 'critical']),
            ]);
        }
    }

    private function createWasteReductionMetrics($users, $energyCooperatives, $energyReports): void
    {
        $this->command->info('♻️ Creando métricas de reducción de residuos...');

        for ($i = 0; $i < 12; $i++) {
            $user = $users->random();
            $cooperative = $energyCooperatives->isEmpty() ? null : $energyCooperatives->random();
            $energyReport = $energyReports->isEmpty() ? null : $energyReports->random();
            $measurementDate = Carbon::now()->subDays(rand(1, 90));
            $periodStart = $measurementDate->copy()->subDays(rand(7, 30));
            $periodEnd = $measurementDate;

            $value = fake()->randomFloat(2, 10, 80);

            SustainabilityMetric::create([
                'metric_name' => fake()->randomElement([
                    'Reducción de Residuos',
                    'Tasa de Reciclaje',
                    'Minimización de Desechos',
                    'Gestión Sostenible de Residuos',
                    'Reducción de Residuos por Usuario'
                ]),
                'metric_code' => 'SUST-WAS-' . strtoupper(fake()->bothify('####-??')),
                'description' => fake()->sentence,
                'metric_type' => 'waste_reduction',
                'metric_category' => 'environmental',
                'entity_type' => fake()->randomElement(['user', 'cooperative', 'system']),
                'entity_id' => $user->id,
                'entity_name' => $user->name,
                'measurement_date' => $measurementDate,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'period_type' => fake()->randomElement(['monthly', 'quarterly']),
                'value' => $value,
                'unit' => '%',
                'baseline_value' => $value * fake()->randomFloat(2, 0.7, 1.3),
                'target_value' => $value * fake()->randomFloat(2, 1.1, 1.4),
                'previous_period_value' => $value * fake()->randomFloat(2, 0.8, 1.2),
                'change_absolute' => fake()->randomFloat(2, -10, 10),
                'change_percentage' => fake()->randomFloat(2, -20, 20),
                'trend' => fake()->randomElement(['improving', 'declining', 'stable']),
                'trend_score' => fake()->randomFloat(2, -20, 20),
                'is_certified' => fake()->boolean(20),
                'verification_status' => fake()->randomElement(['unverified', 'self_reported', 'third_party', 'certified']),
                'performance_rating' => fake()->randomElement(['poor', 'below_average', 'average', 'above_average', 'excellent']),
                'contributes_to_sdg' => fake()->boolean(70),
                'sdg_targets' => json_encode(['12.3', '12.4', '12.5']),
                'include_in_reports' => true,
                'is_public' => fake()->boolean(60),
                'energy_cooperative_id' => $cooperative?->id,
                'user_id' => $user->id,
                'energy_report_id' => $energyReport?->id,
                'calculated_by_id' => $user->id,
                'alert_enabled' => fake()->boolean(45),
                'alert_status' => fake()->randomElement(['normal', 'warning', 'critical']),
            ]);
        }
    }

    private function createWaterUsageMetrics($users, $energyCooperatives, $energyReports): void
    {
        $this->command->info('💧 Creando métricas de uso de agua...');

        for ($i = 0; $i < 10; $i++) {
            $user = $users->random();
            $cooperative = $energyCooperatives->isEmpty() ? null : $energyCooperatives->random();
            $energyReport = $energyReports->isEmpty() ? null : $energyReports->random();
            $measurementDate = Carbon::now()->subDays(rand(1, 90));
            $periodStart = $measurementDate->copy()->subDays(rand(7, 30));
            $periodEnd = $measurementDate;

            $value = fake()->randomFloat(2, 100, 10000);

            SustainabilityMetric::create([
                'metric_name' => fake()->randomElement([
                    'Consumo de Agua',
                    'Eficiencia Hídrica',
                    'Uso Sostenible del Agua',
                    'Consumo de Agua por Usuario',
                    'Optimización del Uso de Agua'
                ]),
                'metric_code' => 'SUST-WAT-' . strtoupper(fake()->bothify('####-??')),
                'description' => fake()->sentence,
                'metric_type' => 'water_usage',
                'metric_category' => 'environmental',
                'entity_type' => fake()->randomElement(['user', 'cooperative', 'system']),
                'entity_id' => $user->id,
                'entity_name' => $user->name,
                'measurement_date' => $measurementDate,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'period_type' => fake()->randomElement(['monthly', 'quarterly']),
                'value' => $value,
                'unit' => 'litros',
                'baseline_value' => $value * fake()->randomFloat(2, 0.8, 1.2),
                'target_value' => $value * fake()->randomFloat(2, 0.7, 0.9),
                'previous_period_value' => $value * fake()->randomFloat(2, 0.9, 1.1),
                'change_absolute' => fake()->randomFloat(2, -500, 500),
                'change_percentage' => fake()->randomFloat(2, -15, 15),
                'trend' => fake()->randomElement(['improving', 'declining', 'stable']),
                'trend_score' => fake()->randomFloat(2, -20, 20),
                'is_certified' => fake()->boolean(15),
                'verification_status' => fake()->randomElement(['unverified', 'self_reported', 'third_party', 'certified']),
                'performance_rating' => fake()->randomElement(['poor', 'below_average', 'average', 'above_average', 'excellent']),
                'contributes_to_sdg' => fake()->boolean(65),
                'sdg_targets' => json_encode(['6.1', '6.2', '6.4']),
                'include_in_reports' => true,
                'is_public' => fake()->boolean(55),
                'energy_cooperative_id' => $cooperative?->id,
                'user_id' => $user->id,
                'energy_report_id' => $energyReport?->id,
                'calculated_by_id' => $user->id,
                'alert_enabled' => fake()->boolean(40),
                'alert_status' => fake()->randomElement(['normal', 'warning', 'critical']),
            ]);
        }
    }

    private function createSocialImpactMetrics($users, $energyCooperatives, $energyReports): void
    {
        $this->command->info('👥 Creando métricas de impacto social...');

        for ($i = 0; $i < 8; $i++) {
            $user = $users->random();
            $cooperative = $energyCooperatives->isEmpty() ? null : $energyCooperatives->random();
            $energyReport = $energyReports->isEmpty() ? null : $energyReports->random();
            $measurementDate = Carbon::now()->subDays(rand(1, 90));
            $periodStart = $measurementDate->copy()->subDays(rand(7, 30));
            $periodEnd = $measurementDate;

            SustainabilityMetric::create([
                'metric_name' => fake()->randomElement([
                    'Impacto en Comunidades',
                    'Beneficios Sociales',
                    'Creación de Empleo',
                    'Educación Energética',
                    'Participación Comunitaria'
                ]),
                'metric_code' => 'SUST-SOC-' . strtoupper(fake()->bothify('####-??')),
                'description' => fake()->sentence,
                'metric_type' => 'social_impact',
                'metric_category' => 'social',
                'entity_type' => fake()->randomElement(['cooperative', 'system']),
                'entity_id' => $cooperative?->id ?? $user->id,
                'entity_name' => $cooperative?->name ?? $user->name,
                'measurement_date' => $measurementDate,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'period_type' => fake()->randomElement(['monthly', 'quarterly']),
                'value' => fake()->randomFloat(2, 10, 100),
                'unit' => 'personas',
                'baseline_value' => fake()->randomFloat(2, 5, 80),
                'target_value' => fake()->randomFloat(2, 15, 120),
                'previous_period_value' => fake()->randomFloat(2, 8, 90),
                'change_absolute' => fake()->randomFloat(2, -20, 20),
                'change_percentage' => fake()->randomFloat(2, -25, 25),
                'trend' => fake()->randomElement(['improving', 'declining', 'stable']),
                'trend_score' => fake()->randomFloat(2, -20, 20),
                'communities_impacted' => fake()->numberBetween(1, 10),
                'people_benefited' => fake()->numberBetween(10, 500),
                'social_value_eur' => fake()->randomFloat(2, 1000, 50000),
                'education_hours' => fake()->numberBetween(10, 200),
                'awareness_campaigns' => fake()->numberBetween(1, 20),
                'jobs_created' => fake()->numberBetween(0, 50),
                'jobs_sustained' => fake()->numberBetween(0, 100),
                'is_certified' => fake()->boolean(10),
                'verification_status' => fake()->randomElement(['unverified', 'self_reported', 'third_party', 'certified']),
                'performance_rating' => fake()->randomElement(['poor', 'below_average', 'average', 'above_average', 'excellent']),
                'contributes_to_sdg' => fake()->boolean(85),
                'sdg_targets' => json_encode(['1.1', '4.1', '8.5', '11.3']),
                'include_in_reports' => true,
                'is_public' => fake()->boolean(80),
                'energy_cooperative_id' => $cooperative?->id,
                'user_id' => $user->id,
                'energy_report_id' => $energyReport?->id,
                'calculated_by_id' => $user->id,
                'alert_enabled' => fake()->boolean(30),
                'alert_status' => fake()->randomElement(['normal', 'warning', 'critical']),
            ]);
        }
    }

    private function createEconomicImpactMetrics($users, $energyCooperatives, $energyReports): void
    {
        $this->command->info('💰 Creando métricas de impacto económico...');

        for ($i = 0; $i < 10; $i++) {
            $user = $users->random();
            $cooperative = $energyCooperatives->isEmpty() ? null : $energyCooperatives->random();
            $energyReport = $energyReports->isEmpty() ? null : $energyReports->random();
            $measurementDate = Carbon::now()->subDays(rand(1, 90));
            $periodStart = $measurementDate->copy()->subDays(rand(7, 30));
            $periodEnd = $measurementDate;

            $value = fake()->randomFloat(2, 1000, 50000);

            SustainabilityMetric::create([
                'metric_name' => fake()->randomElement([
                    'Impacto Económico',
                    'Ahorros Financieros',
                    'Retorno de Inversión',
                    'Valor Económico Generado',
                    'Beneficios Económicos'
                ]),
                'metric_code' => 'SUST-ECO-' . strtoupper(fake()->bothify('####-??')),
                'description' => fake()->sentence,
                'metric_type' => 'economic_impact',
                'metric_category' => 'economic',
                'entity_type' => fake()->randomElement(['user', 'cooperative', 'system']),
                'entity_id' => $user->id,
                'entity_name' => $user->name,
                'measurement_date' => $measurementDate,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'period_type' => fake()->randomElement(['monthly', 'quarterly']),
                'value' => $value,
                'unit' => 'EUR',
                'baseline_value' => $value * fake()->randomFloat(2, 0.7, 1.3),
                'target_value' => $value * fake()->randomFloat(2, 1.1, 1.5),
                'previous_period_value' => $value * fake()->randomFloat(2, 0.8, 1.2),
                'change_absolute' => fake()->randomFloat(2, -5000, 5000),
                'change_percentage' => fake()->randomFloat(2, -30, 30),
                'trend' => fake()->randomElement(['improving', 'declining', 'stable']),
                'trend_score' => fake()->randomFloat(2, -25, 25),
                'cost_savings_eur' => $value,
                'investment_recovery_eur' => fake()->optional(0.4)->randomFloat(2, 500, 10000),
                'economic_impact_eur' => $value,
                'jobs_created' => fake()->numberBetween(0, 20),
                'jobs_sustained' => fake()->numberBetween(0, 50),
                'is_certified' => fake()->boolean(15),
                'verification_status' => fake()->randomElement(['unverified', 'self_reported', 'third_party', 'certified']),
                'performance_rating' => fake()->randomElement(['poor', 'below_average', 'average', 'above_average', 'excellent']),
                'contributes_to_sdg' => fake()->boolean(70),
                'sdg_targets' => json_encode(['8.1', '8.2', '8.5']),
                'include_in_reports' => true,
                'is_public' => fake()->boolean(65),
                'energy_cooperative_id' => $cooperative?->id,
                'user_id' => $user->id,
                'energy_report_id' => $energyReport?->id,
                'calculated_by_id' => $user->id,
                'alert_enabled' => fake()->boolean(50),
                'alert_status' => fake()->randomElement(['normal', 'warning', 'critical']),
            ]);
        }
    }
}
