<?php

namespace Database\Factories;

use App\Models\PerformanceIndicator;
use App\Models\User;
use App\Models\EnergyCooperative;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PerformanceIndicator>
 */
class PerformanceIndicatorFactory extends Factory
{
    protected $model = PerformanceIndicator::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $indicatorTypes = ['kpi', 'metric', 'target', 'benchmark', 'efficiency', 'utilization', 'quality', 'satisfaction'];
        $categories = ['operational', 'financial', 'technical', 'customer', 'environmental', 'safety', 'quality', 'strategic'];
        $criticalities = ['low', 'medium', 'high', 'critical'];
        $frequencies = ['real_time', 'hourly', 'daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'on_demand'];
        $scopes = ['system', 'cooperative', 'user', 'provider', 'product', 'energy_sharing', 'subscription', 'asset'];
        $periodTypes = ['instant', 'daily', 'weekly', 'monthly', 'quarterly', 'yearly'];
        $trendDirections = ['up', 'down', 'stable', 'volatile'];
        $performanceStatuses = ['excellent', 'good', 'acceptable', 'poor', 'critical'];
        $calculationMethods = ['automatic', 'manual', 'semi_automatic', 'imported'];
        $businessImpacts = ['low', 'medium', 'high', 'strategic'];
        $alertLevels = ['normal', 'warning', 'critical', 'emergency'];

        $measurementTimestamp = $this->faker->dateTimeBetween('-1 month', 'now');
        $measurementDate = $measurementTimestamp->format('Y-m-d');
        $periodStart = $this->faker->dateTimeBetween('-1 week', $measurementTimestamp);
        $periodEnd = $this->faker->dateTimeBetween($periodStart, $measurementTimestamp);

        $indicatorType = $this->faker->randomElement($indicatorTypes);
        $category = $this->faker->randomElement($categories);
        $currentValue = $this->generateValueForIndicator($indicatorType, $category);

        return [
            'indicator_name' => $this->generateIndicatorName($indicatorType, $category),
            'indicator_code' => 'PI-' . strtoupper($this->faker->unique()->lexify('??????')),
            'description' => $this->faker->optional()->sentence(),
            'indicator_type' => $indicatorType,
            'category' => $category,
            'criticality' => $this->faker->randomElement($criticalities),
            'priority' => $this->faker->numberBetween(1, 5),
            'frequency' => $this->faker->randomElement($frequencies),
            'is_active' => $this->faker->boolean(85),
            'scope' => $this->faker->randomElement($scopes),
            'entity_type' => $this->faker->optional()->randomElement(['user', 'cooperative', 'provider']),
            'entity_id' => $this->faker->optional()->numberBetween(1, 100),
            'entity_name' => $this->faker->optional()->company(),
            'measurement_timestamp' => $measurementTimestamp,
            'measurement_date' => $measurementDate,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'period_type' => $this->faker->randomElement($periodTypes),
            'current_value' => $currentValue,
            'unit' => $this->getUnitForIndicator($indicatorType, $category),
            'target_value' => $this->faker->optional()->randomFloat(2, $currentValue * 1.1, $currentValue * 1.5),
            'baseline_value' => $this->faker->optional()->randomFloat(2, $currentValue * 0.7, $currentValue * 0.9),
            'previous_value' => $this->faker->optional()->randomFloat(2, $currentValue * 0.8, $currentValue * 1.2),
            'change_absolute' => $this->faker->randomFloat(2, -50, 50),
            'change_percentage' => $this->faker->randomFloat(1, -25, 25),
            'target_achievement_percentage' => $this->faker->randomFloat(1, 70, 120),
            'trend_direction' => $this->faker->randomElement($trendDirections),
            'performance_status' => $this->faker->randomElement($performanceStatuses),
            'calculation_method' => $this->faker->randomElement($calculationMethods),
            'confidence_level' => $this->faker->randomFloat(1, 70, 99),
            'industry_benchmark' => $this->faker->optional()->randomFloat(2, $currentValue * 0.9, $currentValue * 1.3),
            'business_impact' => $this->faker->randomElement($businessImpacts),
            'created_by_id' => User::factory(),
            'is_validated' => $this->faker->boolean(70),
            'show_in_dashboard' => $this->faker->boolean(60),
            'auto_calculate' => $this->faker->boolean(80),
            'alerts_enabled' => $this->faker->boolean(40),
            'current_alert_level' => $this->faker->randomElement($alertLevels),
        ];
    }

    /**
     * Generar valor apropiado según el tipo y categoría del indicador
     */
    private function generateValueForIndicator(string $type, string $category): float
    {
        return match([$type, $category]) {
            ['efficiency', 'operational'] => $this->faker->randomFloat(1, 70, 95), // %
            ['utilization', 'operational'] => $this->faker->randomFloat(1, 60, 90), // %
            ['quality', 'operational'] => $this->faker->randomFloat(1, 80, 99), // %
            ['kpi', 'financial'] => $this->faker->randomFloat(2, 1000, 50000), // euros
            ['metric', 'technical'] => $this->faker->randomFloat(2, 10, 1000), // ms, requests, etc
            ['satisfaction', 'customer'] => $this->faker->randomFloat(1, 6, 10), // escala 1-10
            default => $this->faker->randomFloat(2, 1, 1000),
        };
    }

