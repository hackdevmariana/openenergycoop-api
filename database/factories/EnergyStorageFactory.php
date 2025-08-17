<?php

namespace Database\Factories;

use App\Models\EnergyStorage;
use App\Models\User;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EnergyStorage>
 */
class EnergyStorageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $capacity = $this->faker->randomFloat(2, 10, 1000);
        $usableCapacity = $capacity * $this->faker->randomFloat(2, 0.8, 0.95);
        $currentCharge = $usableCapacity * $this->faker->randomFloat(2, 0.1, 0.9);
        
        return [
            'user_id' => User::factory(),
            'provider_id' => Provider::factory(),
            'system_id' => 'ES-' . strtoupper($this->faker->bothify('##??####')),
            'name' => $this->faker->words(2, true) . ' Storage System',
            'description' => $this->faker->sentence(),
            'storage_type' => $this->faker->randomElement([
                'battery_lithium', 'battery_lead_acid', 'battery_flow', 
                'pumped_hydro', 'compressed_air', 'flywheel', 'thermal', 'hydrogen'
            ]),
            'manufacturer' => $this->faker->company,
            'model' => $this->faker->bothify('Model ###-??'),
            'capacity_kwh' => $capacity,
            'usable_capacity_kwh' => $usableCapacity,
            'current_charge_kwh' => $currentCharge,
            'charge_level_percentage' => ($currentCharge / $usableCapacity) * 100,
            'max_charge_power_kw' => $this->faker->randomFloat(2, 5, 100),
            'max_discharge_power_kw' => $this->faker->randomFloat(2, 5, 100),
            'round_trip_efficiency' => $this->faker->randomFloat(2, 80, 95),
            'charge_efficiency' => $this->faker->randomFloat(2, 85, 98),
            'discharge_efficiency' => $this->faker->randomFloat(2, 85, 98),
            'cycle_count' => $this->faker->numberBetween(0, 5000),
            'max_cycles' => $this->faker->numberBetween(5000, 15000),
            'current_health_percentage' => $this->faker->randomFloat(2, 80, 100),
            'capacity_degradation_percentage' => $this->faker->randomFloat(2, 0, 15),
            'status' => $this->faker->randomElement(['online', 'offline', 'charging', 'discharging', 'standby', 'maintenance', 'error']),
            'installation_cost' => $this->faker->randomFloat(2, 5000, 100000),
            'maintenance_cost_annual' => $this->faker->randomFloat(2, 200, 2000),
            'warranty_end_date' => $this->faker->dateTimeBetween('now', '+10 years'),
            'next_maintenance_date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'location_description' => $this->faker->address,
            'is_active' => $this->faker->boolean(90),
        ];
    }

    public function charging(): static
    {
        return $this->state(['status' => 'charging']);
    }

    public function discharging(): static
    {
        return $this->state(['status' => 'discharging']);
    }

    public function highCapacity(): static
    {
        return $this->state([
            'capacity_kwh' => $this->faker->randomFloat(2, 500, 2000),
            'usable_capacity_kwh' => function (array $attributes) {
                return $attributes['capacity_kwh'] * 0.9;
            }
        ]);
    }
}