<?php

namespace Database\Factories;

use App\Models\EnergyProduction;
use App\Models\User;
use App\Models\UserAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EnergyProduction>
 */
class EnergyProductionFactory extends Factory
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
            'user_asset_id' => UserAsset::factory(),
            'system_id' => 'EP-' . strtoupper($this->faker->bothify('##??####')),
            'production_datetime' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'period_type' => $this->faker->randomElement(['instant', 'hourly', 'daily', 'monthly', 'annual']),
            'energy_source' => $this->faker->randomElement(['solar_pv', 'solar_thermal', 'wind', 'hydro', 'biomass', 'geothermal', 'biogas', 'combined']),
            'production_kwh' => $this->faker->randomFloat(3, 0.1, 200),
            'peak_production_kw' => $this->faker->randomFloat(3, 0.5, 50),
            'capacity_factor' => $this->faker->randomFloat(2, 15, 85),
            'system_efficiency' => $this->faker->randomFloat(2, 80, 98),
            'renewable_percentage' => $this->faker->randomFloat(2, 85, 100),
            'grid_injection_kwh' => $this->faker->randomFloat(3, 0, 150),
            'self_consumption_kwh' => $this->faker->randomFloat(3, 0, 50),
            'curtailment_kwh' => $this->faker->randomFloat(3, 0, 10),
            'revenue_eur' => $this->faker->randomFloat(2, 5, 100),
            'feed_in_tariff_eur' => $this->faker->randomFloat(4, 0.05, 0.15),
            'co2_avoided_kg' => $this->faker->randomFloat(2, 20, 500),
            'irradiance_wm2' => $this->faker->randomFloat(1, 100, 1200),
            'wind_speed_ms' => $this->faker->randomFloat(1, 0, 25),
            'temperature_c' => $this->faker->randomFloat(1, -10, 45),
            'operational_status' => $this->faker->randomElement(['online', 'offline', 'maintenance', 'error', 'curtailed', 'standby']),
        ];
    }

    public function solar(): static
    {
        return $this->state([
            'energy_source' => 'solar_pv',
            'irradiance_wm2' => $this->faker->randomFloat(1, 200, 1200),
        ]);
    }

    public function wind(): static
    {
        return $this->state([
            'energy_source' => 'wind',
            'wind_speed_ms' => $this->faker->randomFloat(1, 3, 25),
        ]);
    }
}
