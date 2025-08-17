<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        $types = array_keys(Product::TYPES);
        $units = array_keys(Product::UNITS);

        return [
            'provider_id' => Provider::factory(),
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraphs(2, true),
            'type' => $this->faker->randomElement($types),
            'base_purchase_price' => $this->faker->randomFloat(2, 10, 1000),
            'base_sale_price' => $this->faker->randomFloat(2, 15, 1200),
            'commission_type' => $this->faker->randomElement(['percentage', 'fixed', 'none']),
            'commission_value' => $this->faker->randomFloat(4, 0, 15),
            'surcharge_type' => $this->faker->randomElement(['percentage', 'fixed', 'none']),
            'surcharge_value' => $this->faker->randomFloat(4, 0, 10),
            'unit' => $this->faker->randomElement($units),
            'is_active' => $this->faker->boolean(90), // 90% activos
            'start_date' => $this->faker->optional(0.3)->dateTimeBetween('-1 month', '+1 month'),
            'end_date' => $this->faker->optional(0.2)->dateTimeBetween('+1 month', '+1 year'),
            'metadata' => [
                'technical_specs' => $this->faker->words(5),
                'warranty_years' => $this->faker->numberBetween(1, 10),
            ],
            'renewable_percentage' => $this->faker->randomFloat(2, 0, 100),
            'carbon_footprint' => $this->faker->randomFloat(4, 0.001, 1.5),
            'geographical_zone' => $this->faker->randomElement([
                'Madrid', 'Cataluña', 'Andalucía', 'Valencia', 'País Vasco'
            ]),
            'image_path' => $this->faker->optional(0.6)->filePath(),
            'features' => $this->faker->words(4),
            'stock_quantity' => $this->faker->optional(0.7)->numberBetween(1, 1000),
            'weight' => $this->faker->optional(0.5)->randomFloat(3, 0.1, 100),
            'dimensions' => [
                'length' => $this->faker->numberBetween(10, 200),
                'width' => $this->faker->numberBetween(10, 200),
                'height' => $this->faker->numberBetween(5, 50),
            ],
            'warranty_info' => $this->faker->optional(0.7)->sentence,
            'estimated_lifespan_years' => $this->faker->optional(0.8)->numberBetween(5, 25),
            'meta_title' => $this->faker->optional(0.4)->sentence,
            'meta_description' => $this->faker->optional(0.4)->paragraph,
            'keywords' => $this->faker->words(5),
        ];
    }

    /**
     * Indicate that the product is energy-related.
     */
    public function energy(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'energy_kwh',
            'unit' => 'kWh',
            'renewable_percentage' => $this->faker->randomFloat(2, 70, 100),
            'carbon_footprint' => $this->faker->randomFloat(4, 0.001, 0.1),
            'base_purchase_price' => $this->faker->randomFloat(2, 0.05, 0.25),
            'base_sale_price' => $this->faker->randomFloat(2, 0.08, 0.35),
            'estimated_lifespan_years' => null,
            'weight' => null,
        ]);
    }

    /**
     * Indicate that the product is a production right.
     */
    public function productionRight(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'production_right',
            'unit' => 'percentage',
            'renewable_percentage' => $this->faker->randomFloat(2, 80, 100),
            'carbon_footprint' => $this->faker->randomFloat(4, 0.001, 0.05),
            'base_purchase_price' => $this->faker->randomFloat(2, 500, 5000),
            'base_sale_price' => $this->faker->randomFloat(2, 600, 6000),
            'estimated_lifespan_years' => $this->faker->numberBetween(10, 25),
            'metadata' => [
                'annual_kwh_capacity' => $this->faker->numberBetween(1000, 10000),
                'installation_location' => $this->faker->city,
                'start_production_date' => $this->faker->dateTimeBetween('+1 month', '+6 months'),
            ],
        ]);
    }

    /**
     * Indicate that the product is mining-related.
     */
    public function mining(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'mining_ths',
            'unit' => 'TH/s',
            'renewable_percentage' => $this->faker->randomFloat(2, 0, 30),
            'carbon_footprint' => $this->faker->randomFloat(4, 0.5, 1.5),
            'base_purchase_price' => $this->faker->randomFloat(2, 100, 2000),
            'base_sale_price' => $this->faker->randomFloat(2, 120, 2400),
            'estimated_lifespan_years' => $this->faker->numberBetween(2, 5),
            'metadata' => [
                'hash_rate' => $this->faker->numberBetween(50, 200),
                'power_consumption' => $this->faker->numberBetween(1000, 3500),
                'mining_pool' => $this->faker->company,
            ],
        ]);
    }

    /**
     * Indicate that the product is physical.
     */
    public function physical(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'physical',
            'unit' => 'unit',
            'base_purchase_price' => $this->faker->randomFloat(2, 50, 5000),
            'base_sale_price' => $this->faker->randomFloat(2, 75, 6000),
            'stock_quantity' => $this->faker->numberBetween(1, 100),
            'weight' => $this->faker->randomFloat(3, 0.5, 50),
            'dimensions' => [
                'length' => $this->faker->numberBetween(20, 150),
                'width' => $this->faker->numberBetween(20, 150),
                'height' => $this->faker->numberBetween(10, 100),
            ],
        ]);
    }

    /**
     * Indicate that the product is an energy bond.
     */
    public function energyBond(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'energy_bond',
            'unit' => 'kWh',
            'renewable_percentage' => 100,
            'carbon_footprint' => 0,
            'base_purchase_price' => 0, // Bonos son gratuitos
            'base_sale_price' => 0,
            'stock_quantity' => null, // Ilimitado
            'estimated_lifespan_years' => 1,
            'metadata' => [
                'bond_value' => $this->faker->numberBetween(10, 100),
                'eligibility_criteria' => $this->faker->words(3),
                'usage_restrictions' => $this->faker->sentence,
            ],
        ]);
    }

    /**
     * Indicate that the product is highly sustainable.
     */
    public function sustainable(): static
    {
        return $this->state(fn (array $attributes) => [
            'renewable_percentage' => $this->faker->randomFloat(2, 90, 100),
            'carbon_footprint' => $this->faker->randomFloat(4, 0.001, 0.05),
        ]);
    }

    /**
     * Indicate that the product is premium.
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'base_purchase_price' => $this->faker->randomFloat(2, 1000, 10000),
            'base_sale_price' => $this->faker->randomFloat(2, 1200, 12000),
            'renewable_percentage' => $this->faker->randomFloat(2, 95, 100),
            'carbon_footprint' => $this->faker->randomFloat(4, 0.001, 0.02),
            'estimated_lifespan_years' => $this->faker->numberBetween(15, 30),
            'warranty_info' => 'Garantía extendida premium de ' . $this->faker->numberBetween(5, 10) . ' años',
        ]);
    }

    /**
     * Indicate that the product is for a specific provider.
     */
    public function forProvider(Provider $provider): static
    {
        return $this->state(fn (array $attributes) => [
            'provider_id' => $provider->id,
        ]);
    }

    /**
     * Indicate that the product is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
        ]);
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}