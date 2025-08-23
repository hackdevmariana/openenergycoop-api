<?php

namespace App\Http\Resources\Api\V1\Milestone;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MilestoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isManager = $user && $user->hasRole(['manager', 'admin']);
        $isAdmin = $user && $user->hasRole('admin');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'milestone_type' => $this->milestone_type,
            'milestone_type_formatted' => $this->getMilestoneTypeFormattedAttribute(),
            'milestone_type_badge' => $this->getMilestoneTypeBadgeClass(),
            'status' => $this->status,
            'status_formatted' => $this->getStatusFormattedAttribute(),
            'status_badge' => $this->getStatusBadgeClass(),
            'priority' => $this->priority,
            'priority_formatted' => $this->getPriorityFormattedAttribute(),
            'priority_badge' => $this->getPriorityBadgeClass(),
            'target_date' => $this->target_date?->toISOString(),
            'target_date_formatted' => $this->getTargetDateFormattedAttribute(),
            'target_date_badge' => $this->getTargetDateBadgeClass(),
            'start_date' => $this->start_date?->toISOString(),
            'completion_date' => $this->completion_date?->toISOString(),
            'progress_percentage' => $this->progress_percentage,
            'progress_badge' => $this->getProgressBadgeClass(),
            'budget' => $this->budget,
            'actual_cost' => $this->actual_cost,
            'remaining_budget' => $this->getRemainingBudgetAttribute(),
            'cost_variance' => $this->getCostVarianceAttribute(),
            'risk_level' => $this->risk_level,
            'tags' => $this->tags,
            'dependencies' => $this->dependencies,
            'notes' => $this->notes,
            
            // Campos calculados
            'days_until_target' => $this->getDaysUntilTargetAttribute(),
            'days_overdue' => $this->getDaysOverdueAttribute(),
            'is_overdue' => $this->isOverdue(),
            'is_due_soon' => $this->isDueSoon(7),
            'is_completed' => $this->isCompleted(),
            'is_in_progress' => $this->isInProgress(),
            'is_not_started' => $this->isNotStarted(),
            'is_cancelled' => $this->isCancelled(),
            'is_high_priority' => $this->isHighPriority(),
            'can_start' => $this->canStart(),
            'can_complete' => $this->canComplete(),
            
            // Relaciones condicionales
            'parent_milestone' => $this->when($this->parentMilestone, function () {
                return [
                    'id' => $this->parentMilestone->id,
                    'title' => $this->parentMilestone->title,
                    'status' => $this->parentMilestone->status,
                    'progress_percentage' => $this->parentMilestone->progress_percentage,
                ];
            }),
            'sub_milestones' => $this->when($this->subMilestones->isNotEmpty(), function () {
                return $this->subMilestones->map(function ($subMilestone) {
                    return [
                        'id' => $subMilestone->id,
                        'title' => $subMilestone->title,
                        'status' => $subMilestone->status,
                        'progress_percentage' => $subMilestone->progress_percentage,
                        'target_date' => $subMilestone->target_date?->toISOString(),
                    ];
                });
            }),
            'assigned_to' => $this->when($this->assignedTo, function () {
                return [
                    'id' => $this->assignedTo->id,
                    'name' => $this->assignedTo->name,
                    'email' => $this->assignedTo->email,
                ];
            }),
            'created_by' => $this->when($isManager && $this->createdBy, function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email,
                ];
            }),
            
            // Campos sensibles (solo para administradores)
            'cost_analysis' => $this->when($isAdmin, function () {
                return [
                    'budget_utilization' => $this->getBudgetUtilizationAttribute(),
                    'cost_performance_index' => $this->getCostPerformanceIndexAttribute(),
                    'schedule_performance_index' => $this->getSchedulePerformanceIndexAttribute(),
                ];
            }),
            
            'timestamps' => [
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
            ],
        ];
    }
}
