<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\ConsumptionPoint\StoreConsumptionPointRequest;
use App\Http\Requests\Api\V1\ConsumptionPoint\UpdateConsumptionPointRequest;
use App\Http\Resources\Api\V1\ConsumptionPoint\ConsumptionPointResource;
use App\Http\Resources\Api\V1\ConsumptionPoint\ConsumptionPointCollection;
use App\Models\ConsumptionPoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @group Consumption Point Management
 *
 * APIs for managing energy consumption points
 */
class ConsumptionPointController extends \App\Http\Controllers\Controller
{
    /**
     * Display a listing of consumption points.
     *
     * @authenticated
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam search string Search in name, description, point_number, meter_number. Example: "Residential Point"
     * @queryParam point_type string Filter by point type. Example: "residential"
     * @queryParam status string Filter by status. Example: "active"
     * @queryParam customer_id integer Filter by customer ID. Example: 1
     * @queryParam installation_id integer Filter by installation ID. Example: 1
     * @queryParam meter_type string Filter by meter type. Example: "smart"
     * @queryParam connection_type string Filter by connection type. Example: "grid"
     * @queryParam peak_demand_min float Minimum peak demand in kW. Example: 10.0
     * @queryParam peak_demand_max float Maximum peak demand in kW. Example: 100.0
     * @queryParam annual_consumption_min float Minimum annual consumption in kWh. Example: 1000.0
     * @queryParam annual_consumption_max float Maximum annual consumption in kWh. Example: 10000.0
     * @queryParam connection_date_from date Filter by connection date from. Example: "2024-01-01"
     * @queryParam connection_date_to date Filter by connection date to. Example: "2024-12-31"
     * @queryParam sort_by string Sort field. Example: "name"
     * @queryParam sort_direction string Sort direction (asc/desc). Example: "asc"
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "point_number": "CP-001",
     *       "name": "Residential Point",
     *       "point_type": "residential",
     *       "status": "active",
     *       "peak_demand_kw": 15.5,
     *       "annual_consumption_kwh": 5000.0,
     *       "is_connected": true,
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
            $query = ConsumptionPoint::with(['customer', 'installation', 'managedBy', 'createdBy', 'approvedBy']);

            // Filtros de búsqueda
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('point_number', 'like', "%{$search}%")
                      ->orWhere('meter_number', 'like', "%{$search}%")
                      ->orWhere('location_address', 'like', "%{$search}%");
                });
            }

            // Filtros por tipo de punto
            if ($request->filled('point_type')) {
                $query->where('point_type', $request->point_type);
            }

            // Filtros por estado
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filtros por cliente
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            // Filtros por instalación
            if ($request->filled('installation_id')) {
                $query->where('installation_id', $request->installation_id);
            }

            // Filtros por tipo de medidor
            if ($request->filled('meter_type')) {
                $query->where('meter_type', $request->meter_type);
            }

            // Filtros por tipo de conexión
            if ($request->filled('connection_type')) {
                $query->where('connection_type', $request->connection_type);
            }

            // Filtros por demanda pico
            if ($request->filled('peak_demand_min')) {
                $query->where('peak_demand_kw', '>=', $request->peak_demand_min);
            }

            if ($request->filled('peak_demand_max')) {
                $query->where('peak_demand_kw', '<=', $request->peak_demand_max);
            }

            // Filtros por consumo anual
            if ($request->filled('annual_consumption_min')) {
                $query->where('annual_consumption_kwh', '>=', $request->annual_consumption_min);
            }

            if ($request->filled('annual_consumption_max')) {
                $query->where('annual_consumption_kwh', '<=', $request->annual_consumption_max);
            }

            // Filtros por fechas de conexión
            if ($request->filled('connection_date_from')) {
                $query->whereDate('connection_date', '>=', $request->connection_date_from);
            }

            if ($request->filled('connection_date_to')) {
                $query->whereDate('connection_date', '<=', $request->connection_date_to);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            if (in_array($sortBy, ['name', 'point_number', 'peak_demand_kw', 'annual_consumption_kwh', 'connection_date', 'created_at'])) {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Paginación
            $perPage = min($request->get('per_page', 15), 100);
            $consumptionPoints = $query->paginate($perPage);

            return response()->json([
                'data' => ConsumptionPointResource::collection($consumptionPoints),
                'meta' => [
                    'current_page' => $consumptionPoints->currentPage(),
                    'total' => $consumptionPoints->total(),
                    'per_page' => $consumptionPoints->perPage(),
                    'last_page' => $consumptionPoints->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching consumption points: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los puntos de consumo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created consumption point.
     *
     * @authenticated
     * @bodyParam point_number string required The point number. Example: "CP-001"
     * @bodyParam name string required The name of the consumption point. Example: "Residential Point"
     * @bodyParam description string The description of the consumption point. Example: "Residential consumption point"
     * @bodyParam point_type string required The type of consumption point. Example: "residential"
     * @bodyParam status string required The status of the consumption point. Example: "active"
     * @bodyParam customer_id integer required The customer ID. Example: 1
     * @bodyParam installation_id integer The installation ID. Example: 1
     * @bodyParam location_address string The location address. Example: "123 Main St"
     * @bodyParam latitude decimal The latitude coordinate. Example: 40.4168
     * @bodyParam longitude decimal The longitude coordinate. Example: -3.7038
     * @bodyParam peak_demand_kw decimal The peak demand in kW. Example: 15.5
     * @bodyParam average_demand_kw decimal The average demand in kW. Example: 8.2
     * @bodyParam annual_consumption_kwh decimal The annual consumption in kWh. Example: 5000.0
     * @bodyParam connection_date date required The connection date. Example: "2024-01-15"
     * @bodyParam meter_number string The meter number. Example: "MTR-001"
     * @bodyParam meter_type string The meter type. Example: "smart"
     * @bodyParam voltage_level decimal The voltage level. Example: 230.0
     * @bodyParam current_rating decimal The current rating. Example: 63.0
     * @bodyParam phase_type string The phase type. Example: "single"
     * @bodyParam connection_type string The connection type. Example: "grid"
     *
     * @response 201 {
     *   "message": "Punto de consumo creado exitosamente",
     *   "data": {
     *     "id": 1,
     *     "point_number": "CP-001",
     *     "name": "Residential Point",
     *     "point_type": "residential",
     *     "status": "active",
     *     "created_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "Los datos proporcionados no son válidos",
     *   "errors": {
     *     "point_number": ["El campo número de punto es obligatorio"],
     *     "name": ["El campo nombre es obligatorio"]
     *   }
     * }
     */
    public function store(StoreConsumptionPointRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $consumptionPoint = ConsumptionPoint::create($request->validated());

            DB::commit();

            Log::info('Consumption point created', [
                'consumption_point_id' => $consumptionPoint->id,
                'user_id' => auth()->id(),
                'point_number' => $consumptionPoint->point_number
            ]);

            return response()->json([
                'message' => 'Punto de consumo creado exitosamente',
                'data' => new ConsumptionPointResource($consumptionPoint)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating consumption point: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al crear el punto de consumo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified consumption point.
     *
     * @authenticated
     * @urlParam id integer required The consumption point ID. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "point_number": "CP-001",
     *     "name": "Residential Point",
     *     "description": "Residential consumption point",
     *     "point_type": "residential",
     *     "status": "active",
     *     "peak_demand_kw": 15.5,
     *     "annual_consumption_kwh": 5000.0,
     *     "is_connected": true,
     *     "created_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "Punto de consumo no encontrado"
     * }
     */
    public function show(ConsumptionPoint $consumptionPoint): JsonResponse
    {
        try {
            $consumptionPoint->load(['customer', 'installation', 'managedBy', 'createdBy', 'approvedBy', 'meters', 'readings']);

            return response()->json([
                'data' => new ConsumptionPointResource($consumptionPoint)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching consumption point: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener el punto de consumo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified consumption point.
     *
     * @authenticated
     * @urlParam id integer required The consumption point ID. Example: 1
     * @bodyParam name string The name of the consumption point. Example: "Updated Residential Point"
     * @bodyParam description string The description of the consumption point. Example: "Updated description"
     * @bodyParam point_type string The type of consumption point. Example: "residential"
     * @bodyParam status string The status of the consumption point. Example: "active"
     * @bodyParam peak_demand_kw decimal The peak demand in kW. Example: 18.0
     * @bodyParam average_demand_kw decimal The average demand in kW. Example: 9.5
     * @bodyParam annual_consumption_kwh decimal The annual consumption in kWh. Example: 5500.0
     *
     * @response 200 {
     *   "message": "Punto de consumo actualizado exitosamente",
     *   "data": {
     *     "id": 1,
     *     "name": "Updated Residential Point",
     *     "status": "active",
     *     "updated_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "Punto de consumo no encontrado"
     * }
     *
     * @response 422 {
     *   "message": "Los datos proporcionados no son válidos",
     *   "errors": {
     *     "peak_demand_kw": ["La demanda pico debe ser un número positivo"]
     *   }
     * }
     */
    public function update(UpdateConsumptionPointRequest $request, ConsumptionPoint $consumptionPoint): JsonResponse
    {
        try {
            DB::beginTransaction();

            $consumptionPoint->update($request->validated());

            DB::commit();

            Log::info('Consumption point updated', [
                'consumption_point_id' => $consumptionPoint->id,
                'user_id' => auth()->id(),
                'point_number' => $consumptionPoint->point_number
            ]);

            return response()->json([
                'message' => 'Punto de consumo actualizado exitosamente',
                'data' => new ConsumptionPointResource($consumptionPoint)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating consumption point: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al actualizar el punto de consumo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified consumption point.
     *
     * @authenticated
     * @urlParam id integer required The consumption point ID. Example: 1
     *
     * @response 200 {
     *   "message": "Punto de consumo eliminado exitosamente"
     * }
     *
     * @response 404 {
     *   "message": "Punto de consumo no encontrado"
     * }
     */
    public function destroy(ConsumptionPoint $consumptionPoint): JsonResponse
    {
        try {
            DB::beginTransaction();

            $pointNumber = $consumptionPoint->point_number;
            $consumptionPointId = $consumptionPoint->id;

            $consumptionPoint->delete();

            DB::commit();

            Log::info('Consumption point deleted', [
                'consumption_point_id' => $consumptionPointId,
                'user_id' => auth()->id(),
                'point_number' => $pointNumber
            ]);

            return response()->json([
                'message' => 'Punto de consumo eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting consumption point: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al eliminar el punto de consumo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get consumption point statistics.
     *
     * @authenticated
     * @queryParam point_type string Filter by point type. Example: "residential"
     * @queryParam status string Filter by status. Example: "active"
     * @queryParam customer_id integer Filter by customer ID. Example: 1
     * @queryParam installation_id integer Filter by installation ID. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "total_points": 25,
     *     "active_points": 20,
     *     "maintenance_points": 3,
     *     "disconnected_points": 2,
     *     "total_peak_demand_kw": 1250.0,
     *     "total_annual_consumption_kwh": 125000.0,
     *     "average_peak_demand_kw": 50.0,
     *     "average_annual_consumption_kwh": 5000.0,
     *     "points_by_type": {
     *       "residential": 15,
     *       "commercial": 8,
     *       "industrial": 2
     *     },
     *     "points_by_status": {
     *       "active": 20,
     *       "maintenance": 3,
     *       "disconnected": 2
     *     }
     *   }
     * }
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $query = ConsumptionPoint::query();

            // Filtros
            if ($request->filled('point_type')) {
                $query->where('point_type', $request->point_type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            if ($request->filled('installation_id')) {
                $query->where('installation_id', $request->installation_id);
            }

            $totalPoints = $query->count();
            $activePoints = (clone $query)->where('status', 'active')->count();
            $maintenancePoints = (clone $query)->where('status', 'maintenance')->count();
            $disconnectedPoints = (clone $query)->where('status', 'disconnected')->count();
            $totalPeakDemand = (clone $query)->sum('peak_demand_kw');
            $totalAnnualConsumption = (clone $query)->sum('annual_consumption_kwh');
            $averagePeakDemand = (clone $query)->avg('peak_demand_kw');
            $averageAnnualConsumption = (clone $query)->avg('annual_consumption_kwh');

            // Puntos por tipo
            $pointsByType = (clone $query)
                ->selectRaw('point_type, COUNT(*) as count')
                ->groupBy('point_type')
                ->pluck('count', 'point_type')
                ->toArray();

            // Puntos por estado
            $pointsByStatus = (clone $query)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            return response()->json([
                'data' => [
                    'total_points' => $totalPoints,
                    'active_points' => $activePoints,
                    'maintenance_points' => $maintenancePoints,
                    'disconnected_points' => $disconnectedPoints,
                    'total_peak_demand_kw' => $totalPeakDemand,
                    'total_annual_consumption_kwh' => $totalAnnualConsumption,
                    'average_peak_demand_kw' => round($averagePeakDemand, 1),
                    'average_annual_consumption_kwh' => round($averageAnnualConsumption, 1),
                    'points_by_type' => $pointsByType,
                    'points_by_status' => $pointsByStatus,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching consumption point statistics: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las estadísticas de puntos de consumo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get consumption point types.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": {
     *     "residential": "Residencial",
     *     "commercial": "Comercial",
     *     "industrial": "Industrial",
     *     "agricultural": "Agrícola",
     *     "public": "Público",
     *     "street_lighting": "Alumbrado Público",
     *     "charging_station": "Estación de Carga",
     *     "other": "Otro"
     *   }
     * }
     */
    public function types(): JsonResponse
    {
        try {
            return response()->json([
                'data' => ConsumptionPoint::getPointTypes()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching consumption point types: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los tipos de puntos de consumo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get consumption point statuses.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": {
     *     "active": "Activo",
     *     "inactive": "Inactivo",
     *     "maintenance": "Mantenimiento",
     *     "disconnected": "Desconectado",
     *     "planned": "Planificado",
     *     "decommissioned": "Desmantelado"
     *   }
     * }
     */
    public function statuses(): JsonResponse
    {
        try {
            return response()->json([
                'data' => ConsumptionPoint::getStatuses()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching consumption point statuses: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los estados de puntos de consumo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Toggle consumption point status.
     *
     * @authenticated
     * @urlParam id integer required The consumption point ID. Example: 1
     * @bodyParam status string required The new status. Example: "inactive"
     *
     * @response 200 {
     *   "message": "Estado del punto de consumo actualizado exitosamente",
     *   "data": {
     *     "id": 1,
     *     "status": "inactive",
     *     "updated_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     */
    public function updateStatus(Request $request, ConsumptionPoint $consumptionPoint): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|string|in:' . implode(',', array_keys(ConsumptionPoint::getStatuses()))
            ]);

            $oldStatus = $consumptionPoint->status;
            $consumptionPoint->update(['status' => $request->status]);

            Log::info('Consumption point status updated', [
                'consumption_point_id' => $consumptionPoint->id,
                'user_id' => auth()->id(),
                'old_status' => $oldStatus,
                'new_status' => $request->status
            ]);

            return response()->json([
                'message' => 'Estado del punto de consumo actualizado exitosamente',
                'data' => [
                    'id' => $consumptionPoint->id,
                    'status' => $consumptionPoint->status,
                    'updated_at' => $consumptionPoint->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating consumption point status: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al actualizar el estado del punto de consumo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Duplicate a consumption point.
     *
     * @authenticated
     * @urlParam id integer required The consumption point ID. Example: 1
     * @bodyParam point_number string The new point number. Example: "CP-002"
     * @bodyParam name string The new name for the duplicated consumption point. Example: "Residential Point - Copy"
     *
     * @response 200 {
     *   "message": "Punto de consumo duplicado exitosamente",
     *   "data": {
     *     "id": 2,
     *     "point_number": "CP-002",
     *     "name": "Residential Point - Copy",
     *     "status": "planned",
     *     "created_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     */
    public function duplicate(Request $request, ConsumptionPoint $consumptionPoint): JsonResponse
    {
        try {
            $request->validate([
                'point_number' => 'nullable|string|max:255|unique:consumption_points,point_number',
                'name' => 'nullable|string|max:255'
            ]);

            $newConsumptionPoint = $consumptionPoint->replicate();
            $newConsumptionPoint->point_number = $request->point_number ?? $consumptionPoint->point_number . '_copy';
            $newConsumptionPoint->name = $request->name ?? $consumptionPoint->name . ' (Copia)';
            $newConsumptionPoint->status = 'planned';
            $newConsumptionPoint->connection_date = null;
            $newConsumptionPoint->disconnection_date = null;
            $newConsumptionPoint->save();

            Log::info('Consumption point duplicated', [
                'original_consumption_point_id' => $consumptionPoint->id,
                'new_consumption_point_id' => $newConsumptionPoint->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Punto de consumo duplicado exitosamente',
                'data' => new ConsumptionPointResource($newConsumptionPoint)
            ]);

        } catch (\Exception $e) {
            Log::error('Error duplicating consumption point: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al duplicar el punto de consumo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get active consumption points.
     *
     * @authenticated
     * @queryParam limit integer Number of active points to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "point_number": "CP-001",
     *       "name": "Residential Point",
     *       "point_type": "residential",
     *       "status": "active",
     *       "peak_demand_kw": 15.5,
     *       "annual_consumption_kwh": 5000.0
     *     }
     *   ]
     * }
     */
    public function active(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $activePoints = ConsumptionPoint::where('status', 'active')
                ->with(['customer', 'installation'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => ConsumptionPointResource::collection($activePoints)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching active consumption points: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los puntos de consumo activos',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get maintenance consumption points.
     *
     * @authenticated
     * @queryParam limit integer Number of maintenance points to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 2,
     *       "point_number": "CP-002",
     *       "name": "Commercial Point",
     *       "point_type": "commercial",
     *       "status": "maintenance",
     *       "peak_demand_kw": 45.0,
     *       "annual_consumption_kwh": 15000.0
     *     }
     *   ]
     * }
     */
    public function maintenance(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $maintenancePoints = ConsumptionPoint::where('status', 'maintenance')
                ->with(['customer', 'installation'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => ConsumptionPointResource::collection($maintenancePoints)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching maintenance consumption points: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los puntos de consumo en mantenimiento',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get disconnected consumption points.
     *
     * @authenticated
     * @queryParam limit integer Number of disconnected points to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 3,
     *       "point_number": "CP-003",
     *       "name": "Industrial Point",
     *       "point_type": "industrial",
     *       "status": "disconnected",
     *       "peak_demand_kw": 200.0,
     *       "annual_consumption_kwh": 50000.0
     *     }
     *   ]
     * }
     */
    public function disconnected(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $disconnectedPoints = ConsumptionPoint::where('status', 'disconnected')
                ->with(['customer', 'installation'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => ConsumptionPointResource::collection($disconnectedPoints)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching disconnected consumption points: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los puntos de consumo desconectados',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get consumption points by type.
     *
     * @authenticated
     * @urlParam type string required The point type. Example: "residential"
     * @queryParam limit integer Number of points to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "point_number": "CP-001",
     *       "name": "Residential Point",
     *       "point_type": "residential",
     *       "status": "active",
     *       "peak_demand_kw": 15.5
     *     }
     *   ]
     * }
     */
    public function byType(Request $request, string $type): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $points = ConsumptionPoint::where('point_type', $type)
                ->with(['customer', 'installation'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => ConsumptionPointResource::collection($points)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching consumption points by type: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los puntos de consumo por tipo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get consumption points by customer.
     *
     * @authenticated
     * @urlParam customer_id integer required The customer ID. Example: 1
     * @queryParam limit integer Number of points to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "point_number": "CP-001",
     *       "name": "Residential Point",
     *       "point_type": "residential",
     *       "status": "active",
     *       "customer_id": 1
     *     }
     *   ]
     * }
     */
    public function byCustomer(Request $request, int $customerId): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $points = ConsumptionPoint::where('customer_id', $customerId)
                ->with(['installation'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => ConsumptionPointResource::collection($points)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching consumption points by customer: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los puntos de consumo por cliente',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get consumption points by installation.
     *
     * @authenticated
     * @urlParam installation_id integer required The installation ID. Example: 1
     * @queryParam limit integer Number of points to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "point_number": "CP-001",
     *       "name": "Residential Point",
     *       "point_type": "residential",
     *       "status": "active",
     *       "installation_id": 1
     *     }
     *   ]
     * }
     */
    public function byInstallation(Request $request, int $installationId): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $points = ConsumptionPoint::where('installation_id', $installationId)
                ->with(['customer'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => ConsumptionPointResource::collection($points)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching consumption points by installation: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los puntos de consumo por instalación',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get high consumption points.
     *
     * @authenticated
     * @queryParam limit integer Number of high consumption points to return. Example: 10
     * @queryParam threshold float Minimum annual consumption threshold in kWh. Example: 10000.0
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 3,
     *       "point_number": "CP-003",
     *       "name": "Industrial Point",
     *       "point_type": "industrial",
     *       "status": "active",
     *       "peak_demand_kw": 200.0,
     *       "annual_consumption_kwh": 50000.0
     *     }
     *   ]
     * }
     */
    public function highConsumption(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            $threshold = $request->get('threshold', 10000.0);
            
            $highConsumptionPoints = ConsumptionPoint::where('annual_consumption_kwh', '>=', $threshold)
                ->where('status', 'active')
                ->with(['customer', 'installation'])
                ->orderBy('annual_consumption_kwh', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => ConsumptionPointResource::collection($highConsumptionPoints)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching high consumption points: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los puntos de consumo de alto consumo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get consumption points that need meter calibration.
     *
     * @authenticated
     * @queryParam limit integer Number of points to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 4,
     *       "point_number": "CP-004",
     *       "name": "Commercial Point",
     *       "point_type": "commercial",
     *       "status": "active",
     *       "meter_number": "MTR-004",
     *       "meter_next_calibration_date": "2024-01-10"
     *     }
     *   ]
     * }
     */
    public function needsCalibration(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $pointsNeedingCalibration = ConsumptionPoint::whereNotNull('meter_next_calibration_date')
                ->where('meter_next_calibration_date', '<=', now())
                ->where('status', 'active')
                ->with(['customer', 'installation'])
                ->orderBy('meter_next_calibration_date', 'asc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => ConsumptionPointResource::collection($pointsNeedingCalibration)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching consumption points needing calibration: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los puntos de consumo que necesitan calibración',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
