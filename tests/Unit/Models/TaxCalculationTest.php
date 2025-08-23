<?php

namespace Tests\Unit\Models;

use App\Models\TaxCalculation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes()
    {
        $fillable = [
            'calculation_number', 'name', 'description', 'tax_type', 'calculation_type',
            'status', 'priority', 'entity_id', 'entity_type', 'transaction_id',
            'transaction_type', 'tax_period_start', 'tax_period_end', 'calculation_date',
            'due_date', 'payment_date', 'taxable_amount', 'tax_rate', 'tax_amount',
            'tax_base_amount', 'exemption_amount', 'deduction_amount', 'credit_amount',
            'net_tax_amount', 'penalty_amount', 'interest_amount', 'total_amount_due',
            'amount_paid', 'amount_remaining', 'currency', 'exchange_rate',
            'tax_jurisdiction', 'tax_authority', 'tax_registration_number',
            'tax_filing_frequency', 'tax_filing_method', 'is_estimated', 'is_final',
            'is_amended', 'amendment_reason', 'calculation_notes', 'review_notes',
            'approval_notes', 'calculation_details', 'tax_breakdown', 'supporting_documents',
            'audit_trail', 'tags', 'notes', 'calculated_by', 'reviewed_by',
            'approved_by', 'applied_by', 'created_by'
        ];

        $this->assertEquals($fillable, (new TaxCalculation())->getFillable());
    }

    public function test_casts()
    {
        $casts = [
            'tax_period_start' => 'date',
            'tax_period_end' => 'date',
            'calculation_date' => 'date',
            'due_date' => 'date',
            'payment_date' => 'date',
            'taxable_amount' => 'decimal:4',
            'tax_rate' => 'decimal:4',
            'tax_amount' => 'decimal:4',
            'tax_base_amount' => 'decimal:4',
            'exemption_amount' => 'decimal:4',
            'deduction_amount' => 'decimal:4',
            'credit_amount' => 'decimal:4',
            'net_tax_amount' => 'decimal:4',
            'penalty_amount' => 'decimal:4',
            'interest_amount' => 'decimal:4',
            'total_amount_due' => 'decimal:4',
            'amount_paid' => 'decimal:4',
            'amount_remaining' => 'decimal:4',
            'exchange_rate' => 'decimal:6',
            'is_estimated' => 'boolean',
            'is_final' => 'boolean',
            'is_amended' => 'boolean',
            'calculation_details' => 'array',
            'tax_breakdown' => 'array',
            'supporting_documents' => 'array',
            'audit_trail' => 'array',
            'tags' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];

        $this->assertEquals($casts, (new TaxCalculation())->getCasts());
    }

    public function test_static_enum_methods()
    {
        $this->assertIsArray(TaxCalculation::getTaxTypes());
        $this->assertIsArray(TaxCalculation::getCalculationTypes());
        $this->assertIsArray(TaxCalculation::getStatuses());
        $this->assertIsArray(TaxCalculation::getPriorities());
    }

    public function test_relationships()
    {
        $taxCalculation = TaxCalculation::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class, $taxCalculation->entity());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class, $taxCalculation->transaction());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $taxCalculation->calculatedBy());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $taxCalculation->reviewedBy());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $taxCalculation->approvedBy());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $taxCalculation->appliedBy());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $taxCalculation->createdBy());
    }

    public function test_scopes()
    {
        $taxCalculation = TaxCalculation::factory()->create(['status' => 'draft']);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, TaxCalculation::byStatus('draft'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, TaxCalculation::byType('income_tax'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, TaxCalculation::byPriority('high'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, TaxCalculation::overdue());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, TaxCalculation::dueSoon());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, TaxCalculation::highPriority());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, TaxCalculation::estimated());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, TaxCalculation::final());
    }

    public function test_boolean_status_checks()
    {
        $taxCalculation = TaxCalculation::factory()->create(['status' => 'draft']);
        
        $this->assertTrue($taxCalculation->isDraft());
        $this->assertFalse($taxCalculation->isCalculated());
        $this->assertFalse($taxCalculation->isReviewed());
        $this->assertFalse($taxCalculation->isApproved());
        $this->assertFalse($taxCalculation->isApplied());
        $this->assertFalse($taxCalculation->isCancelled());
        $this->assertFalse($taxCalculation->isError());
    }

    public function test_priority_checks()
    {
        $taxCalculation = TaxCalculation::factory()->create(['priority' => 'high']);
        
        $this->assertTrue($taxCalculation->isHighPriority());
        $this->assertFalse($taxCalculation->isLowPriority());
        $this->assertFalse($taxCalculation->isMediumPriority());
        $this->assertFalse($taxCalculation->isCriticalPriority());
    }

    public function test_tax_type_checks()
    {
        $taxCalculation = TaxCalculation::factory()->create(['tax_type' => 'income_tax']);
        
        $this->assertTrue($taxCalculation->isIncomeTax());
        $this->assertFalse($taxCalculation->isSalesTax());
        $this->assertFalse($taxCalculation->isPropertyTax());
        $this->assertFalse($taxCalculation->isExciseTax());
        $this->assertFalse($taxCalculation->isCustomsDuty());
        $this->assertFalse($taxCalculation->isOtherTax());
    }

    public function test_calculation_type_checks()
    {
        $taxCalculation = TaxCalculation::factory()->create(['calculation_type' => 'automatic']);
        
        $this->assertTrue($taxCalculation->isAutomaticCalculation());
        $this->assertFalse($taxCalculation->isManualCalculation());
        $this->assertFalse($taxCalculation->isEstimatedCalculation());
        $this->assertFalse($taxCalculation->isFinalCalculation());
    }

    public function test_calculation_methods()
    {
        $taxCalculation = TaxCalculation::factory()->create([
            'taxable_amount' => 1000.00,
            'tax_rate' => 21.00,
            'exemption_amount' => 100.00,
            'deduction_amount' => 50.00,
            'credit_amount' => 25.00,
            'penalty_amount' => 10.00,
            'interest_amount' => 5.00,
            'amount_paid' => 200.00
        ]);

        $this->assertEquals(210.00, $taxCalculation->getEffectiveTaxAmount());
        $this->assertEquals(35.00, $taxCalculation->getNetTaxAmount());
        $this->assertEquals(50.00, $taxCalculation->getTotalAmountWithPenaltyAndInterest());
        $this->assertEquals(80.0, $taxCalculation->getPaymentPercentage());
    }

    public function test_formatting_methods()
    {
        $taxCalculation = TaxCalculation::factory()->create([
            'taxable_amount' => 1234.56,
            'currency' => 'EUR'
        ]);

        $this->assertStringContainsString('1.234,56', $taxCalculation->getFormattedTaxableAmount());
        $this->assertStringContainsString('EUR', $taxCalculation->getFormattedTaxableAmount());
    }

    public function test_badge_classes()
    {
        $taxCalculation = TaxCalculation::factory()->create(['status' => 'approved']);
        
        $this->assertStringContainsString('bg-green-100', $taxCalculation->getStatusBadgeClass());
        $this->assertStringContainsString('text-green-800', $taxCalculation->getStatusBadgeClass());
    }

    public function test_validation_methods()
    {
        $taxCalculation = TaxCalculation::factory()->create([
            'taxable_amount' => 1000.00,
            'tax_rate' => 21.00,
            'tax_amount' => 210.00
        ]);

        $this->assertTrue($taxCalculation->isValidCalculation());
        $this->assertTrue($taxCalculation->hasValidTaxRate());
        $this->assertTrue($taxCalculation->hasValidAmounts());
    }

    public function test_date_methods()
    {
        $taxCalculation = TaxCalculation::factory()->create([
            'due_date' => now()->addDays(5),
            'tax_period_start' => now()->subDays(30),
            'tax_period_end' => now()->subDays(1)
        ]);

        $this->assertEquals(5, $taxCalculation->getDaysUntilDue());
        $this->assertEquals(29, $taxCalculation->getTaxPeriodDuration());
        $this->assertFalse($taxCalculation->isOverdue());
        $this->assertTrue($taxCalculation->isDueSoon());
    }

    public function test_payment_status_checks()
    {
        $taxCalculation = TaxCalculation::factory()->create([
            'total_amount_due' => 1000.00,
            'amount_paid' => 1000.00
        ]);

        $this->assertTrue($taxCalculation->isFullyPaid());
        $this->assertFalse($taxCalculation->isPartiallyPaid());
        $this->assertFalse($taxCalculation->isUnpaid());
    }
}
