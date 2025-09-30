<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DashboardView;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DashboardViewController extends Controller
{
    /**
     * Display a listing of dashboard views.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = DashboardView::with(['user', 'widgets']);

            // Filtros
            if ($request->has('user_id')) {
                $query->byUser($request->user_id);
            }

            if ($request->has('is_default')) {
                $query->where('is_default', $request->boolean('is_default'));
            }

            if ($request->has('is_public')) {
                $query->where('is_public', $request->boolean('is_public'));
            }

            if ($request->has('theme')) {
                $query->byTheme($request->theme);
            }

            if ($request->has('color_scheme')) {
                $query->byColorScheme($request->color_scheme);
            }

            if ($request->has('search')) {
                $query->search($request->search);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $views = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $views->items(),
                'pagination' => [
                    'current_page' => $views->currentPage(),
                    'last_page' => $views->lastPage(),
                    'per_page' => $views->perPage(),
                    'total' => $views->total(),
                    'from' => $views->firstItem(),
                    'to' => $views->lastItem(),
                ],
                'message' => 'Vistas del dashboard obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener vistas del dashboard: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener vistas del dashboard',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created dashboard view.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'name' => 'nullable|string|max:255',
                'layout_json' => 'nullable|array',
                'is_default' => 'boolean',
                'theme' => ['nullable', Rule::in(array_keys(DashboardView::getAvailableThemes()))],
                'color_scheme' => ['nullable', Rule::in(array_keys(DashboardView::getAvailableColorSchemes()))],
                'widget_settings' => 'nullable|array',
                'is_public' => 'boolean',
                'description' => 'nullable|string',
                'access_permissions' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $view = DashboardView::create($request->all());

            // Si es la vista por defecto, desactivar otras
            if ($view->is_default) {
                $view->setAsDefault();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $view->load(['user', 'widgets']),
                'message' => 'Vista del dashboard creada exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear vista del dashboard: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear vista del dashboard',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified dashboard view.
     */
    public function show(DashboardView $dashboardView): JsonResponse
    {
        try {
            $dashboardView->load(['user', 'widgets']);

            return response()->json([
                'success' => true,
                'data' => $dashboardView,
                'message' => 'Vista del dashboard obtenida exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener vista del dashboard: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener vista del dashboard',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified dashboard view.
     */
    public function update(Request $request, DashboardView $dashboardView): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|nullable|string|max:255',
                'layout_json' => 'sometimes|nullable|array',
                'is_default' => 'sometimes|boolean',
                'theme' => ['sometimes', 'nullable', Rule::in(array_keys(DashboardView::getAvailableThemes()))],
                'color_scheme' => ['sometimes', 'nullable', Rule::in(array_keys(DashboardView::getAvailableColorSchemes()))],
                'widget_settings' => 'sometimes|nullable|array',
                'is_public' => 'sometimes|boolean',
                'description' => 'sometimes|nullable|string',
                'access_permissions' => 'sometimes|nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $dashboardView->update($request->all());

            // Si se establece como vista por defecto, desactivar otras
            if ($request->has('is_default') && $request->boolean('is_default')) {
                $dashboardView->setAsDefault();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $dashboardView->fresh()->load(['user', 'widgets']),
                'message' => 'Vista del dashboard actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar vista del dashboard: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar vista del dashboard',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified dashboard view.
     */
    public function destroy(DashboardView $dashboardView): JsonResponse
    {
        try {
            DB::beginTransaction();

            $dashboardView->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vista del dashboard eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar vista del dashboard: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar vista del dashboard',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Set dashboard view as default.
     */
    public function setAsDefault(DashboardView $dashboardView): JsonResponse
    {
        try {
            $dashboardView->setAsDefault();

            return response()->json([
                'success' => true,
                'data' => $dashboardView->fresh(),
                'message' => 'Vista del dashboard establecida como predeterminada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al establecer vista como predeterminada: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al establecer vista como predeterminada',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Duplicate dashboard view.
     */
    public function duplicate(DashboardView $dashboardView): JsonResponse
    {
        try {
            $duplicatedView = $dashboardView->duplicate();

            return response()->json([
                'success' => true,
                'data' => $duplicatedView->load(['user', 'widgets']),
                'message' => 'Vista del dashboard duplicada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al duplicar vista del dashboard: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al duplicar vista del dashboard',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Add widget to dashboard view.
     */
    public function addWidget(Request $request, DashboardView $dashboardView): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|string',
                'position' => 'nullable|integer|min:1',
                'settings' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $widget = $dashboardView->addWidget(
                $request->type,
                $request->position,
                $request->settings ?? []
            );

            return response()->json([
                'success' => true,
                'data' => $widget,
                'message' => 'Widget añadido exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al añadir widget: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al añadir widget',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove widget from dashboard view.
     */
    public function removeWidget(Request $request, DashboardView $dashboardView): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'widget_id' => 'required|exists:dashboard_widgets,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $dashboardView->removeWidget($request->widget_id);

            return response()->json([
                'success' => true,
                'message' => 'Widget eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar widget: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar widget',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Move widget in dashboard view.
     */
    public function moveWidget(Request $request, DashboardView $dashboardView): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'widget_id' => 'required|exists:dashboard_widgets,id',
                'new_position' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $success = $dashboardView->moveWidget($request->widget_id, $request->new_position);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo mover el widget'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Widget movido exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al mover widget: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al mover widget',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update module settings in dashboard view.
     */
    public function updateModuleSettings(Request $request, DashboardView $dashboardView): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'module' => 'required|string',
                'settings' => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $dashboardView->updateModuleSettings($request->module, $request->settings);

            return response()->json([
                'success' => true,
                'data' => $dashboardView->fresh(),
                'message' => 'Configuración del módulo actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar configuración del módulo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar configuración del módulo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Share dashboard view with user.
     */
    public function share(Request $request, DashboardView $dashboardView): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'permissions' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $dashboardView->shareWith($request->user_id, $request->permissions ?? []);

            return response()->json([
                'success' => true,
                'message' => 'Vista del dashboard compartida exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al compartir vista del dashboard: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al compartir vista del dashboard',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Unshare dashboard view with user.
     */
    public function unshare(Request $request, DashboardView $dashboardView): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $dashboardView->unshareWith($request->user_id);

            return response()->json([
                'success' => true,
                'message' => 'Vista del dashboard descompartida exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al descompartir vista del dashboard: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al descompartir vista del dashboard',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get available themes.
     */
    public function themes(): JsonResponse
    {
        try {
            $themes = DashboardView::getAvailableThemes();

            return response()->json([
                'success' => true,
                'data' => $themes,
                'message' => 'Temas disponibles obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener temas disponibles: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener temas disponibles',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get available color schemes.
     */
    public function colorSchemes(): JsonResponse
    {
        try {
            $colorSchemes = DashboardView::getAvailableColorSchemes();

            return response()->json([
                'success' => true,
                'data' => $colorSchemes,
                'message' => 'Esquemas de colores disponibles obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener esquemas de colores disponibles: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener esquemas de colores disponibles',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get default layout.
     */
    public function defaultLayout(): JsonResponse
    {
        try {
            $layout = DashboardView::getDefaultLayout();

            return response()->json([
                'success' => true,
                'data' => $layout,
                'message' => 'Layout por defecto obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener layout por defecto: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener layout por defecto',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get dashboard view statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_views' => DashboardView::count(),
                'default_views' => DashboardView::where('is_default', true)->count(),
                'public_views' => DashboardView::where('is_public', true)->count(),
                'views_by_theme' => DashboardView::selectRaw('theme, COUNT(*) as count')
                    ->groupBy('theme')
                    ->pluck('count', 'theme'),
                'views_by_color_scheme' => DashboardView::selectRaw('color_scheme, COUNT(*) as count')
                    ->groupBy('color_scheme')
                    ->pluck('count', 'color_scheme'),
                'recent_views' => DashboardView::where('created_at', '>=', now()->subDays(7))
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
}
