<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Organization\StoreOrganizationRequest;
use App\Http\Requests\Api\V1\Organization\UpdateOrganizationRequest;
use App\Http\Resources\Api\V1\OrganizationResource;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Organizations",
 *     description="Gestión de organizaciones del sistema"
 * )
 */
class OrganizationController extends \App\Http\Controllers\Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/organizations",
     *     tags={"Organizations"},
     *     summary="Listar organizaciones",
     *     description="Obtiene una lista paginada de organizaciones",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Buscar por nombre",
     *         required=false,
     *         @OA\Schema(type="string", example="Cooperativa")
     *     ),
     *     @OA\Parameter(
     *         name="active",
     *         in="query",
     *         description="Filtrar por estado activo",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Elementos por página (máximo 50)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=50, example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de organizaciones obtenida exitosamente"
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Organization::query()
            ->with(['features'])
            ->orderBy('name');

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $perPage = min($request->get('per_page', 15), 50);
        $organizations = $query->paginate($perPage);

        return OrganizationResource::collection($organizations);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/organizations",
     *     tags={"Organizations"},
     *     summary="Crear nueva organización",
     *     description="Crea una nueva organización en el sistema",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="Cooperativa de Energía Verde"),
     *             @OA\Property(property="description", type="string", example="Descripción de la organización"),
     *             @OA\Property(property="logo", type="string", example="logos/organization-logo.png"),
     *             @OA\Property(property="website", type="string", format="url", example="https://cooperativa.com"),
     *             @OA\Property(property="email", type="string", format="email", example="info@cooperativa.com"),
     *             @OA\Property(property="phone", type="string", example="+34 123 456 789"),
     *             @OA\Property(property="address", type="string", example="Calle Principal 123"),
     *             @OA\Property(property="city", type="string", example="Madrid"),
     *             @OA\Property(property="postal_code", type="string", example="28001"),
     *             @OA\Property(property="country", type="string", example="España"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Organización creada exitosamente"
     *     )
     * )
     */
    public function store(StoreOrganizationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $organization = Organization::create($validated);
        $organization->load(['features']);

        return response()->json([
            'data' => new OrganizationResource($organization),
            'message' => 'Organización creada exitosamente'
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/organizations/{id}",
     *     tags={"Organizations"},
     *     summary="Obtener organización específica",
     *     description="Obtiene los detalles de una organización específica",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la organización",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="include_stats",
     *         in="query",
     *         description="Incluir estadísticas de la organización",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Organización obtenida exitosamente"
     *     )
     * )
     */
    public function show(Request $request, Organization $organization): JsonResponse
    {
        if (!$organization->active) {
            return response()->json(['message' => 'Organización no encontrada'], 404);
        }

        $organization->load(['features']);

        $data = ['data' => new OrganizationResource($organization)];

        // Incluir estadísticas si se solicita
        if ($request->boolean('include_stats')) {
            $data['stats'] = [
                'total_users' => $organization->users()->count(),
                'total_teams' => $organization->teams()->count(),
                'total_articles' => $organization->articles()->count(),
                'total_pages' => $organization->pages()->count(),
            ];
        }

        return response()->json($data);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/organizations/{id}",
     *     tags={"Organizations"},
     *     summary="Actualizar organización",
     *     description="Actualiza una organización existente",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la organización",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="logo", type="string"),
     *             @OA\Property(property="website", type="string", format="url"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="is_active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Organización actualizada exitosamente"
     *     )
     * )
     */
    public function update(UpdateOrganizationRequest $request, Organization $organization): JsonResponse
    {
        $organization->update($request->validated());
        $organization->load(['features']);

        return response()->json([
            'data' => new OrganizationResource($organization),
            'message' => 'Organización actualizada exitosamente'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/organizations/{id}",
     *     tags={"Organizations"},
     *     summary="Eliminar organización",
     *     description="Elimina una organización del sistema",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la organización",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Organización eliminada exitosamente"
     *     )
     * )
     */
    public function destroy(Organization $organization): JsonResponse
    {
        // Verificar si tiene dependencias
        if ($organization->users()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar una organización que tiene usuarios asociados'
            ], 422);
        }

        $organization->delete();

        return response()->json([
            'message' => 'Organización eliminada exitosamente'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/organizations/{id}/stats",
     *     tags={"Organizations"},
     *     summary="Obtener estadísticas de organización",
     *     description="Obtiene estadísticas detalladas de una organización",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la organización",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente"
     *     )
     * )
     */
    public function stats(Organization $organization): JsonResponse
    {
        $stats = [
            'users' => [
                'total' => $organization->users()->count(),
                'active' => $organization->users()->whereNotNull('email_verified_at')->count(),
                'new_this_month' => $organization->users()->where('created_at', '>=', now()->startOfMonth())->count(),
            ],
            'teams' => [
                'total' => $organization->teams()->count(),
                'active' => $organization->teams()->where('is_active', true)->count(),
            ],
            'content' => [
                'articles' => $organization->articles()->count(),
                'published_articles' => $organization->articles()->where('status', 'published')->count(),
                'pages' => $organization->pages()->count(),
                'published_pages' => $organization->pages()->where('is_draft', false)->count(),
            ],
            'engagement' => [
                'total_comments' => $organization->comments()->count(),
                'approved_comments' => $organization->comments()->where('status', 'approved')->count(),
            ]
        ];

        return response()->json([
            'organization' => new OrganizationResource($organization),
            'stats' => $stats,
            'generated_at' => now()->toISOString()
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/organizations/{id}/features",
     *     tags={"Organizations"},
     *     summary="Obtener características de organización",
     *     description="Obtiene las características habilitadas para una organización",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la organización",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Características obtenidas exitosamente"
     *     )
     * )
     */
    public function features(Organization $organization): JsonResponse
    {
        $features = $organization->features()
            ->where('is_enabled', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'organization' => new OrganizationResource($organization),
            'features' => $features->map(function ($feature) {
                return [
                    'id' => $feature->id,
                    'name' => $feature->name,
                    'description' => $feature->description,
                    'is_enabled' => $feature->is_enabled,
                    'config' => $feature->config,
                ];
            }),
            'total_features' => $features->count()
        ]);
    }
}