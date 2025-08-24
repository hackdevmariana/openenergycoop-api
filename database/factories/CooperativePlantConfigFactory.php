<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CooperativePlantConfig>
 */
class CooperativePlantConfigFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cooperative_id' => \App\Models\EnergyCooperative::factory(),
            'plant_id' => \App\Models\Plant::factory(),
            'default' => false,
            'active' => true,
            'organization_id' => \App\Models\Organization::factory(),
        ];
    }

    /**
     * Indicate that the config is the default for the cooperative.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'default' => true,
        ]);
    }

    /**
     * Indicate that the config is not the default for the cooperative.
     */
    public function notDefault(): static
    {
        return $this->state(fn (array $attributes) => [
            'default' => false,
        ]);
    }

    /**
     * Indicate that the config is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
        ]);
    }

    /**
     * Indicate that the config is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    /**
     * Indicate that the config has no organization.
     */
    public function noOrganization(): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => null,
        ]);
    }
}
