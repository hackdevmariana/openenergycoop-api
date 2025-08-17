<?php

namespace Database\Factories;

use App\Models\EnergyReport;
use App\Models\User;
use App\Models\EnergyCooperative;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EnergyReport>
 */
class EnergyReportFactory extends Factory
{
    protected $model = EnergyReport::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $reportTypes = ['consumption', 'production', 'trading', 'savings', 'cooperative', 'user', 'system', 'custom'];
        $categories = ['energy', 'financial', 'environmental', 'operational', 'performance'];
        $scopes = ['user', 'cooperative', 'provider', 'system', 'custom'];
        $periodTypes = ['daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'custom'];
        $statuses = ['draft', 'generating', 'completed', 'failed', 'scheduled', 'cancelled'];
        $frequencies = ['on_demand', 'daily', 'weekly', 'monthly', 'quarterly', 'yearly'];

        $periodStart = $this->faker->dateTimeBetween('-6 months', '-1 month');
        $periodEnd = $this->faker->dateTimeBetween($periodStart, 'now');

        return [
            'title' => $this->faker->sentence(4),
            'report_code' => 'RPT-' . strtoupper($this->faker->unique()->lexify('????????')),
            'description' => $this->faker->optional()->paragraph(),
            'report_type' => $this->faker->randomElement($reportTypes),
            'report_category' => $this->faker->randomElement($categories),
            'scope' => $this->faker->randomElement($scopes),
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'period_type' => $this->faker->randomElement($periodTypes),
            'generation_frequency' => $this->faker->randomElement($frequencies),
            'generation_time' => $this->faker->optional()->time(),
            'auto_generate' => $this->faker->boolean(30),
            'send_notifications' => $this->faker->boolean(70),
            'status' => $this->faker->randomElement($statuses),
            'generated_at' => $this->faker->optional(60)->dateTimeBetween($periodEnd, 'now'),
            'generation_attempts' => $this->faker->numberBetween(0, 3),
            'is_public' => $this->faker->boolean(20),
            'cache_enabled' => $this->faker->boolean(80),
            'cache_duration_minutes' => $this->faker->numberBetween(30, 1440),
            'view_count' => $this->faker->numberBetween(0, 1000),
            'download_count' => $this->faker->numberBetween(0, 200),
            'priority' => $this->faker->numberBetween(1, 5),
            'created_by_id' => User::factory(),
        ];
    }

    /**
     * Estado para reportes completados
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'generated_at' => $this->faker->dateTimeBetween($attributes['period_end'], 'now'),
            'pdf_path' => 'reports/pdf/' . $this->faker->uuid() . '.pdf',
            'excel_path' => 'reports/excel/' . $this->faker->uuid() . '.xlsx',
            'export_formats' => ['pdf', 'excel', 'csv'],
        ]);
    }

    /**
     * Estado para reportes públicos
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
            'public_share_token' => bin2hex(random_bytes(32)),
            'public_expires_at' => $this->faker->dateTimeBetween('+1 day', '+30 days'),
        ]);
    }

    /**
     * Estado para reportes programados
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'auto_generate' => true,
            'scheduled_for' => $this->faker->dateTimeBetween('+1 hour', '+1 week'),
            'generation_frequency' => $this->faker->randomElement(['daily', 'weekly', 'monthly']),
            'generation_time' => $this->faker->time(),
        ]);
    }

    /**
     * Estado para reportes con datos de resumen
     */
    public function withData(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_summary' => [
                'total_records' => $this->faker->numberBetween(100, 10000),
                'total_energy_kwh' => $this->faker->randomFloat(2, 1000, 50000),
                'average_efficiency' => $this->faker->randomFloat(2, 70, 95),
                'cost_savings_eur' => $this->faker->randomFloat(2, 500, 5000),
            ],
            'metrics' => [
                'energy_consumption' => $this->faker->randomFloat(2, 1000, 5000),
                'energy_production' => $this->faker->randomFloat(2, 800, 4500),
                'co2_emissions_kg' => $this->faker->randomFloat(2, 100, 1000),
                'renewable_percentage' => $this->faker->randomFloat(1, 60, 95),
            ],
            'data_quality_score' => $this->faker->numberBetween(70, 100),
            'total_records_processed' => $this->faker->numberBetween(100, 10000),
            'processing_time_seconds' => $this->faker->randomFloat(2, 5, 300),
        ]);
    }

    /**
     * Estado para reportes de usuario específico
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'created_by_id' => $user->id,
            'scope' => 'user',
        ]);
    }

    /**
     * Estado para reportes de cooperativa específica
     */
    public function forCooperative(EnergyCooperative $cooperative): static
    {
        return $this->state(fn (array $attributes) => [
            'energy_cooperative_id' => $cooperative->id,
            'scope' => 'cooperative',
        ]);
    }
}