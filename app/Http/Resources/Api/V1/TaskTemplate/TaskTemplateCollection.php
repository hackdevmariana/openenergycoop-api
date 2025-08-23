<?php

namespace App\Http\Resources\Api\V1\TaskTemplate;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TaskTemplateCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = auth()->user();
        $isAdmin = $user && $user->hasRole('admin');
        $isManager = $user && $user->hasRole('manager');

        return [
            'data' => $this->collection,
            
            // Metadatos de paginación
            'meta' => [
                'current_page' => $this->currentPage(),
                'from' => $this->firstItem(),
                'last_page' => $this->lastPage(),
                'per_page' => $this->perPage(),
                'to' => $this->lastItem(),
                'total' => $this->total(),
                'has_more_pages' => $this->hasMorePages(),
            ],

            // Enlaces de paginación
            'links' => [
                'first' => $this->url(1),
                'last' => $this->url($this->lastPage()),
                'prev' => $this->previousPageUrl(),
                'next' => $this->nextPageUrl(),
            ],

            // Estadísticas resumidas
            'summary' => [
                'total_templates' => $this->total(),
                'active_templates' => $this->collection->where('is_active', true)->count(),
                'inactive_templates' => $this->collection->where('is_active', false)->count(),
                'standard_templates' => $this->collection->where('is_standard', true)->count(),
                'non_standard_templates' => $this->collection->where('is_standard', false)->count(),
                'approved_templates' => $this->collection->where('is_approved', true)->count(),
                'pending_approval_templates' => $this->collection->where('is_approved', false)->count(),
                
                // Estadísticas por tipo
                'templates_by_type' => $this->collection->groupBy('template_type')->map->count(),
                
                // Estadísticas por prioridad
                'templates_by_priority' => $this->collection->groupBy('priority')->map->count(),
                
                // Estadísticas por nivel de riesgo
                'templates_by_risk_level' => $this->collection->groupBy('risk_level')->map->count(),
                
                // Estadísticas por departamento
                'templates_by_department' => $this->collection->groupBy('department')->map->count(),
                
                // Estadísticas por categoría
                'templates_by_category' => $this->collection->groupBy('category')->map->count(),
                
                // Estadísticas por subcategoría
                'templates_by_subcategory' => $this->collection->groupBy('subcategory')->map->count(),
                
                // Estadísticas de costos y duración
                'average_estimated_duration' => $this->collection->whereNotNull('estimated_duration_hours')->avg('estimated_duration_hours'),
                'average_estimated_cost' => $this->collection->whereNotNull('estimated_cost')->avg('estimated_cost'),
                'total_estimated_cost' => $this->collection->whereNotNull('estimated_cost')->sum('estimated_cost'),
                
                // Estadísticas de uso (solo para admin/manager)
                'usage_statistics' => $this->when($isAdmin || $isManager, [
                    'high_usage_templates' => $this->collection->filter(function ($template) {
                        return $template->maintenanceTasks()->count() > 20;
                    })->count(),
                    'medium_usage_templates' => $this->collection->filter(function ($template) {
                        $count = $template->maintenanceTasks()->count();
                        return $count > 5 && $count <= 20;
                    })->count(),
                    'low_usage_templates' => $this->collection->filter(function ($template) {
                        $count = $template->maintenanceTasks()->count();
                        return $count > 0 && $count <= 5;
                    })->count(),
                    'unused_templates' => $this->collection->filter(function ($template) {
                        return $template->maintenanceTasks()->count() === 0;
                    })->count(),
                ]),
            ],

            // Filtros aplicados
            'filters' => [
                'search' => $request->get('search'),
                'template_type' => $request->get('template_type'),
                'category' => $request->get('category'),
                'subcategory' => $request->get('subcategory'),
                'priority' => $request->get('priority'),
                'risk_level' => $request->get('risk_level'),
                'department' => $request->get('department'),
                'is_active' => $request->get('is_active'),
                'is_standard' => $request->get('is_standard'),
                'is_approved' => $request->get('is_approved'),
                'sort_by' => $request->get('sort_by', 'name'),
                'sort_direction' => $request->get('sort_direction', 'asc'),
            ],

            // Información adicional
            'additional_info' => [
                'can_create' => $user ? $user->can('create', \App\Models\TaskTemplate::class) : false,
                'can_export' => $user ? $user->can('export', \App\Models\TaskTemplate::class) : false,
                'can_bulk_actions' => $user ? $user->can('bulk_actions', \App\Models\TaskTemplate::class) : false,
                'available_actions' => [
                    'duplicate' => $user ? $user->can('create', \App\Models\TaskTemplate::class) : false,
                    'bulk_activate' => $user ? $user->can('bulk_activate', \App\Models\TaskTemplate::class) : false,
                    'bulk_deactivate' => $user ? $user->can('bulk_deactivate', \App\Models\TaskTemplate::class) : false,
                    'bulk_standard' => $user ? $user->can('bulk_standard', \App\Models\TaskTemplate::class) : false,
                    'bulk_delete' => $user ? $user->can('bulk_delete', \App\Models\TaskTemplate::class) : false,
                ],
            ],
        ];
    }
}
