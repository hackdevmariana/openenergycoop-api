<?php

namespace Tests\Feature\Api\V1;

use App\Models\Vendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VendorControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $vendor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->vendor = Vendor::factory()->create();
    }

    public function test_index_returns_paginated_vendors()
    {
        Vendor::factory()->count(15)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'name', 'legal_name', 'vendor_type', 'contact_person',
                        'email', 'is_active', 'status', 'risk_level', 'created_at', 'updated_at'
                    ]
                ],
                'links', 'meta'
            ]);
    }

    public function test_index_with_filters()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors?vendor_type=supplier&industry=energy&status=active&risk_level=low&is_active=true');

        $response->assertStatus(200);
    }

    public function test_index_with_search()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors?search=test');

        $response->assertStatus(200);
    }

    public function test_index_with_sorting()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors?sort=name&order=desc');

        $response->assertStatus(200);
    }

    public function test_index_with_pagination_limit()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors?per_page=5');

        $response->assertStatus(200);
    }

    public function test_store_creates_new_vendor()
    {
        $data = [
            'name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Legal',
            'vendor_type' => 'supplier',
            'contact_person' => 'John Doe',
            'email' => 'john@testvendor.com',
            'phone' => '+1234567890',
            'industry' => 'energy',
            'is_active' => true,
            'status' => 'pending',
            'risk_level' => 'medium',
            'compliance_status' => 'pending_review',
            'rating' => 4.0,
            'credit_limit' => 10000,
            'current_balance' => 0,
            'tax_rate' => 21.0,
            'discount_rate' => 5.0
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/vendors', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'legal_name', 'vendor_type', 'contact_person',
                    'email', 'phone', 'industry', 'is_active', 'status', 'risk_level',
                    'compliance_status', 'rating', 'credit_limit', 'current_balance',
                    'tax_rate', 'discount_rate', 'created_at', 'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('vendors', [
            'name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Legal',
            'vendor_type' => 'supplier',
            'email' => 'john@testvendor.com'
        ]);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/vendors', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'legal_name', 'vendor_type', 'contact_person', 'email']);
    }

    public function test_store_validates_enum_values()
    {
        $data = [
            'name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Legal',
            'vendor_type' => 'invalid_type',
            'contact_person' => 'John Doe',
            'email' => 'john@testvendor.com',
            'status' => 'invalid_status',
            'risk_level' => 'invalid_risk',
            'compliance_status' => 'invalid_compliance'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/vendors', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vendor_type', 'status', 'risk_level', 'compliance_status']);
    }

    public function test_store_validates_cross_field_consistency()
    {
        $data = [
            'name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Legal',
            'vendor_type' => 'supplier',
            'contact_person' => 'John Doe',
            'email' => 'john@testvendor.com',
            'current_balance' => 5000,
            'credit_limit' => 1000
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/vendors', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_balance']);
    }

    public function test_show_returns_vendor()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/vendors/{$this->vendor->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'legal_name', 'vendor_type', 'contact_person',
                    'email', 'is_active', 'status', 'risk_level', 'created_at', 'updated_at'
                ]
            ]);
    }

    public function test_show_returns_404_for_nonexistent_vendor()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/99999');

        $response->assertStatus(404);
    }

    public function test_update_modifies_vendor()
    {
        $data = [
            'name' => 'Updated Vendor',
            'legal_name' => 'Updated Vendor Legal',
            'rating' => 4.5,
            'is_preferred' => true
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/vendors/{$this->vendor->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Updated Vendor',
                    'legal_name' => 'Updated Vendor Legal',
                    'rating' => 4.5,
                    'is_preferred' => true
                ]
            ]);

        $this->assertDatabaseHas('vendors', [
            'id' => $this->vendor->id,
            'name' => 'Updated Vendor',
            'rating' => 4.5
        ]);
    }

    public function test_update_validates_cross_field_consistency()
    {
        $data = [
            'current_balance' => 5000,
            'credit_limit' => 1000
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/vendors/{$this->vendor->id}", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_balance']);
    }

    public function test_destroy_deletes_vendor()
    {
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/vendors/{$this->vendor->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('vendors', [
            'id' => $this->vendor->id
        ]);
    }

    public function test_statistics_returns_vendor_statistics()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_vendors', 'active_vendors', 'inactive_vendors', 'verified_vendors',
                    'preferred_vendors', 'blacklisted_vendors', 'approved_vendors',
                    'pending_approval_vendors', 'high_risk_vendors', 'compliant_vendors',
                    'non_compliant_vendors', 'needs_audit_vendors', 'contract_expiring_soon_vendors',
                    'expired_contract_vendors'
                ]
            ]);
    }

    public function test_vendor_types_returns_vendor_types()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/vendor-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['value', 'label', 'count']
                ]
            ]);
    }

    public function test_statuses_returns_statuses()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/statuses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['value', 'label', 'count']
                ]
            ]);
    }

    public function test_risk_levels_returns_risk_levels()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/risk-levels');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['value', 'label', 'count']
                ]
            ]);
    }

    public function test_compliance_statuses_returns_compliance_statuses()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/compliance-statuses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['value', 'label', 'count']
                ]
            ]);
    }

    public function test_toggle_active_alternates_vendor_status()
    {
        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/vendors/{$this->vendor->id}/toggle-active");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['is_active']
            ]);

        $this->assertDatabaseHas('vendors', [
            'id' => $this->vendor->id,
            'is_active' => !$this->vendor->is_active
        ]);
    }

    public function test_duplicate_creates_copy_of_vendor()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/vendors/{$this->vendor->id}/duplicate");

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'vendor_type', 'contact_person', 'email'
                ]
            ]);

        $this->assertDatabaseCount('vendors', 2);
    }

    public function test_active_returns_active_vendors()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/active');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'is_active']
                ]
            ]);
    }

    public function test_verified_returns_verified_vendors()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/verified');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'is_verified']
                ]
            ]);
    }

    public function test_preferred_returns_preferred_vendors()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/preferred');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'is_preferred']
                ]
            ]);
    }

    public function test_high_risk_returns_high_risk_vendors()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/high-risk');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'risk_level']
                ]
            ]);
    }

    public function test_needs_audit_returns_vendors_needing_audit()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/needs-audit');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'next_audit_date']
                ]
            ]);
    }

    public function test_contract_expiring_returns_vendors_with_expiring_contracts()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/contract-expiring');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'contract_end_date']
                ]
            ]);
    }

    public function test_by_type_returns_vendors_by_type()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/by-type/supplier');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'vendor_type']
                ]
            ]);
    }

    public function test_by_industry_returns_vendors_by_industry()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/by-industry/energy');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'industry']
                ]
            ]);
    }

    public function test_by_location_returns_vendors_by_location()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/by-location?country=Spain');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'country']
                ]
            ]);
    }

    public function test_by_rating_returns_vendors_by_rating()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vendors/by-rating/4.0');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'rating']
                ]
            ]);
    }

    public function test_requires_authentication()
    {
        $response = $this->getJson('/api/v1/vendors');
        $response->assertStatus(401);
    }

    public function test_logs_activity_on_create()
    {
        $data = [
            'name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Legal',
            'vendor_type' => 'supplier',
            'contact_person' => 'John Doe',
            'email' => 'john@testvendor.com',
            'industry' => 'energy',
            'is_active' => true,
            'status' => 'pending',
            'risk_level' => 'medium',
            'compliance_status' => 'pending_review'
        ];

        $this->actingAs($this->user)
            ->postJson('/api/v1/vendors', $data);

        // Verificar que se registró la actividad (si tienes un sistema de logging)
        // $this->assertDatabaseHas('activity_log', [...]);
    }

    public function test_validates_contract_dates()
    {
        $data = [
            'name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Legal',
            'vendor_type' => 'supplier',
            'contact_person' => 'John Doe',
            'email' => 'john@testvendor.com',
            'contract_start_date' => now()->addMonth(),
            'contract_end_date' => now()->subMonth()
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/vendors', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['contract_end_date']);
    }

    public function test_validates_audit_dates()
    {
        $data = [
            'name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Legal',
            'vendor_type' => 'supplier',
            'contact_person' => 'John Doe',
            'email' => 'john@testvendor.com',
            'last_audit_date' => now()->addMonth(),
            'next_audit_date' => now()->subMonth()
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/vendors', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['next_audit_date']);
    }

    public function test_validates_credit_limits()
    {
        $data = [
            'name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Legal',
            'vendor_type' => 'supplier',
            'contact_person' => 'John Doe',
            'email' => 'john@testvendor.com',
            'credit_limit' => 1000,
            'current_balance' => 2000
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/vendors', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_balance']);
    }

    public function test_validates_tax_rates()
    {
        $data = [
            'name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Legal',
            'vendor_type' => 'supplier',
            'contact_person' => 'John Doe',
            'email' => 'john@testvendor.com',
            'tax_rate' => 150.0
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/vendors', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tax_rate']);
    }

    public function test_validates_discount_rates()
    {
        $data = [
            'name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Legal',
            'vendor_type' => 'supplier',
            'contact_person' => 'John Doe',
            'email' => 'john@testvendor.com',
            'discount_rate' => 50.0
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/vendors', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['discount_rate']);
    }

    public function test_validates_rating_range()
    {
        $data = [
            'name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Legal',
            'vendor_type' => 'supplier',
            'contact_person' => 'John Doe',
            'email' => 'john@testvendor.com',
            'rating' => 6.0
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/vendors', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    public function test_validates_coordinates()
    {
        $data = [
            'name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Legal',
            'vendor_type' => 'supplier',
            'contact_person' => 'John Doe',
            'email' => 'john@testvendor.com',
            'latitude' => 100.0,
            'longitude' => 200.0
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/vendors', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    public function test_validates_contact_information()
    {
        $data = [
            'name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Legal',
            'vendor_type' => 'supplier',
            'contact_person' => 'John Doe'
            // Sin email ni teléfono
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/vendors', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_validates_location_information()
    {
        $data = [
            'name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Legal',
            'vendor_type' => 'supplier',
            'contact_person' => 'John Doe',
            'email' => 'john@testvendor.com'
            // Sin dirección, ciudad ni país
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/vendors', $data);

        $response->assertStatus(422);
    }

    public function test_validates_contract_duration()
    {
        $data = [
            'name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Legal',
            'vendor_type' => 'supplier',
            'contact_person' => 'John Doe',
            'email' => 'john@testvendor.com',
            'contract_start_date' => now()->subYears(6),
            'contract_end_date' => now()->addYears(6)
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/vendors', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['contract_end_date']);
    }

    public function test_validates_audit_frequency()
    {
        $data = [
            'name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Legal',
            'vendor_type' => 'supplier',
            'contact_person' => 'John Doe',
            'email' => 'john@testvendor.com',
            'audit_frequency' => 400
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/vendors', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['audit_frequency']);
    }
}
