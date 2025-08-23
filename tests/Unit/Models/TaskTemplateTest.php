<?php

namespace Tests\Unit\Models;

use App\Models\TaskTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTemplateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'name', 'description', 'template_type', 'category', 'subcategory',
            'estimated_duration_hours', 'estimated_cost', 'required_skills',
            'required_tools', 'required_parts', 'safety_requirements',
            'technical_requirements', 'quality_standards', 'checklist_items',
            'work_instructions', 'is_active', 'is_standard', 'version',
            'tags', 'notes', 'department', 'priority', 'risk_level',
            'compliance_requirements', 'documentation_required',
            'training_required', 'certification_required',
            'environmental_considerations', 'budget_code', 'cost_center',
            'project_code', 'created_by', 'approved_by', 'approved_at'
        ];

        $taskTemplate = new TaskTemplate();
        $this->assertEquals($fillable, $taskTemplate->getFillable());
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $taskTemplate = new TaskTemplate();
        $casts = $taskTemplate->getCasts();

        $this->assertArrayHasKey('estimated_duration_hours', $casts);
        $this->assertArrayHasKey('estimated_cost', $casts);
        $this->assertArrayHasKey('required_skills', $casts);
        $this->assertArrayHasKey('required_tools', $casts);
        $this->assertArrayHasKey('required_parts', $casts);
        $this->assertArrayHasKey('safety_requirements', $casts);
        $this->assertArrayHasKey('technical_requirements', $casts);
        $this->assertArrayHasKey('quality_standards', $casts);
        $this->assertArrayHasKey('checklist_items', $casts);
        $this->assertArrayHasKey('work_instructions', $casts);
        $this->assertArrayHasKey('is_active', $casts);
        $this->assertArrayHasKey('is_standard', $casts);
        $this->assertArrayHasKey('training_required', $casts);
        $this->assertArrayHasKey('certification_required', $casts);
        $this->assertArrayHasKey('approved_at', $casts);
        $this->assertArrayHasKey('tags', $casts);
        $this->assertArrayHasKey('compliance_requirements', $casts);
        $this->assertArrayHasKey('documentation_required', $casts);
        $this->assertArrayHasKey('environmental_considerations', $casts);
    }

    /** @test */
    public function it_has_template_types_enum()
    {
        $templateTypes = TaskTemplate::getTemplateTypes();
        
        $this->assertIsArray($templateTypes);
        $this->assertArrayHasKey('maintenance', $templateTypes);
        $this->assertArrayHasKey('inspection', $templateTypes);
        $this->assertArrayHasKey('repair', $templateTypes);
        $this->assertArrayHasKey('replacement', $templateTypes);
        $this->assertArrayHasKey('calibration', $templateTypes);
        $this->assertArrayHasKey('cleaning', $templateTypes);
        $this->assertArrayHasKey('lubrication', $templateTypes);
        $this->assertArrayHasKey('testing', $templateTypes);
        $this->assertArrayHasKey('upgrade', $templateTypes);
        $this->assertArrayHasKey('installation', $templateTypes);
    }

    /** @test */
    public function it_has_priorities_enum()
    {
        $priorities = TaskTemplate::getPriorities();
        
        $this->assertIsArray($priorities);
        $this->assertArrayHasKey('low', $priorities);
        $this->assertArrayHasKey('medium', $priorities);
        $this->assertArrayHasKey('high', $priorities);
        $this->assertArrayHasKey('urgent', $priorities);
        $this->assertArrayHasKey('critical', $priorities);
    }

    /** @test */
    public function it_has_risk_levels_enum()
    {
        $riskLevels = TaskTemplate::getRiskLevels();
        
        $this->assertIsArray($riskLevels);
        $this->assertArrayHasKey('low', $riskLevels);
        $this->assertArrayHasKey('medium', $riskLevels);
        $this->assertArrayHasKey('high', $riskLevels);
        $this->assertArrayHasKey('extreme', $riskLevels);
    }

    /** @test */
    public function it_has_relationships()
    {
        $taskTemplate = TaskTemplate::factory()->create();
        
        $this->assertInstanceOf(User::class, $taskTemplate->createdBy);
        $this->assertInstanceOf(User::class, $taskTemplate->approvedBy);
    }

    /** @test */
    public function it_has_scopes()
    {
        // Active scope
        TaskTemplate::factory()->create(['is_active' => true]);
        TaskTemplate::factory()->create(['is_active' => false]);
        
        $this->assertEquals(1, TaskTemplate::active()->count());
        
        // Standard scope
        TaskTemplate::factory()->create(['is_standard' => true]);
        TaskTemplate::factory()->create(['is_standard' => false]);
        
        $this->assertEquals(1, TaskTemplate::standard()->count());
        
        // ByType scope
        TaskTemplate::factory()->create(['template_type' => 'maintenance']);
        TaskTemplate::factory()->create(['template_type' => 'inspection']);
        
        $this->assertEquals(1, TaskTemplate::byType('maintenance')->count());
        
        // ByCategory scope
        TaskTemplate::factory()->create(['category' => 'Electrical']);
        TaskTemplate::factory()->create(['category' => 'Mechanical']);
        
        $this->assertEquals(1, TaskTemplate::byCategory('Electrical')->count());
        
        // ByPriority scope
        TaskTemplate::factory()->create(['priority' => 'high']);
        TaskTemplate::factory()->create(['priority' => 'low']);
        
        $this->assertEquals(1, TaskTemplate::byPriority('high')->count());
        
        // ByRiskLevel scope
        TaskTemplate::factory()->create(['risk_level' => 'high']);
        TaskTemplate::factory()->create(['risk_level' => 'low']);
        
        $this->assertEquals(1, TaskTemplate::byRiskLevel('high')->count());
        
        // ByDepartment scope
        TaskTemplate::factory()->create(['department' => 'Engineering']);
        TaskTemplate::factory()->create(['department' => 'Operations']);
        
        $this->assertEquals(1, TaskTemplate::byDepartment('Engineering')->count());
        
        // Approved scope
        TaskTemplate::factory()->create(['approved_at' => now()]);
        TaskTemplate::factory()->create(['approved_at' => null]);
        
        $this->assertEquals(1, TaskTemplate::approved()->count());
        
        // PendingApproval scope
        $this->assertEquals(1, TaskTemplate::pendingApproval()->count());
    }

    /** @test */
    public function it_has_boolean_checks()
    {
        $taskTemplate = TaskTemplate::factory()->create([
            'is_active' => true,
            'is_standard' => true
        ]);
        
        $this->assertTrue($taskTemplate->isActive());
        $this->assertTrue($taskTemplate->isStandard());
        $this->assertFalse($taskTemplate->isApproved());
    }

    /** @test */
    public function it_has_template_type_checks()
    {
        $taskTemplate = TaskTemplate::factory()->create(['template_type' => 'maintenance']);
        
        $this->assertTrue($taskTemplate->isMaintenance());
        $this->assertFalse($taskTemplate->isInspection());
        $this->assertFalse($taskTemplate->isRepair());
        $this->assertFalse($taskTemplate->isReplacement());
        $this->assertFalse($taskTemplate->isCalibration());
        $this->assertFalse($taskTemplate->isCleaning());
        $this->assertFalse($taskTemplate->isLubrication());
        $this->assertFalse($taskTemplate->isTesting());
        $this->assertFalse($taskTemplate->isUpgrade());
        $this->assertFalse($taskTemplate->isInstallation());
    }

    /** @test */
    public function it_has_priority_checks()
    {
        $taskTemplate = TaskTemplate::factory()->create(['priority' => 'high']);
        
        $this->assertTrue($taskTemplate->isHighPriority());
        $this->assertFalse($taskTemplate->isLowPriority());
        $this->assertFalse($taskTemplate->isMediumPriority());
        $this->assertFalse($taskTemplate->isUrgent());
        $this->assertFalse($taskTemplate->isCritical());
    }

    /** @test */
    public function it_has_risk_level_checks()
    {
        $taskTemplate = TaskTemplate::factory()->create(['risk_level' => 'high']);
        
        $this->assertTrue($taskTemplate->isHighRisk());
        $this->assertFalse($taskTemplate->isLowRisk());
        $this->assertFalse($taskTemplate->isMediumRisk());
        $this->assertFalse($taskTemplate->isExtremeRisk());
    }

    /** @test */
    public function it_can_get_formatted_estimated_duration()
    {
        $taskTemplate = TaskTemplate::factory()->create(['estimated_duration_hours' => 4.5]);
        
        $this->assertEquals('4.50 horas', $taskTemplate->getFormattedEstimatedDuration());
    }

    /** @test */
    public function it_can_get_formatted_estimated_cost()
    {
        $taskTemplate = TaskTemplate::factory()->create(['estimated_cost' => 1250.75]);
        
        $this->assertEquals('$1,250.75', $taskTemplate->getFormattedEstimatedCost());
    }

    /** @test */
    public function it_can_get_formatted_template_type()
    {
        $taskTemplate = TaskTemplate::factory()->create(['template_type' => 'maintenance']);
        
        $this->assertEquals('Mantenimiento', $taskTemplate->getFormattedTemplateType());
    }

    /** @test */
    public function it_can_get_formatted_priority()
    {
        $taskTemplate = TaskTemplate::factory()->create(['priority' => 'high']);
        
        $this->assertEquals('Alta', $taskTemplate->getFormattedPriority());
    }

    /** @test */
    public function it_can_get_formatted_risk_level()
    {
        $taskTemplate = TaskTemplate::factory()->create(['risk_level' => 'high']);
        
        $this->assertEquals('Alto', $taskTemplate->getFormattedRiskLevel());
    }

    /** @test */
    public function it_can_get_status_badge_class()
    {
        // Inactive
        $taskTemplate = TaskTemplate::factory()->create(['is_active' => false]);
        $this->assertEquals('bg-red-100 text-red-800', $taskTemplate->getStatusBadgeClass());
        
        // Pending approval
        $taskTemplate = TaskTemplate::factory()->create(['is_active' => true, 'approved_at' => null]);
        $this->assertEquals('bg-yellow-100 text-yellow-800', $taskTemplate->getStatusBadgeClass());
        
        // Standard
        $taskTemplate = TaskTemplate::factory()->create([
            'is_active' => true,
            'is_standard' => true,
            'approved_at' => now()
        ]);
        $this->assertEquals('bg-green-100 text-green-800', $taskTemplate->getStatusBadgeClass());
        
        // Regular
        $taskTemplate = TaskTemplate::factory()->create([
            'is_active' => true,
            'is_standard' => false,
            'approved_at' => now()
        ]);
        $this->assertEquals('bg-blue-100 text-blue-800', $taskTemplate->getStatusBadgeClass());
    }

    /** @test */
    public function it_can_get_template_type_badge_class()
    {
        $taskTemplate = TaskTemplate::factory()->create(['template_type' => 'maintenance']);
        
        $this->assertEquals('bg-blue-100 text-blue-800', $taskTemplate->getTemplateTypeBadgeClass());
    }

    /** @test */
    public function it_can_get_priority_badge_class()
    {
        $taskTemplate = TaskTemplate::factory()->create(['priority' => 'high']);
        
        $this->assertEquals('bg-yellow-100 text-yellow-800', $taskTemplate->getPriorityBadgeClass());
    }

    /** @test */
    public function it_can_get_risk_level_badge_class()
    {
        $taskTemplate = TaskTemplate::factory()->create(['risk_level' => 'high']);
        
        $this->assertEquals('bg-orange-100 text-orange-800', $taskTemplate->getRiskLevelBadgeClass());
    }

    /** @test */
    public function it_returns_no_estimated_duration_when_null()
    {
        $taskTemplate = TaskTemplate::factory()->create(['estimated_duration_hours' => null]);
        
        $this->assertEquals('No estimado', $taskTemplate->getFormattedEstimatedDuration());
    }

    /** @test */
    public function it_returns_no_estimated_cost_when_null()
    {
        $taskTemplate = TaskTemplate::factory()->create(['estimated_cost' => null]);
        
        $this->assertEquals('No estimado', $taskTemplate->getFormattedEstimatedCost());
    }

    /** @test */
    public function it_returns_unknown_for_invalid_template_type()
    {
        $taskTemplate = TaskTemplate::factory()->create(['template_type' => 'invalid_type']);
        
        $this->assertEquals('Desconocido', $taskTemplate->getFormattedTemplateType());
    }

    /** @test */
    public function it_returns_unknown_for_invalid_priority()
    {
        $taskTemplate = TaskTemplate::factory()->create(['priority' => 'invalid_priority']);
        
        $this->assertEquals('Desconocida', $taskTemplate->getFormattedPriority());
    }

    /** @test */
    public function it_returns_unknown_for_invalid_risk_level()
    {
        $taskTemplate = TaskTemplate::factory()->create(['risk_level' => 'invalid_risk']);
        
        $this->assertEquals('Desconocido', $taskTemplate->getFormattedRiskLevel());
    }

    /** @test */
    public function it_can_scope_by_type()
    {
        TaskTemplate::factory()->create(['template_type' => 'maintenance']);
        TaskTemplate::factory()->create(['template_type' => 'inspection']);
        
        $this->assertEquals(1, TaskTemplate::byType('maintenance')->count());
        $this->assertEquals(1, TaskTemplate::byType('inspection')->count());
    }

    /** @test */
    public function it_can_scope_by_category()
    {
        TaskTemplate::factory()->create(['category' => 'Electrical']);
        TaskTemplate::factory()->create(['category' => 'Mechanical']);
        
        $this->assertEquals(1, TaskTemplate::byCategory('Electrical')->count());
        $this->assertEquals(1, TaskTemplate::byCategory('Mechanical')->count());
    }

    /** @test */
    public function it_can_scope_by_priority()
    {
        TaskTemplate::factory()->create(['priority' => 'high']);
        TaskTemplate::factory()->create(['priority' => 'low']);
        
        $this->assertEquals(1, TaskTemplate::byPriority('high')->count());
        $this->assertEquals(1, TaskTemplate::byPriority('low')->count());
    }

    /** @test */
    public function it_can_scope_by_risk_level()
    {
        TaskTemplate::factory()->create(['risk_level' => 'high']);
        TaskTemplate::factory()->create(['risk_level' => 'low']);
        
        $this->assertEquals(1, TaskTemplate::byRiskLevel('high')->count());
        $this->assertEquals(1, TaskTemplate::byRiskLevel('low')->count());
    }

    /** @test */
    public function it_can_scope_by_department()
    {
        TaskTemplate::factory()->create(['department' => 'Engineering']);
        TaskTemplate::factory()->create(['department' => 'Operations']);
        
        $this->assertEquals(1, TaskTemplate::byDepartment('Engineering')->count());
        $this->assertEquals(1, TaskTemplate::byDepartment('Operations')->count());
    }

    /** @test */
    public function it_can_scope_approved()
    {
        TaskTemplate::factory()->create(['approved_at' => now()]);
        TaskTemplate::factory()->create(['approved_at' => null]);
        
        $this->assertEquals(1, TaskTemplate::approved()->count());
    }

    /** @test */
    public function it_can_scope_pending_approval()
    {
        TaskTemplate::factory()->create(['approved_at' => now()]);
        TaskTemplate::factory()->create(['approved_at' => null]);
        
        $this->assertEquals(1, TaskTemplate::pendingApproval()->count());
    }

    /** @test */
    public function it_uses_soft_deletes()
    {
        $taskTemplate = TaskTemplate::factory()->create();
        
        $this->assertSoftDeleted($taskTemplate);
    }

    /** @test */
    public function it_has_version_tracking()
    {
        $taskTemplate = TaskTemplate::factory()->create(['version' => '1.0']);
        
        $this->assertEquals('1.0', $taskTemplate->version);
    }

    /** @test */
    public function it_has_budget_information()
    {
        $taskTemplate = TaskTemplate::factory()->create([
            'budget_code' => 'BUD001',
            'cost_center' => 'CC001',
            'project_code' => 'PRJ001'
        ]);
        
        $this->assertEquals('BUD001', $taskTemplate->budget_code);
        $this->assertEquals('CC001', $taskTemplate->cost_center);
        $this->assertEquals('PRJ001', $taskTemplate->project_code);
    }

    /** @test */
    public function it_has_training_and_certification_flags()
    {
        $taskTemplate = TaskTemplate::factory()->create([
            'training_required' => true,
            'certification_required' => true
        ]);
        
        $this->assertTrue($taskTemplate->training_required);
        $this->assertTrue($taskTemplate->certification_required);
    }

    /** @test */
    public function it_has_environmental_considerations()
    {
        $taskTemplate = TaskTemplate::factory()->create([
            'environmental_considerations' => [
                'waste_disposal' => 'Proper disposal required',
                'emissions' => 'Minimize emissions'
            ]
        ]);
        
        $this->assertIsArray($taskTemplate->environmental_considerations);
        $this->assertArrayHasKey('waste_disposal', $taskTemplate->environmental_considerations);
        $this->assertArrayHasKey('emissions', $taskTemplate->environmental_considerations);
    }

    /** @test */
    public function it_has_compliance_requirements()
    {
        $taskTemplate = TaskTemplate::factory()->create([
            'compliance_requirements' => [
                'safety_standards' => 'OSHA compliance required',
                'quality_standards' => 'ISO 9001 compliance'
            ]
        ]);
        
        $this->assertIsArray($taskTemplate->compliance_requirements);
        $this->assertArrayHasKey('safety_standards', $taskTemplate->compliance_requirements);
        $this->assertArrayHasKey('quality_standards', $taskTemplate->compliance_requirements);
    }

    /** @test */
    public function it_has_documentation_requirements()
    {
        $taskTemplate = TaskTemplate::factory()->create([
            'documentation_required' => [
                'work_orders' => true,
                'safety_checklists' => true,
                'quality_reports' => false
            ]
        ]);
        
        $this->assertIsArray($taskTemplate->documentation_required);
        $this->assertArrayHasKey('work_orders', $taskTemplate->documentation_required);
        $this->assertArrayHasKey('safety_checklists', $taskTemplate->documentation_required);
        $this->assertArrayHasKey('quality_reports', $taskTemplate->documentation_required);
    }
}
