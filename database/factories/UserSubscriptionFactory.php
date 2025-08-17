<?php

namespace Database\Factories;

use App\Models\UserSubscription;
use App\Models\User;
use App\Models\EnergyCooperative;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserSubscription>
 */
class UserSubscriptionFactory extends Factory
{
    protected $model = UserSubscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'energy_cooperative_id' => $this->faker->boolean(60) ? EnergyCooperative::factory() : null,
            'subscription_type' => $this->faker->randomElement(['basic_renewable', 'premium_green', 'community_plan']),
            'plan_name' => $this->faker->randomElement(['Plan Solar Básico', 'Plan Verde Premium', 'Plan Comunitario']),
            'service_category' => $this->faker->randomElement(['energy_supply', 'cooperative_membership']),
            'status' => $this->faker->randomElement(['pending', 'active', 'paused', 'cancelled']),
            'start_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'billing_frequency' => $this->faker->randomElement(['monthly', 'quarterly', 'annual']),
            'price' => $this->faker->randomFloat(2, 20, 200),
            'currency' => 'EUR',
            'auto_renewal' => $this->faker->boolean(80),
        ];
    }

    /**
     * Indicate that the subscription is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'auto_renewal' => true,
        ]);
    }

    /**
     * Indicate that the subscription is paused.
     */
    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paused',
            'paused_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }
}