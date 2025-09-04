<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PerformanceIndicator;
use App\Models\User;
use App\Models\EnergyCooperative;
use App\Models\EnergyReport;
use Carbon\Carbon;

class PerformanceIndicatorSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📊 Creando indicadores de rendimiento...');

        $users = User::all();
        $energyCooperatives = EnergyCooperative::all();
        $energyReports = EnergyReport::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles.');
            return;
        }

        // Limpiar datos existentes
        PerformanceIndicator::query()->delete();

        $this->command->info("👥 Usuarios disponibles: {$users->count()}");
        $this->command->info("🏭 Cooperativas disponibles: {$energyCooperatives->count()}");
        $this->command->info("📊 Reportes disponibles: {$energyReports->count()}");

        // Crear diferentes tipos de indicadores
        $this->createOperationalIndicators($users, $energyCooperatives, $energyReports);
        $this->createFinancialIndicators($users, $energyCooperatives, $energyReports);
        $this->createTechnicalIndicators($users, $energyCooperatives, $energyReports);
        $this->createCustomerIndicators($users, $energyCooperatives, $energyReports);
        $this->createEnvironmentalIndicators($users, $energyCooperatives, $energyReports);
        $this->createQualityIndicators($users, $energyCooperatives, $energyReports);
        $this->createStrategicIndicators($users, $energyCooperatives, $energyReports);

        $this->command->info('✅ PerformanceIndicatorSeeder completado. Se crearon ' . PerformanceIndicator::count() . ' indicadores.');
    }

    private function createOperationalIndicators($users, $energyCooperatives, $energyReports): void
    {
        $this->command->info('⚙️ Creando indicadores operacionales...');

        for ($i = 0; $i < 20; $i++) {
            $user = $users->random();
            $cooperative = $energyCooperatives->isEmpty() ? null : $energyCooperatives->random();
            $energyReport = $energyReports->isEmpty() ? null : $energyReports->random();
            $measurementTimestamp = Carbon::now()->subDays(rand(1, 90));
            $periodStart = $measurementTimestamp->copy()->subDays(rand(7, 30));
            $periodEnd = $measurementTimestamp;

            $currentValue = fake()->randomFloat(2, 70, 95);
            $baselineValue = $currentValue * fake()->randomFloat(2, 0.8, 1.2);
            $targetValue = $currentValue * fake()->randomFloat(2, 1.05, 1.15);
            $previousValue = $currentValue * fake()->randomFloat(2, 0.9, 1.1);
            $changeAbsolute = $currentValue - $previousValue;
            $changePercentage = ($changeAbsolute / $previousValue) * 100;

            PerformanceIndicator::create([
                'indicator_name' => fake()->randomElement([
                    'Eficiencia Operacional General',
                    'Utilización de Recursos Energéticos',
                    'Disponibilidad del Sistema',
                    'Tiempo de Respuesta Operacional',
                    'Eficiencia de Procesos',
                    'Utilización de Capacidad',
                    'Rendimiento de Instalaciones',
                    'Eficiencia de Mantenimiento'
                ]),
                'indicator_code' => 'PI-OPE-' . strtoupper(fake()->bothify('####-??')),
                'description' => fake()->sentence,
                'indicator_type' => fake()->randomElement(['efficiency', 'utilization', 'kpi']),
                'category' => 'operational',
                'criticality' => fake()->randomElement(['medium', 'high', 'critical']),
                'priority' => fake()->numberBetween(2, 5),
                'frequency' => fake()->randomElement(['daily', 'weekly', 'monthly']),
                'is_active' => fake()->boolean(90),
                'scope' => fake()->randomElement(['system', 'cooperative', 'user']),
                'entity_type' => 'user',
                'entity_id' => $user->id,
                'entity_name' => $user->name,
                'measurement_timestamp' => $measurementTimestamp,
                'measurement_date' => $measurementTimestamp->toDateString(),
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'period_type' => fake()->randomElement(['daily', 'weekly', 'monthly']),
                'current_value' => $currentValue,
                'unit' => '%',
                'target_value' => $targetValue,
                'baseline_value' => $baselineValue,
                'previous_value' => $previousValue,
                'best_value' => $currentValue * fake()->randomFloat(2, 1.05, 1.2),
                'worst_value' => $currentValue * fake()->randomFloat(2, 0.7, 0.9),
                'change_absolute' => $changeAbsolute,
                'change_percentage' => $changePercentage,
                'target_achievement_percentage' => ($currentValue / $targetValue) * 100,
                'trend_direction' => $changePercentage > 0 ? 'up' : 'down',
                'trend_strength' => fake()->randomFloat(2, 10, 80),
                'performance_status' => $currentValue >= 90 ? 'excellent' : ($currentValue >= 80 ? 'good' : ($currentValue >= 70 ? 'acceptable' : 'poor')),
                'calculation_formula' => 'efficiency = (actual_output / theoretical_maximum) * 100',
                'calculation_parameters' => json_encode([
                    'actual_output' => fake()->randomFloat(2, 800, 1200),
                    'theoretical_maximum' => fake()->randomFloat(2, 1000, 1500),
                    'calculation_date' => $measurementTimestamp->toDateString()
                ]),
                'data_sources' => json_encode(['operational_systems', 'energy_readings', 'maintenance_logs']),
                'calculation_method' => 'automatic',
                'confidence_level' => fake()->randomFloat(2, 85, 98),
                'industry_benchmark' => fake()->randomFloat(2, 75, 90),
                'competitor_benchmark' => fake()->optional(0.6)->randomFloat(2, 70, 95),
                'internal_benchmark' => fake()->optional(0.7)->randomFloat(2, 80, 92),
                'benchmark_comparison' => fake()->randomElement(['above', 'at', 'below']),
                'influencing_factors' => json_encode([
                    'maintenance_schedule' => 'regular',
                    'equipment_age' => fake()->numberBetween(1, 10),
                    'operator_training' => 'certified'
                ]),
                'context_notes' => fake()->optional(0.5)->sentence,
                'external_conditions' => json_encode([
                    'weather_conditions' => 'normal',
                    'market_demand' => 'stable',
                    'energy_prices' => 'moderate'
                ]),
                'seasonality_factor' => fake()->optional(0.4)->randomFloat(2, 0.8, 1.2),
                'weather_dependent' => fake()->boolean(30),
                'alerts_enabled' => fake()->boolean(60),
                'alert_threshold_min' => fake()->optional(0.6)->randomFloat(2, 60, 75),
                'alert_threshold_max' => fake()->optional(0.6)->randomFloat(2, 95, 105),
                'warning_threshold_min' => fake()->optional(0.6)->randomFloat(2, 70, 80),
                'warning_threshold_max' => fake()->optional(0.6)->randomFloat(2, 90, 100),
                'current_alert_level' => fake()->randomElement(['normal', 'warning', 'critical']),
                'last_alert_sent_at' => fake()->optional(0.3)->dateTimeBetween('-1 week', 'now'),
                'improvement_actions' => fake()->optional(0.6)->paragraph,
                'corrective_actions' => fake()->optional(0.4)->paragraph,
                'improvement_potential' => fake()->randomFloat(2, 5, 25),
                'next_review_date' => fake()->optional(0.7)->dateTimeBetween('+1 week', '+3 months'),
                'action_priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
                'business_impact' => fake()->randomElement(['medium', 'high', 'strategic']),
                'financial_impact_eur' => fake()->optional(0.5)->randomFloat(2, 1000, 50000),
                'business_value_description' => fake()->optional(0.6)->sentence,
                'stakeholders' => json_encode(['operations_team', 'management', 'customers']),
                'efficiency_percentage' => $currentValue,
                'utilization_percentage' => fake()->randomFloat(2, 60, 90),
                'availability_percentage' => fake()->randomFloat(2, 95, 99.9),
                'downtime_minutes' => fake()->optional(0.4)->numberBetween(0, 480),
                'energy_cooperative_id' => $cooperative?->id,
                'user_id' => $user->id,
                'energy_report_id' => $energyReport?->id,
                'created_by_id' => $user->id,
                'validated_by_id' => fake()->optional(0.4)->randomElement($users->pluck('id')->toArray()),
                'is_validated' => fake()->boolean(70),
                'validated_at' => fake()->optional(0.4)->dateTimeBetween($measurementTimestamp, 'now'),
                'validation_notes' => fake()->optional(0.3)->sentence,
                'audit_log' => json_encode([
                    'created_by' => $user->id,
                    'calculation_date' => $measurementTimestamp->toDateString(),
                    'data_sources_verified' => true
                ]),
                'revision_number' => fake()->numberBetween(1, 5),
                'show_in_dashboard' => fake()->boolean(80),
                'dashboard_config' => json_encode([
                    'chart_type' => 'gauge',
                    'show_trend' => true,
                    'show_targets' => true,
                    'refresh_interval' => 300
                ]),
                'chart_type' => fake()->randomElement(['line', 'bar', 'gauge', 'number']),
                'dashboard_order' => fake()->numberBetween(1, 20),
                'auto_calculate' => fake()->boolean(85),
                'calculation_time' => '09:00:00',
                'automation_rules' => json_encode([
                    'trigger' => 'daily',
                    'data_sources' => ['operational_systems'],
                    'calculation_method' => 'automatic'
                ]),
                'last_calculated_at' => fake()->optional(0.8)->dateTimeBetween($measurementTimestamp, 'now'),
                'calculation_attempts' => fake()->numberBetween(0, 3),
                'last_calculation_error' => fake()->optional(0.1)->sentence,
                'tags' => json_encode(['operational', 'efficiency', 'performance']),
                'metadata' => json_encode([
                    'version' => '1.0',
                    'calculation_standard' => 'ISO_50001',
                    'last_updated' => now()->toISOString()
                ]),
                'notes' => fake()->optional(0.4)->sentence,
                'is_public' => fake()->boolean(40),
                'public_name' => fake()->optional(0.3)->sentence,
            ]);
        }
    }

    private function createFinancialIndicators($users, $energyCooperatives, $energyReports): void
    {
        $this->command->info('💰 Creando indicadores financieros...');

        for ($i = 0; $i < 15; $i++) {
            $user = $users->random();
            $cooperative = $energyCooperatives->isEmpty() ? null : $energyCooperatives->random();
            $energyReport = $energyReports->isEmpty() ? null : $energyReports->random();
            $measurementTimestamp = Carbon::now()->subDays(rand(1, 90));
            $periodStart = $measurementTimestamp->copy()->subDays(rand(7, 30));
            $periodEnd = $measurementTimestamp;

            $currentValue = fake()->randomFloat(2, 1000, 50000);
            $baselineValue = $currentValue * fake()->randomFloat(2, 0.8, 1.2);
            $targetValue = $currentValue * fake()->randomFloat(2, 1.1, 1.4);
            $previousValue = $currentValue * fake()->randomFloat(2, 0.9, 1.1);
            $changeAbsolute = $currentValue - $previousValue;
            $changePercentage = ($changeAbsolute / $previousValue) * 100;

            PerformanceIndicator::create([
                'indicator_name' => fake()->randomElement([
                    'ROI Energético',
                    'Coste por kWh',
                    'Ingresos por Usuario',
                    'Margen de Beneficio',
                    'Retorno de Inversión',
                    'Coste Operacional',
                    'Ingresos Totales',
                    'Beneficio Neto'
                ]),
                'indicator_code' => 'PI-FIN-' . strtoupper(fake()->bothify('####-??')),
                'description' => fake()->sentence,
                'indicator_type' => 'kpi',
                'category' => 'financial',
                'criticality' => fake()->randomElement(['high', 'critical']),
                'priority' => fake()->numberBetween(3, 5),
                'frequency' => fake()->randomElement(['weekly', 'monthly', 'quarterly']),
                'is_active' => fake()->boolean(95),
                'scope' => fake()->randomElement(['cooperative', 'system']),
                'entity_type' => 'cooperative',
                'entity_id' => $cooperative?->id ?? $user->id,
                'entity_name' => $cooperative?->name ?? $user->name,
                'measurement_timestamp' => $measurementTimestamp,
                'measurement_date' => $measurementTimestamp->toDateString(),
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'period_type' => fake()->randomElement(['monthly', 'quarterly']),
                'current_value' => $currentValue,
                'unit' => 'EUR',
                'target_value' => $targetValue,
                'baseline_value' => $baselineValue,
                'previous_value' => $previousValue,
                'change_absolute' => $changeAbsolute,
                'change_percentage' => $changePercentage,
                'target_achievement_percentage' => ($currentValue / $targetValue) * 100,
                'trend_direction' => $changePercentage > 0 ? 'up' : 'down',
                'performance_status' => $changePercentage > 10 ? 'excellent' : ($changePercentage > 0 ? 'good' : ($changePercentage > -10 ? 'acceptable' : 'poor')),
                'calculation_method' => 'automatic',
                'confidence_level' => fake()->randomFloat(2, 90, 99),
                'industry_benchmark' => fake()->randomFloat(2, 800, 45000),
                'business_impact' => fake()->randomElement(['high', 'strategic']),
                'cost_per_unit' => fake()->randomFloat(4, 0.1, 0.5),
                'revenue_impact_eur' => fake()->randomFloat(2, 500, 25000),
                'roi_percentage' => fake()->randomFloat(2, 5, 25),
                'payback_months' => fake()->randomFloat(2, 12, 60),
                'alerts_enabled' => fake()->boolean(80),
                'current_alert_level' => fake()->randomElement(['normal', 'warning', 'critical']),
                'improvement_potential' => fake()->randomFloat(2, 10, 40),
                'energy_cooperative_id' => $cooperative?->id,
                'user_id' => $user->id,
                'energy_report_id' => $energyReport?->id,
                'created_by_id' => $user->id,
                'is_validated' => fake()->boolean(85),
                'show_in_dashboard' => fake()->boolean(90),
                'chart_type' => fake()->randomElement(['line', 'bar', 'number']),
                'auto_calculate' => fake()->boolean(90),
                'tags' => json_encode(['financial', 'kpi', 'roi']),
            ]);
        }
    }

    private function createTechnicalIndicators($users, $energyCooperatives, $energyReports): void
    {
        $this->command->info('🔧 Creando indicadores técnicos...');

        for ($i = 0; $i < 18; $i++) {
            $user = $users->random();
            $cooperative = $energyCooperatives->isEmpty() ? null : $energyCooperatives->random();
            $energyReport = $energyReports->isEmpty() ? null : $energyReports->random();
            $measurementTimestamp = Carbon::now()->subDays(rand(1, 90));
            $periodStart = $measurementTimestamp->copy()->subDays(rand(7, 30));
            $periodEnd = $measurementTimestamp;

            $currentValue = fake()->randomFloat(2, 10, 1000);
            $baselineValue = $currentValue * fake()->randomFloat(2, 0.8, 1.2);
            $targetValue = $currentValue * fake()->randomFloat(2, 0.7, 0.9);
            $previousValue = $currentValue * fake()->randomFloat(2, 0.9, 1.1);

            PerformanceIndicator::create([
                'indicator_name' => fake()->randomElement([
                    'Tiempo de Respuesta del Sistema',
                    'Rendimiento de Red',
                    'Capacidad de Procesamiento',
                    'Eficiencia Técnica',
                    'Disponibilidad del Servicio',
                    'Latencia de Red',
                    'Throughput del Sistema',
                    'Rendimiento de Base de Datos'
                ]),
                'indicator_code' => 'PI-TEC-' . strtoupper(fake()->bothify('####-??')),
                'description' => fake()->sentence,
                'indicator_type' => 'metric',
                'category' => 'technical',
                'criticality' => fake()->randomElement(['medium', 'high']),
                'priority' => fake()->numberBetween(2, 4),
                'frequency' => fake()->randomElement(['hourly', 'daily', 'weekly']),
                'is_active' => fake()->boolean(85),
                'scope' => 'system',
                'entity_type' => 'system',
                'entity_id' => 1,
                'entity_name' => 'Sistema Principal',
                'measurement_timestamp' => $measurementTimestamp,
                'measurement_date' => $measurementTimestamp->toDateString(),
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'period_type' => fake()->randomElement(['instant', 'daily', 'weekly']),
                'current_value' => $currentValue,
                'unit' => fake()->randomElement(['ms', 'requests/s', 'MB/s', '%']),
                'target_value' => $targetValue,
                'baseline_value' => $baselineValue,
                'previous_value' => $previousValue,
                'system_load_percentage' => fake()->randomFloat(2, 20, 80),
                'response_time_ms' => fake()->randomFloat(2, 50, 500),
                'throughput_per_hour' => fake()->randomFloat(2, 100, 10000),
                'concurrent_users' => fake()->numberBetween(10, 1000),
                'alerts_enabled' => fake()->boolean(70),
                'current_alert_level' => fake()->randomElement(['normal', 'warning', 'critical']),
                'energy_cooperative_id' => $cooperative?->id,
                'user_id' => $user->id,
                'energy_report_id' => $energyReport?->id,
                'created_by_id' => $user->id,
                'is_validated' => fake()->boolean(75),
                'show_in_dashboard' => fake()->boolean(70),
                'chart_type' => fake()->randomElement(['line', 'bar', 'gauge']),
                'auto_calculate' => fake()->boolean(90),
                'tags' => json_encode(['technical', 'performance', 'system']),
            ]);
        }
    }

    private function createCustomerIndicators($users, $energyCooperatives, $energyReports): void
    {
        $this->command->info('👥 Creando indicadores de cliente...');

        for ($i = 0; $i < 12; $i++) {
            $user = $users->random();
            $cooperative = $energyCooperatives->isEmpty() ? null : $energyCooperatives->random();
            $energyReport = $energyReports->isEmpty() ? null : $energyReports->random();
            $measurementTimestamp = Carbon::now()->subDays(rand(1, 90));
            $periodStart = $measurementTimestamp->copy()->subDays(rand(7, 30));
            $periodEnd = $measurementTimestamp;

            $currentValue = fake()->randomFloat(2, 6, 10);

            PerformanceIndicator::create([
                'indicator_name' => fake()->randomElement([
                    'Satisfacción del Cliente',
                    'NPS (Net Promoter Score)',
                    'Tiempo de Resolución',
                    'Retención de Clientes',
                    'Adopción de Servicios',
                    'Experiencia del Usuario',
                    'Soporte al Cliente',
                    'Lealtad del Cliente'
                ]),
                'indicator_code' => 'PI-CUS-' . strtoupper(fake()->bothify('####-??')),
                'description' => fake()->sentence,
                'indicator_type' => 'satisfaction',
                'category' => 'customer',
                'criticality' => fake()->randomElement(['medium', 'high']),
                'priority' => fake()->numberBetween(2, 4),
                'frequency' => fake()->randomElement(['weekly', 'monthly', 'quarterly']),
                'is_active' => fake()->boolean(90),
                'scope' => fake()->randomElement(['cooperative', 'system']),
                'entity_type' => 'cooperative',
                'entity_id' => $cooperative?->id ?? 1,
                'entity_name' => $cooperative?->name ?? 'Sistema',
                'measurement_timestamp' => $measurementTimestamp,
                'measurement_date' => $measurementTimestamp->toDateString(),
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'period_type' => fake()->randomElement(['weekly', 'monthly']),
                'current_value' => $currentValue,
                'unit' => 'puntos',
                'target_value' => $currentValue * fake()->randomFloat(2, 1.05, 1.15),
                'baseline_value' => $currentValue * fake()->randomFloat(2, 0.9, 1.1),
                'satisfaction_score' => $currentValue,
                'alerts_enabled' => fake()->boolean(60),
                'current_alert_level' => fake()->randomElement(['normal', 'warning', 'critical']),
                'energy_cooperative_id' => $cooperative?->id,
                'user_id' => $user->id,
                'energy_report_id' => $energyReport?->id,
                'created_by_id' => $user->id,
                'is_validated' => fake()->boolean(80),
                'show_in_dashboard' => fake()->boolean(85),
                'chart_type' => fake()->randomElement(['gauge', 'line', 'number']),
                'auto_calculate' => fake()->boolean(70),
                'tags' => json_encode(['customer', 'satisfaction', 'nps']),
            ]);
        }
    }

    private function createEnvironmentalIndicators($users, $energyCooperatives, $energyReports): void
    {
        $this->command->info('🌱 Creando indicadores ambientales...');

        for ($i = 0; $i < 10; $i++) {
            $user = $users->random();
            $cooperative = $energyCooperatives->isEmpty() ? null : $energyCooperatives->random();
            $energyReport = $energyReports->isEmpty() ? null : $energyReports->random();
            $measurementTimestamp = Carbon::now()->subDays(rand(1, 90));
            $periodStart = $measurementTimestamp->copy()->subDays(rand(7, 30));
            $periodEnd = $measurementTimestamp;

            $currentValue = fake()->randomFloat(2, 50, 500);

            PerformanceIndicator::create([
                'indicator_name' => fake()->randomElement([
                    'Reducción de Emisiones CO2',
                    'Energía Renovable Utilizada',
                    'Eficiencia Energética',
                    'Huella de Carbono',
                    'Consumo de Recursos',
                    'Impacto Ambiental',
                    'Sostenibilidad',
                    'Gestión de Residuos'
                ]),
                'indicator_code' => 'PI-ENV-' . strtoupper(fake()->bothify('####-??')),
                'description' => fake()->sentence,
                'indicator_type' => 'kpi',
                'category' => 'environmental',
                'criticality' => fake()->randomElement(['high', 'critical']),
                'priority' => fake()->numberBetween(3, 5),
                'frequency' => fake()->randomElement(['monthly', 'quarterly']),
                'is_active' => fake()->boolean(95),
                'scope' => fake()->randomElement(['cooperative', 'system']),
                'entity_type' => 'cooperative',
                'entity_id' => $cooperative?->id ?? 1,
                'entity_name' => $cooperative?->name ?? 'Sistema',
                'measurement_timestamp' => $measurementTimestamp,
                'measurement_date' => $measurementTimestamp->toDateString(),
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'period_type' => fake()->randomElement(['monthly', 'quarterly']),
                'current_value' => $currentValue,
                'unit' => fake()->randomElement(['kg CO2', 'kWh', '%', 'toneladas']),
                'target_value' => $currentValue * fake()->randomFloat(2, 0.7, 0.9),
                'baseline_value' => $currentValue * fake()->randomFloat(2, 1.1, 1.3),
                'alerts_enabled' => fake()->boolean(80),
                'current_alert_level' => fake()->randomElement(['normal', 'warning', 'critical']),
                'energy_cooperative_id' => $cooperative?->id,
                'user_id' => $user->id,
                'energy_report_id' => $energyReport?->id,
                'created_by_id' => $user->id,
                'is_validated' => fake()->boolean(85),
                'show_in_dashboard' => fake()->boolean(90),
                'chart_type' => fake()->randomElement(['line', 'bar', 'gauge']),
                'auto_calculate' => fake()->boolean(85),
                'tags' => json_encode(['environmental', 'sustainability', 'co2']),
            ]);
        }
    }

    private function createQualityIndicators($users, $energyCooperatives, $energyReports): void
    {
        $this->command->info('🎯 Creando indicadores de calidad...');

        for ($i = 0; $i < 8; $i++) {
            $user = $users->random();
            $cooperative = $energyCooperatives->isEmpty() ? null : $energyCooperatives->random();
            $energyReport = $energyReports->isEmpty() ? null : $energyReports->random();
            $measurementTimestamp = Carbon::now()->subDays(rand(1, 90));
            $periodStart = $measurementTimestamp->copy()->subDays(rand(7, 30));
            $periodEnd = $measurementTimestamp;

            $currentValue = fake()->randomFloat(2, 80, 98);

            PerformanceIndicator::create([
                'indicator_name' => fake()->randomElement([
                    'Calidad del Servicio',
                    'Tasa de Errores',
                    'Precisión de Datos',
                    'Confiabilidad del Sistema',
                    'Calidad de Energía',
                    'Cumplimiento de Estándares',
                    'Calidad de Procesos',
                    'Gestión de Calidad'
                ]),
                'indicator_code' => 'PI-QUA-' . strtoupper(fake()->bothify('####-??')),
                'description' => fake()->sentence,
                'indicator_type' => 'quality',
                'category' => 'quality',
                'criticality' => fake()->randomElement(['medium', 'high']),
                'priority' => fake()->numberBetween(2, 4),
                'frequency' => fake()->randomElement(['daily', 'weekly', 'monthly']),
                'is_active' => fake()->boolean(90),
                'scope' => fake()->randomElement(['system', 'cooperative']),
                'entity_type' => 'system',
                'entity_id' => 1,
                'entity_name' => 'Sistema de Calidad',
                'measurement_timestamp' => $measurementTimestamp,
                'measurement_date' => $measurementTimestamp->toDateString(),
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'period_type' => fake()->randomElement(['daily', 'weekly']),
                'current_value' => $currentValue,
                'unit' => '%',
                'target_value' => $currentValue * fake()->randomFloat(2, 1.02, 1.05),
                'baseline_value' => $currentValue * fake()->randomFloat(2, 0.95, 1.02),
                'quality_score' => $currentValue,
                'defects_count' => fake()->optional(0.4)->numberBetween(0, 50),
                'error_rate_percentage' => fake()->optional(0.4)->randomFloat(2, 0.1, 5),
                'alerts_enabled' => fake()->boolean(70),
                'current_alert_level' => fake()->randomElement(['normal', 'warning', 'critical']),
                'energy_cooperative_id' => $cooperative?->id,
                'user_id' => $user->id,
                'energy_report_id' => $energyReport?->id,
                'created_by_id' => $user->id,
                'is_validated' => fake()->boolean(80),
                'show_in_dashboard' => fake()->boolean(75),
                'chart_type' => fake()->randomElement(['gauge', 'line', 'number']),
                'auto_calculate' => fake()->boolean(80),
                'tags' => json_encode(['quality', 'reliability', 'standards']),
            ]);
        }
    }

    private function createStrategicIndicators($users, $energyCooperatives, $energyReports): void
    {
        $this->command->info('🎯 Creando indicadores estratégicos...');

        for ($i = 0; $i < 7; $i++) {
            $user = $users->random();
            $cooperative = $energyCooperatives->isEmpty() ? null : $energyCooperatives->random();
            $energyReport = $energyReports->isEmpty() ? null : $energyReports->random();
            $measurementTimestamp = Carbon::now()->subDays(rand(1, 90));
            $periodStart = $measurementTimestamp->copy()->subDays(rand(7, 30));
            $periodEnd = $measurementTimestamp;

            $currentValue = fake()->randomFloat(2, 1000, 100000);

            PerformanceIndicator::create([
                'indicator_name' => fake()->randomElement([
                    'Crecimiento de Mercado',
                    'Participación de Mercado',
                    'Innovación Tecnológica',
                    'Desarrollo Sostenible',
                    'Expansión Geográfica',
                    'Competitividad',
                    'Liderazgo del Sector',
                    'Transformación Digital'
                ]),
                'indicator_code' => 'PI-STR-' . strtoupper(fake()->bothify('####-??')),
                'description' => fake()->sentence,
                'indicator_type' => 'kpi',
                'category' => 'strategic',
                'criticality' => 'critical',
                'priority' => 5,
                'frequency' => fake()->randomElement(['monthly', 'quarterly', 'yearly']),
                'is_active' => fake()->boolean(100),
                'scope' => 'system',
                'entity_type' => 'system',
                'entity_id' => 1,
                'entity_name' => 'Sistema Estratégico',
                'measurement_timestamp' => $measurementTimestamp,
                'measurement_date' => $measurementTimestamp->toDateString(),
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'period_type' => fake()->randomElement(['monthly', 'quarterly']),
                'current_value' => $currentValue,
                'unit' => fake()->randomElement(['EUR', '%', 'usuarios', 'MW']),
                'target_value' => $currentValue * fake()->randomFloat(2, 1.1, 1.5),
                'baseline_value' => $currentValue * fake()->randomFloat(2, 0.8, 1.2),
                'business_impact' => 'strategic',
                'financial_impact_eur' => fake()->randomFloat(2, 5000, 100000),
                'alerts_enabled' => fake()->boolean(90),
                'current_alert_level' => fake()->randomElement(['normal', 'warning', 'critical']),
                'energy_cooperative_id' => $cooperative?->id,
                'user_id' => $user->id,
                'energy_report_id' => $energyReport?->id,
                'created_by_id' => $user->id,
                'is_validated' => fake()->boolean(90),
                'show_in_dashboard' => fake()->boolean(100),
                'chart_type' => fake()->randomElement(['line', 'bar', 'number']),
                'auto_calculate' => fake()->boolean(95),
                'tags' => json_encode(['strategic', 'growth', 'leadership']),
            ]);
        }
    }
}
