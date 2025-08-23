<?php

namespace Tests\Unit\Models;

use App\Models\EnergySource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnergySourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_source_types()
    {
        $types = EnergySource::getSourceTypes();

        $this->assertIsArray($types);
        $this->assertArrayHasKey('solar', $types);
        $this->assertArrayHasKey('wind', $types);
        $this->assertArrayHasKey('hydroelectric', $types);
        $this->assertArrayHasKey('biomass', $types);
        $this->assertArrayHasKey('geothermal', $types);
        $this->assertArrayHasKey('nuclear', $types);
        $this->assertArrayHasKey('fossil_fuel', $types);
        $this->assertArrayHasKey('hybrid', $types);
        $this->assertArrayHasKey('other', $types);
    }

    /** @test */
    public function it_returns_statuses()
    {
        $statuses = EnergySource::getStatuses();

        $this->assertIsArray($statuses);
        $this->assertArrayHasKey('active', $statuses);
        $this->assertArrayHasKey('inactive', $statuses);
        $this->assertArrayHasKey('maintenance', $statuses);
        $this->assertArrayHasKey('decommissioned', $statuses);
        $this->assertArrayHasKey('planned', $statuses);
        $this->assertArrayHasKey('under_construction', $statuses);
    }

    /** @test */
    public function it_returns_energy_categories()
    {
        $categories = EnergySource::getEnergyCategories();

        $this->assertIsArray($categories);
        $this->assertArrayHasKey('renewable', $categories);
        $this->assertArrayHasKey('non_renewable', $categories);
        $this->assertArrayHasKey('hybrid', $categories);
    }

    /** @test */
    public function it_has_managed_by_relationship()
    {
        $user = User::factory()->create();
        $energySource = EnergySource::factory()->create(['managed_by' => $user->id]);

        $this->assertInstanceOf(User::class, $energySource->managedBy);
        $this->assertEquals($user->id, $energySource->managedBy->id);
    }

    /** @test */
    public function it_has_created_by_relationship()
    {
        $user = User::factory()->create();
        $energySource = EnergySource::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $energySource->createdBy);
        $this->assertEquals($user->id, $energySource->createdBy->id);
    }

    /** @test */
    public function it_has_approved_by_relationship()
    {
        $user = User::factory()->create();
        $energySource = EnergySource::factory()->create(['approved_by' => $user->id]);

        $this->assertInstanceOf(User::class, $energySource->approvedBy);
        $this->assertEquals($user->id, $energySource->approvedBy->id);
    }

    /** @test */
    public function it_has_installations_relationship()
    {
        $energySource = EnergySource::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $energySource->installations);
    }

    /** @test */
    public function it_has_meters_relationship()
    {
        $energySource = EnergySource::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $energySource->meters);
    }

    /** @test */
    public function it_has_readings_relationship()
    {
        $energySource = EnergySource::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $energySource->readings);
    }

    /** @test */
    public function it_has_forecasts_relationship()
    {
        $energySource = EnergySource::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $energySource->forecasts);
    }

    /** @test */
    public function it_has_production_projects_relationship()
    {
        $energySource = EnergySource::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $energySource->productionProjects);
    }

    /** @test */
    public function it_scopes_by_active_status()
    {
        EnergySource::factory()->create(['status' => 'active']);
        EnergySource::factory()->create(['status' => 'inactive']);

        $activeSources = EnergySource::active()->get();

        $this->assertEquals(1, $activeSources->count());
        $this->assertEquals('active', $activeSources->first()->status);
    }

    /** @test */
    public function it_scopes_by_type()
    {
        EnergySource::factory()->create(['source_type' => 'solar']);
        EnergySource::factory()->create(['source_type' => 'wind']);

        $solarSources = EnergySource::byType('solar')->get();

        $this->assertEquals(1, $solarSources->count());
        $this->assertEquals('solar', $solarSources->first()->source_type);
    }

    /** @test */
    public function it_scopes_by_status()
    {
        EnergySource::factory()->create(['status' => 'active']);
        EnergySource::factory()->create(['status' => 'maintenance']);

        $maintenanceSources = EnergySource::byStatus('maintenance')->get();

        $this->assertEquals(1, $maintenanceSources->count());
        $this->assertEquals('maintenance', $maintenanceSources->first()->status);
    }

    /** @test */
    public function it_scopes_by_category()
    {
        EnergySource::factory()->create(['energy_category' => 'renewable']);
        EnergySource::factory()->create(['energy_category' => 'non_renewable']);

        $renewableSources = EnergySource::byCategory('renewable')->get();

        $this->assertEquals(1, $renewableSources->count());
        $this->assertEquals('renewable', $renewableSources->first()->energy_category);
    }

    /** @test */
    public function it_scopes_renewable_sources()
    {
        EnergySource::factory()->create(['energy_category' => 'renewable']);
        EnergySource::factory()->create(['energy_category' => 'non_renewable']);

        $renewableSources = EnergySource::renewable()->get();

        $this->assertEquals(1, $renewableSources->count());
        $this->assertEquals('renewable', $renewableSources->first()->energy_category);
    }

    /** @test */
    public function it_scopes_non_renewable_sources()
    {
        EnergySource::factory()->create(['energy_category' => 'renewable']);
        EnergySource::factory()->create(['energy_category' => 'non_renewable']);

        $nonRenewableSources = EnergySource::nonRenewable()->get();

        $this->assertEquals(1, $nonRenewableSources->count());
        $this->assertEquals('non_renewable', $nonRenewableSources->first()->energy_category);
    }

    /** @test */
    public function it_scopes_hybrid_sources()
    {
        EnergySource::factory()->create(['energy_category' => 'renewable']);
        EnergySource::factory()->create(['energy_category' => 'hybrid']);

        $hybridSources = EnergySource::hybrid()->get();

        $this->assertEquals(1, $hybridSources->count());
        $this->assertEquals('hybrid', $hybridSources->first()->energy_category);
    }

    /** @test */
    public function it_scopes_high_efficiency_sources()
    {
        EnergySource::factory()->create(['efficiency_rating' => 85.0]);
        EnergySource::factory()->create(['efficiency_rating' => 75.0]);

        $highEfficiencySources = EnergySource::highEfficiency(80)->get();

        $this->assertEquals(1, $highEfficiencySources->count());
        $this->assertEquals(85.0, $highEfficiencySources->first()->efficiency_rating);
    }

    /** @test */
    public function it_scopes_high_capacity_sources()
    {
        EnergySource::factory()->create(['operational_capacity_mw' => 150.0]);
        EnergySource::factory()->create(['operational_capacity_mw' => 50.0]);

        $highCapacitySources = EnergySource::highCapacity(100)->get();

        $this->assertEquals(1, $highCapacitySources->count());
        $this->assertEquals(150.0, $highCapacitySources->first()->operational_capacity_mw);
    }

    /** @test */
    public function it_scopes_by_region()
    {
        EnergySource::factory()->create(['region' => 'North']);
        EnergySource::factory()->create(['region' => 'South']);

        $northSources = EnergySource::byRegion('North')->get();

        $this->assertEquals(1, $northSources->count());
        $this->assertEquals('North', $northSources->first()->region);
    }

    /** @test */
    public function it_scopes_by_country()
    {
        EnergySource::factory()->create(['country' => 'Spain']);
        EnergySource::factory()->create(['country' => 'France']);

        $spainSources = EnergySource::byCountry('Spain')->get();

        $this->assertEquals(1, $spainSources->count());
        $this->assertEquals('Spain', $spainSources->first()->country);
    }

    /** @test */
    public function it_scopes_operational_sources()
    {
        EnergySource::factory()->create(['status' => 'active']);
        EnergySource::factory()->create(['status' => 'maintenance']);

        $operationalSources = EnergySource::operational()->get();

        $this->assertEquals(1, $operationalSources->count());
        $this->assertEquals('active', $operationalSources->first()->status);
    }

    /** @test */
    public function it_scopes_under_construction_sources()
    {
        EnergySource::factory()->create(['status' => 'under_construction']);
        EnergySource::factory()->create(['status' => 'active']);

        $underConstructionSources = EnergySource::underConstruction()->get();

        $this->assertEquals(1, $underConstructionSources->count());
        $this->assertEquals('under_construction', $underConstructionSources->first()->status);
    }

    /** @test */
    public function it_scopes_maintenance_sources()
    {
        EnergySource::factory()->create(['status' => 'maintenance']);
        EnergySource::factory()->create(['status' => 'active']);

        $maintenanceSources = EnergySource::maintenance()->get();

        $this->assertEquals(1, $maintenanceSources->count());
        $this->assertEquals('maintenance', $maintenanceSources->first()->status);
    }

    /** @test */
    public function it_checks_if_source_is_active()
    {
        $activeSource = EnergySource::factory()->create(['status' => 'active']);
        $inactiveSource = EnergySource::factory()->create(['status' => 'inactive']);

        $this->assertTrue($activeSource->isActive());
        $this->assertFalse($inactiveSource->isActive());
    }

    /** @test */
    public function it_checks_if_source_is_inactive()
    {
        $activeSource = EnergySource::factory()->create(['status' => 'active']);
        $inactiveSource = EnergySource::factory()->create(['status' => 'inactive']);

        $this->assertFalse($activeSource->isInactive());
        $this->assertTrue($inactiveSource->isInactive());
    }

    /** @test */
    public function it_checks_if_source_is_maintenance()
    {
        $maintenanceSource = EnergySource::factory()->create(['status' => 'maintenance']);
        $activeSource = EnergySource::factory()->create(['status' => 'active']);

        $this->assertTrue($maintenanceSource->isMaintenance());
        $this->assertFalse($activeSource->isMaintenance());
    }

    /** @test */
    public function it_checks_if_source_is_decommissioned()
    {
        $decommissionedSource = EnergySource::factory()->create(['status' => 'decommissioned']);
        $activeSource = EnergySource::factory()->create(['status' => 'active']);

        $this->assertTrue($decommissionedSource->isDecommissioned());
        $this->assertFalse($activeSource->isDecommissioned());
    }

    /** @test */
    public function it_checks_if_source_is_planned()
    {
        $plannedSource = EnergySource::factory()->create(['status' => 'planned']);
        $activeSource = EnergySource::factory()->create(['status' => 'active']);

        $this->assertTrue($plannedSource->isPlanned());
        $this->assertFalse($activeSource->isPlanned());
    }

    /** @test */
    public function it_checks_if_source_is_under_construction()
    {
        $underConstructionSource = EnergySource::factory()->create(['status' => 'under_construction']);
        $activeSource = EnergySource::factory()->create(['status' => 'active']);

        $this->assertTrue($underConstructionSource->isUnderConstruction());
        $this->assertFalse($activeSource->isUnderConstruction());
    }

    /** @test */
    public function it_checks_if_source_is_renewable()
    {
        $renewableSource = EnergySource::factory()->create(['energy_category' => 'renewable']);
        $nonRenewableSource = EnergySource::factory()->create(['energy_category' => 'non_renewable']);

        $this->assertTrue($renewableSource->isRenewable());
        $this->assertFalse($nonRenewableSource->isRenewable());
    }

    /** @test */
    public function it_checks_if_source_is_non_renewable()
    {
        $renewableSource = EnergySource::factory()->create(['energy_category' => 'renewable']);
        $nonRenewableSource = EnergySource::factory()->create(['energy_category' => 'non_renewable']);

        $this->assertFalse($renewableSource->isNonRenewable());
        $this->assertTrue($nonRenewableSource->isNonRenewable());
    }

    /** @test */
    public function it_checks_if_source_is_hybrid()
    {
        $hybridSource = EnergySource::factory()->create(['energy_category' => 'hybrid']);
        $renewableSource = EnergySource::factory()->create(['energy_category' => 'renewable']);

        $this->assertTrue($hybridSource->isHybrid());
        $this->assertFalse($renewableSource->isHybrid());
    }

    /** @test */
    public function it_checks_if_source_is_operational()
    {
        $operationalSource = EnergySource::factory()->create(['status' => 'active']);
        $maintenanceSource = EnergySource::factory()->create(['status' => 'maintenance']);

        $this->assertTrue($operationalSource->isOperational());
        $this->assertFalse($maintenanceSource->isOperational());
    }

    /** @test */
    public function it_checks_if_source_is_approved()
    {
        $approvedSource = EnergySource::factory()->create(['approved_at' => now()]);
        $pendingSource = EnergySource::factory()->create(['approved_at' => null]);

        $this->assertTrue($approvedSource->isApproved());
        $this->assertFalse($pendingSource->isApproved());
    }

    /** @test */
    public function it_calculates_utilization_percentage()
    {
        $energySource = EnergySource::factory()->create([
            'installed_capacity_mw' => 100.0,
            'operational_capacity_mw' => 75.0
        ]);

        $utilization = $energySource->getUtilizationPercentage();

        $this->assertEquals(75.0, $utilization);
    }

    /** @test */
    public function it_returns_zero_utilization_for_zero_capacity()
    {
        $energySource = EnergySource::factory()->create([
            'installed_capacity_mw' => 0.0,
            'operational_capacity_mw' => 50.0
        ]);

        $utilization = $energySource->getUtilizationPercentage();

        $this->assertEquals(0.0, $utilization);
    }

    /** @test */
    public function it_calculates_annual_efficiency()
    {
        $energySource = EnergySource::factory()->create([
            'installed_capacity_mw' => 100.0,
            'annual_production_mwh' => 438000.0 // 50% of theoretical (100 MW * 8760 hours * 0.5)
        ]);

        $efficiency = $energySource->getAnnualEfficiency();

        $this->assertEquals(50.0, $efficiency);
    }

    /** @test */
    public function it_returns_zero_efficiency_for_zero_theoretical_production()
    {
        $energySource = EnergySource::factory()->create([
            'installed_capacity_mw' => 0.0,
            'annual_production_mwh' => 1000.0
        ]);

        $efficiency = $energySource->getAnnualEfficiency();

        $this->assertEquals(0.0, $efficiency);
    }

    /** @test */
    public function it_calculates_monthly_average()
    {
        $energySource = EnergySource::factory()->create([
            'annual_production_mwh' => 1200.0
        ]);

        $monthlyAverage = $energySource->getMonthlyAverage();

        $this->assertEquals(100.0, $monthlyAverage);
    }

    /** @test */
    public function it_calculates_daily_average()
    {
        $energySource = EnergySource::factory()->create([
            'annual_production_mwh' => 3650.0
        ]);

        $dailyAverage = $energySource->getDailyAverage();

        $this->assertEquals(10.0, $dailyAverage);
    }

    /** @test */
    public function it_calculates_hourly_average()
    {
        $energySource = EnergySource::factory()->create([
            'annual_production_mwh' => 8760.0
        ]);

        $hourlyAverage = $energySource->getHourlyAverage();

        $this->assertEquals(1.0, $hourlyAverage);
    }

    /** @test */
    public function it_calculates_remaining_lifespan()
    {
        $energySource = EnergySource::factory()->create([
            'commissioning_date' => now()->subYears(5),
            'expected_lifespan_years' => 25
        ]);

        $remainingLifespan = $energySource->getRemainingLifespan();

        $this->assertEquals(20, $remainingLifespan);
    }

    /** @test */
    public function it_returns_zero_remaining_lifespan_for_missing_dates()
    {
        $energySource = EnergySource::factory()->create([
            'commissioning_date' => null,
            'expected_lifespan_years' => 25
        ]);

        $remainingLifespan = $energySource->getRemainingLifespan();

        $this->assertEquals(0, $remainingLifespan);
    }

    /** @test */
    public function it_calculates_age_in_years()
    {
        $energySource = EnergySource::factory()->create([
            'commissioning_date' => now()->subYears(10)
        ]);

        $age = $energySource->getAgeInYears();

        $this->assertEquals(10, $age);
    }

    /** @test */
    public function it_returns_zero_age_for_missing_commissioning_date()
    {
        $energySource = EnergySource::factory()->create([
            'commissioning_date' => null
        ]);

        $age = $energySource->getAgeInYears();

        $this->assertEquals(0, $age);
    }

    /** @test */
    public function it_calculates_total_annual_cost()
    {
        $energySource = EnergySource::factory()->create([
            'annual_production_mwh' => 1000.0,
            'operational_cost_per_mwh' => 50.0,
            'maintenance_cost_per_mwh' => 25.0
        ]);

        $totalCost = $energySource->getTotalAnnualCost();

        $this->assertEquals(75000.0, $totalCost); // (50 + 25) * 1000
    }

    /** @test */
    public function it_calculates_cost_per_mwh()
    {
        $energySource = EnergySource::factory()->create([
            'annual_production_mwh' => 1000.0,
            'operational_cost_per_mwh' => 50.0,
            'maintenance_cost_per_mwh' => 25.0
        ]);

        $costPerMwh = $energySource->getCostPerMwh();

        $this->assertEquals(75.0, $costPerMwh); // (50 + 25)
    }

    /** @test */
    public function it_returns_zero_cost_per_mwh_for_zero_production()
    {
        $energySource = EnergySource::factory()->create([
            'annual_production_mwh' => 0.0,
            'operational_cost_per_mwh' => 50.0,
            'maintenance_cost_per_mwh' => 25.0
        ]);

        $costPerMwh = $energySource->getCostPerMwh();

        $this->assertEquals(0.0, $costPerMwh);
    }

    /** @test */
    public function it_formats_source_type()
    {
        $energySource = EnergySource::factory()->create(['source_type' => 'solar']);

        $formattedType = $energySource->getFormattedSourceType();

        $this->assertEquals('Solar', $formattedType);
    }

    /** @test */
    public function it_formats_status()
    {
        $energySource = EnergySource::factory()->create(['status' => 'active']);

        $formattedStatus = $energySource->getFormattedStatus();

        $this->assertEquals('Activa', $formattedStatus);
    }

    /** @test */
    public function it_formats_energy_category()
    {
        $energySource = EnergySource::factory()->create(['energy_category' => 'renewable']);

        $formattedCategory = $energySource->getFormattedEnergyCategory();

        $this->assertEquals('Renovable', $formattedCategory);
    }

    /** @test */
    public function it_formats_installed_capacity()
    {
        $energySource = EnergySource::factory()->create(['installed_capacity_mw' => 150.5]);

        $formattedCapacity = $energySource->getFormattedInstalledCapacity();

        $this->assertEquals('150.50 MW', $formattedCapacity);
    }

    /** @test */
    public function it_formats_operational_capacity()
    {
        $energySource = EnergySource::factory()->create(['operational_capacity_mw' => 120.75]);

        $formattedCapacity = $energySource->getFormattedOperationalCapacity();

        $this->assertEquals('120.75 MW', $formattedCapacity);
    }

    /** @test */
    public function it_formats_efficiency_rating()
    {
        $energySource = EnergySource::factory()->create(['efficiency_rating' => 85.5]);

        $formattedEfficiency = $energySource->getFormattedEfficiencyRating();

        $this->assertEquals('85.50%', $formattedEfficiency);
    }

    /** @test */
    public function it_formats_availability_factor()
    {
        $energySource = EnergySource::factory()->create(['availability_factor' => 92.3]);

        $formattedAvailability = $energySource->getFormattedAvailabilityFactor();

        $this->assertEquals('92.30%', $formattedAvailability);
    }

    /** @test */
    public function it_formats_capacity_factor()
    {
        $energySource = EnergySource::factory()->create(['capacity_factor' => 45.7]);

        $formattedCapacityFactor = $energySource->getFormattedCapacityFactor();

        $this->assertEquals('45.70%', $formattedCapacityFactor);
    }

    /** @test */
    public function it_formats_annual_production()
    {
        $energySource = EnergySource::factory()->create(['annual_production_mwh' => 50000.25]);

        $formattedProduction = $energySource->getFormattedAnnualProduction();

        $this->assertEquals('50,000.25 MWh', $formattedProduction);
    }

    /** @test */
    public function it_formats_monthly_production()
    {
        $energySource = EnergySource::factory()->create(['monthly_production_mwh' => 4166.67]);

        $formattedProduction = $energySource->getFormattedMonthlyProduction();

        $this->assertEquals('4,166.67 MWh', $formattedProduction);
    }

    /** @test */
    public function it_formats_daily_production()
    {
        $energySource = EnergySource::factory()->create(['daily_production_mwh' => 136.99]);

        $formattedProduction = $energySource->getFormattedDailyProduction();

        $this->assertEquals('136.99 MWh', $formattedProduction);
    }

    /** @test */
    public function it_formats_hourly_production()
    {
        $energySource = EnergySource::factory()->create(['hourly_production_mwh' => 5.71]);

        $formattedProduction = $energySource->getFormattedHourlyProduction();

        $this->assertEquals('5.71 MWh', $formattedProduction);
    }

    /** @test */
    public function it_formats_construction_cost()
    {
        $energySource = EnergySource::factory()->create(['construction_cost' => 1000000.50]);

        $formattedCost = $energySource->getFormattedConstructionCost();

        $this->assertEquals('$1,000,000.50', $formattedCost);
    }

    /** @test */
    public function it_formats_operational_cost()
    {
        $energySource = EnergySource::factory()->create(['operational_cost_per_mwh' => 45.75]);

        $formattedCost = $energySource->getFormattedOperationalCost();

        $this->assertEquals('$45.75/MWh', $formattedCost);
    }

    /** @test */
    public function it_formats_maintenance_cost()
    {
        $energySource = EnergySource::factory()->create(['maintenance_cost_per_mwh' => 12.50]);

        $formattedCost = $energySource->getFormattedMaintenanceCost();

        $this->assertEquals('$12.50/MWh', $formattedCost);
    }

    /** @test */
    public function it_formats_commissioning_date()
    {
        $energySource = EnergySource::factory()->create(['commissioning_date' => '2020-01-15']);

        $formattedDate = $energySource->getFormattedCommissioningDate();

        $this->assertEquals('15/01/2020', $formattedDate);
    }

    /** @test */
    public function it_formats_decommissioning_date()
    {
        $energySource = EnergySource::factory()->create(['decommissioning_date' => '2050-12-31']);

        $formattedDate = $energySource->getFormattedDecommissioningDate();

        $this->assertEquals('31/12/2050', $formattedDate);
    }

    /** @test */
    public function it_formats_utilization_percentage()
    {
        $energySource = EnergySource::factory()->create([
            'installed_capacity_mw' => 100.0,
            'operational_capacity_mw' => 75.0
        ]);

        $formattedUtilization = $energySource->getFormattedUtilizationPercentage();

        $this->assertEquals('75.0%', $formattedUtilization);
    }

    /** @test */
    public function it_formats_annual_efficiency()
    {
        $energySource = EnergySource::factory()->create([
            'installed_capacity_mw' => 100.0,
            'annual_production_mwh' => 438000.0
        ]);

        $formattedEfficiency = $energySource->getFormattedAnnualEfficiency();

        $this->assertEquals('50.0%', $formattedEfficiency);
    }

    /** @test */
    public function it_formats_total_annual_cost()
    {
        $energySource = EnergySource::factory()->create([
            'annual_production_mwh' => 1000.0,
            'operational_cost_per_mwh' => 50.0,
            'maintenance_cost_per_mwh' => 25.0
        ]);

        $formattedCost = $energySource->getFormattedTotalAnnualCost();

        $this->assertEquals('$75,000.00', $formattedCost);
    }

    /** @test */
    public function it_formats_cost_per_mwh()
    {
        $energySource = EnergySource::factory()->create([
            'annual_production_mwh' => 1000.0,
            'operational_cost_per_mwh' => 50.0,
            'maintenance_cost_per_mwh' => 25.0
        ]);

        $formattedCost = $energySource->getFormattedCostPerMwh();

        $this->assertEquals('$75.00/MWh', $formattedCost);
    }

    /** @test */
    public function it_returns_status_badge_class()
    {
        $activeSource = EnergySource::factory()->create(['status' => 'active']);
        $maintenanceSource = EnergySource::factory()->create(['status' => 'maintenance']);
        $inactiveSource = EnergySource::factory()->create(['status' => 'inactive']);

        $this->assertEquals('bg-green-100 text-green-800', $activeSource->getStatusBadgeClass());
        $this->assertEquals('bg-yellow-100 text-yellow-800', $maintenanceSource->getStatusBadgeClass());
        $this->assertEquals('bg-gray-100 text-gray-800', $inactiveSource->getStatusBadgeClass());
    }

    /** @test */
    public function it_returns_source_type_badge_class()
    {
        $solarSource = EnergySource::factory()->create(['source_type' => 'solar']);
        $windSource = EnergySource::factory()->create(['source_type' => 'wind']);
        $nuclearSource = EnergySource::factory()->create(['source_type' => 'nuclear']);

        $this->assertEquals('bg-yellow-100 text-yellow-800', $solarSource->getSourceTypeBadgeClass());
        $this->assertEquals('bg-blue-100 text-blue-800', $windSource->getSourceTypeBadgeClass());
        $this->assertEquals('bg-purple-100 text-purple-800', $nuclearSource->getSourceTypeBadgeClass());
    }

    /** @test */
    public function it_returns_energy_category_badge_class()
    {
        $renewableSource = EnergySource::factory()->create(['energy_category' => 'renewable']);
        $nonRenewableSource = EnergySource::factory()->create(['energy_category' => 'non_renewable']);
        $hybridSource = EnergySource::factory()->create(['energy_category' => 'hybrid']);

        $this->assertEquals('bg-green-100 text-green-800', $renewableSource->getEnergyCategoryBadgeClass());
        $this->assertEquals('bg-red-100 text-red-800', $nonRenewableSource->getEnergyCategoryBadgeClass());
        $this->assertEquals('bg-blue-100 text-blue-800', $hybridSource->getEnergyCategoryBadgeClass());
    }

    /** @test */
    public function it_returns_efficiency_badge_class()
    {
        $highEfficiencySource = EnergySource::factory()->create(['efficiency_rating' => 95.0]);
        $mediumEfficiencySource = EnergySource::factory()->create(['efficiency_rating' => 75.0]);
        $lowEfficiencySource = EnergySource::factory()->create(['efficiency_rating' => 45.0]);

        $this->assertEquals('bg-green-100 text-green-800', $highEfficiencySource->getEfficiencyBadgeClass());
        $this->assertEquals('bg-yellow-100 text-yellow-800', $mediumEfficiencySource->getEfficiencyBadgeClass());
        $this->assertEquals('bg-red-100 text-red-800', $lowEfficiencySource->getEfficiencyBadgeClass());
    }
}
