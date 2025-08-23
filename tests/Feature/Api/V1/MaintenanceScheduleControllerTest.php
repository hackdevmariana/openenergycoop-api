<?php

namespace Tests\Feature\Api\V1;

use App\Models\MaintenanceSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MaintenanceScheduleControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $maintenanceSchedule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->maintenanceSchedule = MaintenanceSchedule::factory()->create();
    }

    public function test_index_returns_paginated_maintenance_schedules()
    {
        MaintenanceSchedule::factory()->count(15)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/maintenance-schedules');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'name', 'schedule_type', 'frequency_type', 'priority',
                        'is_active', 'created_at', 'updated_at'
                    ]
                ],
                'links', 'meta'
            ]);
    }

    public function test_index_with_filters()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/maintenance-schedules?schedule_type=preventive&frequency_type=daily&priority=high&is_active=true');

        $response->assertStatus(200);
    }

    public function test_index_with_search()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/maintenance-schedules?search=test');

        $response->assertStatus(200);
    }

    public function test_index_with_sorting()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/maintenance-schedules?sort=priority&order=desc');

        $response->assertStatus(200);
    }

    public function test_index_with_pagination_limit()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/maintenance-schedules?per_page=5');

        $response->assertStatus(200);
    }

    public function test_store_creates_new_maintenance_schedule()
    {
        $data = [
            'name' => 'Test Schedule',
            'description' => 'Test Description',
            'schedule_type' => 'preventive',
            'frequency_type' => 'daily',
            'frequency_value' => 1,
            'priority' => 'high',
            'department' => 'maintenance',
            'category' => 'equipment',
            'is_active' => true,
            'auto_generate_tasks' => false,
            'send_notifications' => false,
            'start_date' => now()->addDay()->toDateString(),
            'required_skills' => ['skill1', 'skill2'],
            'required_tools' => ['tool1', 'tool2'],
            'required_materials' => ['material1', 'material2']
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/maintenance-schedules', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'description', 'schedule_type', 'frequency_type',
                    'frequency_value', 'priority', 'department', 'category',
                    'is_active', 'auto_generate_tasks', 'send_notifications',
                    'start_date', 'required_skills', 'required_tools', 'required_materials',
                    'created_at', 'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('maintenance_schedules', [
            'name' => 'Test Schedule',
            'schedule_type' => 'preventive',
            'frequency_type' => 'daily',
            'priority' => 'high'
        ]);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/maintenance-schedules', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'schedule_type', 'frequency_type', 'frequency_value', 'priority', 'start_date']);
    }

    public function test_store_validates_enum_values()
    {
        $data = [
            'name' => 'Test Schedule',
            'schedule_type' => 'invalid_type',
            'frequency_type' => 'invalid_frequency',
            'frequency_value' => 1,
            'priority' => 'invalid_priority',
            'start_date' => now()->addDay()->toDateString()
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/maintenance-schedules', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['schedule_type', 'frequency_type', 'priority']);
    }

    public function test_store_validates_cross_field_consistency()
    {
        $data = [
            'name' => 'Test Schedule',
            'schedule_type' => 'preventive',
            'frequency_type' => 'daily',
            'frequency_value' => 400, // Excede el máximo para daily
            'priority' => 'high',
            'start_date' => now()->addDay()->toDateString()
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/maintenance-schedules', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['frequency_value']);
    }

    public function test_show_returns_maintenance_schedule()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/maintenance-schedules/{$this->maintenanceSchedule->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'schedule_type', 'frequency_type', 'priority',
                    'is_active', 'created_at', 'updated_at'
                ]
            ]);
    }

    public function test_show_returns_404_for_nonexistent_schedule()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/maintenance-schedules/99999');

        $response->assertStatus(404);
    }

    public function test_update_modifies_maintenance_schedule()
    {
        $data = [
            'name' => 'Updated Schedule',
            'description' => 'Updated Description',
            'priority' => 'medium'
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/maintenance-schedules/{$this->maintenanceSchedule->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Updated Schedule',
                    'description' => 'Updated Description',
                    'priority' => 'medium'
                ]
            ]);

        $this->assertDatabaseHas('maintenance_schedules', [
            'id' => $this->maintenanceSchedule->id,
            'name' => 'Updated Schedule',
            'priority' => 'medium'
        ]);
    }

    public function test_update_validates_cross_field_consistency()
    {
        $data = [
            'frequency_value' => 400 // Excede el máximo para daily
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/maintenance-schedules/{$this->maintenanceSchedule->id}", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['frequency_value']);
    }

    public function test_destroy_deletes_maintenance_schedule()
    {
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/maintenance-schedules/{$this->maintenanceSchedule->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('maintenance_schedules', [
            'id' => $this->maintenanceSchedule->id
        ]);
    }

    public function test_statistics_returns_schedule_statistics()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/maintenance-schedules/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_schedules', 'active_schedules', 'inactive_schedules', 'approved_schedules',
                    'pending_approval_schedules', 'high_priority_schedules', 'preventive_schedules',
                    'predictive_schedules', 'condition_based_schedules', 'overdue_schedules',
                    'due_soon_schedules', 'auto_generate_tasks_schedules'
                ]
            ]);
    }

    public function test_schedule_types_returns_schedule_types()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/maintenance-schedules/schedule-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['value', 'label', 'count']
                ]
            ]);
    }

    public function test_frequency_types_returns_frequency_types()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/maintenance-schedules/frequency-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['value', 'label', 'count']
                ]
            ]);
    }

    public function test_priorities_returns_priorities()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/maintenance-schedules/priorities');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['value', 'label', 'count']
                ]
            ]);
    }

    public function test_toggle_active_alternates_schedule_status()
    {
        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/maintenance-schedules/{$this->maintenanceSchedule->id}/toggle-active");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['is_active']
            ]);

        $this->assertDatabaseHas('maintenance_schedules', [
            'id' => $this->maintenanceSchedule->id,
            'is_active' => !$this->maintenanceSchedule->is_active
        ]);
    }

    public function test_duplicate_creates_copy_of_schedule()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/maintenance-schedules/{$this->maintenanceSchedule->id}/duplicate");

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'schedule_type', 'frequency_type', 'priority'
                ]
            ]);

        $this->assertDatabaseCount('maintenance_schedules', 2);
    }

    public function test_active_returns_active_schedules()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/maintenance-schedules/active');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'is_active']
                ]
            ]);
    }

    public function test_overdue_returns_overdue_schedules()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/maintenance-schedules/overdue');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'next_maintenance_date']
                ]
            ]);
    }

    public function test_due_soon_returns_due_soon_schedules()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/maintenance-schedules/due-soon');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'next_maintenance_date']
                ]
            ]);
    }

    public function test_high_priority_returns_high_priority_schedules()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/maintenance-schedules/high-priority');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'priority']
                ]
            ]);
    }

    public function test_by_type_returns_schedules_by_type()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/maintenance-schedules/by-type/preventive');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'schedule_type']
                ]
            ]);
    }

    public function test_by_frequency_type_returns_schedules_by_frequency_type()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/maintenance-schedules/by-frequency-type/daily');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'frequency_type']
                ]
            ]);
    }

    public function test_by_priority_returns_schedules_by_priority()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/maintenance-schedules/by-priority/high');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'priority']
                ]
            ]);
    }

    public function test_by_department_returns_schedules_by_department()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/maintenance-schedules/by-department/maintenance');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'department']
                ]
            ]);
    }

    public function test_by_category_returns_schedules_by_category()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/maintenance-schedules/by-category/equipment');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'category']
                ]
            ]);
    }

    public function test_requires_authentication()
    {
        $response = $this->getJson('/api/v1/maintenance-schedules');
        $response->assertStatus(401);
    }

    public function test_logs_activity_on_create()
    {
        $data = [
            'name' => 'Test Schedule',
            'schedule_type' => 'preventive',
            'frequency_type' => 'daily',
            'frequency_value' => 1,
            'priority' => 'high',
            'start_date' => now()->addDay()->toDateString(),
            'required_skills' => ['skill1'],
            'required_tools' => ['tool1'],
            'required_materials' => ['material1']
        ];

        $this->actingAs($this->user)
            ->postJson('/api/v1/maintenance-schedules', $data);

        // Verificar que se registró la actividad (si tienes un sistema de logging)
        // $this->assertDatabaseHas('activity_log', [...]);
    }

    public function test_validates_frequency_value_consistency()
    {
        $data = [
            'name' => 'Test Schedule',
            'schedule_type' => 'preventive',
            'frequency_type' => 'monthly',
            'frequency_value' => 15, // Excede el máximo para monthly (12)
            'priority' => 'high',
            'start_date' => now()->addDay()->toDateString(),
            'required_skills' => ['skill1'],
            'required_tools' => ['tool1'],
            'required_materials' => ['material1']
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/maintenance-schedules', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['frequency_value']);
    }

    public function test_validates_maintenance_window_duration()
    {
        $data = [
            'name' => 'Test Schedule',
            'schedule_type' => 'preventive',
            'frequency_type' => 'daily',
            'frequency_value' => 1,
            'priority' => 'high',
            'start_date' => now()->addDay()->toDateString(),
            'maintenance_window_start' => '09:00',
            'maintenance_window_end' => '08:00', // Hora anterior
            'required_skills' => ['skill1'],
            'required_tools' => ['tool1'],
            'required_materials' => ['material1']
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/maintenance-schedules', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['maintenance_window_end']);
    }

    public function test_validates_estimated_duration_consistency()
    {
        $data = [
            'name' => 'Test Schedule',
            'schedule_type' => 'preventive',
            'frequency_type' => 'daily',
            'frequency_value' => 1,
            'priority' => 'high',
            'start_date' => now()->addDay()->toDateString(),
            'estimated_duration_hours' => 100, // Excede el máximo para daily (8)
            'required_skills' => ['skill1'],
            'required_tools' => ['tool1'],
            'required_materials' => ['material1']
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/maintenance-schedules', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['estimated_duration_hours']);
    }

    public function test_validates_required_fields_consistency()
    {
        $data = [
            'name' => 'Test Schedule',
            'schedule_type' => 'preventive',
            'frequency_type' => 'daily',
            'frequency_value' => 1,
            'priority' => 'high',
            'start_date' => now()->addDay()->toDateString(),
            'send_notifications' => true,
            'notification_emails' => [], // Vacío cuando se activan notificaciones
            'required_skills' => [], // Vacío
            'required_tools' => [], // Vacío
            'required_materials' => [] // Vacío
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/maintenance-schedules', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['notification_emails', 'required_skills', 'required_tools', 'required_materials']);
    }
}
