<?php

namespace App\Http\Resources\Api\V1\NotificationSetting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NotificationSettingCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = auth()->user();
        $isAdmin = $user && $user->hasRole('admin');

        return [
            'data' => $this->collection,
            
            // Metadatos de paginación
            'meta' => [
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'from' => $this->firstItem(),
                'to' => $this->lastItem(),
                'has_more_pages' => $this->hasMorePages(),
                'links' => [
                    'first' => $this->url(1),
                    'last' => $this->url($this->lastPage()),
                    'prev' => $this->previousPageUrl(),
                    'next' => $this->nextPageUrl(),
                ],
            ],

            // Resumen estadístico
            'summary' => [
                'total_settings' => $this->total(),
                'enabled_count' => $this->collection->where('enabled', true)->count(),
                'disabled_count' => $this->collection->where('enabled', false)->count(),
                'by_channel' => $this->collection->groupBy('channel')->map->count(),
                'by_type' => $this->collection->groupBy('notification_type')->map->count(),
                'by_user' => $this->collection->groupBy('user_id')->map->count(),
            ],

            // Información de la respuesta
            'with' => [
                'success' => true,
                'message' => 'Configuraciones obtenidas exitosamente',
                'applied_filters' => $this->getAppliedFilters($request),
                'applied_sorting' => $this->getAppliedSorting($request),
                'generated_at' => now()->toISOString(),
            ],

            // Campos adicionales para administradores
            'admin_stats' => $this->when($isAdmin, function () {
                return [
                    'total_users_with_settings' => $this->collection->unique('user_id')->count(),
                    'average_settings_per_user' => $this->collection->count() > 0 ? 
                        round($this->collection->count() / $this->collection->unique('user_id')->count(), 2) : 0,
                    'enabled_rate' => $this->collection->count() > 0 ? 
                        round(($this->collection->where('enabled', true)->count() / $this->collection->count()) * 100, 2) : 0,
                    'channel_distribution' => $this->collection->groupBy('channel')->map(function ($group) {
                        return [
                            'count' => $group->count(),
                            'enabled_count' => $group->where('enabled', true)->count(),
                            'disabled_count' => $group->where('enabled', false)->count(),
                        ];
                    }),
                    'type_distribution' => $this->collection->groupBy('notification_type')->map(function ($group) {
                        return [
                            'count' => $group->count(),
                            'enabled_count' => $group->where('enabled', true)->count(),
                            'disabled_count' => $group->where('enabled', false)->count(),
                        ];
                    }),
                ];
            }),
        ];
    }

    /**
     * Obtener los filtros aplicados en la request
     */
    private function getAppliedFilters(Request $request): array
    {
        $filters = [];
        
        if ($request->filled('user_id')) {
            $filters['user_id'] = $request->user_id;
        }
        
        if ($request->filled('channel')) {
            $filters['channel'] = $request->channel;
        }
        
        if ($request->filled('notification_type')) {
            $filters['notification_type'] = $request->notification_type;
        }
        
        if ($request->filled('enabled')) {
            $filters['enabled'] = $request->boolean('enabled');
        }
        
        return $filters;
    }

    /**
     * Obtener el ordenamiento aplicado en la request
     */
    private function getAppliedSorting(Request $request): array
    {
        return [
            'sort_by' => $request->get('sort_by', 'created_at'),
            'sort_direction' => $request->get('sort_direction', 'desc'),
        ];
    }
}
