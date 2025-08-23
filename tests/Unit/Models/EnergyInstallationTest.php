<?php

namespace Tests\Unit\Models;

use App\Models\EnergyInstallation;
use App\Models\EnergySource;
use App\Models\Customer;
use App\Models\ProductionProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnergyInstallationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_get_installation_types()
    {
        $types = EnergyInstallation::getInstallationTypes();

        $this->assertIsArray($types);
        $this->assertArrayHasKey('residential', $types);
        $this->assertArrayHasKey('commercial', $types);
        $this->assertArrayHasKey('industrial', $types);
        $this->assertArrayHasKey('utility_scale', $types);
        $this->assertArrayHasKey('community', $types);
        $this->assertArrayHasKey('microgrid', $types);
        $this->assertArrayHasKey('off_grid', $types);
        $this->assertArrayHasKey('grid_tied', $types);
    }

    /** @test */
    public function it_can_get_statuses()
    {
        $statuses = EnergyInstallation::getStatuses();

        $this->assertIsArray($statuses);
        $this->assertArrayHasKey('planned', $statuses);
        $this->assertArrayHasKey('approved', $statuses);
        $this->assertArrayHasKey('in_progress', $statuses);
        $this->assertArrayHasKey('completed', $statuses);
        $this->assertArrayHasKey('operational', $statuses);
        $this->assertArrayHasKey('maintenance', $statuses);
        $this->assertArrayHasKey('decommissioned', $statuses);
        $this->assertArrayHasKey('cancelled', $statuses);
    }

    /** @test */
    public function it_can_get_priorities()
    {
        $priorities = EnergyInstallation::getPriorities();

        $this->assertIsArray($priorities);
        $this->assertArrayHasKey('low', $priorities);
        $this->assertArrayHasKey('medium', $priorities);
        $this->assertArrayHasKey('high', $priorities);
        $this->assertArrayHasKey('urgent', $priorities);
        $this->assertArrayHasKey('critical', $priorities);
    }

    /** @test */
    public function it_has_energy_source_relationship()
    {
        $energySource = EnergySource::factory()->create();
        $installation = EnergyInstallation::factory()->create([
            'energy_source_id' => $energySource->id
        ]);

        $this->assertInstanceOf(EnergySource::class, $installation->energySource);
        $this->assertEquals($energySource->id, $installation->energySource->id);
    }

    /** @test */
    public function it_has_customer_relationship()
    {
        $customer = Customer::factory()->create();
        $installation = EnergyInstallation::factory()->create([
            'customer_id' => $customer->id
        ]);

        $this->assertInstanceOf(Customer::class, $installation->customer);
        $this->assertEquals($customer->id, $installation->customer->id);
    }

    /** @test */
    public function it_has_project_relationship()
    {
        $project = ProductionProject::factory()->create();
        $installation = EnergyInstallation::factory()->create([
            'project_id' => $project->id
        ]);

        $this->assertInstanceOf(ProductionProject::class, $installation->project);
        $this->assertEquals($project->id, $installation->project->id);
    }

    /** @test */
    public function it_has_installed_by_relationship()
    {
        $user = User::factory()->create();
        $installation = EnergyInstallation::factory()->create([
            'installed_by_id' => $user->id
        ]);

        $this->assertInstanceOf(User::class, $installation->installedBy);
        $this->assertEquals($user->id, $installation->installedBy->id);
    }

    /** @test */
    public function it_has_managed_by_relationship()
    {
        $user = User::factory()->create();
        $installation = EnergyInstallation::factory()->create([
            'managed_by_id' => $user->id
        ]);

        $this->assertInstanceOf(User::class, $installation->managedBy);
        $this->assertEquals($user->id, $installation->managedBy->id);
    }

    /** @test */
    public function it_has_created_by_relationship()
    {
        $user = User::factory()->create();
        $installation = EnergyInstallation::factory()->create([
            'created_by_id' => $user->id
        ]);

        $this->assertInstanceOf(User::class, $installation->createdBy);
        $this->assertEquals($user->id, $installation->createdBy->id);
    }

    /** @test */
    public function it_has_approved_by_relationship()
    {
        $user = User::factory()->create();
        $installation = EnergyInstallation::factory()->create([
            'approved_by_id' => $user->id
        ]);

        $this->assertInstanceOf(User::class, $installation->approvedBy);
        $this->assertEquals($user->id, $installation->approvedBy->id);
    }

    /** @test */
    public function it_has_maintenance_tasks_relationship()
    {
        $installation = EnergyInstallation::factory()->create();
        
        // Asumiendo que existe un modelo MaintenanceTask
        // $maintenanceTask = MaintenanceTask::factory()->create([
        //     'energy_installation_id' => $installation->id
        // ]);

        $this->assertTrue(method_exists($installation, 'maintenanceTasks'));
    }

    /** @test */
    public function it_has_energy_productions_relationship()
    {
        $installation = EnergyInstallation::factory()->create();
        
        // Asumiendo que existe un modelo EnergyProduction
        // $energyProduction = EnergyProduction::factory()->create([
        //     'energy_installation_id' => $installation->id
        // ]);

        $this->assertTrue(method_exists($installation, 'energyProductions'));
    }

    /** @test */
    public function it_has_documents_relationship()
    {
        $installation = EnergyInstallation::factory()->create();
        
        // Asumiendo que existe un modelo Document
        // $document = Document::factory()->create([
        //     'documentable_type' => EnergyInstallation::class,
        //     'documentable_id' => $installation->id
        // ]);

        $this->assertTrue(method_exists($installation, 'documents'));
    }

    /** @test */
    public function it_can_check_if_active()
    {
        $activeInstallation = EnergyInstallation::factory()->create(['is_active' => true]);
        $inactiveInstallation = EnergyInstallation::factory()->create(['is_active' => false]);

        $this->assertTrue($activeInstallation->isActive());
        $this->assertFalse($inactiveInstallation->isActive());
    }

    /** @test */
    public function it_can_check_if_public()
    {
        $publicInstallation = EnergyInstallation::factory()->create(['is_public' => true]);
        $privateInstallation = EnergyInstallation::factory()->create(['is_public' => false]);

        $this->assertTrue($publicInstallation->isPublic());
        $this->assertFalse($privateInstallation->isPublic());
    }

    /** @test */
    public function it_can_check_if_operational()
    {
        $operationalInstallation = EnergyInstallation::factory()->create(['status' => 'operational']);
        $plannedInstallation = EnergyInstallation::factory()->create(['status' => 'planned']);

        $this->assertTrue($operationalInstallation->isOperational());
        $this->assertFalse($plannedInstallation->isOperational());
    }

    /** @test */
    public function it_can_check_if_maintenance()
    {
        $maintenanceInstallation = EnergyInstallation::factory()->create(['status' => 'maintenance']);
        $operationalInstallation = EnergyInstallation::factory()->create(['status' => 'operational']);

        $this->assertTrue($maintenanceInstallation->isMaintenance());
        $this->assertFalse($operationalInstallation->isMaintenance());
    }

    /** @test */
    public function it_can_check_if_planned()
    {
        $plannedInstallation = EnergyInstallation::factory()->create(['status' => 'planned']);
        $operationalInstallation = EnergyInstallation::factory()->create(['status' => 'operational']);

        $this->assertTrue($plannedInstallation->isPlanned());
        $this->assertFalse($operationalInstallation->isPlanned());
    }

    /** @test */
    public function it_can_check_if_high_priority()
    {
        $highPriorityInstallation = EnergyInstallation::factory()->create(['priority' => 'high']);
        $lowPriorityInstallation = EnergyInstallation::factory()->create(['priority' => 'low']);

        $this->assertTrue($highPriorityInstallation->isHighPriority());
        $this->assertFalse($lowPriorityInstallation->isHighPriority());
    }

    /** @test */
    public function it_can_check_if_critical_priority()
    {
        $criticalPriorityInstallation = EnergyInstallation::factory()->create(['priority' => 'critical']);
        $lowPriorityInstallation = EnergyInstallation::factory()->create(['priority' => 'low']);

        $this->assertTrue($criticalPriorityInstallation->isCriticalPriority());
        $this->assertFalse($lowPriorityInstallation->isCriticalPriority());
    }

    /** @test */
    public function it_can_calculate_capacity_utilization_percentage()
    {
        $installation = EnergyInstallation::factory()->create([
            'installed_capacity_kw' => 1000,
            'operational_capacity_kw' => 800
        ]);

        $utilization = $installation->getCapacityUtilizationPercentage();

        $this->assertEquals(80.0, $utilization);
    }

    /** @test */
    public function it_returns_zero_utilization_when_no_operational_capacity()
    {
        $installation = EnergyInstallation::factory()->create([
            'installed_capacity_kw' => 1000,
            'operational_capacity_kw' => null
        ]);

        $utilization = $installation->getCapacityUtilizationPercentage();

        $this->assertEquals(0.0, $utilization);
    }

    /** @test */
    public function it_can_calculate_days_since_installation()
    {
        $installation = EnergyInstallation::factory()->create([
            'installation_date' => now()->subDays(5)
        ]);

        $days = $installation->getDaysSinceInstallation();

        $this->assertEquals(5, $days);
    }

    /** @test */
    public function it_returns_null_days_when_no_installation_date()
    {
        $installation = EnergyInstallation::factory()->create([
            'installation_date' => null
        ]);

        $days = $installation->getDaysSinceInstallation();

        $this->assertNull($days);
    }

    /** @test */
    public function it_can_calculate_days_until_maintenance()
    {
        $installation = EnergyInstallation::factory()->create([
            'next_maintenance_date' => now()->addDays(10)
        ]);

        $days = $installation->getDaysUntilMaintenance();

        $this->assertEquals(10, $days);
    }

    /** @test */
    public function it_returns_null_days_when_no_maintenance_date()
    {
        $installation = EnergyInstallation::factory()->create([
            'next_maintenance_date' => null
        ]);

        $days = $installation->getDaysUntilMaintenance();

        $this->assertNull($days);
    }

    /** @test */
    public function it_can_calculate_days_until_warranty_expiry()
    {
        $installation = EnergyInstallation::factory()->create([
            'warranty_expiry_date' => now()->addDays(30)
        ]);

        $days = $installation->getDaysUntilWarrantyExpiry();

        $this->assertEquals(30, $days);
    }

    /** @test */
    public function it_returns_null_days_when_no_warranty_date()
    {
        $installation = EnergyInstallation::factory()->create([
            'warranty_expiry_date' => null
        ]);

        $days = $installation->getDaysUntilWarrantyExpiry();

        $this->assertNull($days);
    }

    /** @test */
    public function it_can_check_if_under_warranty()
    {
        $underWarrantyInstallation = EnergyInstallation::factory()->create([
            'warranty_expiry_date' => now()->addDays(30)
        ]);

        $expiredWarrantyInstallation = EnergyInstallation::factory()->create([
            'warranty_expiry_date' => now()->subDays(30)
        ]);

        $this->assertTrue($underWarrantyInstallation->isUnderWarranty());
        $this->assertFalse($expiredWarrantyInstallation->isUnderWarranty());
    }

    /** @test */
    public function it_can_check_if_maintenance_due()
    {
        $maintenanceDueInstallation = EnergyInstallation::factory()->create([
            'next_maintenance_date' => now()->subDays(5)
        ]);

        $maintenanceNotDueInstallation = EnergyInstallation::factory()->create([
            'next_maintenance_date' => now()->addDays(5)
        ]);

        $this->assertTrue($maintenanceDueInstallation->isMaintenanceDue());
        $this->assertFalse($maintenanceNotDueInstallation->isMaintenanceDue());
    }

    /** @test */
    public function it_can_check_if_overdue_maintenance()
    {
        $overdueMaintenanceInstallation = EnergyInstallation::factory()->create([
            'next_maintenance_date' => now()->subDays(10)
        ]);

        $notOverdueMaintenanceInstallation = EnergyInstallation::factory()->create([
            'next_maintenance_date' => now()->addDays(5)
        ]);

        $this->assertTrue($overdueMaintenanceInstallation->isOverdueMaintenance());
        $this->assertFalse($notOverdueMaintenanceInstallation->isOverdueMaintenance());
    }

    /** @test */
    public function it_can_format_installation_cost()
    {
        $installation = EnergyInstallation::factory()->create([
            'installation_cost' => 50000.50
        ]);

        $formatted = $installation->getFormattedInstallationCost();

        $this->assertEquals('$50,000.50', $formatted);
    }

    /** @test */
    public function it_can_format_maintenance_cost()
    {
        $installation = EnergyInstallation::factory()->create([
            'maintenance_cost_per_year' => 2500.75
        ]);

        $formatted = $installation->getFormattedMaintenanceCost();

        $this->assertEquals('$2,500.75', $formatted);
    }

    /** @test */
    public function it_can_format_installed_capacity()
    {
        $installation = EnergyInstallation::factory()->create([
            'installed_capacity_kw' => 1500.5
        ]);

        $formatted = $installation->getFormattedInstalledCapacity();

        $this->assertEquals('1,500.5 kW', $formatted);
    }

    /** @test */
    public function it_can_format_operational_capacity()
    {
        $installation = EnergyInstallation::factory()->create([
            'operational_capacity_kw' => 1200.25
        ]);

        $formatted = $installation->getFormattedOperationalCapacity();

        $this->assertEquals('1,200.25 kW', $formatted);
    }

    /** @test */
    public function it_can_format_efficiency()
    {
        $installation = EnergyInstallation::factory()->create([
            'efficiency_rating' => 85.5
        ]);

        $formatted = $installation->getFormattedEfficiency();

        $this->assertEquals('85.5%', $formatted);
    }

    /** @test */
    public function it_can_get_formatted_installation_type()
    {
        $installation = EnergyInstallation::factory()->create([
            'installation_type' => 'residential'
        ]);

        $formatted = $installation->getFormattedInstallationType();

        $this->assertEquals('Residencial', $formatted);
    }

    /** @test */
    public function it_can_get_formatted_status()
    {
        $installation = EnergyInstallation::factory()->create([
            'status' => 'operational'
        ]);

        $formatted = $installation->getFormattedStatus();

        $this->assertEquals('Operativa', $formatted);
    }

    /** @test */
    public function it_can_get_formatted_priority()
    {
        $installation = EnergyInstallation::factory()->create([
            'priority' => 'high'
        ]);

        $formatted = $installation->getFormattedPriority();

        $this->assertEquals('Alta', $formatted);
    }

    /** @test */
    public function it_can_get_status_badge_class()
    {
        $operationalInstallation = EnergyInstallation::factory()->create(['status' => 'operational']);
        $maintenanceInstallation = EnergyInstallation::factory()->create(['status' => 'maintenance']);
        $plannedInstallation = EnergyInstallation::factory()->create(['status' => 'planned']);

        $this->assertEquals('badge-success', $operationalInstallation->getStatusBadgeClass());
        $this->assertEquals('badge-warning', $maintenanceInstallation->getStatusBadgeClass());
        $this->assertEquals('badge-info', $plannedInstallation->getStatusBadgeClass());
    }

    /** @test */
    public function it_can_get_priority_badge_class()
    {
        $lowPriorityInstallation = EnergyInstallation::factory()->create(['priority' => 'low']);
        $mediumPriorityInstallation = EnergyInstallation::factory()->create(['priority' => 'medium']);
        $highPriorityInstallation = EnergyInstallation::factory()->create(['priority' => 'high']);
        $criticalPriorityInstallation = EnergyInstallation::factory()->create(['priority' => 'critical']);

        $this->assertEquals('badge-success', $lowPriorityInstallation->getPriorityBadgeClass());
        $this->assertEquals('badge-info', $mediumPriorityInstallation->getPriorityBadgeClass());
        $this->assertEquals('badge-warning', $highPriorityInstallation->getPriorityBadgeClass());
        $this->assertEquals('badge-danger', $criticalPriorityInstallation->getPriorityBadgeClass());
    }

    /** @test */
    public function it_can_get_type_badge_class()
    {
        $residentialInstallation = EnergyInstallation::factory()->create(['installation_type' => 'residential']);
        $commercialInstallation = EnergyInstallation::factory()->create(['installation_type' => 'commercial']);
        $industrialInstallation = EnergyInstallation::factory()->create(['installation_type' => 'industrial']);

        $this->assertEquals('badge-primary', $residentialInstallation->getTypeBadgeClass());
        $this->assertEquals('badge-success', $commercialInstallation->getTypeBadgeClass());
        $this->assertEquals('badge-warning', $industrialInstallation->getTypeBadgeClass());
    }

    /** @test */
    public function it_has_active_scope()
    {
        EnergyInstallation::factory()->create(['is_active' => true]);
        EnergyInstallation::factory()->create(['is_active' => false]);

        $activeInstallations = EnergyInstallation::active()->get();

        $this->assertEquals(1, $activeInstallations->count());
        $this->assertTrue($activeInstallations->first()->is_active);
    }

    /** @test */
    public function it_has_public_scope()
    {
        EnergyInstallation::factory()->create(['is_public' => true]);
        EnergyInstallation::factory()->create(['is_public' => false]);

        $publicInstallations = EnergyInstallation::public()->get();

        $this->assertEquals(1, $publicInstallations->count());
        $this->assertTrue($publicInstallations->first()->is_public);
    }

    /** @test */
    public function it_has_operational_scope()
    {
        EnergyInstallation::factory()->create(['status' => 'operational']);
        EnergyInstallation::factory()->create(['status' => 'planned']);

        $operationalInstallations = EnergyInstallation::operational()->get();

        $this->assertEquals(1, $operationalInstallations->count());
        $this->assertEquals('operational', $operationalInstallations->first()->status);
    }

    /** @test */
    public function it_has_maintenance_scope()
    {
        EnergyInstallation::factory()->create(['status' => 'maintenance']);
        EnergyInstallation::factory()->create(['status' => 'operational']);

        $maintenanceInstallations = EnergyInstallation::maintenance()->get();

        $this->assertEquals(1, $maintenanceInstallations->count());
        $this->assertEquals('maintenance', $maintenanceInstallations->first()->status);
    }

    /** @test */
    public function it_has_planned_scope()
    {
        EnergyInstallation::factory()->create(['status' => 'planned']);
        EnergyInstallation::factory()->create(['status' => 'operational']);

        $plannedInstallations = EnergyInstallation::planned()->get();

        $this->assertEquals(1, $plannedInstallations->count());
        $this->assertEquals('planned', $plannedInstallations->first()->status);
    }

    /** @test */
    public function it_has_by_type_scope()
    {
        EnergyInstallation::factory()->create(['installation_type' => 'residential']);
        EnergyInstallation::factory()->create(['installation_type' => 'commercial']);

        $residentialInstallations = EnergyInstallation::byType('residential')->get();

        $this->assertEquals(1, $residentialInstallations->count());
        $this->assertEquals('residential', $residentialInstallations->first()->installation_type);
    }

    /** @test */
    public function it_has_by_priority_scope()
    {
        EnergyInstallation::factory()->create(['priority' => 'high']);
        EnergyInstallation::factory()->create(['priority' => 'low']);

        $highPriorityInstallations = EnergyInstallation::byPriority('high')->get();

        $this->assertEquals(1, $highPriorityInstallations->count());
        $this->assertEquals('high', $highPriorityInstallations->first()->priority);
    }

    /** @test */
    public function it_has_by_energy_source_scope()
    {
        $energySource = EnergySource::factory()->create();
        EnergyInstallation::factory()->create(['energy_source_id' => $energySource->id]);
        EnergyInstallation::factory()->create(['energy_source_id' => EnergySource::factory()->create()->id]);

        $installationsBySource = EnergyInstallation::byEnergySource($energySource->id)->get();

        $this->assertEquals(1, $installationsBySource->count());
        $this->assertEquals($energySource->id, $installationsBySource->first()->energy_source_id);
    }

    /** @test */
    public function it_has_by_customer_scope()
    {
        $customer = Customer::factory()->create();
        EnergyInstallation::factory()->create(['customer_id' => $customer->id]);
        EnergyInstallation::factory()->create(['customer_id' => Customer::factory()->create()->id]);

        $installationsByCustomer = EnergyInstallation::byCustomer($customer->id)->get();

        $this->assertEquals(1, $installationsByCustomer->count());
        $this->assertEquals($customer->id, $installationsByCustomer->first()->customer_id);
    }

    /** @test */
    public function it_has_by_project_scope()
    {
        $project = ProductionProject::factory()->create();
        EnergyInstallation::factory()->create(['project_id' => $project->id]);
        EnergyInstallation::factory()->create(['project_id' => ProductionProject::factory()->create()->id]);

        $installationsByProject = EnergyInstallation::byProject($project->id)->get();

        $this->assertEquals(1, $installationsByProject->count());
        $this->assertEquals($project->id, $installationsByProject->first()->project_id);
    }

    /** @test */
    public function it_has_high_capacity_scope()
    {
        EnergyInstallation::factory()->create(['installed_capacity_kw' => 5000]);
        EnergyInstallation::factory()->create(['installed_capacity_kw' => 500]);

        $highCapacityInstallations = EnergyInstallation::highCapacity(1000)->get();

        $this->assertEquals(1, $highCapacityInstallations->count());
        $this->assertGreaterThan(1000, $highCapacityInstallations->first()->installed_capacity_kw);
    }

    /** @test */
    public function it_has_high_efficiency_scope()
    {
        EnergyInstallation::factory()->create(['efficiency_rating' => 90]);
        EnergyInstallation::factory()->create(['efficiency_rating' => 70]);

        $highEfficiencyInstallations = EnergyInstallation::highEfficiency(80)->get();

        $this->assertEquals(1, $highEfficiencyInstallations->count());
        $this->assertGreaterThan(80, $highEfficiencyInstallations->first()->efficiency_rating);
    }

    /** @test */
    public function it_has_recent_scope()
    {
        EnergyInstallation::factory()->create(['created_at' => now()->subDays(5)]);
        EnergyInstallation::factory()->create(['created_at' => now()->subDays(15)]);

        $recentInstallations = EnergyInstallation::recent(10)->get();

        $this->assertEquals(1, $recentInstallations->count());
        $this->assertTrue($recentInstallations->first()->created_at->isAfter(now()->subDays(10)));
    }

    /** @test */
    public function it_has_search_scope()
    {
        EnergyInstallation::factory()->create(['name' => 'Solar Panel Installation']);
        EnergyInstallation::factory()->create(['name' => 'Wind Turbine Installation']);

        $searchResults = EnergyInstallation::search('Solar')->get();

        $this->assertEquals(1, $searchResults->count());
        $this->assertStringContainsString('Solar', $searchResults->first()->name);
    }

    /** @test */
    public function it_can_generate_installation_number()
    {
        $installation = EnergyInstallation::factory()->create(['installation_number' => null]);

        $this->assertNotNull($installation->installation_number);
        $this->assertStringStartsWith('INST-', $installation->installation_number);
    }

    /** @test */
    public function it_can_validate_installation_data()
    {
        $installation = EnergyInstallation::factory()->make();

        $this->assertTrue($installation->isValid());
    }

    /** @test */
    public function it_can_calculate_total_cost()
    {
        $installation = EnergyInstallation::factory()->create([
            'installation_cost' => 50000,
            'maintenance_cost_per_year' => 2500
        ]);

        $totalCost = $installation->getTotalCost();

        $this->assertEquals(52500, $totalCost);
    }

    /** @test */
    public function it_can_calculate_annual_savings()
    {
        $installation = EnergyInstallation::factory()->create([
            'installed_capacity_kw' => 1000,
            'efficiency_rating' => 85
        ]);

        // Asumiendo un cálculo de ahorro anual
        $annualSavings = $installation->getAnnualSavings();

        $this->assertIsNumeric($annualSavings);
        $this->assertGreaterThanOrEqual(0, $annualSavings);
    }

    /** @test */
    public function it_can_calculate_payback_period()
    {
        $installation = EnergyInstallation::factory()->create([
            'installation_cost' => 50000,
            'maintenance_cost_per_year' => 2500
        ]);

        // Asumiendo un cálculo de período de recuperación
        $paybackPeriod = $installation->getPaybackPeriod();

        $this->assertIsNumeric($paybackPeriod);
        $this->assertGreaterThanOrEqual(0, $paybackPeriod);
    }

    /** @test */
    public function it_can_calculate_carbon_footprint_reduction()
    {
        $installation = EnergyInstallation::factory()->create([
            'installed_capacity_kw' => 1000,
            'efficiency_rating' => 85
        ]);

        // Asumiendo un cálculo de reducción de huella de carbono
        $carbonReduction = $installation->getCarbonFootprintReduction();

        $this->assertIsNumeric($carbonReduction);
        $this->assertGreaterThanOrEqual(0, $carbonReduction);
    }

    /** @test */
    public function it_can_get_installation_age()
    {
        $installation = EnergyInstallation::factory()->create([
            'installation_date' => now()->subYears(2)->subMonths(3)
        ]);

        $age = $installation->getInstallationAge();

        $this->assertEquals('2 años, 3 meses', $age);
    }

    /** @test */
    public function it_can_get_next_maintenance_urgency()
    {
        $urgentMaintenance = EnergyInstallation::factory()->create([
            'next_maintenance_date' => now()->addDays(2)
        ]);

        $normalMaintenance = EnergyInstallation::factory()->create([
            'next_maintenance_date' => now()->addDays(30)
        ]);

        $this->assertEquals('urgent', $urgentMaintenance->getNextMaintenanceUrgency());
        $this->assertEquals('normal', $normalMaintenance->getNextMaintenanceUrgency());
    }

    /** @test */
    public function it_can_get_warranty_status()
    {
        $underWarranty = EnergyInstallation::factory()->create([
            'warranty_expiry_date' => now()->addDays(30)
        ]);

        $expiredWarranty = EnergyInstallation::factory()->create([
            'warranty_expiry_date' => now()->subDays(30)
        ]);

        $this->assertEquals('under_warranty', $underWarranty->getWarrantyStatus());
        $this->assertEquals('expired', $expiredWarranty->getWarrantyStatus());
    }

    /** @test */
    public function it_can_get_installation_performance_rating()
    {
        $highPerformance = EnergyInstallation::factory()->create([
            'efficiency_rating' => 95,
            'installed_capacity_kw' => 5000
        ]);

        $lowPerformance = EnergyInstallation::factory()->create([
            'efficiency_rating' => 60,
            'installed_capacity_kw' => 500
        ]);

        $this->assertEquals('excellent', $highPerformance->getInstallationPerformanceRating());
        $this->assertEquals('poor', $lowPerformance->getInstallationPerformanceRating());
    }
}
