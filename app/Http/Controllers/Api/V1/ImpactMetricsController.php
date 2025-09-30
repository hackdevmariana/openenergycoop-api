<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ImpactMetrics;
use App\Models\User;
use App\Models\PlantGroup;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ImpactMetricsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ImpactMetrics::with(['user', 'plantGroup']);

            // Aplicar filtros
            if ($request->has('user_id')) {
                $query->byUser($request->user_id);
            }

            if ($request->has('plant_group_id')) {
                $query->byPlantGroup($request->plant_group_id);
            }

            if ($request->has('type')) {
                if ($request->type === 'individual') {
                    $query->individual();
                } elseif ($request->type === 'global') {
                    $query->global();
                }
            }

            if ($request->has('date_from')) {
                $query->byDateRange($request->date_from, $request->date_to ?? null);
            }

            if ($request->has('co2_min') || $request->has('co2_max')) {
                $query->byCo2Range($request->co2_min ?? 0, $request->co2_max ?? null);
            }

            if ($request->has('kwh_min') || $request->has('kwh_max')) {
                $query->byKwhRange($request->kwh_min ?? 0, $request->kwh_max ?? null);
            }

            // Aplicar ordenamiento
            $sortBy = $request->get('sort_by', 'generated_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            if (in_array($sortBy, ['impact', 'production', 'date'])) {
                switch ($sortBy) {
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
            Log::error('Error al obtener métricas de impacto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métricas de impacto',
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
                'user_id' => 'nullable|exists:users,id',
                'total_kwh_produced' => 'required|numeric|min:0',
                'total_co2_avoided_kg' => 'required|numeric|min:0',
                'plant_group_id' => 'nullable|exists:plant_groups,id',
                'generated_at' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $metrics = ImpactMetrics::create($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Métricas de impacto creadas exitosamente',
                'data' => $metrics->load(['user', 'plantGroup'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear métricas de impacto: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear métricas de impacto',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ImpactMetrics $impactMetric): JsonResponse
    {
        try {
            $impactMetric->load(['user', 'plantGroup']);

            return response()->json([
                'success' => true,
                'data' => $impactMetric
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener métrica de impacto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métrica de impacto',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ImpactMetrics $impactMetric): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'nullable|exists:users,id',
                'total_kwh_produced' => 'sometimes|required|numeric|min:0',
                'total_co2_avoided_kg' => 'sometimes|required|numeric|min:0',
                'plant_group_id' => 'nullable|exists:plant_groups,id',
                'generated_at' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $impactMetric->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Métricas de impacto actualizadas exitosamente',
                'data' => $impactMetric->fresh()->load(['user', 'plantGroup'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar métricas de impacto: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar métricas de impacto',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ImpactMetrics $impactMetric): JsonResponse
    {
        try {
            DB::beginTransaction();

            $impactMetric->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Métricas de impacto eliminadas exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar métricas de impacto: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar métricas de impacto',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtener métricas por usuario
     */
    public function byUser($userId): JsonResponse
    {
        try {
            $validator = Validator::make(['user_id' => $userId], [
                'user_id' => 'required|exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no válido',
                    'errors' => $validator->errors()
                ], 422);
            }

            $metrics = ImpactMetrics::byUser($userId)
                ->with(['user', 'plantGroup'])
                ->orderBy('generated_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas por usuario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métricas por usuario',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtener métricas por grupo de plantas
     */
    public function byPlantGroup($plantGroupId): JsonResponse
    {
        try {
            $validator = Validator::make(['plant_group_id' => $plantGroupId], [
                'plant_group_id' => 'required|exists:plant_groups,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grupo de plantas no válido',
                    'errors' => $validator->errors()
                ], 422);
            }

            $metrics = ImpactMetrics::byPlantGroup($plantGroupId)
                ->with(['user', 'plantGroup'])
                ->orderBy('generated_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas por grupo de plantas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métricas por grupo de plantas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtener métricas globales
     */
    public function global(): JsonResponse
    {
        try {
            $metrics = ImpactMetrics::global()
                ->with(['user', 'plantGroup'])
                ->orderBy('generated_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas globales: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métricas globales',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtener métricas individuales
     */
    public function individual(): JsonResponse
    {
        try {
            $metrics = ImpactMetrics::individual()
                ->with(['user', 'plantGroup'])
                ->orderBy('generated_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas individuales: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métricas individuales',
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
            
            $metrics = ImpactMetrics::recent($days)
                ->with(['user', 'plantGroup'])
                ->orderBy('generated_at', 'desc')
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
            $metrics = ImpactMetrics::thisMonth()
                ->with(['user', 'plantGroup'])
                ->orderBy('generated_at', 'desc')
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
            $metrics = ImpactMetrics::thisYear()
                ->with(['user', 'plantGroup'])
                ->orderBy('generated_at', 'desc')
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
                'total_impact' => ImpactMetrics::getTotalGlobalImpact(),
                'total_production' => ImpactMetrics::getTotalGlobalProduction(),
                'top_users_by_impact' => ImpactMetrics::getTopUsersByImpact(5),
                'top_users_by_production' => ImpactMetrics::getTopUsersByProduction(5),
                'community_impact' => ImpactMetrics::getCommunityImpact(),
                'recent_metrics' => ImpactMetrics::recent(7)->count(),
                'this_month_metrics' => ImpactMetrics::thisMonth()->count(),
                'this_year_metrics' => ImpactMetrics::thisYear()->count(),
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
     * Actualizar métricas existentes
     */
    public function updateMetrics(Request $request, ImpactMetrics $impactMetric): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'kwh_produced' => 'required|numeric|min:0',
                'co2_factor' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $impactMetric->addKwhProduction($request->kwh_produced, $request->co2_factor);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Métricas actualizadas exitosamente',
                'data' => $impactMetric->fresh()->load(['user', 'plantGroup'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar métricas: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar métricas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Reiniciar métricas
     */
    public function resetMetrics(ImpactMetrics $impactMetric): JsonResponse
    {
        try {
            DB::beginTransaction();

            $impactMetric->resetMetrics();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Métricas reiniciadas exitosamente',
                'data' => $impactMetric->fresh()->load(['user', 'plantGroup'])
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
