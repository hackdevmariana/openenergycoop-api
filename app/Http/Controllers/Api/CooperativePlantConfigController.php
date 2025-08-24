<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CooperativePlantConfig;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CooperativePlantConfigController extends Controller
{
    /**
     * Display a listing of cooperative plant configs.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = CooperativePlantConfig::with(['cooperative', 'plant', 'organization']);

            // Filtros
            if ($request->has('cooperative_id')) {
                $query->byCooperative($request->cooperative_id);
            }

            if ($request->has('plant_id')) {
                $query->byPlant($request->plant_id);
            }

            if ($request->has('organization_id')) {
                $query->byOrganization($request->organization_id);
            }

            if ($request->has('default')) {
                $query->where('default', $request->boolean('default'));
            }

            if ($request->has('active')) {
                $query->where('active', $request->boolean('active'));
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'cooperative_id');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortBy, $sortDirection);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $configs = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $configs,
                'message' => 'Configuraciones de plantas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener configuraciones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las configuraciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created cooperative plant config.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'cooperative_id' => 'required|exists:energy_cooperatives,id',
                'plant_id' => 'required|exists:plants,id',
                'default' => 'boolean',
                'active' => 'boolean',
                'organization_id' => 'nullable|exists:organizations,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar que no exista una configuración con la misma cooperativa y planta
            $existingConfig = CooperativePlantConfig::where('cooperative_id', $request->cooperative_id)
                ->where('plant_id', $request->plant_id)
                ->first();

            if ($existingConfig) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una configuración para esta cooperativa y planta',
                    'error' => 'Duplicate configuration'
                ], 409);
            }

            DB::beginTransaction();

            $config = CooperativePlantConfig::create($request->all());

            // Si se marca como por defecto, asegurar que solo haya uno por cooperativa
            if ($config->default) {
                $config->setAsDefault();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $config->load(['cooperative', 'plant', 'organization']),
                'message' => 'Configuración de planta creada exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear configuración: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la configuración',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified cooperative plant config.
     */
    public function show(CooperativePlantConfig $config): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $config->load(['cooperative', 'plant', 'organization']),
                'message' => 'Configuración de planta obtenida exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener configuración: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la configuración',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified cooperative plant config.
     */
    public function update(Request $request, CooperativePlantConfig $config): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'cooperative_id' => 'required|exists:energy_cooperatives,id',
                'plant_id' => 'required|exists:plants,id',
                'default' => 'boolean',
                'active' => 'boolean',
                'organization_id' => 'nullable|exists:organizations,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar que no exista otra configuración con la misma cooperativa y planta
            $existingConfig = CooperativePlantConfig::where('cooperative_id', $request->cooperative_id)
                ->where('plant_id', $request->plant_id)
                ->where('id', '!=', $config->id)
                ->first();

            if ($existingConfig) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe otra configuración para esta cooperativa y planta',
                    'error' => 'Duplicate configuration'
                ], 409);
            }

            DB::beginTransaction();

            $config->update($request->all());

            // Si se marca como por defecto, asegurar que solo haya uno por cooperativa
            if ($config->default) {
                $config->setAsDefault();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $config->load(['cooperative', 'plant', 'organization']),
                'message' => 'Configuración de planta actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar configuración: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la configuración',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified cooperative plant config.
     */
    public function destroy(CooperativePlantConfig $config): JsonResponse
    {
        try {
            DB::beginTransaction();

            $config->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Configuración de planta eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar configuración: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la configuración',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set configuration as default for cooperative.
     */
    public function setAsDefault(CooperativePlantConfig $config): JsonResponse
    {
        try {
            DB::beginTransaction();

            $config->setAsDefault();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $config->fresh()->load(['cooperative', 'plant', 'organization']),
                'message' => 'Configuración marcada como por defecto exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al marcar como por defecto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar como por defecto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove default status from configuration.
     */
    public function removeDefault(CooperativePlantConfig $config): JsonResponse
    {
        try {
            DB::beginTransaction();

            $config->removeDefault();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $config->fresh()->load(['cooperative', 'plant', 'organization']),
                'message' => 'Estado por defecto removido exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al remover estado por defecto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al remover el estado por defecto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get configurations by cooperative.
     */
    public function byCooperative(int $cooperativeId): JsonResponse
    {
        try {
            $configs = CooperativePlantConfig::byCooperative($cooperativeId)
                ->with(['plant', 'organization'])
                ->active()
                ->orderBy('default', 'desc')
                ->orderBy('plant_id')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $configs,
                'message' => 'Configuraciones de la cooperativa obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener configuraciones por cooperativa: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las configuraciones de la cooperativa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get default configuration for cooperative.
     */
    public function getDefault(int $cooperativeId): JsonResponse
    {
        try {
            $config = CooperativePlantConfig::byCooperative($cooperativeId)
                ->default()
                ->active()
                ->with(['plant', 'organization'])
                ->first();

            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró configuración por defecto para esta cooperativa',
                    'error' => 'No default configuration found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $config,
                'message' => 'Configuración por defecto obtenida exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener configuración por defecto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la configuración por defecto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle configuration active status.
     */
    public function toggleActive(CooperativePlantConfig $config): JsonResponse
    {
        try {
            if ($config->default) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede cambiar el estado de una configuración por defecto',
                    'error' => 'Cannot toggle default configuration'
                ], 400);
            }

            DB::beginTransaction();

            $config->toggleActive();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $config->fresh()->load(['cooperative', 'plant', 'organization']),
                'message' => 'Estado de la configuración cambiado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cambiar estado de configuración: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get configuration statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_configs' => CooperativePlantConfig::count(),
                'active_configs' => CooperativePlantConfig::active()->count(),
                'default_configs' => CooperativePlantConfig::default()->count(),
                'configs_by_cooperative' => CooperativePlantConfig::selectRaw('cooperative_id, COUNT(*) as count')
                    ->with('cooperative:id,name')
                    ->groupBy('cooperative_id')
                    ->get(),
                'configs_by_plant' => CooperativePlantConfig::selectRaw('plant_id, COUNT(*) as count')
                    ->with('plant:id,name')
                    ->groupBy('plant_id')
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Estadísticas de configuraciones obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas de configuraciones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas de configuraciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
