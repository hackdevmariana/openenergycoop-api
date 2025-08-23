<?php

namespace App\Http\Resources\Api\V1\MaintenanceSchedule;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceScheduleResource extends JsonResource
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
            'schedule_type' => $this->schedule_type,
            'schedule_type_label' => $this->getFormattedScheduleType(),
            'frequency_type' => $this->frequency_type,
            'frequency_type_label' => $this->getFormattedFrequencyType(),
            'frequency_value' => $this->frequency_value,
            'priority' => $this->priority,
            'priority_label' => $this->getFormattedPriority(),
            'department' => $this->department,
            'category' => $this->category,
            'equipment_id' => $this->equipment_id,
            'location_id' => $this->location_id,
            'vendor_id' => $this->vendor_id,
            'task_template_id' => $this->task_template_id,
            'checklist_template_id' => $this->checklist_template_id,
            'estimated_duration_hours' => $this->estimated_duration_hours,
            'estimated_cost' => $this->estimated_cost,
            'is_active' => $this->is_active,
            'auto_generate_tasks' => $this->auto_generate_tasks,
            'send_notifications' => $this->send_notifications,
            'notification_emails' => $this->notification_emails,
            'start_date' => $this->start_date?->toISOString(),
            'end_date' => $this->end_date?->toISOString(),
            'next_maintenance_date' => $this->next_maintenance_date?->toISOString(),
            'last_maintenance_date' => $this->last_maintenance_date?->toISOString(),
            'maintenance_window_start' => $this->maintenance_window_start,
            'maintenance_window_end' => $this->maintenance_window_end,
            'weather_dependent' => $this->weather_dependent,
            'weather_conditions' => $this->weather_conditions,
            'required_skills' => $this->required_skills,
            'required_tools' => $this->required_tools,
            'required_materials' => $this->required_materials,
            'safety_requirements' => $this->safety_requirements,
            'quality_standards' => $this->quality_standards,
            'compliance_requirements' => $this->compliance_requirements,
            'tags' => $this->tags,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),

            // Campos calculados
            'days_until_next_maintenance' => $this->getDaysUntilNextMaintenance(),
            'is_overdue' => $this->isOverdue(),
            'is_due_soon' => $this->isDueSoon(),
            'maintenance_window_duration_hours' => $this->getMaintenanceWindowDurationHours(),
            'total_estimated_cost' => $this->getTotalEstimatedCost(),
            'completion_percentage' => $this->getCompletionPercentage(),

            // Campos formateados
            'formatted_estimated_duration' => $this->getFormattedEstimatedDuration(),
            'formatted_estimated_cost' => $this->getFormattedEstimatedCost(),
            'formatted_next_maintenance' => $this->getFormattedNextMaintenance(),
            'formatted_last_maintenance' => $this->getFormattedLastMaintenance(),
            'formatted_maintenance_window' => $this->getFormattedMaintenanceWindow(),

            // Flags de estado
            'is_preventive' => $this->isPreventive(),
            'is_predictive' => $this->isPredictive(),
            'is_condition_based' => $this->isConditionBased(),
            'is_corrective' => $this->isCorrective(),
            'is_emergency' => $this->isEmergency(),
            'is_planned' => $this->isPlanned(),
            'is_unplanned' => $this->isUnplanned(),
            'is_high_priority' => $this->isHighPriority(),
            'is_urgent' => $this->isUrgent(),
            'is_approved' => $this->isApproved(),
            'is_pending_approval' => $this->isPendingApproval(),

            // Clases de badges para UI
            'status_badge_class' => $this->getStatusBadgeClass(),
            'priority_badge_class' => $this->getPriorityBadgeClass(),
            'schedule_type_badge_class' => $this->getScheduleTypeBadgeClass(),
            'frequency_type_badge_class' => $this->getFrequencyTypeBadgeClass(),

            // Flags de permisos
            'can_edit' => $this->canEdit(),
            'can_delete' => $this->canDelete(),
            'can_duplicate' => $this->canDuplicate(),
            'can_approve' => $this->canApprove(),
            'can_activate' => $this->canActivate(),
            'can_generate_tasks' => $this->canGenerateTasks(),

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
            'vendor' => $this->whenLoaded('vendor', function () {
                return [
                    'id' => $this->vendor->id,
                    'name' => $this->vendor->name,
                    'contact_person' => $this->vendor->contact_person,
                ];
            }),
            'task_template' => $this->whenLoaded('taskTemplate', function () {
                return [
                    'id' => $this->taskTemplate->id,
                    'name' => $this->taskTemplate->name,
                    'description' => $this->taskTemplate->description,
                ];
            }),
            'checklist_template' => $this->whenLoaded('checklistTemplate', function () {
                return [
                    'id' => $this->checklistTemplate->id,
                    'name' => $this->checklistTemplate->name,
                    'description' => $this->checklistTemplate->description,
                ];
            }),
            'equipment' => $this->when($this->equipment_id, function () {
                return [
                    'id' => $this->equipment_id,
                    'name' => 'Equipment Name', // Placeholder
                ];
            }),
            'location' => $this->when($this->location_id, function () {
                return [
                    'id' => $this->location_id,
                    'name' => 'Location Name', // Placeholder
                ];
            }),
        ];
    }

    /**
     * Verificar si se puede editar el programa.
     */
    private function canEdit(): bool
    {
        // Solo se puede editar si no está aprobado o si el usuario tiene permisos especiales
        return !$this->isApproved() || auth()->user()?->hasPermissionTo('maintenance-schedules.edit') ?? false;
    }

    /**
     * Verificar si se puede eliminar el programa.
     */
    private function canDelete(): bool
    {
        // Solo se puede eliminar si no está activo o si el usuario tiene permisos especiales
        return !$this->isActive() || auth()->user()?->hasPermissionTo('maintenance-schedules.delete') ?? false;
    }

    /**
     * Verificar si se puede duplicar el programa.
     */
    private function canDuplicate(): bool
    {
        return auth()->user()?->hasPermissionTo('maintenance-schedules.create') ?? true;
    }

    /**
     * Verificar si se puede aprobar el programa.
     */
    private function canApprove(): bool
    {
        return !$this->isApproved() && 
               (auth()->user()?->hasPermissionTo('maintenance-schedules.approve') ?? false);
    }

    /**
     * Verificar si se puede activar/desactivar el programa.
     */
    private function canActivate(): bool
    {
        return $this->isApproved() && 
               (auth()->user()?->hasPermissionTo('maintenance-schedules.activate') ?? false);
    }

    /**
     * Verificar si se pueden generar tareas automáticamente.
     */
    private function canGenerateTasks(): bool
    {
        return $this->auto_generate_tasks && 
               (auth()->user()?->hasPermissionTo('maintenance-schedules.generate-tasks') ?? false);
    }
}
