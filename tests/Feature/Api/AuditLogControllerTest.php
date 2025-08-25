<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class AuditLogControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $auditLog;
    protected $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear usuario autenticado
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
        
        // Crear organización
        $this->organization = Organization::factory()->create();
        
        // Crear log de auditoría de prueba
        $this->auditLog = AuditLog::factory()->create([
            'user_id' => $this->user->id,
            'actor_type' => 'user',
            'actor_identifier' => $this->user->id,
            'action' => 'user.login',
            'description' => 'User logged in successfully',
            'auditable_type' => 'App\Models\User',
            'auditable_id' => $this->user->id
        ]);
    }

    /** @test */
    public function it_can_list_audit_logs()
    {
        $response = $this->getJson('/api/v1/audit-logs');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id', 'user_id', 'actor_type', 'actor_identifier', 'action',
                            'description', 'auditable_type', 'auditable_id', 'old_values',
                            'new_values', 'ip_address', 'user_agent', 'url', 'created_at'
                        ]
                    ],
                    'links', 'meta'
                ]);
    }

    /** @test */
    public function it_can_create_audit_log()
    {
        $logData = [
            'actor_type' => 'system',
            'actor_identifier' => 'cron_job',
            'action' => 'system.maintenance',
            'description' => 'System maintenance completed',
            'auditable_type' => 'App\Models\System',
            'auditable_id' => 1,
            'old_values' => ['status' => 'running'],
            'new_values' => ['status' => 'maintenance']
        ];

        $response = $this->postJson('/api/v1/audit-logs', $logData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'user_id', 'actor_type', 'actor_identifier', 'action',
                        'description', 'auditable_type', 'auditable_id', 'old_values',
                        'new_values', 'ip_address', 'user_agent', 'url', 'created_at'
                    ]
                ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'system.maintenance',
            'actor_type' => 'system'
        ]);
    }

    /** @test */
    public function it_can_show_audit_log()
    {
        $response = $this->getJson("/api/v1/audit-logs/{$this->auditLog->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'user_id', 'actor_type', 'actor_identifier', 'action',
                        'description', 'auditable_type', 'auditable_id', 'old_values',
                        'new_values', 'ip_address', 'user_agent', 'url', 'created_at'
                    ]
                ]);
    }

    /** @test */
    public function it_can_update_audit_log()
    {
        $updateData = [
            'description' => 'Updated description',
            'new_values' => ['status' => 'updated']
        ];

        $response = $this->putJson("/api/v1/audit-logs/{$this->auditLog->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'user_id', 'actor_type', 'actor_identifier', 'action',
                        'description', 'auditable_type', 'auditable_id', 'old_values',
                        'new_values', 'ip_address', 'user_agent', 'url', 'created_at'
                    ]
                ]);

        $this->assertDatabaseHas('audit_logs', [
            'id' => $this->auditLog->id,
            'description' => 'Updated description'
        ]);
    }

    /** @test */
    public function it_can_delete_audit_log()
    {
        $response = $this->deleteJson("/api/v1/audit-logs/{$this->auditLog->id}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Audit log deleted successfully']);

        $this->assertDatabaseMissing('audit_logs', ['id' => $this->auditLog->id]);
    }

    /** @test */
    public function it_can_get_audit_log_changes()
    {
        $response = $this->getJson("/api/v1/audit-logs/{$this->auditLog->id}/changes");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'audit_log_id', 'changes', 'diff', 'formatted_changes'
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_audit_log_statistics()
    {
        $response = $this->getJson('/api/v1/audit-logs/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_logs', 'logs_by_action', 'logs_by_actor_type',
                        'logs_by_date', 'recent_activity'
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_audit_log_actions()
    {
        $response = $this->getJson('/api/v1/audit-logs/actions');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['action', 'count', 'description', 'category']
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_audit_log_actor_types()
    {
        $response = $this->getJson('/api/v1/audit-logs/actor-types');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['type', 'count', 'description', 'examples']
                    ]
                ]);
    }

    /** @test */
    public function it_can_export_audit_logs()
    {
        $response = $this->getJson('/api/v1/audit-logs/export?format=csv');

        $response->assertStatus(200)
                ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /** @test */
    public function it_can_export_audit_logs_as_json()
    {
        $response = $this->getJson('/api/v1/audit-logs/export?format=json');

        $response->assertStatus(200)
                ->assertHeader('Content-Type', 'application/json');
    }

    /** @test */
    public function it_can_export_audit_logs_as_xml()
    {
        $response = $this->getJson('/api/v1/audit-logs/export?format=xml');

        $response->assertStatus(200)
                ->assertHeader('Content-Type', 'application/xml');
    }

    /** @test */
    public function it_can_get_audit_log_summary()
    {
        $response = $this->getJson('/api/v1/audit-logs/summary');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_logs', 'unique_users', 'unique_actions',
                        'most_active_user', 'most_common_action'
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_audit_log_timeline()
    {
        $response = $this->getJson('/api/v1/audit-logs/timeline');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'timeline' => [
                            '*' => ['date', 'count', 'actions', 'users']
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_can_bulk_delete_audit_logs()
    {
        $auditLog2 = AuditLog::factory()->create(['user_id' => $this->user->id]);
        
        $bulkData = [
            'audit_log_ids' => [$this->auditLog->id, $auditLog2->id]
        ];

        $response = $this->postJson('/api/v1/audit-logs/bulk-delete', $bulkData);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Audit logs deleted successfully']);

        $this->assertDatabaseMissing('audit_logs', ['id' => $this->auditLog->id]);
        $this->assertDatabaseMissing('audit_logs', ['id' => $auditLog2->id]);
    }

    /** @test */
    public function it_validates_required_fields_on_create()
    {
        $response = $this->postJson('/api/v1/audit-logs', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['action', 'description']);
    }

    /** @test */
    public function it_validates_action_on_create()
    {
        $logData = [
            'action' => 'invalid.action',
            'description' => 'Test description'
        ];

        $response = $this->postJson('/api/v1/audit-logs', $logData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['action']);
    }

    /** @test */
    public function it_requires_authentication()
    {
        auth()->logout();

        $response = $this->getJson('/api/v1/audit-logs');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_filter_audit_logs_by_action()
    {
        $loginLog = AuditLog::factory()->create([
            'user_id' => $this->user->id,
            'action' => 'user.login'
        ]);

        $response = $this->getJson('/api/v1/audit-logs?action=user.login');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_filter_audit_logs_by_actor_type()
    {
        $systemLog = AuditLog::factory()->create([
            'user_id' => $this->user->id,
            'actor_type' => 'system'
        ]);

        $response = $this->getJson('/api/v1/audit-logs?actor_type=system');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_filter_audit_logs_by_date_range()
    {
        $response = $this->getJson('/api/v1/audit-logs?date_from=2024-01-01&date_to=2024-12-31');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_filter_audit_logs_by_user()
    {
        $otherUser = User::factory()->create();
        $otherUserLog = AuditLog::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->getJson('/api/v1/audit-logs?user_id=' . $this->user->id);

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_sort_audit_logs()
    {
        $response = $this->getJson('/api/v1/audit-logs?sort=created_at&order=desc');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_paginate_audit_logs()
    {
        AuditLog::factory()->count(15)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/audit-logs?per_page=10');

        $response->assertStatus(200)
                ->assertJsonCount(10, 'data');
    }

    /** @test */
    public function it_can_search_audit_logs()
    {
        $response = $this->getJson('/api/v1/audit-logs?search=login');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_filter_audit_logs_by_auditable_type()
    {
        $userLog = AuditLog::factory()->create([
            'user_id' => $this->user->id,
            'auditable_type' => 'App\Models\User'
        ]);

        $response = $this->getJson('/api/v1/audit-logs?auditable_type=App\Models\User');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }
}
