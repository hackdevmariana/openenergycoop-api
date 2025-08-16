<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\OrganizationFeatureResource;
use App\Models\OrganizationFeature;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Organization Features',
    description: 'Gestión de características organizacionales'
)]
class OrganizationFeatureController extends Controller
{
    #[OA\Get(
        path: '/api/v1/organization-features',
        tags: ['Organization Features'],
        summary: 'Listar características organizacionales',
        description: 'Obtiene todas las características disponibles para organizaciones',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'organization_id',
                in: 'query',
                description: 'Filtrar por organización',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'feature_key',
                in: 'query',
                description: 'Filtrar por clave de característica',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'enabled_dashboard',
                in: 'query',
                description: 'Filtrar por estado en dashboard',
                required: false,
                schema: new OA\Schema(type: 'boolean')
            ),
            new OA\Parameter(
                name: 'enabled_web',
                in: 'query',
                description: 'Filtrar por estado en web',
                required: false,
                schema: new OA\Schema(type: 'boolean')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de características obtenida exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/OrganizationFeatureResource'))
                    ]
                )
            )
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = OrganizationFeature::query()
            ->with(['organization'])
            ->orderBy('name');

        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        if ($request->filled('feature_key')) {
            $query->where('feature_key', $request->feature_key);
        }

        if ($request->has('enabled_dashboard')) {
            $query->where('enabled_dashboard', $request->boolean('enabled_dashboard'));
        }

        if ($request->has('enabled_web')) {
            $query->where('enabled_web', $request->boolean('enabled_web'));
        }

        $features = $query->get();
        return OrganizationFeatureResource::collection($features);
    }

    #[OA\Post(
        path: '/api/v1/organization-features',
        tags: ['Organization Features'],
        summary: 'Crear nueva característica organizacional',
        description: 'Registra una nueva característica para una organización',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['feature_key', 'organization_id'],
                properties: [
                    new OA\Property(property: 'feature_key', type: 'string', maxLength: 255, example: 'energy_management'),
                    new OA\Property(property: 'organization_id', type: 'integer', example: 1),
                    new OA\Property(property: 'enabled_dashboard', type: 'boolean', example: true),
                    new OA\Property(property: 'enabled_web', type: 'boolean', example: true),
                    new OA\Property(property: 'notes', type: 'string', maxLength: 1000, nullable: true, example: 'Feature enabled for solar panel management')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Característica creada exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/OrganizationFeatureResource'),
                        new OA\Property(property: 'message', type: 'string', example: 'Característica creada exitosamente')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Errores de validación')
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        if (!auth()->user()->can('manage organizations')) {
            abort(403, 'No tienes permisos para crear características organizacionales');
        }

        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'feature_key' => 'required|string|max:255',
            'enabled_dashboard' => 'boolean',
            'enabled_web' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $feature = OrganizationFeature::create($validated);

        return response()->json([
            'data' => new OrganizationFeatureResource($feature),
            'message' => 'Característica creada exitosamente'
        ], 201);
    }

    #[OA\Get(
        path: '/api/v1/organization-features/{organization_feature}',
        tags: ['Organization Features'],
        summary: 'Ver característica organizacional específica',
        description: 'Obtiene los detalles de una característica organizacional específica',
        parameters: [
            new OA\Parameter(
                name: 'organization_feature',
                in: 'path',
                description: 'ID de la característica organizacional',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Característica obtenida exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/OrganizationFeatureResource')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Característica no encontrada')
        ]
    )]
    public function show(OrganizationFeature $organizationFeature): JsonResponse
    {
        $organizationFeature->load(['organization']);

        return response()->json([
            'data' => new OrganizationFeatureResource($organizationFeature)
        ]);
    }

    #[OA\Put(
        path: '/api/v1/organization-features/{organization_feature}',
        tags: ['Organization Features'],
        summary: 'Actualizar característica organizacional',
        description: 'Actualiza una característica organizacional existente',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'organization_feature',
                in: 'path',
                description: 'ID de la característica organizacional',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'enabled_dashboard', type: 'boolean', example: false),
                    new OA\Property(property: 'enabled_web', type: 'boolean', example: true),
                    new OA\Property(property: 'notes', type: 'string', maxLength: 1000, nullable: true, example: 'Updated feature configuration')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Característica actualizada exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/OrganizationFeatureResource'),
                        new OA\Property(property: 'message', type: 'string', example: 'Característica actualizada exitosamente')
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Sin permisos para actualizar características'),
            new OA\Response(response: 404, description: 'Característica no encontrada'),
            new OA\Response(response: 422, description: 'Errores de validación')
        ]
    )]
    public function update(Request $request, OrganizationFeature $organizationFeature): JsonResponse
    {
        if (!auth()->user()->can('manage organizations')) {
            abort(403, 'No tienes permisos para actualizar características organizacionales');
        }

        $validated = $request->validate([
            'enabled_dashboard' => 'boolean',
            'enabled_web' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $organizationFeature->update($validated);

        return response()->json([
            'data' => new OrganizationFeatureResource($organizationFeature),
            'message' => 'Característica actualizada exitosamente'
        ]);
    }

    #[OA\Delete(
        path: '/api/v1/organization-features/{organization_feature}',
        tags: ['Organization Features'],
        summary: 'Eliminar característica organizacional',
        description: 'Elimina permanentemente una característica organizacional',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'organization_feature',
                in: 'path',
                description: 'ID de la característica organizacional',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Característica eliminada exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Característica eliminada exitosamente')
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Sin permisos para eliminar características'),
            new OA\Response(response: 404, description: 'Característica no encontrada')
        ]
    )]
    public function destroy(OrganizationFeature $organizationFeature): JsonResponse
    {
        if (!auth()->user()->can('manage organizations')) {
            abort(403, 'No tienes permisos para eliminar características organizacionales');
        }

        $organizationFeature->delete();

        return response()->json([
            'message' => 'Característica eliminada exitosamente'
        ]);
    }
}