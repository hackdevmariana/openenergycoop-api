<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $cifCounter = 1;
        
        return [
            'name' => fake()->company() . ' ' . fake()->randomElement(['S.L.', 'S.A.', 'S.C.', 'S.Coop.']),
            'cif' => 'B' . str_pad($cifCounter++, 8, '0', STR_PAD_LEFT),
            'contact_person' => fake()->name(),
            'company_address' => fake()->streetAddress() . ', ' . fake()->postcode() . ' ' . fake()->city() . ', ' . fake()->state(),
        ];
    }

    /**
     * Indicate that the company is a renewable energy company.
     */
    public function renewable(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement([
                'Solar Energy', 'Wind Power', 'Hydroelectric', 'Biomass Solutions'
            ]) . ' ' . fake()->randomElement(['S.L.', 'S.A.', 'S.Coop.']),
        ]);
    }

    /**
     * Indicate that the company is a utility company.
     */
    public function utility(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement([
                'Electric Company', 'Gas Provider', 'Water Services', 'Energy Solutions'
            ]) . ' ' . fake()->randomElement(['S.L.', 'S.A.', 'S.Coop.']),
        ]);
    }

    /**
     * Indicate that the company is a cooperative.
     */
    public function cooperative(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement([
                'Energy Cooperative', 'Renewable Coop', 'Green Power Coop', 'Community Energy'
            ]) . ' S.Coop.',
        ]);
    }
}
