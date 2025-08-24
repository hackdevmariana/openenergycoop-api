<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlantGroup>
 */
class PlantGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $groupNames = [
            'Mi viña solar',
            'Pinar cooperativo',
            'Huerta urbana',
            'Bosque comunitario',
            'Jardín sostenible',
            'Plantación ecológica',
            'Vergel solar',
            'Arboretum verde',
            'Senda botánica',
            'Oasis energético'
        ];

        return [
            'user_id' => \App\Models\User::factory(),
            'name' => $this->faker->randomElement($groupNames),
            'plant_id' => \App\Models\Plant::factory(),
            'number_of_plants' => $this->faker->numberBetween(1, 100),
            'co2_avoided_total' => $this->faker->randomFloat(4, 10, 1000),
            'custom_label' => $this->faker->optional(0.3)->sentence(3),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the plant group is collective (no user).
     */
    public function collective(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }

    /**
     * Indicate that the plant group is individual (has user).
     */
    public function individual(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => \App\Models\User::factory(),
        ]);
    }

    /**
     * Indicate that the plant group is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the plant group has many plants.
     */
    public function large(): static
    {
        return $this->state(fn (array $attributes) => [
            'number_of_plants' => $this->faker->numberBetween(100, 1000),
            'co2_avoided_total' => $this->faker->randomFloat(4, 1000, 10000),
        ]);
    }

    /**
     * Indicate that the plant group has few plants.
     */
    public function small(): static
    {
        return $this->state(fn (array $attributes) => [
            'number_of_plants' => $this->faker->numberBetween(1, 10),
            'co2_avoided_total' => $this->faker->randomFloat(4, 1, 100),
        ]);
    }

    /**
     * Indicate that the plant group has high CO2 avoidance.
     */
    public function highCo2Avoidance(): static
    {
        return $this->state(fn (array $attributes) => [
            'co2_avoided_total' => $this->faker->randomFloat(4, 500, 2000),
        ]);
    }
}
