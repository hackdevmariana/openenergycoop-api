<?php

namespace App\Http\Resources\Api\V1\MaintenanceTask;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceTaskResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'task_type' => $this->task_type,
            'task_type_label' => $this->getTaskTypeLabel(),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'priority' => $this->priority,
            'priority_label' => $this->getPriorityLabel(),
            'due_date' => $this->due_date?->toISOString(),
            'due_date_formatted' => $this->due_date?->format('d/m/Y'),
            'days_until_due' => $this->getDaysUntilDue(),
            'is_overdue' => $this->isOverdue(),
            'estimated_hours' => $this->estimated_hours,
            'estimated_cost' => $this->estimated_cost,
            'formatted_estimated_cost' => $this->getFormattedEstimatedCost(),
            'actual_start_time' => $this->actual_start_time?->toISOString(),
            'actual_start_time_formatted' => $this->actual_start_time?->format('d/m/Y H:i'),
            'actual_end_time' => $this->actual_end_time?->toISOString(),
            'actual_end_time_formatted' => $this->actual_end_time?->format('d/m/Y H:i'),
            'progress_percentage' => $this->progress_percentage,
            'progress_bar_color' => $this->getProgressBarColor(),
            'completion_notes' => $this->completion_notes,
            'actual_hours' => $this->actual_hours,
            'actual_cost' => $this->actual_cost,
            'formatted_actual_cost' => $this->getFormattedActualCost(),
            'quality_score' => $this->quality_score,
            'quality_badge_color' => $this->getQualityBadgeColor(),
            'materials_used' => $this->materials_used,
            'tools_required' => $this->tools_required,
            'safety_requirements' => $this->safety_requirements,
            'technical_requirements' => $this->technical_requirements,
            'documentation_required' => $this->documentation_required,
            'photos_required' => $this->photos_required,
            'signature_required' => $this->signature_required,
            'approval_required' => $this->approval_required,
            'is_recurring' => $this->is_recurring,
            'recurrence_pattern' => $this->recurrence_pattern,
            'next_occurrence' => $this->next_occurrence?->toISOString(),
            'next_occurrence_formatted' => $this->next_occurrence?->format('d/m/Y'),
            'tags' => $this->tags,
            'notes' => $this->notes,
            'attachments' => $this->attachments,
            
            // Timestamps
            'started_at' => $this->started_at?->toISOString(),
            'started_at_formatted' => $this->started_at?->format('d/m/Y H:i'),
            'completed_at' => $this->completed_at?->toISOString(),
            'completed_at_formatted' => $this->completed_at?->format('d/m/Y H:i'),
            'paused_at' => $this->paused_at?->toISOString(),
            'paused_at_formatted' => $this->paused_at?->format('d/m/Y H:i'),
            'resumed_at' => $this->resumed_at?->toISOString(),
            'resumed_at_formatted' => $this->resumed_at?->format('d/m/Y H:i'),
            'cancelled_at' => $this->cancelled_at?->toISOString(),
            'cancelled_at_formatted' => $this->cancelled_at?->format('d/m/Y H:i'),
            'reassigned_at' => $this->reassigned_at?->toISOString(),
            'reassigned_at_formatted' => $this->reassigned_at?->format('d/m/Y H:i'),
            'progress_updated_at' => $this->progress_updated_at?->toISOString(),
            'progress_updated_at_formatted' => $this->progress_updated_at?->format('d/m/Y H:i'),
            'created_at' => $this->created_at?->toISOString(),
            'created_at_formatted' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at?->toISOString(),
            'updated_at_formatted' => $this->updated_at?->format('d/m/Y H:i'),
            
            // Relationships
            'assigned_to' => $this->whenLoaded('assignedTo', function () {
                return [
                    'id' => $this->assignedTo->id,
                    'name' => $this->assignedTo->name,
                    'email' => $this->assignedTo->email,
                ];
            }),
            'assigned_by' => $this->whenLoaded('assignedBy', function () {
                return [
                    'id' => $this->assignedBy->id,
                    'name' => $this->assignedBy->name,
                    'email' => $this->assignedBy->email,
                ];
            }),
            'equipment' => $this->whenLoaded('equipment', function () {
                return [
                    'id' => $this->equipment->id,
                    'name' => $this->equipment->name,
                    'model' => $this->equipment->model,
                    'serial_number' => $this->equipment->serial_number,
                ];
            }),
            'location' => $this->whenLoaded('location', function () {
                return [
                    'id' => $this->location->id,
                    'name' => $this->location->name,
                    'address' => $this->location->address,
                ];
            }),
            'schedule' => $this->whenLoaded('schedule', function () {
                return [
                    'id' => $this->schedule->id,
                    'name' => $this->schedule->name,
                    'frequency' => $this->schedule->frequency,
                ];
            }),
            'organization' => $this->whenLoaded('organization', function () {
                return [
                    'id' => $this->organization->id,
                    'name' => $this->organization->name,
                    'slug' => $this->organization->slug,
                ];
            }),
            
            // Links
            'links' => [
                'self' => route('api.v1.maintenance-tasks.show', $this->id),
                'edit' => route('api.v1.maintenance-tasks.update', $this->id),
                'delete' => route('api.v1.maintenance-tasks.destroy', $this->id),
            ],
        ];
    }

    /**
     * Get task type label
     */
    private function getTaskTypeLabel(): string
    {
        return match($this->task_type) {
            'inspection' => 'Inspección',
            'repair' => 'Reparación',
            'replacement' => 'Reemplazo',
            'preventive' => 'Preventivo',
            'corrective' => 'Correctivo',
            'emergency' => 'Emergencia',
            'upgrade' => 'Actualización',
            'calibration' => 'Calibración',
            'cleaning' => 'Limpieza',
            'testing' => 'Pruebas',
            'other' => 'Otro',
            default => $this->task_type,
        };
    }

    /**
     * Get status label
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'in_progress' => 'En Progreso',
            'paused' => 'Pausada',
            'completed' => 'Completada',
            'cancelled' => 'Cancelada',
            'on_hold' => 'En Espera',
            default => $this->status,
        };
    }

    /**
     * Get priority label
     */
    private function getPriorityLabel(): string
    {
        return match($this->priority) {
            'low' => 'Baja',
            'medium' => 'Media',
            'high' => 'Alta',
            'urgent' => 'Urgente',
            'critical' => 'Crítica',
            default => $this->priority,
        };
    }

    /**
     * Get days until due
     */
    private function getDaysUntilDue(): ?int
    {
        if (!$this->due_date) {
            return null;
        }
        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Check if task is overdue
     */
    private function isOverdue(): bool
    {
        if (!$this->due_date) {
            return false;
        }
        return now()->isAfter($this->due_date) && !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Get formatted estimated cost
     */
    private function getFormattedEstimatedCost(): ?string
    {
        if (!$this->estimated_cost) {
            return null;
        }
        return '€' . number_format($this->estimated_cost, 2);
    }

    /**
     * Get formatted actual cost
     */
    private function getFormattedActualCost(): ?string
    {
        if (!$this->actual_cost) {
            return null;
        }
        return '€' . number_format($this->actual_cost, 2);
    }

    /**
     * Get progress bar color
     */
    private function getProgressBarColor(): string
    {
        if (!$this->progress_percentage) {
            return 'gray';
        }
        
        if ($this->progress_percentage >= 80) {
            return 'green';
        } elseif ($this->progress_percentage >= 50) {
            return 'yellow';
        } elseif ($this->progress_percentage >= 20) {
            return 'orange';
        } else {
            return 'red';
        }
    }

    /**
     * Get quality badge color
     */
    private function getQualityBadgeColor(): string
    {
        if (!$this->quality_score) {
            return 'gray';
        }
        
        if ($this->quality_score >= 90) {
            return 'green';
        } elseif ($this->quality_score >= 80) {
            return 'blue';
        } elseif ($this->quality_score >= 70) {
            return 'yellow';
        } elseif ($this->quality_score >= 60) {
            return 'orange';
        } else {
            return 'red';
        }
    }
}
