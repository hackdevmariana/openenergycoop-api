<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DeviceController extends Controller
{
    /**
     * Display a listing of devices.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Device::with(['user', 'consumptionPoint']);

            // Filtros
            if ($request->has('type')) {
                $query->byType($request->type);
            }

            if ($request->has('status')) {
                switch ($request->status) {
                    case 'online':
                        $query->online();
                        break;
                    case 'offline':
                        $query->offline();
                        break;
                    case 'inactive':
                        $query->inactive();
                        break;
                }
            }

            if ($request->has('user_id')) {
                $query->byUser($request->user_id);
            }

            if ($request->has('manufacturer')) {
                $query->byManufacturer($request->manufacturer);
            }

            if ($request->has('active')) {
                $query->where('active', $request->boolean('active'));
            }

            if ($request->has('search')) {
                $query->search($request->search);
            }

            if ($request->has('capability')) {
                $query->withCapability($request->capability);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $devices = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $devices->items(),
                'pagination' => [
                    'current_page' => $devices->currentPage(),
                    'last_page' => $devices->lastPage(),
                    'per_page' => $devices->perPage(),
                    'total' => $devices->total(),
                    'from' => $devices->firstItem(),
                    'to' => $devices->lastItem(),
                ],
                'message' => 'Dispositivos obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener dispositivos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener dispositivos',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created device.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'type' => ['required', Rule::in(Device::getAvailableTypes())],
                'user_id' => 'required|exists:users,id',
                'consumption_point_id' => 'nullable|exists:consumption_points,id',
                'api_endpoint' => 'nullable|url|max:255',
                'api_credentials' => 'nullable|array',
                'device_config' => 'nullable|array',
                'active' => 'boolean',
                'model' => 'nullable|string|max:255',
                'manufacturer' => 'nullable|string|max:255',
                'serial_number' => 'nullable|string|max:255|unique:devices',
                'firmware_version' => 'nullable|string|max:255',
                'capabilities' => 'nullable|array',
                'location' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $device = Device::create($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $device->load(['user', 'consumptionPoint']),
                'message' => 'Dispositivo creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear dispositivo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear dispositivo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified device.
     */
    public function show(Device $device): JsonResponse
    {
        try {
            $device->load(['user', 'consumptionPoint']);

            return response()->json([
                'success' => true,
                'data' => $device,
                'message' => 'Dispositivo obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener dispositivo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener dispositivo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified device.
     */
    public function update(Request $request, Device $device): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'type' => ['sometimes', 'required', Rule::in(Device::getAvailableTypes())],
                'user_id' => 'sometimes|required|exists:users,id',
                'consumption_point_id' => 'nullable|exists:consumption_points,id',
                'api_endpoint' => 'nullable|url|max:255',
                'api_credentials' => 'nullable|array',
                'device_config' => 'nullable|array',
                'active' => 'sometimes|boolean',
                'model' => 'nullable|string|max:255',
                'manufacturer' => 'nullable|string|max:255',
                'serial_number' => ['nullable', 'string', 'max:255', Rule::unique('devices')->ignore($device->id)],
                'firmware_version' => 'nullable|string|max:255',
                'capabilities' => 'nullable|array',
                'location' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $device->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $device->fresh()->load(['user', 'consumptionPoint']),
                'message' => 'Dispositivo actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar dispositivo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar dispositivo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified device.
     */
    public function destroy(Device $device): JsonResponse
    {
        try {
            DB::beginTransaction();

            $device->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Dispositivo eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar dispositivo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar dispositivo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Activate the specified device.
     */
    public function activate(Device $device): JsonResponse
    {
        try {
            $device->activate();

            return response()->json([
                'success' => true,
                'data' => $device->fresh(),
                'message' => 'Dispositivo activado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al activar dispositivo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al activar dispositivo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Deactivate the specified device.
     */
    public function deactivate(Device $device): JsonResponse
    {
        try {
            $device->deactivate();

            return response()->json([
                'success' => true,
                'data' => $device->fresh(),
                'message' => 'Dispositivo desactivado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al desactivar dispositivo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar dispositivo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update device communication timestamp.
     */
    public function updateCommunication(Device $device): JsonResponse
    {
        try {
            $device->updateCommunication();

            return response()->json([
                'success' => true,
                'data' => $device->fresh(),
                'message' => 'Comunicación del dispositivo actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar comunicación del dispositivo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar comunicación del dispositivo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get device statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_devices' => Device::count(),
                'active_devices' => Device::where('active', true)->count(),
                'inactive_devices' => Device::where('active', false)->count(),
                'online_devices' => Device::online()->count(),
                'offline_devices' => Device::offline()->count(),
                'devices_by_type' => Device::selectRaw('type, COUNT(*) as count')
                    ->groupBy('type')
                    ->pluck('count', 'type'),
                'devices_by_manufacturer' => Device::selectRaw('manufacturer, COUNT(*) as count')
                    ->whereNotNull('manufacturer')
                    ->groupBy('manufacturer')
                    ->pluck('count', 'manufacturer')
                    ->take(10),
                'recent_activity' => Device::where('last_communication', '>=', now()->subDays(7))
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Estadísticas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas de dispositivos: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas de dispositivos',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get available device types.
     */
    public function types(): JsonResponse
    {
        try {
            $types = Device::getAvailableTypes();

            return response()->json([
                'success' => true,
                'data' => $types,
                'message' => 'Tipos de dispositivos obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener tipos de dispositivos: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipos de dispositivos',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get available device capabilities.
     */
    public function capabilities(): JsonResponse
    {
        try {
            $capabilities = Device::getCapabilityOptions();

            return response()->json([
                'success' => true,
                'data' => $capabilities,
                'message' => 'Capacidades de dispositivos obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener capacidades de dispositivos: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener capacidades de dispositivos',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Bulk update devices.
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'device_ids' => 'required|array|min:1',
                'device_ids.*' => 'exists:devices,id',
                'updates' => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $deviceIds = $request->device_ids;
            $updates = $request->updates;

            $devices = Device::whereIn('id', $deviceIds)->get();
            $updatedCount = 0;

            foreach ($devices as $device) {
                $device->update($updates);
                $updatedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'updated_count' => $updatedCount,
                    'total_requested' => count($deviceIds)
                ],
                'message' => "{$updatedCount} dispositivos actualizados exitosamente"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en actualización masiva de dispositivos: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error en actualización masiva de dispositivos',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Bulk delete devices.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'device_ids' => 'required|array|min:1',
                'device_ids.*' => 'exists:devices,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $deviceIds = $request->device_ids;
            $devices = Device::whereIn('id', $deviceIds)->get();
            $deletedCount = 0;

            foreach ($devices as $device) {
                $device->delete();
                $deletedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'deleted_count' => $deletedCount,
                    'total_requested' => count($deviceIds)
                ],
                'message' => "{$deletedCount} dispositivos eliminados exitosamente"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en eliminación masiva de dispositivos: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error en eliminación masiva de dispositivos',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
