<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SubscriptionRequest\StoreSubscriptionRequestRequest;
use App\Http\Requests\Api\V1\SubscriptionRequest\UpdateSubscriptionRequestRequest;
use App\Http\Resources\Api\V1\SubscriptionRequestResource;
use App\Models\SubscriptionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="SubscriptionRequests",
 *     description="API Endpoints para la gestión de solicitudes de suscripción"
 * )
 */
class SubscriptionRequestController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/subscription-requests",
     *     summary="Listar todas las solicitudes de suscripción",
     *     description="Retorna una lista paginada de todas las solicitudes de suscripción",
     *     operationId="getSubscriptionRequests",
     *     tags={"SubscriptionRequests"},
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
     *         @OA\Schema(type="string", enum={"pending", "approved", "rejected", "in_review"})
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrar por tipo",
     *         required=false,
     *         @OA\Schema(type="string", enum={"new_subscription", "ownership_change", "tenant_request"})
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filtrar por usuario",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="cooperative_id",
     *         in="query",
     *         description="Filtrar por cooperativa",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de solicitudes de suscripción",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SubscriptionRequest")),
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
        $this->authorize('viewAny', SubscriptionRequest::class);

        $query = SubscriptionRequest::with(['user', 'cooperative']);

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }

        if ($request->has('cooperative_id')) {
            $query->where('cooperative_id', $request->get('cooperative_id'));
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('notes', 'like', "%{$search}%");
        }

        $subscriptionRequests = $query->paginate($request->get('per_page', 15));

        return SubscriptionRequestResource::collection($subscriptionRequests);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/subscription-requests",
     *     summary="Crear una nueva solicitud de suscripción",
     *     description="Crea una nueva solicitud de suscripción con los datos proporcionados",
     *     operationId="storeSubscriptionRequest",
     *     tags={"SubscriptionRequests"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreSubscriptionRequestRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Solicitud de suscripción creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/SubscriptionRequest"),
     *             @OA\Property(property="message", type="string", example="Solicitud de suscripción creada exitosamente")
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
    public function store(StoreSubscriptionRequestRequest $request): JsonResponse
    {
        $this->authorize('create', SubscriptionRequest::class);

        $data = $request->validated();
        $data['submitted_at'] = now();

        $subscriptionRequest = SubscriptionRequest::create($data);

        return response()->json([
            'data' => new SubscriptionRequestResource($subscriptionRequest),
            'message' => 'Solicitud de suscripción creada exitosamente'
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/subscription-requests/{id}",
     *     summary="Obtener una solicitud de suscripción específica",
     *     description="Retorna los datos de una solicitud de suscripción específica por su ID",
     *     operationId="showSubscriptionRequest",
     *     tags={"SubscriptionRequests"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la solicitud de suscripción",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos de la solicitud de suscripción",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/SubscriptionRequest")
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
     *         description="Solicitud de suscripción no encontrada"
     *     )
     * )
     */
    public function show(SubscriptionRequest $subscriptionRequest): JsonResponse
    {
        $this->authorize('view', $subscriptionRequest);

        return response()->json([
            'data' => new SubscriptionRequestResource($subscriptionRequest)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/subscription-requests/{id}",
     *     summary="Actualizar una solicitud de suscripción",
     *     description="Actualiza los datos de una solicitud de suscripción existente",
     *     operationId="updateSubscriptionRequest",
     *     tags={"SubscriptionRequests"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la solicitud de suscripción",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateSubscriptionRequestRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Solicitud de suscripción actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/SubscriptionRequest"),
     *             @OA\Property(property="message", type="string", example="Solicitud de suscripción actualizada exitosamente")
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
     *         description="Solicitud de suscripción no encontrada"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function update(UpdateSubscriptionRequestRequest $request, SubscriptionRequest $subscriptionRequest): JsonResponse
    {
        $this->authorize('update', $subscriptionRequest);

        $subscriptionRequest->update($request->validated());

        return response()->json([
            'data' => new SubscriptionRequestResource($subscriptionRequest),
            'message' => 'Solicitud de suscripción actualizada exitosamente'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/subscription-requests/{id}",
     *     summary="Eliminar una solicitud de suscripción",
     *     description="Elimina una solicitud de suscripción existente",
     *     operationId="destroySubscriptionRequest",
     *     tags={"SubscriptionRequests"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la solicitud de suscripción",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Solicitud de suscripción eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Solicitud de suscripción eliminada exitosamente")
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
     *         description="Solicitud de suscripción no encontrada"
     *     )
     * )
     */
    public function destroy(SubscriptionRequest $subscriptionRequest): JsonResponse
    {
        $this->authorize('delete', $subscriptionRequest);

        $subscriptionRequest->delete();

        return response()->json([
            'message' => 'Solicitud de suscripción eliminada exitosamente'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/subscription-requests/{id}/approve",
     *     summary="Aprobar una solicitud de suscripción",
     *     description="Aprueba una solicitud de suscripción pendiente",
     *     operationId="approveSubscriptionRequest",
     *     tags={"SubscriptionRequests"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la solicitud de suscripción",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Solicitud de suscripción aprobada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/SubscriptionRequest"),
     *             @OA\Property(property="message", type="string", example="Solicitud de suscripción aprobada exitosamente")
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
     *         description="Solicitud de suscripción no encontrada"
     *     )
     * )
     */
    public function approve(int $id): JsonResponse
    {
        // Buscar el modelo por ID
        $subscriptionRequest = SubscriptionRequest::findOrFail($id);
        
        $this->authorize('update', $subscriptionRequest);

        // Actualizar directamente en la base de datos
        $result = $subscriptionRequest->update([
            'status' => SubscriptionRequest::STATUS_APPROVED,
            'processed_at' => now(),
        ]);

        if (!$result) {
            return response()->json([
                'message' => 'Error al actualizar la solicitud'
            ], 500);
        }

        // Buscar el modelo actualizado desde la base de datos
        $updatedSubscriptionRequest = SubscriptionRequest::find($subscriptionRequest->id);
        
        if (!$updatedSubscriptionRequest) {
            return response()->json([
                'message' => 'Error al recuperar la solicitud actualizada'
            ], 500);
        }

        return response()->json([
            'data' => new SubscriptionRequestResource($updatedSubscriptionRequest),
            'message' => 'Solicitud de suscripción aprobada exitosamente'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/subscription-requests/{id}/reject",
     *     summary="Rechazar una solicitud de suscripción",
     *     description="Rechaza una solicitud de suscripción pendiente",
     *     operationId="rejectSubscriptionRequest",
     *     tags={"SubscriptionRequests"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la solicitud de suscripción",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Solicitud de suscripción rechazada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/SubscriptionRequest"),
     *             @OA\Property(property="message", type="string", example="Solicitud de suscripción rechazada exitosamente")
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
     *         description="Solicitud de suscripción no encontrada"
     *     )
     * )
     */
    public function reject(int $id): JsonResponse
    {
        // Buscar el modelo por ID
        $subscriptionRequest = SubscriptionRequest::findOrFail($id);
        
        $this->authorize('update', $subscriptionRequest);

        // Actualizar directamente en la base de datos
        $result = $subscriptionRequest->update([
            'status' => SubscriptionRequest::STATUS_REJECTED,
            'processed_at' => now(),
        ]);

        if (!$result) {
            return response()->json([
                'message' => 'Error al actualizar la solicitud'
            ], 500);
        }

        // Buscar el modelo actualizado desde la base de datos
        $updatedSubscriptionRequest = SubscriptionRequest::find($subscriptionRequest->id);
        
        if (!$updatedSubscriptionRequest) {
            return response()->json([
                'message' => 'Error al recuperar la solicitud actualizada'
            ], 500);
        }

        return response()->json([
            'data' => new SubscriptionRequestResource($updatedSubscriptionRequest),
            'message' => 'Solicitud de suscripción rechazada exitosamente'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/subscription-requests/{id}/review",
     *     summary="Poner en revisión una solicitud de suscripción",
     *     description="Pone en revisión una solicitud de suscripción pendiente",
     *     operationId="reviewSubscriptionRequest",
     *     tags={"SubscriptionRequests"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la solicitud de suscripción",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Solicitud de suscripción puesta en revisión exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/SubscriptionRequest"),
     *             @OA\Property(property="message", example="Solicitud de suscripción puesta en revisión exitosamente")
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
     *         description="Solicitud de suscripción no encontrada"
     *     )
     * )
     */
    public function review(int $id): JsonResponse
    {
        // Buscar el modelo por ID
        $subscriptionRequest = SubscriptionRequest::findOrFail($id);
        
        $this->authorize('update', $subscriptionRequest);

        // Actualizar directamente en la base de datos
        $result = $subscriptionRequest->update([
            'status' => SubscriptionRequest::STATUS_IN_REVIEW,
        ]);

        if (!$result) {
            return response()->json([
                'message' => 'Error al actualizar la solicitud'
            ], 500);
        }

        // Buscar el modelo actualizado desde la base de datos
        $updatedSubscriptionRequest = SubscriptionRequest::find($subscriptionRequest->id);
        
        if (!$updatedSubscriptionRequest) {
            return response()->json([
                'message' => 'Error al recuperar la solicitud actualizada'
            ], 500);
        }

        return response()->json([
            'data' => new SubscriptionRequestResource($updatedSubscriptionRequest),
            'message' => 'Solicitud de suscripción puesta en revisión exitosamente'
        ]);
    }
}
