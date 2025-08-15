<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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
        if (!auth()->user()->can('manage forms')) {
            abort(403, 'No tienes permisos para ver envíos de formularios');
        }

        $query = FormSubmission::query()
            ->with(['user', 'assignedTo', 'processedBy'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('form_type')) {
            $query->byFormType($request->form_type);
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
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'form_name' => 'required|string|max:255',
            'form_type' => 'required|string|max:100',
            'form_data' => 'required|array',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'source_page' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id() ?? null;
        $validated['ip_address'] = $request->ip();
        $validated['user_agent'] = $request->userAgent();
        $validated['referrer'] = $request->header('referer');

        $submission = FormSubmission::create($validated);

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
        if (!auth()->user()->can('manage forms')) {
            abort(403, 'No tienes permisos para ver este envío');
        }

        $formSubmission->load(['user', 'assignedTo', 'processedBy']);

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
    public function update(Request $request, FormSubmission $formSubmission): JsonResponse
    {
        if (!auth()->user()->can('manage forms')) {
            abort(403, 'No tienes permisos para actualizar este envío');
        }

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,reviewed,processed,rejected',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'internal_notes' => 'nullable|string',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'processed') {
            $validated['processed_at'] = now();
            $validated['processed_by_user_id'] = auth()->id();
        }

        $formSubmission->update($validated);

        return response()->json([
            'data' => new FormSubmissionResource($formSubmission->fresh()),
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
        if (!auth()->user()->can('manage forms')) {
            abort(403, 'No tienes permisos para eliminar este envío');
        }

        $formSubmission->delete();

        return response()->json([
            'message' => 'Envío eliminado exitosamente'
        ]);
    }
}