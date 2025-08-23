<?php

namespace Tests\Unit\Models;

use App\Models\EnergyPool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnergyPoolTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes()
    {
        $fillable = [
            'pool_number', 'name', 'description', 'pool_type', 'status', 'energy_category',
            'total_capacity_mw', 'available_capacity_mw', 'reserved_capacity_mw', 'utilized_capacity_mw',
            'efficiency_rating', 'availability_factor', 'capacity_factor', 'annual_production_mwh',
            'monthly_production_mwh', 'daily_production_mwh', 'hourly_production_mwh',
            'location_address', 'latitude', 'longitude', 'region', 'country',
            'commissioning_date', 'decommissioning_date', 'expected_lifespan_years',
            'construction_cost', 'operational_cost_per_mwh', 'maintenance_cost_per_mwh',
            'technical_specifications', 'environmental_impact', 'regulatory_compliance',
            'safety_features', 'pool_members', 'pool_operators', 'pool_governance',
            'trading_rules', 'settlement_procedures', 'risk_management', 'performance_metrics',
            'environmental_data', 'regulatory_documents', 'tags', 'managed_by', 'created_by',
            'approved_by', 'approved_at', 'notes'
        ];

        $this->assertEquals($fillable, (new EnergyPool())->getFillable());
    }

    public function test_casts()
    {
        $casts = [
            'total_capacity_mw' => 'decimal:2',
            'available_capacity_mw' => 'decimal:2',
            'reserved_capacity_mw' => 'decimal:2',
            'utilized_capacity_mw' => 'decimal:2',
            'efficiency_rating' => 'decimal:2',
            'availability_factor' => 'decimal:2',
            'capacity_factor' => 'decimal:2',
            'annual_production_mwh' => 'decimal:2',
            'monthly_production_mwh' => 'decimal:2',
            'daily_production_mwh' => 'decimal:2',
            'hourly_production_mwh' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'construction_cost' => 'decimal:2',
            'operational_cost_per_mwh' => 'decimal:2',
            'maintenance_cost_per_mwh' => 'decimal:2',
            'commissioning_date' => 'date',
            'decommissioning_date' => 'date',
            'approved_at' => 'datetime',
            'technical_specifications' => 'array',
            'environmental_impact' => 'array',
            'regulatory_compliance' => 'array',
            'safety_features' => 'array',
            'pool_members' => 'array',
            'pool_operators' => 'array',
            'pool_governance' => 'array',
            'trading_rules' => 'array',
            'settlement_procedures' => 'array',
            'risk_management' => 'array',
            'performance_metrics' => 'array',
            'environmental_data' => 'array',
            'regulatory_documents' => 'array',
            'tags' => 'array',
        ];

        $this->assertEquals($casts, (new EnergyPool())->getCasts());
    }

    public function test_static_enum_methods()
    {
        $this->assertIsArray(EnergyPool::getPoolTypes());
        $this->assertIsArray(EnergyPool::getStatuses());
        $this->assertIsArray(EnergyPool::getEnergyCategories());

        $this->assertArrayHasKey('trading', EnergyPool::getPoolTypes());
        $this->assertArrayHasKey('active', EnergyPool::getStatuses());
        $this->assertArrayHasKey('renewable', EnergyPool::getEnergyCategories());
    }

    public function test_relationships()
    {
        $energyPool = EnergyPool::factory()->create();
        $user = User::factory()->create();

        $energyPool->update(['managed_by' => $user->id, 'created_by' => $user->id, 'approved_by' => $user->id]);

        $this->assertInstanceOf(User::class, $energyPool->managedBy);
        $this->assertInstanceOf(User::class, $energyPool->createdBy);
        $this->assertInstanceOf(User::class, $energyPool->approvedBy);
    }

    public function test_scopes()
    {
        // Scope active
        EnergyPool::factory()->create(['status' => 'active']);
        EnergyPool::factory()->create(['status' => 'inactive']);

        $this->assertEquals(1, EnergyPool::active()->count());

        // Scope byType
        EnergyPool::factory()->create(['pool_type' => 'trading']);
        EnergyPool::factory()->create(['pool_type' => 'storage']);

        $this->assertEquals(1, EnergyPool::byType('trading')->count());

        // Scope byCategory
        EnergyPool::factory()->create(['energy_category' => 'renewable']);
        EnergyPool::factory()->create(['energy_category' => 'fossil']);

        $this->assertEquals(1, EnergyPool::byCategory('renewable')->count());

        // Scope byRegion
        EnergyPool::factory()->create(['region' => 'North']);
        EnergyPool::factory()->create(['region' => 'South']);

        $this->assertEquals(1, EnergyPool::byRegion('North')->count());

        // Scope byCountry
        EnergyPool::factory()->create(['country' => 'Spain']);
        EnergyPool::factory()->create(['country' => 'France']);

        $this->assertEquals(1, EnergyPool::byCountry('Spain')->count());

        // Scope highEfficiency
        EnergyPool::factory()->create(['efficiency_rating' => 95.0]);
        EnergyPool::factory()->create(['efficiency_rating' => 75.0]);

        $this->assertEquals(1, EnergyPool::highEfficiency()->count());

        // Scope highAvailability
        EnergyPool::factory()->create(['availability_factor' => 98.0]);
        EnergyPool::factory()->create(['availability_factor' => 85.0]);

        $this->assertEquals(1, EnergyPool::highAvailability()->count());

        // Scope pendingApproval
        EnergyPool::factory()->create(['approved_by' => null]);
        EnergyPool::factory()->create(['approved_by' => 1]);

        $this->assertEquals(1, EnergyPool::pendingApproval()->count());

        // Scope approved
        $this->assertEquals(1, EnergyPool::approved()->count());
    }

    public function test_validation_helper_methods()
    {
        $energyPool = EnergyPool::factory()->create([
            'pool_type' => 'trading',
            'status' => 'active',
            'energy_category' => 'renewable'
        ]);

        $this->assertTrue($energyPool->isValidPoolType('trading'));
        $this->assertFalse($energyPool->isValidPoolType('invalid'));

        $this->assertTrue($energyPool->isValidStatus('active'));
        $this->assertFalse($energyPool->isValidStatus('invalid'));

        $this->assertTrue($energyPool->isValidEnergyCategory('renewable'));
        $this->assertFalse($energyPool->isValidEnergyCategory('invalid'));
    }

    public function test_calculation_methods()
    {
        $energyPool = EnergyPool::factory()->create([
            'total_capacity_mw' => 100.0,
            'utilized_capacity_mw' => 75.0,
            'reserved_capacity_mw' => 15.0,
            'available_capacity_mw' => 10.0,
            'efficiency_rating' => 85.0,
            'availability_factor' => 92.0,
            'capacity_factor' => 78.0
        ]);

        $this->assertEquals(75.0, $energyPool->getUtilizationPercentage());
        $this->assertEquals(15.0, $energyPool->getReservationPercentage());
        $this->assertEquals(10.0, $energyPool->getAvailablePercentage());

        $this->assertEquals(85.0, $energyPool->getEfficiencyRating());
        $this->assertEquals(92.0, $energyPool->getAvailabilityFactor());
        $this->assertEquals(78.0, $energyPool->getCapacityFactor());
    }

    public function test_formatting_methods()
    {
        $energyPool = EnergyPool::factory()->create([
            'total_capacity_mw' => 150.75,
            'efficiency_rating' => 87.50,
            'construction_cost' => 2500000.00
        ]);

        $this->assertStringContainsString('150.75', $energyPool->getFormattedTotalCapacity());
        $this->assertStringContainsString('87.50', $energyPool->getFormattedEfficiencyRating());
        $this->assertStringContainsString('2,500,000.00', $energyPool->getFormattedConstructionCost());
    }

    public function test_badge_classes()
    {
        $energyPool = EnergyPool::factory()->create([
            'status' => 'active',
            'pool_type' => 'trading',
            'energy_category' => 'renewable',
            'efficiency_rating' => 90.0,
            'availability_factor' => 95.0,
            'capacity_factor' => 85.0
        ]);

        $this->assertStringContainsString('success', $energyPool->getStatusBadgeClass());
        $this->assertStringContainsString('primary', $energyPool->getPoolTypeBadgeClass());
        $this->assertStringContainsString('info', $energyPool->getEnergyCategoryBadgeClass());
        $this->assertStringContainsString('success', $energyPool->getEfficiencyBadgeClass());
        $this->assertStringContainsString('success', $energyPool->getAvailabilityBadgeClass());
        $this->assertStringContainsString('warning', $energyPool->getCapacityFactorBadgeClass());
    }

    public function test_summary_methods()
    {
        EnergyPool::factory()->count(5)->create(['status' => 'active']);
        EnergyPool::factory()->count(3)->create(['status' => 'inactive']);

        $this->assertEquals(5, EnergyPool::getActiveCount());
        $this->assertEquals(3, EnergyPool::getInactiveCount());
        $this->assertEquals(8, EnergyPool::getTotalCount());
    }

    public function test_boolean_status_checks()
    {
        $energyPool = EnergyPool::factory()->create([
            'status' => 'active',
            'approved_by' => 1,
            'commissioning_date' => now()->subYear(),
            'total_capacity_mw' => 100.0,
            'utilized_capacity_mw' => 100.0,
            'efficiency_rating' => 90.0,
            'availability_factor' => 95.0,
            'capacity_factor' => 85.0
        ]);

        $this->assertTrue($energyPool->isActive());
        $this->assertTrue($energyPool->isApproved());
        $this->assertTrue($energyPool->isCommissioned());
        $this->assertFalse($energyPool->hasAvailableCapacity());
        $this->assertTrue($energyPool->isFullyUtilized());
        $this->assertTrue($energyPool->isHighEfficiency());
        $this->assertTrue($energyPool->isHighAvailability());
        $this->assertTrue($energyPool->isHighCapacityFactor());
    }

    public function test_cost_calculations()
    {
        $energyPool = EnergyPool::factory()->create([
            'annual_production_mwh' => 1000.0,
            'operational_cost_per_mwh' => 50.0,
            'maintenance_cost_per_mwh' => 25.0
        ]);

        $this->assertEquals(75000.0, $energyPool->getTotalAnnualCost());
        $this->assertEquals(75.0, $energyPool->getCostPerMwh());
    }

    public function test_lifespan_calculations()
    {
        $energyPool = EnergyPool::factory()->create([
            'commissioning_date' => now()->subYears(5),
            'expected_lifespan_years' => 25
        ]);

        $this->assertEquals(5, $energyPool->getAgeInYears());
        $this->assertEquals(20, $energyPool->getRemainingLifespan());
    }
}
