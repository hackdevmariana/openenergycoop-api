<?php

namespace Database\Factories;

use App\Models\EnergyCooperative;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EnergyCooperative>
 */
class EnergyCooperativeFactory extends Factory
{
    protected $model = EnergyCooperative::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company() . ' Energética';
        $code = strtoupper($this->faker->lexify('???')) . $this->faker->numberBetween(100, 999);

        return [
            // Información básica
            'name' => $name,
            'code' => $code,
            'description' => $this->faker->text(200),
            'status' => $this->faker->randomElement(['pending', 'active', 'suspended']),
            
            // Ubicación
            'city' => $this->faker->city(),
            'country' => 'España',
            
            // Configuración
            'max_members' => $this->faker->numberBetween(50, 1000),
            'current_members' => $this->faker->numberBetween(10, 50),
            'open_enrollment' => $this->faker->boolean(70),
            'allows_energy_sharing' => $this->faker->boolean(80),
            'allows_trading' => $this->faker->boolean(70),
            
            // Capacidad energética
            'total_capacity_kw' => $this->faker->numberBetween(100, 5000),
            'available_capacity_kw' => $this->faker->numberBetween(50, 1000),
            
            // Relaciones
            'founder_id' => User::factory(),
            'administrator_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the cooperative is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'open_enrollment' => true,
            'allows_energy_sharing' => true,
        ]);
    }

    /**
     * Indicate that the cooperative is pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }
}