    /**
     * Obtener unidad apropiada según el tipo y categoría
     */
    private function getUnitForIndicator(string $type, string $category): string
    {
        return match([$type, $category]) {
            ['efficiency', 'operational'] => '%',
            ['utilization', 'operational'] => '%',
            ['quality', 'operational'] => '%',
            ['kpi', 'financial'] => '€',
            ['metric', 'technical'] => 'ms',
            ['satisfaction', 'customer'] => 'puntos',
            default => 'unidad',
        };
    }

    /**
     * Generar nombre apropiado según el tipo y categoría
     */
    private function generateIndicatorName(string $type, string $category): string
    {
        $names = [
            'efficiency_operational' => 'Eficiencia Operacional',
            'utilization_operational' => 'Utilización de Recursos',
            'quality_operational' => 'Calidad del Servicio',
            'kpi_financial' => 'ROI Energético',
            'metric_technical' => 'Tiempo de Respuesta del Sistema',
            'satisfaction_customer' => 'Satisfacción del Cliente',
            'kpi_environmental' => 'Reducción de Emisiones',
            'efficiency_technical' => 'Eficiencia Técnica',
            'utilization_financial' => 'Utilización de Capital',
        ];

        $key = $type . '_' . $category;
        
        return $names[$key] ?? 
               ucfirst($type) . ' de ' . ucfirst($category);
    }

    /**
     * Estado para indicadores críticos
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'criticality' => 'critical',
            'priority' => 5,
            'alerts_enabled' => true,
            'current_alert_level' => $this->faker->randomElement(['critical', 'emergency']),
            'show_in_dashboard' => true,
        ]);
    }

    /**
     * Estado para indicadores de dashboard
     */
    public function forDashboard(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_in_dashboard' => true,
            'is_active' => true,
            'dashboard_order' => $this->faker->numberBetween(1, 20),
            'chart_type' => $this->faker->randomElement(['line', 'bar', 'gauge', 'number']),
        ]);
    }

    /**
     * Estado para indicadores con alertas
     */
    public function withAlerts(): static
    {
        return $this->state(fn (array $attributes) => [
            'alerts_enabled' => true,
            'alert_threshold_min' => $this->faker->randomFloat(2, 0, $attributes['current_value'] * 0.8),
            'alert_threshold_max' => $this->faker->randomFloat(2, $attributes['current_value'] * 1.2, $attributes['current_value'] * 2),
            'current_alert_level' => $this->faker->randomElement(['warning', 'critical']),
            'last_alert_sent_at' => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Estado para indicadores financieros
     */
    public function financial(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'financial',
            'indicator_type' => 'kpi',
            'unit' => '€',
            'current_value' => $this->faker->randomFloat(2, 1000, 100000),
            'cost_per_unit' => $this->faker->randomFloat(2, 0.1, 10),
            'revenue_impact_eur' => $this->faker->randomFloat(2, 500, 50000),
            'roi_percentage' => $this->faker->randomFloat(1, 5, 25),
        ]);
    }

    /**
     * Estado para indicadores operacionales
     */
    public function operational(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'operational',
            'indicator_type' => 'efficiency',
            'unit' => '%',
            'current_value' => $this->faker->randomFloat(1, 70, 95),
            'efficiency_percentage' => $this->faker->randomFloat(1, 70, 95),
            'utilization_percentage' => $this->faker->randomFloat(1, 60, 90),
            'availability_percentage' => $this->faker->randomFloat(1, 95, 99.9),
        ]);
    }

    /**
     * Estado para indicadores de calidad
     */
    public function quality(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'quality',
            'indicator_type' => 'quality',
            'unit' => 'puntos',
            'current_value' => $this->faker->randomFloat(1, 7, 10),
            'quality_score' => $this->faker->randomFloat(1, 80, 98),
            'satisfaction_score' => $this->faker->randomFloat(1, 7, 10),
            'error_rate_percentage' => $this->faker->randomFloat(2, 0.1, 5),
        ]);
    }

    /**
     * Estado para indicadores de usuario específico
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'scope' => 'user',
            'entity_type' => 'user',
            'entity_id' => $user->id,
            'entity_name' => $user->name,
        ]);
    }

    /**
     * Estado para indicadores de cooperativa específica
     */
    public function forCooperative(EnergyCooperative $cooperative): static
    {
        return $this->state(fn (array $attributes) => [
            'energy_cooperative_id' => $cooperative->id,
            'scope' => 'cooperative',
            'entity_type' => 'cooperative',
            'entity_id' => $cooperative->id,
            'entity_name' => $cooperative->name,
        ]);
    }
}