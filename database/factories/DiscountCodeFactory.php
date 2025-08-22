<?php

namespace Database\Factories;

use App\Models\DiscountCode;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DiscountCode>
 */
class DiscountCodeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DiscountCode::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['percentage', 'fixed_amount', 'free_shipping', 'buy_one_get_one', 'other'];
        $statuses = ['active', 'inactive', 'expired', 'suspended'];
        $promotionalWords = ['Sale', 'Discount', 'Offer', 'Deal', 'Promo', 'Special', 'Limited', 'Flash'];

        return [
            'code' => strtoupper($this->faker->bothify('????##')),
            'name' => $this->faker->randomElement($promotionalWords) . ' ' . $this->faker->numberBetween(5, 50) . ($this->faker->boolean(70) ? '% Off' : ' Off'),
            'description' => $this->faker->sentence(10),
            'type' => $this->faker->randomElement($types),
            'value' => $this->faker->randomFloat(2, 5, 50),
            'max_discount_amount' => $this->faker->optional(0.7)->randomFloat(2, 10, 200),
            'min_order_amount' => $this->faker->optional(0.6)->randomFloat(2, 20, 500),
            'max_order_amount' => $this->faker->optional(0.4)->randomFloat(2, 1000, 10000),
            'usage_limit' => $this->faker->optional(0.8)->numberBetween(10, 1000),
            'usage_limit_per_user' => $this->faker->optional(0.7)->numberBetween(1, 5),
            'usage_count' => 0,
            'total_discount_amount' => 0.0,
            'status' => $this->faker->randomElement($statuses),
            'is_public' => $this->faker->boolean(80),
            'is_first_time_only' => $this->faker->boolean(20),
            'is_new_customer_only' => $this->faker->boolean(15),
            'is_returning_customer_only' => $this->faker->boolean(10),
            'valid_from' => $this->faker->optional(0.7)->dateTimeBetween('-1 month', 'now'),
            'valid_until' => $this->faker->optional(0.8)->dateTimeBetween('now', '+6 months'),
            'organization_id' => Organization::factory(),
            'created_by' => User::factory(),
            'activated_at' => $this->faker->optional(0.6)->dateTimeBetween('-2 months', 'now'),
            'deactivated_at' => $this->faker->optional(0.2)->dateTimeBetween('-1 month', 'now'),
            'notes' => $this->faker->optional(0.6)->paragraph(1),
            'tags' => $this->faker->optional(0.7)->words($this->faker->numberBetween(2, 5)),
            'terms_conditions' => $this->faker->optional(0.5)->paragraph(2),
            'restrictions' => $this->faker->optional(0.4)->paragraph(1),
            'auto_apply' => $this->faker->boolean(30),
            'priority' => $this->faker->optional(0.6)->numberBetween(1, 100),
            'is_stackable' => $this->faker->boolean(40),
            'max_stack_count' => $this->faker->optional(0.3)->numberBetween(2, 5),
            'customer_groups' => $this->faker->optional(0.5)->words($this->faker->numberBetween(1, 3)),
            'geographic_restrictions' => $this->faker->optional(0.3)->array([
                'countries' => $this->faker->optional(0.7)->words($this->faker->numberBetween(1, 3)),
                'states' => $this->faker->optional(0.5)->words($this->faker->numberBetween(1, 2)),
                'cities' => $this->faker->optional(0.4)->words($this->faker->numberBetween(1, 2)),
            ]),
            'time_restrictions' => $this->faker->optional(0.2)->array([
                'days_of_week' => $this->faker->optional(0.6)->randomElements([1, 2, 3, 4, 5, 6, 7], $this->faker->numberBetween(1, 3)),
                'hours_of_day' => $this->faker->optional(0.4)->randomElements(range(9, 18), $this->faker->numberBetween(1, 5)),
            ]),
            'applicable_products' => $this->faker->optional(0.4)->words($this->faker->numberBetween(1, 5)),
            'applicable_categories' => $this->faker->optional(0.4)->words($this->faker->numberBetween(1, 3)),
            'excluded_products' => $this->faker->optional(0.3)->words($this->faker->numberBetween(1, 3)),
            'excluded_categories' => $this->faker->optional(0.3)->words($this->faker->numberBetween(1, 2)),
            'attachments' => $this->faker->optional(0.3)->words($this->faker->numberBetween(1, 2)),
        ];
    }

    /**
     * Indicate that the discount code is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'activated_at' => now(),
            'deactivated_at' => null,
        ]);
    }

    /**
     * Indicate that the discount code is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
            'activated_at' => null,
            'deactivated_at' => now(),
        ]);
    }

    /**
     * Indicate that the discount code is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'valid_until' => now()->subDays(1),
        ]);
    }

    /**
     * Indicate that the discount code is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
            'activated_at' => null,
            'deactivated_at' => now(),
        ]);
    }

    /**
     * Indicate that the discount code is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Indicate that the discount code is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    /**
     * Indicate that the discount code is percentage-based.
     */
    public function percentage(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'percentage',
            'value' => $this->faker->randomFloat(2, 5, 50),
        ]);
    }

    /**
     * Indicate that the discount code is fixed amount.
     */
    public function fixedAmount(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'fixed_amount',
            'value' => $this->faker->randomFloat(2, 5, 100),
        ]);
    }

    /**
     * Indicate that the discount code is free shipping.
     */
    public function freeShipping(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'free_shipping',
            'value' => $this->faker->randomFloat(2, 5, 20),
        ]);
    }

    /**
     * Indicate that the discount code is buy one get one.
     */
    public function buyOneGetOne(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'buy_one_get_one',
            'value' => $this->faker->randomFloat(2, 10, 50),
        ]);
    }

    /**
     * Indicate that the discount code has high value.
     */
    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => $this->faker->randomFloat(2, 30, 80),
        ]);
    }

    /**
     * Indicate that the discount code has low value.
     */
    public function lowValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => $this->faker->randomFloat(2, 5, 15),
        ]);
    }

    /**
     * Indicate that the discount code has usage limit.
     */
    public function withUsageLimit(): static
    {
        return $this->state(fn (array $attributes) => [
            'usage_limit' => $this->faker->numberBetween(10, 500),
        ]);
    }

    /**
     * Indicate that the discount code has no usage limit.
     */
    public function unlimitedUsage(): static
    {
        return $this->state(fn (array $attributes) => [
            'usage_limit' => null,
        ]);
    }

    /**
     * Indicate that the discount code has minimum order amount.
     */
    public function withMinOrderAmount(): static
    {
        return $this->state(fn (array $attributes) => [
            'min_order_amount' => $this->faker->randomFloat(2, 25, 200),
        ]);
    }

    /**
     * Indicate that the discount code has maximum discount amount.
     */
    public function withMaxDiscountAmount(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_discount_amount' => $this->faker->randomFloat(2, 20, 150),
        ]);
    }

    /**
     * Indicate that the discount code is first-time only.
     */
    public function firstTimeOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_first_time_only' => true,
        ]);
    }

    /**
     * Indicate that the discount code is for new customers only.
     */
    public function newCustomerOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_new_customer_only' => true,
        ]);
    }

    /**
     * Indicate that the discount code is for returning customers only.
     */
    public function returningCustomerOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_returning_customer_only' => true,
        ]);
    }

    /**
     * Indicate that the discount code is auto-apply.
     */
    public function autoApply(): static
    {
        return $this->state(fn (array $attributes) => [
            'auto_apply' => true,
        ]);
    }

    /**
     * Indicate that the discount code is stackable.
     */
    public function stackable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_stackable' => true,
            'max_stack_count' => $this->faker->numberBetween(2, 5),
        ]);
    }

    /**
     * Indicate that the discount code has tags.
     */
    public function withTags(): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => $this->faker->words($this->faker->numberBetween(2, 5)),
        ]);
    }

    /**
     * Indicate that the discount code has terms and conditions.
     */
    public function withTerms(): static
    {
        return $this->state(fn (array $attributes) => [
            'terms_conditions' => $this->faker->paragraph(2),
        ]);
    }

    /**
     * Indicate that the discount code has restrictions.
     */
    public function withRestrictions(): static
    {
        return $this->state(fn (array $attributes) => [
            'restrictions' => $this->faker->paragraph(1),
        ]);
    }

    /**
     * Indicate that the discount code has geographic restrictions.
     */
    public function withGeographicRestrictions(): static
    {
        return $this->state(fn (array $attributes) => [
            'geographic_restrictions' => [
                'countries' => $this->faker->words($this->faker->numberBetween(1, 3)),
                'states' => $this->faker->words($this->faker->numberBetween(1, 2)),
                'cities' => $this->faker->words($this->faker->numberBetween(1, 2)),
            ],
        ]);
    }

    /**
     * Indicate that the discount code has time restrictions.
     */
    public function withTimeRestrictions(): static
    {
        return $this->state(fn (array $attributes) => [
            'time_restrictions' => [
                'days_of_week' => $this->faker->randomElements([1, 2, 3, 4, 5, 6, 7], $this->faker->numberBetween(1, 3)),
                'hours_of_day' => $this->faker->randomElements(range(9, 18), $this->faker->numberBetween(1, 5)),
            ],
        ]);
    }

    /**
     * Indicate that the discount code has applicable products.
     */
    public function withApplicableProducts(): static
    {
        return $this->state(fn (array $attributes) => [
            'applicable_products' => $this->faker->words($this->faker->numberBetween(1, 5)),
        ]);
    }

    /**
     * Indicate that the discount code has applicable categories.
     */
    public function withApplicableCategories(): static
    {
        return $this->state(fn (array $attributes) => [
            'applicable_categories' => $this->faker->words($this->faker->numberBetween(1, 3)),
        ]);
    }

    /**
     * Indicate that the discount code has excluded products.
     */
    public function withExcludedProducts(): static
    {
        return $this->state(fn (array $attributes) => [
            'excluded_products' => $this->faker->words($this->faker->numberBetween(1, 3)),
        ]);
    }

    /**
     * Indicate that the discount code has excluded categories.
     */
    public function withExcludedCategories(): static
    {
        return $this->state(fn (array $attributes) => [
            'excluded_categories' => $this->faker->words($this->faker->numberBetween(1, 2)),
        ]);
    }

    /**
     * Indicate that the discount code has customer groups.
     */
    public function withCustomerGroups(): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_groups' => $this->faker->words($this->faker->numberBetween(1, 3)),
        ]);
    }

    /**
     * Indicate that the discount code has attachments.
     */
    public function withAttachments(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachments' => $this->faker->words($this->faker->numberBetween(1, 2)),
        ]);
    }

    /**
     * Indicate that the discount code is long-term valid.
     */
    public function longTerm(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_from' => now()->subDays(30),
            'valid_until' => now()->addMonths(6),
        ]);
    }

    /**
     * Indicate that the discount code is short-term valid.
     */
    public function shortTerm(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_from' => now()->subDays(7),
            'valid_until' => now()->addDays(30),
        ]);
    }

    /**
     * Indicate that the discount code is expiring soon.
     */
    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_until' => now()->addDays(7),
        ]);
    }

    /**
     * Indicate that the discount code is seasonal.
     */
    public function seasonal(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement(['Summer', 'Winter', 'Spring', 'Fall', 'Holiday']) . ' ' . $this->faker->randomElement(['Sale', 'Discount', 'Offer']) . ' ' . $this->faker->numberBetween(10, 50) . '%',
            'tags' => ['seasonal', 'limited-time', 'special-offer'],
        ]);
    }

    /**
     * Indicate that the discount code is for holidays.
     */
    public function holiday(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement(['Christmas', 'New Year', 'Easter', 'Thanksgiving', 'Black Friday']) . ' ' . $this->faker->randomElement(['Sale', 'Discount', 'Offer']) . ' ' . $this->faker->numberBetween(15, 60) . '%',
            'tags' => ['holiday', 'celebration', 'special-offer'],
        ]);
    }

    /**
     * Indicate that the discount code is for new customers.
     */
    public function newCustomer(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Welcome ' . $this->faker->numberBetween(10, 25) . '% Off',
            'is_new_customer_only' => true,
            'tags' => ['welcome', 'new-customer', 'first-purchase'],
        ]);
    }

    /**
     * Indicate that the discount code is for loyalty customers.
     */
    public function loyalty(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Loyalty ' . $this->faker->numberBetween(15, 30) . '% Off',
            'is_returning_customer_only' => true,
            'tags' => ['loyalty', 'returning-customer', 'appreciation'],
        ]);
    }

    /**
     * Indicate that the discount code has high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $this->faker->numberBetween(80, 100),
        ]);
    }

    /**
     * Indicate that the discount code has low priority.
     */
    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $this->faker->numberBetween(1, 20),
        ]);
    }
}
