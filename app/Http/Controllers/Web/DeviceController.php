<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DeviceController extends Controller
{
    /**
     * Display a listing of devices.
     */
    public function index(Request $request): View
    {
        try {
            $query = Device::with(['user', 'consumptionPoint']);

            // Filtros
            if ($request->has('type') && $request->type !== '') {
                $query->byType($request->type);
            }

            if ($request->has('status') && $request->status !== '') {
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

            if ($request->has('user_id') && $request->user_id !== '') {
                $query->byUser($request->user_id);
            }

            if ($request->has('manufacturer') && $request->manufacturer !== '') {
                $query->byManufacturer($request->manufacturer);
            }

            if ($request->has('active') && $request->active !== '') {
                $query->where('active', $request->boolean('active'));
            }

            if ($request->has('search') && $request->search !== '') {
                $query->search($request->search);
            }

            if ($request->has('capability') && $request->capability !== '') {
                $query->withCapability($request->capability);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $devices = $query->paginate($perPage);

            // Datos para filtros
            $users = User::select('id', 'name')->orderBy('name')->get();
            $types = Device::getAvailableTypes();
            $capabilities = Device::getCapabilityOptions();
            $manufacturers = Device::select('manufacturer')
                ->whereNotNull('manufacturer')
                ->distinct()
                ->pluck('manufacturer')
                ->sort()
                ->values();

            // Estadísticas
            $stats = [
                'total' => Device::count(),
                'active' => Device::where('active', true)->count(),
                'online' => Device::online()->count(),
                'offline' => Device::offline()->count(),
            ];

            return view('devices.index', compact(
                'devices',
                'users',
                'types',
                'capabilities',
                'manufacturers',
                'stats'
            ));

        } catch (\Exception $e) {
            Log::error('Error al obtener dispositivos: ' . $e->getMessage());
            
            return view('devices.index')->withErrors([
                'error' => 'Error al cargar los dispositivos. Por favor, inténtalo de nuevo.'
            ]);
        }
    }

    /**
     * Show the form for creating a new device.
     */
    public function create(): View
    {
        try {
            $users = User::select('id', 'name')->orderBy('name')->get();
            $types = Device::getAvailableTypes();
            $capabilities = Device::getCapabilityOptions();

            return view('devices.create', compact('users', 'types', 'capabilities'));

        } catch (\Exception $e) {
            Log::error('Error al mostrar formulario de creación: ' . $e->getMessage());
            
            return redirect()->route('devices.index')->withErrors([
                'error' => 'Error al cargar el formulario. Por favor, inténtalo de nuevo.'
            ]);
        }
    }

    /**
     * Store a newly created device.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'type' => ['required', Rule::in(array_keys(Device::getAvailableTypes()))],
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
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $device = Device::create($request->all());

            DB::commit();

            return redirect()->route('devices.show', $device)
                ->with('success', 'Dispositivo creado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear dispositivo: ' . $e->getMessage());
            
            return redirect()->back()
                ->withErrors(['error' => 'Error al crear el dispositivo. Por favor, inténtalo de nuevo.'])
                ->withInput();
        }
    }

    /**
     * Display the specified device.
     */
    public function show(Device $device): View
    {
        try {
            $device->load(['user', 'consumptionPoint']);

            return view('devices.show', compact('device'));

        } catch (\Exception $e) {
            Log::error('Error al mostrar dispositivo: ' . $e->getMessage());
            
            return redirect()->route('devices.index')->withErrors([
                'error' => 'Error al cargar el dispositivo. Por favor, inténtalo de nuevo.'
            ]);
        }
    }

    /**
     * Show the form for editing the specified device.
     */
    public function edit(Device $device): View
    {
        try {
            $users = User::select('id', 'name')->orderBy('name')->get();
            $types = Device::getAvailableTypes();
            $capabilities = Device::getCapabilityOptions();

            return view('devices.edit', compact('device', 'users', 'types', 'capabilities'));

        } catch (\Exception $e) {
            Log::error('Error al mostrar formulario de edición: ' . $e->getMessage());
            
            return redirect()->route('devices.index')->withErrors([
                'error' => 'Error al cargar el formulario. Por favor, inténtalo de nuevo.'
            ]);
        }
    }

    /**
     * Update the specified device.
     */
    public function update(Request $request, Device $device): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'type' => ['sometimes', 'required', Rule::in(array_keys(Device::getAvailableTypes()))],
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
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $device->update($request->all());

            DB::commit();

            return redirect()->route('devices.show', $device)
                ->with('success', 'Dispositivo actualizado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar dispositivo: ' . $e->getMessage());
            
            return redirect()->back()
                ->withErrors(['error' => 'Error al actualizar el dispositivo. Por favor, inténtalo de nuevo.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified device.
     */
    public function destroy(Device $device): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $device->delete();

            DB::commit();

            return redirect()->route('devices.index')
                ->with('success', 'Dispositivo eliminado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar dispositivo: ' . $e->getMessage());
            
            return redirect()->back()->withErrors([
                'error' => 'Error al eliminar el dispositivo. Por favor, inténtalo de nuevo.'
            ]);
        }
    }

    /**
     * Activate the specified device.
     */
    public function activate(Device $device): RedirectResponse
    {
        try {
            $device->activate();

            return redirect()->back()
                ->with('success', 'Dispositivo activado exitosamente');

        } catch (\Exception $e) {
            Log::error('Error al activar dispositivo: ' . $e->getMessage());
            
            return redirect()->back()->withErrors([
                'error' => 'Error al activar el dispositivo. Por favor, inténtalo de nuevo.'
            ]);
        }
    }

    /**
     * Deactivate the specified device.
     */
    public function deactivate(Device $device): RedirectResponse
    {
        try {
            $device->deactivate();

            return redirect()->back()
                ->with('success', 'Dispositivo desactivado exitosamente');

        } catch (\Exception $e) {
            Log::error('Error al desactivar dispositivo: ' . $e->getMessage());
            
            return redirect()->back()->withErrors([
                'error' => 'Error al desactivar el dispositivo. Por favor, inténtalo de nuevo.'
            ]);
        }
    }

    /**
     * Update device communication timestamp.
     */
    public function updateCommunication(Device $device): RedirectResponse
    {
        try {
            $device->updateCommunication();

            return redirect()->back()
                ->with('success', 'Comunicación del dispositivo actualizada exitosamente');

        } catch (\Exception $e) {
            Log::error('Error al actualizar comunicación del dispositivo: ' . $e->getMessage());
            
            return redirect()->back()->withErrors([
                'error' => 'Error al actualizar la comunicación del dispositivo. Por favor, inténtalo de nuevo.'
            ]);
        }
    }

    /**
     * Show device statistics.
     */
    public function statistics(): View
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

            return view('devices.statistics', compact('stats'));

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas de dispositivos: ' . $e->getMessage());
            
            return redirect()->route('devices.index')->withErrors([
                'error' => 'Error al cargar las estadísticas. Por favor, inténtalo de nuevo.'
            ]);
        }
    }

    /**
     * Show device dashboard.
     */
    public function dashboard(): View
    {
        try {
            $devices = Device::with(['user'])
                ->orderBy('last_communication', 'desc')
                ->limit(10)
                ->get();

            $recentDevices = Device::where('last_communication', '>=', now()->subHours(24))
                ->count();

            $deviceTypes = Device::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type');

            $onlineDevices = Device::online()->count();
            $totalDevices = Device::count();

            return view('devices.dashboard', compact(
                'devices',
                'recentDevices',
                'deviceTypes',
                'onlineDevices',
                'totalDevices'
            ));

        } catch (\Exception $e) {
            Log::error('Error al cargar dashboard de dispositivos: ' . $e->getMessage());
            
            return redirect()->route('devices.index')->withErrors([
                'error' => 'Error al cargar el dashboard. Por favor, inténtalo de nuevo.'
            ]);
        }
    }

    /**
     * Bulk update devices.
     */
    public function bulkUpdate(Request $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'device_ids' => 'required|array|min:1',
                'device_ids.*' => 'exists:devices,id',
                'updates' => 'required|array',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
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

            return redirect()->back()
                ->with('success', "{$updatedCount} dispositivos actualizados exitosamente");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en actualización masiva de dispositivos: ' . $e->getMessage());
            
            return redirect()->back()->withErrors([
                'error' => 'Error en la actualización masiva. Por favor, inténtalo de nuevo.'
            ]);
        }
    }

    /**
     * Bulk delete devices.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'device_ids' => 'required|array|min:1',
                'device_ids.*' => 'exists:devices,id',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $deviceIds = $request->device_ids;
            $devices = Device::whereIn('id', $deviceIds)->get();
            $deletedCount = 0;

            foreach ($devices as $device) {
                $device->delete();
                $deletedCount++;
            }

            DB::commit();

            return redirect()->back()
                ->with('success', "{$deletedCount} dispositivos eliminados exitosamente");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en eliminación masiva de dispositivos: ' . $e->getMessage());
            
            return redirect()->back()->withErrors([
                'error' => 'Error en la eliminación masiva. Por favor, inténtalo de nuevo.'
            ]);
        }
    }
}
