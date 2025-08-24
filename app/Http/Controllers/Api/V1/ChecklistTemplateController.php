<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ChecklistTemplate\ChecklistTemplateResource;
use App\Http\Resources\Api\V1\ChecklistTemplate\ChecklistTemplateCollection;
use App\Http\Requests\Api\V1\ChecklistTemplate\StoreChecklistTemplateRequest;
use App\Http\Requests\Api\V1\ChecklistTemplate\UpdateChecklistTemplateRequest;
use App\Models\ChecklistTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Checklist Templates',
    description: 'Endpoints para gestión de plantillas de listas de verificación'
)]
class ChecklistTemplateController extends \App\Http\Controllers\Controller
{
    #[OA\Get(
        path: '/api/v1/checklist-templates',
        summary: 'Listar plantillas de listas de verificación',
        description: 'Obtiene una lista paginada de plantillas de listas de verificación con filtros opcionales',
        tags: ['Checklist Templates'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                in: 'query',
                description: 'Buscar en nombre y descripción',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'template_type',
                in: 'query',
                description: 'Filtrar por tipo de plantilla',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['maintenance', 'inspection', 'safety', 'quality', 'compliance', 'audit', 'training', 'operations', 'procedure', 'workflow'])
            ),
            new OA\Parameter(
                name: 'category',
                in: 'query',
                description: 'Filtrar por categoría',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'priority',
                in: 'query',
                description: 'Filtrar por prioridad',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['low', 'medium', 'high', 'urgent', 'critical'])
            ),
            new OA\Parameter(
                name: 'risk_level',
                in: 'query',
                description: 'Filtrar por nivel de riesgo',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['low', 'medium', 'high', 'extreme'])
            ),
            new OA\Parameter(
                name: 'department',
                in: 'query',
                description: 'Filtrar por departamento',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'is_active',
                in: 'query',
                description: 'Filtrar por estado activo',
                required: false,
                schema: new OA\Schema(type: 'boolean')
            ),
            new OA\Parameter(
                name: 'is_standard',
                in: 'query',
                description: 'Filtrar por plantillas estándar',
                required: false,
                schema: new OA\Schema(type: 'boolean')
            ),
            new OA\Parameter(
                name: 'is_approved',
                in: 'query',
                description: 'Filtrar por estado de aprobación',
                required: false,
                schema: new OA\Schema(type: 'boolean')
            ),
            new OA\Parameter(
                name: 'sort',
                in: 'query',
                description: 'Campo para ordenar',
                required: false,
                schema: new OA\Schema(type: 'string', default: 'created_at')
            ),
            new OA\Parameter(
                name: 'order',
                in: 'query',
                description: 'Orden ascendente o descendente',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'desc')
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
                description: 'Lista de plantillas obtenida exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ChecklistTemplate')),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/Meta'),
                        new OA\Property(property: 'summary', ref: '#/components/schemas/ChecklistTemplateSummary')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autorizado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ChecklistTemplate::query()
            ->with(['createdBy', 'approvedBy', 'reviewedBy']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('template_type')) {
            $query->byType($request->template_type);
        }

        if ($request->filled('category')) {
            $query->byCategory($request->category);
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
        $sortField = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortField, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $checklistTemplates = $query->paginate($perPage);

        return new ChecklistTemplateCollection($checklistTemplates);
    }

    #[OA\Get(
        path: '/api/v1/checklist-templates/{id}',
        summary: 'Mostrar plantilla de lista de verificación',
        description: 'Obtiene los detalles de una plantilla de lista de verificación específica',
        tags: ['Checklist Templates'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de la plantilla',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Plantilla obtenida exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/ChecklistTemplate')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Plantilla no encontrada'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function show(ChecklistTemplate $checklistTemplate): JsonResponse
    {
        $checklistTemplate->load(['createdBy', 'approvedBy', 'reviewedBy']);

        return response()->json([
            'data' => new ChecklistTemplateResource($checklistTemplate)
        ]);
    }

    #[OA\Post(
        path: '/api/v1/checklist-templates',
        summary: 'Crear plantilla de lista de verificación',
        description: 'Crea una nueva plantilla de lista de verificación',
        tags: ['Checklist Templates'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StoreChecklistTemplateRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Plantilla creada exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/ChecklistTemplate'),
                        new OA\Property(property: 'message', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Datos de validación inválidos'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function store(StoreChecklistTemplateRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['created_by'] = auth()->id();

            $checklistTemplate = ChecklistTemplate::create($data);
            $checklistTemplate->load(['createdBy']);

            Log::info('ChecklistTemplate created', [
                'id' => $checklistTemplate->id,
                'name' => $checklistTemplate->name,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'data' => new ChecklistTemplateResource($checklistTemplate),
                'message' => 'Plantilla de lista de verificación creada exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating ChecklistTemplate', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Error al crear la plantilla de lista de verificación'
            ], 500);
        }
    }

    #[OA\Put(
        path: '/api/v1/checklist-templates/{id}',
        summary: 'Actualizar plantilla de lista de verificación',
        description: 'Actualiza una plantilla de lista de verificación existente',
        tags: ['Checklist Templates'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de la plantilla',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateChecklistTemplateRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Plantilla actualizada exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/ChecklistTemplate'),
                        new OA\Property(property: 'message', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Plantilla no encontrada'),
            new OA\Response(response: 422, description: 'Datos de validación inválidos'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function update(UpdateChecklistTemplateRequest $request, ChecklistTemplate $checklistTemplate): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $checklistTemplate->update($data);
            $checklistTemplate->load(['createdBy', 'approvedBy', 'reviewedBy']);

            Log::info('ChecklistTemplate updated', [
                'id' => $checklistTemplate->id,
                'name' => $checklistTemplate->name,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'data' => new ChecklistTemplateResource($checklistTemplate),
                'message' => 'Plantilla de lista de verificación actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating ChecklistTemplate', [
                'id' => $checklistTemplate->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Error al actualizar la plantilla de lista de verificación'
            ], 500);
        }
    }

    #[OA\Delete(
        path: '/api/v1/checklist-templates/{id}',
        summary: 'Eliminar plantilla de lista de verificación',
        description: 'Elimina una plantilla de lista de verificación (soft delete)',
        tags: ['Checklist Templates'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de la plantilla',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Plantilla eliminada exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Plantilla no encontrada'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function destroy(ChecklistTemplate $checklistTemplate): JsonResponse
    {
        try {
            DB::beginTransaction();

            $checklistTemplate->delete();

            Log::info('ChecklistTemplate deleted', [
                'id' => $checklistTemplate->id,
                'name' => $checklistTemplate->name,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Plantilla de lista de verificación eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting ChecklistTemplate', [
                'id' => $checklistTemplate->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Error al eliminar la plantilla de lista de verificación'
            ], 500);
        }
    }

    #[OA\Get(
        path: '/api/v1/checklist-templates/statistics',
        summary: 'Obtener estadísticas de plantillas',
        description: 'Obtiene estadísticas generales de las plantillas de listas de verificación',
        tags: ['Checklist Templates'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Estadísticas obtenidas exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/ChecklistTemplateStatistics')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function statistics(): JsonResponse
    {
        $statistics = [
            'total_templates' => ChecklistTemplate::count(),
            'active' => ChecklistTemplate::active()->count(),
            'inactive' => ChecklistTemplate::where('is_active', false)->count(),
            'standard' => ChecklistTemplate::standard()->count(),
            'custom' => ChecklistTemplate::where('is_standard', false)->count(),
            'approved' => ChecklistTemplate::approved()->count(),
            'pending_approval' => ChecklistTemplate::pendingApproval()->count(),
            'needs_review' => ChecklistTemplate::needsReview()->count(),
            'by_type' => ChecklistTemplate::selectRaw('template_type, COUNT(*) as count')
                ->groupBy('template_type')
                ->pluck('count', 'template_type')
                ->toArray(),
            'by_category' => ChecklistTemplate::selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
            'by_priority' => ChecklistTemplate::selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->pluck('count', 'priority')
                ->toArray(),
            'by_risk_level' => ChecklistTemplate::selectRaw('risk_level, COUNT(*) as count')
                ->groupBy('risk_level')
                ->pluck('count', 'risk_level')
                ->toArray(),
            'by_department' => ChecklistTemplate::selectRaw('department, COUNT(*) as count')
                ->groupBy('department')
                ->pluck('count', 'department')
                ->toArray(),
            'average_completion_time' => ChecklistTemplate::whereNotNull('estimated_completion_time')
                ->avg('estimated_completion_time'),
            'average_cost' => ChecklistTemplate::whereNotNull('estimated_cost')
                ->avg('estimated_cost'),
        ];

        return response()->json(['data' => $statistics]);
    }

    #[OA\Get(
        path: '/api/v1/checklist-templates/template-types',
        summary: 'Obtener tipos de plantillas',
        description: 'Obtiene la lista de tipos de plantillas disponibles',
        tags: ['Checklist Templates'],
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
    public function templateTypes(): JsonResponse
    {
        return response()->json(['data' => ChecklistTemplate::getTemplateTypes()]);
    }

    #[OA\Get(
        path: '/api/v1/checklist-templates/priorities',
        summary: 'Obtener prioridades',
        description: 'Obtiene la lista de prioridades disponibles',
        tags: ['Checklist Templates'],
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
        return response()->json(['data' => ChecklistTemplate::getPriorities()]);
    }

    #[OA\Get(
        path: '/api/v1/checklist-templates/risk-levels',
        summary: 'Obtener niveles de riesgo',
        description: 'Obtiene la lista de niveles de riesgo disponibles',
        tags: ['Checklist Templates'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Niveles de riesgo obtenidos exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            )
        ]
    )]
    public function riskLevels(): JsonResponse
    {
        return response()->json(['data' => ChecklistTemplate::getRiskLevels()]);
    }

    #[OA\Patch(
        path: '/api/v1/checklist-templates/{id}/toggle-active',
        summary: 'Alternar estado activo',
        description: 'Alterna el estado activo de una plantilla',
        tags: ['Checklist Templates'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de la plantilla',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Estado alternado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/ChecklistTemplate'),
                        new OA\Property(property: 'message', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Plantilla no encontrada'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function toggleActive(ChecklistTemplate $checklistTemplate): JsonResponse
    {
        $checklistTemplate->update(['is_active' => !$checklistTemplate->is_active]);

        return response()->json([
            'data' => new ChecklistTemplateResource($checklistTemplate),
            'message' => 'Estado activo alternado exitosamente'
        ]);
    }

    #[OA\Patch(
        path: '/api/v1/checklist-templates/{id}/toggle-standard',
        summary: 'Alternar estado estándar',
        description: 'Alterna el estado estándar de una plantilla',
        tags: ['Checklist Templates'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de la plantilla',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Estado estándar alternado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/ChecklistTemplate'),
                        new OA\Property(property: 'message', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Plantilla no encontrada'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function toggleStandard(ChecklistTemplate $checklistTemplate): JsonResponse
    {
        $checklistTemplate->update(['is_standard' => !$checklistTemplate->is_standard]);

        return response()->json([
            'data' => new ChecklistTemplateResource($checklistTemplate),
            'message' => 'Estado estándar alternado exitosamente'
        ]);
    }

    #[OA\Post(
        path: '/api/v1/checklist-templates/{id}/duplicate',
        summary: 'Duplicar plantilla',
        description: 'Crea una copia de una plantilla existente',
        tags: ['Checklist Templates'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de la plantilla',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Plantilla duplicada exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/ChecklistTemplate'),
                        new OA\Property(property: 'message', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Plantilla no encontrada'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function duplicate(ChecklistTemplate $checklistTemplate): JsonResponse
    {
        $duplicate = $checklistTemplate->replicate();
        $duplicate->name = $checklistTemplate->name . ' (Copia)';
        $duplicate->version = $this->incrementVersion($checklistTemplate->version);
        $duplicate->is_standard = false;
        $duplicate->approved_at = null;
        $duplicate->approved_by = null;
        $duplicate->created_by = auth()->id();
        $duplicate->save();

        $duplicate->load(['createdBy']);

        return response()->json([
            'data' => new ChecklistTemplateResource($duplicate),
            'message' => 'Plantilla duplicada exitosamente'
        ], 201);
    }

    #[OA\Get(
        path: '/api/v1/checklist-templates/active',
        summary: 'Obtener plantillas activas',
        description: 'Obtiene solo las plantillas activas',
        tags: ['Checklist Templates'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Plantillas activas obtenidas exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ChecklistTemplate'))
                    ]
                )
            )
        ]
    )]
    public function active(): AnonymousResourceCollection
    {
        $checklistTemplates = ChecklistTemplate::active()
            ->with(['createdBy', 'approvedBy'])
            ->orderBy('name')
            ->get();

        return ChecklistTemplateResource::collection($checklistTemplates);
    }

    #[OA\Get(
        path: '/api/v1/checklist-templates/standard',
        summary: 'Obtener plantillas estándar',
        description: 'Obtiene solo las plantillas estándar',
        tags: ['Checklist Templates'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Plantillas estándar obtenidas exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ChecklistTemplate'))
                    ]
                )
            )
        ]
    )]
    public function standard(): AnonymousResourceCollection
    {
        $checklistTemplates = ChecklistTemplate::standard()
            ->with(['createdBy', 'approvedBy'])
            ->orderBy('name')
            ->get();

        return ChecklistTemplateResource::collection($checklistTemplates);
    }

    #[OA\Get(
        path: '/api/v1/checklist-templates/approved',
        summary: 'Obtener plantillas aprobadas',
        description: 'Obtiene solo las plantillas aprobadas',
        tags: ['Checklist Templates'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Plantillas aprobadas obtenidas exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ChecklistTemplate'))
                    ]
                )
            )
        ]
    )]
    public function approved(): AnonymousResourceCollection
    {
        $checklistTemplates = ChecklistTemplate::approved()
            ->with(['createdBy', 'approvedBy'])
            ->orderBy('name')
            ->get();

        return ChecklistTemplateResource::collection($checklistTemplates);
    }

    #[OA\Get(
        path: '/api/v1/checklist-templates/pending-approval',
        summary: 'Obtener plantillas pendientes de aprobación',
        description: 'Obtiene solo las plantillas pendientes de aprobación',
        tags: ['Checklist Templates'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Plantillas pendientes obtenidas exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ChecklistTemplate'))
                    ]
                )
            )
        ]
    )]
    public function pendingApproval(): AnonymousResourceCollection
    {
        $checklistTemplates = ChecklistTemplate::pendingApproval()
            ->with(['createdBy', 'approvedBy'])
            ->orderBy('name')
            ->get();

        return ChecklistTemplateResource::collection($checklistTemplates);
    }

    #[OA\Get(
        path: '/api/v1/checklist-templates/by-type/{type}',
        summary: 'Obtener plantillas por tipo',
        description: 'Obtiene plantillas filtradas por tipo específico',
        tags: ['Checklist Templates'],
        parameters: [
            new OA\Parameter(
                name: 'type',
                in: 'path',
                description: 'Tipo de plantilla',
                required: true,
                schema: new OA\Schema(type: 'string', enum: ['maintenance', 'inspection', 'safety', 'quality', 'compliance', 'audit', 'training', 'operations', 'procedure', 'workflow'])
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Plantillas por tipo obtenidas exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ChecklistTemplate'))
                    ]
                )
            )
        ]
    )]
    public function byType(string $type): AnonymousResourceCollection
    {
        $checklistTemplates = ChecklistTemplate::byType($type)
            ->with(['createdBy', 'approvedBy'])
            ->orderBy('name')
            ->get();

        return ChecklistTemplateResource::collection($checklistTemplates);
    }

    #[OA\Get(
        path: '/api/v1/checklist-templates/by-category/{category}',
        summary: 'Obtener plantillas por categoría',
        description: 'Obtiene plantillas filtradas por categoría específica',
        tags: ['Checklist Templates'],
        parameters: [
            new OA\Parameter(
                name: 'category',
                in: 'path',
                description: 'Categoría de plantilla',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Plantillas por categoría obtenidas exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ChecklistTemplate'))
                    ]
                )
            )
        ]
    )]
    public function byCategory(string $category): AnonymousResourceCollection
    {
        $checklistTemplates = ChecklistTemplate::byCategory($category)
            ->with(['createdBy', 'approvedBy'])
            ->orderBy('name')
            ->get();

        return ChecklistTemplateResource::collection($checklistTemplates);
    }

    #[OA\Get(
        path: '/api/v1/checklist-templates/by-priority/{priority}',
        summary: 'Obtener plantillas por prioridad',
        description: 'Obtiene plantillas filtradas por prioridad específica',
        tags: ['Checklist Templates'],
        parameters: [
            new OA\Parameter(
                name: 'priority',
                in: 'path',
                description: 'Prioridad de plantilla',
                required: true,
                schema: new OA\Schema(type: 'string', enum: ['low', 'medium', 'high', 'urgent', 'critical'])
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Plantillas por prioridad obtenidas exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ChecklistTemplate'))
                    ]
                )
            )
        ]
    )]
    public function byPriority(string $priority): AnonymousResourceCollection
    {
        $checklistTemplates = ChecklistTemplate::byPriority($priority)
            ->with(['createdBy', 'approvedBy'])
            ->orderBy('name')
            ->get();

        return ChecklistTemplateResource::collection($checklistTemplates);
    }

    #[OA\Get(
        path: '/api/v1/checklist-templates/by-risk-level/{riskLevel}',
        summary: 'Obtener plantillas por nivel de riesgo',
        description: 'Obtiene plantillas filtradas por nivel de riesgo específico',
        tags: ['Checklist Templates'],
        parameters: [
            new OA\Parameter(
                name: 'riskLevel',
                in: 'path',
                description: 'Nivel de riesgo de plantilla',
                required: true,
                schema: new OA\Schema(type: 'string', enum: ['low', 'medium', 'high', 'extreme'])
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Plantillas por nivel de riesgo obtenidas exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ChecklistTemplate'))
                    ]
                )
            )
        ]
    )]
    public function byRiskLevel(string $riskLevel): AnonymousResourceCollection
    {
        $checklistTemplates = ChecklistTemplate::byRiskLevel($riskLevel)
            ->with(['createdBy', 'approvedBy'])
            ->orderBy('name')
            ->get();

        return ChecklistTemplateResource::collection($checklistTemplates);
    }

    #[OA\Get(
        path: '/api/v1/checklist-templates/by-department/{department}',
        summary: 'Obtener plantillas por departamento',
        description: 'Obtiene plantillas filtradas por departamento específico',
        tags: ['Checklist Templates'],
        parameters: [
            new OA\Parameter(
                name: 'department',
                in: 'path',
                description: 'Departamento de plantilla',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Plantillas por departamento obtenidas exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ChecklistTemplate'))
                    ]
                )
            )
        ]
    )]
    public function byDepartment(string $department): AnonymousResourceCollection
    {
        $checklistTemplates = ChecklistTemplate::byDepartment($department)
            ->with(['createdBy', 'approvedBy'])
            ->orderBy('name')
            ->get();

        return ChecklistTemplateResource::collection($checklistTemplates);
    }

    #[OA\Get(
        path: '/api/v1/checklist-templates/high-priority',
        summary: 'Obtener plantillas de alta prioridad',
        description: 'Obtiene solo las plantillas de alta prioridad (high, urgent, critical)',
        tags: ['Checklist Templates'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Plantillas de alta prioridad obtenidas exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ChecklistTemplate'))
                    ]
                )
            )
        ]
    )]
    public function highPriority(): AnonymousResourceCollection
    {
        $checklistTemplates = ChecklistTemplate::whereIn('priority', ['high', 'urgent', 'critical'])
            ->with(['createdBy', 'approvedBy'])
            ->orderBy('priority')
            ->orderBy('name')
            ->get();

        return ChecklistTemplateResource::collection($checklistTemplates);
    }

    #[OA\Get(
        path: '/api/v1/checklist-templates/high-risk',
        summary: 'Obtener plantillas de alto riesgo',
        description: 'Obtiene solo las plantillas de alto riesgo (high, extreme)',
        tags: ['Checklist Templates'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Plantillas de alto riesgo obtenidas exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ChecklistTemplate'))
                    ]
                )
            )
        ]
    )]
    public function highRisk(): AnonymousResourceCollection
    {
        $checklistTemplates = ChecklistTemplate::whereIn('risk_level', ['high', 'extreme'])
            ->with(['createdBy', 'approvedBy'])
            ->orderBy('risk_level')
            ->orderBy('name')
            ->get();

        return ChecklistTemplateResource::collection($checklistTemplates);
    }

    #[OA\Get(
        path: '/api/v1/checklist-templates/needs-review',
        summary: 'Obtener plantillas que necesitan revisión',
        description: 'Obtiene plantillas que necesitan revisión o están próximas a necesitarla',
        tags: ['Checklist Templates'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Plantillas que necesitan revisión obtenidas exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ChecklistTemplate'))
                    ]
                )
            )
        ]
    )]
    public function needsReview(): AnonymousResourceCollection
    {
        $checklistTemplates = ChecklistTemplate::needsReview()
            ->with(['createdBy', 'approvedBy', 'reviewedBy'])
            ->orderBy('next_review_date')
            ->orderBy('name')
            ->get();

        return ChecklistTemplateResource::collection($checklistTemplates);
    }

    /**
     * Incrementa la versión de una plantilla
     */
    private function incrementVersion(string $version): string
    {
        if (preg_match('/^(\d+)\.(\d+)$/', $version, $matches)) {
            $major = (int) $matches[1];
            $minor = (int) $matches[2];
            return $major . '.' . ($minor + 1);
        }
        
        return '1.1';
    }
}
