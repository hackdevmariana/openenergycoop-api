<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\EnergyInstallation\StoreEnergyInstallationRequest;
use App\Http\Requests\Api\V1\EnergyInstallation\UpdateEnergyInstallationRequest;
use App\Http\Resources\Api\V1\EnergyInstallation\EnergyInstallationResource;
use App\Http\Resources\Api\V1\EnergyInstallation\EnergyInstallationCollection;
use App\Models\EnergyInstallation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @group Energy Installation Management
 *
 * APIs for managing energy installations
 */
class EnergyInstallationController extends Controller
{
    /**
     * Display a listing of energy installations.
     *
     * @authenticated
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam search string Search in name, description, installation_number. Example: "Solar Panel Installation"
     * @queryParam installation_type string Filter by installation type. Example: "residential"
     * @queryParam status string Filter by status. Example: "operational"
     * @queryParam priority string Filter by priority. Example: "high"
     * @queryParam energy_source_id integer Filter by energy source ID. Example: 1
     * @queryParam customer_id integer Filter by customer ID. Example: 1
     * @queryParam project_id integer Filter by project ID. Example: 1
     * @queryParam is_active boolean Filter by active status. Example: true
     * @queryParam capacity_min float Minimum installed capacity in kW. Example: 100.0
     * @queryParam capacity_max float Maximum installed capacity in kW. Example: 1000.0
     * @queryParam efficiency_min float Minimum efficiency percentage. Example: 80.0
     * @queryParam efficiency_max float Maximum efficiency percentage. Example: 95.0
     * @queryParam installation_date_from date Filter by installation date from. Example: "2024-01-01"
     * @queryParam installation_date_to date Filter by installation date to. Example: "2024-12-31"
     * @queryParam sort_by string Sort field. Example: "name"
     * @queryParam sort_direction string Sort direction (asc/desc). Example: "asc"
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "installation_number": "INST-001",
     *       "name": "Solar Panel Installation",
     *       "installation_type": "residential",
     *       "status": "operational",
     *       "priority": "medium",
     *       "installed_capacity_kw": 500.0,
     *       "efficiency_rating": 85.5,
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
            $query = EnergyInstallation::with(['energySource', 'customer', 'project']);

            // Filtros de búsqueda
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('installation_number', 'like', "%{$search}%")
                      ->orWhere('location_address', 'like', "%{$search}%");
                });
            }

            // Filtros por tipo de instalación
            if ($request->filled('installation_type')) {
                $query->where('installation_type', $request->installation_type);
            }

            // Filtros por estado
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filtros por prioridad
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            // Filtros por fuente de energía
            if ($request->filled('energy_source_id')) {
                $query->where('energy_source_id', $request->energy_source_id);
            }

            // Filtros por cliente
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            // Filtros por proyecto
            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            // Filtros por estado activo
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // Filtros por capacidad
            if ($request->filled('capacity_min')) {
                $query->where('installed_capacity_kw', '>=', $request->capacity_min);
            }

            if ($request->filled('capacity_max')) {
                $query->where('installed_capacity_kw', '<=', $request->capacity_max);
            }

            // Filtros por eficiencia
            if ($request->filled('efficiency_min')) {
                $query->where('efficiency_rating', '>=', $request->efficiency_min);
            }

            if ($request->filled('efficiency_max')) {
                $query->where('efficiency_rating', '<=', $request->efficiency_max);
            }

            // Filtros por fechas de instalación
            if ($request->filled('installation_date_from')) {
                $query->whereDate('installation_date', '>=', $request->installation_date_from);
            }

            if ($request->filled('installation_date_to')) {
                $query->whereDate('installation_date', '<=', $request->installation_date_to);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            if (in_array($sortBy, ['name', 'installation_number', 'installed_capacity_kw', 'efficiency_rating', 'installation_date', 'created_at'])) {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Paginación
            $perPage = min($request->get('per_page', 15), 100);
            $installations = $query->paginate($perPage);

            return response()->json([
                'data' => EnergyInstallationResource::collection($installations),
                'meta' => [
                    'current_page' => $installations->currentPage(),
                    'total' => $installations->total(),
                    'per_page' => $installations->perPage(),
                    'last_page' => $installations->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy installations: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las instalaciones de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created energy installation.
     *
     * @authenticated
     * @bodyParam installation_number string required The installation number. Example: "INST-001"
     * @bodyParam name string required The name of the installation. Example: "Solar Panel Installation"
     * @bodyParam description string The description of the installation. Example: "Residential solar panel installation"
     * @bodyParam installation_type string required The type of installation. Example: "residential"
     * @bodyParam status string required The status of the installation. Example: "planned"
     * @bodyParam priority string required The priority of the installation. Example: "medium"
     * @bodyParam energy_source_id integer required The energy source ID. Example: 1
     * @bodyParam customer_id integer The customer ID. Example: 1
     * @bodyParam project_id integer The project ID. Example: 1
     * @bodyParam installed_capacity_kw float required The installed capacity in kW. Example: 500.0
     * @bodyParam operational_capacity_kw float The operational capacity in kW. Example: 500.0
     * @bodyParam efficiency_rating float The efficiency rating percentage. Example: 85.5
     * @bodyParam installation_date date required The installation date. Example: "2024-01-15"
     * @bodyParam commissioning_date date The commissioning date. Example: "2024-01-20"
     *
     * @response 201 {
     *   "message": "Instalación de energía creada exitosamente",
     *   "data": {
     *     "id": 1,
     *     "installation_number": "INST-001",
     *     "name": "Solar Panel Installation",
     *     "installation_type": "residential",
     *     "status": "planned",
     *     "created_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "Los datos proporcionados no son válidos",
     *   "errors": {
     *     "installation_number": ["El campo número de instalación es obligatorio"],
     *     "name": ["El campo nombre es obligatorio"]
     *   }
     * }
     */
    public function store(StoreEnergyInstallationRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $installation = EnergyInstallation::create($request->validated());

            DB::commit();

            Log::info('Energy installation created', [
                'installation_id' => $installation->id,
                'user_id' => auth()->id(),
                'installation_number' => $installation->installation_number
            ]);

            return response()->json([
                'message' => 'Instalación de energía creada exitosamente',
                'data' => new EnergyInstallationResource($installation)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating energy installation: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al crear la instalación de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified energy installation.
     *
     * @authenticated
     * @urlParam id integer required The energy installation ID. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "installation_number": "INST-001",
     *     "name": "Solar Panel Installation",
     *     "description": "Residential solar panel installation",
     *     "installation_type": "residential",
     *     "status": "operational",
     *     "priority": "medium",
     *     "installed_capacity_kw": 500.0,
     *     "efficiency_rating": 85.5,
     *     "is_active": true,
     *     "created_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "Instalación de energía no encontrada"
     * }
     */
    public function show(EnergyInstallation $energyInstallation): JsonResponse
    {
        try {
            $energyInstallation->load(['energySource', 'customer', 'project', 'installedBy', 'managedBy', 'createdBy', 'approvedBy']);

            return response()->json([
                'data' => new EnergyInstallationResource($energyInstallation)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy installation: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener la instalación de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified energy installation.
     *
     * @authenticated
     * @urlParam id integer required The energy installation ID. Example: 1
     * @bodyParam name string The name of the installation. Example: "Updated Solar Panel Installation"
     * @bodyParam description string The description of the installation. Example: "Updated description"
     * @bodyParam installation_type string The type of installation. Example: "residential"
     * @bodyParam status string The status of the installation. Example: "operational"
     * @bodyParam priority string The priority of the installation. Example: "high"
     * @bodyParam efficiency_rating float The efficiency rating percentage. Example: 87.0
     * @bodyParam operational_capacity_kw float The operational capacity in kW. Example: 480.0
     *
     * @response 200 {
     *   "message": "Instalación de energía actualizada exitosamente",
     *   "data": {
     *     "id": 1,
     *     "name": "Updated Solar Panel Installation",
     *     "status": "operational",
     *     "updated_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "Instalación de energía no encontrada"
     * }
     *
     * @response 422 {
     *   "message": "Los datos proporcionados no son válidos",
     *   "errors": {
     *     "efficiency_rating": ["La eficiencia debe ser un número entre 0 y 100"]
     *   }
     * }
     */
    public function update(UpdateEnergyInstallationRequest $request, EnergyInstallation $energyInstallation): JsonResponse
    {
        try {
            DB::beginTransaction();

            $energyInstallation->update($request->validated());

            DB::commit();

            Log::info('Energy installation updated', [
                'installation_id' => $energyInstallation->id,
                'user_id' => auth()->id(),
                'installation_number' => $energyInstallation->installation_number
            ]);

            return response()->json([
                'message' => 'Instalación de energía actualizada exitosamente',
                'data' => new EnergyInstallationResource($energyInstallation)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating energy installation: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al actualizar la instalación de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified energy installation.
     *
     * @authenticated
     * @urlParam id integer required The energy installation ID. Example: 1
     *
     * @response 200 {
     *   "message": "Instalación de energía eliminada exitosamente"
     * }
     *
     * @response 404 {
     *   "message": "Instalación de energía no encontrada"
     * }
     */
    public function destroy(EnergyInstallation $energyInstallation): JsonResponse
    {
        try {
            DB::beginTransaction();

            $installationNumber = $energyInstallation->installation_number;
            $installationId = $energyInstallation->id;

            $energyInstallation->delete();

            DB::commit();

            Log::info('Energy installation deleted', [
                'installation_id' => $installationId,
                'user_id' => auth()->id(),
                'installation_number' => $installationNumber
            ]);

            return response()->json([
                'message' => 'Instalación de energía eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting energy installation: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al eliminar la instalación de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get energy installation statistics.
     *
     * @authenticated
     * @queryParam installation_type string Filter by installation type. Example: "residential"
     * @queryParam status string Filter by status. Example: "operational"
     * @queryParam priority string Filter by priority. Example: "high"
     * @queryParam energy_source_id integer Filter by energy source ID. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "total_installations": 25,
     *     "operational_installations": 20,
     *     "maintenance_installations": 3,
     *     "planned_installations": 2,
     *     "total_capacity_kw": 125000.0,
     *     "average_efficiency": 82.5,
     *     "installations_by_type": {
     *       "residential": 15,
     *       "commercial": 8,
     *       "industrial": 2
     *     },
     *     "installations_by_status": {
     *       "operational": 20,
     *       "maintenance": 3,
     *       "planned": 2
     *     },
     *     "installations_by_priority": {
     *       "low": 10,
     *       "medium": 12,
     *       "high": 3
     *     }
     *   }
     * }
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $query = EnergyInstallation::query();

            // Filtros
            if ($request->filled('installation_type')) {
                $query->where('installation_type', $request->installation_type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            if ($request->filled('energy_source_id')) {
                $query->where('energy_source_id', $request->energy_source_id);
            }

            $totalInstallations = $query->count();
            $operationalInstallations = (clone $query)->where('status', 'operational')->count();
            $maintenanceInstallations = (clone $query)->where('status', 'maintenance')->count();
            $plannedInstallations = (clone $query)->where('status', 'planned')->count();
            $totalCapacity = (clone $query)->sum('installed_capacity_kw');
            $averageEfficiency = (clone $query)->avg('efficiency_rating');

            // Instalaciones por tipo
            $installationsByType = (clone $query)
                ->selectRaw('installation_type, COUNT(*) as count')
                ->groupBy('installation_type')
                ->pluck('count', 'installation_type')
                ->toArray();

            // Instalaciones por estado
            $installationsByStatus = (clone $query)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // Instalaciones por prioridad
            $installationsByPriority = (clone $query)
                ->selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->pluck('count', 'priority')
                ->toArray();

            return response()->json([
                'data' => [
                    'total_installations' => $totalInstallations,
                    'operational_installations' => $operationalInstallations,
                    'maintenance_installations' => $maintenanceInstallations,
                    'planned_installations' => $plannedInstallations,
                    'total_capacity_kw' => $totalCapacity,
                    'average_efficiency' => round($averageEfficiency, 1),
                    'installations_by_type' => $installationsByType,
                    'installations_by_status' => $installationsByStatus,
                    'installations_by_priority' => $installationsByPriority,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy installation statistics: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las estadísticas de instalaciones de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get energy installation types.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": {
     *     "residential": "Residencial",
     *     "commercial": "Comercial",
     *     "industrial": "Industrial",
     *     "utility_scale": "Escala de Utilidad",
     *     "community": "Comunitaria",
     *     "microgrid": "Microred",
     *     "off_grid": "Fuera de la Red",
     *     "grid_tied": "Conectada a la Red"
     *   }
     * }
     */
    public function types(): JsonResponse
    {
        try {
            return response()->json([
                'data' => EnergyInstallation::getInstallationTypes()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching energy installation types: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los tipos de instalaciones de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get energy installation statuses.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": {
     *     "planned": "Planificada",
     *     "approved": "Aprobada",
     *     "in_progress": "En Progreso",
     *     "completed": "Completada",
     *     "operational": "Operativa",
     *     "maintenance": "Mantenimiento",
     *     "decommissioned": "Desmantelada",
     *     "cancelled": "Cancelada"
     *   }
     * }
     */
    public function statuses(): JsonResponse
    {
        try {
            return response()->json([
                'data' => EnergyInstallation::getStatuses()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching energy installation statuses: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los estados de instalaciones de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get energy installation priorities.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": {
     *     "low": "Baja",
     *     "medium": "Media",
     *     "high": "Alta",
     *     "urgent": "Urgente",
     *     "critical": "Crítica"
     *   }
     * }
     */
    public function priorities(): JsonResponse
    {
        try {
            return response()->json([
                'data' => EnergyInstallation::getPriorities()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching energy installation priorities: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las prioridades de instalaciones de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Toggle energy installation active status.
     *
     * @authenticated
     * @urlParam id integer required The energy installation ID. Example: 1
     *
     * @response 200 {
     *   "message": "Estado de la instalación de energía actualizado exitosamente",
     *   "data": {
     *     "id": 1,
     *     "is_active": false,
     *     "updated_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     */
    public function toggleActive(EnergyInstallation $energyInstallation): JsonResponse
    {
        try {
            $energyInstallation->update([
                'is_active' => !$energyInstallation->is_active
            ]);

            Log::info('Energy installation active status toggled', [
                'installation_id' => $energyInstallation->id,
                'user_id' => auth()->id(),
                'new_status' => $energyInstallation->is_active
            ]);

            return response()->json([
                'message' => 'Estado de la instalación de energía actualizado exitosamente',
                'data' => [
                    'id' => $energyInstallation->id,
                    'is_active' => $energyInstallation->is_active,
                    'updated_at' => $energyInstallation->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling energy installation active status: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al cambiar el estado de la instalación de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update energy installation status.
     *
     * @authenticated
     * @urlParam id integer required The energy installation ID. Example: 1
     * @bodyParam status string required The new status. Example: "operational"
     *
     * @response 200 {
     *   "message": "Estado de la instalación de energía actualizado exitosamente",
     *   "data": {
     *     "id": 1,
     *     "status": "operational",
     *     "updated_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     */
    public function updateStatus(Request $request, EnergyInstallation $energyInstallation): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|string|in:' . implode(',', array_keys(EnergyInstallation::getStatuses()))
            ]);

            $oldStatus = $energyInstallation->status;
            $energyInstallation->update(['status' => $request->status]);

            Log::info('Energy installation status updated', [
                'installation_id' => $energyInstallation->id,
                'user_id' => auth()->id(),
                'old_status' => $oldStatus,
                'new_status' => $request->status
            ]);

            return response()->json([
                'message' => 'Estado de la instalación de energía actualizado exitosamente',
                'data' => [
                    'id' => $energyInstallation->id,
                    'status' => $energyInstallation->status,
                    'updated_at' => $energyInstallation->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating energy installation status: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al actualizar el estado de la instalación de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update energy installation priority.
     *
     * @authenticated
     * @urlParam id integer required The energy installation ID. Example: 1
     * @bodyParam priority string required The new priority. Example: "high"
     *
     * @response 200 {
     *   "message": "Prioridad de la instalación de energía actualizada exitosamente",
     *   "data": {
     *     "id": 1,
     *     "priority": "high",
     *     "updated_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     */
    public function updatePriority(Request $request, EnergyInstallation $energyInstallation): JsonResponse
    {
        try {
            $request->validate([
                'priority' => 'required|string|in:' . implode(',', array_keys(EnergyInstallation::getPriorities()))
            ]);

            $oldPriority = $energyInstallation->priority;
            $energyInstallation->update(['priority' => $request->priority]);

            Log::info('Energy installation priority updated', [
                'installation_id' => $energyInstallation->id,
                'user_id' => auth()->id(),
                'old_priority' => $oldPriority,
                'new_priority' => $request->priority
            ]);

            return response()->json([
                'message' => 'Prioridad de la instalación de energía actualizada exitosamente',
                'data' => [
                    'id' => $energyInstallation->id,
                    'priority' => $energyInstallation->priority,
                    'updated_at' => $energyInstallation->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating energy installation priority: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al actualizar la prioridad de la instalación de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Duplicate an energy installation.
     *
     * @authenticated
     * @urlParam id integer required The energy installation ID. Example: 1
     * @bodyParam installation_number string The new installation number. Example: "INST-002"
     * @bodyParam name string The new name for the duplicated installation. Example: "Solar Panel Installation - Copy"
     *
     * @response 200 {
     *   "message": "Instalación de energía duplicada exitosamente",
     *   "data": {
     *     "id": 2,
     *     "installation_number": "INST-002",
     *     "name": "Solar Panel Installation - Copy",
     *     "status": "planned",
     *     "is_active": true,
     *     "created_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     */
    public function duplicate(Request $request, EnergyInstallation $energyInstallation): JsonResponse
    {
        try {
            $request->validate([
                'installation_number' => 'nullable|string|max:255|unique:energy_installations,installation_number',
                'name' => 'nullable|string|max:255'
            ]);

            $newInstallation = $energyInstallation->replicate();
            $newInstallation->installation_number = $request->installation_number ?? $energyInstallation->installation_number . '_copy';
            $newInstallation->name = $request->name ?? $energyInstallation->name . ' (Copia)';
            $newInstallation->status = 'planned';
            $newInstallation->installation_date = null;
            $newInstallation->commissioning_date = null;
            $newInstallation->is_active = true;
            $newInstallation->save();

            Log::info('Energy installation duplicated', [
                'original_installation_id' => $energyInstallation->id,
                'new_installation_id' => $newInstallation->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Instalación de energía duplicada exitosamente',
                'data' => new EnergyInstallationResource($newInstallation)
            ]);

        } catch (\Exception $e) {
            Log::error('Error duplicating energy installation: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al duplicar la instalación de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get operational energy installations.
     *
     * @authenticated
     * @queryParam limit integer Number of operational installations to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "installation_number": "INST-001",
     *       "name": "Solar Panel Installation",
     *       "installation_type": "residential",
     *       "status": "operational",
     *       "installed_capacity_kw": 500.0,
     *       "efficiency_rating": 85.5
     *     }
     *   ]
     * }
     */
    public function operational(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $operationalInstallations = EnergyInstallation::where('status', 'operational')
                ->where('is_active', true)
                ->with(['energySource', 'customer'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergyInstallationResource::collection($operationalInstallations)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching operational energy installations: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las instalaciones de energía operativas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get maintenance energy installations.
     *
     * @authenticated
     * @queryParam limit integer Number of maintenance installations to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 2,
     *       "installation_number": "INST-002",
     *       "name": "Wind Turbine Installation",
     *       "installation_type": "commercial",
     *       "status": "maintenance",
     *       "installed_capacity_kw": 1000.0,
     *       "efficiency_rating": 78.2
     *     }
     *   ]
     * }
     */
    public function maintenance(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $maintenanceInstallations = EnergyInstallation::where('status', 'maintenance')
                ->with(['energySource', 'customer'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergyInstallationResource::collection($maintenanceInstallations)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching maintenance energy installations: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las instalaciones de energía en mantenimiento',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get high priority energy installations.
     *
     * @authenticated
     * @queryParam limit integer Number of high priority installations to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 3,
     *       "installation_number": "INST-003",
     *       "name": "Critical Installation",
     *       "installation_type": "industrial",
     *       "status": "operational",
     *       "priority": "critical",
     *       "installed_capacity_kw": 5000.0
     *     }
     *   ]
     * }
     */
    public function highPriority(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $highPriorityInstallations = EnergyInstallation::whereIn('priority', ['high', 'urgent', 'critical'])
                ->with(['energySource', 'customer'])
                ->orderBy('priority', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergyInstallationResource::collection($highPriorityInstallations)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching high priority energy installations: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las instalaciones de energía de alta prioridad',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get installations by type.
     *
     * @authenticated
     * @urlParam type string required The installation type. Example: "residential"
     * @queryParam limit integer Number of installations to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "installation_number": "INST-001",
     *       "name": "Solar Panel Installation",
     *       "installation_type": "residential",
     *       "status": "operational",
     *       "installed_capacity_kw": 500.0
     *     }
     *   ]
     * }
     */
    public function byType(Request $request, string $type): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $installations = EnergyInstallation::where('installation_type', $type)
                ->with(['energySource', 'customer'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergyInstallationResource::collection($installations)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy installations by type: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las instalaciones de energía por tipo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get installations by customer.
     *
     * @authenticated
     * @urlParam customer_id integer required The customer ID. Example: 1
     * @queryParam limit integer Number of installations to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "installation_number": "INST-001",
     *       "name": "Solar Panel Installation",
     *       "installation_type": "residential",
     *       "status": "operational",
     *       "customer_id": 1
     *     }
     *   ]
     * }
     */
    public function byCustomer(Request $request, int $customerId): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $installations = EnergyInstallation::where('customer_id', $customerId)
                ->with(['energySource', 'project'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergyInstallationResource::collection($installations)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy installations by customer: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las instalaciones de energía por cliente',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get installations by project.
     *
     * @authenticated
     * @urlParam project_id integer required The project ID. Example: 1
     * @queryParam limit integer Number of installations to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "installation_number": "INST-001",
     *       "name": "Solar Panel Installation",
     *       "installation_type": "residential",
     *       "status": "operational",
     *       "project_id": 1
     *     }
     *   ]
     * }
     */
    public function byProject(Request $request, int $projectId): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            $installations = EnergyInstallation::where('project_id', $projectId)
                ->with(['energySource', 'customer'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => EnergyInstallationResource::collection($installations)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy installations by project: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las instalaciones de energía por proyecto',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
