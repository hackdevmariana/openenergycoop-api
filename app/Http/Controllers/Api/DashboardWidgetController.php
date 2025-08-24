<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DashboardWidget;
use App\Models\User;
use App\Models\DashboardView;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DashboardWidgetController extends Controller
{
    /**
     * Display a listing of dashboard widgets.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = DashboardWidget::with(['user', 'dashboardView']);

            // Filtros
            if ($request->has('user_id')) {
                $query->byUser($request->user_id);
            }

            if ($request->has('dashboard_view_id')) {
                $query->byDashboardView($request->dashboard_view_id);
            }

            if ($request->has('type')) {
                $query->byType($request->type);
            }

            if ($request->has('size')) {
                $query->bySize($request->size);
            }

            if ($request->has('visible')) {
                $query->where('visible', $request->boolean('visible'));
            }

            if ($request->has('collapsible')) {
                $query->where('collapsible', $request->boolean('collapsible'));
            }

            if ($request->has('search')) {
                $query->search($request->search);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'position');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $widgets = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $widgets->items(),
                'pagination' => [
                    'current_page' => $widgets->currentPage(),
                    'last_page' => $widgets->lastPage(),
                    'per_page' => $widgets->perPage(),
                    'total' => $widgets->total(),
                    'from' => $widgets->firstItem(),
                    'to' => $widgets->lastItem(),
                ],
                'message' => 'Widgets del dashboard obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener widgets del dashboard: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener widgets del dashboard',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created dashboard widget.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'dashboard_view_id' => 'nullable|exists:dashboard_views,id',
                'type' => ['required', Rule::in(array_keys(DashboardWidget::getAvailableTypes()))],
                'title' => 'nullable|string|max:255',
                'position' => 'nullable|integer|min:1',
                'settings_json' => 'nullable|array',
                'visible' => 'boolean',
                'collapsible' => 'boolean',
                'collapsed' => 'boolean',
                'size' => ['nullable', Rule::in(array_keys(DashboardWidget::getAvailableSizes()))],
                'grid_position' => 'nullable|array',
                'refresh_interval' => 'nullable|integer|min:30|max:3600',
                'data_source' => 'nullable|array',
                'filters' => 'nullable|array',
                'permissions' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $widget = DashboardWidget::create($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $widget->load(['user', 'dashboardView']),
                'message' => 'Widget del dashboard creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear widget del dashboard: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear widget del dashboard',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified dashboard widget.
     */
    public function show(DashboardWidget $dashboardWidget): JsonResponse
    {
        try {
            $dashboardWidget->load(['user', 'dashboardView']);

            return response()->json([
                'success' => true,
                'data' => $dashboardWidget,
                'message' => 'Widget del dashboard obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener widget del dashboard: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener widget del dashboard',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified dashboard widget.
     */
    public function update(Request $request, DashboardWidget $dashboardWidget): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'type' => ['sometimes', 'required', Rule::in(array_keys(DashboardWidget::getAvailableTypes()))],
                'title' => 'sometimes|nullable|string|max:255',
                'position' => 'sometimes|nullable|integer|min:1',
                'settings_json' => 'sometimes|nullable|array',
                'visible' => 'sometimes|boolean',
                'collapsible' => 'sometimes|boolean',
                'collapsed' => 'sometimes|boolean',
                'size' => ['sometimes', 'nullable', Rule::in(array_keys(DashboardWidget::getAvailableSizes()))],
                'grid_position' => 'sometimes|nullable|array',
                'refresh_interval' => 'sometimes|nullable|integer|min:30|max:3600',
                'data_source' => 'sometimes|nullable|array',
                'filters' => 'sometimes|nullable|array',
                'permissions' => 'sometimes|nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $dashboardWidget->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $dashboardWidget->fresh()->load(['user', 'dashboardView']),
                'message' => 'Widget del dashboard actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar widget del dashboard: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar widget del dashboard',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified dashboard widget.
     */
    public function destroy(DashboardWidget $dashboardWidget): JsonResponse
    {
        try {
            DB::beginTransaction();

            $dashboardWidget->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Widget del dashboard eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar widget del dashboard: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar widget del dashboard',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Show the specified widget.
     */
    public function showWidget(DashboardWidget $dashboardWidget): JsonResponse
    {
        try {
            $dashboardWidget->show();

            return response()->json([
                'success' => true,
                'data' => $dashboardWidget->fresh(),
                'message' => 'Widget mostrado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al mostrar widget: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al mostrar widget',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Hide the specified widget.
     */
    public function hideWidget(DashboardWidget $dashboardWidget): JsonResponse
    {
        try {
            $dashboardWidget->hide();

            return response()->json([
                'success' => true,
                'data' => $dashboardWidget->fresh(),
                'message' => 'Widget ocultado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al ocultar widget: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al ocultar widget',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Refresh the specified widget.
     */
    public function refresh(DashboardWidget $dashboardWidget): JsonResponse
    {
        try {
            $dashboardWidget->refresh();

            return response()->json([
                'success' => true,
                'data' => $dashboardWidget->fresh(),
                'message' => 'Widget actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar widget: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar widget',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Duplicate the specified widget.
     */
    public function duplicate(DashboardWidget $dashboardWidget): JsonResponse
    {
        try {
            $duplicatedWidget = $dashboardWidget->duplicate();

            return response()->json([
                'success' => true,
                'data' => $duplicatedWidget->load(['user', 'dashboardView']),
                'message' => 'Widget duplicado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al duplicar widget: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al duplicar widget',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update widget position.
     */
    public function updatePosition(Request $request, DashboardWidget $dashboardWidget): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'position' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $dashboardWidget->updatePosition($request->position);

            return response()->json([
                'success' => true,
                'data' => $dashboardWidget->fresh(),
                'message' => 'Posición del widget actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar posición del widget: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar posición del widget',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update widget grid position.
     */
    public function updateGridPosition(Request $request, DashboardWidget $dashboardWidget): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'grid_position' => 'required|array',
                'grid_position.x' => 'required|integer|min:0',
                'grid_position.y' => 'required|integer|min:0',
                'grid_position.width' => 'required|integer|min:1|max:12',
                'grid_position.height' => 'required|integer|min:1|max:12',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $dashboardWidget->updateGridPosition($request->grid_position);

            return response()->json([
                'success' => true,
                'data' => $dashboardWidget->fresh(),
                'message' => 'Posición en grid del widget actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar posición en grid del widget: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar posición en grid del widget',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update widget settings.
     */
    public function updateSettings(Request $request, DashboardWidget $dashboardWidget): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'settings' => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $dashboardWidget->updateSettings($request->settings);

            return response()->json([
                'success' => true,
                'data' => $dashboardWidget->fresh(),
                'message' => 'Configuración del widget actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar configuración del widget: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar configuración del widget',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update widget filters.
     */
    public function updateFilters(Request $request, DashboardWidget $dashboardWidget): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'filters' => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $dashboardWidget->updateFilters($request->filters);

            return response()->json([
                'success' => true,
                'data' => $dashboardWidget->fresh(),
                'message' => 'Filtros del widget actualizados exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar filtros del widget: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar filtros del widget',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get widget data.
     */
    public function getData(DashboardWidget $dashboardWidget): JsonResponse
    {
        try {
            $data = $dashboardWidget->getData();

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Datos del widget obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener datos del widget: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos del widget',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get available widget types.
     */
    public function types(): JsonResponse
    {
        try {
            $types = DashboardWidget::getAvailableTypes();

            return response()->json([
                'success' => true,
                'data' => $types,
                'message' => 'Tipos de widgets disponibles obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener tipos de widgets disponibles: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipos de widgets disponibles',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get available widget sizes.
     */
    public function sizes(): JsonResponse
    {
        try {
            $sizes = DashboardWidget::getAvailableSizes();

            return response()->json([
                'success' => true,
                'data' => $sizes,
                'message' => 'Tamaños de widgets disponibles obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener tamaños de widgets disponibles: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tamaños de widgets disponibles',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get widget statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_widgets' => DashboardWidget::count(),
                'visible_widgets' => DashboardWidget::where('visible', true)->count(),
                'hidden_widgets' => DashboardWidget::where('visible', false)->count(),
                'collapsible_widgets' => DashboardWidget::where('collapsible', true)->count(),
                'widgets_by_type' => DashboardWidget::selectRaw('type, COUNT(*) as count')
                    ->groupBy('type')
                    ->pluck('count', 'type'),
                'widgets_by_size' => DashboardWidget::selectRaw('size, COUNT(*) as count')
                    ->groupBy('size')
                    ->pluck('count', 'size'),
                'recent_widgets' => DashboardWidget::where('created_at', '>=', now()->subDays(7))
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Estadísticas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Bulk update widgets.
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'widget_ids' => 'required|array|min:1',
                'widget_ids.*' => 'exists:dashboard_widgets,id',
                'updates' => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $widgetIds = $request->widget_ids;
            $updates = $request->updates;

            $widgets = DashboardWidget::whereIn('id', $widgetIds)->get();
            $updatedCount = 0;

            foreach ($widgets as $widget) {
                $widget->update($updates);
                $updatedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'updated_count' => $updatedCount,
                    'total_requested' => count($widgetIds)
                ],
                'message' => "{$updatedCount} widgets actualizados exitosamente"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en actualización masiva de widgets: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error en actualización masiva de widgets',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Bulk delete widgets.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'widget_ids' => 'required|array|min:1',
                'widget_ids.*' => 'exists:dashboard_widgets,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $widgetIds = $request->widget_ids;
            $widgets = DashboardWidget::whereIn('id', $widgetIds)->get();
            $deletedCount = 0;

            foreach ($widgets as $widget) {
                $widget->delete();
                $deletedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'deleted_count' => $deletedCount,
                    'total_requested' => count($widgetIds)
                ],
                'message' => "{$deletedCount} widgets eliminados exitosamente"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en eliminación masiva de widgets: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error en eliminación masiva de widgets',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
