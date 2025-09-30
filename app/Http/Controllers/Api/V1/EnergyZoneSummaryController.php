<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EnergyZoneSummary;
use App\Models\Municipality;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EnergyZoneSummaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = EnergyZoneSummary::with('municipality');

            // Filtros
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('municipality_id')) {
                $query->where('municipality_id', $request->municipality_id);
            }

            if ($request->filled('postal_code')) {
                $query->where('postal_code', 'like', '%' . $request->postal_code . '%');
            }

            if ($request->filled('with_available_energy')) {
                $query->where('available_kwh_day', '>', 0);
            }

            if ($request->filled('recently_updated')) {
                $query->where('last_updated_at', '>=', now()->subHours(24));
            }

            if ($request->filled('min_production')) {
                $query->where('estimated_production_kwh_day', '>=', $request->min_production);
            }

            if ($request->filled('max_production')) {
                $query->where('estimated_production_kwh_day', '<=', $request->max_production);
            }

            // Búsqueda por nombre de zona
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('zone_name', 'like', "%{$search}%")
                      ->orWhere('postal_code', 'like', "%{$search}%")
                      ->orWhereHas('municipality', function ($municipalityQuery) use ($search) {
                          $municipalityQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Ordenación
            $sortBy = $request->get('sort_by', 'zone_name');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = min($request->get('per_page', 15), 100);
            $zones = $query->paginate($perPage);

            // Transformar datos para incluir cálculos
            $zones->getCollection()->transform(function ($zone) {
                return [
                    'id' => $zone->id,
                    'zone_name' => $zone->zone_name,
                    'postal_code' => $zone->postal_code,
                    'municipality' => $zone->municipality ? [
                        'id' => $zone->municipality->id,
                        'name' => $zone->municipality->name,
                    ] : null,
                    'estimated_production_kwh_day' => $zone->estimated_production_kwh_day,
                    'reserved_kwh_day' => $zone->reserved_kwh_day,
                    'requested_kwh_day' => $zone->requested_kwh_day,
                    'available_kwh_day' => $zone->available_kwh_day,
                    'status' => $zone->status,
                    'utilization_percentage' => $zone->utilization_percentage,
                    'demand_percentage' => $zone->demand_percentage,
                    'status_color' => $zone->status_color,
                    'last_updated_at' => $zone->last_updated_at,
                    'notes' => $zone->notes,
                    'created_at' => $zone->created_at,
                    'updated_at' => $zone->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Zonas energéticas obtenidas exitosamente',
                'data' => $zones,
                'meta' => [
                    'total_zones' => EnergyZoneSummary::count(),
                    'system_summary' => EnergyZoneSummary::getSystemSummary(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener zonas energéticas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las zonas energéticas',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
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
                'zone_name' => 'required|string|max:255',
                'postal_code' => 'required|string|max:10',
                'municipality_id' => 'required|exists:municipalities,id',
                'estimated_production_kwh_day' => 'required|numeric|min:0',
                'reserved_kwh_day' => 'required|numeric|min:0',
                'requested_kwh_day' => 'required|numeric|min:0',
                'status' => 'required|in:verde,naranja,rojo',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $zoneData = $request->only([
                'zone_name', 'postal_code', 'municipality_id',
                'estimated_production_kwh_day', 'reserved_kwh_day', 'requested_kwh_day',
                'status', 'notes'
            ]);

            // Calcular energía disponible
            $zoneData['available_kwh_day'] = max(0, $zoneData['estimated_production_kwh_day'] - $zoneData['reserved_kwh_day']);
            $zoneData['last_updated_at'] = now();

            $zone = EnergyZoneSummary::create($zoneData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Zona energética creada exitosamente',
                'data' => [
                    'id' => $zone->id,
                    'zone_name' => $zone->zone_name,
                    'postal_code' => $zone->postal_code,
                    'municipality' => $zone->municipality ? [
                        'id' => $zone->municipality->id,
                        'name' => $zone->municipality->name,
                    ] : null,
                    'estimated_production_kwh_day' => $zone->estimated_production_kwh_day,
                    'reserved_kwh_day' => $zone->reserved_kwh_day,
                    'requested_kwh_day' => $zone->requested_kwh_day,
                    'available_kwh_day' => $zone->available_kwh_day,
                    'status' => $zone->status,
                    'utilization_percentage' => $zone->utilization_percentage,
                    'demand_percentage' => $zone->demand_percentage,
                    'status_color' => $zone->status_color,
                    'last_updated_at' => $zone->last_updated_at,
                    'notes' => $zone->notes,
                    'created_at' => $zone->created_at,
                    'updated_at' => $zone->updated_at,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear zona energética: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la zona energética',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $zone = EnergyZoneSummary::with('municipality')->find($id);

            if (!$zone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zona energética no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Zona energética obtenida exitosamente',
                'data' => [
                    'id' => $zone->id,
                    'zone_name' => $zone->zone_name,
                    'postal_code' => $zone->postal_code,
                    'municipality' => $zone->municipality ? [
                        'id' => $zone->municipality->id,
                        'name' => $zone->municipality->name,
                        'full_name' => $zone->municipality->full_name,
                    ] : null,
                    'estimated_production_kwh_day' => $zone->estimated_production_kwh_day,
                    'reserved_kwh_day' => $zone->reserved_kwh_day,
                    'requested_kwh_day' => $zone->requested_kwh_day,
                    'available_kwh_day' => $zone->available_kwh_day,
                    'status' => $zone->status,
                    'utilization_percentage' => $zone->utilization_percentage,
                    'demand_percentage' => $zone->demand_percentage,
                    'status_color' => $zone->status_color,
                    'last_updated_at' => $zone->last_updated_at,
                    'notes' => $zone->notes,
                    'energy_summary' => $zone->getEnergySummary(),
                    'historical_data' => $zone->getHistoricalData(now()->subDays(30), now()),
                    'created_at' => $zone->created_at,
                    'updated_at' => $zone->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener zona energética: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la zona energética',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $zone = EnergyZoneSummary::find($id);

            if (!$zone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zona energética no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'zone_name' => 'sometimes|required|string|max:255',
                'postal_code' => 'sometimes|required|string|max:10',
                'municipality_id' => 'sometimes|required|exists:municipalities,id',
                'estimated_production_kwh_day' => 'sometimes|required|numeric|min:0',
                'reserved_kwh_day' => 'sometimes|required|numeric|min:0',
                'requested_kwh_day' => 'sometimes|required|numeric|min:0',
                'status' => 'sometimes|required|in:verde,naranja,rojo',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $updateData = $request->only([
                'zone_name', 'postal_code', 'municipality_id',
                'estimated_production_kwh_day', 'reserved_kwh_day', 'requested_kwh_day',
                'status', 'notes'
            ]);

            // Recalcular energía disponible si se actualizan los valores
            if (isset($updateData['estimated_production_kwh_day']) || isset($updateData['reserved_kwh_day'])) {
                $estimated = $updateData['estimated_production_kwh_day'] ?? $zone->estimated_production_kwh_day;
                $reserved = $updateData['reserved_kwh_day'] ?? $zone->reserved_kwh_day;
                $updateData['available_kwh_day'] = max(0, $estimated - $reserved);
            }

            $updateData['last_updated_at'] = now();

            $zone->update($updateData);

            // Actualizar estado automáticamente
            $zone->updateStatus();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Zona energética actualizada exitosamente',
                'data' => [
                    'id' => $zone->id,
                    'zone_name' => $zone->zone_name,
                    'postal_code' => $zone->postal_code,
                    'municipality' => $zone->municipality ? [
                        'id' => $zone->municipality->id,
                        'name' => $zone->municipality->name,
                    ] : null,
                    'estimated_production_kwh_day' => $zone->estimated_production_kwh_day,
                    'reserved_kwh_day' => $zone->reserved_kwh_day,
                    'requested_kwh_day' => $zone->requested_kwh_day,
                    'available_kwh_day' => $zone->available_kwh_day,
                    'status' => $zone->status,
                    'utilization_percentage' => $zone->utilization_percentage,
                    'demand_percentage' => $zone->demand_percentage,
                    'status_color' => $zone->status_color,
                    'last_updated_at' => $zone->last_updated_at,
                    'notes' => $zone->notes,
                    'created_at' => $zone->created_at,
                    'updated_at' => $zone->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar zona energética: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la zona energética',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $zone = EnergyZoneSummary::find($id);

            if (!$zone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zona energética no encontrada'
                ], 404);
            }

            DB::beginTransaction();

            $zoneName = $zone->zone_name;
            $zone->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Zona energética '{$zoneName}' eliminada exitosamente"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar zona energética: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la zona energética',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Get system summary statistics
     */
    public function systemSummary(): JsonResponse
    {
        try {
            $summary = EnergyZoneSummary::getSystemSummary();

            return response()->json([
                'success' => true,
                'message' => 'Resumen del sistema obtenido exitosamente',
                'data' => $summary
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener resumen del sistema: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el resumen del sistema',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Update status for a specific zone
     */
    public function updateStatus(string $id): JsonResponse
    {
        try {
            $zone = EnergyZoneSummary::find($id);

            if (!$zone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zona energética no encontrada'
                ], 404);
            }

            $oldStatus = $zone->status;
            $zone->updateStatus();

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado exitosamente',
                'data' => [
                    'id' => $zone->id,
                    'zone_name' => $zone->zone_name,
                    'old_status' => $oldStatus,
                    'new_status' => $zone->status,
                    'status_color' => $zone->status_color,
                    'utilization_percentage' => $zone->utilization_percentage,
                    'demand_percentage' => $zone->demand_percentage,
                    'last_updated_at' => $zone->last_updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar estado de zona: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado de la zona',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Reserve energy for a zone
     */
    public function reserveEnergy(Request $request, string $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'kwh' => 'required|numeric|min:0.01',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $zone = EnergyZoneSummary::find($id);

            if (!$zone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zona energética no encontrada'
                ], 404);
            }

            $kwh = $request->kwh;

            if (!$zone->canReserveEnergy($kwh)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay suficiente energía disponible para reservar',
                    'data' => [
                        'requested_kwh' => $kwh,
                        'available_kwh' => $zone->available_kwh_day,
                        'shortage_kwh' => $kwh - $zone->available_kwh_day,
                    ]
                ], 422);
            }

            DB::beginTransaction();

            $success = $zone->reserveEnergy($kwh);

            if (!$success) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Error al reservar la energía'
                ], 500);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Energía de {$kwh} kWh reservada exitosamente",
                'data' => [
                    'id' => $zone->id,
                    'zone_name' => $zone->zone_name,
                    'reserved_kwh' => $kwh,
                    'total_reserved_kwh_day' => $zone->reserved_kwh_day,
                    'available_kwh_day' => $zone->available_kwh_day,
                    'utilization_percentage' => $zone->utilization_percentage,
                    'status' => $zone->status,
                    'last_updated_at' => $zone->last_updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al reservar energía: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al reservar la energía',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Release energy for a zone
     */
    public function releaseEnergy(Request $request, string $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'kwh' => 'required|numeric|min:0.01',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $zone = EnergyZoneSummary::find($id);

            if (!$zone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zona energética no encontrada'
                ], 404);
            }

            $kwh = $request->kwh;

            if ($kwh > $zone->reserved_kwh_day) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede liberar más energía de la que está reservada',
                    'data' => [
                        'requested_kwh' => $kwh,
                        'reserved_kwh' => $zone->reserved_kwh_day,
                    ]
                ], 422);
            }

            DB::beginTransaction();

            $zone->releaseEnergy($kwh);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Energía de {$kwh} kWh liberada exitosamente",
                'data' => [
                    'id' => $zone->id,
                    'zone_name' => $zone->zone_name,
                    'released_kwh' => $kwh,
                    'total_reserved_kwh_day' => $zone->reserved_kwh_day,
                    'available_kwh_day' => $zone->available_kwh_day,
                    'utilization_percentage' => $zone->utilization_percentage,
                    'status' => $zone->status,
                    'last_updated_at' => $zone->last_updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al liberar energía: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al liberar la energía',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }
}
