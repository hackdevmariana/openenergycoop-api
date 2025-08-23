<?php

namespace App\Http\Resources\Api\V1\Milestone;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MilestoneCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        $pagination = $this->resource->toArray();
        
        return [
            'data' => MilestoneResource::collection($this->collection),
            'meta' => [
                'current_page' => $pagination['current_page'] ?? null,
                'from' => $pagination['from'] ?? null,
                'last_page' => $pagination['last_page'] ?? null,
                'per_page' => $pagination['per_page'] ?? null,
                'to' => $pagination['to'] ?? null,
                'total' => $pagination['total'] ?? null,
            ],
            'summary' => $this->getSummary(),
            'with' => [
                'success' => true,
                'message' => 'Lista de hitos obtenida exitosamente',
                'applied_filters' => $this->getAppliedFilters($request),
                'applied_sorting' => $this->getAppliedSorting($request),
            ],
        ];
    }

    private function getSummary(): array
    {
        $collection = $this->collection;
        
        return [
            'total_milestones' => $collection->count(),
            'by_type' => $collection->groupBy('milestone_type')->map->count(),
            'by_status' => $collection->groupBy('status')->map->count(),
            'by_priority' => $collection->groupBy('priority')->map->count(),
            'overdue_count' => $collection->filter(fn($m) => $m->isOverdue())->count(),
            'due_soon_count' => $collection->filter(fn($m) => $m->isDueSoon(7))->count(),
            'completed_count' => $collection->filter(fn($m) => $m->isCompleted())->count(),
            'in_progress_count' => $collection->filter(fn($m) => $m->isInProgress())->count(),
            'not_started_count' => $collection->filter(fn($m) => $m->isNotStarted())->count(),
            'high_priority_count' => $collection->filter(fn($m) => $m->isHighPriority())->count(),
            'average_progress' => $collection->avg('progress_percentage'),
            'total_budget' => $collection->sum('budget'),
            'total_actual_cost' => $collection->sum('actual_cost'),
            'budget_utilization' => $this->calculateBudgetUtilization(),
        ];
    }

    private function calculateBudgetUtilization(): float
    {
        $totalBudget = $this->collection->sum('budget');
        $totalActualCost = $this->collection->sum('actual_cost');
        
        if ($totalBudget > 0) {
            return round(($totalActualCost / $totalBudget) * 100, 2);
        }
        
        return 0;
    }

    private function getAppliedFilters(Request $request): array
    {
        $filters = [];
        
        if ($request->filled('search')) {
            $filters['search'] = $request->search;
        }
        
        if ($request->filled('milestone_type')) {
            $filters['milestone_type'] = $request->milestone_type;
        }
        
        if ($request->filled('status')) {
            $filters['status'] = $request->status;
        }
        
        if ($request->filled('priority')) {
            $filters['priority'] = $request->priority;
        }
        
        if ($request->filled('assigned_to')) {
            $filters['assigned_to'] = $request->assigned_to;
        }
        
        return $filters;
    }

    private function getAppliedSorting(Request $request): array
    {
        return [
            'sort_by' => $request->get('sort', 'target_date'),
            'sort_order' => $request->get('order', 'asc'),
        ];
    }
}
