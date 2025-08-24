<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\EnergyPool\StoreEnergyPoolRequest;
use App\Http\Requests\Api\V1\EnergyPool\UpdateEnergyPoolRequest;
use App\Http\Resources\Api\V1\EnergyPool\EnergyPoolResource;
use App\Http\Resources\Api\V1\EnergyPool\EnergyPoolCollection;
use App\Models\EnergyPool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @group Energy Pool Management
 * APIs for managing energy pools
 */
class EnergyPoolController extends \App\Http\Controllers\Controller
{
    /**
     * Display a listing of energy pools.
     *
     * @authenticated
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam search string Search in name, description, pool_number. Example: "Pool 001"
     * @queryParam pool_type string Filter by pool type. Example: "trading"
     * @queryParam status string Filter by status. Example: "active"
     * @queryParam energy_category string Filter by energy category. Example: "renewable"
     * @queryParam region string Filter by region. Example: "North"
     * @queryParam country string Filter by country. Example: "Spain"
     * @queryParam managed_by integer Filter by manager ID. Example: 1
     * @queryParam efficiency_min float Minimum efficiency rating. Example: 80.0
     * @queryParam efficiency_max float Maximum efficiency rating. Example: 100.0
     * @queryParam availability_min float Minimum availability factor. Example: 90.0
     * @queryParam availability_max float Maximum availability factor. Example: 100.0
     * @queryParam capacity_min float Minimum total capacity in MW. Example: 100.0
     * @queryParam capacity_max float Maximum total capacity in MW. Example: 1000.0
     * @queryParam sort_by string Sort field. Example: "name"
     * @queryParam sort_direction string Sort direction (asc/desc). Example: "asc"
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "pool_number": "POOL-001",
     *       "name": "Solar Energy Pool",
     *       "pool_type": "trading",
     *       "status": "active",
     *       "energy_category": "renewable",
     *       "total_capacity_mw": 150.00,
     *       "available_capacity_mw": 120.00
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
            $query = EnergyPool::with(['managedBy', 'createdBy', 'approvedBy']);

            // Filtros de búsqueda
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('pool_number', 'like', "%{$search}%");
                });
            }

            // Filtros por tipo, estado y categoría
            if ($request->filled('pool_type')) {
                $query->where('pool_type', $request->pool_type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('energy_category')) {
                $query->where('energy_category', $request->energy_category);
            }

            // Filtros por ubicación
            if ($request->filled('region')) {
                $query->where('region', $request->region);
            }

            if ($request->filled('country')) {
                $query->where('country', $request->country);
            }

            // Filtros por gestión
            if ($request->filled('managed_by')) {
                $query->where('managed_by', $request->managed_by);
            }

            // Filtros por eficiencia
            if ($request->filled('efficiency_min')) {
                $query->where('efficiency_rating', '>=', $request->efficiency_min);
            }

            if ($request->filled('efficiency_max')) {
                $query->where('efficiency_rating', '<=', $request->efficiency_max);
            }

            // Filtros por disponibilidad
            if ($request->filled('availability_min')) {
                $query->where('availability_factor', '>=', $request->availability_min);
            }

            if ($request->filled('availability_max')) {
                $query->where('availability_factor', '<=', $request->availability_max);
            }

            // Filtros por capacidad
            if ($request->filled('capacity_min')) {
                $query->where('total_capacity_mw', '>=', $request->capacity_min);
            }

            if ($request->filled('capacity_max')) {
                $query->where('total_capacity_mw', '<=', $request->capacity_max);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'name');
            $sortDirection = $request->get('sort_direction', 'asc');
            
            if (in_array($sortBy, ['name', 'pool_number', 'total_capacity_mw', 'efficiency_rating', 'availability_factor', 'created_at'])) {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Paginación
            $perPage = min($request->get('per_page', 15), 100);
            $energyPools = $query->paginate($perPage);

            return response()->json([
                'data' => EnergyPoolResource::collection($energyPools),
                'meta' => [
                    'current_page' => $energyPools->currentPage(),
                    'total' => $energyPools->total(),
                    'per_page' => $energyPools->perPage(),
                    'last_page' => $energyPools->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy pools: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los pools de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created energy pool.
     *
     * @authenticated
     * @bodyParam pool_number string required Unique pool number. Example: "POOL-001"
     * @bodyParam name string required Pool name. Example: "Solar Energy Pool"
     * @bodyParam description string Pool description. Example: "Pool for solar energy trading"
     * @bodyParam pool_type string required Pool type. Example: "trading"
     * @bodyParam status string required Pool status. Example: "active"
     * @bodyParam energy_category string required Energy category. Example: "renewable"
     * @bodyParam total_capacity_mw numeric Total capacity in MW. Example: 150.00
     * @bodyParam available_capacity_mw numeric Available capacity in MW. Example: 120.00
     * @bodyParam efficiency_rating numeric Efficiency rating percentage. Example: 85.50
     * @bodyParam availability_factor numeric Availability factor percentage. Example: 92.00
     * @bodyParam location_address string Location address. Example: "123 Solar Street"
     * @bodyParam latitude numeric Latitude coordinate. Example: 40.4168
     * @bodyParam longitude numeric Longitude coordinate. Example: -3.7038
     * @bodyParam region string Region name. Example: "Madrid"
     * @bodyParam country string Country name. Example: "Spain"
     * @bodyParam commissioning_date date Commissioning date. Example: "2024-01-15"
     * @bodyParam expected_lifespan_years integer Expected lifespan in years. Example: 25
     * @bodyParam construction_cost numeric Construction cost. Example: 1500000.00
     * @bodyParam operational_cost_per_mwh numeric Operational cost per MWh. Example: 45.00
     * @bodyParam maintenance_cost_per_mwh numeric Maintenance cost per MWh. Example: 15.00
     * @bodyParam technical_specifications array Technical specifications. Example: {"voltage": "400V", "frequency": "50Hz"}
     * @bodyParam environmental_impact array Environmental impact data. Example: {"co2_reduction": "5000 tons/year"}
     * @bodyParam regulatory_compliance array Regulatory compliance data. Example: {"certification": "ISO 14001"}
     * @bodyParam safety_features array Safety features. Example: {"emergency_shutdown": true}
     * @bodyParam pool_members array Pool members. Example: {"member1": "Company A", "member2": "Company B"}
     * @bodyParam pool_operators array Pool operators. Example: {"operator1": "Operator A"}
     * @bodyParam pool_governance array Pool governance rules. Example: {"voting_rights": "proportional"}
     * @bodyParam trading_rules array Trading rules. Example: {"min_order": "1 MWh"}
     * @bodyParam settlement_procedures array Settlement procedures. Example: {"payment_terms": "30 days"}
     * @bodyParam risk_management array Risk management data. Example: {"max_exposure": "1000 MWh"}
     * @bodyParam performance_metrics array Performance metrics. Example: {"uptime": "99.5%"}
     * @bodyParam environmental_data array Environmental data. Example: {"emissions": "0 gCO2/kWh"}
     * @bodyParam regulatory_documents array Regulatory documents. Example: {"permit": "PER-2024-001"}
     * @bodyParam tags array Tags. Example: ["solar", "renewable", "trading"]
     * @bodyParam managed_by integer Manager user ID. Example: 1
     * @bodyParam created_by integer Creator user ID. Example: 1
     * @bodyParam notes string Additional notes. Example: "Pool created for Q1 2024"
     *
     * @response 201 {
     *   "data": {
     *     "id": 1,
     *     "pool_number": "POOL-001",
     *     "name": "Solar Energy Pool",
     *     "pool_type": "trading",
     *     "status": "active",
     *     "energy_category": "renewable"
     *   },
     *   "message": "Pool de energía creado exitosamente"
     * }
     */
    public function store(StoreEnergyPoolRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $energyPool = EnergyPool::create($request->validated());

            // Log activity
            Log::info('Energy pool created', [
                'pool_id' => $energyPool->id,
                'pool_number' => $energyPool->pool_number,
                'created_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'data' => new EnergyPoolResource($energyPool),
                'message' => 'Pool de energía creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating energy pool: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al crear el pool de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified energy pool.
     *
     * @authenticated
     * @urlParam energyPool integer required The ID of the energy pool. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "pool_number": "POOL-001",
     *     "name": "Solar Energy Pool",
     *     "pool_type": "trading",
     *     "status": "active",
     *     "energy_category": "renewable",
     *     "total_capacity_mw": 150.00,
     *     "available_capacity_mw": 120.00,
     *     "efficiency_rating": 85.50,
     *     "availability_factor": 92.00
     *   }
     * }
     */
    public function show(EnergyPool $energyPool): JsonResponse
    {
        try {
            $energyPool->load(['managedBy', 'createdBy', 'approvedBy']);

            return response()->json([
                'data' => new EnergyPoolResource($energyPool)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy pool: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al obtener el pool de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified energy pool.
     *
     * @authenticated
     * @urlParam energyPool integer required The ID of the energy pool. Example: 1
     * @bodyParam name string Pool name. Example: "Updated Solar Energy Pool"
     * @bodyParam description string Pool description. Example: "Updated description"
     * @bodyParam status string Pool status. Example: "maintenance"
     * @bodyParam efficiency_rating numeric Efficiency rating percentage. Example: 87.50
     * @bodyParam available_capacity_mw numeric Available capacity in MW. Example: 125.00
     * @bodyParam notes string Additional notes. Example: "Updated notes"
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "Updated Solar Energy Pool",
     *     "status": "maintenance",
     *     "efficiency_rating": 87.50
     *   },
     *   "message": "Pool de energía actualizado exitosamente"
     * }
     */
    public function update(UpdateEnergyPoolRequest $request, EnergyPool $energyPool): JsonResponse
    {
        try {
            DB::beginTransaction();

            $energyPool->update($request->validated());

            // Log activity
            Log::info('Energy pool updated', [
                'pool_id' => $energyPool->id,
                'pool_number' => $energyPool->pool_number,
                'updated_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'data' => new EnergyPoolResource($energyPool),
                'message' => 'Pool de energía actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating energy pool: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al actualizar el pool de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified energy pool.
     *
     * @authenticated
     * @urlParam energyPool integer required The ID of the energy pool. Example: 1
     *
     * @response 200 {
     *   "message": "Pool de energía eliminado exitosamente"
     * }
     */
    public function destroy(EnergyPool $energyPool): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Log activity before deletion
            Log::info('Energy pool deleted', [
                'pool_id' => $energyPool->id,
                'pool_number' => $energyPool->pool_number,
                'deleted_by' => auth()->id()
            ]);

            $energyPool->delete();

            DB::commit();

            return response()->json([
                'message' => 'Pool de energía eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting energy pool: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al eliminar el pool de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get energy pool statistics.
     *
     * @authenticated
     *
     * @response 200 {
     *   "total_pools": 25,
     *   "active_pools": 20,
     *   "total_capacity_mw": 5000.00,
     *   "average_efficiency": 82.50,
     *   "pools_by_type": {
     *     "trading": 10,
     *     "reserve": 5,
     *     "balancing": 3
     *   },
     *   "pools_by_status": {
     *     "active": 20,
     *     "maintenance": 3,
     *     "inactive": 2
     *   },
     *   "pools_by_category": {
     *     "renewable": 15,
     *     "hybrid": 5,
     *     "storage": 5
     *   }
     * }
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_pools' => EnergyPool::count(),
                'active_pools' => EnergyPool::active()->count(),
                'total_capacity_mw' => EnergyPool::sum('total_capacity_mw'),
                'average_efficiency' => EnergyPool::avg('efficiency_rating'),
                'pools_by_type' => EnergyPool::selectRaw('pool_type, COUNT(*) as count')
                    ->groupBy('pool_type')
                    ->pluck('count', 'pool_type')
                    ->toArray(),
                'pools_by_status' => EnergyPool::selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray(),
                'pools_by_category' => EnergyPool::selectRaw('energy_category, COUNT(*) as count')
                    ->groupBy('energy_category')
                    ->pluck('count', 'energy_category')
                    ->toArray(),
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Error fetching energy pool statistics: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al obtener estadísticas de pools de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get available pool types.
     *
     * @authenticated
     *
     * @response 200 {
     *   "types": {
     *     "trading": "Trading",
     *     "reserve": "Reserva",
     *     "balancing": "Balanceo",
     *     "ancillary": "Auxiliar",
     *     "capacity": "Capacidad",
     *     "demand_response": "Respuesta a la Demanda",
     *     "virtual": "Virtual",
     *     "hybrid": "Híbrido",
     *     "other": "Otro"
     *   }
     * }
     */
    public function types(): JsonResponse
    {
        return response()->json([
            'types' => EnergyPool::getPoolTypes()
        ]);
    }

    /**
     * Get available pool statuses.
     *
     * @authenticated
     *
     * @response 200 {
     *   "statuses": {
     *     "active": "Activo",
     *     "inactive": "Inactivo",
     *     "maintenance": "Mantenimiento",
     *     "suspended": "Suspendido",
     *     "closed": "Cerrado",
     *     "planned": "Planificado"
     *   }
     * }
     */
    public function statuses(): JsonResponse
    {
        return response()->json([
            'statuses' => EnergyPool::getStatuses()
        ]);
    }

    /**
     * Get available energy categories.
     *
     * @authenticated
     *
     * @response 200 {
     *   "categories": {
     *     "renewable": "Renovable",
     *     "non_renewable": "No Renovable",
     *     "hybrid": "Híbrido",
     *     "storage": "Almacenamiento",
     *     "demand": "Demanda",
     *     "other": "Otro"
     *   }
     * }
     */
    public function categories(): JsonResponse
    {
        return response()->json([
            'categories' => EnergyPool::getEnergyCategories()
        ]);
    }

    /**
     * Update pool status.
     *
     * @authenticated
     * @urlParam energyPool integer required The ID of the energy pool. Example: 1
     * @bodyParam status string required New status. Example: "maintenance"
     * @bodyParam notes string Additional notes. Example: "Scheduled maintenance"
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "status": "maintenance",
     *     "notes": "Scheduled maintenance"
     *   },
     *   "message": "Estado del pool actualizado exitosamente"
     * }
     */
    public function updateStatus(Request $request, EnergyPool $energyPool): JsonResponse
    {
        try {
            $request->validate([
                'status' => ['required', 'string', 'in:' . implode(',', array_keys(EnergyPool::getStatuses()))],
                'notes' => 'nullable|string|max:1000',
            ]);

            DB::beginTransaction();

            $energyPool->update([
                'status' => $request->status,
                'notes' => $request->notes ?: $energyPool->notes,
            ]);

            // Log activity
            Log::info('Energy pool status updated', [
                'pool_id' => $energyPool->id,
                'pool_number' => $energyPool->pool_number,
                'old_status' => $energyPool->getOriginal('status'),
                'new_status' => $request->status,
                'updated_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'data' => new EnergyPoolResource($energyPool),
                'message' => 'Estado del pool actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating energy pool status: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al actualizar el estado del pool',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Duplicate an energy pool.
     *
     * @authenticated
     * @urlParam energyPool integer required The ID of the energy pool to duplicate. Example: 1
     * @bodyParam pool_number string required New unique pool number. Example: "POOL-002"
     * @bodyParam name string New pool name. Example: "Copy of Solar Energy Pool"
     *
     * @response 200 {
     *   "data": {
     *     "id": 2,
     *     "pool_number": "POOL-002",
     *     "name": "Copy of Solar Energy Pool"
     *   },
     *   "message": "Pool de energía duplicado exitosamente"
     * }
     */
    public function duplicate(Request $request, EnergyPool $energyPool): JsonResponse
    {
        try {
            $request->validate([
                'pool_number' => 'required|string|max:255|unique:energy_pools,pool_number',
                'name' => 'required|string|max:255',
            ]);

            DB::beginTransaction();

            $duplicateData = $energyPool->toArray();
            unset($duplicateData['id'], $duplicateData['created_at'], $duplicateData['updated_at']);
            
            $duplicateData['pool_number'] = $request->pool_number;
            $duplicateData['name'] = $request->name;
            $duplicateData['status'] = 'planned';
            $duplicateData['created_by'] = auth()->id();
            $duplicateData['approved_at'] = null;
            $duplicateData['approved_by'] = null;

            $duplicatePool = EnergyPool::create($duplicateData);

            // Log activity
            Log::info('Energy pool duplicated', [
                'original_pool_id' => $energyPool->id,
                'duplicate_pool_id' => $duplicatePool->id,
                'duplicated_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'data' => new EnergyPoolResource($duplicatePool),
                'message' => 'Pool de energía duplicado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error duplicating energy pool: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al duplicar el pool de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get active energy pools.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "pool_number": "POOL-001",
     *       "name": "Solar Energy Pool",
     *       "status": "active"
     *     }
     *   ]
     * }
     */
    public function active(): JsonResponse
    {
        try {
            $activePools = EnergyPool::active()->get();

            return response()->json([
                'data' => EnergyPoolResource::collection($activePools)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching active energy pools: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al obtener pools activos',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get pools by type.
     *
     * @authenticated
     * @urlParam type string required Pool type. Example: "trading"
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "pool_number": "POOL-001",
     *       "name": "Solar Energy Pool",
     *       "pool_type": "trading"
     *     }
     *   ]
     * }
     */
    public function byType(string $type): JsonResponse
    {
        try {
            $pools = EnergyPool::byPoolType($type)->get();

            return response()->json([
                'data' => EnergyPoolResource::collection($pools)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy pools by type: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al obtener pools por tipo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get pools by energy category.
     *
     * @authenticated
     * @urlParam category string required Energy category. Example: "renewable"
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "pool_number": "POOL-001",
     *       "name": "Solar Energy Pool",
     *       "energy_category": "renewable"
     *     }
     *   ]
     * }
     */
    public function byCategory(string $category): JsonResponse
    {
        try {
            $pools = EnergyPool::byEnergyCategory($category)->get();

            return response()->json([
                'data' => EnergyPoolResource::collection($pools)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy pools by category: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al obtener pools por categoría',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get high efficiency pools.
     *
     * @authenticated
     * @queryParam min_efficiency float Minimum efficiency rating. Example: 80.0
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "pool_number": "POOL-001",
     *       "name": "Solar Energy Pool",
     *       "efficiency_rating": 85.50
     *     }
     *   ]
     * }
     */
    public function highEfficiency(Request $request): JsonResponse
    {
        try {
            $minEfficiency = $request->get('min_efficiency', 80);
            $pools = EnergyPool::highEfficiency($minEfficiency)->get();

            return response()->json([
                'data' => EnergyPoolResource::collection($pools)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching high efficiency energy pools: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al obtener pools de alta eficiencia',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get high availability pools.
     *
     * @authenticated
     * @queryParam min_availability float Minimum availability factor. Example: 90.0
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "pool_number": "POOL-001",
     *       "name": "Solar Energy Pool",
     *       "availability_factor": 92.00
     *     }
     *   ]
     * }
     */
    public function highAvailability(Request $request): JsonResponse
    {
        try {
            $minAvailability = $request->get('min_availability', 90);
            $pools = EnergyPool::highAvailability($minAvailability)->get();

            return response()->json([
                'data' => EnergyPoolResource::collection($pools)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching high availability energy pools: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al obtener pools de alta disponibilidad',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get pools by region.
     *
     * @authenticated
     * @urlParam region string required Region name. Example: "North"
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "pool_number": "POOL-001",
     *       "name": "Solar Energy Pool",
     *       "region": "North"
     *     }
     *   ]
     * }
     */
    public function byRegion(string $region): JsonResponse
    {
        try {
            $pools = EnergyPool::byRegion($region)->get();

            return response()->json([
                'data' => EnergyPoolResource::collection($pools)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy pools by region: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al obtener pools por región',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get pools by country.
     *
     * @authenticated
     * @urlParam country string required Country name. Example: "Spain"
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "pool_number": "POOL-001",
     *       "name": "Solar Energy Pool",
     *       "country": "Spain"
     *     }
     *   ]
     * }
     */
    public function byCountry(string $country): JsonResponse
    {
        try {
            $pools = EnergyPool::byCountry($country)->get();

            return response()->json([
                'data' => EnergyPoolResource::collection($pools)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy pools by country: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al obtener pools por país',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get pools pending approval.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "pool_number": "POOL-001",
     *       "name": "Solar Energy Pool",
     *       "approved_at": null
     *     }
     *   ]
     * }
     */
    public function pendingApproval(): JsonResponse
    {
        try {
            $pools = EnergyPool::pendingApproval()->get();

            return response()->json([
                'data' => EnergyPoolResource::collection($pools)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching pending approval energy pools: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al obtener pools pendientes de aprobación',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get approved pools.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "pool_number": "POOL-001",
     *       "name": "Solar Energy Pool",
     *       "approved_at": "2024-01-15T10:00:00Z"
     *     }
     *   ]
     * }
     */
    public function approved(): JsonResponse
    {
        try {
            $pools = EnergyPool::approved()->get();

            return response()->json([
                'data' => EnergyPoolResource::collection($pools)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching approved energy pools: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al obtener pools aprobados',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
