<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\FormSubmission\StoreFormSubmissionRequest;
use App\Http\Requests\Api\V1\FormSubmission\UpdateFormSubmissionRequest;
use App\Http\Resources\Api\V1\FormSubmissionResource;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Form Submissions',
    description: 'Gestión de envíos de formularios'
)]
class FormSubmissionController extends Controller
{
    #[OA\Get(
        path: '/api/v1/form-submissions',
        tags: ['Form Submissions'],
        summary: 'Listar envíos de formularios',
        description: 'Obtiene todos los envíos de formularios con filtros opcionales',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'status',
                in: 'query',
                description: 'Filtrar por estado',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['pending', 'processed', 'archived'])
            ),
            new OA\Parameter(
                name: 'form_type',
                in: 'query',
                description: 'Filtrar por tipo de formulario',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'assigned_to',
                in: 'query',
                description: 'Filtrar por usuario asignado',
                required: false,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de envíos obtenida exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/FormSubmissionResource'))
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Sin permisos para ver formularios')
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        // Solo usuarios autenticados pueden listar envíos
        // En producción, se pueden implementar permisos más específicos

        $query = FormSubmission::query()
            ->with(['processedBy', 'organization'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by form type
        if ($request->filled('form_name')) {
            $query->byFormType($request->form_name);
        }

        // Filter by processed status
        if ($request->boolean('unprocessed_only')) {
            $query->unprocessed();
        }

        // Filter by processed by user
        if ($request->filled('processed_by')) {
            $query->processedBy($request->processed_by);
        }

        // Filter recent submissions
        if ($request->filled('recent_days')) {
            $query->recent((int) $request->recent_days);
        }

        // Filter by IP address
        if ($request->filled('ip_address')) {
            $query->fromIp($request->ip_address);
        }

        // Search in fields
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('fields->name', 'LIKE', "%{$search}%")
                  ->orWhere('fields->email', 'LIKE', "%{$search}%")
                  ->orWhere('fields->message', 'LIKE', "%{$search}%")
                  ->orWhere('fields->subject', 'LIKE', "%{$search}%")
                  ->orWhere('fields->full_name', 'LIKE', "%{$search}%")
                  ->orWhere('fields->mensaje', 'LIKE', "%{$search}%")
                  ->orWhere('fields->asunto', 'LIKE', "%{$search}%");
            });
        }

        $perPage = min($request->get('per_page', 20), 50);
        $submissions = $query->paginate($perPage);

        return FormSubmissionResource::collection($submissions);
    }

    #[OA\Post(
        path: '/api/v1/form-submissions',
        tags: ['Form Submissions'],
        summary: 'Crear nuevo envío de formulario',
        description: 'Registra un nuevo envío de formulario',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['form_name', 'form_type', 'form_data'],
                properties: [
                    new OA\Property(property: 'form_name', type: 'string', maxLength: 255, example: 'Formulario de Contacto'),
                    new OA\Property(property: 'form_type', type: 'string', maxLength: 100, example: 'contact'),
                    new OA\Property(property: 'form_data', type: 'object', example: ['mensaje' => 'Hola, necesito información']),
                    new OA\Property(property: 'name', type: 'string', maxLength: 255, nullable: true, example: 'Juan Pérez'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true, example: 'juan@email.com'),
                    new OA\Property(property: 'source_page', type: 'string', nullable: true, example: '/contacto')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Envío creado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/FormSubmissionResource'),
                        new OA\Property(property: 'message', type: 'string', example: 'Formulario enviado exitosamente')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Errores de validación')
        ]
    )]
    public function store(StoreFormSubmissionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Capture client information
        $validated['ip_address'] = $request->ip();
        $validated['user_agent'] = $request->userAgent();
        $validated['referrer'] = $request->header('referer');
        $validated['status'] = 'pending'; // Always start as pending

        $submission = FormSubmission::create($validated);
        $submission->load(['processedBy', 'organization']);

        return response()->json([
            'data' => new FormSubmissionResource($submission),
            'message' => 'Formulario enviado exitosamente'
        ], 201);
    }

    #[OA\Get(
        path: '/api/v1/form-submissions/{form_submission}',
        tags: ['Form Submissions'],
        summary: 'Ver envío de formulario específico',
        description: 'Obtiene los detalles de un envío de formulario específico',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'form_submission',
                in: 'path',
                description: 'ID del envío de formulario',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Envío obtenido exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/FormSubmissionResource')
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Sin permisos para ver este envío'),
            new OA\Response(response: 404, description: 'Envío no encontrado')
        ]
    )]
    public function show(FormSubmission $formSubmission): JsonResponse
    {
        // Solo usuarios autenticados pueden ver detalles
        // En producción, se pueden implementar permisos más específicos

        $formSubmission->load(['processedBy', 'organization']);

        return response()->json([
            'data' => new FormSubmissionResource($formSubmission)
        ]);
    }

    #[OA\Put(
        path: '/api/v1/form-submissions/{form_submission}',
        tags: ['Form Submissions'],
        summary: 'Actualizar envío de formulario',
        description: 'Actualiza el estado y metadatos de un envío de formulario',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'form_submission',
                in: 'path',
                description: 'ID del envío de formulario',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'reviewed', 'processed', 'rejected'], example: 'processed'),
                    new OA\Property(property: 'assigned_to_user_id', type: 'integer', nullable: true, example: 1),
                    new OA\Property(property: 'internal_notes', type: 'string', nullable: true, example: 'Revisado y aprobado')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Envío actualizado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/FormSubmissionResource'),
                        new OA\Property(property: 'message', type: 'string', example: 'Envío actualizado exitosamente')
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Sin permisos para actualizar este envío'),
            new OA\Response(response: 404, description: 'Envío no encontrado'),
            new OA\Response(response: 422, description: 'Errores de validación')
        ]
    )]
    public function update(UpdateFormSubmissionRequest $request, FormSubmission $formSubmission): JsonResponse
    {
        // Solo usuarios autenticados pueden actualizar envíos
        // En producción, se pueden implementar permisos más específicos

        $validated = $request->validated();

        $formSubmission->update($validated);
        $formSubmission->load(['processedBy', 'organization']);

        return response()->json([
            'data' => new FormSubmissionResource($formSubmission),
            'message' => 'Envío actualizado exitosamente'
        ]);
    }

    #[OA\Delete(
        path: '/api/v1/form-submissions/{form_submission}',
        tags: ['Form Submissions'],
        summary: 'Eliminar envío de formulario',
        description: 'Elimina permanentemente un envío de formulario',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'form_submission',
                in: 'path',
                description: 'ID del envío de formulario',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Envío eliminado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Envío eliminado exitosamente')
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Sin permisos para eliminar este envío'),
            new OA\Response(response: 404, description: 'Envío no encontrado')
        ]
    )]
    public function destroy(FormSubmission $formSubmission): JsonResponse
    {
        // Solo usuarios autenticados pueden eliminar envíos
        // En producción, se pueden implementar permisos más específicos

        $formSubmission->delete();

        return response()->json([
            'message' => 'Envío eliminado exitosamente'
        ]);
    }

    public function markAsProcessed(Request $request, FormSubmission $formSubmission): JsonResponse
    {
        $request->validate([
            'processing_notes' => 'nullable|string|max:1000',
        ]);

        $formSubmission->markAsProcessed(
            auth()->id(),
            $request->processing_notes
        );

        $formSubmission->load(['processedBy', 'organization']);

        return response()->json([
            'data' => new FormSubmissionResource($formSubmission),
            'message' => 'Envío marcado como procesado'
        ]);
    }

    public function markAsSpam(FormSubmission $formSubmission): JsonResponse
    {
        $formSubmission->markAsSpam();

        return response()->json([
            'data' => new FormSubmissionResource($formSubmission),
            'message' => 'Envío marcado como spam'
        ]);
    }

    public function archive(FormSubmission $formSubmission): JsonResponse
    {
        $formSubmission->archive();

        return response()->json([
            'data' => new FormSubmissionResource($formSubmission),
            'message' => 'Envío archivado exitosamente'
        ]);
    }

    public function reopen(FormSubmission $formSubmission): JsonResponse
    {
        $formSubmission->reopen();

        return response()->json([
            'data' => new FormSubmissionResource($formSubmission),
            'message' => 'Envío reabierto exitosamente'
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        // Solo usuarios autenticados pueden ver estadísticas
        // En producción, se pueden implementar permisos más específicos

        $organizationId = $request->get('organization_id');
        $query = FormSubmission::query();

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        $stats = [
            'total_submissions' => $query->count(),
            'pending' => $query->clone()->pending()->count(),
            'processed' => $query->clone()->processed()->count(),
            'archived' => $query->clone()->archived()->count(),
            'spam' => $query->clone()->spam()->count(),
            'this_month' => $query->clone()->where('created_at', '>=', now()->startOfMonth())->count(),
            'this_week' => $query->clone()->where('created_at', '>=', now()->startOfWeek())->count(),
            'today' => $query->clone()->whereDate('created_at', today())->count(),
            'by_form_type' => $query->clone()
                ->selectRaw('form_name, COUNT(*) as count')
                ->groupBy('form_name')
                ->orderBy('count', 'desc')
                ->get(),
            'processing_time' => [
                'avg_hours' => $query->clone()->processed()
                    ->whereNotNull('processed_at')
                    ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, processed_at)) as avg_hours')
                    ->value('avg_hours'),
                'total_processed_today' => $query->clone()
                    ->whereDate('processed_at', today())
                    ->count(),
            ],
            'top_sources' => $query->clone()
                ->whereNotNull('source_url')
                ->selectRaw('source_url, COUNT(*) as count')
                ->groupBy('source_url')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json([
            'stats' => $stats,
            'generated_at' => now()->toISOString()
        ]);
    }
}