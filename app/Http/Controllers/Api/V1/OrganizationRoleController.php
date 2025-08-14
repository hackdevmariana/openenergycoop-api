<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\OrganizationRole\StoreOrganizationRoleRequest;
use App\Http\Requests\Api\V1\OrganizationRole\UpdateOrganizationRoleRequest;
use App\Http\Resources\Api\V1\OrganizationRoleResource;
use App\Models\OrganizationRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="OrganizationRoles",
 *     description="API Endpoints para la gestión de roles de organización"
 * )
 */
class OrganizationRoleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/organization-roles",
     *     summary="Listar todos los roles de organización",
     *     description="Retorna una lista paginada de todos los roles de organización",
     *     operationId="getOrganizationRoles",
     *     tags={"OrganizationRoles"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Elementos por página",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="organization_id",
     *         in="query",
     *         description="Filtrar por organización",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Término de búsqueda",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de roles de organización",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/OrganizationRole")),
     *             @OA\Property(property="links", ref="#/components/schemas/Links"),
     *             @OA\Property(property="meta", ref="#/components/schemas/Meta")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No autorizado"
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', OrganizationRole::class);

        $query = OrganizationRole::with(['organization']);

        if ($request->has('organization_id')) {
            $query->where('organization_id', $request->get('organization_id'));
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $organizationRoles = $query->paginate($request->get('per_page', 15));

        return OrganizationRoleResource::collection($organizationRoles);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/organization-roles",
     *     summary="Crear un nuevo rol de organización",
     *     description="Crea un nuevo rol de organización con los datos proporcionados",
     *     operationId="storeOrganizationRole",
     *     tags={"OrganizationRoles"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreOrganizationRoleRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Rol de organización creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/OrganizationRole"),
     *             @OA\Property(property="message", type="string", example="Rol de organización creado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Datos de entrada inválidos"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No autorizado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function store(StoreOrganizationRoleRequest $request): JsonResponse
    {
        $this->authorize('create', OrganizationRole::class);

        $organizationRole = OrganizationRole::create($request->validated());

        return response()->json([
            'data' => new OrganizationRoleResource($organizationRole),
            'message' => 'Rol de organización creado exitosamente'
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/organization-roles/{id}",
     *     summary="Obtener un rol de organización específico",
     *     description="Retorna los datos de un rol de organización específico por su ID",
     *     operationId="showOrganizationRole",
     *     tags={"OrganizationRoles"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del rol de organización",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos del rol de organización",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/OrganizationRole")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No autorizado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Rol de organización no encontrado"
     *     )
     * )
     */
    public function show(OrganizationRole $organizationRole): JsonResponse
    {
        $this->authorize('view', $organizationRole);

        return response()->json([
            'data' => new OrganizationRoleResource($organizationRole)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/organization-roles/{id}",
     *     summary="Actualizar un rol de organización",
     *     description="Actualiza los datos de un rol de organización existente",
     *     operationId="updateOrganizationRole",
     *     tags={"OrganizationRoles"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del rol de organización",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateOrganizationRoleRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rol de organización actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/OrganizationRole"),
     *             @OA\Property(property="message", type="string", example="Rol de organización actualizado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Datos de entrada inválidos"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No autorizado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Rol de organización no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function update(UpdateOrganizationRoleRequest $request, OrganizationRole $organizationRole): JsonResponse
    {
        $this->authorize('update', $organizationRole);

        $organizationRole->update($request->validated());

        return response()->json([
            'data' => new OrganizationRoleResource($organizationRole),
            'message' => 'Rol de organización actualizado exitosamente'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/organization-roles/{id}",
     *     summary="Eliminar un rol de organización",
     *     description="Elimina un rol de organización existente",
     *     operationId="destroyOrganizationRole",
     *     tags={"OrganizationRoles"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del rol de organización",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rol de organización eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Rol de organización eliminado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No autorizado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Rol de organización no encontrado"
     *     )
     * )
     */
    public function destroy(OrganizationRole $organizationRole): JsonResponse
    {
        $this->authorize('delete', $organizationRole);

        $organizationRole->delete();

        return response()->json([
            'message' => 'Rol de organización eliminado exitosamente'
        ]);
    }
}
