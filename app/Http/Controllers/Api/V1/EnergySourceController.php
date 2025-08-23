<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\EnergySource\StoreEnergySourceRequest;
use App\Http\Requests\Api\V1\EnergySource\UpdateEnergySourceRequest;
use App\Http\Resources\Api\V1\EnergySource\EnergySourceResource;
use App\Http\Resources\Api\V1\EnergySource\EnergySourceCollection;
use App\Models\EnergySource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @group Energy Source Management
 *
 * APIs for managing energy sources
 */
class EnergySourceController extends Controller
{
    /**
     * Display a listing of energy sources.
     *
     * @authenticated
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam search string Search in name, description, technology. Example: "Solar Panel"
     * @queryParam category string Filter by category. Example: "renewable"
     * @queryParam type string Filter by type. Example: "photovoltaic"
     * @queryParam status string Filter by status. Example: "active"
     * @queryParam is_renewable boolean Filter by renewable status. Example: true
     * @queryParam is_clean boolean Filter by clean status. Example: true
     * @queryParam is_active boolean Filter by active status. Example: true
     * @queryParam is_featured boolean Filter by featured status. Example: true
     * @queryParam efficiency_min float Minimum efficiency percentage. Example: 80.0
     * @queryParam efficiency_max float Maximum efficiency percentage. Example: 95.0
     * @queryParam capacity_min float Minimum capacity in kW. Example: 100.0
     * @queryParam capacity_max float Maximum capacity in kW. Example: 1000.0
     * @queryParam carbon_footprint_max float Maximum carbon footprint. Example: 0.5
     * @queryParam cost_min float Minimum installation cost per kW. Example: 1000.0
     * @queryParam cost_max float Maximum installation cost per kW. Example: 5000.0
     * @queryParam sort_by string Sort field. Example: "name"
     * @queryParam sort_direction string Sort direction (asc/desc). Example: "asc"
     * @queryParam created_at_from date Filter by creation date from. Example: "2024-01-01"
     * @queryParam created_at_to date Filter by creation date to. Example: "2024-12-31"
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Solar Panel High Efficiency",
     *       "description": "High efficiency photovoltaic solar panel",
     *       "category": "renewable",
     *       "type": "photovoltaic",
     *       "status": "active",
     *       "efficiency_typical": 85.5,
     *       "capacity_typical": 500.0,
     *       "is_renewable": true,
     *       "is_clean": true,
     *       "is_active": true,
     *       "created_at": "2024-01-15T10:00:00Z"
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "total": 25,
     *     "per_page": 15
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = EnergySource::query();

            // Filtros de búsqueda
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('technology_description', 'like', "%{$search}%")
                      ->orWhere('manufacturer', 'like', "%{$search}%");
                });
            }

            // Filtros por categoría
            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            // Filtros por tipo
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            // Filtros por estado
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filtros por estado renovable
            if ($request->filled('is_renewable')) {
                $query->where('is_renewable', $request->boolean('is_renewable'));
            }

            // Filtros por estado limpio
            if ($request->filled('is_clean')) {
                $query->where('is_clean', $request->boolean('is_clean'));
            }

            // Filtros por estado activo
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // Filtros por estado destacado
            if ($request->filled('is_featured')) {
                $query->where('is_featured', $request->boolean('is_featured'));
            }

            // Filtros por eficiencia
            if ($request->filled('efficiency_min')) {
                $query->where('efficiency_typical', '>=', $request->efficiency_min);
            }

            if ($request->filled('efficiency_max')) {
                $query->where('efficiency_typical', '<=', $request->efficiency_max);
            }

            // Filtros por capacidad
            if ($request->filled('capacity_min')) {
                $query->where('capacity_typical', '>=', $request->capacity_min);
            }

            if ($request->filled('capacity_max')) {
                $query->where('capacity_typical', '<=', $request->capacity_max);
            }

            // Filtros por huella de carbono
            if ($request->filled('carbon_footprint_max')) {
                $query->where('carbon_footprint_kg_kwh', '<=', $request->carbon_footprint_max);
            }

            // Filtros por costo
            if ($request->filled('cost_min')) {
                $query->where('installation_cost_per_kw', '>=', $request->cost_min);
            }

            if ($request->filled('cost_max')) {
                $query->where('installation_cost_per_kw', '<=', $request->cost_max);
            }

            // Filtros por fechas de creación
            if ($request->filled('created_at_from')) {
                $query->whereDate('created_at', '>=', $request->created_at_from);
            }

            if ($request->filled('created_at_to')) {
                $query->whereDate('created_at', '<=', $request->created_at_to);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'sort_order');
            $sortDirection = $request->get('sort_direction', 'asc');
            
            if (in_array($sortBy, ['name', 'efficiency_typical', 'capacity_typical', 'installation_cost_per_kw', 'created_at', 'sort_order'])) {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Paginación
            $perPage = min($request->get('per_page', 15), 100);
            $energySources = $query->paginate($perPage);

            return response()->json([
                'data' => EnergySourceResource::collection($energySources),
                'meta' => [
                    'current_page' => $energySources->currentPage(),
                    'total' => $energySources->total(),
                    'per_page' => $energySources->perPage(),
                    'last_page' => $energySources->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy sources: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las fuentes de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created energy source.
     *
     * @authenticated
     * @bodyParam name string required The name of the energy source. Example: "Solar Panel High Efficiency"
     * @bodyParam description string The description of the energy source. Example: "High efficiency photovoltaic solar panel"
     * @bodyParam category string required The category of the energy source. Example: "renewable"
     * @bodyParam type string required The type of the energy source. Example: "photovoltaic"
     * @bodyParam status string required The status of the energy source. Example: "active"
     * @bodyParam efficiency_typical float The typical efficiency percentage. Example: 85.5
     * @bodyParam capacity_typical float The typical capacity in kW. Example: 500.0
     * @bodyParam is_renewable boolean Whether the energy source is renewable. Example: true
     * @bodyParam is_clean boolean Whether the energy source is clean. Example: true
     * @bodyParam is_active boolean Whether the energy source is active. Example: true
     * @bodyParam is_featured boolean Whether the energy source is featured. Example: false
     *
     * @response 201 {
     *   "message": "Fuente de energía creada exitosamente",
     *   "data": {
     *     "id": 1,
     *     "name": "Solar Panel High Efficiency",
     *     "category": "renewable",
     *     "type": "photovoltaic",
     *     "status": "active",
     *     "created_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "Los datos proporcionados no son válidos",
     *   "errors": {
     *     "name": ["El campo nombre es obligatorio"],
     *     "category": ["El campo categoría es obligatorio"]
     *   }
     * }
     */
    public function store(StoreEnergySourceRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $energySource = EnergySource::create($request->validated());

            DB::commit();

            Log::info('Energy source created', [
                'energy_source_id' => $energySource->id,
                'user_id' => auth()->id(),
                'energy_source_name' => $energySource->name
            ]);

            return response()->json([
                'message' => 'Fuente de energía creada exitosamente',
                'data' => new EnergySourceResource($energySource)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating energy source: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al crear la fuente de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified energy source.
     *
     * @authenticated
     * @urlParam id integer required The energy source ID. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "Solar Panel High Efficiency",
     *     "description": "High efficiency photovoltaic solar panel",
     *     "category": "renewable",
     *     "type": "photovoltaic",
     *     "status": "active",
     *     "efficiency_typical": 85.5,
     *     "capacity_typical": 500.0,
     *     "is_renewable": true,
     *     "is_clean": true,
     *     "is_active": true,
     *     "created_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "Fuente de energía no encontrada"
     * }
     */
    public function show(EnergySource $energySource): JsonResponse
    {
        try {
            return response()->json([
                'data' => new EnergySourceResource($energySource)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy source: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener la fuente de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified energy source.
     *
     * @authenticated
     * @urlParam id integer required The energy source ID. Example: 1
     * @bodyParam name string The name of the energy source. Example: "Solar Panel High Efficiency Updated"
     * @bodyParam description string The description of the energy source. Example: "Updated description"
     * @bodyParam category string The category of the energy source. Example: "renewable"
     * @bodyParam type string The type of the energy source. Example: "photovoltaic"
     * @bodyParam status string The status of the energy source. Example: "active"
     * @bodyParam efficiency_typical float The typical efficiency percentage. Example: 87.0
     * @bodyParam capacity_typical float The typical capacity in kW. Example: 600.0
     * @bodyParam is_renewable boolean Whether the energy source is renewable. Example: true
     * @bodyParam is_clean boolean Whether the energy source is clean. Example: true
     * @bodyParam is_active boolean Whether the energy source is active. Example: true
     * @bodyParam is_featured boolean Whether the energy source is featured. Example: true
     *
     * @response 200 {
     *   "message": "Fuente de energía actualizada exitosamente",
     *   "data": {
     *     "id": 1,
     *     "name": "Solar Panel High Efficiency Updated",
     *     "status": "active",
     *     "updated_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "Fuente de energía no encontrada"
     * }
     *
     * @response 422 {
     *   "message": "Los datos proporcionados no son válidos",
     *   "errors": {
     *     "efficiency_typical": ["La eficiencia debe ser un número entre 0 y 100"]
     *   }
     * }
     */
    public function update(UpdateEnergySourceRequest $request, EnergySource $energySource): JsonResponse
    {
        try {
            DB::beginTransaction();

            $energySource->update($request->validated());

            DB::commit();

            Log::info('Energy source updated', [
                'energy_source_id' => $energySource->id,
                'user_id' => auth()->id(),
                'energy_source_name' => $energySource->name
            ]);

            return response()->json([
                'message' => 'Fuente de energía actualizada exitosamente',
                'data' => new EnergySourceResource($energySource)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating energy source: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al actualizar la fuente de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified energy source.
     *
     * @authenticated
     * @urlParam id integer required The energy source ID. Example: 1
     *
     * @response 200 {
     *   "message": "Fuente de energía eliminada exitosamente"
     * }
     *
     * @response 404 {
     *   "message": "Fuente de energía no encontrada"
     * }
     */
    public function destroy(EnergySource $energySource): JsonResponse
    {
        try {
            DB::beginTransaction();

            $energySourceName = $energySource->name;
            $energySourceId = $energySource->id;

            $energySource->delete();

            DB::commit();

            Log::info('Energy source deleted', [
                'energy_source_id' => $energySourceId,
                'user_id' => auth()->id(),
                'energy_source_name' => $energySourceName
            ]);

            return response()->json([
                'message' => 'Fuente de energía eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting energy source: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al eliminar la fuente de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get energy source statistics.
     *
     * @authenticated
     * @queryParam category string Filter by category. Example: "renewable"
     * @queryParam type string Filter by type. Example: "photovoltaic"
     * @queryParam status string Filter by status. Example: "active"
     *
     * @response 200 {
     *   "data": {
     *     "total_sources": 25,
     *     "active_sources": 20,
     *     "renewable_sources": 18,
     *     "clean_sources": 20,
     *     "average_efficiency": 82.5,
     *     "total_capacity_kw": 125000.0,
     *     "sources_by_category": {
     *       "renewable": 18,
     *       "non_renewable": 5,
     *       "hybrid": 2
     *     },
     *     "sources_by_type": {
     *       "photovoltaic": 12,
     *       "wind_turbine": 6,
     *       "hydroelectric": 3
     *     },
     *     "sources_by_status": {
     *       "active": 20,
     *       "maintenance": 3,
     *       "inactive": 2
     *     }
     *   }
     * }
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $query = EnergySource::query();

            // Filtros
            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $totalSources = $query->count();
            $activeSources = (clone $query)->where('is_active', true)->count();
            $renewableSources = (clone $query)->where('is_renewable', true)->count();
            $cleanSources = (clone $query)->where('is_clean', true)->count();
            $averageEfficiency = (clone $query)->avg('efficiency_typical');
            $totalCapacity = (clone $query)->sum('capacity_typical');

            // Fuentes por categoría
            $sourcesByCategory = (clone $query)
                ->selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray();

            // Fuentes por tipo
            $sourcesByType = (clone $query)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray();

            // Fuentes por estado
            $sourcesByStatus = (clone $query)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            return response()->json([
                'data' => [
                    'total_sources' => $totalSources,
                    'active_sources' => $activeSources,
                    'renewable_sources' => $renewableSources,
                    'clean_sources' => $cleanSources,
                    'average_efficiency' => round($averageEfficiency, 1),
                    'total_capacity_kw' => $totalCapacity,
                    'sources_by_category' => $sourcesByCategory,
                    'sources_by_type' => $sourcesByType,
                    'sources_by_status' => $sourcesByStatus,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy source statistics: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las estadísticas de fuentes de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get energy source categories.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": {
     *     "renewable": "Renovable",
     *     "non_renewable": "No Renovable",
     *     "hybrid": "Híbrida"
     *   }
     * }
     */
    public function categories(): JsonResponse
    {
        try {
            return response()->json([
                'data' => EnergySource::getCategories()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching energy source categories: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las categorías de fuentes de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get energy source types.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": {
     *     "photovoltaic": "Fotovoltaica",
     *     "concentrated_solar": "Solar Concentrada",
     *     "wind_turbine": "Turbina Eólica",
     *     "hydroelectric": "Hidroeléctrica",
     *     "biomass_plant": "Planta de Biomasa",
     *     "geothermal_plant": "Planta Geotérmica",
     *     "nuclear_reactor": "Reactor Nuclear",
     *     "coal_plant": "Planta de Carbón",
     *     "gas_plant": "Planta de Gas",
     *     "other": "Otro"
     *   }
     * }
     */
    public function types(): JsonResponse
    {
        try {
            return response()->json([
                'data' => EnergySource::getTypes()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching energy source types: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los tipos de fuentes de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get energy source statuses.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": {
     *     "active": "Activa",
     *     "inactive": "Inactiva",
     *     "maintenance": "Mantenimiento",
     *     "development": "En Desarrollo",
     *     "testing": "En Pruebas",
     *     "deprecated": "Obsoleta"
     *   }
     * }
     */
    public function statuses(): JsonResponse
    {
        try {
            return response()->json([
                'data' => EnergySource::getStatuses()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching energy source statuses: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los estados de fuentes de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Toggle energy source active status.
     *
     * @authenticated
     * @urlParam id integer required The energy source ID. Example: 1
     *
     * @response 200 {
     *   "message": "Estado de la fuente de energía actualizado exitosamente",
     *   "data": {
     *     "id": 1,
     *     "is_active": false,
     *     "updated_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     */
    public function toggleActive(EnergySource $energySource): JsonResponse
    {
        try {
            $energySource->update([
                'is_active' => !$energySource->is_active
            ]);

            Log::info('Energy source active status toggled', [
                'energy_source_id' => $energySource->id,
                'user_id' => auth()->id(),
                'new_status' => $energySource->is_active
            ]);

            return response()->json([
                'message' => 'Estado de la fuente de energía actualizado exitosamente',
                'data' => [
                    'id' => $energySource->id,
                    'is_active' => $energySource->is_active,
                    'updated_at' => $energySource->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling energy source active status: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al cambiar el estado de la fuente de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Toggle energy source featured status.
     *
     * @authenticated
     * @urlParam id integer required The energy source ID. Example: 1
     *
     * @response 200 {
     *   "message": "Estado destacado de la fuente de energía actualizado exitosamente",
     *   "data": {
     *     "id": 1,
     *     "is_featured": true,
     *     "updated_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     */
    public function toggleFeatured(EnergySource $energySource): JsonResponse
    {
        try {
            $energySource->update([
                'is_featured' => !$energySource->is_featured
            ]);

            Log::info('Energy source featured status toggled', [
                'energy_source_id' => $energySource->id,
                'user_id' => auth()->id(),
                'new_status' => $energySource->is_featured
            ]);

            return response()->json([
                'message' => 'Estado destacado de la fuente de energía actualizado exitosamente',
                'data' => [
                    'id' => $energySource->id,
                    'is_featured' => $energySource->is_featured,
                    'updated_at' => $energySource->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling energy source featured status: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al cambiar el estado destacado de la fuente de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update energy source status.
     *
     * @authenticated
     * @urlParam id integer required The energy source ID. Example: 1
     * @bodyParam status string required The new status. Example: "maintenance"
     *
     * @response 200 {
     *   "message": "Estado de la fuente de energía actualizado exitosamente",
     *   "data": {
     *     "id": 1,
     *     "status": "maintenance",
     *     "updated_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     */
    public function updateStatus(Request $request, EnergySource $energySource): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|string|in:' . implode(',', array_keys(EnergySource::getStatuses()))
            ]);

            $oldStatus = $energySource->status;
            $energySource->update(['status' => $request->status]);

            Log::info('Energy source status updated', [
                'energy_source_id' => $energySource->id,
                'user_id' => auth()->id(),
                'old_status' => $oldStatus,
                'new_status' => $request->status
            ]);

            return response()->json([
                'message' => 'Estado de la fuente de energía actualizado exitosamente',
                'data' => [
                    'id' => $energySource->id,
                    'status' => $energySource->status,
                    'updated_at' => $energySource->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating energy source status: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al actualizar el estado de la fuente de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Duplicate an energy source.
     *
     * @authenticated
     * @urlParam id integer required The energy source ID. Example: 1
     * @bodyParam name string The new name for the duplicated energy source. Example: "Solar Panel High Efficiency - Copy"
     *
     * @response 200 {
     *   "message": "Fuente de energía duplicada exitosamente",
     *   "data": {
     *     "id": 2,
     *     "name": "Solar Panel High Efficiency - Copy",
     *     "status": "active",
     *     "is_active": true,
     *     "is_featured": false,
     *     "created_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     */
    public function duplicate(Request $request, EnergySource $energySource): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'nullable|string|max:255'
            ]);

            $newEnergySource = $energySource->replicate();
            $newEnergySource->name = $request->name ?? $energySource->name . ' (Copia)';
            $newEnergySource->slug = $energySource->slug . '-copy-' . time();
            $newEnergySource->is_active = true;
            $newEnergySource->is_featured = false;
            $newEnergySource->sort_order = $energySource->sort_order + 1;
            $newEnergySource->save();

            Log::info('Energy source duplicated', [
                'original_energy_source_id' => $energySource->id,
                'new_energy_source_id' => $newEnergySource->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Fuente de energía duplicada exitosamente',
                'data' => new EnergySourceResource($newEnergySource)
            ]);

        } catch (\Exception $e) {
            Log::error('Error duplicating energy source: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al duplicar la fuente de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get featured energy sources.
     *
     * @authenticated
     * @queryParam limit integer Number of featured sources to return. Example: 5
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Solar Panel High Efficiency",
     *       "description": "High efficiency photovoltaic solar panel",
     *       "category": "renewable",
     *       "type": "photovoltaic",
     *       "efficiency_typical": 85.5,
     *       "is_featured": true
     *     }
     *   ]
     * }
     */
    public function featured(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 10), 50);
            
            $featuredSources = EnergySource::where('is_featured', true)
                ->where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergySourceResource::collection($featuredSources)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching featured energy sources: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las fuentes de energía destacadas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get renewable energy sources.
     *
     * @authenticated
     * @queryParam limit integer Number of renewable sources to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Solar Panel High Efficiency",
     *       "category": "renewable",
     *       "type": "photovoltaic",
     *       "is_renewable": true,
     *       "is_clean": true
     *     }
     *   ]
     * }
     */
    public function renewable(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $renewableSources = EnergySource::where('is_renewable', true)
                ->where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergySourceResource::collection($renewableSources)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching renewable energy sources: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las fuentes de energía renovables',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get clean energy sources.
     *
     * @authenticated
     * @queryParam limit integer Number of clean sources to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Solar Panel High Efficiency",
     *       "category": "renewable",
     *       "type": "photovoltaic",
     *       "is_clean": true,
     *       "carbon_footprint_kg_kwh": 0.05
     *     }
     *   ]
     * }
     */
    public function clean(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $cleanSources = EnergySource::where('is_clean', true)
                ->where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergySourceResource::collection($cleanSources)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching clean energy sources: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las fuentes de energía limpias',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
