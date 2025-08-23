<?php

namespace Tests\Unit\Models;

use App\Models\Milestone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MilestoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_milestone_has_fillable_attributes()
    {
        $milestone = new Milestone();
        
        $fillable = [
            'title',
            'description',
            'milestone_type',
            'status',
            'priority',
            'target_date',
            'start_date',
            'completion_date',
            'progress_percentage',
            'budget',
            'actual_cost',
            'parent_milestone_id',
            'assigned_to',
            'tags',
            'dependencies',
            'risk_level',
            'notes',
            'created_by',
        ];

        $this->assertEquals($fillable, $milestone->getFillable());
    }

    public function test_milestone_has_correct_casts()
    {
        $milestone = new Milestone();
        
        $casts = [
            'target_date' => 'date',
            'start_date' => 'date',
            'completion_date' => 'date',
            'progress_percentage' => 'decimal:2',
            'budget' => 'decimal:2',
            'actual_cost' => 'decimal:2',
            'tags' => 'array',
            'dependencies' => 'array',
        ];

        $this->assertEquals($casts, $milestone->getCasts());
    }

    public function test_milestone_has_milestone_types()
    {
        $types = Milestone::getMilestoneTypes();
        
        $this->assertIsArray($types);
        $this->assertContains(Milestone::MILESTONE_TYPE_PROJECT, $types);
        $this->assertContains(Milestone::MILESTONE_TYPE_PHASE, $types);
        $this->assertContains(Milestone::MILESTONE_TYPE_DELIVERABLE, $types);
        $this->assertContains(Milestone::MILESTONE_TYPE_REVIEW, $types);
    }

    public function test_milestone_has_statuses()
    {
        $statuses = Milestone::getStatuses();
        
        $this->assertIsArray($statuses);
        $this->assertContains(Milestone::STATUS_NOT_STARTED, $statuses);
        $this->assertContains(Milestone::STATUS_IN_PROGRESS, $statuses);
        $this->assertContains(Milestone::STATUS_COMPLETED, $statuses);
        $this->assertContains(Milestone::STATUS_ON_HOLD, $statuses);
        $this->assertContains(Milestone::STATUS_CANCELLED, $statuses);
    }

    public function test_milestone_has_priorities()
    {
        $priorities = Milestone::getPriorities();
        
        $this->assertIsArray($priorities);
        $this->assertContains(Milestone::PRIORITY_LOW, $priorities);
        $this->assertContains(Milestone::PRIORITY_MEDIUM, $priorities);
        $this->assertContains(Milestone::PRIORITY_HIGH, $priorities);
        $this->assertContains(Milestone::PRIORITY_CRITICAL, $priorities);
    }

    public function test_milestone_belongs_to_parent_milestone()
    {
        $parentMilestone = Milestone::factory()->create();
        $milestone = Milestone::factory()->create(['parent_milestone_id' => $parentMilestone->id]);

        $this->assertInstanceOf(Milestone::class, $milestone->parentMilestone);
        $this->assertEquals($parentMilestone->id, $milestone->parentMilestone->id);
    }

    public function test_milestone_has_many_sub_milestones()
    {
        $parentMilestone = Milestone::factory()->create();
        $subMilestones = Milestone::factory()->count(3)->create(['parent_milestone_id' => $parentMilestone->id]);

        $this->assertCount(3, $parentMilestone->subMilestones);
        $this->assertInstanceOf(Milestone::class, $parentMilestone->subMilestones->first());
    }

    public function test_milestone_belongs_to_assigned_user()
    {
        $user = User::factory()->create();
        $milestone = Milestone::factory()->create(['assigned_to' => $user->id]);

        $this->assertInstanceOf(User::class, $milestone->assignedTo);
        $this->assertEquals($user->id, $milestone->assignedTo->id);
    }

    public function test_milestone_belongs_to_created_by_user()
    {
        $user = User::factory()->create();
        $milestone = Milestone::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $milestone->createdBy);
        $this->assertEquals($user->id, $milestone->createdBy->id);
    }

    public function test_milestone_scopes()
    {
        // Crear hitos con diferentes tipos
        Milestone::factory()->create(['milestone_type' => Milestone::MILESTONE_TYPE_PROJECT]);
        Milestone::factory()->create(['milestone_type' => Milestone::MILESTONE_TYPE_PHASE]);
        
        $projectMilestones = Milestone::byType(Milestone::MILESTONE_TYPE_PROJECT)->get();
        $this->assertCount(1, $projectMilestones);
        $this->assertEquals(Milestone::MILESTONE_TYPE_PROJECT, $projectMilestones->first()->milestone_type);
    }

    public function test_milestone_status_scopes()
    {
        Milestone::factory()->create(['status' => Milestone::STATUS_NOT_STARTED]);
        Milestone::factory()->create(['status' => Milestone::STATUS_IN_PROGRESS]);
        Milestone::factory()->create(['status' => Milestone::STATUS_COMPLETED]);

        $notStarted = Milestone::notStarted()->get();
        $inProgress = Milestone::inProgress()->get();
        $completed = Milestone::completed()->get();

        $this->assertCount(1, $notStarted);
        $this->assertCount(1, $inProgress);
        $this->assertCount(1, $completed);
    }

    public function test_milestone_priority_scopes()
    {
        Milestone::factory()->create(['priority' => Milestone::PRIORITY_LOW]);
        Milestone::factory()->create(['priority' => Milestone::PRIORITY_HIGH]);
        Milestone::factory()->create(['priority' => Milestone::PRIORITY_CRITICAL]);

        $highPriority = Milestone::highPriority()->get();
        $this->assertCount(2, $highPriority); // HIGH y CRITICAL
    }

    public function test_milestone_date_scopes()
    {
        $overdueMilestone = Milestone::factory()->create([
            'target_date' => now()->subDays(5),
            'status' => Milestone::STATUS_IN_PROGRESS
        ]);

        $dueSoonMilestone = Milestone::factory()->create([
            'target_date' => now()->addDays(3),
            'status' => Milestone::STATUS_NOT_STARTED
        ]);

        $overdue = Milestone::overdueStatus()->get();
        $dueSoon = Milestone::dueSoon(7)->get();

        $this->assertCount(1, $overdue);
        $this->assertCount(1, $dueSoon);
    }

    public function test_milestone_boolean_checks()
    {
        $notStarted = Milestone::factory()->create(['status' => Milestone::STATUS_NOT_STARTED]);
        $inProgress = Milestone::factory()->create(['status' => Milestone::STATUS_IN_PROGRESS]);
        $completed = Milestone::factory()->create(['status' => Milestone::STATUS_COMPLETED]);
        $cancelled = Milestone::factory()->create(['status' => Milestone::STATUS_CANCELLED]);

        $this->assertTrue($notStarted->isNotStarted());
        $this->assertTrue($inProgress->isInProgress());
        $this->assertTrue($completed->isCompleted());
        $this->assertTrue($cancelled->isCancelled());
    }

    public function test_milestone_priority_checks()
    {
        $highPriority = Milestone::factory()->create(['priority' => Milestone::PRIORITY_HIGH]);
        $criticalPriority = Milestone::factory()->create(['priority' => Milestone::PRIORITY_CRITICAL]);

        $this->assertTrue($highPriority->isHighPriority());
        $this->assertTrue($criticalPriority->isHighPriority());
    }

    public function test_milestone_can_start()
    {
        $notStarted = Milestone::factory()->create(['status' => Milestone::STATUS_NOT_STARTED]);
        $inProgress = Milestone::factory()->create(['status' => Milestone::STATUS_IN_PROGRESS]);

        $this->assertTrue($notStarted->canStart());
        $this->assertFalse($inProgress->canStart());
    }

    public function test_milestone_can_complete()
    {
        $inProgress = Milestone::factory()->create(['status' => Milestone::STATUS_IN_PROGRESS]);
        $completed = Milestone::factory()->create(['status' => Milestone::STATUS_COMPLETED]);

        $this->assertTrue($inProgress->canComplete());
        $this->assertFalse($completed->canComplete());
    }

    public function test_milestone_calculated_fields()
    {
        $milestone = Milestone::factory()->create([
            'target_date' => now()->addDays(10),
            'progress_percentage' => 50,
            'budget' => 1000,
            'actual_cost' => 600
        ]);

        $this->assertEquals(10, $milestone->getDaysUntilTargetAttribute());
        $this->assertEquals(50, $milestone->getProgressPercentageAttribute());
        $this->assertEquals(400, $milestone->getRemainingBudgetAttribute());
        $this->assertEquals(-200, $milestone->getCostVarianceAttribute());
    }

    public function test_milestone_overdue_calculation()
    {
        $overdueMilestone = Milestone::factory()->create([
            'target_date' => now()->subDays(5),
            'status' => Milestone::STATUS_IN_PROGRESS
        ]);

        $this->assertTrue($overdueMilestone->isOverdue());
        $this->assertEquals(5, $overdueMilestone->getDaysOverdueAttribute());
    }

    public function test_milestone_due_soon_check()
    {
        $dueSoonMilestone = Milestone::factory()->create([
            'target_date' => now()->addDays(3),
            'status' => Milestone::STATUS_NOT_STARTED
        ]);

        $this->assertTrue($dueSoonMilestone->isDueSoon(7));
        $this->assertFalse($dueSoonMilestone->isDueSoon(2));
    }

    public function test_milestone_formatted_values()
    {
        $milestone = Milestone::factory()->create([
            'milestone_type' => Milestone::MILESTONE_TYPE_PROJECT,
            'status' => Milestone::STATUS_IN_PROGRESS,
            'priority' => Milestone::PRIORITY_HIGH
        ]);

        $this->assertIsString($milestone->getMilestoneTypeFormattedAttribute());
        $this->assertIsString($milestone->getStatusFormattedAttribute());
        $this->assertIsString($milestone->getPriorityFormattedAttribute());
    }

    public function test_milestone_badge_classes()
    {
        $milestone = Milestone::factory()->create();

        $this->assertIsString($milestone->getMilestoneTypeBadgeClass());
        $this->assertIsString($milestone->getStatusBadgeClass());
        $this->assertIsString($milestone->getPriorityBadgeClass());
        $this->assertIsString($milestone->getTargetDateBadgeClass());
        $this->assertIsString($milestone->getProgressBadgeClass());
    }

    public function test_milestone_null_values_handling()
    {
        $milestone = Milestone::factory()->create([
            'start_date' => null,
            'completion_date' => null,
            'budget' => null,
            'actual_cost' => null
        ]);

        $this->assertNull($milestone->start_date);
        $this->assertNull($milestone->completion_date);
        $this->assertNull($milestone->budget);
        $this->assertNull($milestone->actual_cost);
    }

    public function test_milestone_invalid_values_handling()
    {
        $milestone = Milestone::factory()->create([
            'progress_percentage' => 150, // Debería ser validado por las reglas
            'budget' => -100 // Debería ser validado por las reglas
        ]);

        // Los valores se mantienen como se asignan, la validación se hace en el request
        $this->assertEquals(150, $milestone->progress_percentage);
        $this->assertEquals(-100, $milestone->budget);
    }

    public function test_milestone_soft_deletes()
    {
        $milestone = Milestone::factory()->create();
        $milestoneId = $milestone->id;

        $milestone->delete();

        $this->assertSoftDeleted('milestones', ['id' => $milestoneId]);
        $this->assertDatabaseMissing('milestones', ['id' => $milestoneId]);
    }

    public function test_milestone_version_tracking()
    {
        $milestone = Milestone::factory()->create(['version' => '1.0']);
        
        $this->assertEquals('1.0', $milestone->version);
    }

    public function test_milestone_budget_info()
    {
        $milestone = Milestone::factory()->create([
            'budget' => 1000,
            'actual_cost' => 750
        ]);

        $this->assertEquals(1000, $milestone->budget);
        $this->assertEquals(750, $milestone->actual_cost);
        $this->assertEquals(250, $milestone->getRemainingBudgetAttribute());
    }

    public function test_milestone_progress_tracking()
    {
        $milestone = Milestone::factory()->create([
            'progress_percentage' => 75,
            'status' => Milestone::STATUS_IN_PROGRESS
        ]);

        $this->assertEquals(75, $milestone->progress_percentage);
        $this->assertTrue($milestone->isInProgress());
        $this->assertFalse($milestone->isCompleted());
    }

    public function test_milestone_dependencies()
    {
        $milestone = Milestone::factory()->create([
            'dependencies' => [1, 2, 3]
        ]);

        $this->assertEquals([1, 2, 3], $milestone->dependencies);
        $this->assertIsArray($milestone->dependencies);
    }

    public function test_milestone_tags()
    {
        $milestone = Milestone::factory()->create([
            'tags' => ['urgent', 'phase1', 'development']
        ]);

        $this->assertEquals(['urgent', 'phase1', 'development'], $milestone->tags);
        $this->assertIsArray($milestone->tags);
    }

    public function test_milestone_risk_assessment()
    {
        $milestone = Milestone::factory()->create([
            'risk_level' => 'high',
            'priority' => Milestone::PRIORITY_CRITICAL
        ]);

        $this->assertEquals('high', $milestone->risk_level);
        $this->assertEquals(Milestone::PRIORITY_CRITICAL, $milestone->priority);
    }

    public function test_milestone_documentation()
    {
        $milestone = Milestone::factory()->create([
            'notes' => 'Important milestone notes',
            'description' => 'Detailed milestone description'
        ]);

        $this->assertEquals('Important milestone notes', $milestone->notes);
        $this->assertEquals('Detailed milestone description', $milestone->description);
    }
}
