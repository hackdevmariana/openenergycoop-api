<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\EnergyReading\StoreEnergyReadingRequest;
use App\Http\Requests\Api\V1\EnergyReading\UpdateEnergyReadingRequest;
use App\Http\Resources\Api\V1\EnergyReading\EnergyReadingResource;
use App\Http\Resources\Api\V1\EnergyReading\EnergyReadingCollection;
use App\Models\EnergyReading;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @group Energy Reading Management
 * APIs for managing energy readings
 */
class EnergyReadingController extends Controller
{
    /**
     * Display a listing of energy readings.
     *
     * @authenticated
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam search string Search in reading_number, notes. Example: "Reading 001"
     * @queryParam reading_type string Filter by reading type. Example: "instantaneous"
     * @queryParam reading_source string Filter by reading source. Example: "automatic"
     * @queryParam reading_status string Filter by reading status. Example: "valid"
     * @queryParam meter_id integer Filter by meter ID. Example: 1
     * @queryParam installation_id integer Filter by installation ID. Example: 1
     * @queryParam consumption_point_id integer Filter by consumption point ID. Example: 1
     * @queryParam customer_id integer Filter by customer ID. Example: 1
     * @queryParam reading_timestamp_from date Filter by reading timestamp from. Example: "2024-01-01"
     * @queryParam reading_timestamp_to date Filter by reading timestamp to. Example: "2024-12-31"
     * @queryParam quality_score_min float Minimum quality score. Example: 80.0
     * @queryParam quality_score_max float Maximum quality score. Example: 100.0
     * @queryParam sort_by string Sort field. Example: "reading_timestamp"
     * @queryParam sort_direction string Sort direction (asc/desc). Example: "desc"
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "reading_number": "RDG-001",
     *       "reading_type": "instantaneous",
     *       "reading_status": "valid",
     *       "reading_value": 150.5000,
     *       "reading_unit": "kWh",
     *       "reading_timestamp": "2024-01-15T10:00:00Z"
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
            $query = EnergyReading::with(['meter', 'installation', 'consumptionPoint', 'customer', 'readBy', 'validatedBy', 'correctedBy', 'createdBy']);

            // Filtros de búsqueda
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('reading_number', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%");
                });
            }

            // Filtros por tipo, fuente y estado
            if ($request->filled('reading_type')) {
                $query->where('reading_type', $request->reading_type);
            }

            if ($request->filled('reading_source')) {
                $query->where('reading_source', $request->reading_source);
            }

            if ($request->filled('reading_status')) {
                $query->where('reading_status', $request->reading_status);
            }

            // Filtros por entidades relacionadas
            if ($request->filled('meter_id')) {
                $query->where('meter_id', $request->meter_id);
            }

            if ($request->filled('installation_id')) {
                $query->where('installation_id', $request->installation_id);
            }

            if ($request->filled('consumption_point_id')) {
                $query->where('consumption_point_id', $request->consumption_point_id);
            }

            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            // Filtros por fechas
            if ($request->filled('reading_timestamp_from')) {
                $query->whereDate('reading_timestamp', '>=', $request->reading_timestamp_from);
            }

            if ($request->filled('reading_timestamp_to')) {
                $query->whereDate('reading_timestamp', '<=', $request->reading_timestamp_to);
            }

            // Filtros por calidad
            if ($request->filled('quality_score_min')) {
                $query->where('quality_score', '>=', $request->quality_score_min);
            }

            if ($request->filled('quality_score_max')) {
                $query->where('quality_score', '<=', $request->quality_score_max);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'reading_timestamp');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            if (in_array($sortBy, ['reading_timestamp', 'reading_value', 'quality_score', 'created_at'])) {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Paginación
            $perPage = min($request->get('per_page', 15), 100);
            $energyReadings = $query->paginate($perPage);

            return response()->json([
                'data' => EnergyReadingResource::collection($energyReadings),
                'meta' => [
                    'current_page' => $energyReadings->currentPage(),
                    'total' => $energyReadings->total(),
                    'per_page' => $energyReadings->perPage(),
                    'last_page' => $energyReadings->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy readings: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las lecturas de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created energy reading.
     *
     * @authenticated
     * @bodyParam reading_number string required The reading number. Example: "RDG-001"
     * @bodyParam meter_id integer required The meter ID. Example: 1
     * @bodyParam installation_id integer The installation ID. Example: 1
     * @bodyParam consumption_point_id integer The consumption point ID. Example: 1
     * @bodyParam customer_id integer required The customer ID. Example: 1
     * @bodyParam reading_type string required The reading type. Example: "instantaneous"
     * @bodyParam reading_source string required The reading source. Example: "automatic"
     * @bodyParam reading_status string required The reading status. Example: "valid"
     * @bodyParam reading_timestamp datetime required The reading timestamp. Example: "2024-01-15T10:00:00Z"
     * @bodyParam reading_value decimal required The reading value. Example: 150.5000
     * @bodyParam reading_unit string required The reading unit. Example: "kWh"
     * @bodyParam previous_reading_value decimal The previous reading value. Example: 145.2000
     * @bodyParam consumption_value decimal The consumption value. Example: 5.3000
     * @bodyParam quality_score decimal The quality score. Example: 95.5
     *
     * @response 201 {
     *   "message": "Lectura de energía creada exitosamente",
     *   "data": {
     *     "id": 1,
     *     "reading_number": "RDG-001",
     *     "reading_type": "instantaneous",
     *     "reading_status": "valid",
     *     "created_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     */
    public function store(StoreEnergyReadingRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $energyReading = EnergyReading::create($request->validated());

            DB::commit();

            Log::info('Energy reading created', [
                'energy_reading_id' => $energyReading->id,
                'user_id' => auth()->id(),
                'reading_number' => $energyReading->reading_number
            ]);

            return response()->json([
                'message' => 'Lectura de energía creada exitosamente',
                'data' => new EnergyReadingResource($energyReading)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating energy reading: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al crear la lectura de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified energy reading.
     *
     * @authenticated
     * @urlParam id integer required The energy reading ID. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "reading_number": "RDG-001",
     *     "reading_type": "instantaneous",
     *     "reading_status": "valid",
     *     "reading_value": 150.5000,
     *     "reading_unit": "kWh",
     *     "reading_timestamp": "2024-01-15T10:00:00Z"
     *   }
     * }
     */
    public function show(EnergyReading $energyReading): JsonResponse
    {
        try {
            $energyReading->load(['meter', 'installation', 'consumptionPoint', 'customer', 'readBy', 'validatedBy', 'correctedBy', 'createdBy']);

            return response()->json([
                'data' => new EnergyReadingResource($energyReading)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy reading: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener la lectura de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified energy reading.
     *
     * @authenticated
     * @urlParam id integer required The energy reading ID. Example: 1
     * @bodyParam reading_status string The reading status. Example: "validated"
     * @bodyParam quality_score decimal The quality score. Example: 98.5
     * @bodyParam notes string The notes. Example: "Updated notes"
     *
     * @response 200 {
     *   "message": "Lectura de energía actualizada exitosamente",
     *   "data": {
     *     "id": 1,
     *     "reading_status": "validated",
     *     "updated_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     */
    public function update(UpdateEnergyReadingRequest $request, EnergyReading $energyReading): JsonResponse
    {
        try {
            DB::beginTransaction();

            $energyReading->update($request->validated());

            DB::commit();

            Log::info('Energy reading updated', [
                'energy_reading_id' => $energyReading->id,
                'user_id' => auth()->id(),
                'reading_number' => $energyReading->reading_number
            ]);

            return response()->json([
                'message' => 'Lectura de energía actualizada exitosamente',
                'data' => new EnergyReadingResource($energyReading)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating energy reading: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al actualizar la lectura de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified energy reading.
     *
     * @authenticated
     * @urlParam id integer required The energy reading ID. Example: 1
     *
     * @response 200 {
     *   "message": "Lectura de energía eliminada exitosamente"
     * }
     */
    public function destroy(EnergyReading $energyReading): JsonResponse
    {
        try {
            DB::beginTransaction();

            $readingNumber = $energyReading->reading_number;
            $energyReadingId = $energyReading->id;

            $energyReading->delete();

            DB::commit();

            Log::info('Energy reading deleted', [
                'energy_reading_id' => $energyReadingId,
                'user_id' => auth()->id(),
                'reading_number' => $readingNumber
            ]);

            return response()->json([
                'message' => 'Lectura de energía eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting energy reading: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al eliminar la lectura de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get energy reading statistics.
     *
     * @authenticated
     * @queryParam reading_type string Filter by reading type. Example: "instantaneous"
     * @queryParam reading_source string Filter by reading source. Example: "automatic"
     * @queryParam reading_status string Filter by reading status. Example: "valid"
     * @queryParam meter_id integer Filter by meter ID. Example: 1
     * @queryParam customer_id integer Filter by customer ID. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "total_readings": 1000,
     *     "valid_readings": 950,
     *     "invalid_readings": 30,
     *     "suspicious_readings": 20,
     *     "readings_by_type": {
     *       "instantaneous": 400,
     *       "interval": 300,
     *       "cumulative": 300
     *     },
     *     "readings_by_source": {
     *       "automatic": 800,
     *       "manual": 150,
     *       "remote": 50
     *     },
     *     "average_quality_score": 92.5
     *   }
     * }
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $query = EnergyReading::query();

            // Filtros
            if ($request->filled('reading_type')) {
                $query->where('reading_type', $request->reading_type);
            }

            if ($request->filled('reading_source')) {
                $query->where('reading_source', $request->reading_source);
            }

            if ($request->filled('reading_status')) {
                $query->where('reading_status', $request->reading_status);
            }

            if ($request->filled('meter_id')) {
                $query->where('meter_id', $request->meter_id);
            }

            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            $totalReadings = $query->count();
            $validReadings = (clone $query)->where('reading_status', 'valid')->count();
            $invalidReadings = (clone $query)->where('reading_status', 'invalid')->count();
            $suspiciousReadings = (clone $query)->where('reading_status', 'suspicious')->count();
            $estimatedReadings = (clone $query)->where('reading_status', 'estimated')->count();
            $correctedReadings = (clone $query)->where('reading_status', 'corrected')->count();
            $missingReadings = (clone $query)->where('reading_status', 'missing')->count();

            // Lecturas por tipo
            $readingsByType = (clone $query)
                ->selectRaw('reading_type, COUNT(*) as count')
                ->groupBy('reading_type')
                ->pluck('count', 'reading_type')
                ->toArray();

            // Lecturas por fuente
            $readingsBySource = (clone $query)
                ->selectRaw('reading_source, COUNT(*) as count')
                ->groupBy('reading_source')
                ->pluck('count', 'reading_source')
                ->toArray();

            // Lecturas por estado
            $readingsByStatus = (clone $query)
                ->selectRaw('reading_status, COUNT(*) as count')
                ->groupBy('reading_status')
                ->pluck('count', 'reading_status')
                ->toArray();

            // Puntuación promedio de calidad
            $averageQualityScore = (clone $query)
                ->whereNotNull('quality_score')
                ->avg('quality_score');

            return response()->json([
                'data' => [
                    'total_readings' => $totalReadings,
                    'valid_readings' => $validReadings,
                    'invalid_readings' => $invalidReadings,
                    'suspicious_readings' => $suspiciousReadings,
                    'estimated_readings' => $estimatedReadings,
                    'corrected_readings' => $correctedReadings,
                    'missing_readings' => $missingReadings,
                    'readings_by_type' => $readingsByType,
                    'readings_by_source' => $readingsBySource,
                    'readings_by_status' => $readingsByStatus,
                    'average_quality_score' => round($averageQualityScore, 2),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy reading statistics: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las estadísticas de lecturas de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get reading types.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": {
     *     "instantaneous": "Instantáneo",
     *     "interval": "Intervalo",
     *     "cumulative": "Acumulativo",
     *     "demand": "Demanda",
     *     "energy": "Energía"
     *   }
     * }
     */
    public function types(): JsonResponse
    {
        try {
            return response()->json([
                'data' => EnergyReading::getReadingTypes()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching reading types: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los tipos de lectura',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get reading sources.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": {
     *     "manual": "Manual",
     *     "automatic": "Automático",
     *     "remote": "Remoto",
     *     "estimated": "Estimado",
     *     "calculated": "Calculado"
     *   }
     * }
     */
    public function sources(): JsonResponse
    {
        try {
            return response()->json([
                'data' => EnergyReading::getReadingSources()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching reading sources: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las fuentes de lectura',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get reading statuses.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": {
     *     "valid": "Válido",
     *     "invalid": "Inválido",
     *     "suspicious": "Sospechoso",
     *     "estimated": "Estimado",
     *     "corrected": "Corregido"
     *   }
     * }
     */
    public function statuses(): JsonResponse
    {
        try {
            return response()->json([
                'data' => EnergyReading::getReadingStatuses()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching reading statuses: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los estados de lectura',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update reading status.
     *
     * @authenticated
     * @urlParam id integer required The energy reading ID. Example: 1
     * @bodyParam reading_status string required The new status. Example: "validated"
     *
     * @response 200 {
     *   "message": "Estado de lectura actualizado exitosamente",
     *   "data": {
     *     "id": 1,
     *     "reading_status": "validated",
     *     "updated_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     */
    public function updateStatus(Request $request, EnergyReading $energyReading): JsonResponse
    {
        try {
            $request->validate([
                'reading_status' => 'required|string|in:' . implode(',', array_keys(EnergyReading::getReadingStatuses()))
            ]);

            $oldStatus = $energyReading->reading_status;
            $energyReading->update(['reading_status' => $request->reading_status]);

            Log::info('Energy reading status updated', [
                'energy_reading_id' => $energyReading->id,
                'user_id' => auth()->id(),
                'old_status' => $oldStatus,
                'new_status' => $request->reading_status
            ]);

            return response()->json([
                'message' => 'Estado de lectura actualizado exitosamente',
                'data' => [
                    'id' => $energyReading->id,
                    'reading_status' => $energyReading->reading_status,
                    'updated_at' => $energyReading->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating energy reading status: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al actualizar el estado de la lectura',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Validate a reading.
     *
     * @authenticated
     * @urlParam id integer required The energy reading ID. Example: 1
     * @bodyParam quality_score decimal The quality score. Example: 95.5
     * @bodyParam validation_notes string The validation notes. Example: "Validated by operator"
     *
     * @response 200 {
     *   "message": "Lectura validada exitosamente",
     *   "data": {
     *     "id": 1,
     *     "validated_at": "2024-01-15T10:00:00Z",
     *     "quality_score": 95.5
     *   }
     * }
     */
    public function validate(Request $request, EnergyReading $energyReading): JsonResponse
    {
        try {
            $request->validate([
                'quality_score' => 'nullable|numeric|min:0|max:100',
                'validation_notes' => 'nullable|string|max:1000'
            ]);

            $data = [
                'validated_by' => auth()->id(),
                'validated_at' => now(),
            ];

            if ($request->filled('quality_score')) {
                $data['quality_score'] = $request->quality_score;
            }

            if ($request->filled('validation_notes')) {
                $data['validation_notes'] = $request->validation_notes;
            }

            $energyReading->update($data);

            Log::info('Energy reading validated', [
                'energy_reading_id' => $energyReading->id,
                'user_id' => auth()->id(),
                'quality_score' => $data['quality_score'] ?? null
            ]);

            return response()->json([
                'message' => 'Lectura validada exitosamente',
                'data' => [
                    'id' => $energyReading->id,
                    'validated_at' => $energyReading->validated_at,
                    'quality_score' => $energyReading->quality_score
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error validating energy reading: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al validar la lectura',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get valid readings.
     *
     * @authenticated
     * @queryParam limit integer Number of readings to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "reading_number": "RDG-001",
     *       "reading_type": "instantaneous",
     *       "reading_status": "valid",
     *       "reading_value": 150.5000
     *     }
     *   ]
     * }
     */
    public function valid(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $validReadings = EnergyReading::where('reading_status', 'valid')
                ->with(['meter', 'installation', 'consumptionPoint', 'customer'])
                ->orderBy('reading_timestamp', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergyReadingResource::collection($validReadings)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching valid energy readings: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las lecturas válidas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get readings by type.
     *
     * @authenticated
     * @urlParam type string required The reading type. Example: "instantaneous"
     * @queryParam limit integer Number of readings to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "reading_number": "RDG-001",
     *       "reading_type": "instantaneous",
     *       "reading_value": 150.5000
     *     }
     *   ]
     * }
     */
    public function byType(Request $request, string $type): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $readings = EnergyReading::where('reading_type', $type)
                ->with(['meter', 'installation', 'consumptionPoint', 'customer'])
                ->orderBy('reading_timestamp', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergyReadingResource::collection($readings)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching readings by type: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las lecturas por tipo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get readings by meter.
     *
     * @authenticated
     * @urlParam meter_id integer required The meter ID. Example: 1
     * @queryParam limit integer Number of readings to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "reading_number": "RDG-001",
     *       "meter_id": 1,
     *       "reading_value": 150.5000
     *     }
     *   ]
     * }
     */
    public function byMeter(Request $request, int $meterId): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $readings = EnergyReading::where('meter_id', $meterId)
                ->with(['installation', 'consumptionPoint', 'customer'])
                ->orderBy('reading_timestamp', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergyReadingResource::collection($readings)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching readings by meter: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las lecturas por medidor',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get readings by customer.
     *
     * @authenticated
     * @urlParam customer_id integer required The customer ID. Example: 1
     * @queryParam limit integer Number of readings to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "reading_number": "RDG-001",
     *       "customer_id": 1,
     *       "reading_value": 150.5000
     *     }
     *   ]
     * }
     */
    public function byCustomer(Request $request, int $customerId): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $readings = EnergyReading::where('customer_id', $customerId)
                ->with(['meter', 'installation', 'consumptionPoint'])
                ->orderBy('reading_timestamp', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergyReadingResource::collection($readings)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching readings by customer: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las lecturas por cliente',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get high quality readings.
     *
     * @authenticated
     * @queryParam limit integer Number of readings to return. Example: 10
     * @queryParam min_score float Minimum quality score. Example: 90.0
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "reading_number": "RDG-001",
     *       "quality_score": 95.5,
     *       "reading_value": 150.5000
     *     }
     *   ]
     * }
     */
    public function highQuality(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            $minScore = $request->get('min_score', 90.0);
            
            $highQualityReadings = EnergyReading::whereNotNull('quality_score')
                ->where('quality_score', '>=', $minScore)
                ->with(['meter', 'installation', 'consumptionPoint', 'customer'])
                ->orderBy('quality_score', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergyReadingResource::collection($highQualityReadings)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching high quality readings: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las lecturas de alta calidad',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get today's readings.
     *
     * @authenticated
     * @queryParam limit integer Number of readings to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "reading_number": "RDG-001",
     *       "reading_timestamp": "2024-01-15T10:00:00Z",
     *       "reading_value": 150.5000
     *     }
     *   ]
     * }
     */
    public function today(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $todayReadings = EnergyReading::today()
                ->with(['meter', 'installation', 'consumptionPoint', 'customer'])
                ->orderBy('reading_timestamp', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergyReadingResource::collection($todayReadings)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching today\'s readings: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las lecturas de hoy',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get this month's readings.
     *
     * @authenticated
     * @queryParam limit integer Number of readings to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "reading_number": "RDG-001",
     *       "reading_timestamp": "2024-01-15T10:00:00Z",
     *       "reading_value": 150.5000
     *     }
     *   ]
     * }
     */
    public function thisMonth(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $thisMonthReadings = EnergyReading::thisMonth()
                ->with(['meter', 'installation', 'consumptionPoint', 'customer'])
                ->orderBy('reading_timestamp', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergyReadingResource::collection($thisMonthReadings)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching this month\'s readings: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las lecturas de este mes',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
