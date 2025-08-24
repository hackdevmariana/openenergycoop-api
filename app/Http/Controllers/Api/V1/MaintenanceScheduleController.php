<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\MaintenanceSchedule\StoreMaintenanceScheduleRequest;
use App\Http\Requests\Api\V1\MaintenanceSchedule\UpdateMaintenanceScheduleRequest;
use App\Http\Resources\Api\V1\MaintenanceSchedule\MaintenanceScheduleCollection;
use App\Http\Resources\Api\V1\MaintenanceSchedule\MaintenanceScheduleResource;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="MaintenanceSchedules",
 *     description="API Endpoints para gestión de programas de mantenimiento"
 * )
 */
class MaintenanceScheduleController extends \App\Http\Controllers\Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/maintenance-schedules",
     *     summary="Listar programas de mantenimiento",
     *     tags={"MaintenanceSchedules"},
     *     @OA\Parameter(name="page", in="query", description="Número de página", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", description="Elementos por página", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", description="Término de búsqueda", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort", in="query", description="Campo de ordenamiento", @OA\Schema(type="string")),
     *     @OA\Parameter(name="order", in="query", description="Orden (asc/desc)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="schedule_type", in="query", description="Filtrar por tipo de programa", @OA\Schema(type="string")),
     *     @OA\Parameter(name="frequency_type", in="query", description="Filtrar por tipo de frecuencia", @OA\Schema(type="string")),
     *     @OA\Parameter(name="priority", in="query", description="Filtrar por prioridad", @OA\Schema(type="string")),
     *     @OA\Parameter(name="is_active", in="query", description="Filtrar por estado activo", @OA\Schema(type="boolean")),
     *     @OA\Response(response=200, description="Lista de programas obtenida exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = MaintenanceSchedule::query()
                ->with(['createdBy', 'approvedBy', 'vendor', 'taskTemplate', 'checklistTemplate']);

            // Filtros
            if ($request->filled('schedule_type')) {
                $query->byType($request->schedule_type);
            }

            if ($request->filled('frequency_type')) {
                $query->byFrequencyType($request->frequency_type);
            }

            if ($request->filled('priority')) {
                $query->byPriority($request->priority);
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->filled('department')) {
                $query->byDepartment($request->department);
            }

            if ($request->filled('category')) {
                $query->byCategory($request->category);
            }

            // Búsqueda
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Ordenamiento
            $sortField = $request->get('sort', 'next_maintenance_date');
            $sortOrder = $request->get('order', 'asc');
            $query->orderBy($sortField, $sortOrder);

            // Paginación
            $perPage = min($request->get('per_page', 15), 100);
            $schedules = $query->paginate($perPage);

            Log::info('MaintenanceSchedules listados', [
                'user_id' => auth()->id(),
                'filters' => $request->only(['schedule_type', 'frequency_type', 'priority', 'is_active']),
                'total' => $schedules->total()
            ]);

            return response()->json(new MaintenanceScheduleCollection($schedules));
        } catch (\Exception $e) {
            Log::error('Error al listar MaintenanceSchedules', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al listar los programas de mantenimiento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/maintenance-schedules",
     *     summary="Crear nuevo programa de mantenimiento",
     *     tags={"MaintenanceSchedules"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreMaintenanceScheduleRequest")
     *     ),
     *     @OA\Response(response=201, description="Programa creado exitosamente"),
     *     @OA\Response(response=422, description="Error de validación"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function store(StoreMaintenanceScheduleRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $schedule = MaintenanceSchedule::create($request->validated());

            DB::commit();

            Log::info('MaintenanceSchedule creado', [
                'user_id' => auth()->id(),
                'schedule_id' => $schedule->id,
                'name' => $schedule->name
            ]);

            return response()->json([
                'message' => 'Programa de mantenimiento creado exitosamente',
                'data' => new MaintenanceScheduleResource($schedule)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al crear MaintenanceSchedule', [
                'user_id' => auth()->id(),
                'data' => $request->validated(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al crear el programa de mantenimiento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/maintenance-schedules/{id}",
     *     summary="Obtener programa de mantenimiento específico",
     *     tags={"MaintenanceSchedules"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del programa", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Programa obtenido exitosamente"),
     *     @OA\Response(response=404, description="Programa no encontrado"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function show(MaintenanceSchedule $maintenanceSchedule): JsonResponse
    {
        try {
            $maintenanceSchedule->load(['createdBy', 'approvedBy', 'vendor', 'taskTemplate', 'checklistTemplate']);

            Log::info('MaintenanceSchedule consultado', [
                'user_id' => auth()->id(),
                'schedule_id' => $maintenanceSchedule->id
            ]);

            return response()->json([
                'data' => new MaintenanceScheduleResource($maintenanceSchedule)
            ]);
        } catch (\Exception $e) {
            Log::error('Error al consultar MaintenanceSchedule', [
                'user_id' => auth()->id(),
                'schedule_id' => $maintenanceSchedule->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al consultar el programa de mantenimiento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/maintenance-schedules/{id}",
     *     summary="Actualizar programa de mantenimiento",
     *     tags={"MaintenanceSchedules"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del programa", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateMaintenanceScheduleRequest")
     *     ),
     *     @OA\Response(response=200, description="Programa actualizado exitosamente"),
     *     @OA\Response(response=422, description="Error de validación"),
     *     @OA\Response(response=404, description="Programa no encontrado"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function update(UpdateMaintenanceScheduleRequest $request, MaintenanceSchedule $maintenanceSchedule): JsonResponse
    {
        try {
            DB::beginTransaction();

            $maintenanceSchedule->update($request->validated());

            DB::commit();

            Log::info('MaintenanceSchedule actualizado', [
                'user_id' => auth()->id(),
                'schedule_id' => $maintenanceSchedule->id,
                'changes' => $request->validated()
            ]);

            return response()->json([
                'message' => 'Programa de mantenimiento actualizado exitosamente',
                'data' => new MaintenanceScheduleResource($maintenanceSchedule)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al actualizar MaintenanceSchedule', [
                'user_id' => auth()->id(),
                'schedule_id' => $maintenanceSchedule->id,
                'data' => $request->validated(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al actualizar el programa de mantenimiento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/maintenance-schedules/{id}",
     *     summary="Eliminar programa de mantenimiento",
     *     tags={"MaintenanceSchedules"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del programa", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Programa eliminado exitosamente"),
     *     @OA\Response(response=404, description="Programa no encontrado"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function destroy(MaintenanceSchedule $maintenanceSchedule): JsonResponse
    {
        try {
            DB::beginTransaction();

            $maintenanceSchedule->delete();

            DB::commit();

            Log::info('MaintenanceSchedule eliminado', [
                'user_id' => auth()->id(),
                'schedule_id' => $maintenanceSchedule->id,
                'name' => $maintenanceSchedule->name
            ]);

            return response()->json([
                'message' => 'Programa de mantenimiento eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al eliminar MaintenanceSchedule', [
                'user_id' => auth()->id(),
                'schedule_id' => $maintenanceSchedule->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al eliminar el programa de mantenimiento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/maintenance-schedules/statistics",
     *     summary="Obtener estadísticas de programas de mantenimiento",
     *     tags={"MaintenanceSchedules"},
     *     @OA\Response(response=200, description="Estadísticas obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_schedules' => MaintenanceSchedule::count(),
                'active_schedules' => MaintenanceSchedule::where('is_active', true)->count(),
                'inactive_schedules' => MaintenanceSchedule::where('is_active', false)->count(),
                'approved_schedules' => MaintenanceSchedule::approved()->count(),
                'pending_approval_schedules' => MaintenanceSchedule::pendingApproval()->count(),
                'high_priority_schedules' => MaintenanceSchedule::highPriority()->count(),
                'preventive_schedules' => MaintenanceSchedule::preventive()->count(),
                'predictive_schedules' => MaintenanceSchedule::predictive()->count(),
                'condition_based_schedules' => MaintenanceSchedule::conditionBased()->count(),
                'overdue_schedules' => MaintenanceSchedule::overdue()->count(),
                'due_soon_schedules' => MaintenanceSchedule::dueSoon()->count(),
                'auto_generate_tasks_schedules' => MaintenanceSchedule::autoGenerateTasks()->count(),
            ];

            Log::info('Estadísticas de MaintenanceSchedules consultadas', [
                'user_id' => auth()->id()
            ]);

            return response()->json(['data' => $stats]);
        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas de MaintenanceSchedules', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/maintenance-schedules/schedule-types",
     *     summary="Obtener tipos de programa disponibles",
     *     tags={"MaintenanceSchedules"},
     *     @OA\Response(response=200, description="Tipos obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function scheduleTypes(): JsonResponse
    {
        try {
            $types = collect(MaintenanceSchedule::getScheduleTypes())->map(function ($label, $value) {
                return [
                    'value' => $value,
                    'label' => $label,
                    'count' => MaintenanceSchedule::byType($value)->count()
                ];
            })->values();

            return response()->json(['data' => $types]);
        } catch (\Exception $e) {
            Log::error('Error al obtener tipos de MaintenanceSchedules', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los tipos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/maintenance-schedules/frequency-types",
     *     summary="Obtener tipos de frecuencia disponibles",
     *     tags={"MaintenanceSchedules"},
     *     @OA\Response(response=200, description="Tipos de frecuencia obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function frequencyTypes(): JsonResponse
    {
        try {
            $types = collect(MaintenanceSchedule::getFrequencyTypes())->map(function ($label, $value) {
                return [
                    'value' => $value,
                    'label' => $label,
                    'count' => MaintenanceSchedule::byFrequencyType($value)->count()
                ];
            })->values();

            return response()->json(['data' => $types]);
        } catch (\Exception $e) {
            Log::error('Error al obtener tipos de frecuencia', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los tipos de frecuencia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/maintenance-schedules/priorities",
     *     summary="Obtener prioridades disponibles",
     *     tags={"MaintenanceSchedules"},
     *     @OA\Response(response=200, description="Prioridades obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function priorities(): JsonResponse
    {
        try {
            $priorities = collect(MaintenanceSchedule::getPriorities())->map(function ($label, $value) {
                return [
                    'value' => $value,
                    'label' => $label,
                    'count' => MaintenanceSchedule::byPriority($value)->count()
                ];
            })->values();

            return response()->json(['data' => $priorities]);
        } catch (\Exception $e) {
            Log::error('Error al obtener prioridades', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las prioridades',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/maintenance-schedules/{id}/toggle-active",
     *     summary="Alternar estado activo del programa",
     *     tags={"MaintenanceSchedules"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del programa", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Estado alternado exitosamente"),
     *     @OA\Response(response=404, description="Programa no encontrado"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function toggleActive(MaintenanceSchedule $maintenanceSchedule): JsonResponse
    {
        try {
            DB::beginTransaction();

            $maintenanceSchedule->update(['is_active' => !$maintenanceSchedule->is_active]);

            DB::commit();

            Log::info('Estado activo de MaintenanceSchedule alternado', [
                'user_id' => auth()->id(),
                'schedule_id' => $maintenanceSchedule->id,
                'new_status' => $maintenanceSchedule->is_active
            ]);

            return response()->json([
                'message' => 'Estado activo alternado exitosamente',
                'data' => new MaintenanceScheduleResource($maintenanceSchedule)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al alternar estado activo', [
                'user_id' => auth()->id(),
                'schedule_id' => $maintenanceSchedule->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al alternar el estado activo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/maintenance-schedules/{id}/duplicate",
     *     summary="Duplicar programa de mantenimiento",
     *     tags={"MaintenanceSchedules"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del programa", @OA\Schema(type="integer")),
     *     @OA\Response(response=201, description="Programa duplicado exitosamente"),
     *     @OA\Response(response=404, description="Programa no encontrado"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function duplicate(MaintenanceSchedule $maintenanceSchedule): JsonResponse
    {
        try {
            DB::beginTransaction();

            $duplicate = $maintenanceSchedule->replicate();
            $duplicate->name = $duplicate->name . ' (Copia)';
            $duplicate->is_active = false;
            $duplicate->next_maintenance_date = null;
            $duplicate->last_maintenance_date = null;
            $duplicate->save();

            DB::commit();

            Log::info('MaintenanceSchedule duplicado', [
                'user_id' => auth()->id(),
                'original_id' => $maintenanceSchedule->id,
                'duplicate_id' => $duplicate->id
            ]);

            return response()->json([
                'message' => 'Programa duplicado exitosamente',
                'data' => new MaintenanceScheduleResource($duplicate)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al duplicar MaintenanceSchedule', [
                'user_id' => auth()->id(),
                'schedule_id' => $maintenanceSchedule->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al duplicar el programa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/maintenance-schedules/active",
     *     summary="Obtener programas activos",
     *     tags={"MaintenanceSchedules"},
     *     @OA\Response(response=200, description="Programas activos obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function active(): JsonResponse
    {
        try {
            $schedules = MaintenanceSchedule::active()
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new MaintenanceScheduleCollection($schedules));
        } catch (\Exception $e) {
            Log::error('Error al obtener programas activos', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los programas activos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/maintenance-schedules/overdue",
     *     summary="Obtener programas atrasados",
     *     tags={"MaintenanceSchedules"},
     *     @OA\Response(response=200, description="Programas atrasados obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function overdue(): JsonResponse
    {
        try {
            $schedules = MaintenanceSchedule::overdue()
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new MaintenanceScheduleCollection($schedules));
        } catch (\Exception $e) {
            Log::error('Error al obtener programas atrasados', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los programas atrasados',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/maintenance-schedules/due-soon",
     *     summary="Obtener programas próximos a vencer",
     *     tags={"MaintenanceSchedules"},
     *     @OA\Response(response=200, description="Programas obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function dueSoon(): JsonResponse
    {
        try {
            $schedules = MaintenanceSchedule::dueSoon()
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new MaintenanceScheduleCollection($schedules));
        } catch (\Exception $e) {
            Log::error('Error al obtener programas próximos a vencer', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los programas próximos a vencer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/maintenance-schedules/high-priority",
     *     summary="Obtener programas de alta prioridad",
     *     tags={"MaintenanceSchedules"},
     *     @OA\Response(response=200, description="Programas obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function highPriority(): JsonResponse
    {
        try {
            $schedules = MaintenanceSchedule::highPriority()
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new MaintenanceScheduleCollection($schedules));
        } catch (\Exception $e) {
            Log::error('Error al obtener programas de alta prioridad', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los programas de alta prioridad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/maintenance-schedules/by-type/{type}",
     *     summary="Obtener programas por tipo",
     *     tags={"MaintenanceSchedules"},
     *     @OA\Parameter(name="type", in="path", required=true, description="Tipo de programa", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Programas obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function byType(string $type): JsonResponse
    {
        try {
            $schedules = MaintenanceSchedule::byType($type)
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new MaintenanceScheduleCollection($schedules));
        } catch (\Exception $e) {
            Log::error('Error al obtener programas por tipo', [
                'user_id' => auth()->id(),
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los programas por tipo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/maintenance-schedules/by-frequency-type/{frequencyType}",
     *     summary="Obtener programas por tipo de frecuencia",
     *     tags={"MaintenanceSchedules"},
     *     @OA\Parameter(name="frequencyType", in="path", required=true, description="Tipo de frecuencia", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Programas obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function byFrequencyType(string $frequencyType): JsonResponse
    {
        try {
            $schedules = MaintenanceSchedule::byFrequencyType($frequencyType)
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new MaintenanceScheduleCollection($schedules));
        } catch (\Exception $e) {
            Log::error('Error al obtener programas por tipo de frecuencia', [
                'user_id' => auth()->id(),
                'frequency_type' => $frequencyType,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los programas por tipo de frecuencia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/maintenance-schedules/by-priority/{priority}",
     *     summary="Obtener programas por prioridad",
     *     tags={"MaintenanceSchedules"},
     *     @OA\Parameter(name="priority", in="path", required=true, description="Prioridad", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Programas obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function byPriority(string $priority): JsonResponse
    {
        try {
            $schedules = MaintenanceSchedule::byPriority($priority)
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new MaintenanceScheduleCollection($schedules));
        } catch (\Exception $e) {
            Log::error('Error al obtener programas por prioridad', [
                'user_id' => auth()->id(),
                'priority' => $priority,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los programas por prioridad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/maintenance-schedules/by-department/{department}",
     *     summary="Obtener programas por departamento",
     *     tags={"MaintenanceSchedules"},
     *     @OA\Parameter(name="department", in="path", required=true, description="Departamento", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Programas obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function byDepartment(string $department): JsonResponse
    {
        try {
            $schedules = MaintenanceSchedule::byDepartment($department)
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new MaintenanceScheduleCollection($schedules));
        } catch (\Exception $e) {
            Log::error('Error al obtener programas por departamento', [
                'user_id' => auth()->id(),
                'department' => $department,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los programas por departamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/maintenance-schedules/by-category/{category}",
     *     summary="Obtener programas por categoría",
     *     tags={"MaintenanceSchedules"},
     *     @OA\Parameter(name="category", in="path", required=true, description="Categoría", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Programas obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function byCategory(string $category): JsonResponse
    {
        try {
            $schedules = MaintenanceSchedule::byCategory($category)
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new MaintenanceScheduleCollection($schedules));
        } catch (\Exception $e) {
            Log::error('Error al obtener programas por categoría', [
                'user_id' => auth()->id(),
                'category' => $category,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los programas por categoría',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
