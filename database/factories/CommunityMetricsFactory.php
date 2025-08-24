<?php

namespace Database\Factories;

use App\Models\CommunityMetrics;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CommunityMetrics>
 */
class CommunityMetricsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CommunityMetrics::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'total_users' => $this->faker->numberBetween(10, 1000),
            'total_kwh_produced' => $this->faker->randomFloat(4, 1000, 100000),
            'total_co2_avoided' => $this->faker->randomFloat(4, 500, 50000),
        ];
    }

    /**
     * Indicate that the organization has many users.
     */
    public function manyUsers(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_users' => $this->faker->numberBetween(500, 5000),
        ]);
    }

    /**
     * Indicate that the organization has few users.
     */
    public function fewUsers(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_users' => $this->faker->numberBetween(5, 50),
        ]);
    }

    /**
     * Indicate that the organization has medium users.
     */
    public function mediumUsers(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_users' => $this->faker->numberBetween(50, 500),
        ]);
    }

    /**
     * Indicate high production metrics.
     */
    public function highProduction(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_kwh_produced' => $this->faker->randomFloat(4, 50000, 200000),
            'total_co2_avoided' => $this->faker->randomFloat(4, 25000, 100000),
        ]);
    }

    /**
     * Indicate low production metrics.
     */
    public function lowProduction(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_kwh_produced' => $this->faker->randomFloat(4, 100, 1000),
            'total_co2_avoided' => $this->faker->randomFloat(4, 50, 500),
        ]);
    }

    /**
     * Indicate medium production metrics.
     */
    public function mediumProduction(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_kwh_produced' => $this->faker->randomFloat(4, 1000, 50000),
            'total_co2_avoided' => $this->faker->randomFloat(4, 500, 25000),
        ]);
    }

    /**
     * Indicate that the organization is very active.
     */
    public function veryActive(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_users' => $this->faker->numberBetween(1000, 10000),
            'total_kwh_produced' => $this->faker->randomFloat(4, 100000, 500000),
            'total_co2_avoided' => $this->faker->randomFloat(4, 50000, 250000),
        ]);
    }

    /**
     * Indicate that the organization is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_users' => 0,
            'total_kwh_produced' => 0,
            'total_co2_avoided' => 0,
        ]);
    }

    /**
     * Indicate that the organization is starting up.
     */
    public function startup(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_users' => $this->faker->numberBetween(1, 20),
            'total_kwh_produced' => $this->faker->randomFloat(4, 100, 1000),
            'total_co2_avoided' => $this->faker->randomFloat(4, 50, 500),
        ]);
    }

    /**
     * Indicate that the organization is established.
     */
    public function established(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_users' => $this->faker->numberBetween(100, 1000),
            'total_kwh_produced' => $this->faker->randomFloat(4, 10000, 100000),
            'total_co2_avoided' => $this->faker->randomFloat(4, 5000, 50000),
        ]);
    }

    /**
     * Indicate that the organization is large scale.
     */
    public function largeScale(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_users' => $this->faker->numberBetween(1000, 10000),
            'total_kwh_produced' => $this->faker->randomFloat(4, 100000, 1000000),
            'total_co2_avoided' => $this->faker->randomFloat(4, 50000, 500000),
        ]);
    }

    /**
     * Indicate that the metrics have specific user count range.
     */
    public function withUserRange($minUsers, $maxUsers): static
    {
        return $this->state(fn (array $attributes) => [
            'total_users' => $this->faker->numberBetween($minUsers, $maxUsers),
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

    /**
     * Indicate that the metrics have specific CO2 range.
     */
    public function withCo2Range($minCo2, $maxCo2): static
    {
        return $this->state(fn (array $attributes) => [
            'total_co2_avoided' => $this->faker->randomFloat(4, $minCo2, $maxCo2),
        ]);
    }

    /**
     * Indicate that the organization has balanced metrics (proportional to users).
     */
    public function balanced(): static
    {
        return $this->state(function (array $attributes) {
            $users = $this->faker->numberBetween(50, 500);
            $kwhPerUser = $this->faker->randomFloat(2, 10, 100);
            $co2PerUser = $this->faker->randomFloat(2, 5, 50);
            
            return [
                'total_users' => $users,
                'total_kwh_produced' => $users * $kwhPerUser,
                'total_co2_avoided' => $users * $co2PerUser,
            ];
        });
    }
}
