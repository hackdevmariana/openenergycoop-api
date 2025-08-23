<?php

namespace Tests\Unit\Models;

use App\Models\AutomationRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutomationRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes()
    {
        $fillable = [
            'name', 'description', 'rule_type', 'trigger_type', 'trigger_conditions',
            'action_type', 'action_parameters', 'target_entity_id', 'target_entity_type',
            'is_active', 'priority', 'execution_frequency', 'last_executed_at',
            'next_execution_at', 'execution_count', 'max_executions', 'success_count',
            'failure_count', 'last_error_message', 'schedule_cron', 'timezone',
            'retry_on_failure', 'max_retries', 'retry_delay_minutes', 'notification_emails',
            'webhook_url', 'webhook_headers', 'tags', 'notes', 'created_by',
            'approved_by', 'approved_at'
        ];

        $this->assertEquals($fillable, (new AutomationRule())->getFillable());
    }

    public function test_casts()
    {
        $casts = [
            'trigger_conditions' => 'array',
            'action_parameters' => 'array',
            'is_active' => 'boolean',
            'priority' => 'integer',
            'execution_count' => 'integer',
            'max_executions' => 'integer',
            'success_count' => 'integer',
            'failure_count' => 'integer',
            'retry_on_failure' => 'boolean',
            'max_retries' => 'integer',
            'retry_delay_minutes' => 'integer',
            'last_executed_at' => 'datetime',
            'next_execution_at' => 'datetime',
            'approved_at' => 'datetime',
            'notification_emails' => 'array',
            'webhook_headers' => 'array',
            'tags' => 'array',
        ];

        $this->assertEquals($casts, (new AutomationRule())->getCasts());
    }

    public function test_static_enum_methods()
    {
        $this->assertIsArray(AutomationRule::getRuleTypes());
        $this->assertIsArray(AutomationRule::getTriggerTypes());
        $this->assertIsArray(AutomationRule::getActionTypes());
        $this->assertIsArray(AutomationRule::getExecutionFrequencies());
    }

    public function test_relationships()
    {
        $automationRule = AutomationRule::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class, $automationRule->targetEntity());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $automationRule->createdBy());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $automationRule->approvedBy());
    }

    public function test_scopes()
    {
        $automationRule = AutomationRule::factory()->create(['is_active' => true]);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, AutomationRule::active());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, AutomationRule::byType('scheduled'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, AutomationRule::byTriggerType('time'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, AutomationRule::byActionType('email'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, AutomationRule::byPriority(5));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, AutomationRule::highPriority());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, AutomationRule::byExecutionFrequency('daily'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, AutomationRule::scheduled());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, AutomationRule::eventDriven());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, AutomationRule::conditionBased());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, AutomationRule::approved());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, AutomationRule::pendingApproval());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, AutomationRule::readyToExecute());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, AutomationRule::failed());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, AutomationRule::successful());
    }

    public function test_boolean_status_checks()
    {
        $automationRule = AutomationRule::factory()->create(['is_active' => true]);
        
        $this->assertTrue($automationRule->isActive());
        $this->assertFalse($automationRule->isApproved());
    }

    public function test_rule_type_checks()
    {
        $automationRule = AutomationRule::factory()->create(['rule_type' => 'scheduled']);
        
        $this->assertTrue($automationRule->isScheduled());
        $this->assertFalse($automationRule->isEventDriven());
        $this->assertFalse($automationRule->isConditionBased());
        $this->assertFalse($automationRule->isManual());
        $this->assertFalse($automationRule->isWebhook());
    }

    public function test_trigger_type_checks()
    {
        $automationRule = AutomationRule::factory()->create(['trigger_type' => 'time']);
        
        $this->assertTrue($automationRule->isTimeTriggered());
        $this->assertFalse($automationRule->isEventTriggered());
        $this->assertFalse($automationRule->isConditionTriggered());
        $this->assertFalse($automationRule->isThresholdTriggered());
    }

    public function test_priority_checks()
    {
        $automationRule = AutomationRule::factory()->create(['priority' => 9]);
        
        $this->assertTrue($automationRule->isHighPriority());
        $this->assertTrue($automationRule->isUrgent());
    }

    public function test_calculation_methods()
    {
        $automationRule = AutomationRule::factory()->create([
            'success_count' => 8,
            'failure_count' => 2,
            'execution_count' => 10
        ]);

        $this->assertEquals(80.0, $automationRule->getSuccessRate());
        $this->assertEquals(20.0, $automationRule->getFailureRate());
    }

    public function test_formatting_methods()
    {
        $automationRule = AutomationRule::factory()->create([
            'priority' => 9,
            'rule_type' => 'scheduled',
            'trigger_type' => 'time',
            'action_type' => 'email',
            'execution_frequency' => 'daily'
        ]);

        $this->assertEquals('Alto', $automationRule->getFormattedPriority());
        $this->assertEquals('Programado', $automationRule->getFormattedRuleType());
        $this->assertEquals('Tiempo', $automationRule->getFormattedTriggerType());
        $this->assertEquals('Email', $automationRule->getFormattedActionType());
        $this->assertEquals('Diario', $automationRule->getFormattedExecutionFrequency());
    }

    public function test_badge_classes()
    {
        $automationRule = AutomationRule::factory()->create(['is_active' => true]);
        
        $this->assertStringContainsString('bg-green-100', $automationRule->getStatusBadgeClass());
        $this->assertStringContainsString('text-green-800', $automationRule->getStatusBadgeClass());
    }

    public function test_execution_status_methods()
    {
        $automationRule = AutomationRule::factory()->create([
            'is_active' => true,
            'approved_at' => now(),
            'execution_count' => 0
        ]);

        $this->assertEquals('Listo para Ejecutar', $automationRule->getExecutionStatus());
        $this->assertTrue($automationRule->canExecute());
    }

    public function test_retry_methods()
    {
        $automationRule = AutomationRule::factory()->create([
            'retry_on_failure' => true,
            'max_retries' => 3,
            'failure_count' => 1
        ]);

        $this->assertTrue($automationRule->shouldRetry());
        $this->assertNotNull($automationRule->getNextRetryTime());
    }

    public function test_date_methods()
    {
        $automationRule = AutomationRule::factory()->create([
            'last_executed_at' => now()->subHours(2),
            'next_execution_at' => now()->addHours(1)
        ]);

        $this->assertStringContainsString('ejecutado', $automationRule->getFormattedLastExecuted());
        $this->assertStringContainsString('programado', $automationRule->getFormattedNextExecution());
    }

    public function test_ready_to_execute_scope()
    {
        $readyRule = AutomationRule::factory()->create([
            'is_active' => true,
            'approved_at' => now(),
            'next_execution_at' => now()->subHour()
        ]);

        $notReadyRule = AutomationRule::factory()->create([
            'is_active' => false,
            'approved_at' => null
        ]);

        $readyRules = AutomationRule::readyToExecute()->get();
        
        $this->assertTrue($readyRules->contains($readyRule));
        $this->assertFalse($readyRules->contains($notReadyRule));
    }

    public function test_high_priority_scope()
    {
        $highPriorityRule = AutomationRule::factory()->create(['priority' => 9]);
        $lowPriorityRule = AutomationRule::factory()->create(['priority' => 3]);

        $highPriorityRules = AutomationRule::highPriority()->get();
        
        $this->assertTrue($highPriorityRules->contains($highPriorityRule));
        $this->assertFalse($highPriorityRules->contains($lowPriorityRule));
    }

    public function test_by_type_scope()
    {
        $scheduledRule = AutomationRule::factory()->create(['rule_type' => 'scheduled']);
        $eventRule = AutomationRule::factory()->create(['rule_type' => 'event_driven']);

        $scheduledRules = AutomationRule::byType('scheduled')->get();
        
        $this->assertTrue($scheduledRules->contains($scheduledRule));
        $this->assertFalse($scheduledRules->contains($eventRule));
    }

    public function test_by_trigger_type_scope()
    {
        $timeRule = AutomationRule::factory()->create(['trigger_type' => 'time']);
        $eventRule = AutomationRule::factory()->create(['trigger_type' => 'event']);

        $timeRules = AutomationRule::byTriggerType('time')->get();
        
        $this->assertTrue($timeRules->contains($timeRule));
        $this->assertFalse($timeRules->contains($eventRule));
    }

    public function test_by_action_type_scope()
    {
        $emailRule = AutomationRule::factory()->create(['action_type' => 'email']);
        $webhookRule = AutomationRule::factory()->create(['action_type' => 'webhook']);

        $emailRules = AutomationRule::byActionType('email')->get();
        
        $this->assertTrue($emailRules->contains($emailRule));
        $this->assertFalse($emailRules->contains($webhookRule));
    }

    public function test_by_execution_frequency_scope()
    {
        $dailyRule = AutomationRule::factory()->create(['execution_frequency' => 'daily']);
        $weeklyRule = AutomationRule::factory()->create(['execution_frequency' => 'weekly']);

        $dailyRules = AutomationRule::byExecutionFrequency('daily')->get();
        
        $this->assertTrue($dailyRules->contains($dailyRule));
        $this->assertFalse($dailyRules->contains($weeklyRule));
    }

    public function test_approved_scope()
    {
        $approvedRule = AutomationRule::factory()->create(['approved_at' => now()]);
        $pendingRule = AutomationRule::factory()->create(['approved_at' => null]);

        $approvedRules = AutomationRule::approved()->get();
        
        $this->assertTrue($approvedRules->contains($approvedRule));
        $this->assertFalse($approvedRules->contains($pendingRule));
    }

    public function test_pending_approval_scope()
    {
        $approvedRule = AutomationRule::factory()->create(['approved_at' => now()]);
        $pendingRule = AutomationRule::factory()->create(['approved_at' => null]);

        $pendingRules = AutomationRule::pendingApproval()->get();
        
        $this->assertTrue($pendingRules->contains($pendingRule));
        $this->assertFalse($pendingRules->contains($approvedRule));
    }

    public function test_failed_scope()
    {
        $failedRule = AutomationRule::factory()->create(['failure_count' => 5]);
        $successfulRule = AutomationRule::factory()->create(['failure_count' => 0]);

        $failedRules = AutomationRule::failed()->get();
        
        $this->assertTrue($failedRules->contains($failedRule));
        $this->assertFalse($failedRules->contains($successfulRule));
    }

    public function test_successful_scope()
    {
        $successfulRule = AutomationRule::factory()->create(['success_count' => 5]);
        $failedRule = AutomationRule::factory()->create(['success_count' => 0]);

        $successfulRules = AutomationRule::successful()->get();
        
        $this->assertTrue($successfulRules->contains($successfulRule));
        $this->assertFalse($successfulRules->contains($failedRule));
    }
}
