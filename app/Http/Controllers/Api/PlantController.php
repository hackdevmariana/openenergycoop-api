<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlantController extends Controller
{
    /**
     * Display a listing of plants.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Plant::query();

            // Filtros
            if ($request->has('search')) {
                $query->search($request->search);
            }

            if ($request->has('unit_label')) {
                $query->byUnitLabel($request->unit_label);
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->has('min_co2') || $request->has('max_co2')) {
                $minCo2 = $request->get('min_co2');
                $maxCo2 = $request->get('max_co2');
                $query->byCo2Range($minCo2, $maxCo2);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'name');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortBy, $sortDirection);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $plants = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $plants,
                'message' => 'Plantas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener plantas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las plantas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created plant.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:plants,name',
                'unit_label' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'co2_equivalent_per_unit_kg' => 'required|numeric|min:0|max:999.9999',
                'image' => 'nullable|string|max:255',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $plant = Plant::create($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $plant,
                'message' => 'Planta creada exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear planta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la planta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified plant.
     */
    public function show(Plant $plant): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $plant->load(['plantGroups', 'cooperativeConfigs']),
                'message' => 'Planta obtenida exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener planta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la planta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified plant.
     */
    public function update(Request $request, Plant $plant): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:plants,name,' . $plant->id,
                'unit_label' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'co2_equivalent_per_unit_kg' => 'required|numeric|min:0|max:999.9999',
                'image' => 'nullable|string|max:255',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $plant->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $plant,
                'message' => 'Planta actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar planta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la planta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified plant.
     */
    public function destroy(Plant $plant): JsonResponse
    {
        try {
            DB::beginTransaction();

            $plant->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Planta eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar planta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la planta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active plants only.
     */
    public function active(): JsonResponse
    {
        try {
            $plants = Plant::active()->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => $plants,
                'message' => 'Plantas activas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener plantas activas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las plantas activas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get plants by unit label.
     */
    public function byUnitLabel(string $unitLabel): JsonResponse
    {
        try {
            $plants = Plant::byUnitLabel($unitLabel)->active()->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => $plants,
                'message' => "Plantas de tipo '{$unitLabel}' obtenidas exitosamente"
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener plantas por tipo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las plantas por tipo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get plants by CO2 range.
     */
    public function byCo2Range(Request $request): JsonResponse
    {
        try {
            $minCo2 = $request->get('min_co2', 0);
            $maxCo2 = $request->get('max_co2');

            $plants = Plant::byCo2Range($minCo2, $maxCo2)->active()->orderBy('co2_equivalent_per_unit_kg')->get();

            return response()->json([
                'success' => true,
                'data' => $plants,
                'message' => 'Plantas filtradas por rango de CO2 exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al filtrar plantas por CO2: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al filtrar plantas por CO2',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle plant active status.
     */
    public function toggleActive(Plant $plant): JsonResponse
    {
        try {
            DB::beginTransaction();

            $plant->toggleActive();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $plant,
                'message' => 'Estado de la planta cambiado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cambiar estado de planta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado de la planta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get plant statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_plants' => Plant::count(),
                'active_plants' => Plant::active()->count(),
                'inactive_plants' => Plant::where('is_active', false)->count(),
                'plants_by_unit_label' => Plant::selectRaw('unit_label, COUNT(*) as count')
                    ->groupBy('unit_label')
                    ->get(),
                'co2_range' => [
                    'min' => Plant::min('co2_equivalent_per_unit_kg'),
                    'max' => Plant::max('co2_equivalent_per_unit_kg'),
                    'average' => Plant::avg('co2_equivalent_per_unit_kg')
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Estadísticas de plantas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas de plantas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas de plantas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
