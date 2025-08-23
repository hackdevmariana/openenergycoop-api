<?php

namespace App\Http\Resources\Api\V1\AutomationRule;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AutomationRuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'rule_type' => $this->rule_type,
            'rule_type_label' => $this->getFormattedRuleType(),
            'trigger_type' => $this->trigger_type,
            'trigger_type_label' => $this->getFormattedTriggerType(),
            'trigger_conditions' => $this->trigger_conditions,
            'action_type' => $this->action_type,
            'action_type_label' => $this->getFormattedActionType(),
            'action_parameters' => $this->action_parameters,
            'target_entity_id' => $this->target_entity_id,
            'target_entity_type' => $this->target_entity_type,
            'is_active' => $this->is_active,
            'priority' => $this->priority,
            'priority_label' => $this->getFormattedPriority(),
            'execution_frequency' => $this->execution_frequency,
            'execution_frequency_label' => $this->getFormattedExecutionFrequency(),
            'last_executed_at' => $this->last_executed_at?->toISOString(),
            'next_execution_at' => $this->next_execution_at?->toISOString(),
            'execution_count' => $this->execution_count,
            'max_executions' => $this->max_executions,
            'success_count' => $this->success_count,
            'failure_count' => $this->failure_count,
            'last_error_message' => $this->last_error_message,
            'schedule_cron' => $this->schedule_cron,
            'timezone' => $this->timezone,
            'retry_on_failure' => $this->retry_on_failure,
            'max_retries' => $this->max_retries,
            'retry_delay_minutes' => $this->retry_delay_minutes,
            'notification_emails' => $this->notification_emails,
            'webhook_url' => $this->webhook_url,
            'webhook_headers' => $this->webhook_headers,
            'tags' => $this->tags,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),

            // Campos calculados
            'success_rate' => $this->getSuccessRate(),
            'failure_rate' => $this->getFailureRate(),
            'execution_status' => $this->getExecutionStatus(),
            'can_execute' => $this->canExecute(),
            'should_retry' => $this->shouldRetry(),
            'next_retry_time' => $this->getNextRetryTime(),

            // Campos formateados
            'formatted_last_executed' => $this->getFormattedLastExecuted(),
            'formatted_next_execution' => $this->getFormattedNextExecution(),
            'formatted_success_rate' => $this->getFormattedSuccessRate(),
            'formatted_failure_rate' => $this->getFormattedFailureRate(),

            // Flags de estado
            'is_scheduled' => $this->isScheduled(),
            'is_event_driven' => $this->isEventDriven(),
            'is_condition_based' => $this->isConditionBased(),
            'is_manual' => $this->isManual(),
            'is_webhook' => $this->isWebhook(),
            'is_time_triggered' => $this->isTimeTriggered(),
            'is_event_triggered' => $this->isEventTriggered(),
            'is_condition_triggered' => $this->isConditionTriggered(),
            'is_threshold_triggered' => $this->isThresholdTriggered(),
            'is_high_priority' => $this->isHighPriority(),
            'is_urgent' => $this->isUrgent(),
            'is_approved' => $this->isApproved(),

            // Clases de badges para UI
            'status_badge_class' => $this->getStatusBadgeClass(),
            'priority_badge_class' => $this->getPriorityBadgeClass(),
            'rule_type_badge_class' => $this->getRuleTypeBadgeClass(),
            'trigger_type_badge_class' => $this->getTriggerTypeBadgeClass(),
            'action_type_badge_class' => $this->getActionTypeBadgeClass(),
            'execution_status_badge_class' => $this->getExecutionStatusBadgeClass(),

            // Flags de permisos
            'can_edit' => $this->canEdit(),
            'can_delete' => $this->canDelete(),
            'can_duplicate' => $this->canDuplicate(),
            'can_approve' => $this->canApprove(),
            'can_activate' => $this->canActivate(),
            'can_execute' => $this->canExecute(),

            // Relaciones (cargadas condicionalmente)
            'created_by_user' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email,
                ];
            }),
            'approved_by_user' => $this->whenLoaded('approvedBy', function () {
                return [
                    'id' => $this->approvedBy->id,
                    'name' => $this->approvedBy->name,
                    'email' => $this->approvedBy->email,
                ];
            }),
            'target_entity' => $this->when($this->target_entity_id && $this->target_entity_type, function () {
                return [
                    'id' => $this->target_entity_id,
                    'type' => $this->target_entity_type,
                ];
            }),
        ];
    }

    /**
     * Verificar si se puede editar la regla.
     */
    private function canEdit(): bool
    {
        // Solo se puede editar si no está aprobada o si el usuario tiene permisos especiales
        return !$this->isApproved() || auth()->user()?->hasPermissionTo('automation-rules.edit') ?? false;
    }

    /**
     * Verificar si se puede eliminar la regla.
     */
    private function canDelete(): bool
    {
        // Solo se puede eliminar si no está activa o si el usuario tiene permisos especiales
        return !$this->isActive() || auth()->user()?->hasPermissionTo('automation-rules.delete') ?? false;
    }

    /**
     * Verificar si se puede duplicar la regla.
     */
    private function canDuplicate(): bool
    {
        return auth()->user()?->hasPermissionTo('automation-rules.create') ?? true;
    }

    /**
     * Verificar si se puede aprobar la regla.
     */
    private function canApprove(): bool
    {
        return !$this->isApproved() && 
               (auth()->user()?->hasPermissionTo('automation-rules.approve') ?? false);
    }

    /**
     * Verificar si se puede activar/desactivar la regla.
     */
    private function canActivate(): bool
    {
        return $this->isApproved() && 
               (auth()->user()?->hasPermissionTo('automation-rules.activate') ?? false);
    }
}
