<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\AutomationRule\StoreAutomationRuleRequest;
use App\Http\Requests\Api\V1\AutomationRule\UpdateAutomationRuleRequest;
use App\Http\Resources\Api\V1\AutomationRule\AutomationRuleCollection;
use App\Http\Resources\Api\V1\AutomationRule\AutomationRuleResource;
use App\Models\AutomationRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="AutomationRules",
 *     description="API Endpoints para gestión de reglas de automatización"
 * )
 */
class AutomationRuleController extends \App\Http\Controllers\Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/automation-rules",
     *     summary="Listar reglas de automatización",
     *     tags={"AutomationRules"},
     *     @OA\Parameter(name="page", in="query", description="Número de página", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", description="Elementos por página", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", description="Término de búsqueda", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort", in="query", description="Campo de ordenamiento", @OA\Schema(type="string")),
     *     @OA\Parameter(name="order", in="query", description="Orden (asc/desc)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="rule_type", in="query", description="Filtrar por tipo de regla", @OA\Schema(type="string")),
     *     @OA\Parameter(name="trigger_type", in="query", description="Filtrar por tipo de disparador", @OA\Schema(type="string")),
     *     @OA\Parameter(name="action_type", in="query", description="Filtrar por tipo de acción", @OA\Schema(type="string")),
     *     @OA\Parameter(name="priority", in="query", description="Filtrar por prioridad", @OA\Schema(type="string")),
     *     @OA\Parameter(name="is_active", in="query", description="Filtrar por estado activo", @OA\Schema(type="boolean")),
     *     @OA\Response(response=200, description="Lista de reglas obtenida exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = AutomationRule::query()
                ->with(['createdBy', 'approvedBy']);

            // Filtros
            if ($request->filled('rule_type')) {
                $query->byType($request->rule_type);
            }

            if ($request->filled('trigger_type')) {
                $query->byTriggerType($request->trigger_type);
            }

            if ($request->filled('action_type')) {
                $query->byActionType($request->action_type);
            }

            if ($request->filled('priority')) {
                $query->byPriority($request->priority);
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->filled('execution_frequency')) {
                $query->byExecutionFrequency($request->execution_frequency);
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
            $sortField = $request->get('sort', 'created_at');
            $sortOrder = $request->get('order', 'desc');
            $query->orderBy($sortField, $sortOrder);

            // Paginación
            $perPage = min($request->get('per_page', 15), 100);
            $automationRules = $query->paginate($perPage);

            Log::info('AutomationRules listados', [
                'user_id' => auth()->id(),
                'filters' => $request->only(['rule_type', 'trigger_type', 'action_type', 'priority', 'is_active']),
                'total' => $automationRules->total()
            ]);

            return response()->json(new AutomationRuleCollection($automationRules));
        } catch (\Exception $e) {
            Log::error('Error al listar AutomationRules', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al listar las reglas de automatización',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/automation-rules",
     *     summary="Crear nueva regla de automatización",
     *     tags={"AutomationRules"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreAutomationRuleRequest")
     *     ),
     *     @OA\Response(response=201, description="Regla creada exitosamente"),
     *     @OA\Response(response=422, description="Error de validación"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function store(StoreAutomationRuleRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $automationRule = AutomationRule::create($request->validated());

            DB::commit();

            Log::info('AutomationRule creado', [
                'user_id' => auth()->id(),
                'automation_rule_id' => $automationRule->id,
                'name' => $automationRule->name
            ]);

            return response()->json([
                'message' => 'Regla de automatización creada exitosamente',
                'data' => new AutomationRuleResource($automationRule)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al crear AutomationRule', [
                'user_id' => auth()->id(),
                'data' => $request->validated(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al crear la regla de automatización',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/automation-rules/{id}",
     *     summary="Obtener regla de automatización específica",
     *     tags={"AutomationRules"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID de la regla", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Regla obtenida exitosamente"),
     *     @OA\Response(response=404, description="Regla no encontrada"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function show(AutomationRule $automationRule): JsonResponse
    {
        try {
            $automationRule->load(['createdBy', 'approvedBy']);

            Log::info('AutomationRule consultado', [
                'user_id' => auth()->id(),
                'automation_rule_id' => $automationRule->id
            ]);

            return response()->json([
                'data' => new AutomationRuleResource($automationRule)
            ]);
        } catch (\Exception $e) {
            Log::error('Error al consultar AutomationRule', [
                'user_id' => auth()->id(),
                'automation_rule_id' => $automationRule->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al consultar la regla de automatización',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/automation-rules/{id}",
     *     summary="Actualizar regla de automatización",
     *     tags={"AutomationRules"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID de la regla", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateAutomationRuleRequest")
     *     ),
     *     @OA\Response(response=200, description="Regla actualizada exitosamente"),
     *     @OA\Response(response=422, description="Error de validación"),
     *     @OA\Response(response=404, description="Regla no encontrada"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function update(UpdateAutomationRuleRequest $request, AutomationRule $automationRule): JsonResponse
    {
        try {
            DB::beginTransaction();

            $automationRule->update($request->validated());

            DB::commit();

            Log::info('AutomationRule actualizado', [
                'user_id' => auth()->id(),
                'automation_rule_id' => $automationRule->id,
                'changes' => $request->validated()
            ]);

            return response()->json([
                'message' => 'Regla de automatización actualizada exitosamente',
                'data' => new AutomationRuleResource($automationRule)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al actualizar AutomationRule', [
                'user_id' => auth()->id(),
                'automation_rule_id' => $automationRule->id,
                'data' => $request->validated(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al actualizar la regla de automatización',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/automation-rules/{id}",
     *     summary="Eliminar regla de automatización",
     *     tags={"AutomationRules"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID de la regla", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Regla eliminada exitosamente"),
     *     @OA\Response(response=404, description="Regla no encontrada"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function destroy(AutomationRule $automationRule): JsonResponse
    {
        try {
            DB::beginTransaction();

            $automationRule->delete();

            DB::commit();

            Log::info('AutomationRule eliminado', [
                'user_id' => auth()->id(),
                'automation_rule_id' => $automationRule->id,
                'name' => $automationRule->name
            ]);

            return response()->json([
                'message' => 'Regla de automatización eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al eliminar AutomationRule', [
                'user_id' => auth()->id(),
                'automation_rule_id' => $automationRule->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al eliminar la regla de automatización',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/automation-rules/statistics",
     *     summary="Obtener estadísticas de reglas de automatización",
     *     tags={"AutomationRules"},
     *     @OA\Response(response=200, description="Estadísticas obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_rules' => AutomationRule::count(),
                'active_rules' => AutomationRule::where('is_active', true)->count(),
                'inactive_rules' => AutomationRule::where('is_active', false)->count(),
                'approved_rules' => AutomationRule::approved()->count(),
                'pending_approval_rules' => AutomationRule::pendingApproval()->count(),
                'high_priority_rules' => AutomationRule::highPriority()->count(),
                'scheduled_rules' => AutomationRule::scheduled()->count(),
                'event_driven_rules' => AutomationRule::eventDriven()->count(),
                'condition_based_rules' => AutomationRule::conditionBased()->count(),
                'total_executions' => AutomationRule::sum('execution_count'),
                'total_successes' => AutomationRule::sum('success_count'),
                'total_failures' => AutomationRule::sum('failure_count'),
                'average_success_rate' => AutomationRule::where('execution_count', '>', 0)
                    ->avg(DB::raw('(success_count / execution_count) * 100')),
            ];

            Log::info('Estadísticas de AutomationRules consultadas', [
                'user_id' => auth()->id()
            ]);

            return response()->json(['data' => $stats]);
        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas de AutomationRules', [
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
     *     path="/api/v1/automation-rules/types",
     *     summary="Obtener tipos de reglas disponibles",
     *     tags={"AutomationRules"},
     *     @OA\Response(response=200, description="Tipos obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function types(): JsonResponse
    {
        try {
            $types = collect(AutomationRule::getRuleTypes())->map(function ($label, $value) {
                return [
                    'value' => $value,
                    'label' => $label,
                    'count' => AutomationRule::byType($value)->count()
                ];
            })->values();

            return response()->json(['data' => $types]);
        } catch (\Exception $e) {
            Log::error('Error al obtener tipos de AutomationRules', [
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
     *     path="/api/v1/automation-rules/trigger-types",
     *     summary="Obtener tipos de disparadores disponibles",
     *     tags={"AutomationRules"},
     *     @OA\Response(response=200, description="Tipos de disparadores obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function triggerTypes(): JsonResponse
    {
        try {
            $types = collect(AutomationRule::getTriggerTypes())->map(function ($label, $value) {
                return [
                    'value' => $value,
                    'label' => $label,
                    'count' => AutomationRule::byTriggerType($value)->count()
                ];
            })->values();

            return response()->json(['data' => $types]);
        } catch (\Exception $e) {
            Log::error('Error al obtener tipos de disparadores', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los tipos de disparadores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/automation-rules/action-types",
     *     summary="Obtener tipos de acciones disponibles",
     *     tags={"AutomationRules"},
     *     @OA\Response(response=200, description="Tipos de acciones obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function actionTypes(): JsonResponse
    {
        try {
            $types = collect(AutomationRule::getActionTypes())->map(function ($label, $value) {
                return [
                    'value' => $value,
                    'label' => $label,
                    'count' => AutomationRule::byActionType($value)->count()
                ];
            })->values();

            return response()->json(['data' => $types]);
        } catch (\Exception $e) {
            Log::error('Error al obtener tipos de acciones', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los tipos de acciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/automation-rules/execution-frequencies",
     *     summary="Obtener frecuencias de ejecución disponibles",
     *     tags={"AutomationRules"},
     *     @OA\Response(response=200, description="Frecuencias obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function executionFrequencies(): JsonResponse
    {
        try {
            $frequencies = collect(AutomationRule::getExecutionFrequencies())->map(function ($label, $value) {
                return [
                    'value' => $value,
                    'label' => $label,
                    'count' => AutomationRule::byExecutionFrequency($value)->count()
                ];
            })->values();

            return response()->json(['data' => $frequencies]);
        } catch (\Exception $e) {
            Log::error('Error al obtener frecuencias de ejecución', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las frecuencias',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/automation-rules/{id}/toggle-active",
     *     summary="Alternar estado activo de la regla",
     *     tags={"AutomationRules"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID de la regla", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Estado alternado exitosamente"),
     *     @OA\Response(response=404, description="Regla no encontrada"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function toggleActive(AutomationRule $automationRule): JsonResponse
    {
        try {
            DB::beginTransaction();

            $automationRule->update(['is_active' => !$automationRule->is_active]);

            DB::commit();

            Log::info('Estado activo de AutomationRule alternado', [
                'user_id' => auth()->id(),
                'automation_rule_id' => $automationRule->id,
                'new_status' => $automationRule->is_active
            ]);

            return response()->json([
                'message' => 'Estado activo alternado exitosamente',
                'data' => new AutomationRuleResource($automationRule)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al alternar estado activo', [
                'user_id' => auth()->id(),
                'automation_rule_id' => $automationRule->id,
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
     *     path="/api/v1/automation-rules/{id}/duplicate",
     *     summary="Duplicar regla de automatización",
     *     tags={"AutomationRules"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID de la regla", @OA\Schema(type="integer")),
     *     @OA\Response(response=201, description="Regla duplicada exitosamente"),
     *     @OA\Response(response=404, description="Regla no encontrada"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function duplicate(AutomationRule $automationRule): JsonResponse
    {
        try {
            DB::beginTransaction();

            $duplicate = $automationRule->replicate();
            $duplicate->name = $duplicate->name . ' (Copia)';
            $duplicate->is_active = false;
            $duplicate->execution_count = 0;
            $duplicate->success_count = 0;
            $duplicate->failure_count = 0;
            $duplicate->last_executed_at = null;
            $duplicate->next_execution_at = null;
            $duplicate->save();

            DB::commit();

            Log::info('AutomationRule duplicado', [
                'user_id' => auth()->id(),
                'original_id' => $automationRule->id,
                'duplicate_id' => $duplicate->id
            ]);

            return response()->json([
                'message' => 'Regla duplicada exitosamente',
                'data' => new AutomationRuleResource($duplicate)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al duplicar AutomationRule', [
                'user_id' => auth()->id(),
                'automation_rule_id' => $automationRule->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al duplicar la regla',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/automation-rules/active",
     *     summary="Obtener reglas activas",
     *     tags={"AutomationRules"},
     *     @OA\Response(response=200, description="Reglas activas obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function active(): JsonResponse
    {
        try {
            $rules = AutomationRule::active()
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new AutomationRuleCollection($rules));
        } catch (\Exception $e) {
            Log::error('Error al obtener reglas activas', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las reglas activas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/automation-rules/ready-to-execute",
     *     summary="Obtener reglas listas para ejecutar",
     *     tags={"AutomationRules"},
     *     @OA\Response(response=200, description="Reglas obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function readyToExecute(): JsonResponse
    {
        try {
            $rules = AutomationRule::readyToExecute()
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new AutomationRuleCollection($rules));
        } catch (\Exception $e) {
            Log::error('Error al obtener reglas listas para ejecutar', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las reglas listas para ejecutar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/automation-rules/failed",
     *     summary="Obtener reglas con fallos",
     *     tags={"AutomationRules"},
     *     @OA\Response(response=200, description="Reglas obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function failed(): JsonResponse
    {
        try {
            $rules = AutomationRule::failed()
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new AutomationRuleCollection($rules));
        } catch (\Exception $e) {
            Log::error('Error al obtener reglas con fallos', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las reglas con fallos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/automation-rules/successful",
     *     summary="Obtener reglas exitosas",
     *     tags={"AutomationRules"},
     *     @OA\Response(response=200, description="Reglas obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function successful(): JsonResponse
    {
        try {
            $rules = AutomationRule::successful()
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new AutomationRuleCollection($rules));
        } catch (\Exception $e) {
            Log::error('Error al obtener reglas exitosas', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las reglas exitosas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/automation-rules/high-priority",
     *     summary="Obtener reglas de alta prioridad",
     *     tags={"AutomationRules"},
     *     @OA\Response(response=200, description="Reglas obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function highPriority(): JsonResponse
    {
        try {
            $rules = AutomationRule::highPriority()
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new AutomationRuleCollection($rules));
        } catch (\Exception $e) {
            Log::error('Error al obtener reglas de alta prioridad', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las reglas de alta prioridad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/automation-rules/by-type/{type}",
     *     summary="Obtener reglas por tipo",
     *     tags={"AutomationRules"},
     *     @OA\Parameter(name="type", in="path", required=true, description="Tipo de regla", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Reglas obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function byType(string $type): JsonResponse
    {
        try {
            $rules = AutomationRule::byType($type)
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new AutomationRuleCollection($rules));
        } catch (\Exception $e) {
            Log::error('Error al obtener reglas por tipo', [
                'user_id' => auth()->id(),
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las reglas por tipo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/automation-rules/by-trigger-type/{triggerType}",
     *     summary="Obtener reglas por tipo de disparador",
     *     tags={"AutomationRules"},
     *     @OA\Parameter(name="triggerType", in="path", required=true, description="Tipo de disparador", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Reglas obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function byTriggerType(string $triggerType): JsonResponse
    {
        try {
            $rules = AutomationRule::byTriggerType($triggerType)
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new AutomationRuleCollection($rules));
        } catch (\Exception $e) {
            Log::error('Error al obtener reglas por tipo de disparador', [
                'user_id' => auth()->id(),
                'trigger_type' => $triggerType,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las reglas por tipo de disparador',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/automation-rules/by-action-type/{actionType}",
     *     summary="Obtener reglas por tipo de acción",
     *     tags={"AutomationRules"},
     *     @OA\Parameter(name="actionType", in="path", required=true, description="Tipo de acción", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Reglas obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function byActionType(string $actionType): JsonResponse
    {
        try {
            $rules = AutomationRule::byActionType($actionType)
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new AutomationRuleCollection($rules));
        } catch (\Exception $e) {
            Log::error('Error al obtener reglas por tipo de acción', [
                'user_id' => auth()->id(),
                'action_type' => $actionType,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las reglas por tipo de acción',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/automation-rules/by-execution-frequency/{frequency}",
     *     summary="Obtener reglas por frecuencia de ejecución",
     *     tags={"AutomationRules"},
     *     @OA\Parameter(name="frequency", in="path", required=true, description="Frecuencia de ejecución", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Reglas obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function byExecutionFrequency(string $frequency): JsonResponse
    {
        try {
            $rules = AutomationRule::byExecutionFrequency($frequency)
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new AutomationRuleCollection($rules));
        } catch (\Exception $e) {
            Log::error('Error al obtener reglas por frecuencia de ejecución', [
                'user_id' => auth()->id(),
                'frequency' => $frequency,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las reglas por frecuencia de ejecución',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
