<?php

namespace Database\Factories;

use App\Models\Balance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Balance>
 */
class BalanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = array_keys(Balance::TYPES);
        $currencies = array_keys(Balance::CURRENCIES);

        return [
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement($types),
            'amount' => $this->faker->randomFloat(6, 0, 10000),
            'currency' => $this->faker->randomElement($currencies),
            'is_frozen' => $this->faker->boolean(5), // 5% congelados
            'last_transaction_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 month', 'now'),
            'daily_limit' => $this->faker->optional(0.6)->randomFloat(2, 100, 5000),
            'monthly_limit' => $this->faker->optional(0.6)->randomFloat(2, 1000, 50000),
            'metadata' => [
                'account_notes' => $this->faker->optional(0.3)->sentence,
                'risk_level' => $this->faker->randomElement(['low', 'medium', 'high']),
            ],
        ];
    }

    /**
     * Indicate that the balance is a wallet.
     */
    public function wallet(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'wallet',
            'currency' => 'EUR',
            'amount' => $this->faker->randomFloat(2, 0, 5000),
            'daily_limit' => $this->faker->randomFloat(2, 500, 2000),
            'monthly_limit' => $this->faker->randomFloat(2, 5000, 20000),
        ]);
    }

    /**
     * Indicate that the balance is energy.
     */
    public function energy(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'energy_kwh',
            'currency' => 'EUR',
            'amount' => $this->faker->randomFloat(3, 0, 10000), // kWh
            'daily_limit' => null, // Sin límite para energía
            'monthly_limit' => null,
        ]);
    }

    /**
     * Indicate that the balance is mining.
     */
    public function mining(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'mining_ths',
            'currency' => 'BTC',
            'amount' => $this->faker->randomFloat(6, 0, 100), // TH/s
            'daily_limit' => null,
            'monthly_limit' => null,
        ]);
    }

    /**
     * Indicate that the balance has high amount.
     */
    public function highAmount(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $this->faker->randomFloat(6, 5000, 50000),
            'last_transaction_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the balance is frozen.
     */
    public function frozen(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_frozen' => true,
            'metadata' => [
                'frozen_at' => now()->toISOString(),
                'freeze_reason' => $this->faker->randomElement([
                    'Suspicious activity',
                    'Manual review required',
                    'Compliance check',
                    'User request',
                ]),
            ],
        ]);
    }

    /**
     * Indicate that the balance is empty.
     */
    public function empty(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => 0,
            'last_transaction_at' => null,
        ]);
    }

    /**
     * Indicate that the balance is for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the balance is carbon credits.
     */
    public function carbonCredits(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'carbon_credits',
            'currency' => 'EUR',
            'amount' => $this->faker->randomFloat(6, 0, 1000), // tCO₂e
        ]);
    }

    /**
     * Indicate that the balance has limits.
     */
    public function withLimits(): static
    {
        return $this->state(fn (array $attributes) => [
            'daily_limit' => $this->faker->randomFloat(2, 100, 1000),
            'monthly_limit' => $this->faker->randomFloat(2, 2000, 10000),
        ]);
    }

    /**
     * Indicate that the balance has no limits.
     */
    public function withoutLimits(): static
    {
        return $this->state(fn (array $attributes) => [
            'daily_limit' => null,
            'monthly_limit' => null,
        ]);
    }
}