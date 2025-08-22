<?php

namespace Database\Factories;

use App\Models\BondDonation;
use App\Models\EnergyBond;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BondDonation>
 */
class BondDonationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BondDonation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $donationTypes = ['one_time', 'recurring', 'matching', 'challenge', 'memorial', 'honor', 'corporate', 'foundation', 'other'];
        $paymentMethods = ['credit_card', 'debit_card', 'bank_transfer', 'paypal', 'stripe', 'check', 'cash', 'crypto', 'other'];
        $paymentStatuses = ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded'];
        $statuses = ['pending', 'confirmed', 'processed', 'rejected', 'refunded', 'cancelled'];
        $currencies = ['USD', 'EUR', 'GBP', 'JPY'];
        $recurringFrequencies = ['weekly', 'monthly', 'quarterly', 'annually'];

        return [
            'donor_id' => User::factory(),
            'donor_name' => $this->faker->name(),
            'donor_email' => $this->faker->unique()->safeEmail(),
            'donor_phone' => $this->faker->optional(0.7)->phoneNumber(),
            'donor_address' => $this->faker->optional(0.6)->address(),
            'donor_city' => $this->faker->optional(0.6)->city(),
            'donor_state' => $this->faker->optional(0.6)->state(),
            'donor_country' => $this->faker->optional(0.6)->country(),
            'donor_postal_code' => $this->faker->optional(0.6)->postcode(),
            'energy_bond_id' => EnergyBond::factory(),
            'organization_id' => Organization::factory(),
            'campaign_id' => null, // Will be set by specific states if needed
            'donation_type' => $this->faker->randomElement($donationTypes),
            'amount' => $this->faker->randomFloat(2, 10, 10000),
            'currency' => $this->faker->randomElement($currencies),
            'payment_method' => $this->faker->randomElement($paymentMethods),
            'payment_status' => $this->faker->randomElement($paymentStatuses),
            'status' => $this->faker->randomElement($statuses),
            'is_anonymous' => $this->faker->boolean(20),
            'is_public' => $this->faker->boolean(80),
            'message' => $this->faker->optional(0.6)->paragraph(2),
            'dedication_name' => $this->faker->optional(0.3)->name(),
            'dedication_message' => $this->faker->optional(0.3)->sentence(),
            'recurring_frequency' => $this->faker->optional(0.2)->randomElement($recurringFrequencies),
            'recurring_start_date' => $this->faker->optional(0.2)->dateTimeBetween('+1 day', '+30 days'),
            'recurring_end_date' => $this->faker->optional(0.2)->dateTimeBetween('+31 days', '+365 days'),
            'tax_receipt_required' => $this->faker->boolean(70),
            'tax_receipt_sent' => $this->faker->boolean(50),
            'tax_receipt_number' => $this->faker->optional(0.4)->bothify('TR-####-####'),
            'tax_receipt_date' => $this->faker->optional(0.4)->dateTimeBetween('-30 days', 'now'),
            'notes' => $this->faker->optional(0.5)->paragraph(1),
            'internal_notes' => $this->faker->optional(0.4)->paragraph(1),
            'tags' => $this->faker->optional(0.6)->words($this->faker->numberBetween(1, 5)),
            'attachments' => $this->faker->optional(0.3)->words($this->faker->numberBetween(1, 3)),
        ];
    }

    /**
     * Indicate that the donation is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
    }

    /**
     * Indicate that the donation is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'payment_status' => 'completed',
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Indicate that the donation is processed.
     */
    public function processed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processed',
            'payment_status' => 'completed',
            'confirmed_at' => now()->subDays($this->faker->numberBetween(1, 7)),
            'processed_at' => now(),
        ]);
    }

    /**
     * Indicate that the donation is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'payment_status' => 'failed',
            'rejected_at' => now(),
            'rejection_reason' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the donation is refunded.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'refunded',
            'payment_status' => 'refunded',
            'confirmed_at' => now()->subDays($this->faker->numberBetween(7, 30)),
            'refunded_at' => now(),
            'refund_reason' => $this->faker->sentence(),
            'refund_amount' => $this->faker->randomFloat(2, 10, $this->amount),
        ]);
    }

    /**
     * Indicate that the donation is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'payment_status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Indicate that the donation is anonymous.
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_anonymous' => true,
        ]);
    }

    /**
     * Indicate that the donation is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Indicate that the donation is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    /**
     * Indicate that the donation is one-time.
     */
    public function oneTime(): static
    {
        return $this->state(fn (array $attributes) => [
            'donation_type' => 'one_time',
        ]);
    }

    /**
     * Indicate that the donation is recurring.
     */
    public function recurring(): static
    {
        return $this->state(fn (array $attributes) => [
            'donation_type' => 'recurring',
            'recurring_frequency' => $this->faker->randomElement(['weekly', 'monthly', 'quarterly', 'annually']),
            'recurring_start_date' => now()->addDays($this->faker->numberBetween(1, 30)),
            'recurring_end_date' => now()->addDays($this->faker->numberBetween(31, 365)),
        ]);
    }

    /**
     * Indicate that the donation is a matching donation.
     */
    public function matching(): static
    {
        return $this->state(fn (array $attributes) => [
            'donation_type' => 'matching',
        ]);
    }

    /**
     * Indicate that the donation is a challenge donation.
     */
    public function challenge(): static
    {
        return $this->state(fn (array $attributes) => [
            'donation_type' => 'challenge',
        ]);
    }

    /**
     * Indicate that the donation is a memorial donation.
     */
    public function memorial(): static
    {
        return $this->state(fn (array $attributes) => [
            'donation_type' => 'memorial',
            'dedication_name' => $this->faker->name(),
            'dedication_message' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the donation is an honor donation.
     */
    public function honor(): static
    {
        return $this->state(fn (array $attributes) => [
            'donation_type' => 'honor',
            'dedication_name' => $this->faker->name(),
            'dedication_message' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the donation is corporate.
     */
    public function corporate(): static
    {
        return $this->state(fn (array $attributes) => [
            'donation_type' => 'corporate',
            'amount' => $this->faker->randomFloat(2, 1000, 50000),
        ]);
    }

    /**
     * Indicate that the donation is from a foundation.
     */
    public function foundation(): static
    {
        return $this->state(fn (array $attributes) => [
            'donation_type' => 'foundation',
            'amount' => $this->faker->randomFloat(2, 5000, 100000),
        ]);
    }

    /**
     * Indicate that the donation requires tax receipt.
     */
    public function requiresTaxReceipt(): static
    {
        return $this->state(fn (array $attributes) => [
            'tax_receipt_required' => true,
        ]);
    }

    /**
     * Indicate that the donation has tax receipt sent.
     */
    public function taxReceiptSent(): static
    {
        return $this->state(fn (array $attributes) => [
            'tax_receipt_required' => true,
            'tax_receipt_sent' => true,
            'tax_receipt_number' => $this->faker->bothify('TR-####-####'),
            'tax_receipt_date' => now(),
        ]);
    }

    /**
     * Indicate that the donation is high value.
     */
    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $this->faker->randomFloat(2, 10000, 100000),
        ]);
    }

    /**
     * Indicate that the donation is low value.
     */
    public function lowValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $this->faker->randomFloat(2, 10, 100),
        ]);
    }

    /**
     * Indicate that the donation is in USD.
     */
    public function usd(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => 'USD',
        ]);
    }

    /**
     * Indicate that the donation is in EUR.
     */
    public function eur(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => 'EUR',
        ]);
    }

    /**
     * Indicate that the donation is paid by credit card.
     */
    public function creditCard(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'credit_card',
        ]);
    }

    /**
     * Indicate that the donation is paid by bank transfer.
     */
    public function bankTransfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'bank_transfer',
        ]);
    }

    /**
     * Indicate that the donation is paid by PayPal.
     */
    public function paypal(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'paypal',
        ]);
    }

    /**
     * Indicate that the donation has a message.
     */
    public function withMessage(): static
    {
        return $this->state(fn (array $attributes) => [
            'message' => $this->faker->paragraph(2),
        ]);
    }

    /**
     * Indicate that the donation has tags.
     */
    public function withTags(): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => $this->faker->words($this->faker->numberBetween(2, 5)),
        ]);
    }

    /**
     * Indicate that the donation has attachments.
     */
    public function withAttachments(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachments' => $this->faker->words($this->faker->numberBetween(1, 3)),
        ]);
    }

    /**
     * Indicate that the donation is recent (within last 7 days).
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'donation_date' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the donation is old (more than 30 days ago).
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'donation_date' => $this->faker->dateTimeBetween('-365 days', '-30 days'),
        ]);
    }
}
