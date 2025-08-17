<?php

namespace Database\Factories;

use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Provider>
 */
class ProviderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = array_keys(Provider::TYPES);
        $certifications = array_keys(Provider::CERTIFICATIONS);

        return [
            'name' => $this->faker->company,
            'description' => $this->faker->paragraph(3),
            'contact_info' => [
                'contact_person' => $this->faker->name,
                'phone' => $this->faker->phoneNumber,
                'address' => $this->faker->address,
            ],
            'type' => $this->faker->randomElement($types),
            'is_active' => $this->faker->boolean(85), // 85% activos
            'website' => $this->faker->optional(0.7)->url,
            'email' => $this->faker->companyEmail,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'logo_path' => $this->faker->optional(0.4)->filePath(),
            'rating' => $this->faker->optional(0.6)->randomFloat(2, 1, 5),
            'total_reviews' => $this->faker->numberBetween(0, 500),
            'certifications' => $this->faker->randomElements($certifications, $this->faker->numberBetween(0, 3)),
            'operating_regions' => $this->faker->randomElements([
                'Madrid', 'Barcelona', 'Valencia', 'Sevilla', 'Bilbao', 
                'Zaragoza', 'Murcia', 'Las Palmas', 'Palma', 'Córdoba'
            ], $this->faker->numberBetween(1, 4)),
        ];
    }

    /**
     * Indicate that the provider is energy-focused.
     */
    public function energy(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'energy',
            'certifications' => ['iso_14001', 'renewable_energy'],
            'rating' => $this->faker->randomFloat(2, 3.5, 5),
        ]);
    }

    /**
     * Indicate that the provider is mining-focused.
     */
    public function mining(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'mining',
            'certifications' => ['energy_star'],
            'rating' => $this->faker->randomFloat(2, 2.5, 4.5),
        ]);
    }

    /**
     * Indicate that the provider is charity-focused.
     */
    public function charity(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'charity',
            'certifications' => ['fair_trade', 'carbon_neutral'],
            'rating' => $this->faker->randomFloat(2, 4, 5),
        ]);
    }

    /**
     * Indicate that the provider is highly rated.
     */
    public function highRated(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => $this->faker->randomFloat(2, 4.2, 5),
            'total_reviews' => $this->faker->numberBetween(100, 1000),
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the provider is new.
     */
    public function new(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => null,
            'total_reviews' => 0,
            'certifications' => [],
        ]);
    }

    /**
     * Indicate that the provider operates in Madrid.
     */
    public function madrid(): static
    {
        return $this->state(fn (array $attributes) => [
            'operating_regions' => ['Madrid'],
            'address' => $this->faker->address . ', Madrid',
        ]);
    }

    /**
     * Create a specific renewable energy provider.
     */
    public function renewableEnergy(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Energía Verde ' . $this->faker->word,
            'type' => 'energy',
            'certifications' => ['iso_14001', 'renewable_energy', 'carbon_neutral'],
            'rating' => $this->faker->randomFloat(2, 4, 5),
            'total_reviews' => $this->faker->numberBetween(50, 300),
            'is_active' => true,
        ]);
    }
}