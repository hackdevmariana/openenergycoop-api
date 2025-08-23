<?php

namespace Tests\Unit\Models;

use App\Models\EnergyTradingOrder;
use App\Models\User;
use App\Models\EnergyPool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnergyTradingOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes()
    {
        $fillable = [
            'order_number', 'order_type', 'order_status', 'order_side', 'trader_id', 'pool_id',
            'counterparty_id', 'quantity_mwh', 'filled_quantity_mwh', 'remaining_quantity_mwh',
            'price_per_mwh', 'total_value', 'filled_value', 'remaining_value', 'price_type',
            'price_index', 'price_adjustment', 'valid_from', 'valid_until', 'execution_time',
            'expiry_time', 'execution_type', 'priority', 'is_negotiable', 'negotiation_terms',
            'special_conditions', 'delivery_requirements', 'payment_terms', 'order_conditions',
            'order_restrictions', 'order_metadata', 'tags', 'created_by', 'approved_by',
            'approved_at', 'executed_by', 'executed_at', 'notes'
        ];

        $this->assertEquals($fillable, (new EnergyTradingOrder())->getFillable());
    }

    public function test_casts()
    {
        $casts = [
            'quantity_mwh' => 'decimal:2',
            'filled_quantity_mwh' => 'decimal:2',
            'remaining_quantity_mwh' => 'decimal:2',
            'price_per_mwh' => 'decimal:2',
            'total_value' => 'decimal:2',
            'filled_value' => 'decimal:2',
            'remaining_value' => 'decimal:2',
            'price_adjustment' => 'decimal:2',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'execution_time' => 'datetime',
            'expiry_time' => 'datetime',
            'approved_at' => 'datetime',
            'executed_at' => 'datetime',
            'is_negotiable' => 'boolean',
            'order_conditions' => 'array',
            'order_restrictions' => 'array',
            'order_metadata' => 'array',
            'tags' => 'array',
        ];

        $this->assertEquals($casts, (new EnergyTradingOrder())->getCasts());
    }

    public function test_static_enum_methods()
    {
        $this->assertIsArray(EnergyTradingOrder::getOrderTypes());
        $this->assertIsArray(EnergyTradingOrder::getOrderStatuses());
        $this->assertIsArray(EnergyTradingOrder::getOrderSides());
        $this->assertIsArray(EnergyTradingOrder::getPriceTypes());
        $this->assertIsArray(EnergyTradingOrder::getExecutionTypes());
        $this->assertIsArray(EnergyTradingOrder::getPriorities());

        $this->assertArrayHasKey('buy', EnergyTradingOrder::getOrderTypes());
        $this->assertArrayHasKey('pending', EnergyTradingOrder::getOrderStatuses());
        $this->assertArrayHasKey('buy', EnergyTradingOrder::getOrderSides());
        $this->assertArrayHasKey('fixed', EnergyTradingOrder::getPriceTypes());
        $this->assertArrayHasKey('immediate', EnergyTradingOrder::getExecutionTypes());
        $this->assertArrayHasKey('normal', EnergyTradingOrder::getPriorities());
    }

    public function test_relationships()
    {
        $order = EnergyTradingOrder::factory()->create();
        $user = User::factory()->create();
        $pool = EnergyPool::factory()->create();

        $order->update([
            'trader_id' => $user->id,
            'pool_id' => $pool->id,
            'created_by' => $user->id,
            'approved_by' => $user->id,
            'executed_by' => $user->id
        ]);

        $this->assertInstanceOf(User::class, $order->trader);
        $this->assertInstanceOf(EnergyPool::class, $order->pool);
        $this->assertInstanceOf(User::class, $order->createdBy);
        $this->assertInstanceOf(User::class, $order->approvedBy);
        $this->assertInstanceOf(User::class, $order->executedBy);
    }

    public function test_scopes()
    {
        // Scope active
        EnergyTradingOrder::factory()->create(['order_status' => 'active']);
        EnergyTradingOrder::factory()->create(['order_status' => 'cancelled']);

        $this->assertEquals(1, EnergyTradingOrder::active()->count());

        // Scope byOrderType
        EnergyTradingOrder::factory()->create(['order_type' => 'buy']);
        EnergyTradingOrder::factory()->create(['order_type' => 'sell']);

        $this->assertEquals(1, EnergyTradingOrder::byOrderType('buy')->count());

        // Scope byOrderStatus
        EnergyTradingOrder::factory()->create(['order_status' => 'pending']);
        EnergyTradingOrder::factory()->create(['order_status' => 'filled']);

        $this->assertEquals(1, EnergyTradingOrder::byOrderStatus('pending')->count());

        // Scope byOrderSide
        EnergyTradingOrder::factory()->create(['order_side' => 'buy']);
        EnergyTradingOrder::factory()->create(['order_side' => 'sell']);

        $this->assertEquals(1, EnergyTradingOrder::byOrderSide('buy')->count());

        // Scope byTrader
        $user = User::factory()->create();
        EnergyTradingOrder::factory()->create(['trader_id' => $user->id]);
        EnergyTradingOrder::factory()->create(['trader_id' => User::factory()->create()->id]);

        $this->assertEquals(1, EnergyTradingOrder::byTrader($user->id)->count());

        // Scope byPool
        $pool = EnergyPool::factory()->create();
        EnergyTradingOrder::factory()->create(['pool_id' => $pool->id]);
        EnergyTradingOrder::factory()->create(['pool_id' => EnergyPool::factory()->create()->id]);

        $this->assertEquals(1, EnergyTradingOrder::byPool($pool->id)->count());

        // Scope byPriceType
        EnergyTradingOrder::factory()->create(['price_type' => 'fixed']);
        EnergyTradingOrder::factory()->create(['price_type' => 'floating']);

        $this->assertEquals(1, EnergyTradingOrder::byPriceType('fixed')->count());

        // Scope byExecutionType
        EnergyTradingOrder::factory()->create(['execution_type' => 'immediate']);
        EnergyTradingOrder::factory()->create(['execution_type' => 'good_till_cancelled']);

        $this->assertEquals(1, EnergyTradingOrder::byExecutionType('immediate')->count());

        // Scope byPriority
        EnergyTradingOrder::factory()->create(['priority' => 'high']);
        EnergyTradingOrder::factory()->create(['priority' => 'normal']);

        $this->assertEquals(1, EnergyTradingOrder::byPriority('high')->count());

        // Scope pending
        EnergyTradingOrder::factory()->create(['order_status' => 'pending']);
        EnergyTradingOrder::factory()->create(['order_status' => 'active']);

        $this->assertEquals(1, EnergyTradingOrder::pending()->count());

        // Scope filled
        EnergyTradingOrder::factory()->create(['order_status' => 'filled']);
        EnergyTradingOrder::factory()->create(['order_status' => 'active']);

        $this->assertEquals(1, EnergyTradingOrder::filled()->count());

        // Scope cancelled
        EnergyTradingOrder::factory()->create(['order_status' => 'cancelled']);
        EnergyTradingOrder::factory()->create(['order_status' => 'active']);

        $this->assertEquals(1, EnergyTradingOrder::cancelled()->count());

        // Scope buy
        EnergyTradingOrder::factory()->create(['order_side' => 'buy']);
        EnergyTradingOrder::factory()->create(['order_side' => 'sell']);

        $this->assertEquals(1, EnergyTradingOrder::buy()->count());

        // Scope sell
        $this->assertEquals(1, EnergyTradingOrder::sell()->count());

        // Scope highPriority
        EnergyTradingOrder::factory()->create(['priority' => 'high']);
        EnergyTradingOrder::factory()->create(['priority' => 'normal']);

        $this->assertEquals(1, EnergyTradingOrder::highPriority()->count());

        // Scope negotiable
        EnergyTradingOrder::factory()->create(['is_negotiable' => true]);
        EnergyTradingOrder::factory()->create(['is_negotiable' => false]);

        $this->assertEquals(1, EnergyTradingOrder::negotiable()->count());
    }

    public function test_validation_helper_methods()
    {
        $order = EnergyTradingOrder::factory()->create([
            'order_type' => 'buy',
            'order_status' => 'active',
            'order_side' => 'buy',
            'price_type' => 'fixed',
            'execution_type' => 'immediate',
            'priority' => 'normal'
        ]);

        $this->assertTrue($order->isValidOrderType('buy'));
        $this->assertFalse($order->isValidOrderType('invalid'));

        $this->assertTrue($order->isValidStatus('active'));
        $this->assertFalse($order->isValidStatus('invalid'));

        $this->assertTrue($order->isValidEnergyCategory('buy'));
        $this->assertFalse($order->isValidEnergyCategory('invalid'));
    }

    public function test_calculation_methods()
    {
        $order = EnergyTradingOrder::factory()->create([
            'quantity_mwh' => 100.0,
            'filled_quantity_mwh' => 75.0,
            'remaining_quantity_mwh' => 25.0,
            'price_per_mwh' => 50.0,
            'price_adjustment' => 5.0
        ]);

        $this->assertEquals(75.0, $order->getFillPercentage());
        $this->assertEquals(55.0, $order->getAdjustedPrice());
        $this->assertEquals(5500.0, $order->getTotalAdjustedValue());
    }

    public function test_formatting_methods()
    {
        $order = EnergyTradingOrder::factory()->create([
            'quantity_mwh' => 150.75,
            'price_per_mwh' => 87.50,
            'total_value' => 13181.25
        ]);

        $this->assertStringContainsString('150.75', $order->getFormattedQuantity());
        $this->assertStringContainsString('87.50', $order->getFormattedPrice());
        $this->assertStringContainsString('13,181.25', $order->getFormattedTotalValue());
    }

    public function test_badge_classes()
    {
        $order = EnergyTradingOrder::factory()->create([
            'order_status' => 'active',
            'order_type' => 'buy',
            'order_side' => 'buy',
            'price_type' => 'fixed',
            'execution_type' => 'immediate',
            'priority' => 'high'
        ]);

        $this->assertStringContainsString('green', $order->getOrderStatusBadgeClass());
        $this->assertStringContainsString('green', $order->getOrderTypeBadgeClass());
        $this->assertStringContainsString('green', $order->getOrderSideBadgeClass());
        $this->assertStringContainsString('blue', $order->getPriceTypeBadgeClass());
        $this->assertStringContainsString('blue', $order->getExecutionTypeBadgeClass());
        $this->assertStringContainsString('yellow', $order->getPriorityBadgeClass());
    }

    public function test_boolean_status_checks()
    {
        $order = EnergyTradingOrder::factory()->create([
            'order_type' => 'buy',
            'order_side' => 'buy',
            'order_status' => 'active',
            'price_type' => 'fixed',
            'execution_type' => 'immediate',
            'priority' => 'high',
            'is_negotiable' => true
        ]);

        $this->assertTrue($order->isBuy());
        $this->assertFalse($order->isSell());
        $this->assertTrue($order->isActive());
        $this->assertFalse($order->isFilled());
        $this->assertTrue($order->isFixedPrice());
        $this->assertTrue($order->isImmediateExecution());
        $this->assertTrue($order->isHighPriority());
        $this->assertTrue($order->isNegotiable());
    }

    public function test_can_be_cancelled()
    {
        $order = EnergyTradingOrder::factory()->create(['order_status' => 'pending']);
        $this->assertTrue($order->canBeCancelled());

        $order = EnergyTradingOrder::factory()->create(['order_status' => 'filled']);
        $this->assertFalse($order->canBeCancelled());
    }

    public function test_can_be_modified()
    {
        $order = EnergyTradingOrder::factory()->create(['order_status' => 'pending']);
        $this->assertTrue($order->canBeModified());

        $order = EnergyTradingOrder::factory()->create(['order_status' => 'active']);
        $this->assertFalse($order->canBeModified());
    }

    public function test_is_expired()
    {
        $order = EnergyTradingOrder::factory()->create([
            'expiry_time' => now()->subDay()
        ]);
        $this->assertTrue($order->isExpired());

        $order = EnergyTradingOrder::factory()->create([
            'expiry_time' => now()->addDay()
        ]);
        $this->assertFalse($order->isExpired());
    }

    public function test_is_expiring_soon()
    {
        $order = EnergyTradingOrder::factory()->create([
            'expiry_time' => now()->addHours(12)
        ]);
        $this->assertTrue($order->isExpiringSoon(24));

        $order = EnergyTradingOrder::factory()->create([
            'expiry_time' => now()->addDays(2)
        ]);
        $this->assertFalse($order->isExpiringSoon(24));
    }

    public function test_profit_loss_calculations()
    {
        $buyOrder = EnergyTradingOrder::factory()->create([
            'order_side' => 'buy',
            'price_per_mwh' => 50.0,
            'filled_quantity_mwh' => 100.0
        ]);

        $sellOrder = EnergyTradingOrder::factory()->create([
            'order_side' => 'sell',
            'price_per_mwh' => 60.0,
            'filled_quantity_mwh' => 100.0
        ]);

        $this->assertEquals(1000.0, $buyOrder->getProfitLoss(60.0)); // Profit when price goes up
        $this->assertEquals(1000.0, $sellOrder->getProfitLoss(50.0)); // Profit when price goes down
    }

    public function test_summary_methods()
    {
        EnergyTradingOrder::factory()->count(5)->create(['order_status' => 'active']);
        EnergyTradingOrder::factory()->count(3)->create(['order_status' => 'cancelled']);

        // These methods don't exist in the model, but we can test the scopes
        $this->assertEquals(5, EnergyTradingOrder::active()->count());
        $this->assertEquals(3, EnergyTradingOrder::cancelled()->count());
        $this->assertEquals(8, EnergyTradingOrder::count());
    }
}
