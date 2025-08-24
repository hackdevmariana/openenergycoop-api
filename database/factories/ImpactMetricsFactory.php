<?php

namespace Database\Factories;

use App\Models\ImpactMetrics;
use App\Models\User;
use App\Models\PlantGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ImpactMetrics>
 */
class ImpactMetricsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ImpactMetrics::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'total_kwh_produced' => $this->faker->randomFloat(4, 100, 10000),
            'total_co2_avoided_kg' => $this->faker->randomFloat(4, 50, 5000),
            'plant_group_id' => PlantGroup::factory(),
            'generated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the metrics are global (no user).
     */
    public function global(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'plant_group_id' => null,
        ]);
    }

    /**
     * Indicate that the metrics are for an individual user.
     */
    public function individual(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory(),
            'plant_group_id' => null,
        ]);
    }

    /**
     * Indicate that the metrics are for a specific plant group.
     */
    public function forPlantGroup(): static
    {
        return $this->state(fn (array $attributes) => [
            'plant_group_id' => PlantGroup::factory(),
        ]);
    }

    /**
     * Indicate that the metrics are recent (last 30 days).
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'generated_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the metrics are for this month.
     */
    public function thisMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'generated_at' => $this->faker->dateTimeBetween('first day of this month', 'now'),
        ]);
    }

    /**
     * Indicate that the metrics are for this year.
     */
    public function thisYear(): static
    {
        return $this->state(fn (array $attributes) => [
            'generated_at' => $this->faker->dateTimeBetween('first day of january this year', 'now'),
        ]);
    }

    /**
     * Indicate high impact metrics.
     */
    public function highImpact(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_kwh_produced' => $this->faker->randomFloat(4, 5000, 20000),
            'total_co2_avoided_kg' => $this->faker->randomFloat(4, 2500, 10000),
        ]);
    }

    /**
     * Indicate low impact metrics.
     */
    public function lowImpact(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_kwh_produced' => $this->faker->randomFloat(4, 50, 500),
            'total_co2_avoided_kg' => $this->faker->randomFloat(4, 25, 250),
        ]);
    }

    /**
     * Indicate medium impact metrics.
     */
    public function mediumImpact(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_kwh_produced' => $this->faker->randomFloat(4, 500, 5000),
            'total_co2_avoided_kg' => $this->faker->randomFloat(4, 250, 2500),
        ]);
    }

    /**
     * Indicate that the metrics are for a specific date range.
     */
    public function forDateRange($startDate, $endDate): static
    {
        return $this->state(fn (array $attributes) => [
            'generated_at' => $this->faker->dateTimeBetween($startDate, $endDate),
        ]);
    }

    /**
     * Indicate that the metrics have specific CO2 range.
     */
    public function withCo2Range($minCo2, $maxCo2): static
    {
        return $this->state(fn (array $attributes) => [
            'total_co2_avoided_kg' => $this->faker->randomFloat(4, $minCo2, $maxCo2),
        ]);
    }

    /**
     * Indicate that the metrics have specific kWh range.
     */
    public function withKwhRange($minKwh, $maxKwh): static
    {
        return $this->state(fn (array $attributes) => [
            'total_kwh_produced' => $this->faker->randomFloat(4, $minKwh, $maxKwh),
        ]);
    }
}
