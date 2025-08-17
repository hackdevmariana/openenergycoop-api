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
        $types = ['renewable', 'traditional', 'hybrid'];
        $certifications = [
            'ISO 14001 - Gestión Ambiental',
            'ISO 50001 - Gestión Energética', 
            'Certificado Energía Renovable',
            'Carbon Neutral Certified',
            'Green Energy Certified',
            'LEED Certified Provider'
        ];

        return [
            'name' => $this->faker->company . ' Energy',
            'description' => $this->faker->paragraph(3),
            'company_id' => \App\Models\Company::factory(),
            'type' => $this->faker->randomElement($types),
            'rating' => $this->faker->randomFloat(2, 1, 5),
            'total_products' => $this->faker->numberBetween(5, 100),
            'sustainability_score' => $this->faker->numberBetween(40, 100),
            'verification_status' => $this->faker->randomElement(['pending', 'verified', 'rejected']),
            'is_active' => $this->faker->boolean(85),
            'certifications' => $this->faker->randomElements($certifications, $this->faker->numberBetween(1, 4)),
            'contact_info' => [
                'email' => $this->faker->companyEmail,
                'phone' => $this->faker->phoneNumber,
                'website' => $this->faker->url,
            ],
            'metadata' => [
                'established_year' => $this->faker->year,
                'employees_count' => $this->faker->numberBetween(10, 1000),
                'headquarters' => $this->faker->city,
            ],
            'last_verified_at' => $this->faker->optional(0.6)->dateTimeThisYear(),
        ];
    }

    /**
     * Indicate that the provider is renewable energy focused.
     */
    public function renewable(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'renewable',
            'certifications' => [
                'ISO 14001 - Gestión Ambiental',
                'Certificado Energía Renovable',
                'Carbon Neutral Certified'
            ],
            'rating' => $this->faker->randomFloat(2, 4.0, 5.0),
            'sustainability_score' => $this->faker->numberBetween(80, 100),
            'verification_status' => 'verified',
        ]);
    }

    /**
     * Indicate that the provider is traditional energy focused.
     */
    public function traditional(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'traditional',
            'certifications' => ['ISO 50001 - Gestión Energética'],
            'rating' => $this->faker->randomFloat(2, 2.5, 4.0),
            'sustainability_score' => $this->faker->numberBetween(40, 70),
        ]);
    }

    /**
     * Indicate that the provider is hybrid energy focused.
     */
    public function hybrid(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'hybrid',
            'certifications' => [
                'ISO 14001 - Gestión Ambiental',
                'ISO 50001 - Gestión Energética'
            ],
            'rating' => $this->faker->randomFloat(2, 3.5, 4.5),
            'sustainability_score' => $this->faker->numberBetween(60, 85),
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
     * Indicate that the provider is newly registered.
     */
    public function newProvider(): static
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
     * Create a verified provider.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => 'verified',
            'rating' => $this->faker->randomFloat(2, 4.0, 5.0),
            'sustainability_score' => $this->faker->numberBetween(75, 100),
            'last_verified_at' => now(),
            'is_active' => true,
        ]);
    }

    /**
     * Create a sustainable provider.
     */
    public function sustainable(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'EcoGreen ' . $this->faker->word . ' Energy',
            'type' => 'renewable',
            'sustainability_score' => $this->faker->numberBetween(85, 100),
            'certifications' => [
                'ISO 14001 - Gestión Ambiental',
                'Certificado Energía Renovable',
                'Carbon Neutral Certified',
                'Green Energy Certified'
            ],
            'rating' => $this->faker->randomFloat(2, 4.2, 5.0),
            'verification_status' => 'verified',
            'is_active' => true,
        ]);
    }
}