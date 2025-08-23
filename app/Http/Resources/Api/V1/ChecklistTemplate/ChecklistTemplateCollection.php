<?php

namespace App\Http\Resources\Api\V1\ChecklistTemplate;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ChecklistTemplateCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'current_page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'last_page' => $this->lastPage(),
                'from' => $this->firstItem(),
                'to' => $this->lastItem(),
                'links' => [
                    'first' => $this->url(1),
                    'last' => $this->url($this->lastPage()),
                    'prev' => $this->previousPageUrl(),
                    'next' => $this->nextPageUrl(),
                ],
            ],
            'summary' => [
                'total_templates' => $this->total(),
                'active' => $this->collection->where('is_active', true)->count(),
                'inactive' => $this->collection->where('is_active', false)->count(),
                'standard' => $this->collection->where('is_standard', true)->count(),
                'custom' => $this->collection->where('is_standard', false)->count(),
                'approved' => $this->collection->where('is_approved', true)->count(),
                'pending_approval' => $this->collection->where('is_approved', false)->count(),
                'needs_review' => $this->collection->where('needs_review', true)->count(),
                'by_type' => $this->collection->groupBy('template_type')->map->count(),
                'by_category' => $this->collection->groupBy('category')->map->count(),
                'by_priority' => $this->collection->groupBy('priority')->map->count(),
                'by_risk_level' => $this->collection->groupBy('risk_level')->map->count(),
                'by_department' => $this->collection->groupBy('department')->map->count(),
                'high_priority_count' => $this->collection->whereIn('priority', ['high', 'urgent', 'critical'])->count(),
                'high_risk_count' => $this->collection->whereIn('risk_level', ['high', 'extreme'])->count(),
                'training_required_count' => $this->collection->where('training_required', true)->count(),
                'certification_required_count' => $this->collection->where('certification_required', true)->count(),
                'average_completion_time' => $this->collection->whereNotNull('estimated_completion_time')->avg('estimated_completion_time'),
                'average_cost' => $this->collection->whereNotNull('estimated_cost')->avg('estimated_cost'),
                'total_items_average' => $this->collection->avg('total_items'),
                'completion_percentage_average' => $this->collection->avg('completion_percentage'),
            ],
            'with' => [
                'success' => true,
                'message' => 'Plantillas de listas de verificación obtenidas exitosamente',
                'filters_applied' => $this->getAppliedFilters($request),
                'sorting_applied' => $this->getAppliedSorting($request),
            ],
        ];
    }

    /**
     * Obtiene los filtros aplicados en la consulta
     */
    private function getAppliedFilters(Request $request): array
    {
        $filters = [];
        
        if ($request->filled('search')) {
            $filters['search'] = $request->search;
        }
        
        if ($request->filled('template_type')) {
            $filters['template_type'] = $request->template_type;
        }
        
        if ($request->filled('category')) {
            $filters['category'] = $request->category;
        }
        
        if ($request->filled('priority')) {
            $filters['priority'] = $request->priority;
        }
        
        if ($request->filled('risk_level')) {
            $filters['risk_level'] = $request->risk_level;
        }
        
        if ($request->filled('department')) {
            $filters['department'] = $request->department;
        }
        
        if ($request->filled('is_active')) {
            $filters['is_active'] = $request->boolean('is_active');
        }
        
        if ($request->filled('is_standard')) {
            $filters['is_standard'] = $request->boolean('is_standard');
        }
        
        if ($request->filled('is_approved')) {
            $filters['is_approved'] = $request->boolean('is_approved');
        }
        
        return $filters;
    }

    /**
     * Obtiene el ordenamiento aplicado en la consulta
     */
    private function getAppliedSorting(Request $request): array
    {
        $sorting = [];
        
        if ($request->filled('sort')) {
            $sorting['field'] = $request->sort;
            $sorting['direction'] = $request->get('order', 'desc');
        }
        
        return $sorting;
    }
}
