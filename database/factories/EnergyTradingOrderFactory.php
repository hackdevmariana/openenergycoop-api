<?php

namespace Database\Factories;

use App\Models\EnergyTradingOrder;
use App\Models\User;
use App\Models\EnergyPool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EnergyTradingOrder>
 */
class EnergyTradingOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $orderType = $this->faker->randomElement(array_keys(EnergyTradingOrder::getOrderTypes()));
        $orderStatus = $this->faker->randomElement(array_keys(EnergyTradingOrder::getOrderStatuses()));
        $orderSide = $this->faker->randomElement(array_keys(EnergyTradingOrder::getOrderSides()));
        $priceType = $this->faker->randomElement(array_keys(EnergyTradingOrder::getPriceTypes()));
        $executionType = $this->faker->randomElement(array_keys(EnergyTradingOrder::getExecutionTypes()));
        $priority = $this->faker->randomElement(array_keys(EnergyTradingOrder::getPriorities()));
        
        $quantity = $this->faker->randomFloat(2, 10, 1000);
        $price = $this->faker->randomFloat(2, 20, 200);
        $totalValue = $quantity * $price;
        
        $filledQuantity = $this->faker->randomFloat(2, 0, $quantity);
        $remainingQuantity = $quantity - $filledQuantity;
        $filledValue = $filledQuantity * $price;
        $remainingValue = $remainingQuantity * $price;
        
        $validFrom = $this->faker->dateTimeBetween('-1 month', 'now');
        $validUntil = $this->faker->optional(0.7)->dateTimeBetween($validFrom, '+6 months');
        $executionTime = $this->faker->optional(0.3)->dateTimeBetween($validFrom, $validUntil);
        $expiryTime = $this->faker->optional(0.2)->dateTimeBetween($validFrom, '+1 year');
        
        $priceAdjustment = $this->faker->optional(0.4)->randomFloat(2, -20, 20);
        
        return [
            'order_number' => 'ORDER-' . $this->faker->unique()->numberBetween(100000, 999999),
            'order_type' => $orderType,
            'order_status' => $orderStatus,
            'order_side' => $orderSide,
            'trader_id' => User::factory(),
            'pool_id' => EnergyPool::factory(),
            'counterparty_id' => $this->faker->optional(0.6)->randomElement([User::factory(), null]),
            'quantity_mwh' => $quantity,
            'filled_quantity_mwh' => $filledQuantity,
            'remaining_quantity_mwh' => $remainingQuantity,
            'price_per_mwh' => $price,
            'total_value' => $totalValue,
            'filled_value' => $filledValue,
            'remaining_value' => $remainingValue,
            'price_type' => $priceType,
            'price_index' => $this->faker->optional(0.3)->randomElement(['SPOT', 'FUTURES', 'FORWARD', 'INDEX-001', 'INDEX-002']),
            'price_adjustment' => $priceAdjustment,
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'execution_time' => $executionTime,
            'expiry_time' => $expiryTime,
            'execution_type' => $executionType,
            'priority' => $priority,
            'is_negotiable' => $this->faker->boolean(70),
            'negotiation_terms' => $this->faker->optional(0.4)->sentence(),
            'special_conditions' => $this->faker->optional(0.3)->sentence(),
            'delivery_requirements' => $this->faker->optional(0.3)->sentence(),
            'payment_terms' => $this->faker->optional(0.3)->sentence(),
            'order_conditions' => $this->faker->optional(0.5)->randomElements([
                'Must be renewable energy',
                'Peak hours only',
                'Off-peak hours only',
                'Weekdays only',
                'Weekends only',
                'Minimum delivery period: 1 month',
                'Maximum delivery period: 12 months',
                'Grid stability requirements',
                'Environmental compliance required',
                'Quality certification required'
            ], $this->faker->numberBetween(1, 3)),
            'order_restrictions' => $this->faker->optional(0.4)->randomElements([
                'No nuclear energy',
                'No fossil fuels',
                'No hydroelectric during drought',
                'No wind energy during low wind periods',
                'No solar energy during night',
                'Geographic restrictions apply',
                'Time-of-use restrictions',
                'Capacity limitations',
                'Voltage requirements',
                'Frequency requirements'
            ], $this->faker->numberBetween(1, 3)),
            'order_metadata' => [
                'market_segment' => $this->faker->randomElement(['wholesale', 'retail', 'industrial', 'commercial', 'residential']),
                'trading_session' => $this->faker->randomElement(['day_ahead', 'intraday', 'balancing', 'reserve']),
                'grid_operator' => $this->faker->company(),
                'energy_exchange' => $this->faker->randomElement(['OMIE', 'EPEX', 'Nord Pool', 'PJM', 'ERCOT']),
                'clearing_house' => $this->faker->company(),
                'settlement_currency' => $this->faker->randomElement(['EUR', 'USD', 'GBP', 'SEK', 'NOK']),
                'credit_rating' => $this->faker->randomElement(['AAA', 'AA', 'A', 'BBB', 'BB', 'B']),
                'collateral_required' => $this->faker->boolean(30),
                'margin_requirements' => $this->faker->optional(0.4)->randomFloat(2, 5, 25),
                'credit_limit' => $this->faker->optional(0.4)->randomFloat(2, 10000, 1000000)
            ],
            'tags' => $this->faker->randomElements([
                'Peak Hours', 'Off-Peak', 'Renewable', 'Clean Energy', 'Grid Stability',
                'High Priority', 'Negotiable', 'Long Term', 'Short Term', 'Flexible',
                'Baseload', 'Peaking', 'Reserve', 'Balancing', 'Emergency',
                'Green Energy', 'Carbon Neutral', 'Sustainable', 'Eco-Friendly', 'Low Carbon'
            ], $this->faker->numberBetween(2, 6)),
            'created_by' => User::factory(),
            'approved_by' => $this->faker->optional(0.8)->randomElement([User::factory(), null]),
            'approved_at' => $this->faker->optional(0.8)->dateTimeBetween($validFrom, 'now'),
            'executed_by' => $this->faker->optional(0.3)->randomElement([User::factory(), null]),
            'executed_at' => $this->faker->optional(0.3)->dateTimeBetween($validFrom, 'now'),
            'notes' => $this->faker->optional(0.7)->paragraph(),
        ];
    }

    /**
     * Indicate that the order is a buy order.
     */
    public function buy(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_side' => 'buy',
        ]);
    }

    /**
     * Indicate that the order is a sell order.
     */
    public function sell(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_side' => 'sell',
        ]);
    }

    /**
     * Indicate that the order is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_status' => 'pending',
        ]);
    }

    /**
     * Indicate that the order is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_status' => 'active',
        ]);
    }

    /**
     * Indicate that the order is filled.
     */
    public function filled(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_status' => 'filled',
            'filled_quantity_mwh' => $this->faker->randomFloat(2, 10, 1000),
            'remaining_quantity_mwh' => 0,
        ]);
    }

    /**
     * Indicate that the order is partially filled.
     */
    public function partiallyFilled(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_status' => 'partially_filled',
            'filled_quantity_mwh' => $this->faker->randomFloat(2, 10, 500),
            'remaining_quantity_mwh' => $this->faker->randomFloat(2, 10, 500),
        ]);
    }

    /**
     * Indicate that the order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_status' => 'cancelled',
        ]);
    }

    /**
     * Indicate that the order is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_status' => 'rejected',
        ]);
    }

    /**
     * Indicate that the order is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_status' => 'expired',
            'expiry_time' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
        ]);
    }

    /**
     * Indicate that the order is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_status' => 'completed',
        ]);
    }

    /**
     * Indicate that the order is a market order.
     */
    public function market(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_type' => 'market',
        ]);
    }

    /**
     * Indicate that the order is a limit order.
     */
    public function limit(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_type' => 'limit',
        ]);
    }

    /**
     * Indicate that the order is a stop order.
     */
    public function stop(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_type' => 'stop',
        ]);
    }

    /**
     * Indicate that the order is a stop limit order.
     */
    public function stopLimit(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_type' => 'stop_limit',
        ]);
    }

    /**
     * Indicate that the order has fixed pricing.
     */
    public function fixedPrice(): static
    {
        return $this->state(fn (array $attributes) => [
            'price_type' => 'fixed',
        ]);
    }

    /**
     * Indicate that the order has floating pricing.
     */
    public function floatingPrice(): static
    {
        return $this->state(fn (array $attributes) => [
            'price_type' => 'floating',
        ]);
    }

    /**
     * Indicate that the order has indexed pricing.
     */
    public function indexedPrice(): static
    {
        return $this->state(fn (array $attributes) => [
            'price_type' => 'indexed',
        ]);
    }

    /**
     * Indicate that the order has immediate execution.
     */
    public function immediateExecution(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_type' => 'immediate',
        ]);
    }

    /**
     * Indicate that the order is good till cancelled.
     */
    public function goodTillCancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_type' => 'good_till_cancelled',
        ]);
    }

    /**
     * Indicate that the order is good till date.
     */
    public function goodTillDate(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_type' => 'good_till_date',
            'expiry_time' => $this->faker->dateTimeBetween('+1 month', '+1 year'),
        ]);
    }

    /**
     * Indicate that the order is fill or kill.
     */
    public function fillOrKill(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_type' => 'fill_or_kill',
        ]);
    }

    /**
     * Indicate that the order is all or nothing.
     */
    public function allOrNothing(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_type' => 'all_or_nothing',
        ]);
    }

    /**
     * Indicate that the order has high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    /**
     * Indicate that the order has urgent priority.
     */
    public function urgentPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
        ]);
    }

    /**
     * Indicate that the order has critical priority.
     */
    public function criticalPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'critical',
        ]);
    }

    /**
     * Indicate that the order is negotiable.
     */
    public function negotiable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_negotiable' => true,
        ]);
    }

    /**
     * Indicate that the order is not negotiable.
     */
    public function notNegotiable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_negotiable' => false,
        ]);
    }

    /**
     * Indicate that the order is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_by' => User::factory(),
            'approved_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the order is pending approval.
     */
    public function pendingApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    /**
     * Indicate that the order is executed.
     */
    public function executed(): static
    {
        return $this->state(fn (array $attributes) => [
            'executed_by' => User::factory(),
            'executed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the order is not executed.
     */
    public function notExecuted(): static
    {
        return $this->state(fn (array $attributes) => [
            'executed_by' => null,
            'executed_at' => null,
        ]);
    }

    /**
     * Indicate that the order has low quantity.
     */
    public function lowQuantity(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_mwh' => $this->faker->randomFloat(2, 1, 50),
        ]);
    }

    /**
     * Indicate that the order has medium quantity.
     */
    public function mediumQuantity(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_mwh' => $this->faker->randomFloat(2, 50, 200),
        ]);
    }

    /**
     * Indicate that the order has high quantity.
     */
    public function highQuantity(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_mwh' => $this->faker->randomFloat(2, 200, 1000),
        ]);
    }

    /**
     * Indicate that the order has low price.
     */
    public function lowPrice(): static
    {
        return $this->state(fn (array $attributes) => [
            'price_per_mwh' => $this->faker->randomFloat(2, 20, 60),
        ]);
    }

    /**
     * Indicate that the order has medium price.
     */
    public function mediumPrice(): static
    {
        return $this->state(fn (array $attributes) => [
            'price_per_mwh' => $this->faker->randomFloat(2, 60, 120),
        ]);
    }

    /**
     * Indicate that the order has high price.
     */
    public function highPrice(): static
    {
        return $this->state(fn (array $attributes) => [
            'price_per_mwh' => $this->faker->randomFloat(2, 120, 200),
        ]);
    }

    /**
     * Indicate that the order is expiring soon.
     */
    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_time' => $this->faker->dateTimeBetween('now', '+24 hours'),
        ]);
    }

    /**
     * Indicate that the order has a long validity period.
     */
    public function longValidity(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_from' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'valid_until' => $this->faker->dateTimeBetween('+6 months', '+2 years'),
        ]);
    }

    /**
     * Indicate that the order has a short validity period.
     */
    public function shortValidity(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_from' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'valid_until' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
        ]);
    }

    /**
     * Indicate that the order has specific tags.
     */
    public function withTags(array $tags): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => $tags,
        ]);
    }

    /**
     * Indicate that the order has no counterparty.
     */
    public function noCounterparty(): static
    {
        return $this->state(fn (array $attributes) => [
            'counterparty_id' => null,
        ]);
    }

    /**
     * Indicate that the order has no price adjustment.
     */
    public function noPriceAdjustment(): static
    {
        return $this->state(fn (array $attributes) => [
            'price_adjustment' => null,
        ]);
    }

    /**
     * Indicate that the order has no expiry time.
     */
    public function noExpiryTime(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_time' => null,
        ]);
    }

    /**
     * Indicate that the order has no execution time.
     */
    public function noExecutionTime(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_time' => null,
        ]);
    }
}
