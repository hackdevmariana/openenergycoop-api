<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UserOrganizationRole\StoreUserOrganizationRoleRequest;
use App\Http\Requests\Api\V1\UserOrganizationRole\UpdateUserOrganizationRoleRequest;
use App\Http\Resources\Api\V1\UserOrganizationRoleResource;
use App\Models\UserOrganizationRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="UserOrganizationRoles",
 *     description="API Endpoints para la gestión de asignaciones de roles de usuario en organizaciones"
 * )
 */
class UserOrganizationRoleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/user-organization-roles",
     *     summary="Listar todas las asignaciones de roles de usuario",
     *     description="Retorna una lista paginada de todas las asignaciones de roles de usuario en organizaciones",
     *     operationId="getUserOrganizationRoles",
     *     tags={"UserOrganizationRoles"},
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
     *         name="user_id",
     *         in="query",
     *         description="Filtrar por usuario",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="organization_id",
     *         in="query",
     *         description="Filtrar por organización",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="organization_role_id",
     *         in="query",
     *         description="Filtrar por rol de organización",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de asignaciones de roles de usuario",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/UserOrganizationRole")),
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
        $this->authorize('viewAny', UserOrganizationRole::class);

        $query = UserOrganizationRole::with(['user', 'organization', 'organizationRole']);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }

        if ($request->has('organization_id')) {
            $query->where('organization_id', $request->get('organization_id'));
        }

        if ($request->has('organization_role_id')) {
            $query->where('organization_role_id', $request->get('organization_role_id'));
        }

        $userOrganizationRoles = $query->paginate($request->get('per_page', 15));

        return UserOrganizationRoleResource::collection($userOrganizationRoles);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user-organization-roles",
     *     summary="Crear una nueva asignación de rol de usuario",
     *     description="Crea una nueva asignación de rol de usuario en una organización",
     *     operationId="storeUserOrganizationRole",
     *     tags={"UserOrganizationRoles"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreUserOrganizationRoleRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Asignación de rol de usuario creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/UserOrganizationRole"),
     *             @OA\Property(property="message", type="string", example="Asignación de rol de usuario creada exitosamente")
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
    public function store(StoreUserOrganizationRoleRequest $request): JsonResponse
    {
        $this->authorize('create', UserOrganizationRole::class);

        $data = $request->validated();
        $data['assigned_at'] = now();

        $userOrganizationRole = UserOrganizationRole::create($data);

        return response()->json([
            'data' => new UserOrganizationRoleResource($userOrganizationRole),
            'message' => 'Asignación de rol de usuario creada exitosamente'
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user-organization-roles/{id}",
     *     summary="Obtener una asignación de rol de usuario específica",
     *     description="Retorna los datos de una asignación de rol de usuario específica por su ID",
     *     operationId="showUserOrganizationRole",
     *     tags={"UserOrganizationRoles"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la asignación de rol de usuario",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos de la asignación de rol de usuario",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/UserOrganizationRole")
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
     *         description="Asignación de rol de usuario no encontrada"
     *     )
     * )
     */
    public function show(UserOrganizationRole $userOrganizationRole): JsonResponse
    {
        $this->authorize('view', $userOrganizationRole);

        return response()->json([
            'data' => new UserOrganizationRoleResource($userOrganizationRole)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/user-organization-roles/{id}",
     *     summary="Actualizar una asignación de rol de usuario",
     *     description="Actualiza los datos de una asignación de rol de usuario existente",
     *     operationId="updateUserOrganizationRole",
     *     tags={"UserOrganizationRoles"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la asignación de rol de usuario",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateUserOrganizationRoleRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Asignación de rol de usuario actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/UserOrganizationRole"),
     *             @OA\Property(property="message", type="string", example="Asignación de rol de usuario actualizada exitosamente")
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
     *         response=403",
     *         description="No autorizado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Asignación de rol de usuario no encontrada"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function update(UpdateUserOrganizationRoleRequest $request, UserOrganizationRole $userOrganizationRole): JsonResponse
    {
        $this->authorize('update', $userOrganizationRole);

        $userOrganizationRole->update($request->validated());

        return response()->json([
            'data' => new UserOrganizationRoleResource($userOrganizationRole),
            'message' => 'Asignación de rol de usuario actualizada exitosamente'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/user-organization-roles/{id}",
     *     summary="Eliminar una asignación de rol de usuario",
     *     description="Elimina una asignación de rol de usuario existente",
     *     operationId="destroyUserOrganizationRole",
     *     tags={"UserOrganizationRoles"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la asignación de rol de usuario",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Asignación de rol de usuario eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Asignación de rol de usuario eliminada exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     ),
     *     @OA\Response(
     *         response=403",
     *         description="No autorizado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Asignación de rol de usuario no encontrada"
     *     )
     * )
     */
    public function destroy(UserOrganizationRole $userOrganizationRole): JsonResponse
    {
        $this->authorize('delete', $userOrganizationRole);

        $userOrganizationRole->delete();

        return response()->json([
            'message' => 'Asignación de rol de usuario eliminada exitosamente'
        ]);
    }
}
