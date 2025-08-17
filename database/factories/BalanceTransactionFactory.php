<?php

namespace Database\Factories;

use App\Models\Balance;
use App\Models\BalanceTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BalanceTransaction>
 */
class BalanceTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = array_keys(BalanceTransaction::TYPES);
        $statuses = array_keys(BalanceTransaction::STATUSES);
        $amount = $this->faker->randomFloat(6, 1, 1000);
        $balanceBefore = $this->faker->randomFloat(6, 0, 5000);

        return [
            'balance_id' => Balance::factory(),
            'type' => $this->faker->randomElement($types),
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceBefore + ($this->faker->boolean() ? $amount : -$amount),
            'description' => $this->faker->sentence,
            'reference' => $this->faker->optional(0.4)->uuid,
            'related_model_type' => null,
            'related_model_id' => null,
            'batch_id' => $this->faker->optional(0.3)->uuid,
            'exchange_rate' => $this->faker->optional(0.2)->randomFloat(6, 0.1, 10),
            'original_currency' => $this->faker->optional(0.2)->randomElement(['USD', 'BTC', 'ETH']),
            'original_amount' => $this->faker->optional(0.2)->randomFloat(6, 1, 1000),
            'tax_amount' => $this->faker->randomFloat(2, 0, $amount * 0.21),
            'fee_amount' => $this->faker->randomFloat(2, 0, $amount * 0.05),
            'net_amount' => $amount,
            'metadata' => [
                'transaction_notes' => $this->faker->optional(0.3)->sentence,
                'source_ip' => $this->faker->optional(0.5)->ipv4,
                'user_agent' => $this->faker->optional(0.3)->userAgent,
            ],
            'created_by_user_id' => $this->faker->optional(0.7)->randomElement([null, User::factory()]),
            'status' => $this->faker->randomElement($statuses),
            'processed_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 month', 'now'),
            'notes' => $this->faker->optional(0.3)->sentence,
            'accounting_reference' => $this->faker->optional(0.4)->regexify('[A-Z]{2}[0-9]{6}'),
            'is_reconciled' => $this->faker->boolean(60), // 60% reconciliadas
            'reconciled_at' => $this->faker->optional(0.6)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the transaction is income.
     */
    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'income',
            'description' => $this->faker->randomElement([
                'Yield from energy production',
                'Mining rewards',
                'Referral bonus',
                'Investment returns',
                'Sale proceeds',
            ]),
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the transaction is expense.
     */
    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
            'description' => $this->faker->randomElement([
                'Product purchase',
                'Maintenance cost',
                'Transaction fee',
                'Withdrawal',
                'Transfer to external wallet',
            ]),
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the transaction is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'processed_at' => null,
            'is_reconciled' => false,
            'reconciled_at' => null,
        ]);
    }

    /**
     * Indicate that the transaction is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'processed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the transaction failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'notes' => $this->faker->randomElement([
                'Insufficient funds',
                'Payment gateway error',
                'Invalid account',
                'Fraud detection',
                'Technical error',
            ]),
            'is_reconciled' => false,
            'reconciled_at' => null,
        ]);
    }

    /**
     * Indicate that the transaction is a transfer.
     */
    public function transfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $this->faker->randomElement(['transfer_in', 'transfer_out']),
            'batch_id' => $this->faker->uuid,
            'description' => 'Transfer between users',
            'metadata' => [
                'transfer_type' => 'user_to_user',
                'counterpart_user_id' => User::factory(),
            ],
        ]);
    }

    /**
     * Indicate that the transaction has high amount.
     */
    public function highAmount(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $this->faker->randomFloat(6, 1000, 10000),
            'tax_amount' => $this->faker->randomFloat(2, 50, 500),
            'fee_amount' => $this->faker->randomFloat(2, 10, 100),
        ]);
    }

    /**
     * Indicate that the transaction is reconciled.
     */
    public function reconciled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_reconciled' => true,
            'reconciled_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'accounting_reference' => $this->faker->regexify('[A-Z]{2}[0-9]{8}'),
        ]);
    }

    /**
     * Indicate that the transaction is for a specific balance.
     */
    public function forBalance(Balance $balance): static
    {
        return $this->state(fn (array $attributes) => [
            'balance_id' => $balance->id,
        ]);
    }

    /**
     * Indicate that the transaction has foreign exchange.
     */
    public function withForeignExchange(): static
    {
        return $this->state(fn (array $attributes) => [
            'exchange_rate' => $this->faker->randomFloat(6, 0.5, 2),
            'original_currency' => $this->faker->randomElement(['USD', 'BTC', 'ETH']),
            'original_amount' => $this->faker->randomFloat(6, 1, 1000),
        ]);
    }

    /**
     * Indicate that the transaction is part of a batch.
     */
    public function batched(): static
    {
        return $this->state(fn (array $attributes) => [
            'batch_id' => $this->faker->uuid,
            'description' => 'Batch transaction processing',
        ]);
    }
}