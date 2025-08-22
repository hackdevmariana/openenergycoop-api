<?php

namespace Tests\Feature\Api\V1;

use App\Models\SaleOrder;
use App\Models\Organization;
use App\Models\User;
use App\Models\CustomerProfile;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SaleOrderControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Organization $organization;
    protected CustomerProfile $customer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create();
        $this->customer = CustomerProfile::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $this->product = Product::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_list_sale_orders()
    {
        SaleOrder::factory()->count(5)->create([
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->getJson('/api/v1/sale-orders');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'order_number',
                            'customer_name',
                            'customer_email',
                            'status',
                            'payment_status',
                            'total',
                            'created_at',
                        ]
                    ],
                    'meta' => [
                        'current_page',
                        'total',
                        'per_page',
                    ]
                ]);

        $this->assertCount(5, $response->json('data'));
    }

    /** @test */
    public function it_can_filter_sale_orders_by_status()
    {
        SaleOrder::factory()->create([
            'status' => 'pending',
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);
        
        SaleOrder::factory()->create([
            'status' => 'processing',
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->getJson('/api/v1/sale-orders?status=pending');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('pending', $response->json('data.0.status'));
    }

    /** @test */
    public function it_can_filter_sale_orders_by_payment_status()
    {
        SaleOrder::factory()->create([
            'payment_status' => 'paid',
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);
        
        SaleOrder::factory()->create([
            'payment_status' => 'pending',
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->getJson('/api/v1/sale-orders?payment_status=paid');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('paid', $response->json('data.0.payment_status'));
    }

    /** @test */
    public function it_can_search_sale_orders()
    {
        SaleOrder::factory()->create([
            'order_number' => 'ORD-001',
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);
        
        SaleOrder::factory()->create([
            'order_number' => 'ORD-002',
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->getJson('/api/v1/sale-orders?search=ORD-001');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('ORD-001', $response->json('data.0.order_number'));
    }

    /** @test */
    public function it_can_create_sale_order()
    {
        $saleOrderData = [
            'customer_id' => $this->customer->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 100.00,
            'tax_amount' => 10.00,
            'shipping_amount' => 15.00,
            'discount_amount' => 20.00,
            'total' => 105.00,
            'currency' => 'USD',
            'notes' => 'Test order notes',
            'is_urgent' => false,
            'shipping_method' => 'standard',
            'organization_id' => $this->organization->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => 'Test Product',
                    'quantity' => 2,
                    'unit_price' => 50.00,
                    'total_price' => 100.00,
                ]
            ],
        ];

        $response = $this->postJson('/api/v1/sale-orders', $saleOrderData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'order_number',
                        'customer_name',
                        'status',
                        'total',
                    ]
                ]);

        $this->assertDatabaseHas('sale_orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'status' => 'pending',
            'total' => 105.00,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_sale_order()
    {
        $response = $this->postJson('/api/v1/sale-orders', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'customer_id',
                    'customer_name',
                    'customer_email',
                    'status',
                    'payment_status',
                    'total',
                    'items',
                ]);
    }

    /** @test */
    public function it_validates_order_status_enum()
    {
        $saleOrderData = [
            'customer_id' => $this->customer->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'status' => 'invalid_status',
            'payment_status' => 'pending',
            'total' => 100.00,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => 'Test Product',
                    'quantity' => 1,
                    'unit_price' => 100.00,
                    'total_price' => 100.00,
                ]
            ],
        ];

        $response = $this->postJson('/api/v1/sale-orders', $saleOrderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function it_validates_payment_status_enum()
    {
        $saleOrderData = [
            'customer_id' => $this->customer->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'status' => 'pending',
            'payment_status' => 'invalid_payment_status',
            'total' => 100.00,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => 'Test Product',
                    'quantity' => 1,
                    'unit_price' => 100.00,
                    'total_price' => 100.00,
                ]
            ],
        ];

        $response = $this->postJson('/api/v1/sale-orders', $saleOrderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['payment_status']);
    }

    /** @test */
    public function it_validates_total_is_positive()
    {
        $saleOrderData = [
            'customer_id' => $this->customer->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'status' => 'pending',
            'payment_status' => 'pending',
            'total' => -100.00,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => 'Test Product',
                    'quantity' => 1,
                    'unit_price' => 100.00,
                    'total_price' => 100.00,
                ]
            ],
        ];

        $response = $this->postJson('/api/v1/sale-orders', $saleOrderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['total']);
    }

    /** @test */
    public function it_can_show_sale_order()
    {
        $saleOrder = SaleOrder::factory()->create([
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->getJson("/api/v1/sale-orders/{$saleOrder->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'order_number',
                        'customer_name',
                        'status',
                        'total',
                        'created_at',
                        'links',
                    ]
                ]);

        $this->assertEquals($saleOrder->id, $response->json('data.id'));
    }

    /** @test */
    public function it_returns_404_for_nonexistent_sale_order()
    {
        $response = $this->getJson('/api/v1/sale-orders/999999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_sale_order()
    {
        $saleOrder = SaleOrder::factory()->create([
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $updateData = [
            'status' => 'processing',
            'notes' => 'Order is being processed',
        ];

        $response = $this->putJson("/api/v1/sale-orders/{$saleOrder->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'status',
                        'notes',
                    ]
                ]);

        $this->assertDatabaseHas('sale_orders', [
            'id' => $saleOrder->id,
            'status' => 'processing',
        ]);
    }

    /** @test */
    public function it_can_delete_sale_order()
    {
        $saleOrder = SaleOrder::factory()->create([
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->deleteJson("/api/v1/sale-orders/{$saleOrder->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Sale order deleted successfully'
                ]);

        $this->assertDatabaseMissing('sale_orders', [
            'id' => $saleOrder->id,
        ]);
    }

    /** @test */
    public function it_can_get_pending_sale_orders()
    {
        SaleOrder::factory()->create([
            'status' => 'pending',
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);
        
        SaleOrder::factory()->create([
            'status' => 'processing',
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->getJson('/api/v1/sale-orders/pending');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('pending', $response->json('data.0.status'));
    }

    /** @test */
    public function it_can_get_urgent_sale_orders()
    {
        SaleOrder::factory()->create([
            'is_urgent' => true,
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);
        
        SaleOrder::factory()->create([
            'is_urgent' => false,
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->getJson('/api/v1/sale-orders/urgent');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertTrue($response->json('data.0.is_urgent'));
    }

    /** @test */
    public function it_can_get_sale_orders_by_status()
    {
        SaleOrder::factory()->create([
            'status' => 'processing',
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);
        
        SaleOrder::factory()->create([
            'status' => 'shipped',
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->getJson('/api/v1/sale-orders/by-status/processing');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('processing', $response->json('data.0.status'));
    }

    /** @test */
    public function it_validates_status_parameter_for_by_status_endpoint()
    {
        $response = $this->getJson('/api/v1/sale-orders/by-status/invalid_status');

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_get_sale_orders_by_customer()
    {
        $otherCustomer = CustomerProfile::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        
        SaleOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'organization_id' => $this->organization->id,
        ]);
        
        SaleOrder::factory()->create([
            'customer_id' => $otherCustomer->id,
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/sale-orders/by-customer?customer_id=' . $this->customer->id);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($this->customer->id, $response->json('data.0.customer_id'));
    }

    /** @test */
    public function it_validates_customer_id_for_by_customer_endpoint()
    {
        $response = $this->getJson('/api/v1/sale-orders/by-customer');

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_get_sale_order_statistics()
    {
        SaleOrder::factory()->count(3)->create([
            'status' => 'pending',
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);
        
        SaleOrder::factory()->count(2)->create([
            'status' => 'delivered',
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->getJson('/api/v1/sale-orders/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_orders',
                        'pending_orders',
                        'processing_orders',
                        'shipped_orders',
                        'delivered_orders',
                        'cancelled_orders',
                        'total_revenue',
                        'average_order_value',
                        'orders_by_status',
                        'revenue_by_status'
                    ]
                ]);

        $this->assertEquals(5, $response->json('data.total_orders'));
        $this->assertEquals(3, $response->json('data.pending_orders'));
        $this->assertEquals(2, $response->json('data.delivered_orders'));
    }

    /** @test */
    public function it_can_update_sale_order_status()
    {
        $saleOrder = SaleOrder::factory()->create([
            'status' => 'pending',
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->postJson("/api/v1/sale-orders/{$saleOrder->id}/update-status", [
            'status' => 'processing',
            'notes' => 'Order is being processed',
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Sale order status updated successfully'
                ]);

        $this->assertDatabaseHas('sale_orders', [
            'id' => $saleOrder->id,
            'status' => 'processing',
        ]);
    }

    /** @test */
    public function it_validates_status_for_update_status_endpoint()
    {
        $saleOrder = SaleOrder::factory()->create([
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->postJson("/api/v1/sale-orders/{$saleOrder->id}/update-status", [
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_update_payment_status()
    {
        $saleOrder = SaleOrder::factory()->create([
            'payment_status' => 'pending',
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->postJson("/api/v1/sale-orders/{$saleOrder->id}/update-payment-status", [
            'payment_status' => 'paid',
            'payment_method' => 'credit_card',
            'notes' => 'Payment received via credit card',
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Payment status updated successfully'
                ]);

        $this->assertDatabaseHas('sale_orders', [
            'id' => $saleOrder->id,
            'payment_status' => 'paid',
            'payment_method' => 'credit_card',
        ]);
    }

    /** @test */
    public function it_validates_payment_status_for_update_payment_status_endpoint()
    {
        $saleOrder = SaleOrder::factory()->create([
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->postJson("/api/v1/sale-orders/{$saleOrder->id}/update-payment-status", [
            'payment_status' => 'invalid_payment_status',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_update_sale_order_urgency()
    {
        $saleOrder = SaleOrder::factory()->create([
            'is_urgent' => false,
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->postJson("/api/v1/sale-orders/{$saleOrder->id}/update-urgency", [
            'is_urgent' => true,
            'urgency_reason' => 'Customer requested express processing',
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Sale order urgency updated successfully'
                ]);

        $this->assertDatabaseHas('sale_orders', [
            'id' => $saleOrder->id,
            'is_urgent' => true,
        ]);
    }

    /** @test */
    public function it_validates_urgency_for_update_urgency_endpoint()
    {
        $saleOrder = SaleOrder::factory()->create([
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->postJson("/api/v1/sale-orders/{$saleOrder->id}/update-urgency", [
            'is_urgent' => 'invalid_boolean',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_duplicate_sale_order()
    {
        $saleOrder = SaleOrder::factory()->create([
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->postJson("/api/v1/sale-orders/{$saleOrder->id}/duplicate", [
            'customer_name' => 'Jane Smith',
            'customer_email' => 'jane@example.com',
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Sale order duplicated successfully'
                ]);

        $this->assertDatabaseHas('sale_orders', [
            'customer_name' => 'Jane Smith',
            'customer_email' => 'jane@example.com',
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
    }

    /** @test */
    public function it_validates_customer_for_duplicate_endpoint()
    {
        $saleOrder = SaleOrder::factory()->create([
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->postJson("/api/v1/sale-orders/{$saleOrder->id}/duplicate", [
            'customer_id' => 999999,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_requires_authentication_for_protected_routes()
    {
        // Clear authentication
        $this->withoutMiddleware();

        $response = $this->getJson('/api/v1/sale-orders');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_filter_by_total_range()
    {
        SaleOrder::factory()->create([
            'total' => 50.0,
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);
        
        SaleOrder::factory()->create([
            'total' => 200.0,
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->getJson('/api/v1/sale-orders?total_min=100&total_max=300');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(200.0, $response->json('data.0.total'));
    }

    /** @test */
    public function it_can_filter_by_urgent_status()
    {
        SaleOrder::factory()->create([
            'is_urgent' => true,
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);
        
        SaleOrder::factory()->create([
            'is_urgent' => false,
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->getJson('/api/v1/sale-orders?is_urgent=true');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertTrue($response->json('data.0.is_urgent'));
    }

    /** @test */
    public function it_can_filter_by_discount_usage()
    {
        SaleOrder::factory()->create([
            'discount_amount' => 20.0,
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);
        
        SaleOrder::factory()->create([
            'discount_amount' => 0.0,
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->getJson('/api/v1/sale-orders?has_discount=true');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertGreaterThan(0, $response->json('data.0.discount_amount'));
    }

    /** @test */
    public function it_can_sort_sale_orders()
    {
        SaleOrder::factory()->create([
            'order_number' => 'ORD-001',
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);
        
        SaleOrder::factory()->create([
            'order_number' => 'ORD-002',
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->getJson('/api/v1/sale-orders?sort_by=order_number&sort_direction=asc');

        $response->assertStatus(200);
        $this->assertEquals('ORD-001', $response->json('data.0.order_number'));
        $this->assertEquals('ORD-002', $response->json('data.1.order_number'));
    }

    /** @test */
    public function it_can_paginate_results()
    {
        SaleOrder::factory()->count(25)->create([
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->getJson('/api/v1/sale-orders?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(25, $response->json('meta.total'));
        $this->assertEquals(3, $response->json('meta.last_page'));
    }

    /** @test */
    public function it_can_filter_by_organization()
    {
        $otherOrganization = Organization::factory()->create();
        
        SaleOrder::factory()->create([
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);
        
        SaleOrder::factory()->create([
            'organization_id' => $otherOrganization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->getJson('/api/v1/sale-orders?organization_id=' . $this->organization->id);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($this->organization->id, $response->json('data.0.organization.id'));
    }

    /** @test */
    public function it_can_filter_by_customer()
    {
        $otherCustomer = CustomerProfile::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        
        SaleOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'organization_id' => $this->organization->id,
        ]);
        
        SaleOrder::factory()->create([
            'customer_id' => $otherCustomer->id,
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/sale-orders?customer_id=' . $this->customer->id);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($this->customer->id, $response->json('data.0.customer_id'));
    }

    /** @test */
    public function it_can_filter_by_date_range()
    {
        $oldOrder = SaleOrder::factory()->create([
            'created_at' => now()->subDays(10),
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);
        
        $newOrder = SaleOrder::factory()->create([
            'created_at' => now()->addDays(5),
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->getJson('/api/v1/sale-orders?created_at_from=' . now()->toDateString());

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($newOrder->id, $response->json('data.0.id'));
    }

    /** @test */
    public function it_handles_complex_search_queries()
    {
        SaleOrder::factory()->create([
            'order_number' => 'ORD-001',
            'status' => 'pending',
            'payment_status' => 'pending',
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);
        
        SaleOrder::factory()->create([
            'order_number' => 'ORD-002',
            'status' => 'processing',
            'payment_status' => 'paid',
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->getJson('/api/v1/sale-orders?search=ORD-001&status=pending&payment_status=pending');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('ORD-001', $response->json('data.0.order_number'));
    }

    /** @test */
    public function it_generates_order_number_automatically()
    {
        $saleOrderData = [
            'customer_id' => $this->customer->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'status' => 'pending',
            'payment_status' => 'pending',
            'total' => 100.00,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => 'Test Product',
                    'quantity' => 1,
                    'unit_price' => 100.00,
                    'total_price' => 100.00,
                ]
            ],
        ];

        $response = $this->postJson('/api/v1/sale-orders', $saleOrderData);

        $response->assertStatus(201);
        $this->assertNotEmpty($response->json('data.order_number'));
        $this->assertStringStartsWith('ORD-', $response->json('data.order_number'));
    }

    /** @test */
    public function it_creates_order_items_when_creating_sale_order()
    {
        $saleOrderData = [
            'customer_id' => $this->customer->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'status' => 'pending',
            'payment_status' => 'pending',
            'total' => 200.00,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => 'Test Product 1',
                    'quantity' => 1,
                    'unit_price' => 100.00,
                    'total_price' => 100.00,
                ],
                [
                    'product_id' => $this->product->id,
                    'product_name' => 'Test Product 2',
                    'quantity' => 1,
                    'unit_price' => 100.00,
                    'total_price' => 100.00,
                ]
            ],
        ];

        $response = $this->postJson('/api/v1/sale-orders', $saleOrderData);

        $response->assertStatus(201);
        $this->assertCount(2, $response->json('data.items'));
    }
}
