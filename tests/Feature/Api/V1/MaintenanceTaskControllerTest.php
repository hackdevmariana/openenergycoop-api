<?php

namespace Tests\Feature\Api\V1;

use App\Models\MaintenanceTask;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MaintenanceTaskControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create();
        
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_list_maintenance_tasks()
    {
        MaintenanceTask::factory()->count(5)->create([
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/maintenance-tasks');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'task_type',
                            'status',
                            'priority',
                            'due_date',
                            'estimated_hours',
                            'created_at',
                        ]
                    ],
                    'meta' => [
                        'current_page',
                        'total',
                        'per_page',
                    ]
                ]);

        $this->assertCount(5, $response->json('data'));
    }

    /** @test */
    public function it_can_filter_maintenance_tasks_by_task_type()
    {
        MaintenanceTask::factory()->create([
            'task_type' => 'inspection',
            'assigned_by' => $this->user->id,
        ]);
        
        MaintenanceTask::factory()->create([
            'task_type' => 'repair',
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/maintenance-tasks?task_type=inspection');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('inspection', $response->json('data.0.task_type'));
    }

    /** @test */
    public function it_can_filter_maintenance_tasks_by_status()
    {
        MaintenanceTask::factory()->create([
            'status' => 'pending',
            'assigned_by' => $this->user->id,
        ]);
        
        MaintenanceTask::factory()->create([
            'status' => 'in_progress',
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/maintenance-tasks?status=pending');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('pending', $response->json('data.0.status'));
    }

    /** @test */
    public function it_can_search_maintenance_tasks()
    {
        MaintenanceTask::factory()->create([
            'title' => 'Inspection Task Alpha',
            'assigned_by' => $this->user->id,
        ]);
        
        MaintenanceTask::factory()->create([
            'title' => 'Repair Task Beta',
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/maintenance-tasks?search=inspection');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertStringContainsString('Inspection', $response->json('data.0.title'));
    }

    /** @test */
    public function it_can_create_maintenance_task()
    {
        $taskData = [
            'title' => 'Test Maintenance Task',
            'description' => 'A test maintenance task',
            'task_type' => 'inspection',
            'status' => 'pending',
            'priority' => 'medium',
            'due_date' => now()->addDays(7)->toDateString(),
            'estimated_hours' => 4.5,
            'estimated_cost' => 150.00,
            'organization_id' => $this->organization->id,
        ];

        $response = $this->postJson('/api/v1/maintenance-tasks', $taskData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'title',
                        'task_type',
                        'status',
                        'priority',
                        'estimated_hours',
                    ]
                ]);

        $this->assertDatabaseHas('maintenance_tasks', [
            'title' => 'Test Maintenance Task',
            'task_type' => 'inspection',
            'assigned_by' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_task()
    {
        $response = $this->postJson('/api/v1/maintenance-tasks', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'title',
                    'task_type',
                    'status',
                    'priority',
                    'due_date',
                    'estimated_hours',
                ]);
    }

    /** @test */
    public function it_validates_task_type_enum_when_creating()
    {
        $taskData = [
            'title' => 'Test Task',
            'task_type' => 'invalid_type',
            'status' => 'pending',
            'priority' => 'medium',
            'due_date' => now()->addDays(7)->toDateString(),
            'estimated_hours' => 4.5,
        ];

        $response = $this->postJson('/api/v1/maintenance-tasks', $taskData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['task_type']);
    }

    /** @test */
    public function it_validates_due_date_is_in_future()
    {
        $taskData = [
            'title' => 'Test Task',
            'task_type' => 'inspection',
            'status' => 'pending',
            'priority' => 'medium',
            'due_date' => now()->subDays(1)->toDateString(),
            'estimated_hours' => 4.5,
        ];

        $response = $this->postJson('/api/v1/maintenance-tasks', $taskData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['due_date']);
    }

    /** @test */
    public function it_can_show_maintenance_task()
    {
        $task = MaintenanceTask::factory()->create([
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->getJson("/api/v1/maintenance-tasks/{$task->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'title',
                        'task_type',
                        'status',
                        'priority',
                        'due_date',
                        'estimated_hours',
                        'created_at',
                        'links',
                    ]
                ]);

        $this->assertEquals($task->id, $response->json('data.id'));
    }

    /** @test */
    public function it_returns_404_for_nonexistent_task()
    {
        $response = $this->getJson('/api/v1/maintenance-tasks/999999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_maintenance_task()
    {
        $task = MaintenanceTask::factory()->create([
            'assigned_by' => $this->user->id,
        ]);

        $updateData = [
            'title' => 'Updated Task Title',
            'estimated_hours' => 6.0,
        ];

        $response = $this->putJson("/api/v1/maintenance-tasks/{$task->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'title',
                        'estimated_hours',
                    ]
                ]);

        $this->assertDatabaseHas('maintenance_tasks', [
            'id' => $task->id,
            'title' => 'Updated Task Title',
            'estimated_hours' => 6.0,
        ]);
    }

    /** @test */
    public function it_can_delete_maintenance_task()
    {
        $task = MaintenanceTask::factory()->create([
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/v1/maintenance-tasks/{$task->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Maintenance task deleted successfully'
                ]);

        $this->assertDatabaseMissing('maintenance_tasks', [
            'id' => $task->id,
        ]);
    }

    /** @test */
    public function it_can_get_overdue_maintenance_tasks()
    {
        MaintenanceTask::factory()->create([
            'due_date' => now()->subDays(5),
            'status' => 'pending',
        ]);
        
        MaintenanceTask::factory()->create([
            'due_date' => now()->addDays(5),
            'status' => 'pending',
        ]);

        $response = $this->getJson('/api/v1/maintenance-tasks/overdue');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertTrue($response->json('data.0.is_overdue'));
    }

    /** @test */
    public function it_can_get_today_maintenance_tasks()
    {
        MaintenanceTask::factory()->create([
            'due_date' => today(),
            'assigned_by' => $this->user->id,
        ]);
        
        MaintenanceTask::factory()->create([
            'due_date' => now()->addDays(1),
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/maintenance-tasks/today');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(today()->format('d/m/Y'), $response->json('data.0.due_date_formatted'));
    }

    /** @test */
    public function it_can_get_week_maintenance_tasks()
    {
        MaintenanceTask::factory()->create([
            'due_date' => now()->startOfWeek()->addDays(2),
            'assigned_by' => $this->user->id,
        ]);
        
        MaintenanceTask::factory()->create([
            'due_date' => now()->addWeeks(2),
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/maintenance-tasks/week');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    /** @test */
    public function it_can_start_maintenance_task()
    {
        $task = MaintenanceTask::factory()->create([
            'status' => 'pending',
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/maintenance-tasks/{$task->id}/start");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Maintenance task started successfully'
                ]);

        $this->assertDatabaseHas('maintenance_tasks', [
            'id' => $task->id,
            'status' => 'in_progress',
        ]);
    }

    /** @test */
    public function it_cannot_start_non_pending_task()
    {
        $task = MaintenanceTask::factory()->create([
            'status' => 'in_progress',
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/maintenance-tasks/{$task->id}/start");

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_complete_maintenance_task()
    {
        $task = MaintenanceTask::factory()->create([
            'status' => 'in_progress',
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/maintenance-tasks/{$task->id}/complete", [
            'completion_notes' => 'Task completed successfully',
            'actual_hours' => 5.0,
            'actual_cost' => 200.00,
            'quality_score' => 95,
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Maintenance task completed successfully'
                ]);

        $this->assertDatabaseHas('maintenance_tasks', [
            'id' => $task->id,
            'status' => 'completed',
            'completion_notes' => 'Task completed successfully',
            'actual_hours' => 5.0,
            'actual_cost' => 200.00,
            'quality_score' => 95,
        ]);
    }

    /** @test */
    public function it_cannot_complete_non_in_progress_task()
    {
        $task = MaintenanceTask::factory()->create([
            'status' => 'pending',
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/maintenance-tasks/{$task->id}/complete", [
            'completion_notes' => 'Task completed',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_pause_maintenance_task()
    {
        $task = MaintenanceTask::factory()->create([
            'status' => 'in_progress',
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/maintenance-tasks/{$task->id}/pause", [
            'pause_reason' => 'Waiting for parts'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Maintenance task paused successfully'
                ]);

        $this->assertDatabaseHas('maintenance_tasks', [
            'id' => $task->id,
            'status' => 'paused',
            'pause_reason' => 'Waiting for parts',
        ]);
    }

    /** @test */
    public function it_validates_pause_reason()
    {
        $task = MaintenanceTask::factory()->create([
            'status' => 'in_progress',
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/maintenance-tasks/{$task->id}/pause", []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['pause_reason']);
    }

    /** @test */
    public function it_can_resume_paused_maintenance_task()
    {
        $task = MaintenanceTask::factory()->create([
            'status' => 'paused',
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/maintenance-tasks/{$task->id}/resume");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Maintenance task resumed successfully'
                ]);

        $this->assertDatabaseHas('maintenance_tasks', [
            'id' => $task->id,
            'status' => 'in_progress',
        ]);
    }

    /** @test */
    public function it_can_cancel_maintenance_task()
    {
        $task = MaintenanceTask::factory()->create([
            'status' => 'pending',
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/maintenance-tasks/{$task->id}/cancel", [
            'cancellation_reason' => 'Equipment no longer available'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Maintenance task cancelled successfully'
                ]);

        $this->assertDatabaseHas('maintenance_tasks', [
            'id' => $task->id,
            'status' => 'cancelled',
            'cancellation_reason' => 'Equipment no longer available',
        ]);
    }

    /** @test */
    public function it_validates_cancellation_reason()
    {
        $task = MaintenanceTask::factory()->create([
            'status' => 'pending',
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/maintenance-tasks/{$task->id}/cancel", []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['cancellation_reason']);
    }

    /** @test */
    public function it_can_reassign_maintenance_task()
    {
        $newUser = User::factory()->create();
        $task = MaintenanceTask::factory()->create([
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/maintenance-tasks/{$task->id}/reassign", [
            'assigned_to' => $newUser->id,
            'reassignment_reason' => 'Better suited for this task'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Maintenance task reassigned successfully'
                ]);

        $this->assertDatabaseHas('maintenance_tasks', [
            'id' => $task->id,
            'assigned_to' => $newUser->id,
            'reassignment_reason' => 'Better suited for this task',
        ]);
    }

    /** @test */
    public function it_validates_assigned_to_user_exists()
    {
        $task = MaintenanceTask::factory()->create([
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/maintenance-tasks/{$task->id}/reassign", [
            'assigned_to' => 999999,
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['assigned_to']);
    }

    /** @test */
    public function it_can_update_task_progress()
    {
        $task = MaintenanceTask::factory()->create([
            'status' => 'in_progress',
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/maintenance-tasks/{$task->id}/update-progress", [
            'progress_percentage' => 75,
            'progress_notes' => 'Three quarters complete',
            'estimated_completion_time' => now()->addHours(2)->toISOString(),
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Task progress updated successfully'
                ]);

        $this->assertDatabaseHas('maintenance_tasks', [
            'id' => $task->id,
            'progress_percentage' => 75,
            'progress_notes' => 'Three quarters complete',
        ]);
    }

    /** @test */
    public function it_cannot_update_progress_for_non_in_progress_task()
    {
        $task = MaintenanceTask::factory()->create([
            'status' => 'pending',
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/maintenance-tasks/{$task->id}/update-progress", [
            'progress_percentage' => 50,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_duplicate_maintenance_task()
    {
        $task = MaintenanceTask::factory()->create([
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/maintenance-tasks/{$task->id}/duplicate");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Maintenance task duplicated successfully'
                ]);

        $this->assertDatabaseHas('maintenance_tasks', [
            'title' => $task->title . ' (Copia)',
            'status' => 'pending',
            'progress_percentage' => 0,
        ]);
    }

    /** @test */
    public function it_can_get_maintenance_task_statistics()
    {
        MaintenanceTask::factory()->count(3)->create([
            'status' => 'completed',
            'assigned_by' => $this->user->id,
        ]);
        
        MaintenanceTask::factory()->count(2)->create([
            'status' => 'pending',
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/maintenance-tasks/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_tasks',
                        'completed_tasks',
                        'pending_tasks',
                        'tasks_by_type',
                        'tasks_by_status',
                        'tasks_by_priority',
                    ]
                ]);

        $this->assertEquals(5, $response->json('data.total_tasks'));
        $this->assertEquals(3, $response->json('data.completed_tasks'));
        $this->assertEquals(2, $response->json('data.pending_tasks'));
    }

    /** @test */
    public function it_requires_authentication_for_protected_routes()
    {
        // Clear authentication
        $this->withoutMiddleware();

        $response = $this->getJson('/api/v1/maintenance-tasks');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_filter_by_estimated_hours_range()
    {
        MaintenanceTask::factory()->create([
            'estimated_hours' => 2.0,
            'assigned_by' => $this->user->id,
        ]);
        
        MaintenanceTask::factory()->create([
            'estimated_hours' => 8.0,
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/maintenance-tasks?estimated_hours_min=5&estimated_hours_max=10');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(8.0, $response->json('data.0.estimated_hours'));
    }

    /** @test */
    public function it_can_sort_maintenance_tasks()
    {
        MaintenanceTask::factory()->create([
            'title' => 'Alpha Task',
            'assigned_by' => $this->user->id,
        ]);
        
        MaintenanceTask::factory()->create([
            'title' => 'Beta Task',
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/maintenance-tasks?sort_by=title&sort_direction=asc');

        $response->assertStatus(200);
        $this->assertEquals('Alpha Task', $response->json('data.0.title'));
        $this->assertEquals('Beta Task', $response->json('data.1.title'));
    }

    /** @test */
    public function it_can_paginate_results()
    {
        MaintenanceTask::factory()->count(25)->create([
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/maintenance-tasks?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(25, $response->json('meta.total'));
        $this->assertEquals(3, $response->json('meta.last_page'));
    }
}
