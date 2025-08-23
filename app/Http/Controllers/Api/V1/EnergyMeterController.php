<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\EnergyMeter\StoreEnergyMeterRequest;
use App\Http\Requests\Api\V1\EnergyMeter\UpdateEnergyMeterRequest;
use App\Http\Resources\Api\V1\EnergyMeter\EnergyMeterResource;
use App\Http\Resources\Api\V1\EnergyMeter\EnergyMeterCollection;
use App\Models\EnergyMeter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @group Energy Meter Management
 *
 * APIs for managing energy meters
 */
class EnergyMeterController extends Controller
{
    /**
     * Display a listing of energy meters.
     *
     * @authenticated
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam search string Search in name, description, meter_number, serial_number. Example: "Smart Meter"
     * @queryParam meter_type string Filter by meter type. Example: "smart_meter"
     * @queryParam status string Filter by status. Example: "active"
     * @queryParam meter_category string Filter by meter category. Example: "electricity"
     * @queryParam manufacturer string Filter by manufacturer. Example: "Siemens"
     * @queryParam model string Filter by model. Example: "SM630"
     * @queryParam customer_id integer Filter by customer ID. Example: 1
     * @queryParam installation_id integer Filter by installation ID. Example: 1
     * @queryParam consumption_point_id integer Filter by consumption point ID. Example: 1
     * @queryParam is_smart_meter boolean Filter by smart meter status. Example: true
     * @queryParam has_remote_reading boolean Filter by remote reading capability. Example: true
     * @queryParam has_two_way_communication boolean Filter by two-way communication capability. Example: true
     * @queryParam accuracy_class_min float Minimum accuracy class. Example: 0.5
     * @queryParam accuracy_class_max float Maximum accuracy class. Example: 2.0
     * @queryParam installation_date_from date Filter by installation date from. Example: "2024-01-01"
     * @queryParam installation_date_to date Filter by installation date to. Example: "2024-12-31"
     * @queryParam next_calibration_date_from date Filter by next calibration date from. Example: "2024-01-01"
     * @queryParam next_calibration_date_to date Filter by next calibration date to. Example: "2024-12-31"
     * @queryParam sort_by string Sort field. Example: "name"
     * @queryParam sort_direction string Sort direction (asc/desc). Example: "asc"
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "meter_number": "MTR-001",
     *       "name": "Smart Meter",
     *       "meter_type": "smart_meter",
     *       "status": "active",
     *       "meter_category": "electricity",
     *       "is_smart_meter": true,
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
            $query = EnergyMeter::with(['installation', 'consumptionPoint', 'customer', 'managedBy', 'createdBy', 'approvedBy']);

            // Filtros de búsqueda
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('meter_number', 'like', "%{$search}%")
                      ->orWhere('serial_number', 'like', "%{$search}%")
                      ->orWhere('manufacturer', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%");
                });
            }

            // Filtros por tipo de medidor
            if ($request->filled('meter_type')) {
                $query->where('meter_type', $request->meter_type);
            }

            // Filtros por estado
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filtros por categoría
            if ($request->filled('meter_category')) {
                $query->where('meter_category', $request->meter_category);
            }

            // Filtros por fabricante y modelo
            if ($request->filled('manufacturer')) {
                $query->where('manufacturer', $request->manufacturer);
            }

            if ($request->filled('model')) {
                $query->where('model', $request->model);
            }

            // Filtros por cliente, instalación y punto de consumo
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            if ($request->filled('installation_id')) {
                $query->where('installation_id', $request->installation_id);
            }

            if ($request->filled('consumption_point_id')) {
                $query->where('consumption_point_id', $request->consumption_point_id);
            }

            // Filtros por capacidades
            if ($request->filled('is_smart_meter')) {
                $query->where('is_smart_meter', $request->boolean('is_smart_meter'));
            }

            if ($request->filled('has_remote_reading')) {
                $query->where('has_remote_reading', $request->boolean('has_remote_reading'));
            }

            if ($request->filled('has_two_way_communication')) {
                $query->where('has_two_way_communication', $request->boolean('has_two_way_communication'));
            }

            // Filtros por clase de precisión
            if ($request->filled('accuracy_class_min')) {
                $query->where('accuracy_class', '>=', $request->accuracy_class_min);
            }

            if ($request->filled('accuracy_class_max')) {
                $query->where('accuracy_class', '<=', $request->accuracy_class_max);
            }

            // Filtros por fechas
            if ($request->filled('installation_date_from')) {
                $query->whereDate('installation_date', '>=', $request->installation_date_from);
            }

            if ($request->filled('installation_date_to')) {
                $query->whereDate('installation_date', '<=', $request->installation_date_to);
            }

            if ($request->filled('next_calibration_date_from')) {
                $query->whereDate('next_calibration_date', '>=', $request->next_calibration_date_from);
            }

            if ($request->filled('next_calibration_date_to')) {
                $query->whereDate('next_calibration_date', '<=', $request->next_calibration_date_to);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            if (in_array($sortBy, ['name', 'meter_number', 'serial_number', 'installation_date', 'accuracy_class', 'created_at'])) {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Paginación
            $perPage = min($request->get('per_page', 15), 100);
            $energyMeters = $query->paginate($perPage);

            return response()->json([
                'data' => EnergyMeterResource::collection($energyMeters),
                'meta' => [
                    'current_page' => $energyMeters->currentPage(),
                    'total' => $energyMeters->total(),
                    'per_page' => $energyMeters->perPage(),
                    'last_page' => $energyMeters->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy meters: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los medidores de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created energy meter.
     *
     * @authenticated
     * @bodyParam meter_number string required The meter number. Example: "MTR-001"
     * @bodyParam name string required The name of the energy meter. Example: "Smart Meter"
     * @bodyParam description string The description of the energy meter. Example: "Smart electricity meter"
     * @bodyParam meter_type string required The type of meter. Example: "smart_meter"
     * @bodyParam status string required The status of the meter. Example: "active"
     * @bodyParam meter_category string required The category of the meter. Example: "electricity"
     * @bodyParam manufacturer string The manufacturer of the meter. Example: "Siemens"
     * @bodyParam model string The model of the meter. Example: "SM630"
     * @bodyParam serial_number string required The serial number. Example: "SN123456"
     * @bodyParam installation_id integer The installation ID. Example: 1
     * @bodyParam consumption_point_id integer The consumption point ID. Example: 1
     * @bodyParam customer_id integer required The customer ID. Example: 1
     * @bodyParam installation_date date required The installation date. Example: "2024-01-15"
     * @bodyParam commissioning_date date The commissioning date. Example: "2024-01-16"
     * @bodyParam next_calibration_date date The next calibration date. Example: "2025-01-15"
     * @bodyParam voltage_rating decimal The voltage rating. Example: 230.0
     * @bodyParam current_rating decimal The current rating. Example: 63.0
     * @bodyParam accuracy_class decimal The accuracy class. Example: 0.5
     * @bodyParam is_smart_meter boolean Whether it's a smart meter. Example: true
     * @bodyParam has_remote_reading boolean Whether it has remote reading. Example: true
     * @bodyParam has_two_way_communication boolean Whether it has two-way communication. Example: true
     *
     * @response 201 {
     *   "message": "Medidor de energía creado exitosamente",
     *   "data": {
     *     "id": 1,
     *     "meter_number": "MTR-001",
     *     "name": "Smart Meter",
     *     "meter_type": "smart_meter",
     *     "status": "active",
     *     "created_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     */
    public function store(StoreEnergyMeterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $energyMeter = EnergyMeter::create($request->validated());

            DB::commit();

            Log::info('Energy meter created', [
                'energy_meter_id' => $energyMeter->id,
                'user_id' => auth()->id(),
                'meter_number' => $energyMeter->meter_number
            ]);

            return response()->json([
                'message' => 'Medidor de energía creado exitosamente',
                'data' => new EnergyMeterResource($energyMeter)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating energy meter: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al crear el medidor de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified energy meter.
     *
     * @authenticated
     * @urlParam id integer required The energy meter ID. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "meter_number": "MTR-001",
     *     "name": "Smart Meter",
     *     "description": "Smart electricity meter",
     *     "meter_type": "smart_meter",
     *     "status": "active",
     *     "meter_category": "electricity",
     *     "is_smart_meter": true,
     *     "created_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     */
    public function show(EnergyMeter $energyMeter): JsonResponse
    {
        try {
            $energyMeter->load(['installation', 'consumptionPoint', 'customer', 'managedBy', 'createdBy', 'approvedBy', 'readings', 'forecasts']);

            return response()->json([
                'data' => new EnergyMeterResource($energyMeter)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy meter: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener el medidor de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified energy meter.
     *
     * @authenticated
     * @urlParam id integer required The energy meter ID. Example: 1
     * @bodyParam name string The name of the energy meter. Example: "Updated Smart Meter"
     * @bodyParam description string The description of the energy meter. Example: "Updated description"
     * @bodyParam status string The status of the meter. Example: "maintenance"
     * @bodyParam next_calibration_date date The next calibration date. Example: "2025-02-15"
     * @bodyParam accuracy_class decimal The accuracy class. Example: 0.3
     *
     * @response 200 {
     *   "message": "Medidor de energía actualizado exitosamente",
     *   "data": {
     *     "id": 1,
     *     "name": "Updated Smart Meter",
     *     "status": "maintenance",
     *     "updated_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     */
    public function update(UpdateEnergyMeterRequest $request, EnergyMeter $energyMeter): JsonResponse
    {
        try {
            DB::beginTransaction();

            $energyMeter->update($request->validated());

            DB::commit();

            Log::info('Energy meter updated', [
                'energy_meter_id' => $energyMeter->id,
                'user_id' => auth()->id(),
                'meter_number' => $energyMeter->meter_number
            ]);

            return response()->json([
                'message' => 'Medidor de energía actualizado exitosamente',
                'data' => new EnergyMeterResource($energyMeter)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating energy meter: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al actualizar el medidor de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified energy meter.
     *
     * @authenticated
     * @urlParam id integer required The energy meter ID. Example: 1
     *
     * @response 200 {
     *   "message": "Medidor de energía eliminado exitosamente"
     * }
     */
    public function destroy(EnergyMeter $energyMeter): JsonResponse
    {
        try {
            DB::beginTransaction();

            $meterNumber = $energyMeter->meter_number;
            $energyMeterId = $energyMeter->id;

            $energyMeter->delete();

            DB::commit();

            Log::info('Energy meter deleted', [
                'energy_meter_id' => $energyMeterId,
                'user_id' => auth()->id(),
                'meter_number' => $meterNumber
            ]);

            return response()->json([
                'message' => 'Medidor de energía eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting energy meter: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al eliminar el medidor de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get energy meter statistics.
     *
     * @authenticated
     * @queryParam meter_type string Filter by meter type. Example: "smart_meter"
     * @queryParam status string Filter by status. Example: "active"
     * @queryParam meter_category string Filter by meter category. Example: "electricity"
     * @queryParam customer_id integer Filter by customer ID. Example: 1
     * @queryParam installation_id integer Filter by installation ID. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "total_meters": 25,
     *     "active_meters": 20,
     *     "maintenance_meters": 3,
     *     "faulty_meters": 2,
     *     "smart_meters": 15,
     *     "meters_by_type": {
     *       "smart_meter": 15,
     *       "digital_meter": 8,
     *       "analog_meter": 2
     *     },
     *     "meters_by_category": {
     *       "electricity": 20,
     *       "water": 3,
     *       "gas": 2
     *     },
     *     "meters_by_status": {
     *       "active": 20,
     *       "maintenance": 3,
     *       "faulty": 2
     *     }
     *   }
     * }
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $query = EnergyMeter::query();

            // Filtros
            if ($request->filled('meter_type')) {
                $query->where('meter_type', $request->meter_type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('meter_category')) {
                $query->where('meter_category', $request->meter_category);
            }

            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            if ($request->filled('installation_id')) {
                $query->where('installation_id', $request->installation_id);
            }

            $totalMeters = $query->count();
            $activeMeters = (clone $query)->where('status', 'active')->count();
            $maintenanceMeters = (clone $query)->where('status', 'maintenance')->count();
            $faultyMeters = (clone $query)->where('status', 'faulty')->count();
            $smartMeters = (clone $query)->where('is_smart_meter', true)->count();

            // Medidores por tipo
            $metersByType = (clone $query)
                ->selectRaw('meter_type, COUNT(*) as count')
                ->groupBy('meter_type')
                ->pluck('count', 'meter_type')
                ->toArray();

            // Medidores por categoría
            $metersByCategory = (clone $query)
                ->selectRaw('meter_category, COUNT(*) as count')
                ->groupBy('meter_category')
                ->pluck('count', 'meter_category')
                ->toArray();

            // Medidores por estado
            $metersByStatus = (clone $query)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            return response()->json([
                'data' => [
                    'total_meters' => $totalMeters,
                    'active_meters' => $activeMeters,
                    'maintenance_meters' => $maintenanceMeters,
                    'faulty_meters' => $faultyMeters,
                    'smart_meters' => $smartMeters,
                    'meters_by_type' => $metersByType,
                    'meters_by_category' => $metersByCategory,
                    'meters_by_status' => $metersByStatus,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy meter statistics: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las estadísticas de medidores de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get meter types.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": {
     *     "smart_meter": "Medidor Inteligente",
     *     "digital_meter": "Medidor Digital",
     *     "analog_meter": "Medidor Analógico",
     *     "prepaid_meter": "Medidor Prepago",
     *     "postpaid_meter": "Medidor Postpago"
     *   }
     * }
     */
    public function types(): JsonResponse
    {
        try {
            return response()->json([
                'data' => EnergyMeter::getMeterTypes()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching meter types: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los tipos de medidores',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get meter statuses.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": {
     *     "active": "Activo",
     *     "inactive": "Inactivo",
     *     "maintenance": "Mantenimiento",
     *     "faulty": "Defectuoso",
     *     "replaced": "Reemplazado"
     *   }
     * }
     */
    public function statuses(): JsonResponse
    {
        try {
            return response()->json([
                'data' => EnergyMeter::getStatuses()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching meter statuses: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los estados de medidores',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get meter categories.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": {
     *     "electricity": "Electricidad",
     *     "water": "Agua",
     *     "gas": "Gas",
     *     "heat": "Calor",
     *     "steam": "Vapor"
     *   }
     * }
     */
    public function categories(): JsonResponse
    {
        try {
            return response()->json([
                'data' => EnergyMeter::getMeterCategories()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching meter categories: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las categorías de medidores',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update meter status.
     *
     * @authenticated
     * @urlParam id integer required The energy meter ID. Example: 1
     * @bodyParam status string required The new status. Example: "maintenance"
     *
     * @response 200 {
     *   "message": "Estado del medidor actualizado exitosamente",
     *   "data": {
     *     "id": 1,
     *     "status": "maintenance",
     *     "updated_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     */
    public function updateStatus(Request $request, EnergyMeter $energyMeter): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|string|in:' . implode(',', array_keys(EnergyMeter::getStatuses()))
            ]);

            $oldStatus = $energyMeter->status;
            $energyMeter->update(['status' => $request->status]);

            Log::info('Energy meter status updated', [
                'energy_meter_id' => $energyMeter->id,
                'user_id' => auth()->id(),
                'old_status' => $oldStatus,
                'new_status' => $request->status
            ]);

            return response()->json([
                'message' => 'Estado del medidor actualizado exitosamente',
                'data' => [
                    'id' => $energyMeter->id,
                    'status' => $energyMeter->status,
                    'updated_at' => $energyMeter->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating energy meter status: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al actualizar el estado del medidor',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Duplicate an energy meter.
     *
     * @authenticated
     * @urlParam id integer required The energy meter ID. Example: 1
     * @bodyParam meter_number string The new meter number. Example: "MTR-002"
     * @bodyParam name string The new name for the duplicated meter. Example: "Smart Meter - Copy"
     *
     * @response 200 {
     *   "message": "Medidor de energía duplicado exitosamente",
     *   "data": {
     *     "id": 2,
     *     "meter_number": "MTR-002",
     *     "name": "Smart Meter - Copy",
     *     "status": "inactive",
     *     "created_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     */
    public function duplicate(Request $request, EnergyMeter $energyMeter): JsonResponse
    {
        try {
            $request->validate([
                'meter_number' => 'nullable|string|max:255|unique:energy_meters,meter_number',
                'name' => 'nullable|string|max:255'
            ]);

            $newEnergyMeter = $energyMeter->replicate();
            $newEnergyMeter->meter_number = $request->meter_number ?? $energyMeter->meter_number . '_copy';
            $newEnergyMeter->name = $request->name ?? $energyMeter->name . ' (Copia)';
            $newEnergyMeter->status = 'inactive';
            $newEnergyMeter->installation_date = null;
            $newEnergyMeter->commissioning_date = null;
            $newEnergyMeter->next_calibration_date = null;
            $newEnergyMeter->save();

            Log::info('Energy meter duplicated', [
                'original_energy_meter_id' => $energyMeter->id,
                'new_energy_meter_id' => $newEnergyMeter->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Medidor de energía duplicado exitosamente',
                'data' => new EnergyMeterResource($newEnergyMeter)
            ]);

        } catch (\Exception $e) {
            Log::error('Error duplicating energy meter: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al duplicar el medidor de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get active meters.
     *
     * @authenticated
     * @queryParam limit integer Number of active meters to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "meter_number": "MTR-001",
     *       "name": "Smart Meter",
     *       "meter_type": "smart_meter",
     *       "status": "active",
     *       "meter_category": "electricity"
     *     }
     *   ]
     * }
     */
    public function active(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $activeMeters = EnergyMeter::where('status', 'active')
                ->with(['installation', 'consumptionPoint', 'customer'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergyMeterResource::collection($activeMeters)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching active energy meters: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los medidores activos',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get smart meters.
     *
     * @authenticated
     * @queryParam limit integer Number of smart meters to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "meter_number": "MTR-001",
     *       "name": "Smart Meter",
     *       "meter_type": "smart_meter",
     *       "is_smart_meter": true,
     *       "has_remote_reading": true
     *     }
     *   ]
     * }
     */
    public function smartMeters(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $smartMeters = EnergyMeter::where('is_smart_meter', true)
                ->with(['installation', 'consumptionPoint', 'customer'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergyMeterResource::collection($smartMeters)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching smart energy meters: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los medidores inteligentes',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get meters needing calibration.
     *
     * @authenticated
     * @queryParam limit integer Number of meters to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 2,
     *       "meter_number": "MTR-002",
     *       "name": "Digital Meter",
     *       "next_calibration_date": "2024-01-10",
     *       "days_until_calibration": -5
     *     }
     *   ]
     * }
     */
    public function needsCalibration(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $metersNeedingCalibration = EnergyMeter::whereNotNull('next_calibration_date')
                ->where('next_calibration_date', '<=', now())
                ->where('status', 'active')
                ->with(['installation', 'consumptionPoint', 'customer'])
                ->orderBy('next_calibration_date', 'asc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergyMeterResource::collection($metersNeedingCalibration)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching meters needing calibration: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los medidores que necesitan calibración',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get meters by type.
     *
     * @authenticated
     * @urlParam type string required The meter type. Example: "smart_meter"
     * @queryParam limit integer Number of meters to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "meter_number": "MTR-001",
     *       "name": "Smart Meter",
     *       "meter_type": "smart_meter",
     *       "status": "active"
     *     }
     *   ]
     * }
     */
    public function byType(Request $request, string $type): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $meters = EnergyMeter::where('meter_type', $type)
                ->with(['installation', 'consumptionPoint', 'customer'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergyMeterResource::collection($meters)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching meters by type: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los medidores por tipo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get meters by category.
     *
     * @authenticated
     * @urlParam category string required The meter category. Example: "electricity"
     * @queryParam limit integer Number of meters to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "meter_number": "MTR-001",
     *       "name": "Smart Meter",
     *       "meter_category": "electricity",
     *       "status": "active"
     *     }
     *   ]
     * }
     */
    public function byCategory(Request $request, string $category): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $meters = EnergyMeter::where('meter_category', $category)
                ->with(['installation', 'consumptionPoint', 'customer'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergyMeterResource::collection($meters)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching meters by category: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los medidores por categoría',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get meters by customer.
     *
     * @authenticated
     * @urlParam customer_id integer required The customer ID. Example: 1
     * @queryParam limit integer Number of meters to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "meter_number": "MTR-001",
     *       "name": "Smart Meter",
     *       "customer_id": 1,
     *       "status": "active"
     *     }
     *   ]
     * }
     */
    public function byCustomer(Request $request, int $customerId): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $meters = EnergyMeter::where('customer_id', $customerId)
                ->with(['installation', 'consumptionPoint'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergyMeterResource::collection($meters)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching meters by customer: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los medidores por cliente',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get meters by installation.
     *
     * @authenticated
     * @urlParam installation_id integer required The installation ID. Example: 1
     * @queryParam limit integer Number of meters to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "meter_number": "MTR-001",
     *       "name": "Smart Meter",
     *       "installation_id": 1,
     *       "status": "active"
     *     }
     *   ]
     * }
     */
    public function byInstallation(Request $request, int $installationId): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $meters = EnergyMeter::where('installation_id', $installationId)
                ->with(['consumptionPoint', 'customer'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergyMeterResource::collection($meters)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching meters by installation: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los medidores por instalación',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get high accuracy meters.
     *
     * @authenticated
     * @queryParam limit integer Number of meters to return. Example: 10
     * @queryParam threshold float Maximum accuracy class threshold. Example: 1.0
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "meter_number": "MTR-001",
     *       "name": "High Accuracy Meter",
     *       "accuracy_class": 0.5,
     *       "meter_type": "smart_meter"
     *     }
     *   ]
     * }
     */
    public function highAccuracy(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            $threshold = $request->get('threshold', 1.0);
            
            $highAccuracyMeters = EnergyMeter::whereNotNull('accuracy_class')
                ->where('accuracy_class', '<=', $threshold)
                ->where('status', 'active')
                ->with(['installation', 'consumptionPoint', 'customer'])
                ->orderBy('accuracy_class', 'asc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergyMeterResource::collection($highAccuracyMeters)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching high accuracy meters: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los medidores de alta precisión',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
