<?php

namespace App\Http\Resources\Api\V1\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NotificationCollection extends ResourceCollection
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
                'total_notifications' => $this->total(),
                'unread_count' => $this->collection->where('is_read', false)->count(),
                'read_count' => $this->collection->where('is_read', true)->count(),
                'by_type' => $this->collection->groupBy('type')->map->count(),
                'recent_count' => $this->collection->where('days_old', '<=', 7)->count(),
                'urgent_count' => $this->collection->whereIn('type', ['error', 'alert'])->count(),
            ],

            // Información de la respuesta
            'with' => [
                'success' => true,
                'message' => 'Notificaciones obtenidas exitosamente',
                'applied_filters' => $this->getAppliedFilters($request),
                'applied_sorting' => $this->getAppliedSorting($request),
                'generated_at' => now()->toISOString(),
            ],

            // Campos adicionales para administradores
            'admin_stats' => $this->when($isAdmin, function () {
                return [
                    'total_users_with_notifications' => $this->collection->unique('user_id')->count(),
                    'average_notifications_per_user' => $this->collection->count() > 0 ? 
                        round($this->collection->count() / $this->collection->unique('user_id')->count(), 2) : 0,
                    'delivery_rate' => $this->collection->count() > 0 ? 
                        round(($this->collection->where('is_delivered', true)->count() / $this->collection->count()) * 100, 2) : 0,
                    'read_rate' => $this->collection->count() > 0 ? 
                        round(($this->collection->where('is_read', true)->count() / $this->collection->count()) * 100, 2) : 0,
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
        
        if ($request->filled('type')) {
            $filters['type'] = $request->type;
        }
        
        if ($request->filled('user_id')) {
            $filters['user_id'] = $request->user_id;
        }
        
        if ($request->filled('is_read')) {
            $filters['is_read'] = $request->boolean('is_read');
        }
        
        if ($request->filled('is_delivered')) {
            $filters['is_delivered'] = $request->boolean('is_delivered');
        }
        
        if ($request->filled('search')) {
            $filters['search'] = $request->search;
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
