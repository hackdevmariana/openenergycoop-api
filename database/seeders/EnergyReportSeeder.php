<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EnergyReport;
use App\Models\User;
use App\Models\EnergyCooperative;
use Carbon\Carbon;

class EnergyReportSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📊 Creando reportes energéticos...');

        $users = User::all();
        $energyCooperatives = EnergyCooperative::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles.');
            return;
        }

        // Limpiar datos existentes
        EnergyReport::query()->delete();

        $this->command->info("👥 Usuarios disponibles: {$users->count()}");
        $this->command->info("🏭 Cooperativas disponibles: {$energyCooperatives->count()}");

        // Crear diferentes tipos de reportes
        $this->createConsumptionReports($users, $energyCooperatives);
        $this->createProductionReports($users, $energyCooperatives);
        $this->createTradingReports($users, $energyCooperatives);
        $this->createSavingsReports($users, $energyCooperatives);
        $this->createCooperativeReports($users, $energyCooperatives);
        $this->createSystemReports($users, $energyCooperatives);

        $this->command->info('✅ EnergyReportSeeder completado. Se crearon ' . EnergyReport::count() . ' reportes.');
    }

    private function createConsumptionReports($users, $energyCooperatives): void
    {
        $this->command->info('⚡ Creando reportes de consumo...');

        for ($i = 0; $i < 20; $i++) {
            $user = $users->random();
            $cooperative = $energyCooperatives->isEmpty() ? null : $energyCooperatives->random();
            $periodStart = Carbon::now()->subMonths(rand(1, 6));
            $periodEnd = $periodStart->copy()->addMonths(rand(1, 3));

            EnergyReport::create([
                'title' => fake()->randomElement([
                    'Reporte de Consumo Energético Mensual',
                    'Análisis de Consumo por Usuario',
                    'Consumo Energético Detallado',
                    'Reporte de Eficiencia Energética',
                    'Análisis de Patrones de Consumo'
                ]),
                'report_code' => 'RPT-CON-' . strtoupper(fake()->bothify('####-??')),
                'description' => fake()->paragraph,
                'report_type' => 'consumption',
                'report_category' => fake()->randomElement(['energy', 'operational', 'performance']),
                'scope' => fake()->randomElement(['user', 'cooperative', 'system']),
                'scope_filters' => json_encode([
                    'user_ids' => [$user->id],
                    'include_details' => true,
                    'group_by' => 'daily'
                ]),
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'period_type' => fake()->randomElement(['monthly', 'quarterly']),
                'generation_frequency' => fake()->randomElement(['monthly', 'quarterly']),
                'generation_time' => fake()->time,
                'generation_config' => json_encode([
                    'include_charts' => true,
                    'include_recommendations' => true,
                    'export_formats' => ['pdf', 'excel']
                ]),
                'auto_generate' => fake()->boolean(70),
                'send_notifications' => fake()->boolean(80),
                'notification_recipients' => json_encode([$user->email]),
                'status' => fake()->randomElement(['completed', 'completed', 'completed', 'draft']),
                'generated_at' => fake()->optional(0.8)->dateTimeBetween($periodEnd, 'now'),
                'generation_attempts' => fake()->numberBetween(0, 2),
                'file_size_bytes' => fake()->optional(0.7)->numberBetween(100000, 5000000),
                'data_summary' => json_encode([
                    'total_consumption_kwh' => fake()->randomFloat(2, 1000, 5000),
                    'average_daily_consumption' => fake()->randomFloat(2, 30, 150),
                    'peak_consumption_hour' => fake()->numberBetween(8, 22),
                    'cost_total_eur' => fake()->randomFloat(2, 200, 1000),
                    'efficiency_score' => fake()->randomFloat(2, 60, 95)
                ]),
                'metrics' => json_encode([
                    'total_kwh' => fake()->randomFloat(2, 1000, 5000),
                    'average_daily_kwh' => fake()->randomFloat(2, 30, 150),
                    'peak_demand_kw' => fake()->randomFloat(2, 5, 25),
                    'cost_per_kwh' => fake()->randomFloat(4, 0.10, 0.30),
                    'efficiency_percentage' => fake()->randomFloat(2, 60, 95)
                ]),
                'charts_config' => json_encode([
                    'daily_consumption_chart' => true,
                    'hourly_pattern_chart' => true,
                    'cost_analysis_chart' => true,
                    'efficiency_trend_chart' => true
                ]),
                'tables_data' => json_encode([
                    'daily_breakdown' => [
                        ['date' => '2024-01-01', 'consumption' => 45.2, 'cost' => 12.50],
                        ['date' => '2024-01-02', 'consumption' => 42.8, 'cost' => 11.80],
                        ['date' => '2024-01-03', 'consumption' => 48.5, 'cost' => 13.40]
                    ]
                ]),
                'insights' => fake()->paragraphs(2, true),
                'recommendations' => fake()->paragraphs(2, true),
                'pdf_path' => fake()->optional(0.7)->filePath(),
                'excel_path' => fake()->optional(0.6)->filePath(),
                'csv_path' => fake()->optional(0.5)->filePath(),
                'export_formats' => json_encode(['pdf', 'excel', 'csv']),
                'dashboard_config' => json_encode([
                    'show_charts' => true,
                    'show_metrics' => true,
                    'refresh_interval' => 300
                ]),
                'is_public' => fake()->boolean(20),
                'public_share_token' => fake()->optional(0.2)->sha256,
                'public_expires_at' => fake()->optional(0.2)->dateTimeBetween('+1 day', '+30 days'),
                'access_permissions' => json_encode(['view', 'download']),
                'total_records_processed' => fake()->numberBetween(100, 1000),
                'processing_time_seconds' => fake()->randomFloat(3, 2, 60),
                'data_quality_score' => fake()->numberBetween(85, 100),
                'data_sources' => json_encode(['energy_readings', 'user_profiles', 'billing_data']),
                'include_comparison' => fake()->boolean(60),
                'comparison_period_start' => fake()->optional(0.6)->dateTimeBetween('-12 months', '-7 months'),
                'comparison_period_end' => fake()->optional(0.6)->dateTimeBetween('-7 months', '-1 month'),
                'comparison_metrics' => json_encode([
                    'consumption_change_percentage' => fake()->randomFloat(2, -20, 20),
                    'cost_change_percentage' => fake()->randomFloat(2, -15, 25),
                    'efficiency_change_percentage' => fake()->randomFloat(2, -10, 15)
                ]),
                'user_id' => $user->id,
                'energy_cooperative_id' => $cooperative?->id,
                'created_by_id' => $user->id,
                'cache_enabled' => fake()->boolean(80),
                'cache_duration_minutes' => fake()->numberBetween(30, 1440),
                'cache_expires_at' => fake()->optional(0.8)->dateTimeBetween('+1 hour', '+24 hours'),
                'cache_key' => fake()->optional(0.8)->md5,
                'view_count' => fake()->numberBetween(0, 50),
                'download_count' => fake()->numberBetween(0, 20),
                'last_viewed_at' => fake()->optional(0.6)->dateTimeBetween('-1 week', 'now'),
                'last_downloaded_at' => fake()->optional(0.4)->dateTimeBetween('-1 week', 'now'),
                'viewer_stats' => json_encode([
                    'unique_viewers' => fake()->numberBetween(1, 10),
                    'average_view_time' => fake()->numberBetween(30, 300),
                    'most_viewed_section' => 'charts'
                ]),
                'tags' => json_encode(['consumption', 'energy', 'analysis', 'monthly']),
                'metadata' => json_encode([
                    'generated_by' => 'system',
                    'version' => '1.0',
                    'template_used' => 'consumption_standard'
                ]),
                'notes' => fake()->optional(0.3)->sentence,
                'priority' => fake()->numberBetween(1, 3),
            ]);
        }
    }

    private function createProductionReports($users, $energyCooperatives): void
    {
        $this->command->info('☀️ Creando reportes de producción...');

        for ($i = 0; $i < 15; $i++) {
            $user = $users->random();
            $cooperative = $energyCooperatives->isEmpty() ? null : $energyCooperatives->random();
            $periodStart = Carbon::now()->subMonths(rand(1, 6));
            $periodEnd = $periodStart->copy()->addMonths(rand(1, 3));

            EnergyReport::create([
                'title' => fake()->randomElement([
                    'Reporte de Producción Energética',
                    'Análisis de Generación Renovable',
                    'Producción Solar Mensual',
                    'Reporte de Eficiencia de Producción',
                    'Análisis de Rendimiento de Instalaciones'
                ]),
                'report_code' => 'RPT-PRO-' . strtoupper(fake()->bothify('####-??')),
                'description' => fake()->paragraph,
                'report_type' => 'production',
                'report_category' => fake()->randomElement(['energy', 'environmental', 'performance']),
                'scope' => fake()->randomElement(['user', 'cooperative', 'system']),
                'scope_filters' => json_encode([
                    'installation_ids' => fake()->numberBetween(1, 10),
                    'include_weather_data' => true,
                    'group_by' => 'daily'
                ]),
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'period_type' => fake()->randomElement(['monthly', 'quarterly']),
                'generation_frequency' => fake()->randomElement(['monthly', 'quarterly']),
                'generation_time' => fake()->time,
                'auto_generate' => fake()->boolean(80),
                'send_notifications' => fake()->boolean(90),
                'status' => fake()->randomElement(['completed', 'completed', 'completed', 'draft']),
                'generated_at' => fake()->optional(0.8)->dateTimeBetween($periodEnd, 'now'),
                'data_summary' => json_encode([
                    'total_production_kwh' => fake()->randomFloat(2, 2000, 8000),
                    'average_daily_production' => fake()->randomFloat(2, 60, 250),
                    'peak_production_hour' => fake()->numberBetween(10, 16),
                    'revenue_total_eur' => fake()->randomFloat(2, 300, 1500),
                    'efficiency_score' => fake()->randomFloat(2, 75, 98)
                ]),
                'metrics' => json_encode([
                    'total_kwh' => fake()->randomFloat(2, 2000, 8000),
                    'average_daily_kwh' => fake()->randomFloat(2, 60, 250),
                    'peak_power_kw' => fake()->randomFloat(2, 10, 50),
                    'revenue_per_kwh' => fake()->randomFloat(4, 0.05, 0.20),
                    'efficiency_percentage' => fake()->randomFloat(2, 75, 98)
                ]),
                'insights' => fake()->paragraphs(2, true),
                'recommendations' => fake()->paragraphs(2, true),
                'pdf_path' => fake()->optional(0.7)->filePath(),
                'excel_path' => fake()->optional(0.6)->filePath(),
                'export_formats' => json_encode(['pdf', 'excel', 'csv']),
                'total_records_processed' => fake()->numberBetween(200, 2000),
                'processing_time_seconds' => fake()->randomFloat(3, 3, 90),
                'data_quality_score' => fake()->numberBetween(90, 100),
                'data_sources' => json_encode(['energy_productions', 'weather_data', 'installation_data']),
                'user_id' => $user->id,
                'energy_cooperative_id' => $cooperative?->id,
                'created_by_id' => $user->id,
                'view_count' => fake()->numberBetween(0, 30),
                'download_count' => fake()->numberBetween(0, 15),
                'tags' => json_encode(['production', 'renewable', 'solar', 'analysis']),
                'priority' => fake()->numberBetween(1, 4),
            ]);
        }
    }

    private function createTradingReports($users, $energyCooperatives): void
    {
        $this->command->info('💰 Creando reportes de trading...');

        for ($i = 0; $i < 10; $i++) {
            $user = $users->random();
            $cooperative = $energyCooperatives->isEmpty() ? null : $energyCooperatives->random();
            $periodStart = Carbon::now()->subMonths(rand(1, 6));
            $periodEnd = $periodStart->copy()->addMonths(rand(1, 3));

            EnergyReport::create([
                'title' => fake()->randomElement([
                    'Reporte de Trading Energético',
                    'Análisis de Mercado Energético',
                    'Reporte de Transacciones',
                    'Análisis de Precios de Mercado',
                    'Reporte de Volumen de Trading'
                ]),
                'report_code' => 'RPT-TRA-' . strtoupper(fake()->bothify('####-??')),
                'description' => fake()->paragraph,
                'report_type' => 'trading',
                'report_category' => fake()->randomElement(['financial', 'operational']),
                'scope' => fake()->randomElement(['cooperative', 'system']),
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'period_type' => fake()->randomElement(['monthly', 'quarterly']),
                'generation_frequency' => fake()->randomElement(['weekly', 'monthly']),
                'status' => fake()->randomElement(['completed', 'completed', 'draft']),
                'generated_at' => fake()->optional(0.8)->dateTimeBetween($periodEnd, 'now'),
                'data_summary' => json_encode([
                    'total_volume_mwh' => fake()->randomFloat(2, 1000, 10000),
                    'average_price_eur_mwh' => fake()->randomFloat(4, 50, 200),
                    'total_revenue_eur' => fake()->randomFloat(2, 50000, 500000),
                    'number_of_transactions' => fake()->numberBetween(50, 500),
                    'profit_margin_percentage' => fake()->randomFloat(2, 5, 25)
                ]),
                'metrics' => json_encode([
                    'volume_traded_mwh' => fake()->randomFloat(2, 1000, 10000),
                    'average_price_eur_mwh' => fake()->randomFloat(4, 50, 200),
                    'total_revenue_eur' => fake()->randomFloat(2, 50000, 500000),
                    'transaction_count' => fake()->numberBetween(50, 500),
                    'profit_margin_percentage' => fake()->randomFloat(2, 5, 25)
                ]),
                'insights' => fake()->paragraphs(2, true),
                'recommendations' => fake()->paragraphs(2, true),
                'pdf_path' => fake()->optional(0.7)->filePath(),
                'excel_path' => fake()->optional(0.6)->filePath(),
                'export_formats' => json_encode(['pdf', 'excel']),
                'total_records_processed' => fake()->numberBetween(500, 5000),
                'processing_time_seconds' => fake()->randomFloat(3, 5, 120),
                'data_quality_score' => fake()->numberBetween(85, 100),
                'data_sources' => json_encode(['market_prices', 'energy_trades', 'billing_data']),
                'user_id' => $user->id,
                'energy_cooperative_id' => $cooperative?->id,
                'created_by_id' => $user->id,
                'view_count' => fake()->numberBetween(0, 20),
                'download_count' => fake()->numberBetween(0, 10),
                'tags' => json_encode(['trading', 'market', 'financial', 'analysis']),
                'priority' => fake()->numberBetween(2, 5),
            ]);
        }
    }

    private function createSavingsReports($users, $energyCooperatives): void
    {
        $this->command->info('💚 Creando reportes de ahorros...');

        for ($i = 0; $i < 12; $i++) {
            $user = $users->random();
            $cooperative = $energyCooperatives->isEmpty() ? null : $energyCooperatives->random();
            $periodStart = Carbon::now()->subMonths(rand(1, 6));
            $periodEnd = $periodStart->copy()->addMonths(rand(1, 3));

            EnergyReport::create([
                'title' => fake()->randomElement([
                    'Reporte de Ahorros Energéticos',
                    'Análisis de Eficiencia y Ahorros',
                    'Reporte de Optimización Energética',
                    'Análisis de Reducción de Costos',
                    'Reporte de Sostenibilidad'
                ]),
                'report_code' => 'RPT-SAV-' . strtoupper(fake()->bothify('####-??')),
                'description' => fake()->paragraph,
                'report_type' => 'savings',
                'report_category' => fake()->randomElement(['financial', 'environmental', 'performance']),
                'scope' => fake()->randomElement(['user', 'cooperative', 'system']),
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'period_type' => fake()->randomElement(['monthly', 'quarterly']),
                'generation_frequency' => fake()->randomElement(['monthly', 'quarterly']),
                'status' => fake()->randomElement(['completed', 'completed', 'draft']),
                'generated_at' => fake()->optional(0.8)->dateTimeBetween($periodEnd, 'now'),
                'data_summary' => json_encode([
                    'total_savings_eur' => fake()->randomFloat(2, 500, 5000),
                    'energy_saved_kwh' => fake()->randomFloat(2, 1000, 10000),
                    'co2_reduced_kg' => fake()->randomFloat(2, 500, 5000),
                    'efficiency_improvement_percentage' => fake()->randomFloat(2, 10, 40),
                    'roi_percentage' => fake()->randomFloat(2, 15, 60)
                ]),
                'metrics' => json_encode([
                    'savings_eur' => fake()->randomFloat(2, 500, 5000),
                    'energy_saved_kwh' => fake()->randomFloat(2, 1000, 10000),
                    'co2_reduced_kg' => fake()->randomFloat(2, 500, 5000),
                    'efficiency_improvement_percentage' => fake()->randomFloat(2, 10, 40),
                    'roi_percentage' => fake()->randomFloat(2, 15, 60)
                ]),
                'insights' => fake()->paragraphs(2, true),
                'recommendations' => fake()->paragraphs(2, true),
                'pdf_path' => fake()->optional(0.7)->filePath(),
                'excel_path' => fake()->optional(0.6)->filePath(),
                'export_formats' => json_encode(['pdf', 'excel', 'csv']),
                'total_records_processed' => fake()->numberBetween(300, 3000),
                'processing_time_seconds' => fake()->randomFloat(3, 4, 100),
                'data_quality_score' => fake()->numberBetween(80, 100),
                'data_sources' => json_encode(['energy_readings', 'billing_data', 'efficiency_metrics']),
                'user_id' => $user->id,
                'energy_cooperative_id' => $cooperative?->id,
                'created_by_id' => $user->id,
                'view_count' => fake()->numberBetween(0, 25),
                'download_count' => fake()->numberBetween(0, 12),
                'tags' => json_encode(['savings', 'efficiency', 'sustainability', 'optimization']),
                'priority' => fake()->numberBetween(1, 4),
            ]);
        }
    }

    private function createCooperativeReports($users, $energyCooperatives): void
    {
        $this->command->info('🏭 Creando reportes de cooperativas...');

        for ($i = 0; $i < 8; $i++) {
            $user = $users->random();
            $cooperative = $energyCooperatives->isEmpty() ? null : $energyCooperatives->random();
            $periodStart = Carbon::now()->subMonths(rand(1, 6));
            $periodEnd = $periodStart->copy()->addMonths(rand(1, 3));

            EnergyReport::create([
                'title' => fake()->randomElement([
                    'Reporte de Cooperativa Energética',
                    'Análisis de Rendimiento Cooperativo',
                    'Reporte de Distribución de Beneficios',
                    'Análisis de Participación de Miembros',
                    'Reporte de Gestión Cooperativa'
                ]),
                'report_code' => 'RPT-COO-' . strtoupper(fake()->bothify('####-??')),
                'description' => fake()->paragraph,
                'report_type' => 'cooperative',
                'report_category' => fake()->randomElement(['operational', 'financial', 'performance']),
                'scope' => 'cooperative',
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'period_type' => fake()->randomElement(['monthly', 'quarterly']),
                'generation_frequency' => fake()->randomElement(['monthly', 'quarterly']),
                'status' => fake()->randomElement(['completed', 'completed', 'draft']),
                'generated_at' => fake()->optional(0.8)->dateTimeBetween($periodEnd, 'now'),
                'data_summary' => json_encode([
                    'total_members' => fake()->numberBetween(50, 500),
                    'total_energy_shared_mwh' => fake()->randomFloat(2, 1000, 10000),
                    'average_benefit_per_member_eur' => fake()->randomFloat(2, 50, 500),
                    'cooperative_efficiency_percentage' => fake()->randomFloat(2, 70, 95),
                    'total_revenue_eur' => fake()->randomFloat(2, 50000, 500000)
                ]),
                'metrics' => json_encode([
                    'member_count' => fake()->numberBetween(50, 500),
                    'energy_shared_mwh' => fake()->randomFloat(2, 1000, 10000),
                    'benefit_per_member_eur' => fake()->randomFloat(2, 50, 500),
                    'efficiency_percentage' => fake()->randomFloat(2, 70, 95),
                    'total_revenue_eur' => fake()->randomFloat(2, 50000, 500000)
                ]),
                'insights' => fake()->paragraphs(2, true),
                'recommendations' => fake()->paragraphs(2, true),
                'pdf_path' => fake()->optional(0.7)->filePath(),
                'excel_path' => fake()->optional(0.6)->filePath(),
                'export_formats' => json_encode(['pdf', 'excel']),
                'total_records_processed' => fake()->numberBetween(1000, 10000),
                'processing_time_seconds' => fake()->randomFloat(3, 10, 180),
                'data_quality_score' => fake()->numberBetween(85, 100),
                'data_sources' => json_encode(['member_data', 'energy_sharing', 'billing_data']),
                'user_id' => $user->id,
                'energy_cooperative_id' => $cooperative?->id,
                'created_by_id' => $user->id,
                'view_count' => fake()->numberBetween(0, 15),
                'download_count' => fake()->numberBetween(0, 8),
                'tags' => json_encode(['cooperative', 'members', 'sharing', 'benefits']),
                'priority' => fake()->numberBetween(2, 5),
            ]);
        }
    }

    private function createSystemReports($users, $energyCooperatives): void
    {
        $this->command->info('🔧 Creando reportes del sistema...');

        for ($i = 0; $i < 5; $i++) {
            $user = $users->random();
            $cooperative = $energyCooperatives->isEmpty() ? null : $energyCooperatives->random();
            $periodStart = Carbon::now()->subMonths(rand(1, 6));
            $periodEnd = $periodStart->copy()->addMonths(rand(1, 3));

            EnergyReport::create([
                'title' => fake()->randomElement([
                    'Reporte del Sistema Energético',
                    'Análisis de Rendimiento del Sistema',
                    'Reporte de Estado de la Red',
                    'Análisis de Infraestructura Energética',
                    'Reporte de Operaciones del Sistema'
                ]),
                'report_code' => 'RPT-SYS-' . strtoupper(fake()->bothify('####-??')),
                'description' => fake()->paragraph,
                'report_type' => 'system',
                'report_category' => fake()->randomElement(['operational', 'performance']),
                'scope' => 'system',
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'period_type' => fake()->randomElement(['monthly', 'quarterly']),
                'generation_frequency' => fake()->randomElement(['monthly', 'quarterly']),
                'status' => fake()->randomElement(['completed', 'completed', 'draft']),
                'generated_at' => fake()->optional(0.8)->dateTimeBetween($periodEnd, 'now'),
                'data_summary' => json_encode([
                    'total_system_capacity_mw' => fake()->randomFloat(2, 100, 1000),
                    'system_uptime_percentage' => fake()->randomFloat(2, 95, 99.9),
                    'total_energy_processed_mwh' => fake()->randomFloat(2, 10000, 100000),
                    'system_efficiency_percentage' => fake()->randomFloat(2, 85, 98),
                    'maintenance_events_count' => fake()->numberBetween(0, 10)
                ]),
                'metrics' => json_encode([
                    'system_capacity_mw' => fake()->randomFloat(2, 100, 1000),
                    'uptime_percentage' => fake()->randomFloat(2, 95, 99.9),
                    'energy_processed_mwh' => fake()->randomFloat(2, 10000, 100000),
                    'efficiency_percentage' => fake()->randomFloat(2, 85, 98),
                    'maintenance_events' => fake()->numberBetween(0, 10)
                ]),
                'insights' => fake()->paragraphs(2, true),
                'recommendations' => fake()->paragraphs(2, true),
                'pdf_path' => fake()->optional(0.7)->filePath(),
                'excel_path' => fake()->optional(0.6)->filePath(),
                'export_formats' => json_encode(['pdf', 'excel']),
                'total_records_processed' => fake()->numberBetween(5000, 50000),
                'processing_time_seconds' => fake()->randomFloat(3, 15, 300),
                'data_quality_score' => fake()->numberBetween(90, 100),
                'data_sources' => json_encode(['system_logs', 'performance_metrics', 'maintenance_records']),
                'user_id' => $user->id,
                'energy_cooperative_id' => $cooperative?->id,
                'created_by_id' => $user->id,
                'view_count' => fake()->numberBetween(0, 10),
                'download_count' => fake()->numberBetween(0, 5),
                'tags' => json_encode(['system', 'infrastructure', 'performance', 'operations']),
                'priority' => fake()->numberBetween(3, 5),
            ]);
        }
    }
}
