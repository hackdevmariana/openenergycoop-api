<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CommunityMetrics;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CommunityMetricsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = CommunityMetrics::with(['organization']);

            // Aplicar filtros
            if ($request->has('organization_id')) {
                $query->byOrganization($request->organization_id);
            }

            if ($request->has('status')) {
                if ($request->status === 'active') {
                    $query->active();
                } elseif ($request->status === 'inactive') {
                    $query->inactive();
                }
            }

            if ($request->has('users_min') || $request->has('users_max')) {
                $query->byUserCount($request->users_min ?? 0, $request->users_max ?? null);
            }

            if ($request->has('co2_min') || $request->has('co2_max')) {
                $query->byCo2Range($request->co2_min ?? 0, $request->co2_max ?? null);
            }

            if ($request->has('kwh_min') || $request->has('kwh_max')) {
                $query->byKwhRange($request->kwh_min ?? 0, $request->kwh_max ?? null);
            }

            // Aplicar ordenamiento
            $sortBy = $request->get('sort_by', 'total_co2_avoided');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            if (in_array($sortBy, ['users', 'impact', 'production', 'date'])) {
                switch ($sortBy) {
                    case 'users':
                        $query->orderByUsers($sortDirection);
                        break;
                    case 'impact':
                        $query->orderByImpact($sortDirection);
                        break;
                    case 'production':
                        $query->orderByProduction($sortDirection);
                        break;
                    case 'date':
                        $query->orderByDate($sortDirection);
                        break;
                }
            } else {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Aplicar paginación
            $perPage = $request->get('per_page', 15);
            $metrics = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $metrics->items(),
                'pagination' => [
                    'current_page' => $metrics->currentPage(),
                    'last_page' => $metrics->lastPage(),
                    'per_page' => $metrics->perPage(),
                    'total' => $metrics->total(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas comunitarias: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métricas comunitarias',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|exists:organizations,id|unique:community_metrics,organization_id',
                'total_users' => 'required|integer|min:0',
                'total_kwh_produced' => 'required|numeric|min:0',
                'total_co2_avoided' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $metrics = CommunityMetrics::create($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Métricas comunitarias creadas exitosamente',
                'data' => $metrics->load(['organization'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear métricas comunitarias: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear métricas comunitarias',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CommunityMetrics $communityMetric): JsonResponse
    {
        try {
            $communityMetric->load(['organization']);

            return response()->json([
                'success' => true,
                'data' => $communityMetric
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener métrica comunitaria: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métrica comunitaria',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CommunityMetrics $communityMetric): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'organization_id' => 'sometimes|required|exists:organizations,id|unique:community_metrics,organization_id,' . $communityMetric->id,
                'total_users' => 'sometimes|required|integer|min:0',
                'total_kwh_produced' => 'sometimes|required|numeric|min:0',
                'total_co2_avoided' => 'sometimes|required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $communityMetric->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Métricas comunitarias actualizadas exitosamente',
                'data' => $communityMetric->fresh()->load(['organization'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar métricas comunitarias: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar métricas comunitarias',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CommunityMetrics $communityMetric): JsonResponse
    {
        try {
            DB::beginTransaction();

            $communityMetric->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Métricas comunitarias eliminadas exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar métricas comunitarias: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar métricas comunitarias',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtener métricas por organización
     */
    public function byOrganization($organizationId): JsonResponse
    {
        try {
            $validator = Validator::make(['organization_id' => $organizationId], [
                'organization_id' => 'required|exists:organizations,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Organización no válida',
                    'errors' => $validator->errors()
                ], 422);
            }

            $metrics = CommunityMetrics::byOrganization($organizationId)
                ->with(['organization'])
                ->first();

            if (!$metrics) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron métricas para esta organización'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas por organización: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métricas por organización',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtener métricas activas
     */
    public function active(): JsonResponse
    {
        try {
            $metrics = CommunityMetrics::active()
                ->with(['organization'])
                ->orderBy('total_co2_avoided', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas activas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métricas activas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtener métricas inactivas
     */
    public function inactive(): JsonResponse
    {
        try {
            $metrics = CommunityMetrics::inactive()
                ->with(['organization'])
                ->orderBy('updated_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas inactivas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métricas inactivas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtener métricas recientes
     */
    public function recent(Request $request): JsonResponse
    {
        try {
            $days = $request->get('days', 30);
            
            $metrics = CommunityMetrics::recent($days)
                ->with(['organization'])
                ->orderBy('updated_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas recientes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métricas recientes',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtener métricas de este mes
     */
    public function thisMonth(): JsonResponse
    {
        try {
            $metrics = CommunityMetrics::thisMonth()
                ->with(['organization'])
                ->orderBy('updated_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas de este mes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métricas de este mes',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtener métricas de este año
     */
    public function thisYear(): JsonResponse
    {
        try {
            $metrics = CommunityMetrics::thisYear()
                ->with(['organization'])
                ->orderBy('updated_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas de este año: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métricas de este año',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtener estadísticas generales
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_community_impact' => CommunityMetrics::getTotalCommunityImpact(),
                'total_community_production' => CommunityMetrics::getTotalCommunityProduction(),
                'total_community_users' => CommunityMetrics::getTotalCommunityUsers(),
                'top_organizations_by_impact' => CommunityMetrics::getTopOrganizationsByImpact(5),
                'top_organizations_by_production' => CommunityMetrics::getTopOrganizationsByProduction(5),
                'top_organizations_by_users' => CommunityMetrics::getTopOrganizationsByUsers(5),
                'average_metrics' => CommunityMetrics::getAverageMetrics(),
                'formatted_average_metrics' => CommunityMetrics::getFormattedAverageMetrics(),
                'active_organizations' => CommunityMetrics::active()->count(),
                'inactive_organizations' => CommunityMetrics::inactive()->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
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
     * Agregar usuario a la organización
     */
    public function addUser(CommunityMetrics $communityMetric): JsonResponse
    {
        try {
            DB::beginTransaction();

            $communityMetric->addUser();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario agregado exitosamente',
                'data' => $communityMetric->fresh()->load(['organization'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al agregar usuario: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar usuario',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remover usuario de la organización
     */
    public function removeUser(CommunityMetrics $communityMetric): JsonResponse
    {
        try {
            DB::beginTransaction();

            $communityMetric->removeUser();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario removido exitosamente',
                'data' => $communityMetric->fresh()->load(['organization'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al remover usuario: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al remover usuario',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Agregar producción de kWh
     */
    public function addKwhProduction(Request $request, CommunityMetrics $communityMetric): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'kwh' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $communityMetric->addKwhProduction($request->kwh);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producción de kWh agregada exitosamente',
                'data' => $communityMetric->fresh()->load(['organization'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al agregar producción de kWh: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar producción de kWh',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Agregar CO2 evitado
     */
    public function addCo2Avoided(Request $request, CommunityMetrics $communityMetric): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'co2' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $communityMetric->addCo2Avoided($request->co2);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'CO2 evitado agregado exitosamente',
                'data' => $communityMetric->fresh()->load(['organization'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al agregar CO2 evitado: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar CO2 evitado',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Reiniciar métricas
     */
    public function resetMetrics(CommunityMetrics $communityMetric): JsonResponse
    {
        try {
            DB::beginTransaction();

            $communityMetric->resetMetrics();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Métricas reiniciadas exitosamente',
                'data' => $communityMetric->fresh()->load(['organization'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al reiniciar métricas: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al reiniciar métricas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
