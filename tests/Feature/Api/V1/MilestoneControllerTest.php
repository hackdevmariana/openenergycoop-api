<?php

namespace Tests\Feature\Api\V1;

use App\Models\Milestone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MilestoneControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_returns_milestones_list()
    {
        Milestone::factory()->count(5)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/milestones');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'milestone_type',
                        'status',
                        'priority',
                        'target_date',
                        'progress_percentage'
                    ]
                ],
                'meta',
                'summary',
                'with'
            ]);
    }

    public function test_index_with_filters()
    {
        Milestone::factory()->create(['milestone_type' => Milestone::MILESTONE_TYPE_PROJECT]);
        Milestone::factory()->create(['milestone_type' => Milestone::MILESTONE_TYPE_PHASE]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/milestones?milestone_type=' . Milestone::MILESTONE_TYPE_PROJECT);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_index_with_search()
    {
        Milestone::factory()->create(['title' => 'Project Alpha']);
        Milestone::factory()->create(['title' => 'Project Beta']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/milestones?search=Alpha');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Project Alpha', $response->json('data.0.title'));
    }

    public function test_index_with_sorting()
    {
        Milestone::factory()->create(['title' => 'B Project']);
        Milestone::factory()->create(['title' => 'A Project']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/milestones?sort=title&order=asc');

        $response->assertStatus(200);
        $this->assertEquals('A Project', $response->json('data.0.title'));
        $this->assertEquals('B Project', $response->json('data.1.title'));
    }

    public function test_show_returns_milestone()
    {
        $milestone = Milestone::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/milestones/{$milestone->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'milestone_type',
                    'status',
                    'priority',
                    'target_date',
                    'progress_percentage'
                ]
            ]);
    }

    public function test_store_creates_milestone()
    {
        $milestoneData = [
            'title' => 'New Milestone',
            'description' => 'Milestone description',
            'milestone_type' => Milestone::MILESTONE_TYPE_PROJECT,
            'status' => Milestone::STATUS_NOT_STARTED,
            'priority' => Milestone::PRIORITY_MEDIUM,
            'target_date' => now()->addDays(30)->toDateString(),
            'progress_percentage' => 0,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/milestones', $milestoneData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'milestone_type',
                    'status',
                    'priority',
                    'target_date',
                    'progress_percentage'
                ],
                'message'
            ]);

        $this->assertDatabaseHas('milestones', [
            'title' => 'New Milestone',
            'milestone_type' => Milestone::MILESTONE_TYPE_PROJECT,
            'created_by' => $this->user->id
        ]);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/milestones', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'title',
                'milestone_type',
                'status',
                'priority',
                'target_date'
            ]);
    }

    public function test_store_validates_milestone_type()
    {
        $milestoneData = [
            'title' => 'New Milestone',
            'milestone_type' => 'invalid_type',
            'status' => Milestone::STATUS_NOT_STARTED,
            'priority' => Milestone::PRIORITY_MEDIUM,
            'target_date' => now()->addDays(30)->toDateString(),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/milestones', $milestoneData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['milestone_type']);
    }

    public function test_store_validates_target_date_future()
    {
        $milestoneData = [
            'title' => 'New Milestone',
            'milestone_type' => Milestone::MILESTONE_TYPE_PROJECT,
            'status' => Milestone::STATUS_NOT_STARTED,
            'priority' => Milestone::PRIORITY_MEDIUM,
            'target_date' => now()->subDays(1)->toDateString(),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/milestones', $milestoneData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['target_date']);
    }

    public function test_store_validates_progress_percentage_range()
    {
        $milestoneData = [
            'title' => 'New Milestone',
            'milestone_type' => Milestone::MILESTONE_TYPE_PROJECT,
            'status' => Milestone::STATUS_NOT_STARTED,
            'priority' => Milestone::PRIORITY_MEDIUM,
            'target_date' => now()->addDays(30)->toDateString(),
            'progress_percentage' => 150,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/milestones', $milestoneData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['progress_percentage']);
    }

    public function test_update_modifies_milestone()
    {
        $milestone = Milestone::factory()->create();
        $updateData = [
            'title' => 'Updated Milestone',
            'progress_percentage' => 50,
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/milestones/{$milestone->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'progress_percentage'
                ],
                'message'
            ]);

        $this->assertDatabaseHas('milestones', [
            'id' => $milestone->id,
            'title' => 'Updated Milestone',
            'progress_percentage' => 50
        ]);
    }

    public function test_update_validates_milestone_type()
    {
        $milestone = Milestone::factory()->create();
        $updateData = [
            'milestone_type' => 'invalid_type',
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/milestones/{$milestone->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['milestone_type']);
    }

    public function test_update_validates_progress_coherence_with_status()
    {
        $milestone = Milestone::factory()->create([
            'status' => Milestone::STATUS_COMPLETED,
            'progress_percentage' => 100
        ]);

        $updateData = [
            'progress_percentage' => 50,
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/milestones/{$milestone->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['progress_percentage']);
    }

    public function test_destroy_deletes_milestone()
    {
        $milestone = Milestone::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/milestones/{$milestone->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Hito eliminado exitosamente']);

        $this->assertSoftDeleted('milestones', ['id' => $milestone->id]);
    }

    public function test_statistics_returns_milestone_stats()
    {
        Milestone::factory()->count(3)->create(['status' => Milestone::STATUS_NOT_STARTED]);
        Milestone::factory()->count(2)->create(['status' => Milestone::STATUS_IN_PROGRESS]);
        Milestone::factory()->count(1)->create(['status' => Milestone::STATUS_COMPLETED]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/milestones/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_milestones',
                    'by_type',
                    'by_status',
                    'by_priority',
                    'overdue',
                    'due_soon',
                    'completed',
                    'in_progress',
                    'not_started',
                    'high_priority'
                ]
            ]);

        $this->assertEquals(6, $response->json('data.total_milestones'));
        $this->assertEquals(3, $response->json('data.not_started'));
        $this->assertEquals(2, $response->json('data.in_progress'));
        $this->assertEquals(1, $response->json('data.completed'));
    }

    public function test_milestone_types_returns_available_types()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/milestones/milestone-types');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);

        $types = $response->json('data');
        $this->assertContains(Milestone::MILESTONE_TYPE_PROJECT, $types);
        $this->assertContains(Milestone::MILESTONE_TYPE_PHASE, $types);
        $this->assertContains(Milestone::MILESTONE_TYPE_DELIVERABLE, $types);
        $this->assertContains(Milestone::MILESTONE_TYPE_REVIEW, $types);
    }

    public function test_statuses_returns_available_statuses()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/milestones/statuses');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);

        $statuses = $response->json('data');
        $this->assertContains(Milestone::STATUS_NOT_STARTED, $statuses);
        $this->assertContains(Milestone::STATUS_IN_PROGRESS, $statuses);
        $this->assertContains(Milestone::STATUS_COMPLETED, $statuses);
        $this->assertContains(Milestone::STATUS_ON_HOLD, $statuses);
        $this->assertContains(Milestone::STATUS_CANCELLED, $statuses);
    }

    public function test_priorities_returns_available_priorities()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/milestones/priorities');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);

        $priorities = $response->json('data');
        $this->assertContains(Milestone::PRIORITY_LOW, $priorities);
        $this->assertContains(Milestone::PRIORITY_MEDIUM, $priorities);
        $this->assertContains(Milestone::PRIORITY_HIGH, $priorities);
        $this->assertContains(Milestone::PRIORITY_CRITICAL, $priorities);
    }

    public function test_start_activates_milestone()
    {
        $milestone = Milestone::factory()->create([
            'status' => Milestone::STATUS_NOT_STARTED
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/milestones/{$milestone->id}/start");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Hito iniciado exitosamente']);

        $this->assertDatabaseHas('milestones', [
            'id' => $milestone->id,
            'status' => Milestone::STATUS_IN_PROGRESS
        ]);
    }

    public function test_start_fails_for_invalid_status()
    {
        $milestone = Milestone::factory()->create([
            'status' => Milestone::STATUS_IN_PROGRESS
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/milestones/{$milestone->id}/start");

        $response->assertStatus(422)
            ->assertJson(['message' => 'No se puede iniciar este hito']);
    }

    public function test_complete_finishes_milestone()
    {
        $milestone = Milestone::factory()->create([
            'status' => Milestone::STATUS_IN_PROGRESS,
            'progress_percentage' => 75
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/milestones/{$milestone->id}/complete");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Hito completado exitosamente']);

        $this->assertDatabaseHas('milestones', [
            'id' => $milestone->id,
            'status' => Milestone::STATUS_COMPLETED,
            'progress_percentage' => 100
        ]);
    }

    public function test_complete_fails_for_invalid_status()
    {
        $milestone = Milestone::factory()->create([
            'status' => Milestone::STATUS_COMPLETED
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/milestones/{$milestone->id}/complete");

        $response->assertStatus(422)
            ->assertJson(['message' => 'No se puede completar este hito']);
    }

    public function test_overdue_returns_overdue_milestones()
    {
        Milestone::factory()->create([
            'target_date' => now()->subDays(5),
            'status' => Milestone::STATUS_IN_PROGRESS
        ]);

        Milestone::factory()->create([
            'target_date' => now()->addDays(5),
            'status' => Milestone::STATUS_NOT_STARTED
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/milestones/overdue');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_due_soon_returns_due_soon_milestones()
    {
        Milestone::factory()->create([
            'target_date' => now()->addDays(3),
            'status' => Milestone::STATUS_NOT_STARTED
        ]);

        Milestone::factory()->create([
            'target_date' => now()->addDays(10),
            'status' => Milestone::STATUS_NOT_STARTED
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/milestones/due-soon');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_high_priority_returns_high_priority_milestones()
    {
        Milestone::factory()->create(['priority' => Milestone::PRIORITY_LOW]);
        Milestone::factory()->create(['priority' => Milestone::PRIORITY_HIGH]);
        Milestone::factory()->create(['priority' => Milestone::PRIORITY_CRITICAL]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/milestones/high-priority');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data')); // HIGH y CRITICAL
    }

    public function test_authentication_required()
    {
        $response = $this->getJson('/api/v1/milestones');
        $response->assertStatus(401);
    }

    public function test_pagination_works()
    {
        Milestone::factory()->count(25)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/milestones?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(25, $response->json('meta.total'));
        $this->assertEquals(3, $response->json('meta.last_page'));
    }

    public function test_relationships_are_loaded()
    {
        $parentMilestone = Milestone::factory()->create();
        $milestone = Milestone::factory()->create([
            'parent_milestone_id' => $parentMilestone->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/milestones/{$milestone->id}");

        $response->assertStatus(200);
        $this->assertNotNull($response->json('data.parent_milestone'));
        $this->assertEquals($parentMilestone->id, $response->json('data.parent_milestone.id'));
    }

    public function test_custom_endpoints_work()
    {
        $milestone = Milestone::factory()->create([
            'status' => Milestone::STATUS_NOT_STARTED
        ]);

        // Test start endpoint
        $startResponse = $this->actingAs($this->user)
            ->patchJson("/api/v1/milestones/{$milestone->id}/start");
        $startResponse->assertStatus(200);

        // Test complete endpoint
        $completeResponse = $this->actingAs($this->user)
            ->patchJson("/api/v1/milestones/{$milestone->id}/complete");
        $completeResponse->assertStatus(200);

        // Verify final state
        $this->assertDatabaseHas('milestones', [
            'id' => $milestone->id,
            'status' => Milestone::STATUS_COMPLETED,
            'progress_percentage' => 100
        ]);
    }
}
