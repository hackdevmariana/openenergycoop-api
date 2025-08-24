<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plant>
 */
class PlantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $plantTypes = [
            ['name' => 'Pino', 'unit_label' => 'árbol', 'co2' => 22.5],
            ['name' => 'Vid', 'unit_label' => 'planta', 'co2' => 15.8],
            ['name' => 'Plátano', 'unit_label' => 'árbol', 'co2' => 18.2],
            ['name' => 'Olivo', 'unit_label' => 'árbol', 'co2' => 25.1],
            ['name' => 'Almendro', 'unit_label' => 'árbol', 'co2' => 20.3],
            ['name' => 'Naranjo', 'unit_label' => 'árbol', 'co2' => 16.7],
            ['name' => 'Limonero', 'unit_label' => 'árbol', 'co2' => 17.4],
            ['name' => 'Manzano', 'unit_label' => 'árbol', 'co2' => 19.8],
            ['name' => 'Peral', 'unit_label' => 'árbol', 'co2' => 21.2],
            ['name' => 'Cerezo', 'unit_label' => 'árbol', 'co2' => 23.6],
        ];

        $plant = $this->faker->randomElement($plantTypes);

        return [
            'name' => $plant['name'],
            'co2_equivalent_per_unit_kg' => $plant['co2'],
            'image' => 'plants/' . strtolower($plant['name']) . '.jpg',
            'description' => $this->faker->paragraph(3),
            'unit_label' => $plant['unit_label'],
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the plant is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the plant is a tree.
     */
    public function tree(): static
    {
        return $this->state(fn (array $attributes) => [
            'unit_label' => 'árbol',
        ]);
    }

    /**
     * Indicate that the plant is a vine.
     */
    public function vine(): static
    {
        return $this->state(fn (array $attributes) => [
            'unit_label' => 'planta',
        ]);
    }

    /**
     * Indicate that the plant has high CO2 absorption.
     */
    public function highCo2(): static
    {
        return $this->state(fn (array $attributes) => [
            'co2_equivalent_per_unit_kg' => $this->faker->randomFloat(4, 25, 35),
        ]);
    }

    /**
     * Indicate that the plant has low CO2 absorption.
     */
    public function lowCo2(): static
    {
        return $this->state(fn (array $attributes) => [
            'co2_equivalent_per_unit_kg' => $this->faker->randomFloat(4, 10, 20),
        ]);
    }
}
