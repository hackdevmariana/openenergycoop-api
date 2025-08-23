<?php

namespace Tests\Feature\Api\V1;

use App\Models\EnergyTradingOrder;
use App\Models\User;
use App\Models\EnergyPool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EnergyTradingOrderControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_returns_paginated_trading_orders()
    {
        EnergyTradingOrder::factory()->count(15)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-trading-orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'order_number', 'order_type', 'order_status', 'order_side',
                        'quantity_mwh', 'price_per_mwh', 'fill_percentage'
                    ]
                ],
                'meta' => ['current_page', 'total', 'per_page', 'last_page']
            ]);

        $this->assertEquals(15, $response->json('meta.total'));
    }

    public function test_index_with_filters()
    {
        EnergyTradingOrder::factory()->create(['order_type' => 'buy', 'order_status' => 'active']);
        EnergyTradingOrder::factory()->create(['order_type' => 'sell', 'order_status' => 'pending']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-trading-orders?order_type=buy&order_status=active');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_index_with_search()
    {
        EnergyTradingOrder::factory()->create(['order_number' => 'ORDER-001']);
        EnergyTradingOrder::factory()->create(['order_number' => 'ORDER-002']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-trading-orders?search=ORDER-001');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_index_with_sorting()
    {
        EnergyTradingOrder::factory()->create(['order_number' => 'B-ORDER']);
        EnergyTradingOrder::factory()->create(['order_number' => 'A-ORDER']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-trading-orders?sort_by=order_number&sort_direction=asc');

        $response->assertStatus(200);
        $this->assertEquals('A-ORDER', $response->json('data.0.order_number'));
    }

    public function test_store_creates_new_trading_order()
    {
        $pool = EnergyPool::factory()->create();
        $data = [
            'order_number' => 'ORDER-001',
            'order_type' => 'buy',
            'order_status' => 'pending',
            'order_side' => 'buy',
            'trader_id' => $this->user->id,
            'pool_id' => $pool->id,
            'quantity_mwh' => 100.0,
            'price_per_mwh' => 75.50,
            'price_type' => 'fixed',
            'execution_type' => 'immediate',
            'priority' => 'normal',
            'valid_from' => now()->subDay(),
            'is_negotiable' => true
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/energy-trading-orders', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'order_number', 'order_type', 'order_status', 'order_side',
                    'quantity_mwh', 'price_per_mwh'
                ]
            ]);

        $this->assertDatabaseHas('energy_trading_orders', [
            'order_number' => 'ORDER-001',
            'order_type' => 'buy'
        ]);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/energy-trading-orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_number', 'order_type', 'order_status', 'order_side', 'trader_id', 'pool_id', 'quantity_mwh', 'price_per_mwh', 'price_type', 'execution_type', 'priority', 'valid_from']);
    }

    public function test_store_validates_unique_order_number()
    {
        EnergyTradingOrder::factory()->create(['order_number' => 'ORDER-001']);
        $pool = EnergyPool::factory()->create();

        $data = [
            'order_number' => 'ORDER-001',
            'order_type' => 'buy',
            'order_status' => 'pending',
            'order_side' => 'buy',
            'trader_id' => $this->user->id,
            'pool_id' => $pool->id,
            'quantity_mwh' => 100.0,
            'price_per_mwh' => 75.50,
            'price_type' => 'fixed',
            'execution_type' => 'immediate',
            'priority' => 'normal',
            'valid_from' => now()->subDay()
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/energy-trading-orders', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_number']);
    }

    public function test_show_returns_trading_order()
    {
        $order = EnergyTradingOrder::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/energy-trading-orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'order_number', 'order_type', 'order_status', 'order_side',
                    'quantity_mwh', 'price_per_mwh', 'fill_percentage'
                ]
            ]);
    }

    public function test_show_returns_404_for_nonexistent_order()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-trading-orders/999');

        $response->assertStatus(404);
    }

    public function test_update_modifies_trading_order()
    {
        $order = EnergyTradingOrder::factory()->create();
        $updateData = ['notes' => 'Updated order details'];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/energy-trading-orders/{$order->id}", $updateData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('energy_trading_orders', [
            'id' => $order->id,
            'notes' => 'Updated order details'
        ]);
    }

    public function test_update_validates_unique_order_number()
    {
        $order1 = EnergyTradingOrder::factory()->create(['order_number' => 'ORDER-001']);
        $order2 = EnergyTradingOrder::factory()->create(['order_number' => 'ORDER-002']);

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/energy-trading-orders/{$order2->id}", ['order_number' => 'ORDER-001']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_number']);
    }

    public function test_destroy_deletes_trading_order()
    {
        $order = EnergyTradingOrder::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/energy-trading-orders/{$order->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('energy_trading_orders', ['id' => $order->id]);
    }

    public function test_statistics_returns_order_statistics()
    {
        EnergyTradingOrder::factory()->count(5)->create(['order_status' => 'active']);
        EnergyTradingOrder::factory()->count(3)->create(['order_status' => 'cancelled']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-trading-orders/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_orders', 'active_orders', 'filled_orders', 'cancelled_orders',
                'by_type', 'by_status', 'by_side'
            ]);

        $this->assertEquals(8, $response->json('total_orders'));
        $this->assertEquals(5, $response->json('active_orders'));
    }

    public function test_types_returns_order_types()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-trading-orders/types');

        $response->assertStatus(200)
            ->assertJsonStructure(['buy', 'sell', 'bid', 'ask']);
    }

    public function test_statuses_returns_order_statuses()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-trading-orders/statuses');

        $response->assertStatus(200)
            ->assertJsonStructure(['pending', 'active', 'filled', 'cancelled']);
    }

    public function test_sides_returns_order_sides()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-trading-orders/sides');

        $response->assertStatus(200)
            ->assertJsonStructure(['buy', 'sell']);
    }

    public function test_price_types_returns_price_types()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-trading-orders/price-types');

        $response->assertStatus(200)
            ->assertJsonStructure(['fixed', 'floating', 'indexed']);
    }

    public function test_execution_types_returns_execution_types()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-trading-orders/execution-types');

        $response->assertStatus(200)
            ->assertJsonStructure(['immediate', 'good_till_cancelled', 'fill_or_kill']);
    }

    public function test_priorities_returns_priorities()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-trading-orders/priorities');

        $response->assertStatus(200)
            ->assertJsonStructure(['low', 'normal', 'high', 'urgent']);
    }

    public function test_update_status_updates_order_status()
    {
        $order = EnergyTradingOrder::factory()->create(['order_status' => 'pending']);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/energy-trading-orders/{$order->id}/update-status", [
                'order_status' => 'active'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('energy_trading_orders', [
            'id' => $order->id,
            'order_status' => 'active'
        ]);
    }

    public function test_update_status_validates_status()
    {
        $order = EnergyTradingOrder::factory()->create();

        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/energy-trading-orders/{$order->id}/update-status", [
                'order_status' => 'invalid_status'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_status']);
    }

    public function test_cancel_cancels_order()
    {
        $order = EnergyTradingOrder::factory()->create(['order_status' => 'pending']);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/energy-trading-orders/{$order->id}/cancel", [
                'notes' => 'Order cancelled by user'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('energy_trading_orders', [
            'id' => $order->id,
            'order_status' => 'cancelled'
        ]);
    }

    public function test_cancel_fails_for_non_cancellable_order()
    {
        $order = EnergyTradingOrder::factory()->create(['order_status' => 'filled']);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/energy-trading-orders/{$order->id}/cancel");

        $response->assertStatus(400);
    }

    public function test_duplicate_creates_copy_of_order()
    {
        $order = EnergyTradingOrder::factory()->create([
            'order_number' => 'ORDER-001',
            'order_status' => 'active'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/energy-trading-orders/{$order->id}/duplicate", [
                'quantity_mwh' => 200.0,
                'price_per_mwh' => 80.0
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('energy_trading_orders', [
            'order_status' => 'pending',
            'quantity_mwh' => 200.0,
            'price_per_mwh' => 80.0
        ]);
    }

    public function test_active_returns_active_orders()
    {
        EnergyTradingOrder::factory()->create(['order_status' => 'active']);
        EnergyTradingOrder::factory()->create(['order_status' => 'cancelled']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-trading-orders/active');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_pending_returns_pending_orders()
    {
        EnergyTradingOrder::factory()->create(['order_status' => 'pending']);
        EnergyTradingOrder::factory()->create(['order_status' => 'active']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-trading-orders/pending');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_filled_returns_filled_orders()
    {
        EnergyTradingOrder::factory()->create(['order_status' => 'filled']);
        EnergyTradingOrder::factory()->create(['order_status' => 'active']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-trading-orders/filled');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_by_type_returns_orders_by_type()
    {
        EnergyTradingOrder::factory()->create(['order_type' => 'buy']);
        EnergyTradingOrder::factory()->create(['order_type' => 'sell']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-trading-orders/by-type/buy');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_by_side_returns_orders_by_side()
    {
        EnergyTradingOrder::factory()->create(['order_side' => 'buy']);
        EnergyTradingOrder::factory()->create(['order_side' => 'sell']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-trading-orders/by-side/buy');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_by_trader_returns_orders_by_trader()
    {
        $user = User::factory()->create();
        EnergyTradingOrder::factory()->create(['trader_id' => $user->id]);
        EnergyTradingOrder::factory()->create(['trader_id' => User::factory()->create()->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/energy-trading-orders/by-trader/{$user->id}");

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_by_pool_returns_orders_by_pool()
    {
        $pool = EnergyPool::factory()->create();
        EnergyTradingOrder::factory()->create(['pool_id' => $pool->id]);
        EnergyTradingOrder::factory()->create(['pool_id' => EnergyPool::factory()->create()->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/energy-trading-orders/by-pool/{$pool->id}");

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_high_priority_returns_high_priority_orders()
    {
        EnergyTradingOrder::factory()->create(['priority' => 'high']);
        EnergyTradingOrder::factory()->create(['priority' => 'normal']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-trading-orders/high-priority');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_negotiable_returns_negotiable_orders()
    {
        EnergyTradingOrder::factory()->create(['is_negotiable' => true]);
        EnergyTradingOrder::factory()->create(['is_negotiable' => false]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-trading-orders/negotiable');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_expiring_returns_expiring_orders()
    {
        EnergyTradingOrder::factory()->create(['expiry_time' => now()->subDay()]);
        EnergyTradingOrder::factory()->create(['expiry_time' => now()->addDay()]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-trading-orders/expiring');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_pagination_with_limit()
    {
        EnergyTradingOrder::factory()->count(25)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-trading-orders?per_page=10');

        $response->assertStatus(200);
        $this->assertEquals(10, $response->json('meta.per_page'));
        $this->assertEquals(25, $response->json('meta.total'));
    }

    public function test_requires_authentication()
    {
        $response = $this->getJson('/api/v1/energy-trading-orders');
        $response->assertStatus(401);
    }

    public function test_logs_activity_on_create()
    {
        $pool = EnergyPool::factory()->create();
        $data = [
            'order_number' => 'ORDER-001',
            'order_type' => 'buy',
            'order_status' => 'pending',
            'order_side' => 'buy',
            'trader_id' => $this->user->id,
            'pool_id' => $pool->id,
            'quantity_mwh' => 100.0,
            'price_per_mwh' => 75.50,
            'price_type' => 'fixed',
            'execution_type' => 'immediate',
            'priority' => 'normal',
            'valid_from' => now()->subDay()
        ];

        $this->actingAs($this->user)
            ->postJson('/api/v1/energy-trading-orders', $data);

        // Verificar que se registró la actividad (si tienes un sistema de logging)
        // $this->assertDatabaseHas('activity_logs', [...]);
    }
}
