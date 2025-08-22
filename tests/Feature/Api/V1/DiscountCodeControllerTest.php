<?php

namespace Tests\Feature\Api\V1;

use App\Models\DiscountCode;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DiscountCodeControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create();
        
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_list_discount_codes()
    {
        DiscountCode::factory()->count(5)->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/discount-codes');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'code',
                            'name',
                            'description',
                            'type',
                            'value',
                            'status',
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
    public function it_can_filter_discount_codes_by_type()
    {
        DiscountCode::factory()->create([
            'type' => 'percentage',
            'organization_id' => $this->organization->id,
        ]);
        
        DiscountCode::factory()->create([
            'type' => 'fixed_amount',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/discount-codes?type=percentage');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('percentage', $response->json('data.0.type'));
    }

    /** @test */
    public function it_can_filter_discount_codes_by_status()
    {
        DiscountCode::factory()->create([
            'status' => 'active',
            'organization_id' => $this->organization->id,
        ]);
        
        DiscountCode::factory()->create([
            'status' => 'inactive',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/discount-codes?status=active');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('active', $response->json('data.0.status'));
    }

    /** @test */
    public function it_can_search_discount_codes()
    {
        DiscountCode::factory()->create([
            'code' => 'SUMMER20',
            'organization_id' => $this->organization->id,
        ]);
        
        DiscountCode::factory()->create([
            'code' => 'WINTER25',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/discount-codes?search=summer');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertStringContainsString('SUMMER', $response->json('data.0.code'));
    }

    /** @test */
    public function it_can_create_discount_code()
    {
        $discountCodeData = [
            'code' => 'TEST20',
            'name' => 'Test Discount 20%',
            'description' => 'Test discount description',
            'type' => 'percentage',
            'value' => 20.0,
            'max_discount_amount' => 100.0,
            'min_order_amount' => 50.0,
            'usage_limit' => 100,
            'usage_limit_per_user' => 1,
            'status' => 'active',
            'is_public' => true,
            'organization_id' => $this->organization->id,
            'valid_from' => now()->toDateString(),
            'valid_until' => now()->addMonths(3)->toDateString(),
        ];

        $response = $this->postJson('/api/v1/discount-codes', $discountCodeData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'code',
                        'name',
                        'type',
                        'value',
                        'status',
                    ]
                ]);

        $this->assertDatabaseHas('discount_codes', [
            'code' => 'TEST20',
            'name' => 'Test Discount 20%',
            'type' => 'percentage',
            'value' => 20.0,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_discount_code()
    {
        $response = $this->postJson('/api/v1/discount-codes', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'code',
                    'name',
                    'type',
                    'value',
                    'status',
                ]);
    }

    /** @test */
    public function it_validates_discount_type_enum()
    {
        $discountCodeData = [
            'code' => 'TEST20',
            'name' => 'Test Discount',
            'type' => 'invalid_type',
            'value' => 20.0,
            'status' => 'active',
        ];

        $response = $this->postJson('/api/v1/discount-codes', $discountCodeData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['type']);
    }

    /** @test */
    public function it_validates_value_is_positive()
    {
        $discountCodeData = [
            'code' => 'TEST20',
            'name' => 'Test Discount',
            'type' => 'percentage',
            'value' => -20.0,
            'status' => 'active',
        ];

        $response = $this->postJson('/api/v1/discount-codes', $discountCodeData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['value']);
    }

    /** @test */
    public function it_can_show_discount_code()
    {
        $discountCode = DiscountCode::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson("/api/v1/discount-codes/{$discountCode->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'code',
                        'name',
                        'type',
                        'value',
                        'status',
                        'created_at',
                        'links',
                    ]
                ]);

        $this->assertEquals($discountCode->id, $response->json('data.id'));
    }

    /** @test */
    public function it_returns_404_for_nonexistent_discount_code()
    {
        $response = $this->getJson('/api/v1/discount-codes/999999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_discount_code()
    {
        $discountCode = DiscountCode::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $updateData = [
            'name' => 'Updated Discount Name',
            'value' => 25.0,
        ];

        $response = $this->putJson("/api/v1/discount-codes/{$discountCode->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'value',
                    ]
                ]);

        $this->assertDatabaseHas('discount_codes', [
            'id' => $discountCode->id,
            'name' => 'Updated Discount Name',
            'value' => 25.0,
        ]);
    }

    /** @test */
    public function it_can_delete_discount_code()
    {
        $discountCode = DiscountCode::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->deleteJson("/api/v1/discount-codes/{$discountCode->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Discount code deleted successfully'
                ]);

        $this->assertDatabaseMissing('discount_codes', [
            'id' => $discountCode->id,
        ]);
    }

    /** @test */
    public function it_can_get_active_discount_codes()
    {
        DiscountCode::factory()->create([
            'status' => 'active',
            'is_public' => true,
            'valid_until' => now()->addDays(30),
            'organization_id' => $this->organization->id,
        ]);
        
        DiscountCode::factory()->create([
            'status' => 'inactive',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/discount-codes/active');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('active', $response->json('data.0.status'));
    }

    /** @test */
    public function it_can_get_discount_codes_by_type()
    {
        DiscountCode::factory()->create([
            'type' => 'percentage',
            'organization_id' => $this->organization->id,
        ]);
        
        DiscountCode::factory()->create([
            'type' => 'fixed_amount',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/discount-codes/by-type/percentage');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('percentage', $response->json('data.0.type'));
    }

    /** @test */
    public function it_validates_type_parameter_for_by_type_endpoint()
    {
        $response = $this->getJson('/api/v1/discount-codes/by-type/invalid_type');

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_validate_discount_code()
    {
        $discountCode = DiscountCode::factory()->create([
            'status' => 'active',
            'type' => 'percentage',
            'value' => 20.0,
            'min_order_amount' => 50.0,
            'usage_limit' => 100,
            'usage_count' => 50,
            'valid_until' => now()->addDays(30),
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->postJson('/api/v1/discount-codes/validate', [
            'code' => $discountCode->code,
            'order_amount' => 100.0,
            'user_id' => $this->user->id,
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'valid' => true
                ])
                ->assertJsonStructure([
                    'valid',
                    'discount_code' => [
                        'id',
                        'code',
                        'name',
                        'type',
                        'value',
                        'discount_amount',
                    ]
                ]);
    }

    /** @test */
    public function it_validates_discount_code_validation_request()
    {
        $response = $this->postJson('/api/v1/discount-codes/validate', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['code']);
    }

    /** @test */
    public function it_returns_invalid_for_nonexistent_code()
    {
        $response = $this->postJson('/api/v1/discount-codes/validate', [
            'code' => 'NONEXISTENT',
            'order_amount' => 100.0,
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'valid' => false,
                    'message' => 'Invalid discount code'
                ]);
    }

    /** @test */
    public function it_returns_invalid_for_expired_code()
    {
        $discountCode = DiscountCode::factory()->create([
            'status' => 'active',
            'valid_until' => now()->subDays(1),
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->postJson('/api/v1/discount-codes/validate', [
            'code' => $discountCode->code,
            'order_amount' => 100.0,
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'valid' => false,
                    'message' => 'Discount code has expired'
                ]);
    }

    /** @test */
    public function it_returns_invalid_for_insufficient_order_amount()
    {
        $discountCode = DiscountCode::factory()->create([
            'status' => 'active',
            'min_order_amount' => 100.0,
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->postJson('/api/v1/discount-codes/validate', [
            'code' => $discountCode->code,
            'order_amount' => 50.0,
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'valid' => false,
                    'message' => 'Minimum order amount of 100 required'
                ]);
    }

    /** @test */
    public function it_returns_invalid_for_usage_limit_reached()
    {
        $discountCode = DiscountCode::factory()->create([
            'status' => 'active',
            'usage_limit' => 10,
            'usage_count' => 10,
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->postJson('/api/v1/discount-codes/validate', [
            'code' => $discountCode->code,
            'order_amount' => 100.0,
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'valid' => false,
                    'message' => 'Discount code usage limit reached'
                ]);
    }

    /** @test */
    public function it_can_get_discount_code_statistics()
    {
        DiscountCode::factory()->count(3)->create([
            'status' => 'active',
            'organization_id' => $this->organization->id,
        ]);
        
        DiscountCode::factory()->count(2)->create([
            'status' => 'inactive',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/discount-codes/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_codes',
                        'active_codes',
                        'expired_codes',
                        'codes_by_type',
                        'codes_by_status',
                        'total_usage',
                        'total_discount_amount',
                        'average_discount_value'
                    ]
                ]);

        $this->assertEquals(5, $response->json('data.total_codes'));
        $this->assertEquals(3, $response->json('data.active_codes'));
        $this->assertEquals(2, $response->json('data.inactive_codes'));
    }

    /** @test */
    public function it_can_activate_discount_code()
    {
        $discountCode = DiscountCode::factory()->create([
            'status' => 'inactive',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->postJson("/api/v1/discount-codes/{$discountCode->id}/activate");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Discount code activated successfully'
                ]);

        $this->assertDatabaseHas('discount_codes', [
            'id' => $discountCode->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function it_cannot_activate_already_active_discount_code()
    {
        $discountCode = DiscountCode::factory()->create([
            'status' => 'active',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->postJson("/api/v1/discount-codes/{$discountCode->id}/activate");

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_deactivate_discount_code()
    {
        $discountCode = DiscountCode::factory()->create([
            'status' => 'active',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->postJson("/api/v1/discount-codes/{$discountCode->id}/deactivate");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Discount code deactivated successfully'
                ]);

        $this->assertDatabaseHas('discount_codes', [
            'id' => $discountCode->id,
            'status' => 'inactive',
        ]);
    }

    /** @test */
    public function it_cannot_deactivate_already_inactive_discount_code()
    {
        $discountCode = DiscountCode::factory()->create([
            'status' => 'inactive',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->postJson("/api/v1/discount-codes/{$discountCode->id}/deactivate");

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_duplicate_discount_code()
    {
        $discountCode = DiscountCode::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->postJson("/api/v1/discount-codes/{$discountCode->id}/duplicate", [
            'code' => 'COPY_' . $discountCode->code,
            'name' => 'Copy of ' . $discountCode->name,
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Discount code duplicated successfully'
                ]);

        $this->assertDatabaseHas('discount_codes', [
            'code' => 'COPY_' . $discountCode->code,
            'name' => 'Copy of ' . $discountCode->name,
            'status' => 'inactive',
            'usage_count' => 0,
        ]);
    }

    /** @test */
    public function it_requires_authentication_for_protected_routes()
    {
        // Clear authentication
        $this->withoutMiddleware();

        $response = $this->getJson('/api/v1/discount-codes');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_filter_by_discount_value_range()
    {
        DiscountCode::factory()->create([
            'value' => 10.0,
            'organization_id' => $this->organization->id,
        ]);
        
        DiscountCode::factory()->create([
            'value' => 30.0,
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/discount-codes?discount_min=20&discount_max=40');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(30.0, $response->json('data.0.value'));
    }

    /** @test */
    public function it_can_filter_by_order_amount_range()
    {
        DiscountCode::factory()->create([
            'min_order_amount' => 50.0,
            'organization_id' => $this->organization->id,
        ]);
        
        DiscountCode::factory()->create([
            'min_order_amount' => 200.0,
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/discount-codes?min_order_amount_min=100&min_order_amount_max=300');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(200.0, $response->json('data.0.min_order_amount'));
    }

    /** @test */
    public function it_can_sort_discount_codes()
    {
        DiscountCode::factory()->create([
            'code' => 'ALPHA10',
            'organization_id' => $this->organization->id,
        ]);
        
        DiscountCode::factory()->create([
            'code' => 'BETA20',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/discount-codes?sort_by=code&sort_direction=asc');

        $response->assertStatus(200);
        $this->assertEquals('ALPHA10', $response->json('data.0.code'));
        $this->assertEquals('BETA20', $response->json('data.1.code'));
    }

    /** @test */
    public function it_can_paginate_results()
    {
        DiscountCode::factory()->count(25)->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/discount-codes?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(25, $response->json('meta.total'));
        $this->assertEquals(3, $response->json('meta.last_page'));
    }

    /** @test */
    public function it_can_filter_by_public_status()
    {
        DiscountCode::factory()->create([
            'is_public' => true,
            'organization_id' => $this->organization->id,
        ]);
        
        DiscountCode::factory()->create([
            'is_public' => false,
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/discount-codes?is_public=true');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertTrue($response->json('data.0.is_public'));
    }

    /** @test */
    public function it_can_filter_by_organization()
    {
        $otherOrganization = Organization::factory()->create();
        
        DiscountCode::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        
        DiscountCode::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);

        $response = $this->getJson('/api/v1/discount-codes?organization_id=' . $this->organization->id);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($this->organization->id, $response->json('data.0.organization.id'));
    }

    /** @test */
    public function it_can_filter_by_date_range()
    {
        $oldDiscountCode = DiscountCode::factory()->create([
            'created_at' => now()->subDays(10),
            'organization_id' => $this->organization->id,
        ]);
        
        $newDiscountCode = DiscountCode::factory()->create([
            'created_at' => now()->addDays(5),
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/discount-codes?created_at_from=' . now()->toDateString());

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($newDiscountCode->id, $response->json('data.0.id'));
    }

    /** @test */
    public function it_handles_complex_search_queries()
    {
        DiscountCode::factory()->create([
            'code' => 'SUMMER20',
            'name' => 'Summer Sale 20%',
            'type' => 'percentage',
            'status' => 'active',
            'organization_id' => $this->organization->id,
        ]);
        
        DiscountCode::factory()->create([
            'code' => 'WINTER25',
            'name' => 'Winter Sale 25%',
            'type' => 'percentage',
            'status' => 'active',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/discount-codes?search=summer&type=percentage&status=active');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('SUMMER20', $response->json('data.0.code'));
    }

    /** @test */
    public function it_calculates_discount_amount_correctly()
    {
        $discountCode = DiscountCode::factory()->create([
            'status' => 'active',
            'type' => 'percentage',
            'value' => 15.0,
            'max_discount_amount' => 50.0,
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->postJson('/api/v1/discount-codes/validate', [
            'code' => $discountCode->code,
            'order_amount' => 200.0,
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'valid' => true
                ]);

        // 15% of 200 = 30, but max discount is 50, so should be 30
        $this->assertEquals(30.0, $response->json('discount_code.discount_amount'));
    }

    /** @test */
    public function it_respects_max_discount_amount()
    {
        $discountCode = DiscountCode::factory()->create([
            'status' => 'active',
            'type' => 'percentage',
            'value' => 25.0,
            'max_discount_amount' => 50.0,
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->postJson('/api/v1/discount-codes/validate', [
            'code' => $discountCode->code,
            'order_amount' => 500.0,
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'valid' => true
                ]);

        // 25% of 500 = 125, but max discount is 50, so should be 50
        $this->assertEquals(50.0, $response->json('discount_code.discount_amount'));
    }
}
