<?php

namespace Tests\Feature\Api\V1;

use App\Models\PreSaleOffer;
use App\Models\Organization;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PreSaleOfferControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Organization $organization;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create();
        $this->product = Product::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_list_pre_sale_offers()
    {
        PreSaleOffer::factory()->count(5)->create([
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->getJson('/api/v1/pre-sale-offers');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'type',
                            'discount_percentage',
                            'status',
                            'is_featured',
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
    public function it_can_filter_pre_sale_offers_by_status()
    {
        PreSaleOffer::factory()->create([
            'status' => 'active',
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);
        
        PreSaleOffer::factory()->create([
            'status' => 'inactive',
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->getJson('/api/v1/pre-sale-offers?status=active');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('active', $response->json('data.0.status'));
    }

    /** @test */
    public function it_can_filter_pre_sale_offers_by_type()
    {
        PreSaleOffer::factory()->create([
            'type' => 'discount',
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);
        
        PreSaleOffer::factory()->create([
            'type' => 'fixed_amount',
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->getJson('/api/v1/pre-sale-offers?type=discount');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('discount', $response->json('data.0.type'));
    }

    /** @test */
    public function it_can_search_pre_sale_offers()
    {
        PreSaleOffer::factory()->create([
            'title' => 'Early Bird Special',
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);
        
        PreSaleOffer::factory()->create([
            'title' => 'Holiday Discount',
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->getJson('/api/v1/pre-sale-offers?search=Early Bird');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Early Bird Special', $response->json('data.0.title'));
    }

    /** @test */
    public function it_can_create_pre_sale_offer()
    {
        $preSaleOfferData = [
            'title' => 'Early Bird Special',
            'description' => 'Get 20% off on pre-orders',
            'type' => 'discount',
            'discount_percentage' => 20.0,
            'original_price' => 200.0,
            'price' => 160.0,
            'currency' => 'USD',
            'status' => 'active',
            'is_featured' => true,
            'is_limited_time' => true,
            'start_date' => now()->addDays(1)->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'max_quantity' => 100,
            'min_quantity' => 1,
            'product_id' => $this->product->id,
            'product_name' => 'Premium Widget',
            'terms_conditions' => 'Valid until the end of the month',
            'organization_id' => $this->organization->id,
            'tags' => ['early-bird', 'pre-sale', 'discount'],
            'requirements' => ['pre-order', 'payment-in-advance'],
            'benefits' => ['exclusive-access', 'limited-edition'],
            'restrictions' => ['one-per-customer', 'non-transferable'],
        ];

        $response = $this->postJson('/api/v1/pre-sale-offers', $preSaleOfferData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'title',
                        'type',
                        'discount_percentage',
                        'status',
                    ]
                ]);

        $this->assertDatabaseHas('pre_sale_offers', [
            'title' => 'Early Bird Special',
            'type' => 'discount',
            'discount_percentage' => 20.0,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_pre_sale_offer()
    {
        $response = $this->postJson('/api/v1/pre-sale-offers', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'title',
                    'type',
                    'status',
                ]);
    }

    /** @test */
    public function it_validates_offer_type_enum()
    {
        $preSaleOfferData = [
            'title' => 'Test Offer',
            'type' => 'invalid_type',
            'status' => 'active',
        ];

        $response = $this->postJson('/api/v1/pre-sale-offers', $preSaleOfferData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['type']);
    }

    /** @test */
    public function it_validates_status_enum()
    {
        $preSaleOfferData = [
            'title' => 'Test Offer',
            'type' => 'discount',
            'status' => 'invalid_status',
        ];

        $response = $this->postJson('/api/v1/pre-sale-offers', $preSaleOfferData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function it_validates_discount_percentage_range()
    {
        $preSaleOfferData = [
            'title' => 'Test Offer',
            'type' => 'discount',
            'status' => 'active',
            'discount_percentage' => 150.0, // Invalid: > 100
        ];

        $response = $this->postJson('/api/v1/pre-sale-offers', $preSaleOfferData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['discount_percentage']);
    }

    /** @test */
    public function it_validates_date_range()
    {
        $preSaleOfferData = [
            'title' => 'Test Offer',
            'type' => 'discount',
            'status' => 'active',
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(), // Invalid: before start_date
        ];

        $response = $this->postJson('/api/v1/pre-sale-offers', $preSaleOfferData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['end_date']);
    }

    /** @test */
    public function it_can_show_pre_sale_offer()
    {
        $preSaleOffer = PreSaleOffer::factory()->create([
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->getJson("/api/v1/pre-sale-offers/{$preSaleOffer->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'title',
                        'type',
                        'status',
                        'created_at',
                        'links',
                    ]
                ]);

        $this->assertEquals($preSaleOffer->id, $response->json('data.id'));
    }

    /** @test */
    public function it_returns_404_for_nonexistent_pre_sale_offer()
    {
        $response = $this->getJson('/api/v1/pre-sale-offers/999999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_pre_sale_offer()
    {
        $preSaleOffer = PreSaleOffer::factory()->create([
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $updateData = [
            'title' => 'Updated Early Bird Special',
            'discount_percentage' => 25.0,
            'status' => 'inactive',
        ];

        $response = $this->putJson("/api/v1/pre-sale-offers/{$preSaleOffer->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'title',
                        'discount_percentage',
                        'status',
                    ]
                ]);

        $this->assertDatabaseHas('pre_sale_offers', [
            'id' => $preSaleOffer->id,
            'title' => 'Updated Early Bird Special',
            'discount_percentage' => 25.0,
            'status' => 'inactive',
        ]);
    }

    /** @test */
    public function it_can_delete_pre_sale_offer()
    {
        $preSaleOffer = PreSaleOffer::factory()->create([
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->deleteJson("/api/v1/pre-sale-offers/{$preSaleOffer->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Pre-sale offer deleted successfully'
                ]);

        $this->assertDatabaseMissing('pre_sale_offers', [
            'id' => $preSaleOffer->id,
        ]);
    }

    /** @test */
    public function it_can_get_active_pre_sale_offers()
    {
        PreSaleOffer::factory()->create([
            'status' => 'active',
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);
        
        PreSaleOffer::factory()->create([
            'status' => 'inactive',
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->getJson('/api/v1/pre-sale-offers/active');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('active', $response->json('data.0.status'));
    }

    /** @test */
    public function it_can_get_featured_pre_sale_offers()
    {
        PreSaleOffer::factory()->create([
            'is_featured' => true,
            'status' => 'active',
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);
        
        PreSaleOffer::factory()->create([
            'is_featured' => false,
            'status' => 'active',
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->getJson('/api/v1/pre-sale-offers/featured');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertTrue($response->json('data.0.is_featured'));
    }

    /** @test */
    public function it_can_get_pre_sale_offers_by_type()
    {
        PreSaleOffer::factory()->create([
            'type' => 'discount',
            'status' => 'active',
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);
        
        PreSaleOffer::factory()->create([
            'type' => 'fixed_amount',
            'status' => 'active',
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->getJson('/api/v1/pre-sale-offers/by-type/discount');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('discount', $response->json('data.0.type'));
    }

    /** @test */
    public function it_validates_type_parameter_for_by_type_endpoint()
    {
        $response = $this->getJson('/api/v1/pre-sale-offers/by-type/invalid_type');

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_get_pre_sale_offers_by_product()
    {
        $otherProduct = Product::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        
        PreSaleOffer::factory()->create([
            'product_id' => $this->product->id,
            'status' => 'active',
            'organization_id' => $this->organization->id,
        ]);
        
        PreSaleOffer::factory()->create([
            'product_id' => $otherProduct->id,
            'status' => 'active',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/pre-sale-offers/by-product?product_id=' . $this->product->id);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($this->product->id, $response->json('data.0.product_id'));
    }

    /** @test */
    public function it_validates_product_id_for_by_product_endpoint()
    {
        $response = $this->getJson('/api/v1/pre-sale-offers/by-product');

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_get_pre_sale_offer_statistics()
    {
        PreSaleOffer::factory()->count(3)->create([
            'status' => 'active',
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);
        
        PreSaleOffer::factory()->count(2)->create([
            'status' => 'inactive',
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->getJson('/api/v1/pre-sale-offers/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_offers',
                        'active_offers',
                        'expired_offers',
                        'offers_by_type',
                        'offers_by_status',
                        'total_discount_value',
                        'average_discount_percentage',
                        'featured_offers'
                    ]
                ]);

        $this->assertEquals(5, $response->json('data.total_offers'));
        $this->assertEquals(3, $response->json('data.active_offers'));
        $this->assertEquals(2, $response->json('data.inactive_offers'));
    }

    /** @test */
    public function it_can_activate_pre_sale_offer()
    {
        $preSaleOffer = PreSaleOffer::factory()->create([
            'status' => 'inactive',
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->postJson("/api/v1/pre-sale-offers/{$preSaleOffer->id}/activate");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Pre-sale offer activated successfully'
                ]);

        $this->assertDatabaseHas('pre_sale_offers', [
            'id' => $preSaleOffer->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function it_cannot_activate_already_active_pre_sale_offer()
    {
        $preSaleOffer = PreSaleOffer::factory()->create([
            'status' => 'active',
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->postJson("/api/v1/pre-sale-offers/{$preSaleOffer->id}/activate");

        $response->assertStatus(422)
                ->assertJson([
                    'message' => 'Pre-sale offer is already active'
                ]);
    }

    /** @test */
    public function it_can_deactivate_pre_sale_offer()
    {
        $preSaleOffer = PreSaleOffer::factory()->create([
            'status' => 'active',
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->postJson("/api/v1/pre-sale-offers/{$preSaleOffer->id}/deactivate");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Pre-sale offer deactivated successfully'
                ]);

        $this->assertDatabaseHas('pre_sale_offers', [
            'id' => $preSaleOffer->id,
            'status' => 'inactive',
        ]);
    }

    /** @test */
    public function it_cannot_deactivate_already_inactive_pre_sale_offer()
    {
        $preSaleOffer = PreSaleOffer::factory()->create([
            'status' => 'inactive',
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->postJson("/api/v1/pre-sale-offers/{$preSaleOffer->id}/deactivate");

        $response->assertStatus(422)
                ->assertJson([
                    'message' => 'Pre-sale offer is already inactive'
                ]);
    }

    /** @test */
    public function it_can_toggle_featured_status()
    {
        $preSaleOffer = PreSaleOffer::factory()->create([
            'is_featured' => false,
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->postJson("/api/v1/pre-sale-offers/{$preSaleOffer->id}/toggle-featured", [
            'is_featured' => true
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Pre-sale offer featured status updated successfully'
                ]);

        $this->assertDatabaseHas('pre_sale_offers', [
            'id' => $preSaleOffer->id,
            'is_featured' => true,
        ]);
    }

    /** @test */
    public function it_validates_featured_status_parameter()
    {
        $preSaleOffer = PreSaleOffer::factory()->create([
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->postJson("/api/v1/pre-sale-offers/{$preSaleOffer->id}/toggle-featured", [
            'is_featured' => 'invalid_boolean'
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_duplicate_pre_sale_offer()
    {
        $preSaleOffer = PreSaleOffer::factory()->create([
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->postJson("/api/v1/pre-sale-offers/{$preSaleOffer->id}/duplicate", [
            'title' => 'Copy of Early Bird Special',
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Pre-sale offer duplicated successfully'
                ]);

        $this->assertDatabaseHas('pre_sale_offers', [
            'title' => 'Copy of Early Bird Special',
            'status' => 'inactive',
        ]);
    }

    /** @test */
    public function it_validates_product_for_duplicate_endpoint()
    {
        $preSaleOffer = PreSaleOffer::factory()->create([
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->postJson("/api/v1/pre-sale-offers/{$preSaleOffer->id}/duplicate", [
            'product_id' => 999999,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_validate_pre_sale_offer()
    {
        $preSaleOffer = PreSaleOffer::factory()->create([
            'status' => 'active',
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(30),
            'min_quantity' => 1,
            'max_quantity' => 100,
            'original_price' => 200.0,
            'discount_percentage' => 20.0,
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->postJson('/api/v1/pre-sale-offers/validate', [
            'offer_id' => $preSaleOffer->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'valid',
                    'offer' => [
                        'id',
                        'title',
                        'type',
                        'discount_percentage',
                        'original_price',
                        'final_price',
                        'quantity',
                        'total_savings',
                    ],
                    'message'
                ]);

        $this->assertTrue($response->json('valid'));
    }

    /** @test */
    public function it_validates_inactive_pre_sale_offer()
    {
        $preSaleOffer = PreSaleOffer::factory()->create([
            'status' => 'inactive',
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->postJson('/api/v1/pre-sale-offers/validate', [
            'offer_id' => $preSaleOffer->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'valid' => false,
                    'message' => 'Pre-sale offer is not active'
                ]);
    }

    /** @test */
    public function it_validates_expired_pre_sale_offer()
    {
        $preSaleOffer = PreSaleOffer::factory()->create([
            'status' => 'active',
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDays(1), // Expired
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->postJson('/api/v1/pre-sale-offers/validate', [
            'offer_id' => $preSaleOffer->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'valid' => false,
                    'message' => 'Pre-sale offer has expired'
                ]);
    }

    /** @test */
    public function it_validates_future_pre_sale_offer()
    {
        $preSaleOffer = PreSaleOffer::factory()->create([
            'status' => 'active',
            'start_date' => now()->addDays(5), // Future
            'end_date' => now()->addDays(30),
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->postJson('/api/v1/pre-sale-offers/validate', [
            'offer_id' => $preSaleOffer->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'valid' => false,
                    'message' => 'Pre-sale offer has not started yet'
                ]);
    }

    /** @test */
    public function it_validates_minimum_quantity()
    {
        $preSaleOffer = PreSaleOffer::factory()->create([
            'status' => 'active',
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(30),
            'min_quantity' => 5,
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->postJson('/api/v1/pre-sale-offers/validate', [
            'offer_id' => $preSaleOffer->id,
            'quantity' => 2, // Below minimum
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'valid' => false,
                    'message' => 'Minimum quantity of 5 required'
                ]);
    }

    /** @test */
    public function it_validates_maximum_quantity()
    {
        $preSaleOffer = PreSaleOffer::factory()->create([
            'status' => 'active',
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(30),
            'max_quantity' => 10,
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->postJson('/api/v1/pre-sale-offers/validate', [
            'offer_id' => $preSaleOffer->id,
            'quantity' => 15, // Above maximum
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'valid' => false,
                    'message' => 'Maximum quantity of 10 allowed'
                ]);
    }

    /** @test */
    public function it_calculates_final_price_correctly()
    {
        $preSaleOffer = PreSaleOffer::factory()->create([
            'status' => 'active',
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(30),
            'original_price' => 100.0,
            'discount_percentage' => 25.0,
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->postJson('/api/v1/pre-sale-offers/validate', [
            'offer_id' => $preSaleOffer->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(150.0, $response->json('offer.final_price')); // 75 * 2
        $this->assertEquals(50.0, $response->json('offer.total_savings')); // 25 * 2
    }

    /** @test */
    public function it_requires_authentication_for_protected_routes()
    {
        // Clear authentication
        $this->withoutMiddleware();

        $response = $this->getJson('/api/v1/pre-sale-offers');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_filter_by_discount_range()
    {
        PreSaleOffer::factory()->create([
            'discount_percentage' => 15.0,
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);
        
        PreSaleOffer::factory()->create([
            'discount_percentage' => 35.0,
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->getJson('/api/v1/pre-sale-offers?discount_min=20&discount_max=50');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(35.0, $response->json('data.0.discount_percentage'));
    }

    /** @test */
    public function it_can_filter_by_price_range()
    {
        PreSaleOffer::factory()->create([
            'price' => 50.0,
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);
        
        PreSaleOffer::factory()->create([
            'price' => 200.0,
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->getJson('/api/v1/pre-sale-offers?price_min=100&price_max=300');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(200.0, $response->json('data.0.price'));
    }

    /** @test */
    public function it_can_filter_by_featured_status()
    {
        PreSaleOffer::factory()->create([
            'is_featured' => true,
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);
        
        PreSaleOffer::factory()->create([
            'is_featured' => false,
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->getJson('/api/v1/pre-sale-offers?is_featured=true');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertTrue($response->json('data.0.is_featured'));
    }

    /** @test */
    public function it_can_filter_by_limited_time_status()
    {
        PreSaleOffer::factory()->create([
            'is_limited_time' => true,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(30),
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);
        
        PreSaleOffer::factory()->create([
            'is_limited_time' => false,
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->getJson('/api/v1/pre-sale-offers?is_limited_time=true');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertTrue($response->json('data.0.is_limited_time'));
    }

    /** @test */
    public function it_can_sort_pre_sale_offers()
    {
        PreSaleOffer::factory()->create([
            'title' => 'A Offer',
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);
        
        PreSaleOffer::factory()->create([
            'title' => 'Z Offer',
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->getJson('/api/v1/pre-sale-offers?sort_by=title&sort_direction=asc');

        $response->assertStatus(200);
        $this->assertEquals('A Offer', $response->json('data.0.title'));
        $this->assertEquals('Z Offer', $response->json('data.1.title'));
    }

    /** @test */
    public function it_can_paginate_results()
    {
        PreSaleOffer::factory()->count(25)->create([
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->getJson('/api/v1/pre-sale-offers?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(25, $response->json('meta.total'));
        $this->assertEquals(3, $response->json('meta.last_page'));
    }

    /** @test */
    public function it_can_filter_by_organization()
    {
        $otherOrganization = Organization::factory()->create();
        
        PreSaleOffer::factory()->create([
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);
        
        PreSaleOffer::factory()->create([
            'organization_id' => $otherOrganization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->getJson('/api/v1/pre-sale-offers?organization_id=' . $this->organization->id);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($this->organization->id, $response->json('data.0.organization.id'));
    }

    /** @test */
    public function it_can_filter_by_product()
    {
        $otherProduct = Product::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        
        PreSaleOffer::factory()->create([
            'product_id' => $this->product->id,
            'organization_id' => $this->organization->id,
        ]);
        
        PreSaleOffer::factory()->create([
            'product_id' => $otherProduct->id,
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/pre-sale-offers?product_id=' . $this->product->id);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($this->product->id, $response->json('data.0.product_id'));
    }

    /** @test */
    public function it_can_filter_by_date_range()
    {
        $oldOffer = PreSaleOffer::factory()->create([
            'created_at' => now()->subDays(10),
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);
        
        $newOffer = PreSaleOffer::factory()->create([
            'created_at' => now()->addDays(5),
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->getJson('/api/v1/pre-sale-offers?created_at_from=' . now()->toDateString());

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($newOffer->id, $response->json('data.0.id'));
    }

    /** @test */
    public function it_handles_complex_search_queries()
    {
        PreSaleOffer::factory()->create([
            'title' => 'Early Bird Special',
            'type' => 'discount',
            'status' => 'active',
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);
        
        PreSaleOffer::factory()->create([
            'title' => 'Holiday Discount',
            'type' => 'fixed_amount',
            'status' => 'inactive',
            'organization_id' => $this->organization->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->getJson('/api/v1/pre-sale-offers?search=Early Bird&type=discount&status=active');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Early Bird Special', $response->json('data.0.title'));
    }
}
