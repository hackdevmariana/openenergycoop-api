<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EnergyStorage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(
 *     name="Energy Storage",
 *     description="Gestión de sistemas de almacenamiento energético"
 * )
 */
class EnergyStorageController extends \App\Http\Controllers\Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = EnergyStorage::with(['user', 'provider']);

        // Filtros
        if ($request->filled('storage_type')) {
            $query->where('storage_type', $request->storage_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filtros de capacidad
        if ($request->filled('min_capacity')) {
            $query->where('capacity_kwh', '>=', $request->min_capacity);
        }

        if ($request->filled('max_capacity')) {
            $query->where('capacity_kwh', '<=', $request->max_capacity);
        }

        // Filtro de eficiencia
        if ($request->filled('min_efficiency')) {
            $query->where('round_trip_efficiency', '>=', $request->min_efficiency);
        }

        // Búsqueda por texto
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('system_id', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('manufacturer', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);

        $storages = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $storages,
            'message' => 'Sistemas de almacenamiento obtenidos exitosamente'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'provider_id' => 'required|exists:providers,id',
            'system_id' => 'required|string|unique:energy_storages,system_id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'storage_type' => 'required|in:battery_lithium,battery_lead_acid,battery_flow,pumped_hydro,compressed_air,flywheel,thermal,hydrogen',
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'capacity_kwh' => 'required|numeric|min:0',
            'usable_capacity_kwh' => 'required|numeric|min:0',
            'max_charge_power_kw' => 'required|numeric|min:0',
            'max_discharge_power_kw' => 'required|numeric|min:0',
            'round_trip_efficiency' => 'nullable|numeric|min:0|max:100',
            'installation_cost' => 'nullable|numeric|min:0',
            'warranty_end_date' => 'nullable|date',
            'location_description' => 'nullable|string|max:255',
        ]);

        $storage = EnergyStorage::create($validatedData);
        $storage->load(['user', 'provider']);

        return response()->json([
            'success' => true,
            'data' => $storage,
            'message' => 'Sistema de almacenamiento creado exitosamente'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(EnergyStorage $energyStorage): JsonResponse
    {
        $energyStorage->load(['user', 'provider']);

        return response()->json([
            'success' => true,
            'data' => $energyStorage,
            'message' => 'Sistema de almacenamiento obtenido exitosamente'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EnergyStorage $energyStorage): JsonResponse
    {
        $validatedData = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'provider_id' => 'sometimes|exists:providers,id',
            'system_id' => [
                'sometimes',
                'string',
                Rule::unique('energy_storages')->ignore($energyStorage->id)
            ],
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'storage_type' => 'sometimes|in:battery_lithium,battery_lead_acid,battery_flow,pumped_hydro,compressed_air,flywheel,thermal,hydrogen',
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'capacity_kwh' => 'sometimes|numeric|min:0',
            'usable_capacity_kwh' => 'sometimes|numeric|min:0',
            'max_charge_power_kw' => 'sometimes|numeric|min:0',
            'max_discharge_power_kw' => 'sometimes|numeric|min:0',
            'current_charge_kwh' => 'sometimes|numeric|min:0',
            'charge_level_percentage' => 'sometimes|numeric|min:0|max:100',
            'status' => 'sometimes|in:online,offline,charging,discharging,standby,maintenance,error',
            'round_trip_efficiency' => 'nullable|numeric|min:0|max:100',
            'cycle_count' => 'sometimes|integer|min:0',
            'current_health_percentage' => 'sometimes|numeric|min:0|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        $energyStorage->update($validatedData);
        $energyStorage->load(['user', 'provider']);

        return response()->json([
            'success' => true,
            'data' => $energyStorage,
            'message' => 'Sistema de almacenamiento actualizado exitosamente'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EnergyStorage $energyStorage): JsonResponse
    {
        if ($energyStorage->status === 'charging' || $energyStorage->status === 'discharging') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un sistema que está en operación'
            ], 422);
        }

        $energyStorage->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sistema de almacenamiento eliminado exitosamente'
        ]);
    }

    /**
     * Iniciar carga del sistema
     */
    public function startCharging(EnergyStorage $energyStorage): JsonResponse
    {
        if (!in_array($energyStorage->status, ['online', 'standby'])) {
            return response()->json([
                'success' => false,
                'message' => 'El sistema debe estar en línea o en espera para iniciar la carga'
            ], 422);
        }

        if ($energyStorage->charge_level_percentage >= $energyStorage->max_charge_level) {
            return response()->json([
                'success' => false,
                'message' => 'El sistema ya está en su nivel máximo de carga'
            ], 422);
        }

        $energyStorage->update(['status' => 'charging']);

        return response()->json([
            'success' => true,
            'data' => $energyStorage,
            'message' => 'Carga iniciada exitosamente'
        ]);
    }

    /**
     * Iniciar descarga del sistema
     */
    public function startDischarging(EnergyStorage $energyStorage): JsonResponse
    {
        if (!in_array($energyStorage->status, ['online', 'standby'])) {
            return response()->json([
                'success' => false,
                'message' => 'El sistema debe estar en línea o en espera para iniciar la descarga'
            ], 422);
        }

        if ($energyStorage->charge_level_percentage <= $energyStorage->min_charge_level) {
            return response()->json([
                'success' => false,
                'message' => 'El sistema está en su nivel mínimo de carga'
            ], 422);
        }

        $energyStorage->update(['status' => 'discharging']);

        return response()->json([
            'success' => true,
            'data' => $energyStorage,
            'message' => 'Descarga iniciada exitosamente'
        ]);
    }

    /**
     * Detener operación del sistema
     */
    public function stopOperation(EnergyStorage $energyStorage): JsonResponse
    {
        if (!in_array($energyStorage->status, ['charging', 'discharging'])) {
            return response()->json([
                'success' => false,
                'message' => 'El sistema no está en operación'
            ], 422);
        }

        $energyStorage->update(['status' => 'standby']);

        return response()->json([
            'success' => true,
            'data' => $energyStorage,
            'message' => 'Operación detenida exitosamente'
        ]);
    }

    /**
     * Actualizar nivel de carga
     */
    public function updateChargeLevel(Request $request, EnergyStorage $energyStorage): JsonResponse
    {
        $validatedData = $request->validate([
            'charge_level_percentage' => 'required|numeric|min:0|max:100',
            'current_charge_kwh' => 'required|numeric|min:0'
        ]);

        $energyStorage->update([
            'charge_level_percentage' => $validatedData['charge_level_percentage'],
            'current_charge_kwh' => $validatedData['current_charge_kwh']
        ]);

        return response()->json([
            'success' => true,
            'data' => $energyStorage,
            'message' => 'Nivel de carga actualizado exitosamente'
        ]);
    }

    /**
     * Obtener métricas de rendimiento
     */
    public function performance(EnergyStorage $energyStorage): JsonResponse
    {
        $performance = [
            'basic_info' => [
                'system_id' => $energyStorage->system_id,
                'name' => $energyStorage->name,
                'storage_type' => $energyStorage->storage_type,
                'status' => $energyStorage->status
            ],
            'capacity_metrics' => [
                'total_capacity_kwh' => $energyStorage->capacity_kwh,
                'usable_capacity_kwh' => $energyStorage->usable_capacity_kwh,
                'current_charge_kwh' => $energyStorage->current_charge_kwh,
                'charge_level_percentage' => $energyStorage->charge_level_percentage,
                'available_capacity_kwh' => $energyStorage->usable_capacity_kwh - $energyStorage->current_charge_kwh
            ],
            'efficiency_metrics' => [
                'round_trip_efficiency' => $energyStorage->round_trip_efficiency,
                'charge_efficiency' => $energyStorage->charge_efficiency,
                'discharge_efficiency' => $energyStorage->discharge_efficiency,
                'current_health_percentage' => $energyStorage->current_health_percentage
            ],
            'operational_metrics' => [
                'cycle_count' => $energyStorage->cycle_count,
                'max_charge_power_kw' => $energyStorage->max_charge_power_kw,
                'max_discharge_power_kw' => $energyStorage->max_discharge_power_kw,
                'capacity_degradation_percentage' => $energyStorage->capacity_degradation_percentage
            ],
            'financial_metrics' => [
                'installation_cost' => $energyStorage->installation_cost,
                'maintenance_cost_annual' => $energyStorage->maintenance_cost_annual,
                'insurance_value' => $energyStorage->insurance_value
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $performance,
            'message' => 'Métricas de rendimiento obtenidas exitosamente'
        ]);
    }

    /**
     * Obtener sistemas del usuario autenticado
     */
    public function myStorageSystems(Request $request): JsonResponse
    {
        $systems = EnergyStorage::where('user_id', Auth::id())
            ->where('is_active', true)
            ->with(['provider'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $systems,
            'message' => 'Sistemas de almacenamiento del usuario obtenidos exitosamente'
        ]);
    }

    /**
     * Obtener resumen de almacenamiento
     */
    public function storageOverview(): JsonResponse
    {
        $overview = [
            'total_systems' => EnergyStorage::where('is_active', true)->count(),
            'systems_by_status' => EnergyStorage::where('is_active', true)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status'),
            'systems_by_type' => EnergyStorage::where('is_active', true)
                ->select('storage_type', DB::raw('count(*) as count'))
                ->groupBy('storage_type')
                ->pluck('count', 'storage_type'),
            'total_capacity_mwh' => EnergyStorage::where('is_active', true)->sum('capacity_kwh') / 1000,
            'total_stored_mwh' => EnergyStorage::where('is_active', true)->sum('current_charge_kwh') / 1000,
            'average_efficiency' => EnergyStorage::where('is_active', true)
                ->whereNotNull('round_trip_efficiency')
                ->avg('round_trip_efficiency'),
            'systems_needing_maintenance' => EnergyStorage::where('is_active', true)
                ->where('next_maintenance_date', '<=', now()->addDays(30))
                ->count(),
            'low_battery_systems' => EnergyStorage::where('is_active', true)
                ->where('charge_level_percentage', '<', 20)
                ->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $overview,
            'message' => 'Resumen de almacenamiento obtenido exitosamente'
        ]);
    }
}