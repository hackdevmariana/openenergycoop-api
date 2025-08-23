<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\ApiTestHelpers;

class VendorControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker, ApiTestHelpers;

    protected User $user;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->admin = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function it_can_list_vendors()
    {
        Vendor::factory()->count(5)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'name', 'legal_name', 'vendor_type', 'industry',
                        'is_active', 'is_verified', 'is_preferred', 'is_blacklisted',
                        'status', 'risk_level', 'compliance_status', 'rating',
                        'created_at', 'updated_at'
                    ]
                ],
                'meta' => [
                    'current_page', 'last_page', 'per_page', 'total',
                    'from', 'to', 'has_more_pages'
                ],
                'summary' => [
                    'total_vendors', 'active_vendors', 'verified_vendors',
                    'preferred_vendors', 'blacklisted_vendors'
                ]
            ]);
    }

    /** @test */
    public function it_can_filter_vendors_by_vendor_type()
    {
        Vendor::factory()->create(['vendor_type' => 'equipment_supplier']);
        Vendor::factory()->create(['vendor_type' => 'service_provider']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors?vendor_type=equipment_supplier');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_filter_vendors_by_industry()
    {
        Vendor::factory()->create(['industry' => 'Technology']);
        Vendor::factory()->create(['industry' => 'Healthcare']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors?industry=Technology');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_filter_vendors_by_country()
    {
        Vendor::factory()->create(['country' => 'Spain']);
        Vendor::factory()->create(['country' => 'France']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors?country=Spain');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_filter_vendors_by_status()
    {
        Vendor::factory()->create(['status' => 'active']);
        Vendor::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors?status=active');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_filter_vendors_by_risk_level()
    {
        Vendor::factory()->create(['risk_level' => 'high']);
        Vendor::factory()->create(['risk_level' => 'low']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors?risk_level=high');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_filter_vendors_by_compliance_status()
    {
        Vendor::factory()->create(['compliance_status' => 'compliant']);
        Vendor::factory()->create(['compliance_status' => 'non_compliant']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors?compliance_status=compliant');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_filter_vendors_by_boolean_fields()
    {
        Vendor::factory()->create(['is_active' => true]);
        Vendor::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors?is_active=true');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_search_vendors()
    {
        Vendor::factory()->create(['name' => 'Tech Solutions Inc']);
        Vendor::factory()->create(['name' => 'Healthcare Services']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors?search=Tech');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_sort_vendors()
    {
        Vendor::factory()->create(['name' => 'A Company']);
        Vendor::factory()->create(['name' => 'Z Company']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors?sort_by=name&sort_direction=asc');

        $response->assertStatus(200);
        $this->assertEquals('A Company', $response->json('data.0.name'));
    }

    /** @test */
    public function it_can_show_vendor()
    {
        $vendor = Vendor::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/vendors/{$vendor->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                    'legal_name' => $vendor->legal_name
                ]
            ]);
    }

    /** @test */
    public function it_can_store_vendor()
    {
        $vendorData = [
            'name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Legal Name',
            'vendor_type' => 'equipment_supplier',
            'industry' => 'Technology',
            'description' => 'Test description',
            'contact_person' => 'John Doe',
            'email' => 'test@vendor.com',
            'phone' => '+1234567890',
            'country' => 'Spain',
            'is_active' => true,
            'status' => 'pending',
            'risk_level' => 'medium',
            'compliance_status' => 'pending_review'
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/vendors', $vendorData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Vendor creado exitosamente',
                'data' => [
                    'name' => 'Test Vendor',
                    'legal_name' => 'Test Vendor Legal Name',
                    'vendor_type' => 'equipment_supplier'
                ]
            ]);

        $this->assertDatabaseHas('vendors', [
            'name' => 'Test Vendor',
            'email' => 'test@vendor.com'
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_storing()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/vendors', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'legal_name', 'vendor_type']);
    }

    /** @test */
    public function it_validates_unique_fields_when_storing()
    {
        Vendor::factory()->create(['email' => 'existing@vendor.com']);

        $vendorData = [
            'name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Legal Name',
            'vendor_type' => 'equipment_supplier',
            'email' => 'existing@vendor.com'
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/vendors', $vendorData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_can_update_vendor()
    {
        $vendor = Vendor::factory()->create(['name' => 'Old Name']);

        $updateData = [
            'name' => 'Updated Name',
            'industry' => 'Updated Industry'
        ];

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/vendors/{$vendor->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Vendor actualizado exitosamente',
                'data' => [
                    'name' => 'Updated Name',
                    'industry' => 'Updated Industry'
                ]
            ]);

        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id,
            'name' => 'Updated Name',
            'industry' => 'Updated Industry'
        ]);
    }

    /** @test */
    public function it_can_destroy_vendor()
    {
        $vendor = Vendor::factory()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/vendors/{$vendor->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Vendor eliminado exitosamente']);

        $this->assertDatabaseMissing('vendors', ['id' => $vendor->id]);
    }

    /** @test */
    public function it_can_get_vendor_statistics()
    {
        Vendor::factory()->count(3)->create(['is_active' => true]);
        Vendor::factory()->count(2)->create(['is_active' => false]);
        Vendor::factory()->count(2)->create(['is_verified' => true]);
        Vendor::factory()->count(1)->create(['is_preferred' => true]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'total_vendors' => 5,
                    'active_vendors' => 3,
                    'inactive_vendors' => 2,
                    'verified_vendors' => 2,
                    'unverified_vendors' => 3,
                    'preferred_vendors' => 1,
                    'non_preferred_vendors' => 4,
                    'blacklisted_vendors' => 0,
                    'non_blacklisted_vendors' => 5,
                    'high_risk_vendors' => 0,
                    'medium_risk_vendors' => 5,
                    'low_risk_vendors' => 0,
                    'compliant_vendors' => 0,
                    'non_compliant_vendors' => 0,
                    'needs_audit_vendors' => 5
                ]
            ]);
    }

    /** @test */
    public function it_can_get_vendor_types()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/vendor-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'equipment_supplier', 'service_provider', 'material_supplier',
                    'consultant', 'contractor', 'distributor', 'manufacturer',
                    'wholesaler', 'retailer', 'other'
                ]
            ]);
    }

    /** @test */
    public function it_can_get_risk_levels()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/risk-levels');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'minimal', 'low', 'medium', 'high', 'extreme'
                ]
            ]);
    }

    /** @test */
    public function it_can_get_compliance_statuses()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/compliance-statuses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'pending_review', 'under_review', 'compliant',
                    'needs_audit', 'non_compliant'
                ]
            ]);
    }

    /** @test */
    public function it_can_toggle_verified_status()
    {
        $vendor = Vendor::factory()->create(['is_verified' => false]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/v1/vendors/{$vendor->id}/toggle-verified");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Estado verificado alternado exitosamente',
                'data' => ['is_verified' => true]
            ]);

        $this->assertTrue($vendor->fresh()->is_verified);
    }

    /** @test */
    public function it_can_toggle_preferred_status()
    {
        $vendor = Vendor::factory()->create(['is_preferred' => false]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/v1/vendors/{$vendor->id}/toggle-preferred");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Estado preferido alternado exitosamente',
                'data' => ['is_preferred' => true]
            ]);

        $this->assertTrue($vendor->fresh()->is_preferred);
    }

    /** @test */
    public function it_can_duplicate_vendor()
    {
        $vendor = Vendor::factory()->create([
            'name' => 'Original Vendor',
            'is_active' => true,
            'is_verified' => true,
            'is_preferred' => true
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/api/v1/vendors/{$vendor->id}/duplicate");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Vendor duplicado exitosamente',
                'data' => [
                    'name' => 'Original Vendor (Copia)',
                    'is_active' => false,
                    'is_verified' => false,
                    'is_preferred' => false
                ]
            ]);

        $this->assertDatabaseHas('vendors', [
            'name' => 'Original Vendor (Copia)',
            'is_active' => false,
            'is_verified' => false,
            'is_preferred' => false
        ]);
    }

    /** @test */
    public function it_can_get_active_vendors()
    {
        Vendor::factory()->create(['is_active' => true]);
        Vendor::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/active');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_get_verified_vendors()
    {
        Vendor::factory()->create(['is_verified' => true]);
        Vendor::factory()->create(['is_verified' => false]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/verified');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_get_preferred_vendors()
    {
        Vendor::factory()->create(['is_preferred' => true]);
        Vendor::factory()->create(['is_preferred' => false]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/preferred');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_get_blacklisted_vendors()
    {
        Vendor::factory()->create(['is_blacklisted' => true]);
        Vendor::factory()->create(['is_blacklisted' => false]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/blacklisted');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_get_compliant_vendors()
    {
        Vendor::factory()->create(['compliance_status' => 'compliant']);
        Vendor::factory()->create(['compliance_status' => 'non_compliant']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/compliant');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_get_vendors_by_risk_level()
    {
        Vendor::factory()->create(['risk_level' => 'high']);
        Vendor::factory()->create(['risk_level' => 'low']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/by-risk-level/high');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_get_vendors_by_compliance_status()
    {
        Vendor::factory()->create(['compliance_status' => 'compliant']);
        Vendor::factory()->create(['compliance_status' => 'non_compliant']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/by-compliance-status/compliant');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_get_vendors_by_location()
    {
        Vendor::factory()->create(['country' => 'Spain']);
        Vendor::factory()->create(['country' => 'France']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/by-location/Spain');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_get_high_rating_vendors()
    {
        Vendor::factory()->create(['rating' => 4.5]);
        Vendor::factory()->create(['rating' => 3.0]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/high-rating');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/v1/vendors');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_paginates_results()
    {
        Vendor::factory()->count(25)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors?per_page=10');

        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'per_page' => 10,
                    'total' => 25,
                    'last_page' => 3
                ]
            ]);
    }

    /** @test */
    public function it_includes_relationships_when_requested()
    {
        $vendor = Vendor::factory()->create([
            'created_by' => $this->admin->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/vendors/{$vendor->id}?include=createdBy");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'created_by' => [
                        'id', 'name', 'email'
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_validates_cross_field_validation_rules()
    {
        $vendorData = [
            'name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Legal Name',
            'vendor_type' => 'equipment_supplier',
            'credit_limit' => 1000,
            'current_balance' => 2000 // Mayor que el límite de crédito
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/vendors', $vendorData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['credit_limit']);
    }

    /** @test */
    public function it_validates_date_relationships()
    {
        $vendorData = [
            'name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Legal Name',
            'vendor_type' => 'equipment_supplier',
            'contract_start_date' => '2024-12-31',
            'contract_end_date' => '2024-01-01' // Fecha anterior a la de inicio
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/vendors', $vendorData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['contract_end_date']);
    }

    /** @test */
    public function it_validates_boolean_consistency()
    {
        $vendorData = [
            'name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Legal Name',
            'vendor_type' => 'equipment_supplier',
            'is_preferred' => true,
            'is_blacklisted' => true // No puede ser preferido y estar en lista negra
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/vendors', $vendorData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['is_preferred']);
    }

    /** @test */
    public function it_protects_critical_fields_for_approved_vendors()
    {
        $vendor = Vendor::factory()->create([
            'approved_at' => now(),
            'vendor_type' => 'equipment_supplier'
        ]);

        $updateData = [
            'vendor_type' => 'service_provider' // Campo crítico
        ];

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/vendors/{$vendor->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vendor_type']);
    }

    /** @test */
    public function it_requires_justification_for_risk_reduction()
    {
        $vendor = Vendor::factory()->create(['risk_level' => 'high']);

        $updateData = [
            'risk_level' => 'low', // Reducción de riesgo
            'notes' => 'Short note' // Nota muy corta
        ];

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/vendors/{$vendor->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['notes']);
    }

    /** @test */
    public function it_requires_justification_for_compliance_improvement()
    {
        $vendor = Vendor::factory()->create(['compliance_status' => 'non_compliant']);

        $updateData = [
            'compliance_status' => 'compliant', // Mejora de cumplimiento
            'notes' => 'Short note' // Nota muy corta
        ];

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/vendors/{$vendor->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['notes']);
    }
}
