<?php

namespace App\Http\Resources\Api\V1\TaskTemplate;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskTemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = auth()->user();
        $isAdmin = $user && $user->hasRole('admin');
        $isManager = $user && $user->hasRole('manager');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'template_type' => $this->template_type,
            'formatted_template_type' => $this->getFormattedTemplateType(),
            'template_type_badge_class' => $this->getTemplateTypeBadgeClass(),
            'category' => $this->category,
            'subcategory' => $this->subcategory,
            'priority' => $this->priority,
            'formatted_priority' => $this->getFormattedPriority(),
            'priority_badge_class' => $this->getPriorityBadgeClass(),
            'risk_level' => $this->risk_level,
            'formatted_risk_level' => $this->getFormattedRiskLevel(),
            'risk_level_badge_class' => $this->getRiskLevelBadgeClass(),
            'department' => $this->department,
            'estimated_duration_hours' => $this->estimated_duration_hours,
            'formatted_estimated_duration' => $this->getFormattedEstimatedDuration(),
            'estimated_cost' => $this->estimated_cost,
            'formatted_estimated_cost' => $this->getFormattedEstimatedCost(),
            'required_skills' => $this->required_skills,
            'required_tools' => $this->required_tools,
            'required_materials' => $this->required_materials,
            'safety_requirements' => $this->safety_requirements,
            'quality_standards' => $this->quality_standards,
            'compliance_requirements' => $this->compliance_requirements,
            'documentation_requirements' => $this->documentation_requirements,
            'approval_workflow' => $this->approval_workflow,
            'version' => $this->version,
            'is_active' => $this->is_active,
            'is_standard' => $this->is_standard,
            'is_approved' => $this->isApproved(),
            'status_badge_class' => $this->getStatusBadgeClass(),
            'tags' => $this->tags,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'approved_at' => $this->approved_at?->toISOString(),

            // Campos calculados
            'total_maintenance_tasks' => $this->when($isAdmin || $isManager, $this->maintenanceTasks()->count()),
            'total_maintenance_schedules' => $this->when($isAdmin || $isManager, $this->maintenanceSchedules()->count()),
            'last_used_at' => $this->when($isAdmin || $isManager, $this->maintenanceTasks()->latest()->first()?->created_at?->toISOString()),
            'usage_frequency' => $this->when($isAdmin || $isManager, $this->getUsageFrequency()),

            // Relaciones condicionales
            'created_by' => $this->when($this->relationLoaded('createdBy'), [
                'id' => $this->createdBy?->id,
                'name' => $this->createdBy?->name,
                'email' => $this->createdBy?->email,
            ]),

            'approved_by' => $this->when($this->relationLoaded('approvedBy'), [
                'id' => $this->approvedBy?->id,
                'name' => $this->approvedBy?->name,
                'email' => $this->approvedBy?->email,
            ]),

            // Campos sensibles solo para administradores y managers
            'maintenance_tasks' => $this->when($isAdmin || $isManager && $this->relationLoaded('maintenanceTasks'), 
                $this->maintenanceTasks->take(5)->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'status' => $task->status,
                        'created_at' => $task->created_at?->toISOString(),
                    ];
                })
            ),

            'maintenance_schedules' => $this->when($isAdmin || $isManager && $this->relationLoaded('maintenanceSchedules'), 
                $this->maintenanceSchedules->take(5)->map(function ($schedule) {
                    return [
                        'id' => $schedule->id,
                        'title' => $schedule->title,
                        'next_execution' => $schedule->next_execution?->toISOString(),
                        'status' => $schedule->status,
                    ];
                })
            ),

            // Metadatos
            'meta' => [
                'can_edit' => $user ? $user->can('update', $this->resource) : false,
                'can_delete' => $user ? $user->can('delete', $this->resource) : false,
                'can_approve' => $user ? $user->can('approve', $this->resource) : false,
                'can_duplicate' => $user ? $user->can('create', $this->resource) : true,
                'is_creator' => $user ? $user->id === $this->created_by : false,
                'is_approver' => $user ? $user->id === $this->approved_by : false,
            ],
        ];
    }

    /**
     * Get usage frequency for the template
     */
    private function getUsageFrequency(): string
    {
        $count = $this->maintenanceTasks()->count();
        
        if ($count === 0) return 'Nunca usado';
        if ($count <= 5) return 'Poco usado';
        if ($count <= 20) return 'Moderadamente usado';
        if ($count <= 50) return 'Frecuentemente usado';
        
        return 'Muy frecuentemente usado';
    }
}
