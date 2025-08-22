<?php

namespace Tests\Feature\Api\V1;

use App\Models\Affiliate;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AffiliateControllerTest extends TestCase
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
    public function it_can_list_affiliates()
    {
        Affiliate::factory()->count(5)->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/affiliates');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'company_name',
                            'website',
                            'status',
                            'type',
                            'commission_rate',
                            'performance_rating',
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
    public function it_can_filter_affiliates_by_type()
    {
        Affiliate::factory()->create([
            'type' => 'partner',
            'organization_id' => $this->organization->id,
        ]);
        
        Affiliate::factory()->create([
            'type' => 'reseller',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/affiliates?type=partner');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('partner', $response->json('data.0.type'));
    }

    /** @test */
    public function it_can_filter_affiliates_by_status()
    {
        Affiliate::factory()->create([
            'status' => 'active',
            'organization_id' => $this->organization->id,
        ]);
        
        Affiliate::factory()->create([
            'status' => 'inactive',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/affiliates?status=active');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('active', $response->json('data.0.status'));
    }

    /** @test */
    public function it_can_search_affiliates()
    {
        Affiliate::factory()->create([
            'name' => 'Energy Partner Alpha',
            'organization_id' => $this->organization->id,
        ]);
        
        Affiliate::factory()->create([
            'name' => 'Solar Solutions Beta',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/affiliates?search=energy');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertStringContainsString('Energy', $response->json('data.0.name'));
    }

    /** @test */
    public function it_can_create_affiliate()
    {
        $affiliateData = [
            'name' => 'Test Affiliate',
            'email' => 'test@affiliate.com',
            'company_name' => 'Test Company',
            'website' => 'https://testcompany.com',
            'phone' => '+1-555-0123',
            'type' => 'partner',
            'status' => 'active',
            'commission_rate' => 10.5,
            'payment_terms' => 'Net 30',
            'organization_id' => $this->organization->id,
            'is_verified' => false,
            'notes' => 'Test affiliate notes',
        ];

        $response = $this->postJson('/api/v1/affiliates', $affiliateData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'email',
                        'company_name',
                        'type',
                        'status',
                    ]
                ]);

        $this->assertDatabaseHas('affiliates', [
            'name' => 'Test Affiliate',
            'email' => 'test@affiliate.com',
            'company_name' => 'Test Company',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_affiliate()
    {
        $response = $this->postJson('/api/v1/affiliates', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'name',
                    'email',
                    'type',
                    'status',
                ]);
    }

    /** @test */
    public function it_validates_affiliate_type_enum()
    {
        $affiliateData = [
            'name' => 'Test Affiliate',
            'email' => 'test@affiliate.com',
            'type' => 'invalid_type',
            'status' => 'active',
        ];

        $response = $this->postJson('/api/v1/affiliates', $affiliateData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['type']);
    }

    /** @test */
    public function it_validates_commission_rate_range()
    {
        $affiliateData = [
            'name' => 'Test Affiliate',
            'email' => 'test@affiliate.com',
            'type' => 'partner',
            'status' => 'active',
            'commission_rate' => 150.0, // Más del 100%
        ];

        $response = $this->postJson('/api/v1/affiliates', $affiliateData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['commission_rate']);
    }

    /** @test */
    public function it_can_show_affiliate()
    {
        $affiliate = Affiliate::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson("/api/v1/affiliates/{$affiliate->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'email',
                        'company_name',
                        'type',
                        'status',
                        'created_at',
                        'links',
                    ]
                ]);

        $this->assertEquals($affiliate->id, $response->json('data.id'));
    }

    /** @test */
    public function it_returns_404_for_nonexistent_affiliate()
    {
        $response = $this->getJson('/api/v1/affiliates/999999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_affiliate()
    {
        $affiliate = Affiliate::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $updateData = [
            'name' => 'Updated Affiliate Name',
            'commission_rate' => 15.0,
        ];

        $response = $this->putJson("/api/v1/affiliates/{$affiliate->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'commission_rate',
                    ]
                ]);

        $this->assertDatabaseHas('affiliates', [
            'id' => $affiliate->id,
            'name' => 'Updated Affiliate Name',
            'commission_rate' => 15.0,
        ]);
    }

    /** @test */
    public function it_can_delete_affiliate()
    {
        $affiliate = Affiliate::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->deleteJson("/api/v1/affiliates/{$affiliate->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Affiliate deleted successfully'
                ]);

        $this->assertDatabaseMissing('affiliates', [
            'id' => $affiliate->id,
        ]);
    }

    /** @test */
    public function it_can_get_active_affiliates()
    {
        Affiliate::factory()->create([
            'status' => 'active',
            'is_verified' => true,
            'organization_id' => $this->organization->id,
        ]);
        
        Affiliate::factory()->create([
            'status' => 'inactive',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/affiliates/active');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('active', $response->json('data.0.status'));
    }

    /** @test */
    public function it_can_get_affiliates_by_type()
    {
        Affiliate::factory()->create([
            'type' => 'partner',
            'organization_id' => $this->organization->id,
        ]);
        
        Affiliate::factory()->create([
            'type' => 'reseller',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/affiliates/by-type/partner');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('partner', $response->json('data.0.type'));
    }

    /** @test */
    public function it_validates_type_parameter_for_by_type_endpoint()
    {
        $response = $this->getJson('/api/v1/affiliates/by-type/invalid_type');

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_get_top_performers()
    {
        Affiliate::factory()->create([
            'status' => 'active',
            'is_verified' => true,
            'performance_rating' => 5,
            'organization_id' => $this->organization->id,
        ]);
        
        Affiliate::factory()->create([
            'status' => 'active',
            'is_verified' => true,
            'performance_rating' => 3,
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/affiliates/top-performers');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data',
                    'period',
                    'total_affiliates'
                ]);

        $this->assertCount(2, $response->json('data'));
        $this->assertEquals(5, $response->json('data.0.performance_rating'));
    }

    /** @test */
    public function it_can_get_affiliate_statistics()
    {
        Affiliate::factory()->count(3)->create([
            'status' => 'active',
            'organization_id' => $this->organization->id,
        ]);
        
        Affiliate::factory()->count(2)->create([
            'status' => 'inactive',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/affiliates/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_affiliates',
                        'active_affiliates',
                        'verified_affiliates',
                        'affiliates_by_type',
                        'affiliates_by_status',
                        'average_commission_rate',
                        'average_performance_rating',
                        'monthly_growth'
                    ]
                ]);

        $this->assertEquals(5, $response->json('data.total_affiliates'));
        $this->assertEquals(3, $response->json('data.active_affiliates'));
        $this->assertEquals(2, $response->json('data.inactive_affiliates'));
    }

    /** @test */
    public function it_can_verify_affiliate()
    {
        $affiliate = Affiliate::factory()->create([
            'is_verified' => false,
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->postJson("/api/v1/affiliates/{$affiliate->id}/verify", [
            'verification_notes' => 'Documents verified successfully'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Affiliate verified successfully'
                ]);

        $this->assertDatabaseHas('affiliates', [
            'id' => $affiliate->id,
            'is_verified' => true,
        ]);
    }

    /** @test */
    public function it_cannot_verify_already_verified_affiliate()
    {
        $affiliate = Affiliate::factory()->create([
            'is_verified' => true,
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->postJson("/api/v1/affiliates/{$affiliate->id}/verify", [
            'verification_notes' => 'Already verified'
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_update_performance_rating()
    {
        $affiliate = Affiliate::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->postJson("/api/v1/affiliates/{$affiliate->id}/update-performance-rating", [
            'performance_rating' => 4,
            'rating_notes' => 'Excellent performance'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Performance rating updated successfully'
                ]);

        $this->assertDatabaseHas('affiliates', [
            'id' => $affiliate->id,
            'performance_rating' => 4,
            'rating_notes' => 'Excellent performance',
        ]);
    }

    /** @test */
    public function it_validates_performance_rating_range()
    {
        $affiliate = Affiliate::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->postJson("/api/v1/affiliates/{$affiliate->id}/update-performance-rating", [
            'performance_rating' => 6, // Más del máximo
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['performance_rating']);
    }

    /** @test */
    public function it_can_update_commission_rate()
    {
        $affiliate = Affiliate::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->postJson("/api/v1/affiliates/{$affiliate->id}/update-commission-rate", [
            'commission_rate' => 12.5,
            'rate_change_reason' => 'Performance improvement'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Commission rate updated successfully'
                ]);

        $this->assertDatabaseHas('affiliates', [
            'id' => $affiliate->id,
            'commission_rate' => 12.5,
            'rate_change_reason' => 'Performance improvement',
        ]);
    }

    /** @test */
    public function it_validates_commission_rate_range_for_update()
    {
        $affiliate = Affiliate::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->postJson("/api/v1/affiliates/{$affiliate->id}/update-commission-rate", [
            'commission_rate' => 150.0, // Más del 100%
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['commission_rate']);
    }

    /** @test */
    public function it_can_duplicate_affiliate()
    {
        $affiliate = Affiliate::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->postJson("/api/v1/affiliates/{$affiliate->id}/duplicate", [
            'name' => 'Copy of ' . $affiliate->name,
            'email' => 'copy_' . $affiliate->email,
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Affiliate duplicated successfully'
                ]);

        $this->assertDatabaseHas('affiliates', [
            'name' => 'Copy of ' . $affiliate->name,
            'email' => 'copy_' . $affiliate->email,
            'status' => 'pending',
            'is_verified' => false,
        ]);
    }

    /** @test */
    public function it_requires_authentication_for_protected_routes()
    {
        // Clear authentication
        $this->withoutMiddleware();

        $response = $this->getJson('/api/v1/affiliates');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_filter_by_commission_rate_range()
    {
        Affiliate::factory()->create([
            'commission_rate' => 5.0,
            'organization_id' => $this->organization->id,
        ]);
        
        Affiliate::factory()->create([
            'commission_rate' => 20.0,
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/affiliates?commission_rate_min=10&commission_rate_max=25');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(20.0, $response->json('data.0.commission_rate'));
    }

    /** @test */
    public function it_can_filter_by_performance_rating_range()
    {
        Affiliate::factory()->create([
            'performance_rating' => 2,
            'organization_id' => $this->organization->id,
        ]);
        
        Affiliate::factory()->create([
            'performance_rating' => 4,
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/affiliates?performance_rating_min=3&performance_rating_max=5');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(4, $response->json('data.0.performance_rating'));
    }

    /** @test */
    public function it_can_sort_affiliates()
    {
        Affiliate::factory()->create([
            'name' => 'Alpha Affiliate',
            'organization_id' => $this->organization->id,
        ]);
        
        Affiliate::factory()->create([
            'name' => 'Beta Affiliate',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/affiliates?sort_by=name&sort_direction=asc');

        $response->assertStatus(200);
        $this->assertEquals('Alpha Affiliate', $response->json('data.0.name'));
        $this->assertEquals('Beta Affiliate', $response->json('data.1.name'));
    }

    /** @test */
    public function it_can_paginate_results()
    {
        Affiliate::factory()->count(25)->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/affiliates?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(25, $response->json('meta.total'));
        $this->assertEquals(3, $response->json('meta.last_page'));
    }

    /** @test */
    public function it_can_filter_by_verification_status()
    {
        Affiliate::factory()->create([
            'is_verified' => true,
            'organization_id' => $this->organization->id,
        ]);
        
        Affiliate::factory()->create([
            'is_verified' => false,
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/affiliates?is_verified=true');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertTrue($response->json('data.0.is_verified'));
    }

    /** @test */
    public function it_can_filter_by_organization()
    {
        $otherOrganization = Organization::factory()->create();
        
        Affiliate::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        
        Affiliate::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);

        $response = $this->getJson('/api/v1/affiliates?organization_id=' . $this->organization->id);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($this->organization->id, $response->json('data.0.organization.id'));
    }

    /** @test */
    public function it_can_filter_by_date_range()
    {
        $oldAffiliate = Affiliate::factory()->create([
            'created_at' => now()->subDays(10),
            'organization_id' => $this->organization->id,
        ]);
        
        $newAffiliate = Affiliate::factory()->create([
            'created_at' => now()->addDays(5),
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/affiliates?created_at_from=' . now()->toDateString());

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($newAffiliate->id, $response->json('data.0.id'));
    }

    /** @test */
    public function it_handles_complex_search_queries()
    {
        Affiliate::factory()->create([
            'name' => 'Energy Partner Corp',
            'company_name' => 'Energy Solutions Inc',
            'type' => 'partner',
            'status' => 'active',
            'organization_id' => $this->organization->id,
        ]);
        
        Affiliate::factory()->create([
            'name' => 'Solar Distributor',
            'company_name' => 'Solar Power Ltd',
            'type' => 'distributor',
            'status' => 'active',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/affiliates?search=energy&type=partner&status=active');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Energy Partner Corp', $response->json('data.0.name'));
    }
}
