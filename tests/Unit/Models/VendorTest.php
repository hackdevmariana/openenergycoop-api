<?php

namespace Tests\Unit\Models;

use App\Models\Vendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'name', 'legal_name', 'tax_id', 'registration_number', 'vendor_type',
            'industry', 'description', 'contact_person', 'email', 'phone', 'website',
            'address', 'city', 'state', 'postal_code', 'country', 'latitude', 'longitude',
            'payment_terms', 'credit_limit', 'current_balance', 'currency', 'tax_rate',
            'discount_rate', 'rating', 'is_active', 'is_verified', 'is_preferred',
            'is_blacklisted', 'contract_start_date', 'contract_end_date', 'contract_terms',
            'insurance_coverage', 'certifications', 'licenses', 'performance_metrics',
            'quality_standards', 'delivery_terms', 'warranty_terms', 'return_policy',
            'notes', 'tags', 'logo', 'documents', 'bank_account', 'payment_methods',
            'contact_history', 'status', 'risk_level', 'compliance_status', 'audit_frequency',
            'last_audit_date', 'next_audit_date', 'financial_stability', 'market_reputation',
            'competitor_analysis', 'strategic_importance', 'dependencies', 'alternatives',
            'cost_benefit_analysis', 'performance_reviews', 'improvement_plans',
            'escalation_procedures', 'created_by', 'approved_by', 'approved_at'
        ];

        $vendor = new Vendor();
        $this->assertEquals($fillable, $vendor->getFillable());
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $vendor = new Vendor();
        $casts = $vendor->getCasts();

        $this->assertArrayHasKey('is_active', $casts);
        $this->assertArrayHasKey('is_verified', $casts);
        $this->assertArrayHasKey('is_preferred', $casts);
        $this->assertArrayHasKey('is_blacklisted', $casts);
        $this->assertArrayHasKey('credit_limit', $casts);
        $this->assertArrayHasKey('current_balance', $casts);
        $this->assertArrayHasKey('tax_rate', $casts);
        $this->assertArrayHasKey('discount_rate', $casts);
        $this->assertArrayHasKey('rating', $casts);
        $this->assertArrayHasKey('latitude', $casts);
        $this->assertArrayHasKey('longitude', $casts);
        $this->assertArrayHasKey('audit_frequency', $casts);
        $this->assertArrayHasKey('contract_start_date', $casts);
        $this->assertArrayHasKey('contract_end_date', $casts);
        $this->assertArrayHasKey('last_audit_date', $casts);
        $this->assertArrayHasKey('next_audit_date', $casts);
        $this->assertArrayHasKey('approved_at', $casts);
        $this->assertArrayHasKey('contract_terms', $casts);
        $this->assertArrayHasKey('insurance_coverage', $casts);
        $this->assertArrayHasKey('certifications', $casts);
        $this->assertArrayHasKey('licenses', $casts);
        $this->assertArrayHasKey('performance_metrics', $casts);
        $this->assertArrayHasKey('quality_standards', $casts);
        $this->assertArrayHasKey('delivery_terms', $casts);
        $this->assertArrayHasKey('warranty_terms', $casts);
        $this->assertArrayHasKey('return_policy', $casts);
        $this->assertArrayHasKey('tags', $casts);
        $this->assertArrayHasKey('documents', $casts);
        $this->assertArrayHasKey('bank_account', $casts);
        $this->assertArrayHasKey('payment_methods', $casts);
        $this->assertArrayHasKey('contact_history', $casts);
        $this->assertArrayHasKey('financial_stability', $casts);
        $this->assertArrayHasKey('market_reputation', $casts);
        $this->assertArrayHasKey('competitor_analysis', $casts);
        $this->assertArrayHasKey('strategic_importance', $casts);
        $this->assertArrayHasKey('dependencies', $casts);
        $this->assertArrayHasKey('alternatives', $casts);
        $this->assertArrayHasKey('cost_benefit_analysis', $casts);
        $this->assertArrayHasKey('performance_reviews', $casts);
        $this->assertArrayHasKey('improvement_plans', $casts);
        $this->assertArrayHasKey('escalation_procedures', $casts);
    }

    /** @test */
    public function it_has_vendor_types_enum()
    {
        $vendorTypes = Vendor::getVendorTypes();
        
        $this->assertIsArray($vendorTypes);
        $this->assertArrayHasKey('equipment_supplier', $vendorTypes);
        $this->assertArrayHasKey('service_provider', $vendorTypes);
        $this->assertArrayHasKey('material_supplier', $vendorTypes);
        $this->assertArrayHasKey('consultant', $vendorTypes);
        $this->assertArrayHasKey('contractor', $vendorTypes);
        $this->assertArrayHasKey('distributor', $vendorTypes);
        $this->assertArrayHasKey('manufacturer', $vendorTypes);
        $this->assertArrayHasKey('wholesaler', $vendorTypes);
        $this->assertArrayHasKey('retailer', $vendorTypes);
        $this->assertArrayHasKey('other', $vendorTypes);
    }

    /** @test */
    public function it_has_statuses_enum()
    {
        $statuses = Vendor::getStatuses();
        
        $this->assertIsArray($statuses);
        $this->assertArrayHasKey('pending', $statuses);
        $this->assertArrayHasKey('active', $statuses);
        $this->assertArrayHasKey('suspended', $statuses);
        $this->assertArrayHasKey('terminated', $statuses);
        $this->assertArrayHasKey('under_review', $statuses);
    }

    /** @test */
    public function it_has_risk_levels_enum()
    {
        $riskLevels = Vendor::getRiskLevels();
        
        $this->assertIsArray($riskLevels);
        $this->assertArrayHasKey('minimal', $riskLevels);
        $this->assertArrayHasKey('low', $riskLevels);
        $this->assertArrayHasKey('medium', $riskLevels);
        $this->assertArrayHasKey('high', $riskLevels);
        $this->assertArrayHasKey('extreme', $riskLevels);
    }

    /** @test */
    public function it_has_compliance_statuses_enum()
    {
        $complianceStatuses = Vendor::getComplianceStatuses();
        
        $this->assertIsArray($complianceStatuses);
        $this->assertArrayHasKey('pending_review', $complianceStatuses);
        $this->assertArrayHasKey('under_review', $complianceStatuses);
        $this->assertArrayHasKey('compliant', $complianceStatuses);
        $this->assertArrayHasKey('needs_audit', $complianceStatuses);
        $this->assertArrayHasKey('non_compliant', $complianceStatuses);
    }

    /** @test */
    public function it_has_relationships()
    {
        $vendor = Vendor::factory()->create();
        
        $this->assertInstanceOf(User::class, $vendor->createdBy);
        $this->assertInstanceOf(User::class, $vendor->approvedBy);
    }

    /** @test */
    public function it_has_scopes()
    {
        // Active scope
        Vendor::factory()->create(['is_active' => true]);
        Vendor::factory()->create(['is_active' => false]);
        
        $this->assertEquals(1, Vendor::active()->count());
        
        // Verified scope
        Vendor::factory()->create(['is_verified' => true]);
        Vendor::factory()->create(['is_verified' => false]);
        
        $this->assertEquals(1, Vendor::verified()->count());
        
        // Preferred scope
        Vendor::factory()->create(['is_preferred' => true]);
        Vendor::factory()->create(['is_preferred' => false]);
        
        $this->assertEquals(1, Vendor::preferred()->count());
        
        // Blacklisted scope
        Vendor::factory()->create(['is_blacklisted' => true]);
        Vendor::factory()->create(['is_blacklisted' => false]);
        
        $this->assertEquals(1, Vendor::blacklisted()->count());
        
        // Compliant scope
        Vendor::factory()->create(['compliance_status' => 'compliant']);
        Vendor::factory()->create(['compliance_status' => 'non_compliant']);
        
        $this->assertEquals(1, Vendor::compliant()->count());
        
        // NonCompliant scope
        $this->assertEquals(1, Vendor::nonCompliant()->count());
        
        // NeedsAudit scope
        Vendor::factory()->create(['compliance_status' => 'needs_audit']);
        $this->assertEquals(1, Vendor::needsAudit()->count());
        
        // HighRating scope
        Vendor::factory()->create(['rating' => 4.5]);
        Vendor::factory()->create(['rating' => 3.0]);
        
        $this->assertEquals(1, Vendor::highRating()->count());
    }

    /** @test */
    public function it_has_boolean_checks()
    {
        $vendor = Vendor::factory()->create([
            'is_active' => true,
            'is_verified' => true,
            'is_preferred' => true,
            'is_blacklisted' => false
        ]);
        
        $this->assertTrue($vendor->isActive());
        $this->assertTrue($vendor->isVerified());
        $this->assertTrue($vendor->isPreferred());
        $this->assertFalse($vendor->isBlacklisted());
    }

    /** @test */
    public function it_can_calculate_available_credit()
    {
        $vendor = Vendor::factory()->create([
            'credit_limit' => 10000,
            'current_balance' => 3000
        ]);
        
        $this->assertEquals(7000, $vendor->getAvailableCredit());
    }

    /** @test */
    public function it_can_calculate_credit_utilization()
    {
        $vendor = Vendor::factory()->create([
            'credit_limit' => 10000,
            'current_balance' => 3000
        ]);
        
        $this->assertEquals(30.0, $vendor->getCreditUtilization());
    }

    /** @test */
    public function it_can_get_vendor_type_label()
    {
        $vendor = Vendor::factory()->create(['vendor_type' => 'equipment_supplier']);
        
        $this->assertEquals('Proveedor de Equipos', $vendor->getVendorTypeLabel());
    }

    /** @test */
    public function it_can_get_status_label()
    {
        $vendor = Vendor::factory()->create(['status' => 'active']);
        
        $this->assertEquals('Activo', $vendor->getStatusLabel());
    }

    /** @test */
    public function it_can_get_risk_level_label()
    {
        $vendor = Vendor::factory()->create(['risk_level' => 'medium']);
        
        $this->assertEquals('Medio', $vendor->getRiskLevelLabel());
    }

    /** @test */
    public function it_can_get_compliance_status_label()
    {
        $vendor = Vendor::factory()->create(['compliance_status' => 'compliant']);
        
        $this->assertEquals('Cumple', $vendor->getComplianceStatusLabel());
    }

    /** @test */
    public function it_can_get_status_badge_class()
    {
        $vendor = Vendor::factory()->create(['status' => 'active']);
        
        $this->assertEquals('badge badge-success', $vendor->getStatusBadgeClass());
    }

    /** @test */
    public function it_can_get_risk_level_badge_class()
    {
        $vendor = Vendor::factory()->create(['risk_level' => 'high']);
        
        $this->assertEquals('badge badge-warning', $vendor->getRiskLevelBadgeClass());
    }

    /** @test */
    public function it_can_get_compliance_status_badge_class()
    {
        $vendor = Vendor::factory()->create(['compliance_status' => 'compliant']);
        
        $this->assertEquals('badge badge-success', $vendor->getComplianceStatusBadgeClass());
    }

    /** @test */
    public function it_can_get_rating_stars()
    {
        $vendor = Vendor::factory()->create(['rating' => 4.5]);
        
        $stars = $vendor->getRatingStars();
        $this->assertStringContainsString('★★★★☆', $stars);
        $this->assertStringContainsString('(4.5)', $stars);
    }

    /** @test */
    public function it_can_get_contract_status()
    {
        $vendor = Vendor::factory()->create([
            'contract_start_date' => now()->subDays(30),
            'contract_end_date' => now()->addDays(30)
        ]);
        
        $this->assertEquals('Activo', $vendor->getContractStatus());
    }

    /** @test */
    public function it_can_get_contract_days_remaining()
    {
        $vendor = Vendor::factory()->create([
            'contract_end_date' => now()->addDays(15)
        ]);
        
        $this->assertEquals(15, $vendor->getContractDaysRemaining());
    }

    /** @test */
    public function it_can_get_audit_status()
    {
        $vendor = Vendor::factory()->create([
            'next_audit_date' => now()->addDays(20)
        ]);
        
        $this->assertEquals('Próxima', $vendor->getAuditStatus());
    }

    /** @test */
    public function it_can_get_days_until_audit()
    {
        $vendor = Vendor::factory()->create([
            'next_audit_date' => now()->addDays(25)
        ]);
        
        $this->assertEquals(25, $vendor->getDaysUntilAudit());
    }

    /** @test */
    public function it_can_get_last_contact_date()
    {
        $vendor = Vendor::factory()->create([
            'contact_history' => [
                ['date' => '2024-01-15', 'type' => 'email'],
                ['date' => '2024-01-20', 'type' => 'phone']
            ]
        ]);
        
        $this->assertEquals('2024-01-20', $vendor->getLastContactDate());
    }

    /** @test */
    public function it_can_toggle_verified_status()
    {
        $vendor = Vendor::factory()->create(['is_verified' => false]);
        
        $vendor->toggleVerified();
        $this->assertTrue($vendor->is_verified);
        
        $vendor->toggleVerified();
        $this->assertFalse($vendor->is_verified);
    }

    /** @test */
    public function it_can_toggle_preferred_status()
    {
        $vendor = Vendor::factory()->create(['is_preferred' => false]);
        
        $vendor->togglePreferred();
        $this->assertTrue($vendor->is_preferred);
        
        $vendor->togglePreferred();
        $this->assertFalse($vendor->is_preferred);
    }

    /** @test */
    public function it_can_toggle_blacklisted_status()
    {
        $vendor = Vendor::factory()->create(['is_blacklisted' => false]);
        
        $vendor->toggleBlacklisted();
        $this->assertTrue($vendor->is_blacklisted);
        
        $vendor->toggleBlacklisted();
        $this->assertFalse($vendor->is_blacklisted);
    }

    /** @test */
    public function it_can_duplicate()
    {
        $vendor = Vendor::factory()->create([
            'name' => 'Original Vendor',
            'is_active' => true,
            'is_verified' => true,
            'is_preferred' => true
        ]);
        
        $duplicate = $vendor->duplicate();
        
        $this->assertNotEquals($vendor->id, $duplicate->id);
        $this->assertEquals('Original Vendor (Copia)', $duplicate->name);
        $this->assertFalse($duplicate->is_active);
        $this->assertFalse($duplicate->is_verified);
        $this->assertFalse($duplicate->is_preferred);
        $this->assertNull($duplicate->approved_at);
        $this->assertNull($duplicate->approved_by);
    }

    /** @test */
    public function it_can_scope_by_location()
    {
        Vendor::factory()->create(['country' => 'Spain']);
        Vendor::factory()->create(['country' => 'France']);
        
        $this->assertEquals(1, Vendor::byLocation('Spain')->count());
        $this->assertEquals(1, Vendor::byLocation('France')->count());
    }

    /** @test */
    public function it_can_scope_by_risk_level()
    {
        Vendor::factory()->create(['risk_level' => 'high']);
        Vendor::factory()->create(['risk_level' => 'medium']);
        
        $this->assertEquals(1, Vendor::byRiskLevel('high')->count());
        $this->assertEquals(1, Vendor::byRiskLevel('medium')->count());
    }

    /** @test */
    public function it_can_scope_by_compliance_status()
    {
        Vendor::factory()->create(['compliance_status' => 'compliant']);
        Vendor::factory()->create(['compliance_status' => 'non_compliant']);
        
        $this->assertEquals(1, Vendor::byComplianceStatus('compliant')->count());
        $this->assertEquals(1, Vendor::byComplianceStatus('non_compliant')->count());
    }

    /** @test */
    public function it_can_scope_by_vendor_type()
    {
        Vendor::factory()->create(['vendor_type' => 'equipment_supplier']);
        Vendor::factory()->create(['vendor_type' => 'service_provider']);
        
        $this->assertEquals(1, Vendor::byVendorType('equipment_supplier')->count());
        $this->assertEquals(1, Vendor::byVendorType('service_provider')->count());
    }

    /** @test */
    public function it_can_scope_by_industry()
    {
        Vendor::factory()->create(['industry' => 'Technology']);
        Vendor::factory()->create(['industry' => 'Healthcare']);
        
        $this->assertEquals(1, Vendor::byIndustry('Technology')->count());
        $this->assertEquals(1, Vendor::byIndustry('Healthcare')->count());
    }

    /** @test */
    public function it_can_scope_by_rating_range()
    {
        Vendor::factory()->create(['rating' => 4.5]);
        Vendor::factory()->create(['rating' => 3.0]);
        Vendor::factory()->create(['rating' => 2.0]);
        
        $this->assertEquals(2, Vendor::byRatingRange(3.0, 5.0)->count());
        $this->assertEquals(1, Vendor::byRatingRange(1.0, 2.5)->count());
    }

    /** @test */
    public function it_can_scope_by_credit_limit_range()
    {
        Vendor::factory()->create(['credit_limit' => 50000]);
        Vendor::factory()->create(['credit_limit' => 100000]);
        Vendor::factory()->create(['credit_limit' => 200000]);
        
        $this->assertEquals(2, Vendor::byCreditLimitRange(50000, 150000)->count());
        $this->assertEquals(1, Vendor::byCreditLimitRange(150000, 300000)->count());
    }

    /** @test */
    public function it_can_scope_by_contract_expiry()
    {
        Vendor::factory()->create(['contract_end_date' => now()->addDays(30)]);
        Vendor::factory()->create(['contract_end_date' => now()->addDays(90)]);
        Vendor::factory()->create(['contract_end_date' => now()->addDays(180)]);
        
        $this->assertEquals(1, Vendor::contractExpiringSoon(60)->count());
        $this->assertEquals(2, Vendor::contractExpiringSoon(120)->count());
    }

    /** @test */
    public function it_can_scope_by_audit_due()
    {
        Vendor::factory()->create(['next_audit_date' => now()->addDays(15)]);
        Vendor::factory()->create(['next_audit_date' => now()->addDays(45)]);
        Vendor::factory()->create(['next_audit_date' => now()->addDays(90)]);
        
        $this->assertEquals(1, Vendor::auditDueSoon(30)->count());
        $this->assertEquals(2, Vendor::auditDueSoon(60)->count());
    }

    /** @test */
    public function it_can_scope_by_creation_date()
    {
        Vendor::factory()->create(['created_at' => now()->subDays(30)]);
        Vendor::factory()->create(['created_at' => now()->subDays(90)]);
        Vendor::factory()->create(['created_at' => now()->subDays(180)]);
        
        $this->assertEquals(2, Vendor::createdAfter(now()->subDays(100))->count());
        $this->assertEquals(1, Vendor::createdBefore(now()->subDays(100))->count());
    }

    /** @test */
    public function it_can_scope_by_approval_date()
    {
        Vendor::factory()->create(['approved_at' => now()->subDays(30)]);
        Vendor::factory()->create(['approved_at' => now()->subDays(90)]);
        Vendor::factory()->create(['approved_at' => now()->subDays(180)]);
        
        $this->assertEquals(2, Vendor::approvedAfter(now()->subDays(100))->count());
        $this->assertEquals(1, Vendor::approvedBefore(now()->subDays(100))->count());
    }
}
