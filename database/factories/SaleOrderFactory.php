<?php

namespace Database\Factories;

use App\Models\SaleOrder;
use App\Models\Organization;
use App\Models\User;
use App\Models\CustomerProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SaleOrder>
 */
class SaleOrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SaleOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
        $paymentStatuses = ['pending', 'processing', 'paid', 'failed', 'cancelled', 'refunded'];
        $paymentMethods = ['credit_card', 'debit_card', 'paypal', 'bank_transfer', 'cash', 'check'];
        $shippingMethods = ['standard', 'express', 'overnight', 'pickup', 'local_delivery'];
        $currencies = ['USD', 'EUR', 'GBP', 'MXN', 'CAD'];

        $subtotal = $this->faker->randomFloat(2, 50, 1000);
        $taxAmount = $subtotal * 0.1; // 10% tax
        $shippingAmount = $this->faker->randomFloat(2, 5, 50);
        $discountAmount = $this->faker->optional(0.3)->randomFloat(2, 5, $subtotal * 0.2); // Up to 20% discount
        $total = $subtotal + $taxAmount + $shippingAmount - ($discountAmount ?? 0);

        return [
            'order_number' => $this->generateOrderNumber(),
            'customer_id' => CustomerProfile::factory(),
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->unique()->safeEmail(),
            'customer_phone' => $this->faker->phoneNumber(),
            'customer_address' => $this->faker->streetAddress(),
            'customer_city' => $this->faker->city(),
            'customer_state' => $this->faker->state(),
            'customer_country' => $this->faker->country(),
            'customer_postal_code' => $this->faker->postcode(),
            'status' => $this->faker->randomElement($statuses),
            'payment_status' => $this->faker->randomElement($paymentStatuses),
            'payment_method' => $this->faker->randomElement($paymentMethods),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'discount_amount' => $discountAmount,
            'total' => $total,
            'currency' => $this->faker->randomElement($currencies),
            'notes' => $this->faker->optional(0.7)->sentence(10),
            'internal_notes' => $this->faker->optional(0.5)->sentence(8),
            'is_urgent' => $this->faker->boolean(15),
            'shipping_method' => $this->faker->randomElement($shippingMethods),
            'tracking_number' => $this->faker->optional(0.6)->bothify('TRK########'),
            'expected_delivery_date' => $this->faker->optional(0.8)->dateTimeBetween('now', '+2 weeks'),
            'discount_code' => $this->faker->optional(0.3)->bothify('DISC####'),
            'organization_id' => Organization::factory(),
            'billing_address' => $this->faker->optional(0.6)->array([
                'street' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'state' => $this->faker->state(),
                'country' => $this->faker->country(),
                'postal_code' => $this->faker->postcode(),
            ]),
            'shipping_address' => $this->faker->optional(0.6)->array([
                'street' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'state' => $this->faker->state(),
                'country' => $this->faker->country(),
                'postal_code' => $this->faker->postcode(),
            ]),
            'custom_fields' => $this->faker->optional(0.4)->array([
                'source' => $this->faker->randomElement(['website', 'mobile_app', 'phone', 'in_store']),
                'campaign' => $this->faker->optional(0.3)->word(),
                'referrer' => $this->faker->optional(0.2)->url(),
            ]),
            'tags' => $this->faker->optional(0.7)->words($this->faker->numberBetween(1, 4)),
            'attachments' => $this->faker->optional(0.3)->words($this->faker->numberBetween(1, 2)),
        ];
    }

    /**
     * Indicate that the sale order is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the sale order is processing.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
        ]);
    }

    /**
     * Indicate that the sale order is shipped.
     */
    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'shipped',
            'tracking_number' => $this->faker->bothify('TRK########'),
        ]);
    }

    /**
     * Indicate that the sale order is delivered.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'tracking_number' => $this->faker->bothify('TRK########'),
        ]);
    }

    /**
     * Indicate that the sale order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Indicate that the sale order is refunded.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'refunded',
        ]);
    }

    /**
     * Indicate that the payment is pending.
     */
    public function paymentPending(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'pending',
        ]);
    }

    /**
     * Indicate that the payment is processing.
     */
    public function paymentProcessing(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'processing',
        ]);
    }

    /**
     * Indicate that the payment is paid.
     */
    public function paymentPaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'paid',
        ]);
    }

    /**
     * Indicate that the payment failed.
     */
    public function paymentFailed(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'failed',
        ]);
    }

    /**
     * Indicate that the payment is cancelled.
     */
    public function paymentCancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'cancelled',
        ]);
    }

    /**
     * Indicate that the payment is refunded.
     */
    public function paymentRefunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'refunded',
        ]);
    }

    /**
     * Indicate that the sale order is urgent.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_urgent' => true,
        ]);
    }

    /**
     * Indicate that the sale order is not urgent.
     */
    public function notUrgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_urgent' => false,
        ]);
    }

    /**
     * Indicate that the sale order has a discount.
     */
    public function withDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_amount' => $this->faker->randomFloat(2, 10, 100),
            'discount_code' => $this->faker->bothify('DISC####'),
        ]);
    }

    /**
     * Indicate that the sale order has no discount.
     */
    public function withoutDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_amount' => 0,
            'discount_code' => null,
        ]);
    }

    /**
     * Indicate that the sale order has high value.
     */
    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'subtotal' => $this->faker->randomFloat(2, 1000, 5000),
            'total' => $this->faker->randomFloat(2, 1100, 5500),
        ]);
    }

    /**
     * Indicate that the sale order has low value.
     */
    public function lowValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'subtotal' => $this->faker->randomFloat(2, 20, 100),
            'total' => $this->faker->randomFloat(2, 25, 120),
        ]);
    }

    /**
     * Indicate that the sale order has express shipping.
     */
    public function expressShipping(): static
    {
        return $this->state(fn (array $attributes) => [
            'shipping_method' => 'express',
            'shipping_amount' => $this->faker->randomFloat(2, 25, 75),
        ]);
    }

    /**
     * Indicate that the sale order has standard shipping.
     */
    public function standardShipping(): static
    {
        return $this->state(fn (array $attributes) => [
            'shipping_method' => 'standard',
            'shipping_amount' => $this->faker->randomFloat(2, 5, 25),
        ]);
    }

    /**
     * Indicate that the sale order has free shipping.
     */
    public function freeShipping(): static
    {
        return $this->state(fn (array $attributes) => [
            'shipping_method' => 'standard',
            'shipping_amount' => 0,
        ]);
    }

    /**
     * Indicate that the sale order has tracking.
     */
    public function withTracking(): static
    {
        return $this->state(fn (array $attributes) => [
            'tracking_number' => $this->faker->bothify('TRK########'),
            'status' => $this->faker->randomElement(['shipped', 'delivered']),
        ]);
    }

    /**
     * Indicate that the sale order has no tracking.
     */
    public function withoutTracking(): static
    {
        return $this->state(fn (array $attributes) => [
            'tracking_number' => null,
            'status' => $this->faker->randomElement(['pending', 'processing']),
        ]);
    }

    /**
     * Indicate that the sale order has expected delivery date.
     */
    public function withExpectedDelivery(): static
    {
        return $this->state(fn (array $attributes) => [
            'expected_delivery_date' => $this->faker->dateTimeBetween('now', '+2 weeks'),
        ]);
    }

    /**
     * Indicate that the sale order has no expected delivery date.
     */
    public function withoutExpectedDelivery(): static
    {
        return $this->state(fn (array $attributes) => [
            'expected_delivery_date' => null,
        ]);
    }

    /**
     * Indicate that the sale order has billing address.
     */
    public function withBillingAddress(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_address' => [
                'street' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'state' => $this->faker->state(),
                'country' => $this->faker->country(),
                'postal_code' => $this->faker->postcode(),
            ],
        ]);
    }

    /**
     * Indicate that the sale order has shipping address.
     */
    public function withShippingAddress(): static
    {
        return $this->state(fn (array $attributes) => [
            'shipping_address' => [
                'street' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'state' => $this->faker->state(),
                'country' => $this->faker->country(),
                'postal_code' => $this->faker->postcode(),
            ],
        ]);
    }

    /**
     * Indicate that the sale order has custom fields.
     */
    public function withCustomFields(): static
    {
        return $this->state(fn (array $attributes) => [
            'custom_fields' => [
                'source' => $this->faker->randomElement(['website', 'mobile_app', 'phone', 'in_store']),
                'campaign' => $this->faker->optional(0.7)->word(),
                'referrer' => $this->faker->optional(0.5)->url(),
                'utm_source' => $this->faker->optional(0.6)->word(),
                'utm_medium' => $this->faker->optional(0.6)->word(),
            ],
        ]);
    }

    /**
     * Indicate that the sale order has tags.
     */
    public function withTags(): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => $this->faker->words($this->faker->numberBetween(2, 5)),
        ]);
    }

    /**
     * Indicate that the sale order has attachments.
     */
    public function withAttachments(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachments' => $this->faker->words($this->faker->numberBetween(1, 3)),
        ]);
    }

    /**
     * Indicate that the sale order is online.
     */
    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'custom_fields' => [
                'source' => 'website',
                'channel' => 'online',
            ],
            'tags' => ['online', 'web-order'],
        ]);
    }

    /**
     * Indicate that the sale order is from mobile app.
     */
    public function mobileApp(): static
    {
        return $this->state(fn (array $attributes) => [
            'custom_fields' => [
                'source' => 'mobile_app',
                'channel' => 'mobile',
            ],
            'tags' => ['mobile', 'app-order'],
        ]);
    }

    /**
     * Indicate that the sale order is from phone.
     */
    public function phone(): static
    {
        return $this->state(fn (array $attributes) => [
            'custom_fields' => [
                'source' => 'phone',
                'channel' => 'telephone',
            ],
            'tags' => ['phone', 'telephone-order'],
        ]);
    }

    /**
     * Indicate that the sale order is in store.
     */
    public function inStore(): static
    {
        return $this->state(fn (array $attributes) => [
            'custom_fields' => [
                'source' => 'in_store',
                'channel' => 'retail',
            ],
            'tags' => ['in-store', 'retail-order'],
        ]);
    }

    /**
     * Indicate that the sale order is from a new customer.
     */
    public function newCustomer(): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => ['new-customer', 'first-order'],
        ]);
    }

    /**
     * Indicate that the sale order is from a returning customer.
     */
    public function returningCustomer(): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => ['returning-customer', 'loyal-customer'],
        ]);
    }

    /**
     * Indicate that the sale order is seasonal.
     */
    public function seasonal(): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => ['seasonal', 'limited-time'],
        ]);
    }

    /**
     * Indicate that the sale order is promotional.
     */
    public function promotional(): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => ['promotional', 'campaign'],
            'discount_amount' => $this->faker->randomFloat(2, 20, 100),
            'discount_code' => $this->faker->bothify('PROMO####'),
        ]);
    }

    /**
     * Generate a unique order number.
     */
    private function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $counter = SaleOrder::whereDate('created_at', today())->count() + 1;
        
        return "{$prefix}-{$date}-" . str_pad($counter, 3, '0', STR_PAD_LEFT);
    }
}
