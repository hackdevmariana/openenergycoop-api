<?php

namespace Database\Factories;

use App\Models\SustainabilityMetric;
use App\Models\User;
use App\Models\EnergyCooperative;
use App\Models\EnergyReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SustainabilityMetric>
 */
class SustainabilityMetricFactory extends Factory
{
    protected $model = SustainabilityMetric::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $metricTypes = ['carbon_footprint', 'renewable_percentage', 'energy_efficiency', 'waste_reduction', 'water_usage'];
        $categories = ['environmental', 'social', 'economic', 'governance', 'operational'];
        $entityTypes = ['user', 'cooperative', 'provider', 'energy_sharing', 'energy_production', 'system'];
        $periodTypes = ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'];
        $trends = ['improving', 'declining', 'stable', 'unknown'];
        $verificationStatuses = ['unverified', 'self_reported', 'third_party', 'certified'];

        $measurementDate = $this->faker->dateTimeBetween('-3 months', 'now');
        $periodStart = $this->faker->dateTimeBetween('-1 month', $measurementDate);
        $periodEnd = $this->faker->dateTimeBetween($periodStart, $measurementDate);

        $metricType = $this->faker->randomElement($metricTypes);
        $value = $this->generateValueForMetricType($metricType);

        return [
            'metric_name' => $this->generateMetricName($metricType),
            'metric_code' => 'SUST-' . strtoupper($this->faker->unique()->lexify('??????')),
            'description' => $this->faker->optional()->sentence(),
            'metric_type' => $metricType,
            'metric_category' => $this->faker->randomElement($categories),
            'entity_type' => $this->faker->randomElement($entityTypes),
            'entity_id' => $this->faker->optional()->numberBetween(1, 100),
            'entity_name' => $this->faker->optional()->company(),
            'measurement_date' => $measurementDate,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'period_type' => $this->faker->randomElement($periodTypes),
            'value' => $value,
            'unit' => $this->getUnitForMetricType($metricType),
            'baseline_value' => $this->faker->optional()->randomFloat(2, $value * 0.8, $value * 1.2),
            'target_value' => $this->faker->optional()->randomFloat(2, $value * 1.1, $value * 1.5),
            'previous_period_value' => $this->faker->optional()->randomFloat(2, $value * 0.9, $value * 1.1),
            'change_absolute' => $this->faker->randomFloat(2, -50, 50),
            'change_percentage' => $this->faker->randomFloat(1, -20, 20),
            'trend' => $this->faker->randomElement($trends),
            'trend_score' => $this->faker->randomFloat(1, -20, 20),
            'is_certified' => $this->faker->boolean(30),
            'verification_status' => $this->faker->randomElement($verificationStatuses),
            'contributes_to_sdg' => $this->faker->boolean(60),
            'paris_agreement_aligned' => $this->faker->boolean(40),
            'is_public' => $this->faker->boolean(70),
            'alert_enabled' => $this->faker->boolean(50),
            'alert_status' => $this->faker->randomElement(['normal', 'warning', 'critical']),
        ];
    }

    /**
     * Generar valor apropiado según el tipo de métrica
     */
    private function generateValueForMetricType(string $metricType): float
    {
        return match($metricType) {
            'carbon_footprint' => $this->faker->randomFloat(2, 50, 5000), // kg CO2
            'renewable_percentage' => $this->faker->randomFloat(1, 20, 95), // %
            'energy_efficiency' => $this->faker->randomFloat(1, 60, 95), // %
            'waste_reduction' => $this->faker->randomFloat(1, 10, 80), // %
            'water_usage' => $this->faker->randomFloat(2, 100, 10000), // litros
            default => $this->faker->randomFloat(2, 1, 1000),
        };
    }

    /**
     * Obtener unidad apropiada según el tipo de métrica
     */
    private function getUnitForMetricType(string $metricType): string
    {
        return match($metricType) {
            'carbon_footprint' => 'kg CO2',
            'renewable_percentage' => '%',
            'energy_efficiency' => '%',
            'waste_reduction' => '%',
            'water_usage' => 'litros',
            default => 'unidad',
        };
    }

    /**
     * Generar nombre apropiado según el tipo de métrica
     */
    private function generateMetricName(string $metricType): string
    {
        return match($metricType) {
            'carbon_footprint' => 'Huella de Carbono ' . $this->faker->randomElement(['Mensual', 'Anual', 'Trimestral']),
            'renewable_percentage' => 'Porcentaje de Energía Renovable',
            'energy_efficiency' => 'Eficiencia Energética',
            'waste_reduction' => 'Reducción de Residuos',
            'water_usage' => 'Consumo de Agua',
            default => 'Métrica de Sostenibilidad',
        };
    }

    /**
     * Estado para métricas certificadas
     */
    public function certified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_certified' => true,
            'verification_status' => 'certified',
            'certification_body' => $this->faker->company(),
            'certification_number' => 'CERT-' . $this->faker->numerify('####-####'),
            'certification_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'certification_expires_at' => $this->faker->dateTimeBetween('+6 months', '+2 years'),
        ]);
    }

    /**
     * Estado para métricas de carbono
     */
    public function carbonFootprint(): static
    {
        return $this->state(fn (array $attributes) => [
            'metric_type' => 'carbon_footprint',
            'metric_name' => 'Huella de Carbono Mensual',
            'unit' => 'kg CO2',
            'value' => $this->faker->randomFloat(2, 100, 2000),
            'co2_emissions_kg' => $this->faker->randomFloat(2, 100, 2000),
            'co2_avoided_kg' => $this->faker->randomFloat(2, 50, 500),
            'carbon_intensity' => $this->faker->randomFloat(3, 0.1, 0.8),
        ]);
    }

    /**
     * Estado para métricas de energía renovable
     */
    public function renewableEnergy(): static
    {
        return $this->state(fn (array $attributes) => [
            'metric_type' => 'renewable_percentage',
            'metric_name' => 'Porcentaje de Energía Renovable',
            'unit' => '%',
            'value' => $this->faker->randomFloat(1, 40, 95),
            'renewable_energy_kwh' => $this->faker->randomFloat(2, 1000, 5000),
            'total_energy_kwh' => $this->faker->randomFloat(2, 1500, 6000),
            'renewable_percentage' => $this->faker->randomFloat(1, 40, 95),
        ]);
    }

    /**
     * Estado para métricas con alertas
     */
    public function withAlert(): static
    {
        return $this->state(fn (array $attributes) => [
            'alert_enabled' => true,
            'alert_threshold_min' => $this->faker->randomFloat(2, 0, $attributes['value'] * 0.8),
            'alert_threshold_max' => $this->faker->randomFloat(2, $attributes['value'] * 1.2, $attributes['value'] * 2),
            'alert_status' => $this->faker->randomElement(['warning', 'critical']),
            'last_alert_sent_at' => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Estado para métricas que contribuyen a ODS
     */
    public function sdgContributor(): static
    {
        return $this->state(fn (array $attributes) => [
            'contributes_to_sdg' => true,
            'sdg_targets' => $this->faker->randomElements(['7.1', '7.2', '13.1', '13.2', '11.6'], 2),
            'paris_agreement_aligned' => true,
        ]);
    }

    /**
     * Estado para métricas de usuario específico
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'entity_type' => 'user',
            'entity_id' => $user->id,
            'entity_name' => $user->name,
        ]);
    }

    /**
     * Estado para métricas de cooperativa específica
     */
    public function forCooperative(EnergyCooperative $cooperative): static
    {
        return $this->state(fn (array $attributes) => [
            'energy_cooperative_id' => $cooperative->id,
            'entity_type' => 'cooperative',
            'entity_id' => $cooperative->id,
            'entity_name' => $cooperative->name,
        ]);
    }
}