<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Notification\StoreNotificationRequest;
use App\Http\Requests\Api\V1\Notification\UpdateNotificationRequest;
use App\Http\Resources\Api\V1\Notification\NotificationCollection;
use App\Http\Resources\Api\V1\Notification\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Notifications",
 *     description="API Endpoints para gestión de notificaciones"
 * )
 */
class NotificationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/notifications",
     *     summary="Listar notificaciones",
     *     description="Obtiene una lista paginada de notificaciones con filtros y búsqueda",
     *     tags={"Notifications"},
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
     *         name="search",
     *         in="query",
     *         description="Término de búsqueda en título y mensaje",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrar por tipo de notificación",
     *         required=false,
     *         @OA\Schema(type="string", enum={"info", "alert", "success", "warning", "error"})
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filtrar por usuario",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="is_read",
     *         in="query",
     *         description="Filtrar por estado de lectura",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="is_delivered",
     *         in="query",
     *         description="Filtrar por estado de entrega",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Campo para ordenar",
     *         required=false,
     *         @OA\Schema(type="string", default="created_at")
     *     ),
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         description="Dirección del ordenamiento",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de notificaciones obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notificaciones obtenidas exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/NotificationCollection")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Notification::with(['user:id,name,email']);

            // Filtros
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->filled('is_read')) {
                if ($request->boolean('is_read')) {
                    $query->whereNotNull('read_at');
                } else {
                    $query->whereNull('read_at');
                }
            }

            if ($request->filled('is_delivered')) {
                if ($request->boolean('is_delivered')) {
                    $query->whereNotNull('delivered_at');
                } else {
                    $query->whereNull('delivered_at');
                }
            }

            // Búsqueda
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('message', 'like', "%{$search}%");
                });
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $query->orderBy($sortBy, $sortDirection);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $notifications = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Notificaciones obtenidas exitosamente',
                'data' => new NotificationCollection($notifications)
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener notificaciones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener notificaciones',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/notifications",
     *     summary="Crear notificación",
     *     description="Crea una nueva notificación en el sistema",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreNotificationRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Notificación creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notificación creada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/NotificationResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Datos de validación inválidos"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function store(StoreNotificationRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $notification = Notification::create($request->validated());

            // Marcar como entregada automáticamente si no se especifica
            if (!$request->filled('delivered_at')) {
                $notification->markAsDelivered();
            }

            DB::commit();

            Log::info("Notificación creada: ID {$notification->id} para usuario {$notification->user_id}");

            return response()->json([
                'success' => true,
                'message' => 'Notificación creada exitosamente',
                'data' => new NotificationResource($notification->load('user'))
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear notificación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear notificación',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/notifications/{id}",
     *     summary="Obtener notificación",
     *     description="Obtiene los detalles de una notificación específica",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la notificación",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notificación obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notificación obtenida exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/NotificationResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notificación no encontrada"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function show(Notification $notification): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Notificación obtenida exitosamente',
                'data' => new NotificationResource($notification->load('user'))
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener notificación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener notificación',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/notifications/{id}",
     *     summary="Actualizar notificación",
     *     description="Actualiza una notificación existente",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la notificación",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateNotificationRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notificación actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notificación actualizada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/NotificationResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notificación no encontrada"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Datos de validación inválidos"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function update(UpdateNotificationRequest $request, Notification $notification): JsonResponse
    {
        try {
            DB::beginTransaction();

            $notification->update($request->validated());

            // Si se marca como leída pero no tiene read_at, establecerlo
            if ($request->boolean('is_read') && !$notification->read_at) {
                $notification->markAsRead();
            }

            // Si se desmarca como leída, limpiar read_at
            if (!$request->boolean('is_read') && $notification->read_at) {
                $notification->update(['read_at' => null]);
            }

            DB::commit();

            Log::info("Notificación actualizada: ID {$notification->id}");

            return response()->json([
                'success' => true,
                'message' => 'Notificación actualizada exitosamente',
                'data' => new NotificationResource($notification->load('user'))
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar notificación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar notificación',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/notifications/{id}",
     *     summary="Eliminar notificación",
     *     description="Elimina una notificación del sistema (soft delete)",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la notificación",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notificación eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notificación eliminada exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notificación no encontrada"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function destroy(Notification $notification): JsonResponse
    {
        try {
            DB::beginTransaction();

            $notificationId = $notification->id;
            $notification->delete();

            DB::commit();

            Log::info("Notificación eliminada: ID {$notificationId}");

            return response()->json([
                'success' => true,
                'message' => 'Notificación eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar notificación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar notificación',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/notifications/statistics",
     *     summary="Estadísticas de notificaciones",
     *     description="Obtiene estadísticas generales de notificaciones",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="ID del usuario para estadísticas específicas",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Estadísticas obtenidas exitosamente"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total", type="integer", example=150),
     *                 @OA\Property(property="unread", type="integer", example=45),
     *                 @OA\Property(property="read", type="integer", example=105),
     *                 @OA\Property(property="by_type", type="object"),
     *                 @OA\Property(property="recent", type="integer", example=12),
     *                 @OA\Property(property="delivered", type="integer", example=140)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $userId = $request->get('user_id');
            $stats = $userId ? Notification::getUserStats($userId) : Notification::getStats();

            return response()->json([
                'success' => true,
                'message' => 'Estadísticas obtenidas exitosamente',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/notifications/types",
     *     summary="Tipos de notificación",
     *     description="Obtiene la lista de tipos de notificación disponibles",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Tipos obtenidos exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tipos obtenidos exitosamente"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function types(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Tipos obtenidos exitosamente',
            'data' => Notification::TYPES
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/notifications/{id}/mark-read",
     *     summary="Marcar como leída",
     *     description="Marca una notificación como leída",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la notificación",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notificación marcada como leída exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notificación marcada como leída")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notificación no encontrada"
     *     )
     * )
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        try {
            $notification->markAsRead();

            Log::info("Notificación marcada como leída: ID {$notification->id}");

            return response()->json([
                'success' => true,
                'message' => 'Notificación marcada como leída'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al marcar notificación como leída: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar notificación como leída'
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/notifications/{id}/mark-delivered",
     *     summary="Marcar como entregada",
     *     description="Marca una notificación como entregada",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la notificación",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notificación marcada como entregada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notificación marcada como entregada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notificación no encontrada"
     *     )
     * )
     */
    public function markAsDelivered(Notification $notification): JsonResponse
    {
        try {
            $notification->markAsDelivered();

            Log::info("Notificación marcada como entregada: ID {$notification->id}");

            return response()->json([
                'success' => true,
                'message' => 'Notificación marcada como entregada'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al marcar notificación como entregada: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar notificación como entregada'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/notifications/unread",
     *     summary="Notificaciones no leídas",
     *     description="Obtiene las notificaciones no leídas del usuario autenticado",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notificaciones no leídas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notificaciones no leídas obtenidas exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/NotificationCollection")
     *         )
     *     )
     * )
     */
    public function unread(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $notifications = auth()->user()->unreadNotifications()->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Notificaciones no leídas obtenidas exitosamente',
                'data' => new NotificationCollection($notifications)
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener notificaciones no leídas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener notificaciones no leídas'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/notifications/recent",
     *     summary="Notificaciones recientes",
     *     description="Obtiene las notificaciones recientes del usuario autenticado",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="days",
     *         in="query",
     *         description="Número de días para considerar recientes",
     *         required=false,
     *         @OA\Schema(type="integer", default=7)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notificaciones recientes obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notificaciones recientes obtenidas exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/NotificationCollection")
     *         )
     *     )
     * )
     */
    public function recent(Request $request): JsonResponse
    {
        try {
            $days = $request->get('days', 7);
            $notifications = auth()->user()->recentNotifications($days)->paginate(15);

            return response()->json([
                'success' => true,
                'message' => 'Notificaciones recientes obtenidas exitosamente',
                'data' => new NotificationCollection($notifications)
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener notificaciones recientes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener notificaciones recientes'
            ], 500);
        }
    }
}
