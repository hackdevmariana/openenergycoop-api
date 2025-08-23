<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\ProductionProject;
use App\Models\User;
use App\Models\Organization;
use App\Models\EnergySource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ProductionProjectTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ProductionProject $project;
    protected User $user;
    protected Organization $organization;
    protected EnergySource $energySource;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create();
        $this->energySource = EnergySource::factory()->create();
        
        $this->project = ProductionProject::factory()->create([
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
            'project_type' => 'solar_farm',
            'status' => 'planning',
            'priority' => 'medium',
            'capacity_kw' => 1000.0,
            'completion_percentage' => 25.0,
            'budget' => 100000.00,
            'spent_amount' => 25000.00,
            'planned_capacity_mw' => 1.0,
            'actual_capacity_mw' => 0.25,
            'efficiency_rating' => 85.5,
            'start_date' => now()->subMonths(3),
            'expected_completion_date' => now()->addMonths(9),
        ]);
    }

    /** @test */
    public function it_has_correct_project_types()
    {
        $types = ProductionProject::getProjectTypes();
        
        $this->assertIsArray($types);
        $this->assertArrayHasKey('solar_farm', $types);
        $this->assertArrayHasKey('wind_farm', $types);
        $this->assertArrayHasKey('hydroelectric', $types);
        $this->assertArrayHasKey('biomass', $types);
        $this->assertArrayHasKey('geothermal', $types);
        $this->assertArrayHasKey('hybrid', $types);
        $this->assertArrayHasKey('storage', $types);
        $this->assertArrayHasKey('grid_upgrade', $types);
        $this->assertArrayHasKey('other', $types);
        
        $this->assertEquals('Granja Solar', $types['solar_farm']);
        $this->assertEquals('Parque Eólico', $types['wind_farm']);
    }

    /** @test */
    public function it_has_correct_statuses()
    {
        $statuses = ProductionProject::getStatuses();
        
        $this->assertIsArray($statuses);
        $this->assertArrayHasKey('planning', $statuses);
        $this->assertArrayHasKey('approved', $statuses);
        $this->assertArrayHasKey('in_progress', $statuses);
        $this->assertArrayHasKey('on_hold', $statuses);
        $this->assertArrayHasKey('completed', $statuses);
        $this->assertArrayHasKey('cancelled', $statuses);
        $this->assertArrayHasKey('maintenance', $statuses);
        
        $this->assertEquals('Planificación', $statuses['planning']);
        $this->assertEquals('En Progreso', $statuses['in_progress']);
    }

    /** @test */
    public function it_has_correct_priorities()
    {
        $priorities = ProductionProject::getPriorities();
        
        $this->assertIsArray($priorities);
        $this->assertArrayHasKey('low', $priorities);
        $this->assertArrayHasKey('medium', $priorities);
        $this->assertArrayHasKey('high', $priorities);
        $this->assertArrayHasKey('urgent', $priorities);
        $this->assertArrayHasKey('critical', $priorities);
        
        $this->assertEquals('Baja', $priorities['low']);
        $this->assertEquals('Media', $priorities['medium']);
        $this->assertEquals('Alta', $priorities['high']);
    }

    /** @test */
    public function it_has_organization_relationship()
    {
        $this->assertInstanceOf(Organization::class, $this->project->organization);
        $this->assertEquals($this->organization->id, $this->project->organization->id);
    }

    /** @test */
    public function it_has_owner_user_relationship()
    {
        $this->assertInstanceOf(User::class, $this->project->ownerUser);
        $this->assertEquals($this->user->id, $this->project->ownerUser->id);
    }

    /** @test */
    public function it_has_energy_source_relationship()
    {
        $this->assertInstanceOf(EnergySource::class, $this->project->energySource);
        $this->assertEquals($this->energySource->id, $this->project->energySource->id);
    }

    /** @test */
    public function it_has_created_by_relationship()
    {
        $this->assertInstanceOf(User::class, $this->project->createdBy);
        $this->assertEquals($this->user->id, $this->project->createdBy->id);
    }

    /** @test */
    public function it_can_check_if_active()
    {
        $this->project->update(['status' => 'planning']);
        $this->assertFalse($this->project->isActive());
        
        $this->project->update(['status' => 'approved']);
        $this->assertTrue($this->project->isActive());
        
        $this->project->update(['status' => 'in_progress']);
        $this->assertTrue($this->project->isActive());
    }

    /** @test */
    public function it_can_check_if_planning()
    {
        $this->project->update(['status' => 'planning']);
        $this->assertTrue($this->project->isPlanning());
        
        $this->project->update(['status' => 'in_progress']);
        $this->assertFalse($this->project->isPlanning());
    }

    /** @test */
    public function it_can_check_if_in_progress()
    {
        $this->project->update(['status' => 'in_progress']);
        $this->assertTrue($this->project->isInProgress());
        
        $this->project->update(['status' => 'planning']);
        $this->assertFalse($this->project->isInProgress());
    }

    /** @test */
    public function it_can_check_if_completed()
    {
        $this->project->update(['status' => 'completed']);
        $this->assertTrue($this->project->isCompleted());
        
        $this->project->update(['status' => 'planning']);
        $this->assertFalse($this->project->isCompleted());
    }

    /** @test */
    public function it_can_check_if_cancelled()
    {
        $this->project->update(['status' => 'cancelled']);
        $this->assertTrue($this->project->isCancelled());
        
        $this->project->update(['status' => 'planning']);
        $this->assertFalse($this->project->isCancelled());
    }

    /** @test */
    public function it_can_check_if_overdue()
    {
        // Proyecto con fecha de finalización en el pasado
        $this->project->update([
            'expected_completion_date' => now()->subDays(10),
            'status' => 'in_progress'
        ]);
        $this->assertTrue($this->project->isOverdue());
        
        // Proyecto con fecha de finalización en el futuro
        $this->project->update(['expected_completion_date' => now()->addDays(10)]);
        $this->assertFalse($this->project->isOverdue());
        
        // Proyecto completado no puede estar retrasado
        $this->project->update(['status' => 'completed']);
        $this->assertFalse($this->project->isOverdue());
    }

    /** @test */
    public function it_can_check_if_high_priority()
    {
        $this->project->update(['priority' => 'low']);
        $this->assertFalse($this->project->isHighPriority());
        
        $this->project->update(['priority' => 'high']);
        $this->assertTrue($this->project->isHighPriority());
        
        $this->project->update(['priority' => 'urgent']);
        $this->assertTrue($this->project->isHighPriority());
        
        $this->project->update(['priority' => 'critical']);
        $this->assertTrue($this->project->isHighPriority());
    }

    /** @test */
    public function it_can_calculate_progress_percentage()
    {
        $this->project->update([
            'budget' => 100000.00,
            'spent_amount' => 25000.00
        ]);
        
        $this->assertEquals(25.0, $this->project->getProgressPercentage());
        
        // Sin presupuesto
        $this->project->update(['budget' => 0]);
        $this->assertEquals(0, $this->project->getProgressPercentage());
        
        // Presupuesto gastado excede el presupuesto
        $this->project->update([
            'budget' => 100000.00,
            'spent_amount' => 150000.00
        ]);
        $this->assertEquals(100.0, $this->project->getProgressPercentage());
    }

    /** @test */
    public function it_can_calculate_capacity_progress_percentage()
    {
        $this->project->update([
            'planned_capacity_mw' => 1.0,
            'actual_capacity_mw' => 0.25
        ]);
        
        $this->assertEquals(25.0, $this->project->getCapacityProgressPercentage());
        
        // Sin capacidad planificada
        $this->project->update(['planned_capacity_mw' => 0]);
        $this->assertEquals(0, $this->project->getCapacityProgressPercentage());
        
        // Capacidad actual excede la planificada
        $this->project->update([
            'planned_capacity_mw' => 1.0,
            'actual_capacity_mw' => 1.5
        ]);
        $this->assertEquals(100.0, $this->project->getCapacityProgressPercentage());
    }

    /** @test */
    public function it_can_calculate_remaining_budget()
    {
        $this->project->update([
            'budget' => 100000.00,
            'spent_amount' => 25000.00
        ]);
        
        $this->assertEquals(75000.00, $this->project->getRemainingBudget());
    }

    /** @test */
    public function it_can_calculate_days_until_completion()
    {
        $this->project->update(['expected_completion_date' => now()->addDays(30)]);
        
        $this->assertEquals(30, $this->project->getDaysUntilCompletion());
        
        // Sin fecha de finalización
        $this->project->update(['expected_completion_date' => null]);
        $this->assertEquals(0, $this->project->getDaysUntilCompletion());
    }

    /** @test */
    public function it_can_calculate_days_overdue()
    {
        $this->project->update([
            'expected_completion_date' => now()->subDays(15),
            'status' => 'in_progress'
        ]);
        
        $this->assertEquals(15, $this->project->getDaysOverdue());
        
        // Proyecto no retrasado
        $this->project->update(['expected_completion_date' => now()->addDays(15)]);
        $this->assertEquals(0, $this->project->getDaysOverdue());
    }

    /** @test */
    public function it_can_calculate_project_duration()
    {
        $this->project->update([
            'start_date' => now()->subDays(30),
            'expected_completion_date' => now()->addDays(30)
        ]);
        
        $this->assertEquals(60, $this->project->getProjectDuration());
        
        // Solo fecha de inicio
        $this->project->update(['expected_completion_date' => null]);
        $duration = $this->project->getProjectDuration();
        $this->assertGreaterThan(0, $duration);
    }

    /** @test */
    public function it_can_get_formatted_project_type()
    {
        $this->project->update(['project_type' => 'solar_farm']);
        $this->assertEquals('Granja Solar', $this->project->getFormattedProjectType());
        
        $this->project->update(['project_type' => 'wind_farm']);
        $this->assertEquals('Parque Eólico', $this->project->getFormattedProjectType());
        
        $this->project->update(['project_type' => 'invalid_type']);
        $this->assertEquals('Desconocido', $this->project->getFormattedProjectType());
    }

    /** @test */
    public function it_can_get_formatted_status()
    {
        $this->project->update(['status' => 'planning']);
        $this->assertEquals('Planificación', $this->project->getFormattedStatus());
        
        $this->project->update(['status' => 'in_progress']);
        $this->assertEquals('En Progreso', $this->project->getFormattedStatus());
        
        $this->project->update(['status' => 'invalid_status']);
        $this->assertEquals('Desconocido', $this->project->getFormattedStatus());
    }

    /** @test */
    public function it_can_get_formatted_priority()
    {
        $this->project->update(['priority' => 'low']);
        $this->assertEquals('Baja', $this->project->getFormattedPriority());
        
        $this->project->update(['priority' => 'high']);
        $this->assertEquals('Alta', $this->project->getFormattedPriority());
        
        $this->project->update(['priority' => 'invalid_priority']);
        $this->assertEquals('Desconocida', $this->project->getFormattedPriority());
    }

    /** @test */
    public function it_can_get_formatted_budget()
    {
        $this->project->update(['budget' => 100000.50]);
        $this->assertEquals('$100,000.50', $this->project->getFormattedBudget());
    }

    /** @test */
    public function it_can_get_formatted_spent_amount()
    {
        $this->project->update(['spent_amount' => 25000.75]);
        $this->assertEquals('$25,000.75', $this->project->getFormattedSpentAmount());
    }

    /** @test */
    public function it_can_get_formatted_remaining_budget()
    {
        $this->project->update([
            'budget' => 100000.00,
            'spent_amount' => 25000.00
        ]);
        $this->assertEquals('$75,000.00', $this->project->getFormattedRemainingBudget());
    }

    /** @test */
    public function it_can_get_formatted_planned_capacity()
    {
        $this->project->update(['planned_capacity_mw' => 1.5]);
        $this->assertEquals('1.50 MW', $this->project->getFormattedPlannedCapacity());
    }

    /** @test */
    public function it_can_get_formatted_actual_capacity()
    {
        $this->project->update(['actual_capacity_mw' => 0.75]);
        $this->assertEquals('0.75 MW', $this->project->getFormattedActualCapacity());
        
        $this->project->update(['actual_capacity_mw' => null]);
        $this->assertEquals('N/A', $this->project->getFormattedActualCapacity());
    }

    /** @test */
    public function it_can_get_formatted_efficiency_rating()
    {
        $this->project->update(['efficiency_rating' => 85.5]);
        $this->assertEquals('85.50%', $this->project->getFormattedEfficiencyRating());
        
        $this->project->update(['efficiency_rating' => null]);
        $this->assertEquals('N/A', $this->project->getFormattedEfficiencyRating());
    }

    /** @test */
    public function it_can_get_formatted_dates()
    {
        $date = now()->subDays(30);
        $this->project->update(['start_date' => $date]);
        
        $this->assertEquals($date->format('d/m/Y'), $this->project->getFormattedStartDate());
        
        $this->project->update(['start_date' => null]);
        $this->assertEquals('N/A', $this->project->getFormattedStartDate());
    }

    /** @test */
    public function it_can_get_formatted_progress()
    {
        $this->project->update([
            'budget' => 100000.00,
            'spent_amount' => 25000.00
        ]);
        
        $this->assertEquals('25.0%', $this->project->getFormattedProgress());
    }

    /** @test */
    public function it_can_get_formatted_capacity_progress()
    {
        $this->project->update([
            'planned_capacity_mw' => 1.0,
            'actual_capacity_mw' => 0.25
        ]);
        
        $this->assertEquals('25.0%', $this->project->getFormattedCapacityProgress());
    }

    /** @test */
    public function it_can_get_formatted_days_until_completion()
    {
        $this->project->update(['expected_completion_date' => now()->addDays(30)]);
        $this->assertEquals('30 días restantes', $this->project->getFormattedDaysUntilCompletion());
        
        $this->project->update(['expected_completion_date' => now()->subDays(15)]);
        $this->assertEquals('15 días de retraso', $this->project->getFormattedDaysUntilCompletion());
        
        $this->project->update(['expected_completion_date' => now()]);
        $this->assertEquals('Vence hoy', $this->project->getFormattedDaysUntilCompletion());
    }

    /** @test */
    public function it_can_get_status_badge_class()
    {
        $this->project->update(['status' => 'planning']);
        $this->assertEquals('bg-blue-100 text-blue-800', $this->project->getStatusBadgeClass());
        
        $this->project->update(['status' => 'in_progress']);
        $this->assertEquals('bg-yellow-100 text-yellow-800', $this->project->getStatusBadgeClass());
        
        $this->project->update(['status' => 'completed']);
        $this->assertEquals('bg-green-100 text-green-800', $this->project->getStatusBadgeClass());
        
        $this->project->update(['status' => 'cancelled']);
        $this->assertEquals('bg-red-100 text-red-800', $this->project->getStatusBadgeClass());
    }

    /** @test */
    public function it_can_get_priority_badge_class()
    {
        $this->project->update(['priority' => 'low']);
        $this->assertEquals('bg-gray-100 text-gray-800', $this->project->getPriorityBadgeClass());
        
        $this->project->update(['priority' => 'medium']);
        $this->assertEquals('bg-blue-100 text-blue-800', $this->project->getPriorityBadgeClass());
        
        $this->project->update(['priority' => 'high']);
        $this->assertEquals('bg-yellow-100 text-yellow-800', $this->project->getPriorityBadgeClass());
        
        $this->project->update(['priority' => 'critical']);
        $this->assertEquals('bg-red-100 text-red-800', $this->project->getPriorityBadgeClass());
    }

    /** @test */
    public function it_can_get_project_type_badge_class()
    {
        $this->project->update(['project_type' => 'solar_farm']);
        $this->assertEquals('bg-yellow-100 text-yellow-800', $this->project->getProjectTypeBadgeClass());
        
        $this->project->update(['project_type' => 'wind_farm']);
        $this->assertEquals('bg-blue-100 text-blue-800', $this->project->getProjectTypeBadgeClass());
        
        $this->project->update(['project_type' => 'hydroelectric']);
        $this->assertEquals('bg-cyan-100 text-cyan-800', $this->project->getProjectTypeBadgeClass());
    }

    /** @test */
    public function it_can_get_overdue_badge_class()
    {
        // Proyecto no retrasado
        $this->project->update(['expected_completion_date' => now()->addDays(30)]);
        $this->assertEquals('bg-gray-100 text-gray-800', $this->project->getOverdueBadgeClass());
        
        // Retrasado menos de 7 días
        $this->project->update(['expected_completion_date' => now()->subDays(5)]);
        $this->assertEquals('bg-yellow-100 text-yellow-800', $this->project->getOverdueBadgeClass());
        
        // Retrasado entre 7 y 30 días
        $this->project->update(['expected_completion_date' => now()->subDays(20)]);
        $this->assertEquals('bg-orange-100 text-orange-800', $this->project->getOverdueBadgeClass());
        
        // Retrasado más de 30 días
        $this->project->update(['expected_completion_date' => now()->subDays(45)]);
        $this->assertEquals('bg-red-100 text-red-800', $this->project->getOverdueBadgeClass());
    }

    /** @test */
    public function it_can_scope_active_projects()
    {
        ProductionProject::factory()->create([
            'status' => 'approved',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        ProductionProject::factory()->create([
            'status' => 'planning',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $activeProjects = ProductionProject::active()->get();
        
        $this->assertCount(1, $activeProjects);
        $this->assertEquals('approved', $activeProjects->first()->status);
    }

    /** @test */
    public function it_can_scope_completed_projects()
    {
        ProductionProject::factory()->create([
            'status' => 'completed',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        ProductionProject::factory()->create([
            'status' => 'in_progress',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $completedProjects = ProductionProject::completed()->get();
        
        $this->assertCount(1, $completedProjects);
        $this->assertEquals('completed', $completedProjects->first()->status);
    }

    /** @test */
    public function it_can_scope_projects_by_type()
    {
        ProductionProject::factory()->create([
            'project_type' => 'solar_farm',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        ProductionProject::factory()->create([
            'project_type' => 'wind_farm',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $solarProjects = ProductionProject::byType('solar_farm')->get();
        
        $this->assertCount(1, $solarProjects);
        $this->assertEquals('solar_farm', $solarProjects->first()->project_type);
    }

    /** @test */
    public function it_can_scope_projects_by_status()
    {
        ProductionProject::factory()->create([
            'status' => 'planning',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        ProductionProject::factory()->create([
            'status' => 'in_progress',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $planningProjects = ProductionProject::byStatus('planning')->get();
        
        $this->assertCount(1, $planningProjects);
        $this->assertEquals('planning', $planningProjects->first()->status);
    }

    /** @test */
    public function it_can_scope_high_priority_projects()
    {
        ProductionProject::factory()->create([
            'priority' => 'high',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        ProductionProject::factory()->create([
            'priority' => 'low',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $highPriorityProjects = ProductionProject::highPriority()->get();
        
        $this->assertCount(1, $highPriorityProjects);
        $this->assertEquals('high', $highPriorityProjects->first()->priority);
    }

    /** @test */
    public function it_can_scope_overdue_projects()
    {
        ProductionProject::factory()->create([
            'expected_completion_date' => now()->subDays(10),
            'status' => 'in_progress',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        ProductionProject::factory()->create([
            'expected_completion_date' => now()->addDays(10),
            'status' => 'in_progress',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $overdueProjects = ProductionProject::overdue()->get();
        
        $this->assertCount(1, $overdueProjects);
        $this->assertTrue($overdueProjects->first()->isOverdue());
    }
}
