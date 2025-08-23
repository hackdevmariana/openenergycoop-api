<?php

namespace Tests\Unit\Models;

use App\Models\MaintenanceSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes()
    {
        $fillable = [
            'name', 'description', 'schedule_type', 'frequency_type', 'frequency_value',
            'priority', 'department', 'category', 'equipment_id', 'location_id',
            'vendor_id', 'task_template_id', 'checklist_template_id', 'estimated_duration_hours',
            'estimated_cost', 'is_active', 'auto_generate_tasks', 'send_notifications',
            'notification_emails', 'start_date', 'end_date', 'next_maintenance_date',
            'last_maintenance_date', 'maintenance_window_start', 'maintenance_window_end',
            'weather_dependent', 'weather_conditions', 'required_skills', 'required_tools',
            'required_materials', 'safety_requirements', 'quality_standards',
            'compliance_requirements', 'tags', 'notes', 'created_by', 'approved_by', 'approved_at'
        ];

        $this->assertEquals($fillable, (new MaintenanceSchedule())->getFillable());
    }

    public function test_casts()
    {
        $casts = [
            'frequency_value' => 'integer',
            'estimated_duration_hours' => 'decimal:2',
            'estimated_cost' => 'decimal:2',
            'is_active' => 'boolean',
            'auto_generate_tasks' => 'boolean',
            'send_notifications' => 'boolean',
            'weather_dependent' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
            'next_maintenance_date' => 'date',
            'last_maintenance_date' => 'date',
            'approved_at' => 'datetime',
            'notification_emails' => 'array',
            'weather_conditions' => 'array',
            'required_skills' => 'array',
            'required_tools' => 'array',
            'required_materials' => 'array',
            'safety_requirements' => 'array',
            'quality_standards' => 'array',
            'compliance_requirements' => 'array',
            'tags' => 'array',
        ];

        $this->assertEquals($casts, (new MaintenanceSchedule())->getCasts());
    }

    public function test_static_enum_methods()
    {
        $this->assertIsArray(MaintenanceSchedule::getScheduleTypes());
        $this->assertIsArray(MaintenanceSchedule::getFrequencyTypes());
        $this->assertIsArray(MaintenanceSchedule::getPriorities());
    }

    public function test_relationships()
    {
        $maintenanceSchedule = MaintenanceSchedule::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $maintenanceSchedule->createdBy());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $maintenanceSchedule->approvedBy());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $maintenanceSchedule->vendor());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $maintenanceSchedule->taskTemplate());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $maintenanceSchedule->checklistTemplate());
    }

    public function test_scopes()
    {
        $maintenanceSchedule = MaintenanceSchedule::factory()->create(['is_active' => true]);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, MaintenanceSchedule::active());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, MaintenanceSchedule::byType('preventive'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, MaintenanceSchedule::byFrequencyType('daily'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, MaintenanceSchedule::byPriority('high'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, MaintenanceSchedule::highPriority());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, MaintenanceSchedule::byDepartment('maintenance'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, MaintenanceSchedule::byCategory('equipment'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, MaintenanceSchedule::preventive());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, MaintenanceSchedule::predictive());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, MaintenanceSchedule::conditionBased());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, MaintenanceSchedule::approved());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, MaintenanceSchedule::pendingApproval());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, MaintenanceSchedule::overdue());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, MaintenanceSchedule::dueSoon());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, MaintenanceSchedule::autoGenerateTasks());
    }

    public function test_boolean_status_checks()
    {
        $maintenanceSchedule = MaintenanceSchedule::factory()->create(['is_active' => true]);
        
        $this->assertTrue($maintenanceSchedule->isActive());
        $this->assertFalse($maintenanceSchedule->isApproved());
    }

    public function test_schedule_type_checks()
    {
        $maintenanceSchedule = MaintenanceSchedule::factory()->create(['schedule_type' => 'preventive']);
        
        $this->assertTrue($maintenanceSchedule->isPreventive());
        $this->assertFalse($maintenanceSchedule->isPredictive());
        $this->assertFalse($maintenanceSchedule->isConditionBased());
        $this->assertFalse($maintenanceSchedule->isCorrective());
        $this->assertFalse($maintenanceSchedule->isEmergency());
        $this->assertTrue($maintenanceSchedule->isPlanned());
        $this->assertFalse($maintenanceSchedule->isUnplanned());
    }

    public function test_priority_checks()
    {
        $maintenanceSchedule = MaintenanceSchedule::factory()->create(['priority' => 'high']);
        
        $this->assertTrue($maintenanceSchedule->isHighPriority());
        $this->assertTrue($maintenanceSchedule->isUrgent());
    }

    public function test_calculation_methods()
    {
        $maintenanceSchedule = MaintenanceSchedule::factory()->create([
            'start_date' => now(),
            'next_maintenance_date' => now()->addDays(5),
            'maintenance_window_start' => '09:00',
            'maintenance_window_end' => '17:00',
            'estimated_cost' => 1000.00
        ]);

        $this->assertIsInt($maintenanceSchedule->getDaysUntilNextMaintenance());
        $this->assertIsFloat($maintenanceSchedule->getMaintenanceWindowDurationHours());
        $this->assertIsFloat($maintenanceSchedule->getTotalEstimatedCost());
        $this->assertIsFloat($maintenanceSchedule->getCompletionPercentage());
    }

    public function test_formatting_methods()
    {
        $maintenanceSchedule = MaintenanceSchedule::factory()->create([
            'priority' => 'high',
            'schedule_type' => 'preventive',
            'frequency_type' => 'daily',
            'estimated_duration_hours' => 8.5,
            'estimated_cost' => 1500.00
        ]);

        $this->assertEquals('Alta', $maintenanceSchedule->getFormattedPriority());
        $this->assertEquals('Preventivo', $maintenanceSchedule->getFormattedScheduleType());
        $this->assertEquals('Diario', $maintenanceSchedule->getFormattedFrequencyType());
        $this->assertStringContainsString('8.5', $maintenanceSchedule->getFormattedEstimatedDuration());
        $this->assertStringContainsString('1,500.00', $maintenanceSchedule->getFormattedEstimatedCost());
    }

    public function test_badge_classes()
    {
        $maintenanceSchedule = MaintenanceSchedule::factory()->create(['is_active' => true]);
        
        $this->assertStringContainsString('bg-green-100', $maintenanceSchedule->getStatusBadgeClass());
        $this->assertStringContainsString('text-green-800', $maintenanceSchedule->getStatusBadgeClass());
    }

    public function test_overdue_and_due_soon_checks()
    {
        $overdueSchedule = MaintenanceSchedule::factory()->create([
            'next_maintenance_date' => now()->subDays(5)
        ]);

        $dueSoonSchedule = MaintenanceSchedule::factory()->create([
            'next_maintenance_date' => now()->addDays(3)
        ]);

        $this->assertTrue($overdueSchedule->isOverdue());
        $this->assertTrue($dueSoonSchedule->isDueSoon());
    }

    public function test_active_scope()
    {
        $activeSchedule = MaintenanceSchedule::factory()->create(['is_active' => true]);
        $inactiveSchedule = MaintenanceSchedule::factory()->create(['is_active' => false]);

        $activeSchedules = MaintenanceSchedule::active()->get();
        
        $this->assertTrue($activeSchedules->contains($activeSchedule));
        $this->assertFalse($activeSchedules->contains($inactiveSchedule));
    }

    public function test_high_priority_scope()
    {
        $highPrioritySchedule = MaintenanceSchedule::factory()->create(['priority' => 'high']);
        $lowPrioritySchedule = MaintenanceSchedule::factory()->create(['priority' => 'low']);

        $highPrioritySchedules = MaintenanceSchedule::highPriority()->get();
        
        $this->assertTrue($highPrioritySchedules->contains($highPrioritySchedule));
        $this->assertFalse($highPrioritySchedules->contains($lowPrioritySchedule));
    }

    public function test_by_type_scope()
    {
        $preventiveSchedule = MaintenanceSchedule::factory()->create(['schedule_type' => 'preventive']);
        $predictiveSchedule = MaintenanceSchedule::factory()->create(['schedule_type' => 'predictive']);

        $preventiveSchedules = MaintenanceSchedule::byType('preventive')->get();
        
        $this->assertTrue($preventiveSchedules->contains($preventiveSchedule));
        $this->assertFalse($preventiveSchedules->contains($predictiveSchedule));
    }

    public function test_by_frequency_type_scope()
    {
        $dailySchedule = MaintenanceSchedule::factory()->create(['frequency_type' => 'daily']);
        $weeklySchedule = MaintenanceSchedule::factory()->create(['frequency_type' => 'weekly']);

        $dailySchedules = MaintenanceSchedule::byFrequencyType('daily')->get();
        
        $this->assertTrue($dailySchedules->contains($dailySchedule));
        $this->assertFalse($dailySchedules->contains($weeklySchedule));
    }

    public function test_by_priority_scope()
    {
        $highPrioritySchedule = MaintenanceSchedule::factory()->create(['priority' => 'high']);
        $lowPrioritySchedule = MaintenanceSchedule::factory()->create(['priority' => 'low']);

        $highPrioritySchedules = MaintenanceSchedule::byPriority('high')->get();
        
        $this->assertTrue($highPrioritySchedules->contains($highPrioritySchedule));
        $this->assertFalse($highPrioritySchedules->contains($lowPrioritySchedule));
    }

    public function test_by_department_scope()
    {
        $maintenanceSchedule = MaintenanceSchedule::factory()->create(['department' => 'maintenance']);
        $operationsSchedule = MaintenanceSchedule::factory()->create(['department' => 'operations']);

        $maintenanceSchedules = MaintenanceSchedule::byDepartment('maintenance')->get();
        
        $this->assertTrue($maintenanceSchedules->contains($maintenanceSchedule));
        $this->assertFalse($maintenanceSchedules->contains($operationsSchedule));
    }

    public function test_by_category_scope()
    {
        $equipmentSchedule = MaintenanceSchedule::factory()->create(['category' => 'equipment']);
        $facilitySchedule = MaintenanceSchedule::factory()->create(['category' => 'facility']);

        $equipmentSchedules = MaintenanceSchedule::byCategory('equipment')->get();
        
        $this->assertTrue($equipmentSchedules->contains($equipmentSchedule));
        $this->assertFalse($equipmentSchedules->contains($facilitySchedule));
    }

    public function test_preventive_scope()
    {
        $preventiveSchedule = MaintenanceSchedule::factory()->create(['schedule_type' => 'preventive']);
        $predictiveSchedule = MaintenanceSchedule::factory()->create(['schedule_type' => 'predictive']);

        $preventiveSchedules = MaintenanceSchedule::preventive()->get();
        
        $this->assertTrue($preventiveSchedules->contains($preventiveSchedule));
        $this->assertFalse($preventiveSchedules->contains($predictiveSchedule));
    }

    public function test_predictive_scope()
    {
        $predictiveSchedule = MaintenanceSchedule::factory()->create(['schedule_type' => 'predictive']);
        $preventiveSchedule = MaintenanceSchedule::factory()->create(['schedule_type' => 'preventive']);

        $predictiveSchedules = MaintenanceSchedule::predictive()->get();
        
        $this->assertTrue($predictiveSchedules->contains($predictiveSchedule));
        $this->assertFalse($predictiveSchedules->contains($preventiveSchedule));
    }

    public function test_condition_based_scope()
    {
        $conditionBasedSchedule = MaintenanceSchedule::factory()->create(['schedule_type' => 'condition_based']);
        $preventiveSchedule = MaintenanceSchedule::factory()->create(['schedule_type' => 'preventive']);

        $conditionBasedSchedules = MaintenanceSchedule::conditionBased()->get();
        
        $this->assertTrue($conditionBasedSchedules->contains($conditionBasedSchedule));
        $this->assertFalse($conditionBasedSchedules->contains($preventiveSchedule));
    }

    public function test_approved_scope()
    {
        $approvedSchedule = MaintenanceSchedule::factory()->create(['approved_at' => now()]);
        $pendingSchedule = MaintenanceSchedule::factory()->create(['approved_at' => null]);

        $approvedSchedules = MaintenanceSchedule::approved()->get();
        
        $this->assertTrue($approvedSchedules->contains($approvedSchedule));
        $this->assertFalse($approvedSchedules->contains($pendingSchedule));
    }

    public function test_pending_approval_scope()
    {
        $approvedSchedule = MaintenanceSchedule::factory()->create(['approved_at' => now()]);
        $pendingSchedule = MaintenanceSchedule::factory()->create(['approved_at' => null]);

        $pendingSchedules = MaintenanceSchedule::pendingApproval()->get();
        
        $this->assertTrue($pendingSchedules->contains($pendingSchedule));
        $this->assertFalse($pendingSchedules->contains($approvedSchedule));
    }

    public function test_overdue_scope()
    {
        $overdueSchedule = MaintenanceSchedule::factory()->create([
            'next_maintenance_date' => now()->subDays(5)
        ]);
        $onTimeSchedule = MaintenanceSchedule::factory()->create([
            'next_maintenance_date' => now()->addDays(5)
        ]);

        $overdueSchedules = MaintenanceSchedule::overdue()->get();
        
        $this->assertTrue($overdueSchedules->contains($overdueSchedule));
        $this->assertFalse($overdueSchedules->contains($onTimeSchedule));
    }

    public function test_due_soon_scope()
    {
        $dueSoonSchedule = MaintenanceSchedule::factory()->create([
            'next_maintenance_date' => now()->addDays(3)
        ]);
        $notDueSoonSchedule = MaintenanceSchedule::factory()->create([
            'next_maintenance_date' => now()->addDays(10)
        ]);

        $dueSoonSchedules = MaintenanceSchedule::dueSoon()->get();
        
        $this->assertTrue($dueSoonSchedules->contains($dueSoonSchedule));
        $this->assertFalse($dueSoonSchedules->contains($notDueSoonSchedule));
    }

    public function test_auto_generate_tasks_scope()
    {
        $autoGenerateSchedule = MaintenanceSchedule::factory()->create(['auto_generate_tasks' => true]);
        $manualSchedule = MaintenanceSchedule::factory()->create(['auto_generate_tasks' => false]);

        $autoGenerateSchedules = MaintenanceSchedule::autoGenerateTasks()->get();
        
        $this->assertTrue($autoGenerateSchedules->contains($autoGenerateSchedule));
        $this->assertFalse($autoGenerateSchedules->contains($manualSchedule));
    }
}
