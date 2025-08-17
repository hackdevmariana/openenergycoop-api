<?php

namespace Database\Factories;

use App\Models\EnergyConsumption;
use App\Models\User;
use App\Models\EnergyContract;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EnergyConsumption>
 */
class EnergyConsumptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'energy_contract_id' => EnergyContract::factory(),
            'measurement_datetime' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'period_type' => $this->faker->randomElement(['instant', 'hourly', 'daily', 'monthly', 'billing_period']),
            'consumption_kwh' => $this->faker->randomFloat(3, 0.1, 100),
            'peak_consumption_kw' => $this->faker->randomFloat(3, 0.5, 20),
            'off_peak_consumption_kwh' => $this->faker->randomFloat(3, 0.1, 50),
            'renewable_percentage' => $this->faker->randomFloat(2, 0, 100),
            'grid_consumption_kwh' => $this->faker->randomFloat(3, 0, 80),
            'self_consumption_kwh' => $this->faker->randomFloat(3, 0, 20),
            'total_cost_eur' => $this->faker->randomFloat(2, 5, 200),
            'energy_cost_eur' => $this->faker->randomFloat(2, 3, 150),
            'grid_cost_eur' => $this->faker->randomFloat(2, 1, 30),
            'taxes_eur' => $this->faker->randomFloat(2, 0.5, 20),
            'tariff_type' => $this->faker->randomElement(['fixed', 'time_of_use', 'demand', 'tiered']),
            'rate_per_kwh' => $this->faker->randomFloat(4, 0.08, 0.25),
            'temperature_avg_c' => $this->faker->randomFloat(1, -5, 40),
            'efficiency_score' => $this->faker->randomFloat(2, 60, 100),
        ];
    }

    public function daily(): static
    {
        return $this->state(['period_type' => 'daily']);
    }

    public function highConsumption(): static
    {
        return $this->state([
            'consumption_kwh' => $this->faker->randomFloat(3, 50, 500),
            'peak_consumption_kw' => $this->faker->randomFloat(3, 10, 100),
        ]);
    }
}
