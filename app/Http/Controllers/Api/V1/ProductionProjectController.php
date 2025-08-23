<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\ProductionProject\StoreProductionProjectRequest;
use App\Http\Requests\Api\V1\ProductionProject\UpdateProductionProjectRequest;
use App\Http\Resources\Api\V1\ProductionProject\ProductionProjectResource;
use App\Http\Resources\Api\V1\ProductionProject\ProductionProjectCollection;
use App\Models\ProductionProject;
use App\Models\Organization;
use App\Models\User;
use App\Models\EnergySource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @group Production Project Management
 *
 * APIs for managing energy production projects
 */
class ProductionProjectController extends Controller
{
    /**
     * Display a listing of production projects.
     *
     * @authenticated
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam search string Search in name, description, location. Example: "Solar Farm"
     * @queryParam project_type string Filter by project type. Example: "solar_farm"
     * @queryParam technology_type string Filter by technology type. Example: "photovoltaic"
     * @queryParam status string Filter by project status. Example: "in_progress"
     * @queryParam organization_id integer Filter by organization. Example: 1
     * @queryParam energy_source_id integer Filter by energy source. Example: 1
     * @queryParam is_active boolean Filter by active status. Example: true
     * @queryParam is_public boolean Filter by public status. Example: true
     * @queryParam accepts_crowdfunding boolean Filter by crowdfunding status. Example: true
     * @queryParam regulatory_approved boolean Filter by regulatory approval. Example: true
     * @queryParam capacity_min float Minimum capacity in kW. Example: 100.0
     * @queryParam capacity_max float Maximum capacity in kW. Example: 1000.0
     * @queryParam completion_min float Minimum completion percentage. Example: 25.0
     * @queryParam completion_max float Maximum completion percentage. Example: 75.0
     * @queryParam investment_min float Minimum investment amount. Example: 10000.0
     * @queryParam investment_max float Maximum investment amount. Example: 100000.0
     * @queryParam location_country string Filter by country code. Example: "ES"
     * @queryParam location_city string Filter by city. Example: "Madrid"
     * @queryParam sort_by string Sort field. Example: "created_at"
     * @queryParam sort_direction string Sort direction (asc/desc). Example: "desc"
     * @queryParam created_at_from date Filter by creation date from. Example: "2024-01-01"
     * @queryParam created_at_to date Filter by creation date to. Example: "2024-12-31"
     * @queryParam construction_start_from date Filter by construction start from. Example: "2024-01-01"
     * @queryParam construction_start_to date Filter by construction start to. Example: "2024-12-31"
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Solar Farm Madrid",
     *       "description": "Large scale solar energy production facility",
     *       "project_type": "solar_farm",
     *       "technology_type": "photovoltaic",
     *       "status": "in_progress",
     *       "capacity_kw": 5000.0,
     *       "completion_percentage": 75.5,
     *       "total_investment": 5000000.0,
     *       "location_city": "Madrid",
     *       "location_country": "ES",
     *       "is_active": true,
     *       "is_public": true,
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
            $query = ProductionProject::with([
                'organization', 
                'ownerUser', 
                'energySource', 
                'createdBy'
            ]);

            // Filtros de búsqueda
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('location_address', 'like', "%{$search}%")
                      ->orWhere('location_city', 'like', "%{$search}%")
                      ->orWhere('manufacturer', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%");
                });
            }

            // Filtros por tipo de proyecto
            if ($request->filled('project_type')) {
                $query->where('project_type', $request->project_type);
            }

            // Filtros por tipo de tecnología
            if ($request->filled('technology_type')) {
                $query->where('technology_type', $request->technology_type);
            }

            // Filtros por estado
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filtros por organización
            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            // Filtros por fuente de energía
            if ($request->filled('energy_source_id')) {
                $query->where('energy_source_id', $request->energy_source_id);
            }

            // Filtros por estado activo
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // Filtros por estado público
            if ($request->filled('is_public')) {
                $query->where('is_public', $request->boolean('is_public'));
            }

            // Filtros por crowdfunding
            if ($request->filled('accepts_crowdfunding')) {
                $query->where('accepts_crowdfunding', $request->boolean('accepts_crowdfunding'));
            }

            // Filtros por aprobación regulatoria
            if ($request->filled('regulatory_approved')) {
                $query->where('regulatory_approved', $request->boolean('regulatory_approved'));
            }

            // Filtros por capacidad
            if ($request->filled('capacity_min')) {
                $query->where('capacity_kw', '>=', $request->capacity_min);
            }

            if ($request->filled('capacity_max')) {
                $query->where('capacity_kw', '<=', $request->capacity_max);
            }

            // Filtros por porcentaje de completado
            if ($request->filled('completion_min')) {
                $query->where('completion_percentage', '>=', $request->completion_min);
            }

            if ($request->filled('completion_max')) {
                $query->where('completion_percentage', '<=', $request->completion_max);
            }

            // Filtros por inversión
            if ($request->filled('investment_min')) {
                $query->where('total_investment', '>=', $request->investment_min);
            }

            if ($request->filled('investment_max')) {
                $query->where('total_investment', '<=', $request->investment_max);
            }

            // Filtros por ubicación
            if ($request->filled('location_country')) {
                $query->where('location_country', $request->location_country);
            }

            if ($request->filled('location_city')) {
                $query->where('location_city', 'like', "%{$request->location_city}%");
            }

            // Filtros por fechas de creación
            if ($request->filled('created_at_from')) {
                $query->whereDate('created_at', '>=', $request->created_at_from);
            }

            if ($request->filled('created_at_to')) {
                $query->whereDate('created_at', '<=', $request->created_at_to);
            }

            // Filtros por fechas de construcción
            if ($request->filled('construction_start_from')) {
                $query->whereDate('construction_start_date', '>=', $request->construction_start_from);
            }

            if ($request->filled('construction_start_to')) {
                $query->whereDate('construction_start_date', '<=', $request->construction_start_to);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            if (in_array($sortBy, ['name', 'capacity_kw', 'completion_percentage', 'total_investment', 'created_at', 'construction_start_date'])) {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Paginación
            $perPage = min($request->get('per_page', 15), 100);
            $projects = $query->paginate($perPage);

            return response()->json([
                'data' => ProductionProjectResource::collection($projects),
                'meta' => [
                    'current_page' => $projects->currentPage(),
                    'total' => $projects->total(),
                    'per_page' => $projects->perPage(),
                    'last_page' => $projects->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching production projects: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los proyectos de producción',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created production project.
     *
     * @authenticated
     * @bodyParam name string required The name of the project. Example: "Solar Farm Madrid"
     * @bodyParam description string The project description. Example: "Large scale solar energy production facility"
     * @bodyParam project_type string required The type of project. Example: "solar_farm"
     * @bodyParam technology_type string required The technology type. Example: "photovoltaic"
     * @bodyParam status string required The project status. Example: "planning"
     * @bodyParam organization_id integer required The organization ID. Example: 1
     * @bodyParam owner_user_id integer required The owner user ID. Example: 1
     * @bodyParam energy_source_id integer required The energy source ID. Example: 1
     * @bodyParam capacity_kw float The capacity in kW. Example: 5000.0
     * @bodyParam estimated_annual_production float The estimated annual production in kWh. Example: 7500000.0
     * @bodyParam total_investment float The total investment amount. Example: 5000000.0
     * @bodyParam location_address string The location address. Example: "Calle Solar 123"
     * @bodyParam location_city string The location city. Example: "Madrid"
     * @bodyParam location_country string The location country code. Example: "ES"
     * @bodyParam is_active boolean Whether the project is active. Example: true
     * @bodyParam is_public boolean Whether the project is public. Example: true
     *
     * @response 201 {
     *   "message": "Proyecto de producción creado exitosamente",
     *   "data": {
     *     "id": 1,
     *     "name": "Solar Farm Madrid",
     *     "project_type": "solar_farm",
     *     "status": "planning",
     *     "created_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "Los datos proporcionados no son válidos",
     *   "errors": {
     *     "name": ["El campo nombre es obligatorio"],
     *     "project_type": ["El campo tipo de proyecto es obligatorio"]
     *   }
     * }
     */
    public function store(StoreProductionProjectRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $project = ProductionProject::create($request->validated());

            // Cargar las relaciones para el recurso
            $project->load(['organization', 'ownerUser', 'energySource', 'createdBy']);

            DB::commit();

            Log::info('Production project created', [
                'project_id' => $project->id,
                'user_id' => auth()->id(),
                'project_name' => $project->name
            ]);

            return response()->json([
                'message' => 'Proyecto de producción creado exitosamente',
                'data' => new ProductionProjectResource($project)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating production project: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al crear el proyecto de producción',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified production project.
     *
     * @authenticated
     * @urlParam id integer required The production project ID. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "Solar Farm Madrid",
     *     "description": "Large scale solar energy production facility",
     *     "project_type": "solar_farm",
     *     "technology_type": "photovoltaic",
     *     "status": "in_progress",
     *     "capacity_kw": 5000.0,
     *     "completion_percentage": 75.5,
     *     "total_investment": 5000000.0,
     *     "location_city": "Madrid",
     *     "location_country": "ES",
     *     "is_active": true,
     *     "is_public": true,
     *     "created_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "Proyecto de producción no encontrado"
     * }
     */
    public function show(ProductionProject $productionProject): JsonResponse
    {
        try {
            $productionProject->load([
                'organization', 
                'ownerUser', 
                'energySource', 
                'createdBy',
                'installations',
                'meters',
                'readings',
                'milestones'
            ]);

            return response()->json([
                'data' => new ProductionProjectResource($productionProject)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching production project: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener el proyecto de producción',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified production project.
     *
     * @authenticated
     * @urlParam id integer required The production project ID. Example: 1
     * @bodyParam name string The name of the project. Example: "Solar Farm Madrid Updated"
     * @bodyParam description string The project description. Example: "Updated description"
     * @bodyParam project_type string The type of project. Example: "solar_farm"
     * @bodyParam technology_type string The technology type. Example: "photovoltaic"
     * @bodyParam status string The project status. Example: "in_progress"
     * @bodyParam capacity_kw float The capacity in kW. Example: 6000.0
     * @bodyParam completion_percentage float The completion percentage. Example: 80.0
     * @bodyParam total_investment float The total investment amount. Example: 6000000.0
     * @bodyParam is_active boolean Whether the project is active. Example: true
     * @bodyParam is_public boolean Whether the project is public. Example: true
     *
     * @response 200 {
     *   "message": "Proyecto de producción actualizado exitosamente",
     *   "data": {
     *     "id": 1,
     *     "name": "Solar Farm Madrid Updated",
     *     "status": "in_progress",
     *     "updated_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "Proyecto de producción no encontrado"
     * }
     *
     * @response 422 {
     *   "message": "Los datos proporcionados no son válidos",
     *   "errors": {
     *     "capacity_kw": ["La capacidad debe ser un número positivo"]
     *   }
     * }
     */
    public function update(UpdateProductionProjectRequest $request, ProductionProject $productionProject): JsonResponse
    {
        try {
            DB::beginTransaction();

            $productionProject->update($request->validated());

            // Cargar las relaciones para el recurso
            $productionProject->load([
                'organization', 
                'ownerUser', 
                'energySource', 
                'createdBy'
            ]);

            DB::commit();

            Log::info('Production project updated', [
                'project_id' => $productionProject->id,
                'user_id' => auth()->id(),
                'project_name' => $productionProject->name
            ]);

            return response()->json([
                'message' => 'Proyecto de producción actualizado exitosamente',
                'data' => new ProductionProjectResource($productionProject)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating production project: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al actualizar el proyecto de producción',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified production project.
     *
     * @authenticated
     * @urlParam id integer required The production project ID. Example: 1
     *
     * @response 200 {
     *   "message": "Proyecto de producción eliminado exitosamente"
     * }
     *
     * @response 404 {
     *   "message": "Proyecto de producción no encontrado"
     * }
     */
    public function destroy(ProductionProject $productionProject): JsonResponse
    {
        try {
            DB::beginTransaction();

            $projectName = $productionProject->name;
            $projectId = $productionProject->id;

            $productionProject->delete();

            DB::commit();

            Log::info('Production project deleted', [
                'project_id' => $projectId,
                'user_id' => auth()->id(),
                'project_name' => $projectName
            ]);

            return response()->json([
                'message' => 'Proyecto de producción eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting production project: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al eliminar el proyecto de producción',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get production project statistics.
     *
     * @authenticated
     * @queryParam organization_id integer Filter by organization. Example: 1
     * @queryParam project_type string Filter by project type. Example: "solar_farm"
     * @queryParam status string Filter by project status. Example: "in_progress"
     *
     * @response 200 {
     *   "data": {
     *     "total_projects": 25,
     *     "active_projects": 18,
     *     "completed_projects": 5,
     *     "total_capacity_kw": 125000.0,
     *     "total_investment": 125000000.0,
     *     "average_completion": 65.5,
     *     "projects_by_type": {
     *       "solar_farm": 15,
     *       "wind_farm": 8,
     *       "hydroelectric": 2
     *     },
     *     "projects_by_status": {
     *       "planning": 3,
     *       "in_progress": 18,
     *       "completed": 5,
     *       "on_hold": 2
     *     }
     *   }
     * }
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $query = ProductionProject::query();

            // Filtros
            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            if ($request->filled('project_type')) {
                $query->where('project_type', $request->project_type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $totalProjects = $query->count();
            $activeProjects = (clone $query)->where('is_active', true)->count();
            $completedProjects = (clone $query)->where('status', 'completed')->count();
            $totalCapacity = (clone $query)->sum('capacity_kw');
            $totalInvestment = (clone $query)->sum('total_investment');
            $averageCompletion = (clone $query)->avg('completion_percentage');

            // Proyectos por tipo
            $projectsByType = (clone $query)
                ->selectRaw('project_type, COUNT(*) as count')
                ->groupBy('project_type')
                ->pluck('count', 'project_type')
                ->toArray();

            // Proyectos por estado
            $projectsByStatus = (clone $query)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            return response()->json([
                'data' => [
                    'total_projects' => $totalProjects,
                    'active_projects' => $activeProjects,
                    'completed_projects' => $completedProjects,
                    'total_capacity_kw' => $totalCapacity,
                    'total_investment' => $totalInvestment,
                    'average_completion' => round($averageCompletion, 1),
                    'projects_by_type' => $projectsByType,
                    'projects_by_status' => $projectsByStatus,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching production project statistics: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las estadísticas de proyectos de producción',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get production project types.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": {
     *     "solar_farm": "Granja Solar",
     *     "wind_farm": "Parque Eólico",
     *     "hydroelectric": "Hidroeléctrica",
     *     "biomass": "Biomasa",
     *     "geothermal": "Geotérmica",
     *     "hybrid": "Híbrida",
     *     "storage": "Almacenamiento",
     *     "grid_upgrade": "Actualización de Red",
     *     "other": "Otro"
     *   }
     * }
     */
    public function types(): JsonResponse
    {
        try {
            return response()->json([
                'data' => ProductionProject::getProjectTypes()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching production project types: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los tipos de proyectos de producción',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get production project statuses.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": {
     *     "planning": "Planificación",
     *     "approved": "Aprobado",
     *     "in_progress": "En Progreso",
     *     "on_hold": "En Espera",
     *     "completed": "Completado",
     *     "cancelled": "Cancelado",
     *     "maintenance": "Mantenimiento"
     *   }
     * }
     */
    public function statuses(): JsonResponse
    {
        try {
            return response()->json([
                'data' => ProductionProject::getStatuses()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching production project statuses: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los estados de proyectos de producción',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get production project technology types.
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
     *     "other": "Otro"
     *   }
     * }
     */
    public function technologyTypes(): JsonResponse
    {
        try {
            return response()->json([
                'data' => ProductionProject::getTechnologyTypes()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching production project technology types: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los tipos de tecnología de proyectos de producción',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Toggle project active status.
     *
     * @authenticated
     * @urlParam id integer required The production project ID. Example: 1
     *
     * @response 200 {
     *   "message": "Estado del proyecto actualizado exitosamente",
     *   "data": {
     *     "id": 1,
     *     "is_active": false,
     *     "updated_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     */
    public function toggleActive(ProductionProject $productionProject): JsonResponse
    {
        try {
            $productionProject->update([
                'is_active' => !$productionProject->is_active
            ]);

            Log::info('Production project active status toggled', [
                'project_id' => $productionProject->id,
                'user_id' => auth()->id(),
                'new_status' => $productionProject->is_active
            ]);

            return response()->json([
                'message' => 'Estado del proyecto actualizado exitosamente',
                'data' => [
                    'id' => $productionProject->id,
                    'is_active' => $productionProject->is_active,
                    'updated_at' => $productionProject->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling production project active status: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al cambiar el estado del proyecto',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Toggle project public status.
     *
     * @authenticated
     * @urlParam id integer required The production project ID. Example: 1
     *
     * @response 200 {
     *   "message": "Estado público del proyecto actualizado exitosamente",
     *   "data": {
     *     "id": 1,
     *     "is_public": true,
     *     "updated_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     */
    public function togglePublic(ProductionProject $productionProject): JsonResponse
    {
        try {
            $productionProject->update([
                'is_public' => !$productionProject->is_public
            ]);

            Log::info('Production project public status toggled', [
                'project_id' => $productionProject->id,
                'user_id' => auth()->id(),
                'new_status' => $productionProject->is_public
            ]);

            return response()->json([
                'message' => 'Estado público del proyecto actualizado exitosamente',
                'data' => [
                    'id' => $productionProject->id,
                    'is_public' => $productionProject->is_public,
                    'updated_at' => $productionProject->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling production project public status: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al cambiar el estado público del proyecto',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update project status.
     *
     * @authenticated
     * @urlParam id integer required The production project ID. Example: 1
     * @bodyParam status string required The new status. Example: "in_progress"
     *
     * @response 200 {
     *   "message": "Estado del proyecto actualizado exitosamente",
     *   "data": {
     *     "id": 1,
     *     "status": "in_progress",
     *     "updated_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     */
    public function updateStatus(Request $request, ProductionProject $productionProject): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|string|in:' . implode(',', array_keys(ProductionProject::getStatuses()))
            ]);

            $oldStatus = $productionProject->status;
            $productionProject->update(['status' => $request->status]);

            Log::info('Production project status updated', [
                'project_id' => $productionProject->id,
                'user_id' => auth()->id(),
                'old_status' => $oldStatus,
                'new_status' => $request->status
            ]);

            return response()->json([
                'message' => 'Estado del proyecto actualizado exitosamente',
                'data' => [
                    'id' => $productionProject->id,
                    'status' => $productionProject->status,
                    'updated_at' => $productionProject->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating production project status: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al actualizar el estado del proyecto',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Duplicate a production project.
     *
     * @authenticated
     * @urlParam id integer required The production project ID. Example: 1
     * @bodyParam name string The new name for the duplicated project. Example: "Solar Farm Madrid - Copy"
     *
     * @response 200 {
     *   "message": "Proyecto duplicado exitosamente",
     *   "data": {
     *     "id": 2,
     *     "name": "Solar Farm Madrid - Copy",
     *     "status": "planning",
     *     "completion_percentage": 0,
     *     "created_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     */
    public function duplicate(Request $request, ProductionProject $productionProject): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'nullable|string|max:255'
            ]);

            $newProject = $productionProject->replicate();
            $newProject->name = $request->name ?? $productionProject->name . ' (Copia)';
            $newProject->slug = $productionProject->slug . '-copy-' . time();
            $newProject->status = 'planning';
            $newProject->completion_percentage = 0;
            $newProject->is_active = false;
            $newProject->is_public = false;
            $newProject->crowdfunding_raised = 0;
            $newProject->save();

            Log::info('Production project duplicated', [
                'original_project_id' => $productionProject->id,
                'new_project_id' => $newProject->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Proyecto duplicado exitosamente',
                'data' => new ProductionProjectResource($newProject)
            ]);

        } catch (\Exception $e) {
            Log::error('Error duplicating production project: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al duplicar el proyecto',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
