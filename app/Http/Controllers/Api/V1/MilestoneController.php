<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Milestone\MilestoneResource;
use App\Http\Resources\Api\V1\Milestone\MilestoneCollection;
use App\Http\Requests\Api\V1\Milestone\StoreMilestoneRequest;
use App\Http\Requests\Api\V1\Milestone\UpdateMilestoneRequest;
use App\Models\Milestone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Milestones',
    description: 'Endpoints para gestión de hitos y metas del proyecto'
)]
class MilestoneController extends \App\Http\Controllers\Controller
{
    #[OA\Get(
        path: '/api/v1/milestones',
        summary: 'Listar hitos',
        description: 'Obtiene una lista paginada de hitos con filtros opcionales',
        tags: ['Milestones'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                in: 'query',
                description: 'Buscar en título y descripción',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'milestone_type',
                in: 'query',
                description: 'Filtrar por tipo de hito',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['project', 'financial', 'operational', 'regulatory', 'community', 'environmental', 'other'])
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                description: 'Filtrar por estado',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['not_started', 'in_progress', 'completed', 'on_hold', 'cancelled', 'overdue'])
            ),
            new OA\Parameter(
                name: 'priority',
                in: 'query',
                description: 'Filtrar por prioridad',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['low', 'medium', 'high', 'urgent', 'critical'])
            ),
            new OA\Parameter(
                name: 'assigned_to',
                in: 'query',
                description: 'Filtrar por usuario asignado',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'parent_milestone_id',
                in: 'query',
                description: 'Filtrar por hito padre',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'target_date_from',
                in: 'query',
                description: 'Fecha objetivo desde (YYYY-MM-DD)',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(
                name: 'target_date_to',
                in: 'query',
                description: 'Fecha objetivo hasta (YYYY-MM-DD)',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(
                name: 'progress_min',
                in: 'query',
                description: 'Progreso mínimo (0-100)',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 0, maximum: 100)
            ),
            new OA\Parameter(
                name: 'progress_max',
                in: 'query',
                description: 'Progreso máximo (0-100)',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 0, maximum: 100)
            ),
            new OA\Parameter(
                name: 'sort',
                in: 'query',
                description: 'Campo para ordenar',
                required: false,
                schema: new OA\Schema(type: 'string', default: 'target_date')
            ),
            new OA\Parameter(
                name: 'order',
                in: 'query',
                description: 'Orden ascendente o descendente',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'asc')
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                description: 'Número de elementos por página',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 15)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de hitos obtenida exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Milestone')),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/Meta'),
                        new OA\Property(property: 'summary', ref: '#/components/schemas/MilestoneSummary')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autorizado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Milestone::query()
            ->with(['parentMilestone', 'subMilestones', 'assignedTo', 'createdBy']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('milestone_type')) {
            $query->byType($request->milestone_type);
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        if ($request->filled('assigned_to')) {
            $query->byAssignedTo($request->assigned_to);
        }

        if ($request->filled('parent_milestone_id')) {
            if ($request->parent_milestone_id === 'null') {
                $query->rootMilestones();
            } else {
                $query->byParentMilestone($request->parent_milestone_id);
            }
        }

        if ($request->filled('target_date_from')) {
            $query->where('target_date', '>=', $request->target_date_from);
        }

        if ($request->filled('target_date_to')) {
            $query->where('target_date', '<=', $request->target_date_to);
        }

        if ($request->filled('progress_min') || $request->filled('progress_max')) {
            $minProgress = $request->get('progress_min', 0);
            $maxProgress = $request->get('progress_max', 100);
            $query->byProgressRange($minProgress, $maxProgress);
        }

        // Ordenamiento
        $sortField = $request->get('sort', 'target_date');
        $sortOrder = $request->get('order', 'asc');
        $query->orderBy($sortField, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $milestones = $query->paginate($perPage);

        return new MilestoneCollection($milestones);
    }

    #[OA\Get(
        path: '/api/v1/milestones/{id}',
        summary: 'Mostrar hito',
        description: 'Obtiene los detalles de un hito específico',
        tags: ['Milestones'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID del hito',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hito obtenido exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Milestone')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Hito no encontrado'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function show(Milestone $milestone): JsonResponse
    {
        $milestone->load(['parentMilestone', 'subMilestones', 'assignedTo', 'createdBy']);

        return response()->json([
            'data' => new MilestoneResource($milestone)
        ]);
    }

    #[OA\Post(
        path: '/api/v1/milestones',
        summary: 'Crear hito',
        description: 'Crea un nuevo hito',
        tags: ['Milestones'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StoreMilestoneRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Hito creado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Milestone'),
                        new OA\Property(property: 'message', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Datos de validación inválidos'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function store(StoreMilestoneRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['created_by'] = auth()->id();

            $milestone = Milestone::create($data);
            $milestone->load(['parentMilestone', 'assignedTo', 'createdBy']);

            Log::info('Milestone created', [
                'id' => $milestone->id,
                'title' => $milestone->title,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'data' => new MilestoneResource($milestone),
                'message' => 'Hito creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating Milestone', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Error al crear el hito'
            ], 500);
        }
    }

    #[OA\Put(
        path: '/api/v1/milestones/{id}',
        summary: 'Actualizar hito',
        description: 'Actualiza un hito existente',
        tags: ['Milestones'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID del hito',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateMilestoneRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hito actualizado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Milestone'),
                        new OA\Property(property: 'message', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Hito no encontrado'),
            new OA\Response(response: 422, description: 'Datos de validación inválidos'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function update(UpdateMilestoneRequest $request, Milestone $milestone): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $milestone->update($data);
            $milestone->load(['parentMilestone', 'subMilestones', 'assignedTo', 'createdBy']);

            Log::info('Milestone updated', [
                'id' => $milestone->id,
                'title' => $milestone->title,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'data' => new MilestoneResource($milestone),
                'message' => 'Hito actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating Milestone', [
                'id' => $milestone->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Error al actualizar el hito'
            ], 500);
        }
    }

    #[OA\Delete(
        path: '/api/v1/milestones/{id}',
        summary: 'Eliminar hito',
        description: 'Elimina un hito (soft delete)',
        tags: ['Milestones'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID del hito',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hito eliminado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Hito no encontrado'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function destroy(Milestone $milestone): JsonResponse
    {
        try {
            DB::beginTransaction();

            $milestone->delete();

            Log::info('Milestone deleted', [
                'id' => $milestone->id,
                'title' => $milestone->title,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Hito eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting Milestone', [
                'id' => $milestone->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Error al eliminar el hito'
            ], 500);
        }
    }

    #[OA\Get(
        path: '/api/v1/milestones/statistics',
        summary: 'Obtener estadísticas de hitos',
        description: 'Obtiene estadísticas generales de los hitos',
        tags: ['Milestones'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Estadísticas obtenidas exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/MilestoneStatistics')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function statistics(): JsonResponse
    {
        $statistics = [
            'total_milestones' => Milestone::count(),
            'by_type' => Milestone::selectRaw('milestone_type, COUNT(*) as count')
                ->groupBy('milestone_type')
                ->pluck('count', 'milestone_type')
                ->toArray(),
            'by_status' => Milestone::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'by_priority' => Milestone::selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->pluck('count', 'priority')
                ->toArray(),
            'overdue' => Milestone::overdueStatus()->count(),
            'due_soon' => Milestone::dueSoon(7)->count(),
            'due_today' => Milestone::whereDate('target_date', today())->count(),
            'completed' => Milestone::completed()->count(),
            'in_progress' => Milestone::inProgress()->count(),
            'not_started' => Milestone::notStarted()->count(),
            'on_hold' => Milestone::onHold()->count(),
            'cancelled' => Milestone::cancelled()->count(),
            'high_priority' => Milestone::highPriority()->count(),
            'root_milestones' => Milestone::rootMilestones()->count(),
            'assigned_milestones' => Milestone::whereNotNull('assigned_to')->count(),
            'unassigned_milestones' => Milestone::whereNull('assigned_to')->count(),
            'average_progress' => Milestone::whereNotNull('progress_percentage')->avg('progress_percentage'),
            'total_target_value' => Milestone::whereNotNull('target_value')->sum('target_value'),
            'total_current_value' => Milestone::whereNotNull('current_value')->sum('current_value'),
        ];

        return response()->json(['data' => $statistics]);
    }

    #[OA\Get(
        path: '/api/v1/milestones/milestone-types',
        summary: 'Obtener tipos de hitos',
        description: 'Obtiene la lista de tipos de hitos disponibles',
        tags: ['Milestones'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tipos obtenidos exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            )
        ]
    )]
    public function milestoneTypes(): JsonResponse
    {
        return response()->json(['data' => Milestone::getMilestoneTypes()]);
    }

    #[OA\Get(
        path: '/api/v1/milestones/statuses',
        summary: 'Obtener estados',
        description: 'Obtiene la lista de estados disponibles',
        tags: ['Milestones'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Estados obtenidos exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            )
        ]
    )]
    public function statuses(): JsonResponse
    {
        return response()->json(['data' => Milestone::getStatuses()]);
    }

    #[OA\Get(
        path: '/api/v1/milestones/priorities',
        summary: 'Obtener prioridades',
        description: 'Obtiene la lista de prioridades disponibles',
        tags: ['Milestones'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Prioridades obtenidas exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            )
        ]
    )]
    public function priorities(): JsonResponse
    {
        return response()->json(['data' => Milestone::getPriorities()]);
    }

    #[OA\Post(
        path: '/api/v1/milestones/{id}/start',
        summary: 'Iniciar hito',
        description: 'Marca un hito como iniciado',
        tags: ['Milestones'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID del hito',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hito iniciado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Milestone'),
                        new OA\Property(property: 'message', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Hito no encontrado'),
            new OA\Response(response: 422, description: 'No se puede iniciar este hito'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function start(Milestone $milestone): JsonResponse
    {
        if (!$milestone->canStart()) {
            return response()->json([
                'message' => 'No se puede iniciar este hito'
            ], 422);
        }

        $milestone->update([
            'status' => Milestone::STATUS_IN_PROGRESS,
            'start_date' => now()
        ]);

        $milestone->load(['parentMilestone', 'subMilestones', 'assignedTo', 'createdBy']);

        return response()->json([
            'data' => new MilestoneResource($milestone),
            'message' => 'Hito iniciado exitosamente'
        ]);
    }

    #[OA\Post(
        path: '/api/v1/milestones/{id}/complete',
        summary: 'Completar hito',
        description: 'Marca un hito como completado',
        tags: ['Milestones'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID del hito',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hito completado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Milestone'),
                        new OA\Property(property: 'message', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Hito no encontrado'),
            new OA\Response(response: 422, description: 'No se puede completar este hito'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function complete(Milestone $milestone): JsonResponse
    {
        if (!$milestone->canComplete()) {
            return response()->json([
                'message' => 'No se puede completar este hito'
            ], 422);
        }

        $milestone->update([
            'status' => Milestone::STATUS_COMPLETED,
            'completion_date' => now(),
            'progress_percentage' => 100
        ]);

        $milestone->load(['parentMilestone', 'subMilestones', 'assignedTo', 'createdBy']);

        return response()->json([
            'data' => new MilestoneResource($milestone),
            'message' => 'Hito completado exitosamente'
        ]);
    }

    #[OA\Post(
        path: '/api/v1/milestones/{id}/put-on-hold',
        summary: 'Poner hito en espera',
        description: 'Marca un hito como en espera',
        tags: ['Milestones'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID del hito',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hito puesto en espera exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Milestone'),
                        new OA\Property(property: 'message', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Hito no encontrado'),
            new OA\Response(response: 422, description: 'No se puede poner en espera este hito'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function putOnHold(Milestone $milestone): JsonResponse
    {
        if (!$milestone->canPutOnHold()) {
            return response()->json([
                'message' => 'No se puede poner en espera este hito'
            ], 422);
        }

        $milestone->update(['status' => Milestone::STATUS_ON_HOLD]);

        $milestone->load(['parentMilestone', 'subMilestones', 'assignedTo', 'createdBy']);

        return response()->json([
            'data' => new MilestoneResource($milestone),
            'message' => 'Hito puesto en espera exitosamente'
        ]);
    }

    #[OA\Post(
        path: '/api/v1/milestones/{id}/cancel',
        summary: 'Cancelar hito',
        description: 'Marca un hito como cancelado',
        tags: ['Milestones'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID del hito',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hito cancelado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Milestone'),
                        new OA\Property(property: 'message', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Hito no encontrado'),
            new OA\Response(response: 422, description: 'No se puede cancelar este hito'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function cancel(Milestone $milestone): JsonResponse
    {
        if (!$milestone->canCancel()) {
            return response()->json([
                'message' => 'No se puede cancelar este hito'
            ], 422);
        }

        $milestone->update(['status' => Milestone::STATUS_CANCELLED]);

        $milestone->load(['parentMilestone', 'subMilestones', 'assignedTo', 'createdBy']);

        return response()->json([
            'data' => new MilestoneResource($milestone),
            'message' => 'Hito cancelado exitosamente'
        ]);
    }

    #[OA\Post(
        path: '/api/v1/milestones/{id}/update-progress',
        summary: 'Actualizar progreso del hito',
        description: 'Actualiza el progreso y valor actual de un hito',
        tags: ['Milestones'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID del hito',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'current_value', type: 'number', description: 'Valor actual del progreso'),
                    new OA\Property(property: 'progress_percentage', type: 'number', description: 'Porcentaje de progreso (0-100)'),
                    new OA\Property(property: 'notes', type: 'string', description: 'Notas sobre el progreso')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Progreso actualizado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Milestone'),
                        new OA\Property(property: 'message', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Hito no encontrado'),
            new OA\Response(response: 422, description: 'Datos de validación inválidos'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function updateProgress(Request $request, Milestone $milestone): JsonResponse
    {
        $request->validate([
            'current_value' => 'required|numeric|min:0',
            'progress_percentage' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000'
        ]);

        $milestone->update([
            'current_value' => $request->current_value,
            'progress_percentage' => $request->progress_percentage,
            'notes' => $request->notes ? $milestone->notes . "\n\n" . now()->format('Y-m-d H:i:s') . ": " . $request->notes : $milestone->notes
        ]);

        $milestone->load(['parentMilestone', 'subMilestones', 'assignedTo', 'createdBy']);

        return response()->json([
            'data' => new MilestoneResource($milestone),
            'message' => 'Progreso actualizado exitosamente'
        ]);
    }

    #[OA\Post(
        path: '/api/v1/milestones/{id}/duplicate',
        summary: 'Duplicar hito',
        description: 'Crea una copia de un hito existente',
        tags: ['Milestones'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID del hito',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Hito duplicado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Milestone'),
                        new OA\Property(property: 'message', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Hito no encontrado'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function duplicate(Milestone $milestone): JsonResponse
    {
        $duplicate = $milestone->replicate();
        $duplicate->title = $milestone->title . ' (Copia)';
        $duplicate->status = Milestone::STATUS_NOT_STARTED;
        $duplicate->start_date = null;
        $duplicate->completion_date = null;
        $duplicate->current_value = 0;
        $duplicate->progress_percentage = 0;
        $duplicate->created_by = auth()->id();
        $duplicate->save();

        $duplicate->load(['parentMilestone', 'assignedTo', 'createdBy']);

        return response()->json([
            'data' => new MilestoneResource($duplicate),
            'message' => 'Hito duplicado exitosamente'
        ], 201);
    }

    #[OA\Get(
        path: '/api/v1/milestones/overdue',
        summary: 'Obtener hitos vencidos',
        description: 'Obtiene solo los hitos que están vencidos',
        tags: ['Milestones'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hitos vencidos obtenidos exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Milestone'))
                    ]
                )
            )
        ]
    )]
    public function overdue(): AnonymousResourceCollection
    {
        $milestones = Milestone::overdueStatus()
            ->with(['parentMilestone', 'assignedTo', 'createdBy'])
            ->orderBy('target_date')
            ->get();

        return MilestoneResource::collection($milestones);
    }

    #[OA\Get(
        path: '/api/v1/milestones/due-soon',
        summary: 'Obtener hitos próximos a vencer',
        description: 'Obtiene hitos que vencen en los próximos 7 días',
        tags: ['Milestones'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hitos próximos a vencer obtenidos exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Milestone'))
                    ]
                )
            )
        ]
    )]
    public function dueSoon(): AnonymousResourceCollection
    {
        $milestones = Milestone::dueSoon(7)
            ->with(['parentMilestone', 'assignedTo', 'createdBy'])
            ->orderBy('target_date')
            ->get();

        return MilestoneResource::collection($milestones);
    }

    #[OA\Get(
        path: '/api/v1/milestones/due-today',
        summary: 'Obtener hitos que vencen hoy',
        description: 'Obtiene hitos que tienen fecha objetivo hoy',
        tags: ['Milestones'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hitos que vencen hoy obtenidos exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Milestone'))
                    ]
                )
            )
        ]
    )]
    public function dueToday(): AnonymousResourceCollection
    {
        $milestones = Milestone::whereDate('target_date', today())
            ->with(['parentMilestone', 'assignedTo', 'createdBy'])
            ->orderBy('priority')
            ->orderBy('target_date')
            ->get();

        return MilestoneResource::collection($milestones);
    }

    #[OA\Get(
        path: '/api/v1/milestones/high-priority',
        summary: 'Obtener hitos de alta prioridad',
        description: 'Obtiene solo los hitos de alta prioridad (high, urgent, critical)',
        tags: ['Milestones'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hitos de alta prioridad obtenidos exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Milestone'))
                    ]
                )
            )
        ]
    )]
    public function highPriority(): AnonymousResourceCollection
    {
        $milestones = Milestone::highPriority()
            ->with(['parentMilestone', 'assignedTo', 'createdBy'])
            ->orderBy('priority')
            ->orderBy('target_date')
            ->get();

        return MilestoneResource::collection($milestones);
    }

    #[OA\Get(
        path: '/api/v1/milestones/root',
        summary: 'Obtener hitos raíz',
        description: 'Obtiene solo los hitos que no tienen hito padre',
        tags: ['Milestones'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hitos raíz obtenidos exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Milestone'))
                    ]
                )
            )
        ]
    )]
    public function root(): AnonymousResourceCollection
    {
        $milestones = Milestone::rootMilestones()
            ->with(['subMilestones', 'assignedTo', 'createdBy'])
            ->orderBy('target_date')
            ->get();

        return MilestoneResource::collection($milestones);
    }

    #[OA\Get(
        path: '/api/v1/milestones/by-type/{type}',
        summary: 'Obtener hitos por tipo',
        description: 'Obtiene hitos filtrados por tipo específico',
        tags: ['Milestones'],
        parameters: [
            new OA\Parameter(
                name: 'type',
                in: 'path',
                description: 'Tipo de hito',
                required: true,
                schema: new OA\Schema(type: 'string', enum: ['project', 'financial', 'operational', 'regulatory', 'community', 'environmental', 'other'])
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hitos por tipo obtenidos exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Milestone'))
                    ]
                )
            )
        ]
    )]
    public function byType(string $type): AnonymousResourceCollection
    {
        $milestones = Milestone::byType($type)
            ->with(['parentMilestone', 'assignedTo', 'createdBy'])
            ->orderBy('target_date')
            ->get();

        return MilestoneResource::collection($milestones);
    }

    #[OA\Get(
        path: '/api/v1/milestones/by-status/{status}',
        summary: 'Obtener hitos por estado',
        description: 'Obtiene hitos filtrados por estado específico',
        tags: ['Milestones'],
        parameters: [
            new OA\Parameter(
                name: 'status',
                in: 'path',
                description: 'Estado del hito',
                required: true,
                schema: new OA\Schema(type: 'string', enum: ['not_started', 'in_progress', 'completed', 'on_hold', 'cancelled', 'overdue'])
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hitos por estado obtenidos exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Milestone'))
                    ]
                )
            )
        ]
    )]
    public function byStatus(string $status): AnonymousResourceCollection
    {
        $milestones = Milestone::byStatus($status)
            ->with(['parentMilestone', 'assignedTo', 'createdBy'])
            ->orderBy('target_date')
            ->get();

        return MilestoneResource::collection($milestones);
    }

    #[OA\Get(
        path: '/api/v1/milestones/by-priority/{priority}',
        summary: 'Obtener hitos por prioridad',
        description: 'Obtiene hitos filtrados por prioridad específica',
        tags: ['Milestones'],
        parameters: [
            new OA\Parameter(
                name: 'priority',
                in: 'path',
                description: 'Prioridad del hito',
                required: true,
                schema: new OA\Schema(type: 'string', enum: ['low', 'medium', 'high', 'urgent', 'critical'])
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hitos por prioridad obtenidos exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Milestone'))
                    ]
                )
            )
        ]
    )]
    public function byPriority(string $priority): AnonymousResourceCollection
    {
        $milestones = Milestone::byPriority($priority)
            ->with(['parentMilestone', 'assignedTo', 'createdBy'])
            ->orderBy('target_date')
            ->get();

        return MilestoneResource::collection($milestones);
    }

    #[OA\Get(
        path: '/api/v1/milestones/assigned-to-me',
        summary: 'Obtener hitos asignados al usuario autenticado',
        description: 'Obtiene hitos asignados al usuario que está autenticado',
        tags: ['Milestones'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hitos asignados obtenidos exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Milestone'))
                    ]
                )
            )
        ]
    )]
    public function assignedToMe(): AnonymousResourceCollection
    {
        $milestones = Milestone::byAssignedTo(auth()->id())
            ->with(['parentMilestone', 'subMilestones', 'assignedTo', 'createdBy'])
            ->orderBy('target_date')
            ->get();

        return MilestoneResource::collection($milestones);
    }

    #[OA\Get(
        path: '/api/v1/milestones/created-by-me',
        summary: 'Obtener hitos creados por el usuario autenticado',
        description: 'Obtiene hitos creados por el usuario que está autenticado',
        tags: ['Milestones'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hitos creados obtenidos exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Milestone'))
                    ]
                )
            )
        ]
    )]
    public function createdByMe(): AnonymousResourceCollection
    {
        $milestones = Milestone::byCreatedBy(auth()->id())
            ->with(['parentMilestone', 'subMilestones', 'assignedTo', 'createdBy'])
            ->orderBy('target_date')
            ->get();

        return MilestoneResource::collection($milestones);
    }
}
