<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use App\Models\UserAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserAsset>
 */
class UserAssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sourceTypes = array_keys(UserAsset::SOURCE_TYPES);
        $statuses = array_keys(UserAsset::STATUSES);

        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'quantity' => $this->faker->randomFloat(4, 0.1, 1000),
            'start_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'end_date' => $this->faker->optional(0.7)->dateTimeBetween('+1 month', '+5 years'),
            'source_type' => $this->faker->randomElement($sourceTypes),
            'status' => $this->faker->randomElement($statuses),
            'current_value' => $this->faker->randomFloat(2, 10, 10000),
            'purchase_price' => $this->faker->randomFloat(2, 10, 10000),
            'daily_yield' => $this->faker->optional(0.6)->randomFloat(4, 0.001, 10),
            'total_yield_generated' => $this->faker->randomFloat(4, 0, 1000),
            'efficiency_rating' => $this->faker->optional(0.7)->randomFloat(2, 60, 100),
            'maintenance_cost' => $this->faker->optional(0.5)->randomFloat(2, 5, 500),
            'last_maintenance_date' => $this->faker->optional(0.4)->dateTimeBetween('-6 months', 'now'),
            'next_maintenance_date' => $this->faker->optional(0.6)->dateTimeBetween('now', '+6 months'),
            'auto_reinvest' => $this->faker->boolean(30), // 30% con auto-reinversión
            'reinvest_threshold' => $this->faker->optional(0.3)->randomFloat(2, 100, 1000),
            'reinvest_percentage' => $this->faker->optional(0.3)->randomFloat(2, 10, 80),
            'is_transferable' => $this->faker->boolean(85), // 85% transferibles
            'is_delegatable' => $this->faker->boolean(20), // 20% delegables
            'delegated_to_user_id' => null, // Se asignará en estados específicos
            'metadata' => [
                'acquisition_notes' => $this->faker->optional(0.3)->sentence,
                'performance_notes' => $this->faker->optional(0.2)->sentence,
            ],
            'estimated_annual_return' => $this->faker->optional(0.7)->randomFloat(4, 2, 15),
            'actual_annual_return' => $this->faker->optional(0.5)->randomFloat(4, 1, 20),
            'performance_history' => [
                'last_month_yield' => $this->faker->randomFloat(4, 0, 100),
                'best_month_yield' => $this->faker->randomFloat(4, 0, 200),
                'worst_month_yield' => $this->faker->randomFloat(4, 0, 50),
            ],
            'notifications_enabled' => $this->faker->boolean(80), // 80% con notificaciones
            'alert_preferences' => [
                'maintenance_alerts' => $this->faker->boolean(90),
                'performance_alerts' => $this->faker->boolean(70),
                'price_alerts' => $this->faker->boolean(60),
            ],
        ];
    }

    /**
     * Indicate that the asset is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'start_date' => $this->faker->dateTimeBetween('-6 months', '-1 day'),
            'end_date' => $this->faker->dateTimeBetween('+1 month', '+2 years'),
        ]);
    }

    /**
     * Indicate that the asset is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'start_date' => $this->faker->dateTimeBetween('-2 years', '-6 months'),
            'end_date' => $this->faker->dateTimeBetween('-5 months', '-1 day'),
            'daily_yield' => 0,
        ]);
    }

    /**
     * Indicate that the asset is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'start_date' => $this->faker->dateTimeBetween('+1 day', '+1 month'),
            'daily_yield' => null,
            'total_yield_generated' => 0,
        ]);
    }

    /**
     * Indicate that the asset was purchased.
     */
    public function purchased(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => 'purchase',
            'status' => 'active',
            'purchase_price' => $this->faker->randomFloat(2, 100, 5000),
        ]);
    }

    /**
     * Indicate that the asset was received as a bonus.
     */
    public function bonus(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => 'bonus',
            'status' => 'active',
            'purchase_price' => 0,
            'current_value' => $this->faker->randomFloat(2, 50, 500),
        ]);
    }

    /**
     * Indicate that the asset was transferred.
     */
    public function transferred(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => 'transfer',
            'status' => 'active',
            'purchase_price' => 0,
        ]);
    }

    /**
     * Indicate that the asset has high yield.
     */
    public function highYield(): static
    {
        return $this->state(fn (array $attributes) => [
            'daily_yield' => $this->faker->randomFloat(4, 5, 25),
            'total_yield_generated' => $this->faker->randomFloat(4, 100, 2000),
            'efficiency_rating' => $this->faker->randomFloat(2, 85, 100),
            'estimated_annual_return' => $this->faker->randomFloat(4, 8, 20),
            'actual_annual_return' => $this->faker->randomFloat(4, 7, 22),
        ]);
    }

    /**
     * Indicate that the asset has auto-reinvestment enabled.
     */
    public function autoReinvest(): static
    {
        return $this->state(fn (array $attributes) => [
            'auto_reinvest' => true,
            'reinvest_threshold' => $this->faker->randomFloat(2, 100, 500),
            'reinvest_percentage' => $this->faker->randomFloat(2, 20, 70),
        ]);
    }

    /**
     * Indicate that the asset is delegated.
     */
    public function delegated(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_delegatable' => true,
            'delegated_to_user_id' => User::factory(),
        ]);
    }

    /**
     * Indicate that the asset needs maintenance.
     */
    public function needsMaintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'next_maintenance_date' => $this->faker->dateTimeBetween('-1 week', '+1 week'),
            'maintenance_cost' => $this->faker->randomFloat(2, 50, 300),
            'last_maintenance_date' => $this->faker->dateTimeBetween('-6 months', '-1 month'),
        ]);
    }

    /**
     * Indicate that the asset is for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the asset is for a specific product.
     */
    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
        ]);
    }

    /**
     * Indicate that the asset is energy-related.
     */
    public function energy(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $this->faker->randomFloat(4, 100, 10000), // kWh
            'daily_yield' => $this->faker->randomFloat(4, 1, 50), // kWh diarios
            'unit' => 'kWh',
        ]);
    }

    /**
     * Indicate that the asset is mining-related.
     */
    public function mining(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $this->faker->randomFloat(4, 1, 100), // TH/s
            'daily_yield' => $this->faker->randomFloat(4, 0.01, 1), // BTC equivalente
            'efficiency_rating' => $this->faker->randomFloat(2, 70, 95),
            'maintenance_cost' => $this->faker->randomFloat(2, 20, 200),
        ]);
    }
}