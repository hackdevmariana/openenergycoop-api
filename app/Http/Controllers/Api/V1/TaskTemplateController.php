<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\TaskTemplate\StoreTaskTemplateRequest;
use App\Http\Requests\Api\V1\TaskTemplate\UpdateTaskTemplateRequest;
use App\Http\Resources\Api\V1\TaskTemplate\TaskTemplateResource;
use App\Http\Resources\Api\V1\TaskTemplate\TaskTemplateCollection;
use App\Models\TaskTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskTemplateController extends \App\Http\Controllers\Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/task-templates",
     *     summary="Listar plantillas de tareas",
     *     tags={"TaskTemplates"},
     *     @OA\Parameter(name="search", in="query", description="Búsqueda por nombre o descripción", @OA\Schema(type="string")),
     *     @OA\Parameter(name="template_type", in="query", description="Filtrar por tipo de plantilla", @OA\Schema(type="string")),
     *     @OA\Parameter(name="category", in="query", description="Filtrar por categoría", @OA\Schema(type="string")),
     *     @OA\Parameter(name="subcategory", in="query", description="Filtrar por subcategoría", @OA\Schema(type="string")),
     *     @OA\Parameter(name="priority", in="query", description="Filtrar por prioridad", @OA\Schema(type="string")),
     *     @OA\Parameter(name="risk_level", in="query", description="Filtrar por nivel de riesgo", @OA\Schema(type="string")),
     *     @OA\Parameter(name="department", in="query", description="Filtrar por departamento", @OA\Schema(type="string")),
     *     @OA\Parameter(name="is_active", in="query", description="Filtrar por estado activo", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="is_standard", in="query", description="Filtrar por plantillas estándar", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="is_approved", in="query", description="Filtrar por estado de aprobación", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="sort_by", in="query", description="Campo para ordenar", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_direction", in="query", description="Dirección del ordenamiento", @OA\Schema(type="string", enum={"asc", "desc"})),
     *     @OA\Parameter(name="per_page", in="query", description="Elementos por página", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Plantillas de tareas obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = TaskTemplate::query();

            // Filtros
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('category', 'like', "%{$search}%")
                      ->orWhere('subcategory', 'like', "%{$search}%");
                });
            }

            if ($request->filled('template_type')) {
                $query->byType($request->template_type);
            }

            if ($request->filled('category')) {
                $query->byCategory($request->category);
            }

            if ($request->filled('subcategory')) {
                $query->where('subcategory', $request->subcategory);
            }

            if ($request->filled('priority')) {
                $query->byPriority($request->priority);
            }

            if ($request->filled('risk_level')) {
                $query->byRiskLevel($request->risk_level);
            }

            if ($request->filled('department')) {
                $query->byDepartment($request->department);
            }

            if ($request->filled('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->filled('is_standard')) {
                $query->where('is_standard', $request->boolean('is_standard'));
            }

            if ($request->filled('is_approved')) {
                if ($request->boolean('is_approved')) {
                    $query->approved();
                } else {
                    $query->pendingApproval();
                }
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'name');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortBy, $sortDirection);

            $taskTemplates = $query->with(['createdBy', 'approvedBy'])
                ->paginate($request->get('per_page', 15));

            return response()->json(new TaskTemplateCollection($taskTemplates));
        } catch (\Exception $e) {
            Log::error('Error al obtener plantillas de tareas', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las plantillas de tareas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/task-templates",
     *     summary="Crear nueva plantilla de tarea",
     *     tags={"TaskTemplates"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreTaskTemplateRequest")),
     *     @OA\Response(response=201, description="Plantilla de tarea creada exitosamente"),
     *     @OA\Response(response=422, description="Datos de validación incorrectos"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function store(StoreTaskTemplateRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $taskTemplate = TaskTemplate::create($request->validated());

            DB::commit();

            Log::info('Plantilla de tarea creada', [
                'user_id' => auth()->id(),
                'task_template_id' => $taskTemplate->id,
                'name' => $taskTemplate->name
            ]);

            return response()->json([
                'message' => 'Plantilla de tarea creada exitosamente',
                'data' => new TaskTemplateResource($taskTemplate)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al crear plantilla de tarea', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al crear la plantilla de tarea',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/task-templates/{id}",
     *     summary="Obtener plantilla de tarea específica",
     *     tags={"TaskTemplates"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID de la plantilla", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Plantilla de tarea obtenida exitosamente"),
     *     @OA\Response(response=404, description="Plantilla de tarea no encontrada"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function show(TaskTemplate $taskTemplate): JsonResponse
    {
        try {
            $taskTemplate->load(['createdBy', 'approvedBy']);

            return response()->json([
                'data' => new TaskTemplateResource($taskTemplate)
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener plantilla de tarea', [
                'user_id' => auth()->id(),
                'task_template_id' => $taskTemplate->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener la plantilla de tarea',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/task-templates/{id}",
     *     summary="Actualizar plantilla de tarea",
     *     tags={"TaskTemplates"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID de la plantilla", @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateTaskTemplateRequest")),
     *     @OA\Response(response=200, description="Plantilla de tarea actualizada exitosamente"),
     *     @OA\Response(response=422, description="Datos de validación incorrectos"),
     *     @OA\Response(response=404, description="Plantilla de tarea no encontrada"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function update(UpdateTaskTemplateRequest $request, TaskTemplate $taskTemplate): JsonResponse
    {
        try {
            DB::beginTransaction();

            $taskTemplate->update($request->validated());

            DB::commit();

            Log::info('Plantilla de tarea actualizada', [
                'user_id' => auth()->id(),
                'task_template_id' => $taskTemplate->id,
                'name' => $taskTemplate->name
            ]);

            return response()->json([
                'message' => 'Plantilla de tarea actualizada exitosamente',
                'data' => new TaskTemplateResource($taskTemplate)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al actualizar plantilla de tarea', [
                'user_id' => auth()->id(),
                'task_template_id' => $taskTemplate->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al actualizar la plantilla de tarea',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/task-templates/{id}",
     *     summary="Eliminar plantilla de tarea",
     *     tags={"TaskTemplates"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID de la plantilla", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Plantilla de tarea eliminada exitosamente"),
     *     @OA\Response(response=404, description="Plantilla de tarea no encontrada"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function destroy(TaskTemplate $taskTemplate): JsonResponse
    {
        try {
            DB::beginTransaction();

            $taskTemplate->delete();

            DB::commit();

            Log::info('Plantilla de tarea eliminada', [
                'user_id' => auth()->id(),
                'task_template_id' => $taskTemplate->id,
                'name' => $taskTemplate->name
            ]);

            return response()->json([
                'message' => 'Plantilla de tarea eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al eliminar plantilla de tarea', [
                'user_id' => auth()->id(),
                'task_template_id' => $taskTemplate->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al eliminar la plantilla de tarea',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/task-templates/statistics",
     *     summary="Obtener estadísticas de plantillas de tareas",
     *     tags={"TaskTemplates"},
     *     @OA\Response(response=200, description="Estadísticas obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function statistics(): JsonResponse
    {
        try {
            $statistics = [
                'total_templates' => TaskTemplate::count(),
                'active_templates' => TaskTemplate::active()->count(),
                'inactive_templates' => TaskTemplate::where('is_active', false)->count(),
                'standard_templates' => TaskTemplate::standard()->count(),
                'non_standard_templates' => TaskTemplate::where('is_standard', false)->count(),
                'approved_templates' => TaskTemplate::approved()->count(),
                'pending_approval_templates' => TaskTemplate::pendingApproval()->count(),
                'templates_by_type' => [],
                'templates_by_priority' => [],
                'templates_by_risk_level' => [],
                'templates_by_department' => [],
                'templates_by_category' => [],
                'templates_by_subcategory' => [],
                'average_estimated_duration' => TaskTemplate::whereNotNull('estimated_duration_hours')->avg('estimated_duration_hours'),
                'average_estimated_cost' => TaskTemplate::whereNotNull('estimated_cost')->avg('estimated_cost'),
                'total_estimated_cost' => TaskTemplate::whereNotNull('estimated_cost')->sum('estimated_cost'),
            ];

            // Estadísticas por tipo
            foreach (TaskTemplate::getTemplateTypes() as $type => $label) {
                $statistics['templates_by_type'][$type] = TaskTemplate::byType($type)->count();
            }

            // Estadísticas por prioridad
            foreach (TaskTemplate::getPriorities() as $priority => $label) {
                $statistics['templates_by_priority'][$priority] = TaskTemplate::byPriority($priority)->count();
            }

            // Estadísticas por nivel de riesgo
            foreach (TaskTemplate::getRiskLevels() as $riskLevel => $label) {
                $statistics['templates_by_risk_level'][$riskLevel] = TaskTemplate::byRiskLevel($riskLevel)->count();
            }

            // Estadísticas por departamento
            $departments = TaskTemplate::distinct()->pluck('department')->filter();
            foreach ($departments as $department) {
                $statistics['templates_by_department'][$department] = TaskTemplate::byDepartment($department)->count();
            }

            // Estadísticas por categoría
            $categories = TaskTemplate::distinct()->pluck('category')->filter();
            foreach ($categories as $category) {
                $statistics['templates_by_category'][$category] = TaskTemplate::byCategory($category)->count();
            }

            // Estadísticas por subcategoría
            $subcategories = TaskTemplate::distinct()->pluck('subcategory')->filter();
            foreach ($subcategories as $subcategory) {
                $statistics['templates_by_subcategory'][$subcategory] = TaskTemplate::where('subcategory', $subcategory)->count();
            }

            return response()->json(['data' => $statistics]);
        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas de plantillas de tareas', [
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
     *     path="/api/v1/task-templates/template-types",
     *     summary="Obtener tipos de plantillas disponibles",
     *     tags={"TaskTemplates"},
     *     @OA\Response(response=200, description="Tipos de plantillas obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function templateTypes(): JsonResponse
    {
        try {
            $templateTypes = TaskTemplate::getTemplateTypes();

            return response()->json(['data' => $templateTypes]);
        } catch (\Exception $e) {
            Log::error('Error al obtener tipos de plantillas', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los tipos de plantillas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/task-templates/priorities",
     *     summary="Obtener prioridades disponibles",
     *     tags={"TaskTemplates"},
     *     @OA\Response(response=200, description="Prioridades obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function priorities(): JsonResponse
    {
        try {
            $priorities = TaskTemplate::getPriorities();

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
     * @OA\Get(
     *     path="/api/v1/task-templates/risk-levels",
     *     summary="Obtener niveles de riesgo disponibles",
     *     tags={"TaskTemplates"},
     *     @OA\Response(response=200, description="Niveles de riesgo obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function riskLevels(): JsonResponse
    {
        try {
            $riskLevels = TaskTemplate::getRiskLevels();

            return response()->json(['data' => $riskLevels]);
        } catch (\Exception $e) {
            Log::error('Error al obtener niveles de riesgo', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los niveles de riesgo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/task-templates/{id}/toggle-active",
     *     summary="Alternar estado activo de la plantilla",
     *     tags={"TaskTemplates"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID de la plantilla", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Estado activo alternado exitosamente"),
     *     @OA\Response(response=404, description="Plantilla no encontrada"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function toggleActive(TaskTemplate $taskTemplate): JsonResponse
    {
        try {
            DB::beginTransaction();

            $taskTemplate->update(['is_active' => !$taskTemplate->is_active]);

            DB::commit();

            Log::info('Estado activo de plantilla de tarea alternado', [
                'user_id' => auth()->id(),
                'task_template_id' => $taskTemplate->id,
                'new_status' => $taskTemplate->is_active
            ]);

            return response()->json([
                'message' => 'Estado activo alternado exitosamente',
                'data' => new TaskTemplateResource($taskTemplate)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al alternar estado activo', [
                'user_id' => auth()->id(),
                'task_template_id' => $taskTemplate->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al alternar el estado activo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/task-templates/{id}/toggle-standard",
     *     summary="Alternar estado estándar de la plantilla",
     *     tags={"TaskTemplates"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID de la plantilla", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Estado estándar alternado exitosamente"),
     *     @OA\Response(response=404, description="Plantilla no encontrada"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function toggleStandard(TaskTemplate $taskTemplate): JsonResponse
    {
        try {
            DB::beginTransaction();

            $taskTemplate->update(['is_standard' => !$taskTemplate->is_standard]);

            DB::commit();

            Log::info('Estado estándar de plantilla de tarea alternado', [
                'user_id' => auth()->id(),
                'task_template_id' => $taskTemplate->id,
                'new_status' => $taskTemplate->is_standard
            ]);

            return response()->json([
                'message' => 'Estado estándar alternado exitosamente',
                'data' => new TaskTemplateResource($taskTemplate)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al alternar estado estándar', [
                'user_id' => auth()->id(),
                'task_template_id' => $taskTemplate->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al alternar el estado estándar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/task-templates/{id}/duplicate",
     *     summary="Duplicar plantilla de tarea",
     *     tags={"TaskTemplates"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID de la plantilla", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Plantilla duplicada exitosamente"),
     *     @OA\Response(response=404, description="Plantilla no encontrada"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function duplicate(TaskTemplate $taskTemplate): JsonResponse
    {
        try {
            DB::beginTransaction();

            $duplicate = $taskTemplate->replicate();
            $duplicate->name = $duplicate->name . ' (Copia)';
            $duplicate->is_active = false;
            $duplicate->is_standard = false;
            $duplicate->approved_at = null;
            $duplicate->approved_by = null;
            $duplicate->version = '1.0';
            $duplicate->created_by = auth()->id();
            $duplicate->save();

            DB::commit();

            Log::info('Plantilla de tarea duplicada', [
                'user_id' => auth()->id(),
                'original_id' => $taskTemplate->id,
                'duplicate_id' => $duplicate->id
            ]);

            return response()->json([
                'message' => 'Plantilla de tarea duplicada exitosamente',
                'data' => new TaskTemplateResource($duplicate)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al duplicar plantilla de tarea', [
                'user_id' => auth()->id(),
                'task_template_id' => $taskTemplate->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al duplicar la plantilla de tarea',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/task-templates/active",
     *     summary="Obtener plantillas activas",
     *     tags={"TaskTemplates"},
     *     @OA\Response(response=200, description="Plantillas activas obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function active(): JsonResponse
    {
        try {
            $taskTemplates = TaskTemplate::active()
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new TaskTemplateCollection($taskTemplates));
        } catch (\Exception $e) {
            Log::error('Error al obtener plantillas activas', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las plantillas activas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/task-templates/standard",
     *     summary="Obtener plantillas estándar",
     *     tags={"TaskTemplates"},
     *     @OA\Response(response=200, description="Plantillas estándar obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function standard(): JsonResponse
    {
        try {
            $taskTemplates = TaskTemplate::standard()
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new TaskTemplateCollection($taskTemplates));
        } catch (\Exception $e) {
            Log::error('Error al obtener plantillas estándar', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las plantillas estándar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/task-templates/approved",
     *     summary="Obtener plantillas aprobadas",
     *     tags={"TaskTemplates"},
     *     @OA\Response(response=200, description="Plantillas aprobadas obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function approved(): JsonResponse
    {
        try {
            $taskTemplates = TaskTemplate::approved()
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new TaskTemplateCollection($taskTemplates));
        } catch (\Exception $e) {
            Log::error('Error al obtener plantillas aprobadas', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las plantillas aprobadas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/task-templates/pending-approval",
     *     summary="Obtener plantillas pendientes de aprobación",
     *     tags={"TaskTemplates"},
     *     @OA\Response(response=200, description="Plantillas pendientes obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function pendingApproval(): JsonResponse
    {
        try {
            $taskTemplates = TaskTemplate::pendingApproval()
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new TaskTemplateCollection($taskTemplates));
        } catch (\Exception $e) {
            Log::error('Error al obtener plantillas pendientes de aprobación', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las plantillas pendientes de aprobación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/task-templates/by-type/{type}",
     *     summary="Obtener plantillas por tipo",
     *     tags={"TaskTemplates"},
     *     @OA\Parameter(name="type", in="path", required=true, description="Tipo de plantilla", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Plantillas obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function byType(string $type): JsonResponse
    {
        try {
            $taskTemplates = TaskTemplate::byType($type)
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new TaskTemplateCollection($taskTemplates));
        } catch (\Exception $e) {
            Log::error('Error al obtener plantillas por tipo', [
                'user_id' => auth()->id(),
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las plantillas por tipo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/task-templates/by-category/{category}",
     *     summary="Obtener plantillas por categoría",
     *     tags={"TaskTemplates"},
     *     @OA\Parameter(name="category", in="path", required=true, description="Categoría", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Plantillas obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function byCategory(string $category): JsonResponse
    {
        try {
            $taskTemplates = TaskTemplate::byCategory($category)
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new TaskTemplateCollection($taskTemplates));
        } catch (\Exception $e) {
            Log::error('Error al obtener plantillas por categoría', [
                'user_id' => auth()->id(),
                'category' => $category,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las plantillas por categoría',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/task-templates/by-priority/{priority}",
     *     summary="Obtener plantillas por prioridad",
     *     tags={"TaskTemplates"},
     *     @OA\Parameter(name="priority", in="path", required=true, description="Prioridad", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Plantillas obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function byPriority(string $priority): JsonResponse
    {
        try {
            $taskTemplates = TaskTemplate::byPriority($priority)
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new TaskTemplateCollection($taskTemplates));
        } catch (\Exception $e) {
            Log::error('Error al obtener plantillas por prioridad', [
                'user_id' => auth()->id(),
                'priority' => $priority,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las plantillas por prioridad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/task-templates/by-risk-level/{riskLevel}",
     *     summary="Obtener plantillas por nivel de riesgo",
     *     tags={"TaskTemplates"},
     *     @OA\Parameter(name="riskLevel", in="path", required=true, description="Nivel de riesgo", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Plantillas obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function byRiskLevel(string $riskLevel): JsonResponse
    {
        try {
            $taskTemplates = TaskTemplate::byRiskLevel($riskLevel)
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new TaskTemplateCollection($taskTemplates));
        } catch (\Exception $e) {
            Log::error('Error al obtener plantillas por nivel de riesgo', [
                'user_id' => auth()->id(),
                'risk_level' => $riskLevel,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las plantillas por nivel de riesgo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/task-templates/by-department/{department}",
     *     summary="Obtener plantillas por departamento",
     *     tags={"TaskTemplates"},
     *     @OA\Parameter(name="department", in="path", required=true, description="Departamento", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Plantillas obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function byDepartment(string $department): JsonResponse
    {
        try {
            $taskTemplates = TaskTemplate::byDepartment($department)
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new TaskTemplateCollection($taskTemplates));
        } catch (\Exception $e) {
            Log::error('Error al obtener plantillas por departamento', [
                'user_id' => auth()->id(),
                'department' => $department,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las plantillas por departamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/task-templates/high-priority",
     *     summary="Obtener plantillas de alta prioridad",
     *     tags={"TaskTemplates"},
     *     @OA\Response(response=200, description="Plantillas de alta prioridad obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function highPriority(): JsonResponse
    {
        try {
            $taskTemplates = TaskTemplate::whereIn('priority', ['high', 'urgent', 'critical'])
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new TaskTemplateCollection($taskTemplates));
        } catch (\Exception $e) {
            Log::error('Error al obtener plantillas de alta prioridad', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las plantillas de alta prioridad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/task-templates/high-risk",
     *     summary="Obtener plantillas de alto riesgo",
     *     tags={"TaskTemplates"},
     *     @OA\Response(response=200, description="Plantillas de alto riesgo obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function highRisk(): JsonResponse
    {
        try {
            $taskTemplates = TaskTemplate::whereIn('risk_level', ['high', 'extreme'])
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new TaskTemplateCollection($taskTemplates));
        } catch (\Exception $e) {
            Log::error('Error al obtener plantillas de alto riesgo', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las plantillas de alto riesgo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
