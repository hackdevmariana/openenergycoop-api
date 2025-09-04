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

        // Crear reportes simples
        $this->createSimpleReports($users, $energyCooperatives);

        $this->command->info('✅ EnergyReportSeeder completado. Se crearon ' . EnergyReport::count() . ' reportes.');
    }

    private function createSimpleReports($users, $energyCooperatives): void
    {
        $this->command->info('📊 Creando reportes simples...');

        $reportTypes = ['consumption', 'production', 'trading', 'savings', 'cooperative', 'system'];
        $categories = ['energy', 'financial', 'environmental', 'operational', 'performance'];
        $statuses = ['completed', 'completed', 'completed', 'draft', 'generating', 'failed'];

        for ($i = 0; $i < 70; $i++) {
            $user = $users->random();
            $cooperative = $energyCooperatives->isEmpty() ? null : $energyCooperatives->random();
            $reportType = $reportTypes[array_rand($reportTypes)];
            $status = $statuses[array_rand($statuses)];

            EnergyReport::create([
                'title' => "Reporte de {$reportType} #" . ($i + 1),
                'report_code' => 'RPT-' . strtoupper(substr($reportType, 0, 3)) . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'description' => fake()->sentence,
                'report_type' => $reportType,
                'report_category' => $categories[array_rand($categories)],
                'scope' => fake()->randomElement(['user', 'cooperative', 'system']),
                'scope_filters' => json_encode(['include_details' => true]),
                'period_start' => Carbon::now()->subMonths(rand(1, 6)),
                'period_end' => Carbon::now()->subMonths(rand(0, 2)),
                'period_type' => fake()->randomElement(['monthly', 'quarterly']),
                'generation_frequency' => fake()->randomElement(['monthly', 'quarterly']),
                'generation_time' => '09:00:00',
                'generation_config' => json_encode(['include_charts' => true]),
                'auto_generate' => fake()->boolean(70),
                'send_notifications' => fake()->boolean(80),
                'notification_recipients' => json_encode([$user->email]),
                'status' => $status,
                'generated_at' => $status === 'completed' ? Carbon::now()->subDays(rand(1, 30)) : null,
                'generation_attempts' => fake()->numberBetween(0, 3),
                'file_size_bytes' => $status === 'completed' ? fake()->numberBetween(100000, 5000000) : null,
                'data_summary' => json_encode([
                    'total_records' => fake()->numberBetween(100, 1000),
                    'total_energy_kwh' => fake()->randomFloat(2, 1000, 5000),
                    'average_efficiency' => fake()->randomFloat(2, 70, 95),
                ]),
                'metrics' => json_encode([
                    'energy_consumption' => fake()->randomFloat(2, 1000, 5000),
                    'energy_production' => fake()->randomFloat(2, 800, 4500),
                    'efficiency_percentage' => fake()->randomFloat(2, 60, 95),
                ]),
                'charts_config' => json_encode(['show_charts' => true]),
                'tables_data' => json_encode(['data' => []]),
                'insights' => fake()->paragraph,
                'recommendations' => fake()->paragraph,
                'pdf_path' => $status === 'completed' ? 'reports/pdf/report_' . ($i + 1) . '.pdf' : null,
                'excel_path' => $status === 'completed' ? 'reports/excel/report_' . ($i + 1) . '.xlsx' : null,
                'export_formats' => json_encode(['pdf', 'excel']),
                'dashboard_config' => json_encode(['show_metrics' => true]),
                'is_public' => fake()->boolean(20),
                'public_share_token' => fake()->optional(0.2)->sha256,
                'public_expires_at' => fake()->optional(0.2)->dateTimeBetween('+1 day', '+30 days'),
                'access_permissions' => json_encode(['view', 'download']),
                'total_records_processed' => fake()->numberBetween(100, 1000),
                'processing_time_seconds' => fake()->randomFloat(3, 2, 60),
                'data_quality_score' => fake()->numberBetween(85, 100),
                'data_sources' => json_encode(['energy_readings', 'user_profiles']),
                'include_comparison' => fake()->boolean(60),
                'comparison_metrics' => json_encode([
                    'change_percentage' => fake()->randomFloat(2, -20, 20),
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
                ]),
                'tags' => json_encode([$reportType, 'energy', 'analysis']),
                'metadata' => json_encode([
                    'generated_by' => 'system',
                    'version' => '1.0',
                ]),
                'notes' => fake()->optional(0.3)->sentence,
                'priority' => fake()->numberBetween(1, 5),
            ]);
        }
    }
}
