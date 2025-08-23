<?php

namespace Tests\Feature\Api\V1;

use App\Models\AutomationRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AutomationRuleControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $automationRule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->automationRule = AutomationRule::factory()->create();
    }

    public function test_index_returns_paginated_automation_rules()
    {
        AutomationRule::factory()->count(15)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/automation-rules');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'name', 'rule_type', 'trigger_type', 'action_type',
                        'priority', 'execution_frequency', 'is_active', 'created_at', 'updated_at'
                    ]
                ],
                'links', 'meta'
            ]);
    }

    public function test_index_with_filters()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/automation-rules?rule_type=scheduled&trigger_type=time&action_type=email&priority=5&is_active=true');

        $response->assertStatus(200);
    }

    public function test_index_with_search()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/automation-rules?search=test');

        $response->assertStatus(200);
    }

    public function test_index_with_sorting()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/automation-rules?sort=priority&order=desc');

        $response->assertStatus(200);
    }

    public function test_index_with_pagination_limit()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/automation-rules?per_page=5');

        $response->assertStatus(200);
    }

    public function test_store_creates_new_automation_rule()
    {
        $data = [
            'name' => 'Test Rule',
            'description' => 'Test Description',
            'rule_type' => 'scheduled',
            'trigger_type' => 'time',
            'action_type' => 'email',
            'priority' => 5,
            'execution_frequency' => 'daily',
            'is_active' => true,
            'retry_on_failure' => false,
            'max_retries' => 3,
            'retry_delay_minutes' => 5
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/automation-rules', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'description', 'rule_type', 'trigger_type',
                    'action_type', 'priority', 'execution_frequency', 'is_active',
                    'retry_on_failure', 'max_retries', 'retry_delay_minutes',
                    'created_at', 'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('automation_rules', [
            'name' => 'Test Rule',
            'rule_type' => 'scheduled',
            'trigger_type' => 'time',
            'action_type' => 'email'
        ]);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/automation-rules', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'rule_type', 'trigger_type', 'action_type', 'execution_frequency']);
    }

    public function test_store_validates_enum_values()
    {
        $data = [
            'name' => 'Test Rule',
            'rule_type' => 'invalid_type',
            'trigger_type' => 'invalid_trigger',
            'action_type' => 'invalid_action',
            'execution_frequency' => 'invalid_frequency'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/automation-rules', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rule_type', 'trigger_type', 'action_type', 'execution_frequency']);
    }

    public function test_store_validates_cross_field_consistency()
    {
        $data = [
            'name' => 'Test Rule',
            'rule_type' => 'scheduled',
            'trigger_type' => 'time',
            'action_type' => 'email',
            'execution_frequency' => 'daily',
            'success_count' => 10,
            'execution_count' => 5
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/automation-rules', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['success_count']);
    }

    public function test_show_returns_automation_rule()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/automation-rules/{$this->automationRule->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'rule_type', 'trigger_type', 'action_type',
                    'priority', 'execution_frequency', 'is_active', 'created_at', 'updated_at'
                ]
            ]);
    }

    public function test_show_returns_404_for_nonexistent_rule()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/automation-rules/99999');

        $response->assertStatus(404);
    }

    public function test_update_modifies_automation_rule()
    {
        $data = [
            'name' => 'Updated Rule',
            'description' => 'Updated Description',
            'priority' => 8
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/automation-rules/{$this->automationRule->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Updated Rule',
                    'description' => 'Updated Description',
                    'priority' => 8
                ]
            ]);

        $this->assertDatabaseHas('automation_rules', [
            'id' => $this->automationRule->id,
            'name' => 'Updated Rule',
            'priority' => 8
        ]);
    }

    public function test_update_validates_cross_field_consistency()
    {
        $data = [
            'success_count' => 10,
            'execution_count' => 5
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/automation-rules/{$this->automationRule->id}", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['success_count']);
    }

    public function test_destroy_deletes_automation_rule()
    {
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/automation-rules/{$this->automationRule->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('automation_rules', [
            'id' => $this->automationRule->id
        ]);
    }

    public function test_statistics_returns_rule_statistics()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/automation-rules/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_rules', 'active_rules', 'inactive_rules', 'approved_rules',
                    'pending_approval_rules', 'high_priority_rules', 'scheduled_rules',
                    'event_driven_rules', 'condition_based_rules', 'total_executions',
                    'total_successes', 'total_failures', 'average_success_rate'
                ]
            ]);
    }

    public function test_types_returns_rule_types()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/automation-rules/types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['value', 'label', 'count']
                ]
            ]);
    }

    public function test_trigger_types_returns_trigger_types()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/automation-rules/trigger-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['value', 'label', 'count']
                ]
            ]);
    }

    public function test_action_types_returns_action_types()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/automation-rules/action-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['value', 'label', 'count']
                ]
            ]);
    }

    public function test_execution_frequencies_returns_execution_frequencies()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/automation-rules/execution-frequencies');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['value', 'label', 'count']
                ]
            ]);
    }

    public function test_toggle_active_alternates_rule_status()
    {
        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/automation-rules/{$this->automationRule->id}/toggle-active");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['is_active']
            ]);

        $this->assertDatabaseHas('automation_rules', [
            'id' => $this->automationRule->id,
            'is_active' => !$this->automationRule->is_active
        ]);
    }

    public function test_duplicate_creates_copy_of_rule()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/automation-rules/{$this->automationRule->id}/duplicate");

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'rule_type', 'trigger_type', 'action_type'
                ]
            ]);

        $this->assertDatabaseCount('automation_rules', 2);
    }

    public function test_active_returns_active_rules()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/automation-rules/active');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'is_active']
                ]
            ]);
    }

    public function test_ready_to_execute_returns_ready_rules()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/automation-rules/ready-to-execute');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'is_active', 'approved_at']
                ]
            ]);
    }

    public function test_failed_returns_failed_rules()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/automation-rules/failed');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'failure_count']
                ]
            ]);
    }

    public function test_successful_returns_successful_rules()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/automation-rules/successful');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'success_count']
                ]
            ]);
    }

    public function test_high_priority_returns_high_priority_rules()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/automation-rules/high-priority');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'priority']
                ]
            ]);
    }

    public function test_by_type_returns_rules_by_type()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/automation-rules/by-type/scheduled');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'rule_type']
                ]
            ]);
    }

    public function test_by_trigger_type_returns_rules_by_trigger_type()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/automation-rules/by-trigger-type/time');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'trigger_type']
                ]
            ]);
    }

    public function test_by_action_type_returns_rules_by_action_type()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/automation-rules/by-action-type/email');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'action_type']
                ]
            ]);
    }

    public function test_by_execution_frequency_returns_rules_by_frequency()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/automation-rules/by-execution-frequency/daily');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'execution_frequency']
                ]
            ]);
    }

    public function test_requires_authentication()
    {
        $response = $this->getJson('/api/v1/automation-rules');
        $response->assertStatus(401);
    }

    public function test_logs_activity_on_create()
    {
        $data = [
            'name' => 'Test Rule',
            'rule_type' => 'scheduled',
            'trigger_type' => 'time',
            'action_type' => 'email',
            'execution_frequency' => 'daily',
            'priority' => 5,
            'is_active' => true
        ];

        $this->actingAs($this->user)
            ->postJson('/api/v1/automation-rules', $data);

        // Verificar que se registró la actividad (si tienes un sistema de logging)
        // $this->assertDatabaseHas('activity_log', [...]);
    }

    public function test_validates_cron_expression()
    {
        $data = [
            'name' => 'Test Rule',
            'rule_type' => 'scheduled',
            'trigger_type' => 'time',
            'action_type' => 'email',
            'execution_frequency' => 'daily',
            'priority' => 5,
            'is_active' => true,
            'schedule_cron' => 'invalid cron'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/automation-rules', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['schedule_cron']);
    }

    public function test_validates_timezone()
    {
        $data = [
            'name' => 'Test Rule',
            'rule_type' => 'scheduled',
            'trigger_type' => 'time',
            'action_type' => 'email',
            'execution_frequency' => 'daily',
            'priority' => 5,
            'is_active' => true,
            'timezone' => 'Invalid/Timezone'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/automation-rules', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['timezone']);
    }

    public function test_validates_retry_configuration()
    {
        $data = [
            'name' => 'Test Rule',
            'rule_type' => 'scheduled',
            'trigger_type' => 'time',
            'action_type' => 'email',
            'execution_frequency' => 'daily',
            'priority' => 5,
            'is_active' => true,
            'retry_on_failure' => true,
            'max_retries' => 0
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/automation-rules', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['max_retries']);
    }
}
