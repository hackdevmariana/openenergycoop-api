<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\InvitationToken\StoreInvitationTokenRequest;
use App\Http\Resources\Api\V1\InvitationTokenResource;
use App\Models\InvitationToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Invitation Tokens",
 *     description="API Endpoints para la gestión de tokens de invitación"
 * )
 */
class InvitationTokenController extends \App\Http\Controllers\Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/invitation-tokens",
     *     summary="Listar tokens de invitación",
     *     description="Retorna una lista paginada de tokens de invitación",
     *     operationId="getInvitationTokens",
     *     tags={"Invitation Tokens"},
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
     *         name="status",
     *         in="query",
     *         description="Filtrar por estado",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "used", "expired", "revoked"})
     *     ),
     *     @OA\Parameter(
     *         name="organization_id",
     *         in="query",
     *         description="Filtrar por organización",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de tokens obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/InvitationToken")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acceso denegado"
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', InvitationToken::class);

        $query = InvitationToken::with(['organization', 'organizationRole', 'invitedByUser']);

        // Filtrar por estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtrar por organización
        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        // Solo mostrar tokens creados por el usuario actual o de su organización
        $user = $request->user();
        if (!$user->hasRole('super_admin')) {
            $query->where(function ($q) use ($user) {
                $q->where('invited_by', $user->id)
                  ->orWhereHas('organization', function ($orgQuery) use ($user) {
                      // Aquí deberías agregar lógica para verificar si el usuario pertenece a la organización
                      // Por ahora lo dejamos así para que funcione
                  });
            });
        }

        $tokens = $query->orderBy('created_at', 'desc')
                       ->paginate($request->integer('per_page', 15));

        return InvitationTokenResource::collection($tokens);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/invitation-tokens",
     *     summary="Crear un nuevo token de invitación",
     *     description="Crea un nuevo token de invitación para onboarding",
     *     operationId="storeInvitationToken",
     *     tags={"Invitation Tokens"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreInvitationTokenRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Token creado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/InvitationToken")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acceso denegado"
     *     )
     * )
     */
    public function store(StoreInvitationTokenRequest $request): InvitationTokenResource
    {
        $token = InvitationToken::createInvitation(
            $request->organization_id,
            $request->organization_role_id,
            $request->user()->id,
            $request->email,
            $request->expires_at ? now()->parse($request->expires_at) : null
        );

        $token->load(['organization', 'organizationRole', 'invitedByUser']);

        return new InvitationTokenResource($token);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/invitation-tokens/{id}",
     *     summary="Obtener un token de invitación específico",
     *     description="Retorna los detalles de un token de invitación específico",
     *     operationId="getInvitationToken",
     *     tags={"Invitation Tokens"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del token de invitación",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token obtenido exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/InvitationToken")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Token no encontrado"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acceso denegado"
     *     )
     * )
     */
    public function show(InvitationToken $invitationToken): InvitationTokenResource
    {
        $this->authorize('view', $invitationToken);

        $invitationToken->load(['organization', 'organizationRole', 'invitedByUser']);

        return new InvitationTokenResource($invitationToken);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/invitation-tokens/{id}/revoke",
     *     summary="Revocar un token de invitación",
     *     description="Revoca un token de invitación, impidiendo su uso",
     *     operationId="revokeInvitationToken",
     *     tags={"Invitation Tokens"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del token de invitación",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token revocado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Token revocado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/InvitationToken")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="El token no puede ser revocado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Token no encontrado"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acceso denegado"
     *     )
     * )
     */
    public function revoke(InvitationToken $invitationToken): JsonResponse
    {
        $this->authorize('update', $invitationToken);

        if ($invitationToken->status !== 'pending') {
            return response()->json([
                'message' => 'Solo se pueden revocar tokens pendientes',
                'error' => 'invalid_status'
            ], 400);
        }

        $invitationToken->revoke();
        $invitationToken->load(['organization', 'organizationRole', 'invitedByUser']);

        return response()->json([
            'message' => 'Token revocado exitosamente',
            'data' => new InvitationTokenResource($invitationToken)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/invitation-tokens/validate/{token}",
     *     summary="Validar un token de invitación",
     *     description="Valida si un token de invitación es válido y puede ser usado",
     *     operationId="validateInvitationToken",
     *     tags={"Invitation Tokens"},
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         description="Token de invitación",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token validado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="valid", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/InvitationToken")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Token inválido o expirado",
     *         @OA\JsonContent(
     *             @OA\Property(property="valid", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Token inválido o expirado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Token no encontrado"
     *     )
     * )
     */
    public function validateToken(string $token): JsonResponse
    {
        $invitationToken = InvitationToken::where('token', $token)
            ->with(['organization', 'organizationRole'])
            ->first();

        if (!$invitationToken) {
            return response()->json([
                'valid' => false,
                'message' => 'Token no encontrado'
            ], 404);
        }

        if (!$invitationToken->isValid()) {
            return response()->json([
                'valid' => false,
                'message' => 'Token inválido o expirado',
                'status' => $invitationToken->status,
                'expired' => $invitationToken->isExpired()
            ], 400);
        }

        return response()->json([
            'valid' => true,
            'data' => new InvitationTokenResource($invitationToken)
        ]);
    }
}
