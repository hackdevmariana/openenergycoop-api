<?php

namespace App\Http\Resources\Api\V1\EnergyTransfer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnergyTransferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transfer_number' => $this->transfer_number,
            'name' => $this->name,
            'description' => $this->description,
            'transfer_type' => $this->transfer_type,
            'transfer_type_label' => $this->getFormattedTransferType(),
            'status' => $this->status,
            'status_label' => $this->getFormattedStatus(),
            'priority' => $this->priority,
            'priority_label' => $this->getFormattedPriority(),
            'source_id' => $this->source_id,
            'source_type' => $this->source_type,
            'source' => $this->whenLoaded('source'),
            'destination_id' => $this->destination_id,
            'destination_type' => $this->destination_type,
            'destination' => $this->whenLoaded('destination'),
            'source_meter_id' => $this->source_meter_id,
            'source_meter' => $this->whenLoaded('sourceMeter'),
            'destination_meter_id' => $this->destination_meter_id,
            'destination_meter' => $this->whenLoaded('destinationMeter'),
            'transfer_amount_kwh' => $this->transfer_amount_kwh,
            'transfer_amount_kwh_formatted' => $this->getFormattedTransferAmountKwh(),
            'transfer_amount_mwh' => $this->transfer_amount_mwh,
            'transfer_amount_mwh_formatted' => $this->getFormattedTransferAmountMwh(),
            'transfer_rate_kw' => $this->transfer_rate_kw,
            'transfer_rate_kw_formatted' => $this->getFormattedTransferRateKw(),
            'transfer_rate_mw' => $this->transfer_rate_mw,
            'transfer_rate_mw_formatted' => $this->getFormattedTransferRateMw(),
            'transfer_unit' => $this->transfer_unit,
            'scheduled_start_time' => $this->scheduled_start_time?->toISOString(),
            'scheduled_start_time_formatted' => $this->getFormattedScheduledStartTime(),
            'scheduled_end_time' => $this->scheduled_end_time?->toISOString(),
            'scheduled_end_time_formatted' => $this->getFormattedScheduledEndTime(),
            'actual_start_time' => $this->actual_start_time?->toISOString(),
            'actual_start_time_formatted' => $this->getFormattedActualStartTime(),
            'actual_end_time' => $this->actual_end_time?->toISOString(),
            'actual_end_time_formatted' => $this->getFormattedActualEndTime(),
            'completion_time' => $this->completion_time?->toISOString(),
            'completion_time_formatted' => $this->getFormattedCompletionTime(),
            'duration_hours' => $this->duration_hours,
            'duration_hours_formatted' => $this->getFormattedDurationHours(),
            'efficiency_percentage' => $this->efficiency_percentage,
            'efficiency_percentage_formatted' => $this->getFormattedEfficiencyPercentage(),
            'loss_percentage' => $this->loss_percentage,
            'loss_percentage_formatted' => $this->getFormattedLossPercentage(),
            'loss_amount_kwh' => $this->loss_amount_kwh,
            'loss_amount_kwh_formatted' => $this->getFormattedLossAmountKwh(),
            'net_transfer_amount_kwh' => $this->net_transfer_amount_kwh,
            'net_transfer_amount_kwh_formatted' => $this->getFormattedNetTransferAmountKwh(),
            'net_transfer_amount_mwh' => $this->net_transfer_amount_mwh,
            'net_transfer_amount_mwh_formatted' => $this->getFormattedNetTransferAmountMwh(),
            'cost_per_kwh' => $this->cost_per_kwh,
            'cost_per_kwh_formatted' => $this->getFormattedCostPerKwh(),
            'total_cost' => $this->total_cost,
            'total_cost_formatted' => $this->getFormattedTotalCost(),
            'currency' => $this->currency,
            'exchange_rate' => $this->exchange_rate,
            'exchange_rate_formatted' => $this->getFormattedExchangeRate(),
            'transfer_method' => $this->transfer_method,
            'transfer_medium' => $this->transfer_medium,
            'transfer_protocol' => $this->transfer_protocol,
            'is_automated' => $this->is_automated,
            'requires_approval' => $this->requires_approval,
            'is_approved' => $this->is_approved,
            'is_verified' => $this->is_verified,
            'transfer_conditions' => $this->transfer_conditions,
            'safety_requirements' => $this->safety_requirements,
            'quality_standards' => $this->quality_standards,
            'transfer_parameters' => $this->transfer_parameters,
            'monitoring_data' => $this->monitoring_data,
            'alarm_settings' => $this->alarm_settings,
            'event_logs' => $this->event_logs,
            'performance_metrics' => $this->performance_metrics,
            'tags' => $this->tags,
            'scheduled_by' => $this->scheduled_by,
            'scheduled_by_user' => $this->whenLoaded('scheduledBy'),
            'initiated_by' => $this->initiated_by,
            'initiated_by_user' => $this->whenLoaded('initiatedBy'),
            'approved_by' => $this->approved_by,
            'approved_by_user' => $this->whenLoaded('approvedBy'),
            'verified_by' => $this->verified_by,
            'verified_by_user' => $this->whenLoaded('verifiedBy'),
            'completed_by' => $this->completed_by,
            'completed_by_user' => $this->whenLoaded('completedBy'),
            'created_by' => $this->created_by,
            'created_by_user' => $this->whenLoaded('createdBy'),
            'notes' => $this->notes,
            
            // Campos calculados
            'transfer_duration' => $this->getTransferDuration(),
            'transfer_duration_formatted' => $this->getFormattedTransferDuration(),
            'actual_duration' => $this->getActualDuration(),
            'actual_duration_formatted' => $this->getFormattedActualDuration(),
            'time_to_start' => $this->getTimeToStart(),
            'time_to_start_formatted' => $this->getFormattedTimeToStart(),
            'time_to_end' => $this->getTimeToEnd(),
            'time_to_end_formatted' => $this->getFormattedTimeToEnd(),
            'is_starting_soon' => $this->isStartingSoon(),
            'is_ending_soon' => $this->isEndingSoon(),
            
            // Campos de estado
            'is_pending' => $this->isPending(),
            'is_scheduled' => $this->isScheduled(),
            'is_in_progress' => $this->isInProgress(),
            'is_completed' => $this->isCompleted(),
            'is_cancelled' => $this->isCancelled(),
            'is_failed' => $this->isFailed(),
            'is_on_hold' => $this->isOnHold(),
            'is_reversed' => $this->isReversed(),
            'is_scheduled_for' => $this->isScheduledFor(),
            'is_overdue' => $this->isOverdue(),
            
            // Campos de prioridad
            'is_high_priority' => $this->isHighPriority(),
            'is_low_priority' => $this->isLowPriority(),
            'is_normal_priority' => $this->isNormalPriority(),
            
            // Campos de tipo
            'is_generation' => $this->isGeneration(),
            'is_consumption' => $this->isConsumption(),
            'is_storage' => $this->isStorage(),
            'is_grid_import' => $this->isGridImport(),
            'is_grid_export' => $this->isGridExport(),
            'is_peer_to_peer' => $this->isPeerToPeer(),
            'is_virtual' => $this->isVirtual(),
            'is_physical' => $this->isPhysical(),
            'is_contractual' => $this->isContractual(),
            
            // Campos de operación
            'is_automated_transfer' => $this->isAutomated(),
            'is_manual_transfer' => $this->isManual(),
            'requires_approval_transfer' => $this->requiresApproval(),
            'is_approved_transfer' => $this->isApproved(),
            'is_verified_transfer' => $this->isVerified(),
            
            // Campos de validación
            'can_be_cancelled' => $this->canBeCancelled(),
            'can_be_started' => $this->canBeStarted(),
            'can_be_completed' => $this->canBeCompleted(),
            
            // Campos de UI
            'status_badge_class' => $this->getStatusBadgeClass(),
            'transfer_type_badge_class' => $this->getTransferTypeBadgeClass(),
            'priority_badge_class' => $this->getPriorityBadgeClass(),
            'automated_badge_class' => $this->getAutomatedBadgeClass(),
            'approval_badge_class' => $this->getApprovalBadgeClass(),
            'verification_badge_class' => $this->getVerificationBadgeClass(),
            'scheduled_badge_class' => $this->getScheduledBadgeClass(),
            'efficiency_badge_class' => $this->getEfficiencyBadgeClass(),
            'loss_badge_class' => $this->getLossBadgeClass(),
            
            // Permisos
            'can_edit' => $this->canEdit(),
            'can_delete' => $this->canDelete(),
            'can_duplicate' => $this->canDuplicate(),
            'can_approve' => $this->canApprove(),
            'can_verify' => $this->canVerify(),
            'can_start' => $this->canStart(),
            'can_complete' => $this->canComplete(),
            'can_cancel' => $this->canCancel(),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
    
    /**
     * Verificar si se puede editar
     */
    protected function canEdit(): bool
    {
        return !$this->isCompleted() && !$this->isCancelled() && !$this->isFailed() && !$this->isReversed();
    }
    
    /**
     * Verificar si se puede eliminar
     */
    protected function canDelete(): bool
    {
        return $this->isPending() || $this->isCancelled() || $this->isFailed();
    }
    
    /**
     * Verificar si se puede duplicar
     */
    protected function canDuplicate(): bool
    {
        return true; // Siempre se puede duplicar
    }
    
    /**
     * Verificar si se puede aprobar
     */
    protected function canApprove(): bool
    {
        return $this->requiresApproval() && !$this->isApproved() && !$this->isCancelled() && !$this->isFailed();
    }
    
    /**
     * Verificar si se puede verificar
     */
    protected function canVerify(): bool
    {
        return !$this->isVerified() && !$this->isCancelled() && !$this->isFailed();
    }
    
    /**
     * Verificar si se puede iniciar
     */
    protected function canStart(): bool
    {
        return $this->canBeStarted() && !$this->isCancelled() && !$this->isFailed();
    }
    
    /**
     * Verificar si se puede completar
     */
    protected function canComplete(): bool
    {
        return $this->canBeCompleted() && !$this->isCancelled() && !$this->isFailed();
    }
    
    /**
     * Verificar si se puede cancelar
     */
    protected function canCancel(): bool
    {
        return $this->canBeCancelled() && !$this->isCancelled() && !$this->isFailed();
    }
}
