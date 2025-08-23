<?php

namespace Tests\Unit\Models;

use App\Models\Vendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes()
    {
        $fillable = [
            'name', 'legal_name', 'tax_id', 'registration_number', 'vendor_type',
            'industry', 'description', 'contact_person', 'email', 'phone', 'website',
            'address', 'city', 'state', 'postal_code', 'country', 'latitude',
            'longitude', 'payment_terms', 'credit_limit', 'current_balance',
            'currency', 'tax_rate', 'discount_rate', 'rating', 'is_active',
            'is_verified', 'is_preferred', 'is_blacklisted', 'contract_start_date',
            'contract_end_date', 'contract_terms', 'insurance_coverage',
            'certifications', 'licenses', 'performance_metrics', 'quality_standards',
            'delivery_terms', 'warranty_terms', 'return_policy', 'notes', 'tags',
            'logo', 'documents', 'bank_account', 'payment_methods', 'contact_history',
            'created_by', 'approved_by', 'approved_at', 'status', 'risk_level',
            'compliance_status', 'audit_frequency', 'last_audit_date',
            'next_audit_date', 'financial_stability', 'market_reputation',
            'competitor_analysis', 'strategic_importance', 'dependencies',
            'alternatives', 'cost_benefit_analysis', 'performance_reviews',
            'improvement_plans', 'escalation_procedures'
        ];

        $this->assertEquals($fillable, (new Vendor())->getFillable());
    }

    public function test_casts()
    {
        $casts = [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'credit_limit' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'discount_rate' => 'decimal:2',
            'rating' => 'decimal:1',
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'is_preferred' => 'boolean',
            'is_blacklisted' => 'boolean',
            'contract_start_date' => 'date',
            'contract_end_date' => 'date',
            'contract_terms' => 'array',
            'insurance_coverage' => 'array',
            'certifications' => 'array',
            'licenses' => 'array',
            'performance_metrics' => 'array',
            'quality_standards' => 'array',
            'delivery_terms' => 'array',
            'warranty_terms' => 'array',
            'return_policy' => 'array',
            'tags' => 'array',
            'documents' => 'array',
            'bank_account' => 'array',
            'payment_methods' => 'array',
            'contact_history' => 'array',
            'approved_at' => 'datetime',
            'audit_frequency' => 'integer',
            'last_audit_date' => 'date',
            'next_audit_date' => 'date',
            'financial_stability' => 'array',
            'market_reputation' => 'array',
            'competitor_analysis' => 'array',
            'strategic_importance' => 'array',
            'dependencies' => 'array',
            'alternatives' => 'array',
            'cost_benefit_analysis' => 'array',
            'performance_reviews' => 'array',
            'improvement_plans' => 'array',
            'escalation_procedures' => 'array',
        ];

        $this->assertEquals($casts, (new Vendor())->getCasts());
    }

    public function test_static_enum_methods()
    {
        $this->assertIsArray(Vendor::getVendorTypes());
        $this->assertIsArray(Vendor::getStatuses());
        $this->assertIsArray(Vendor::getRiskLevels());
        $this->assertIsArray(Vendor::getComplianceStatuses());
    }

    public function test_relationships()
    {
        $vendor = Vendor::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $vendor->country());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $vendor->state());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $vendor->city());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $vendor->maintenanceTasks());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $vendor->maintenanceSchedules());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $vendor->createdBy());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $vendor->approvedBy());
    }

    public function test_scopes()
    {
        $vendor = Vendor::factory()->create(['is_active' => true]);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, Vendor::active());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, Vendor::verified());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, Vendor::preferred());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, Vendor::blacklisted());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, Vendor::byType('supplier'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, Vendor::byIndustry('energy'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, Vendor::byStatus('active'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, Vendor::byRiskLevel('low'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, Vendor::byComplianceStatus('compliant'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, Vendor::byRating(4.0));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, Vendor::highRating());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, Vendor::byLocation('Spain'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, Vendor::byContractStatus('active'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, Vendor::byPaymentTerms('30 days'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, Vendor::byCreditLimit(1000));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, Vendor::approved());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, Vendor::pendingApproval());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, Vendor::highRisk());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, Vendor::compliant());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, Vendor::nonCompliant());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, Vendor::needsAudit());
    }

    public function test_boolean_status_checks()
    {
        $vendor = Vendor::factory()->create(['is_active' => true]);
        
        $this->assertTrue($vendor->isActive());
        $this->assertFalse($vendor->isVerified());
        $this->assertFalse($vendor->isPreferred());
        $this->assertFalse($vendor->isBlacklisted());
        $this->assertFalse($vendor->isApproved());
    }

    public function test_vendor_type_checks()
    {
        $vendor = Vendor::factory()->create(['vendor_type' => 'supplier']);
        
        $this->assertTrue($vendor->isSupplier());
        $this->assertFalse($vendor->isServiceProvider());
        $this->assertFalse($vendor->isContractor());
        $this->assertFalse($vendor->isConsultant());
        $this->assertFalse($vendor->isManufacturer());
        $this->assertFalse($vendor->isDistributor());
        $this->assertFalse($vendor->isWholesaler());
        $this->assertFalse($vendor->isRetailer());
        $this->assertFalse($vendor->isMaintenance());
        $this->assertFalse($vendor->isItServices());
        $this->assertFalse($vendor->isFinancial());
        $this->assertFalse($vendor->isInsurance());
        $this->assertFalse($vendor->isLegal());
        $this->assertFalse($vendor->isMarketing());
        $this->assertFalse($vendor->isTransportation());
        $this->assertFalse($vendor->isWasteManagement());
        $this->assertFalse($vendor->isSecurity());
        $this->assertFalse($vendor->isCleaning());
        $this->assertFalse($vendor->isCatering());
        $this->assertFalse($vendor->isOther());
    }

    public function test_risk_level_checks()
    {
        $vendor = Vendor::factory()->create(['risk_level' => 'high']);
        
        $this->assertTrue($vendor->isHighRisk());
        $this->assertFalse($vendor->isLowRisk());
        $this->assertFalse($vendor->isMediumRisk());
        $this->assertFalse($vendor->isExtremeRisk());
    }

    public function test_compliance_status_checks()
    {
        $vendor = Vendor::factory()->create(['compliance_status' => 'compliant']);
        
        $this->assertTrue($vendor->isCompliant());
        $this->assertFalse($vendor->isNonCompliant());
        $this->assertFalse($vendor->isPendingReview());
        $this->assertFalse($vendor->isUnderInvestigation());
        $this->assertFalse($vendor->isComplianceApproved());
        $this->assertFalse($vendor->isComplianceRejected());
    }

    public function test_contract_methods()
    {
        $vendor = Vendor::factory()->create([
            'contract_start_date' => now()->subMonth(),
            'contract_end_date' => now()->addMonth()
        ]);

        $this->assertTrue($vendor->hasActiveContract());
        $this->assertFalse($vendor->isContractExpired());
        $this->assertFalse($vendor->isContractExpiringSoon());
        $this->assertGreaterThan(0, $vendor->getDaysUntilContractExpiry());
    }

    public function test_credit_methods()
    {
        $vendor = Vendor::factory()->create([
            'credit_limit' => 1000,
            'current_balance' => 300
        ]);

        $this->assertTrue($vendor->hasAvailableCredit());
        $this->assertEquals(700, $vendor->getAvailableCredit());
        $this->assertEquals(30.0, $vendor->getCreditUtilization());
    }

    public function test_audit_methods()
    {
        $vendor = Vendor::factory()->create([
            'next_audit_date' => now()->addDays(15)
        ]);

        $this->assertFalse($vendor->needsAudit());
        $this->assertEquals(15, $vendor->getDaysUntilNextAudit());
    }

    public function test_calculation_methods()
    {
        $vendor = Vendor::factory()->create([
            'rating' => 4.5,
            'tax_rate' => 21.0,
            'discount_rate' => 5.0
        ]);

        $this->assertEquals('4.5/5.0', $vendor->getFormattedRating());
        $this->assertEquals('21.00%', $vendor->getFormattedTaxRate());
        $this->assertEquals('5.00%', $vendor->getFormattedDiscountRate());
    }

    public function test_formatting_methods()
    {
        $vendor = Vendor::factory()->create([
            'vendor_type' => 'supplier',
            'status' => 'active',
            'risk_level' => 'low',
            'compliance_status' => 'compliant'
        ]);

        $this->assertEquals('Proveedor', $vendor->getFormattedVendorType());
        $this->assertEquals('Activo', $vendor->getFormattedStatus());
        $this->assertEquals('Bajo', $vendor->getFormattedRiskLevel());
        $this->assertEquals('Cumple', $vendor->getFormattedComplianceStatus());
    }

    public function test_badge_classes()
    {
        $vendor = Vendor::factory()->create(['is_active' => true]);
        
        $this->assertStringContainsString('bg-blue-100', $vendor->getStatusBadgeClass());
        $this->assertStringContainsString('text-blue-800', $vendor->getStatusBadgeClass());
        $this->assertStringContainsString('bg-blue-100', $vendor->getVendorTypeBadgeClass());
        $this->assertStringContainsString('bg-green-100', $vendor->getRiskLevelBadgeClass());
        $this->assertStringContainsString('bg-green-100', $vendor->getComplianceStatusBadgeClass());
    }

    public function test_contract_status_badge_class()
    {
        $vendor = Vendor::factory()->create([
            'contract_start_date' => now()->subMonth(),
            'contract_end_date' => now()->addDays(5)
        ]);

        $this->assertStringContainsString('bg-orange-100', $vendor->getContractStatusBadgeClass());
    }

    public function test_audit_status_badge_class()
    {
        $vendor = Vendor::factory()->create([
            'next_audit_date' => now()->subDays(5)
        ]);

        $this->assertStringContainsString('bg-red-100', $vendor->getAuditStatusBadgeClass());
    }

    public function test_credit_status_badge_class()
    {
        $vendor = Vendor::factory()->create([
            'credit_limit' => 1000,
            'current_balance' => 950
        ]);

        $this->assertStringContainsString('bg-red-100', $vendor->getCreditStatusBadgeClass());
    }

    public function test_date_methods()
    {
        $vendor = Vendor::factory()->create([
            'contract_start_date' => now()->subMonth(),
            'contract_end_date' => now()->addMonth(),
            'last_audit_date' => now()->subMonths(6),
            'next_audit_date' => now()->addMonths(6)
        ]);

        $this->assertStringContainsString('establecida', $vendor->getFormattedContractStartDate());
        $this->assertStringContainsString('fin', $vendor->getFormattedContractEndDate());
        $this->assertStringContainsString('auditado', $vendor->getFormattedLastAuditDate());
        $this->assertStringContainsString('programado', $vendor->getFormattedNextAuditDate());
    }

    public function test_ready_to_execute_scope()
    {
        $readyVendor = Vendor::factory()->create([
            'is_active' => true,
            'next_audit_date' => now()->subHour()
        ]);

        $notReadyVendor = Vendor::factory()->create([
            'is_active' => false,
            'next_audit_date' => now()->addMonth()
        ]);

        $needsAuditVendors = Vendor::needsAudit()->get();
        
        $this->assertTrue($needsAuditVendors->contains($readyVendor));
        $this->assertFalse($needsAuditVendors->contains($notReadyVendor));
    }

    public function test_high_risk_scope()
    {
        $highRiskVendor = Vendor::factory()->create(['risk_level' => 'high']);
        $lowRiskVendor = Vendor::factory()->create(['risk_level' => 'low']);

        $highRiskVendors = Vendor::highRisk()->get();
        
        $this->assertTrue($highRiskVendors->contains($highRiskVendor));
        $this->assertFalse($highRiskVendors->contains($lowRiskVendor));
    }

    public function test_by_type_scope()
    {
        $supplierVendor = Vendor::factory()->create(['vendor_type' => 'supplier']);
        $serviceVendor = Vendor::factory()->create(['vendor_type' => 'service_provider']);

        $supplierVendors = Vendor::byType('supplier')->get();
        
        $this->assertTrue($supplierVendors->contains($supplierVendor));
        $this->assertFalse($supplierVendors->contains($serviceVendor));
    }

    public function test_by_industry_scope()
    {
        $energyVendor = Vendor::factory()->create(['industry' => 'energy']);
        $techVendor = Vendor::factory()->create(['industry' => 'technology']);

        $energyVendors = Vendor::byIndustry('energy')->get();
        
        $this->assertTrue($energyVendors->contains($energyVendor));
        $this->assertFalse($energyVendors->contains($techVendor));
    }

    public function test_by_status_scope()
    {
        $activeVendor = Vendor::factory()->create(['status' => 'active']);
        $inactiveVendor = Vendor::factory()->create(['status' => 'inactive']);

        $activeVendors = Vendor::byStatus('active')->get();
        
        $this->assertTrue($activeVendors->contains($activeVendor));
        $this->assertFalse($activeVendors->contains($inactiveVendor));
    }

    public function test_by_risk_level_scope()
    {
        $highRiskVendor = Vendor::factory()->create(['risk_level' => 'high']);
        $lowRiskVendor = Vendor::factory()->create(['risk_level' => 'low']);

        $highRiskVendors = Vendor::byRiskLevel('high')->get();
        
        $this->assertTrue($highRiskVendors->contains($highRiskVendor));
        $this->assertFalse($highRiskVendors->contains($lowRiskVendor));
    }

    public function test_by_compliance_status_scope()
    {
        $compliantVendor = Vendor::factory()->create(['compliance_status' => 'compliant']);
        $nonCompliantVendor = Vendor::factory()->create(['compliance_status' => 'non_compliant']);

        $compliantVendors = Vendor::compliant()->get();
        
        $this->assertTrue($compliantVendors->contains($compliantVendor));
        $this->assertFalse($compliantVendors->contains($nonCompliantVendor));
    }

    public function test_by_rating_scope()
    {
        $highRatingVendor = Vendor::factory()->create(['rating' => 4.5]);
        $lowRatingVendor = Vendor::factory()->create(['rating' => 2.5]);

        $highRatingVendors = Vendor::byRating(4.0)->get();
        
        $this->assertTrue($highRatingVendors->contains($highRatingVendor));
        $this->assertFalse($highRatingVendors->contains($lowRatingVendor));
    }

    public function test_high_rating_scope()
    {
        $highRatingVendor = Vendor::factory()->create(['rating' => 4.5]);
        $lowRatingVendor = Vendor::factory()->create(['rating' => 3.5]);

        $highRatingVendors = Vendor::highRating()->get();
        
        $this->assertTrue($highRatingVendors->contains($highRatingVendor));
        $this->assertFalse($highRatingVendors->contains($lowRatingVendor));
    }

    public function test_by_location_scope()
    {
        $spainVendor = Vendor::factory()->create(['country' => 'Spain']);
        $franceVendor = Vendor::factory()->create(['country' => 'France']);

        $spainVendors = Vendor::byLocation('Spain')->get();
        
        $this->assertTrue($spainVendors->contains($spainVendor));
        $this->assertFalse($spainVendors->contains($franceVendor));
    }

    public function test_by_contract_status_scope()
    {
        $activeContractVendor = Vendor::factory()->create([
            'contract_start_date' => now()->subMonth(),
            'contract_end_date' => now()->addMonth()
        ]);

        $expiredContractVendor = Vendor::factory()->create([
            'contract_start_date' => now()->subMonths(2),
            'contract_end_date' => now()->subMonth()
        ]);

        $activeContractVendors = Vendor::byContractStatus('active')->get();
        
        $this->assertTrue($activeContractVendors->contains($activeContractVendor));
        $this->assertFalse($activeContractVendors->contains($expiredContractVendor));
    }

    public function test_by_payment_terms_scope()
    {
        $thirtyDaysVendor = Vendor::factory()->create(['payment_terms' => '30 days']);
        $sixtyDaysVendor = Vendor::factory()->create(['payment_terms' => '60 days']);

        $thirtyDaysVendors = Vendor::byPaymentTerms('30 days')->get();
        
        $this->assertTrue($thirtyDaysVendors->contains($thirtyDaysVendor));
        $this->assertFalse($thirtyDaysVendors->contains($sixtyDaysVendor));
    }

    public function test_by_credit_limit_scope()
    {
        $highLimitVendor = Vendor::factory()->create(['credit_limit' => 10000]);
        $lowLimitVendor = Vendor::factory()->create(['credit_limit' => 1000]);

        $highLimitVendors = Vendor::byCreditLimit(5000)->get();
        
        $this->assertTrue($highLimitVendors->contains($highLimitVendor));
        $this->assertFalse($highLimitVendors->contains($lowLimitVendor));
    }

    public function test_approved_scope()
    {
        $approvedVendor = Vendor::factory()->create(['approved_at' => now()]);
        $pendingVendor = Vendor::factory()->create(['approved_at' => null]);

        $approvedVendors = Vendor::approved()->get();
        
        $this->assertTrue($approvedVendors->contains($approvedVendor));
        $this->assertFalse($approvedVendors->contains($pendingVendor));
    }

    public function test_pending_approval_scope()
    {
        $approvedVendor = Vendor::factory()->create(['approved_at' => now()]);
        $pendingVendor = Vendor::factory()->create(['approved_at' => null]);

        $pendingVendors = Vendor::pendingApproval()->get();
        
        $this->assertTrue($pendingVendors->contains($pendingVendor));
        $this->assertFalse($pendingVendors->contains($approvedVendor));
    }

    public function test_non_compliant_scope()
    {
        $compliantVendor = Vendor::factory()->create(['compliance_status' => 'compliant']);
        $nonCompliantVendor = Vendor::factory()->create(['compliance_status' => 'non_compliant']);

        $nonCompliantVendors = Vendor::nonCompliant()->get();
        
        $this->assertTrue($nonCompliantVendors->contains($nonCompliantVendor));
        $this->assertFalse($nonCompliantVendors->contains($compliantVendor));
    }
}
