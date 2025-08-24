<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlantGroup;
use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlantGroupController extends Controller
{
    /**
     * Display a listing of plant groups.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = PlantGroup::with(['plant', 'user']);

            // Filtros
            if ($request->has('search')) {
                $query->search($request->search);
            }

            if ($request->has('plant_id')) {
                $query->byPlant($request->plant_id);
            }

            if ($request->has('user_id')) {
                $query->byUser($request->user_id);
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->has('type')) {
                if ($request->type === 'collective') {
                    $query->collective();
                } elseif ($request->type === 'individual') {
                    $query->individual();
                }
            }

            if ($request->has('min_co2') || $request->has('max_co2')) {
                $minCo2 = $request->get('min_co2');
                $maxCo2 = $request->get('max_co2');
                $query->byCo2Range($minCo2, $maxCo2);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'updated_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $query->orderBy($sortBy, $sortDirection);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $plantGroups = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $plantGroups,
                'message' => 'Grupos de plantas obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener grupos de plantas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los grupos de plantas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created plant group.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'plant_id' => 'required|exists:plants,id',
                'user_id' => 'nullable|exists:users,id',
                'number_of_plants' => 'required|integer|min:0',
                'co2_avoided_total' => 'required|numeric|min:0',
                'custom_label' => 'nullable|string|max:255',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar que no exista un grupo con la misma planta y usuario
            $existingGroup = PlantGroup::where('plant_id', $request->plant_id)
                ->where('user_id', $request->user_id)
                ->first();

            if ($existingGroup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un grupo con esta planta para este usuario',
                    'error' => 'Duplicate plant group'
                ], 409);
            }

            DB::beginTransaction();

            $plantGroup = PlantGroup::create($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $plantGroup->load(['plant', 'user']),
                'message' => 'Grupo de plantas creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear grupo de plantas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el grupo de plantas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified plant group.
     */
    public function show(PlantGroup $plantGroup): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $plantGroup->load(['plant', 'user']),
                'message' => 'Grupo de plantas obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener grupo de plantas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el grupo de plantas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified plant group.
     */
    public function update(Request $request, PlantGroup $plantGroup): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'plant_id' => 'required|exists:plants,id',
                'user_id' => 'nullable|exists:users,id',
                'number_of_plants' => 'required|integer|min:0',
                'co2_avoided_total' => 'required|numeric|min:0',
                'custom_label' => 'nullable|string|max:255',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar que no exista otro grupo con la misma planta y usuario
            $existingGroup = PlantGroup::where('plant_id', $request->plant_id)
                ->where('user_id', $request->user_id)
                ->where('id', '!=', $plantGroup->id)
                ->first();

            if ($existingGroup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe otro grupo con esta planta para este usuario',
                    'error' => 'Duplicate plant group'
                ], 409);
            }

            DB::beginTransaction();

            $plantGroup->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $plantGroup->load(['plant', 'user']),
                'message' => 'Grupo de plantas actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar grupo de plantas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el grupo de plantas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified plant group.
     */
    public function destroy(PlantGroup $plantGroup): JsonResponse
    {
        try {
            DB::beginTransaction();

            $plantGroup->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Grupo de plantas eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar grupo de plantas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el grupo de plantas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add plants to a group.
     */
    public function addPlants(Request $request, PlantGroup $plantGroup): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'count' => 'required|integer|min:1',
                'co2_avoided' => 'nullable|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $plantGroup->addPlants($request->count, $request->co2_avoided);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $plantGroup->fresh()->load(['plant', 'user']),
                'message' => 'Plantas añadidas al grupo exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al añadir plantas al grupo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al añadir plantas al grupo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove plants from a group.
     */
    public function removePlants(Request $request, PlantGroup $plantGroup): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'count' => 'required|integer|min:1',
                'co2_avoided' => 'nullable|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'error' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $plantGroup->removePlants($request->count, $request->co2_avoided);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $plantGroup->fresh()->load(['plant', 'user']),
                'message' => 'Plantas removidas del grupo exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al remover plantas del grupo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al remover plantas del grupo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get plant groups by user.
     */
    public function byUser(int $userId): JsonResponse
    {
        try {
            $plantGroups = PlantGroup::byUser($userId)
                ->with(['plant'])
                ->active()
                ->orderBy('updated_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $plantGroups,
                'message' => 'Grupos de plantas del usuario obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener grupos de plantas del usuario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los grupos de plantas del usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get collective plant groups.
     */
    public function collective(): JsonResponse
    {
        try {
            $plantGroups = PlantGroup::collective()
                ->with(['plant'])
                ->active()
                ->orderBy('updated_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $plantGroups,
                'message' => 'Grupos colectivos de plantas obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener grupos colectivos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los grupos colectivos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle plant group active status.
     */
    public function toggleActive(PlantGroup $plantGroup): JsonResponse
    {
        try {
            DB::beginTransaction();

            $plantGroup->toggleActive();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $plantGroup->fresh()->load(['plant', 'user']),
                'message' => 'Estado del grupo de plantas cambiado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cambiar estado del grupo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado del grupo de plantas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get plant group statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_groups' => PlantGroup::count(),
                'active_groups' => PlantGroup::active()->count(),
                'individual_groups' => PlantGroup::individual()->count(),
                'collective_groups' => PlantGroup::collective()->count(),
                'total_plants' => PlantGroup::sum('number_of_plants'),
                'total_co2_avoided' => PlantGroup::sum('co2_avoided_total'),
                'groups_by_plant_type' => PlantGroup::selectRaw('plant_id, COUNT(*) as count')
                    ->with('plant:id,name')
                    ->groupBy('plant_id')
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Estadísticas de grupos de plantas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas de grupos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas de grupos de plantas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
