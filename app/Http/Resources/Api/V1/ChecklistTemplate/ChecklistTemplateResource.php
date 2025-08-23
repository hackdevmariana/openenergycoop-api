<?php

namespace App\Http\Resources\Api\V1\ChecklistTemplate;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChecklistTemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isAdmin = $user && $user->hasRole('admin');
        $isManager = $user && ($user->hasRole('manager') || $user->hasRole('admin'));

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'template_type' => $this->template_type,
            'category' => $this->category,
            'subcategory' => $this->subcategory,
            'checklist_items' => $this->checklist_items,
            'required_items' => $this->required_items,
            'optional_items' => $this->optional_items,
            'conditional_items' => $this->conditional_items,
            'item_order' => $this->item_order,
            'scoring_system' => $this->scoring_system,
            'pass_threshold' => $this->pass_threshold,
            'fail_threshold' => $this->fail_threshold,
            'is_active' => $this->is_active,
            'is_standard' => $this->is_standard,
            'version' => $this->version,
            'tags' => $this->tags,
            'notes' => $this->notes,
            'department' => $this->department,
            'priority' => $this->priority,
            'risk_level' => $this->risk_level,
            'compliance_requirements' => $this->compliance_requirements,
            'quality_standards' => $this->quality_standards,
            'safety_requirements' => $this->safety_requirements,
            'training_required' => $this->training_required,
            'certification_required' => $this->certification_required,
            'documentation_required' => $this->documentation_required,
            'environmental_considerations' => $this->environmental_considerations,
            'budget_code' => $this->budget_code,
            'cost_center' => $this->cost_center,
            'project_code' => $this->project_code,
            'estimated_completion_time' => $this->estimated_completion_time,
            'estimated_cost' => $this->estimated_cost,
            'required_skills' => $this->required_skills,
            'required_tools' => $this->required_tools,
            'required_parts' => $this->required_parts,
            'work_instructions' => $this->work_instructions,
            'reference_documents' => $this->reference_documents,
            'best_practices' => $this->best_practices,
            'lessons_learned' => $this->lessons_learned,
            'continuous_improvement' => $this->continuous_improvement,
            'audit_frequency' => $this->audit_frequency,
            'last_review_date' => $this->last_review_date,
            'next_review_date' => $this->next_review_date,
            'approval_workflow' => $this->approval_workflow,
            'escalation_procedures' => $this->escalation_procedures,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Campos formateados
            'formatted_template_type' => $this->getFormattedTemplateType(),
            'formatted_priority' => $this->getFormattedPriority(),
            'formatted_risk_level' => $this->getFormattedRiskLevel(),
            'formatted_estimated_completion_time' => $this->getFormattedEstimatedCompletionTime(),
            'formatted_estimated_cost' => $this->getFormattedEstimatedCost(),
            'formatted_pass_threshold' => $this->getFormattedPassThreshold(),
            'formatted_fail_threshold' => $this->getFormattedFailThreshold(),
            'formatted_last_review_date' => $this->getFormattedLastReviewDate(),
            'formatted_next_review_date' => $this->getFormattedNextReviewDate(),
            'formatted_completion_percentage' => $this->getFormattedCompletionPercentage(),

            // Campos calculados
            'total_items' => $this->getTotalItems(),
            'required_items_count' => $this->getRequiredItemsCount(),
            'optional_items_count' => $this->getOptionalItemsCount(),
            'conditional_items_count' => $this->getConditionalItemsCount(),
            'completion_percentage' => $this->getCompletionPercentage(),
            'days_until_review' => $this->getDaysUntilReview(),
            'needs_review' => $this->needsReview(),

            // Badges
            'status_badge_class' => $this->getStatusBadgeClass(),
            'template_type_badge_class' => $this->getTemplateTypeBadgeClass(),
            'priority_badge_class' => $this->getPriorityBadgeClass(),
            'risk_level_badge_class' => $this->getRiskLevelBadgeClass(),
            'review_status_badge_class' => $this->getReviewStatusBadgeClass(),

            // Relaciones condicionales
            'created_by' => $this->when($isManager, function () {
                return [
                    'id' => $this->createdBy->id ?? null,
                    'name' => $this->createdBy->name ?? null,
                    'email' => $this->createdBy->email ?? null,
                ];
            }),

            'approved_by' => $this->when($isManager, function () {
                return $this->approvedBy ? [
                    'id' => $this->approvedBy->id,
                    'name' => $this->approvedBy->name,
                    'email' => $this->approvedBy->email,
                ] : null;
            }),

            'reviewed_by' => $this->when($isManager, function () {
                return $this->reviewedBy ? [
                    'id' => $this->reviewedBy->id,
                    'name' => $this->reviewedBy->name,
                    'email' => $this->reviewedBy->email,
                ] : null;
            }),

            // Campos sensibles solo para administradores
            'approval_workflow' => $this->when($isAdmin, $this->approval_workflow),
            'escalation_procedures' => $this->when($isAdmin, $this->escalation_procedures),
            'budget_code' => $this->when($isAdmin, $this->budget_code),
            'cost_center' => $this->when($isAdmin, $this->cost_center),
            'project_code' => $this->when($isAdmin, $this->project_code),

            // Métodos de verificación
            'is_active' => $this->isActive(),
            'is_standard' => $this->isStandard(),
            'is_approved' => $this->isApproved(),

            // Verificaciones de tipo
            'is_maintenance' => $this->isMaintenance(),
            'is_inspection' => $this->isInspection(),
            'is_safety' => $this->isSafety(),
            'is_quality' => $this->isQuality(),
            'is_compliance' => $this->isCompliance(),
            'is_audit' => $this->isAudit(),
            'is_training' => $this->isTraining(),
            'is_operations' => $this->isOperations(),
            'is_procedure' => $this->isProcedure(),
            'is_workflow' => $this->isWorkflow(),

            // Verificaciones de prioridad
            'is_low_priority' => $this->isLowPriority(),
            'is_medium_priority' => $this->isMediumPriority(),
            'is_high_priority' => $this->isHighPriority(),
            'is_urgent' => $this->isUrgent(),
            'is_critical' => $this->isCritical(),

            // Verificaciones de riesgo
            'is_low_risk' => $this->isLowRisk(),
            'is_medium_risk' => $this->isMediumRisk(),
            'is_high_risk' => $this->isHighRisk(),
            'is_extreme_risk' => $this->isExtremeRisk(),

            // Estadísticas de uso
            'total_maintenance_tasks' => $this->when($isManager, function () {
                return $this->maintenanceTasks()->count();
            }),

            'total_maintenance_schedules' => $this->when($isManager, function () {
                return $this->maintenanceSchedules()->count();
            }),

            'last_used_at' => $this->when($isManager, function () {
                $lastTask = $this->maintenanceTasks()->latest()->first();
                $lastSchedule = $this->maintenanceSchedules()->latest()->first();
                
                if ($lastTask && $lastSchedule) {
                    return $lastTask->created_at->gt($lastSchedule->created_at) 
                        ? $lastTask->created_at 
                        : $lastSchedule->created_at;
                }
                
                return $lastTask?->created_at ?? $lastSchedule?->created_at;
            }),

            'usage_frequency' => $this->when($isManager, function () {
                $totalTasks = $this->maintenanceTasks()->count();
                $totalSchedules = $this->maintenanceSchedules()->count();
                $totalUsage = $totalTasks + $totalSchedules;
                
                if ($totalUsage === 0) {
                    return 'Nunca usado';
                }
                
                if ($totalUsage <= 5) {
                    return 'Bajo uso';
                }
                
                if ($totalUsage <= 20) {
                    return 'Uso moderado';
                }
                
                if ($totalUsage <= 50) {
                    return 'Alto uso';
                }
                
                return 'Muy alto uso';
            }),
        ];
    }
}
