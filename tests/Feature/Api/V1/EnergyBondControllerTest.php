<?php

namespace Tests\Feature\Api\V1;

use App\Models\EnergyBond;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EnergyBondControllerTest extends TestCase
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
    public function it_can_list_energy_bonds()
    {
        EnergyBond::factory()->count(5)->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/energy-bonds');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'bond_type',
                            'status',
                            'face_value',
                            'interest_rate',
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
    public function it_can_filter_energy_bonds_by_bond_type()
    {
        EnergyBond::factory()->create([
            'bond_type' => 'solar',
            'created_by' => $this->user->id,
        ]);
        
        EnergyBond::factory()->create([
            'bond_type' => 'wind',
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/energy-bonds?bond_type=solar');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('solar', $response->json('data.0.bond_type'));
    }

    /** @test */
    public function it_can_filter_energy_bonds_by_status()
    {
        EnergyBond::factory()->create([
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);
        
        EnergyBond::factory()->create([
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/energy-bonds?status=active');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('active', $response->json('data.0.status'));
    }

    /** @test */
    public function it_can_search_energy_bonds()
    {
        EnergyBond::factory()->create([
            'name' => 'Solar Bond Alpha',
            'created_by' => $this->user->id,
        ]);
        
        EnergyBond::factory()->create([
            'name' => 'Wind Bond Beta',
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/energy-bonds?search=solar');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertStringContainsString('Solar', $response->json('data.0.name'));
    }

    /** @test */
    public function it_can_create_energy_bond()
    {
        $bondData = [
            'name' => 'Test Solar Bond',
            'description' => 'A test solar energy bond',
            'bond_type' => 'solar',
            'status' => 'draft',
            'priority' => 'medium',
            'face_value' => 10000.00,
            'interest_rate' => 5.5,
            'maturity_date' => now()->addYears(5)->toDateString(),
            'coupon_frequency' => 'annually',
            'payment_method' => 'bank_transfer',
            'currency' => 'EUR',
            'total_units' => 1000,
            'available_units' => 1000,
            'minimum_investment' => 100.00,
            'risk_rating' => 'aa',
            'organization_id' => $this->organization->id,
        ];

        $response = $this->postJson('/api/v1/energy-bonds', $bondData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'bond_type',
                        'status',
                        'face_value',
                        'interest_rate',
                    ]
                ]);

        $this->assertDatabaseHas('energy_bonds', [
            'name' => 'Test Solar Bond',
            'bond_type' => 'solar',
            'created_by' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_bond()
    {
        $response = $this->postJson('/api/v1/energy-bonds', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'name',
                    'bond_type',
                    'status',
                    'priority',
                    'face_value',
                    'interest_rate',
                    'maturity_date',
                    'coupon_frequency',
                    'payment_method',
                    'currency',
                    'total_units',
                    'available_units',
                    'minimum_investment',
                    'risk_rating',
                ]);
    }

    /** @test */
    public function it_validates_bond_type_enum_when_creating()
    {
        $bondData = [
            'name' => 'Test Bond',
            'bond_type' => 'invalid_type',
            'status' => 'draft',
            'priority' => 'medium',
            'face_value' => 10000.00,
            'interest_rate' => 5.5,
            'maturity_date' => now()->addYears(5)->toDateString(),
            'coupon_frequency' => 'annually',
            'payment_method' => 'bank_transfer',
            'currency' => 'EUR',
            'total_units' => 1000,
            'available_units' => 1000,
            'minimum_investment' => 100.00,
            'risk_rating' => 'aa',
        ];

        $response = $this->postJson('/api/v1/energy-bonds', $bondData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['bond_type']);
    }

    /** @test */
    public function it_validates_maturity_date_is_in_future()
    {
        $bondData = [
            'name' => 'Test Bond',
            'bond_type' => 'solar',
            'status' => 'draft',
            'priority' => 'medium',
            'face_value' => 10000.00,
            'interest_rate' => 5.5,
            'maturity_date' => now()->subDays(1)->toDateString(),
            'coupon_frequency' => 'annually',
            'payment_method' => 'bank_transfer',
            'currency' => 'EUR',
            'total_units' => 1000,
            'available_units' => 1000,
            'minimum_investment' => 100.00,
            'risk_rating' => 'aa',
        ];

        $response = $this->postJson('/api/v1/energy-bonds', $bondData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['maturity_date']);
    }

    /** @test */
    public function it_can_show_energy_bond()
    {
        $bond = EnergyBond::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson("/api/v1/energy-bonds/{$bond->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'bond_type',
                        'status',
                        'face_value',
                        'interest_rate',
                        'created_at',
                        'links',
                    ]
                ]);

        $this->assertEquals($bond->id, $response->json('data.id'));
    }

    /** @test */
    public function it_returns_404_for_nonexistent_bond()
    {
        $response = $this->getJson('/api/v1/energy-bonds/999999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_energy_bond()
    {
        $bond = EnergyBond::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $updateData = [
            'name' => 'Updated Bond Name',
            'interest_rate' => 6.5,
        ];

        $response = $this->putJson("/api/v1/energy-bonds/{$bond->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'interest_rate',
                    ]
                ]);

        $this->assertDatabaseHas('energy_bonds', [
            'id' => $bond->id,
            'name' => 'Updated Bond Name',
            'interest_rate' => 6.5,
        ]);
    }

    /** @test */
    public function it_can_delete_energy_bond()
    {
        $bond = EnergyBond::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/v1/energy-bonds/{$bond->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Energy bond deleted successfully'
                ]);

        $this->assertDatabaseMissing('energy_bonds', [
            'id' => $bond->id,
        ]);
    }

    /** @test */
    public function it_can_get_public_energy_bonds()
    {
        EnergyBond::factory()->create([
            'is_public' => true,
            'status' => 'active',
        ]);
        
        EnergyBond::factory()->create([
            'is_public' => false,
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/v1/energy-bonds/public');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertTrue($response->json('data.0.is_public'));
    }

    /** @test */
    public function it_can_get_featured_energy_bonds()
    {
        EnergyBond::factory()->create([
            'is_featured' => true,
            'is_public' => true,
            'status' => 'active',
        ]);
        
        EnergyBond::factory()->create([
            'is_featured' => false,
            'is_public' => true,
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/v1/energy-bonds/featured');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertTrue($response->json('data.0.is_featured'));
    }

    /** @test */
    public function it_can_approve_energy_bond()
    {
        $bond = EnergyBond::factory()->create([
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/energy-bonds/{$bond->id}/approve");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Energy bond approved successfully'
                ]);

        $this->assertDatabaseHas('energy_bonds', [
            'id' => $bond->id,
            'status' => 'approved',
            'approved_by' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_cannot_approve_non_pending_bond()
    {
        $bond = EnergyBond::factory()->create([
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/energy-bonds/{$bond->id}/approve");

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_reject_energy_bond()
    {
        $bond = EnergyBond::factory()->create([
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/energy-bonds/{$bond->id}/reject", [
            'rejection_reason' => 'Insufficient documentation'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Energy bond rejected successfully'
                ]);

        $this->assertDatabaseHas('energy_bonds', [
            'id' => $bond->id,
            'status' => 'rejected',
            'approved_by' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_validates_rejection_reason()
    {
        $bond = EnergyBond::factory()->create([
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/energy-bonds/{$bond->id}/reject", []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['rejection_reason']);
    }

    /** @test */
    public function it_can_mark_bond_as_featured()
    {
        $bond = EnergyBond::factory()->create([
            'is_featured' => false,
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/energy-bonds/{$bond->id}/mark-featured");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Energy bond marked as featured successfully'
                ]);

        $this->assertDatabaseHas('energy_bonds', [
            'id' => $bond->id,
            'is_featured' => true,
        ]);
    }

    /** @test */
    public function it_can_remove_featured_status()
    {
        $bond = EnergyBond::factory()->create([
            'is_featured' => true,
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/energy-bonds/{$bond->id}/remove-featured");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Featured status removed successfully'
                ]);

        $this->assertDatabaseHas('energy_bonds', [
            'id' => $bond->id,
            'is_featured' => false,
        ]);
    }

    /** @test */
    public function it_can_make_bond_public()
    {
        $bond = EnergyBond::factory()->create([
            'is_public' => false,
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/energy-bonds/{$bond->id}/make-public");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Energy bond made public successfully'
                ]);

        $this->assertDatabaseHas('energy_bonds', [
            'id' => $bond->id,
            'is_public' => true,
        ]);
    }

    /** @test */
    public function it_can_make_bond_private()
    {
        $bond = EnergyBond::factory()->create([
            'is_public' => true,
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/energy-bonds/{$bond->id}/make-private");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Energy bond made private successfully'
                ]);

        $this->assertDatabaseHas('energy_bonds', [
            'id' => $bond->id,
            'is_public' => false,
        ]);
    }

    /** @test */
    public function it_can_duplicate_energy_bond()
    {
        $bond = EnergyBond::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/energy-bonds/{$bond->id}/duplicate");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Energy bond duplicated successfully'
                ]);

        $this->assertDatabaseHas('energy_bonds', [
            'name' => $bond->name . ' (Copia)',
            'status' => 'draft',
            'is_public' => false,
            'is_featured' => false,
        ]);
    }

    /** @test */
    public function it_can_get_energy_bond_statistics()
    {
        EnergyBond::factory()->count(3)->create([
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);
        
        EnergyBond::factory()->count(2)->create([
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/energy-bonds/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_bonds',
                        'active_bonds',
                        'pending_bonds',
                        'bonds_by_type',
                        'bonds_by_status',
                    ]
                ]);

        $this->assertEquals(5, $response->json('data.total_bonds'));
        $this->assertEquals(3, $response->json('data.active_bonds'));
        $this->assertEquals(2, $response->json('data.pending_bonds'));
    }

    /** @test */
    public function it_requires_authentication_for_protected_routes()
    {
        // Clear authentication
        $this->withoutMiddleware();

        $response = $this->getJson('/api/v1/energy-bonds');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_filter_by_face_value_range()
    {
        EnergyBond::factory()->create([
            'face_value' => 5000,
            'created_by' => $this->user->id,
        ]);
        
        EnergyBond::factory()->create([
            'face_value' => 15000,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/energy-bonds?min_face_value=10000&max_face_value=20000');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(15000, $response->json('data.0.face_value'));
    }

    /** @test */
    public function it_can_sort_energy_bonds()
    {
        EnergyBond::factory()->create([
            'name' => 'Alpha Bond',
            'created_by' => $this->user->id,
        ]);
        
        EnergyBond::factory()->create([
            'name' => 'Beta Bond',
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/energy-bonds?sort_by=name&sort_direction=asc');

        $response->assertStatus(200);
        $this->assertEquals('Alpha Bond', $response->json('data.0.name'));
        $this->assertEquals('Beta Bond', $response->json('data.1.name'));
    }

    /** @test */
    public function it_can_paginate_results()
    {
        EnergyBond::factory()->count(25)->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/energy-bonds?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(25, $response->json('meta.total'));
        $this->assertEquals(3, $response->json('meta.last_page'));
    }
}
