<?php

namespace Tests\Feature\Api\V1;

use App\Models\TaskTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskTemplateControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->adminUser = User::factory()->create(['role' => 'admin']);
    }

    /** @test */
    public function it_can_list_task_templates()
    {
        TaskTemplate::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'name', 'description', 'template_type', 'category',
                        'subcategory', 'estimated_duration_hours', 'estimated_cost',
                        'is_active', 'is_standard', 'version', 'department',
                        'priority', 'risk_level', 'created_at', 'updated_at'
                    ]
                ],
                'meta' => ['current_page', 'per_page', 'total'],
                'summary' => ['total_templates', 'active', 'standard', 'approved']
            ]);
    }

    /** @test */
    public function it_can_filter_task_templates_by_template_type()
    {
        TaskTemplate::factory()->create(['template_type' => 'maintenance']);
        TaskTemplate::factory()->create(['template_type' => 'inspection']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates?template_type=maintenance');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_filter_task_templates_by_category()
    {
        TaskTemplate::factory()->create(['category' => 'Electrical']);
        TaskTemplate::factory()->create(['category' => 'Mechanical']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates?category=Electrical');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_filter_task_templates_by_priority()
    {
        TaskTemplate::factory()->create(['priority' => 'high']);
        TaskTemplate::factory()->create(['priority' => 'low']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates?priority=high');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_filter_task_templates_by_risk_level()
    {
        TaskTemplate::factory()->create(['risk_level' => 'high']);
        TaskTemplate::factory()->create(['risk_level' => 'low']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates?risk_level=high');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_filter_task_templates_by_department()
    {
        TaskTemplate::factory()->create(['department' => 'Engineering']);
        TaskTemplate::factory()->create(['department' => 'Operations']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates?department=Engineering');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_filter_task_templates_by_active_status()
    {
        TaskTemplate::factory()->create(['is_active' => true]);
        TaskTemplate::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates?is_active=1');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_filter_task_templates_by_standard_status()
    {
        TaskTemplate::factory()->create(['is_standard' => true]);
        TaskTemplate::factory()->create(['is_standard' => false]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates?is_standard=1');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_filter_task_templates_by_approval_status()
    {
        TaskTemplate::factory()->create(['approved_at' => now()]);
        TaskTemplate::factory()->create(['approved_at' => null]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates?is_approved=1');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_search_task_templates()
    {
        TaskTemplate::factory()->create(['name' => 'Electrical Maintenance']);
        TaskTemplate::factory()->create(['name' => 'Mechanical Repair']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates?search=Electrical');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_sort_task_templates()
    {
        TaskTemplate::factory()->create(['name' => 'A Template']);
        TaskTemplate::factory()->create(['name' => 'B Template']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates?sort=name&order=asc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('A Template', $data[0]['name']);
    }

    /** @test */
    public function it_can_paginate_task_templates()
    {
        TaskTemplate::factory()->count(25)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates?per_page=10');

        $response->assertStatus(200);
        $this->assertEquals(10, count($response->json('data')));
        $this->assertEquals(25, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_show_task_template()
    {
        $taskTemplate = TaskTemplate::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/task-templates/{$taskTemplate->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'description', 'template_type', 'category',
                    'subcategory', 'estimated_duration_hours', 'estimated_cost',
                    'required_skills', 'required_tools', 'required_parts',
                    'safety_requirements', 'technical_requirements',
                    'quality_standards', 'checklist_items', 'work_instructions',
                    'is_active', 'is_standard', 'version', 'tags', 'notes',
                    'department', 'priority', 'risk_level',
                    'compliance_requirements', 'documentation_required',
                    'training_required', 'certification_required',
                    'environmental_considerations', 'budget_code', 'cost_center',
                    'project_code', 'created_at', 'updated_at'
                ]
            ]);
    }

    /** @test */
    public function it_can_store_task_template()
    {
        $data = [
            'name' => 'Test Template',
            'description' => 'Test Description',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'subcategory' => 'Preventive',
            'estimated_duration_hours' => 4.5,
            'estimated_cost' => 500.00,
            'required_skills' => ['electrical', 'maintenance'],
            'required_tools' => ['multimeter', 'screwdriver'],
            'required_parts' => ['fuses', 'wires'],
            'safety_requirements' => ['PPE required', 'Lockout/Tagout'],
            'technical_requirements' => ['Voltage testing', 'Insulation check'],
            'quality_standards' => ['Industry standards', 'Safety compliance'],
            'checklist_items' => ['Check voltage', 'Test insulation'],
            'work_instructions' => ['Step 1', 'Step 2'],
            'is_active' => true,
            'is_standard' => false,
            'version' => '1.0',
            'tags' => ['electrical', 'maintenance'],
            'notes' => 'Test notes',
            'department' => 'Engineering',
            'priority' => 'medium',
            'risk_level' => 'low',
            'compliance_requirements' => ['OSHA', 'NEC'],
            'documentation_required' => ['work_order', 'safety_checklist'],
            'training_required' => true,
            'certification_required' => false,
            'environmental_considerations' => ['waste_disposal'],
            'budget_code' => 'BUD001',
            'cost_center' => 'CC001',
            'project_code' => 'PRJ001'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'description', 'template_type', 'category',
                    'subcategory', 'estimated_duration_hours', 'estimated_cost'
                ]
            ]);

        $this->assertDatabaseHas('task_templates', [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical'
        ]);
    }

    /** @test */
    public function it_can_update_task_template()
    {
        $taskTemplate = TaskTemplate::factory()->create();
        $updateData = [
            'name' => 'Updated Template',
            'description' => 'Updated Description',
            'priority' => 'high'
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/task-templates/{$taskTemplate->id}", $updateData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('task_templates', [
            'id' => $taskTemplate->id,
            'name' => 'Updated Template',
            'description' => 'Updated Description',
            'priority' => 'high'
        ]);
    }

    /** @test */
    public function it_can_delete_task_template()
    {
        $taskTemplate = TaskTemplate::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/task-templates/{$taskTemplate->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('task_templates', ['id' => $taskTemplate->id]);
    }

    /** @test */
    public function it_can_get_task_template_statistics()
    {
        TaskTemplate::factory()->count(5)->create(['is_active' => true]);
        TaskTemplate::factory()->count(3)->create(['is_active' => false]);
        TaskTemplate::factory()->count(2)->create(['is_standard' => true]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_templates', 'active', 'inactive', 'standard',
                    'custom', 'approved', 'pending_approval', 'by_type',
                    'by_category', 'by_priority', 'by_risk_level',
                    'by_department', 'average_duration', 'average_cost'
                ]
            ]);
    }

    /** @test */
    public function it_can_get_template_types()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates/template-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'maintenance', 'inspection', 'repair', 'replacement',
                    'calibration', 'cleaning', 'lubrication', 'testing',
                    'upgrade', 'installation'
                ]
            ]);
    }

    /** @test */
    public function it_can_get_priorities()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates/priorities');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'low', 'medium', 'high', 'urgent', 'critical'
                ]
            ]);
    }

    /** @test */
    public function it_can_get_risk_levels()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates/risk-levels');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'low', 'medium', 'high', 'extreme'
                ]
            ]);
    }

    /** @test */
    public function it_can_toggle_active_status()
    {
        $taskTemplate = TaskTemplate::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/task-templates/{$taskTemplate->id}/toggle-active");

        $response->assertStatus(200);
        $this->assertTrue($taskTemplate->fresh()->is_active);
    }

    /** @test */
    public function it_can_toggle_standard_status()
    {
        $taskTemplate = TaskTemplate::factory()->create(['is_standard' => false]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/task-templates/{$taskTemplate->id}/toggle-standard");

        $response->assertStatus(200);
        $this->assertTrue($taskTemplate->fresh()->is_standard);
    }

    /** @test */
    public function it_can_duplicate_task_template()
    {
        $taskTemplate = TaskTemplate::factory()->create([
            'name' => 'Original Template',
            'version' => '1.0'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/task-templates/{$taskTemplate->id}/duplicate");

        $response->assertStatus(201);
        $this->assertDatabaseHas('task_templates', [
            'name' => 'Original Template (Copy)',
            'version' => '1.1'
        ]);
    }

    /** @test */
    public function it_can_get_active_templates()
    {
        TaskTemplate::factory()->create(['is_active' => true]);
        TaskTemplate::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates/active');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_standard_templates()
    {
        TaskTemplate::factory()->create(['is_standard' => true]);
        TaskTemplate::factory()->create(['is_standard' => false]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates/standard');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_approved_templates()
    {
        TaskTemplate::factory()->create(['approved_at' => now()]);
        TaskTemplate::factory()->create(['approved_at' => null]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates/approved');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_pending_approval_templates()
    {
        TaskTemplate::factory()->create(['approved_at' => now()]);
        TaskTemplate::factory()->create(['approved_at' => null]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates/pending-approval');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_templates_by_type()
    {
        TaskTemplate::factory()->create(['template_type' => 'maintenance']);
        TaskTemplate::factory()->create(['template_type' => 'inspection']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates/by-type/maintenance');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_templates_by_category()
    {
        TaskTemplate::factory()->create(['category' => 'Electrical']);
        TaskTemplate::factory()->create(['category' => 'Mechanical']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates/by-category/Electrical');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_templates_by_priority()
    {
        TaskTemplate::factory()->create(['priority' => 'high']);
        TaskTemplate::factory()->create(['priority' => 'low']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates/by-priority/high');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_templates_by_risk_level()
    {
        TaskTemplate::factory()->create(['risk_level' => 'high']);
        TaskTemplate::factory()->create(['risk_level' => 'low']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates/by-risk-level/high');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_templates_by_department()
    {
        TaskTemplate::factory()->create(['department' => 'Engineering']);
        TaskTemplate::factory()->create(['department' => 'Operations']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates/by-department/Engineering');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_high_priority_templates()
    {
        TaskTemplate::factory()->create(['priority' => 'high']);
        TaskTemplate::factory()->create(['priority' => 'low']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates/high-priority');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_high_risk_templates()
    {
        TaskTemplate::factory()->create(['risk_level' => 'high']);
        TaskTemplate::factory()->create(['risk_level' => 'low']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/task-templates/high-risk');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/v1/task-templates');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_validates_required_fields_on_store()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'template_type', 'category']);
    }

    /** @test */
    public function it_validates_template_type_enum_on_store()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'invalid_type',
            'category' => 'Electrical'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['template_type']);
    }

    /** @test */
    public function it_validates_priority_enum_on_store()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'priority' => 'invalid_priority'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priority']);
    }

    /** @test */
    public function it_validates_risk_level_enum_on_store()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'risk_level' => 'invalid_risk'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['risk_level']);
    }

    /** @test */
    public function it_validates_numeric_fields_on_store()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'estimated_duration_hours' => 'not_numeric',
            'estimated_cost' => 'not_numeric'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['estimated_duration_hours', 'estimated_cost']);
    }

    /** @test */
    public function it_validates_array_fields_on_store()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'required_skills' => 'not_array',
            'required_tools' => 'not_array'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['required_skills', 'required_tools']);
    }

    /** @test */
    public function it_validates_boolean_fields_on_store()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'is_active' => 'not_boolean',
            'is_standard' => 'not_boolean'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['is_active', 'is_standard']);
    }

    /** @test */
    public function it_validates_unique_name_on_store()
    {
        TaskTemplate::factory()->create(['name' => 'Existing Template']);

        $data = [
            'name' => 'Existing Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_validates_unique_name_ignoring_current_on_update()
    {
        $taskTemplate1 = TaskTemplate::factory()->create(['name' => 'Template 1']);
        $taskTemplate2 = TaskTemplate::factory()->create(['name' => 'Template 2']);

        $updateData = ['name' => 'Template 1'];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/task-templates/{$taskTemplate2->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_can_handle_cross_field_validation()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'estimated_duration_hours' => 0,
            'estimated_cost' => -100
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['estimated_duration_hours', 'estimated_cost']);
    }

    /** @test */
    public function it_can_handle_complex_array_validation()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'checklist_items' => [''],
            'work_instructions' => ['']
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['checklist_items.0', 'work_instructions.0']);
    }

    /** @test */
    public function it_can_handle_version_validation()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'version' => 'invalid_version_format'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['version']);
    }

    /** @test */
    public function it_can_handle_department_validation()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'department' => str_repeat('a', 101)
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['department']);
    }

    /** @test */
    public function it_can_handle_budget_codes_validation()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'budget_code' => str_repeat('a', 51),
            'cost_center' => str_repeat('a', 51),
            'project_code' => str_repeat('a', 51)
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['budget_code', 'cost_center', 'project_code']);
    }

    /** @test */
    public function it_can_handle_tags_validation()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'tags' => ['tag1', 'tag2', 'tag3', 'tag4', 'tag5', 'tag6']
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tags']);
    }

    /** @test */
    public function it_can_handle_notes_validation()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'notes' => str_repeat('a', 1001)
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['notes']);
    }

    /** @test */
    public function it_can_handle_required_skills_validation()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'required_skills' => ['skill1', 'skill2', 'skill3', 'skill4', 'skill5', 'skill6']
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['required_skills']);
    }

    /** @test */
    public function it_can_handle_required_tools_validation()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'required_tools' => ['tool1', 'tool2', 'tool3', 'tool4', 'tool5', 'tool6']
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['required_tools']);
    }

    /** @test */
    public function it_can_handle_required_parts_validation()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'required_parts' => ['part1', 'part2', 'part3', 'part4', 'part5', 'part6']
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['required_parts']);
    }

    /** @test */
    public function it_can_handle_safety_requirements_validation()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'safety_requirements' => ['req1', 'req2', 'req3', 'req4', 'req5', 'req6']
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['safety_requirements']);
    }

    /** @test */
    public function it_can_handle_technical_requirements_validation()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'technical_requirements' => ['req1', 'req2', 'req3', 'req4', 'req5', 'req6']
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['technical_requirements']);
    }

    /** @test */
    public function it_can_handle_quality_standards_validation()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'quality_standards' => ['std1', 'std2', 'std3', 'std4', 'std5', 'std6']
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quality_standards']);
    }

    /** @test */
    public function it_can_handle_checklist_items_validation()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'checklist_items' => ['item1', 'item2', 'item3', 'item4', 'item5', 'item6']
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['checklist_items']);
    }

    /** @test */
    public function it_can_handle_work_instructions_validation()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'work_instructions' => ['inst1', 'inst2', 'inst3', 'inst4', 'inst5', 'inst6']
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['work_instructions']);
    }

    /** @test */
    public function it_can_handle_compliance_requirements_validation()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'compliance_requirements' => ['req1', 'req2', 'req3', 'req4', 'req5', 'req6']
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['compliance_requirements']);
    }

    /** @test */
    public function it_can_handle_documentation_required_validation()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'documentation_required' => ['doc1', 'doc2', 'doc3', 'doc4', 'doc5', 'doc6']
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['documentation_required']);
    }

    /** @test */
    public function it_can_handle_environmental_considerations_validation()
    {
        $data = [
            'name' => 'Test Template',
            'template_type' => 'maintenance',
            'category' => 'Electrical',
            'environmental_considerations' => ['env1', 'env2', 'env3', 'env4', 'env5', 'env6']
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/task-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['environmental_considerations']);
    }
}
