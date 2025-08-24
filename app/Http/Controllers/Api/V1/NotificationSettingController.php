<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\NotificationSetting\StoreNotificationSettingRequest;
use App\Http\Requests\Api\V1\NotificationSetting\UpdateNotificationSettingRequest;
use App\Http\Resources\Api\V1\NotificationSetting\NotificationSettingCollection;
use App\Http\Resources\Api\V1\NotificationSetting\NotificationSettingResource;
use App\Models\NotificationSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="NotificationSettings",
 *     description="API Endpoints para gestión de configuraciones de notificaciones"
 * )
 */
class NotificationSettingController extends \App\Http\Controllers\Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/notification-settings",
     *     summary="Listar configuraciones de notificaciones",
     *     description="Obtiene una lista paginada de configuraciones de notificaciones con filtros y búsqueda",
     *     tags={"NotificationSettings"},
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
     *         name="channel",
     *         in="query",
     *         description="Filtrar por canal",
     *         required=false,
     *         @OA\Schema(type="string", enum={"email", "push", "sms", "in_app"})
     *     ),
     *     @OA\Parameter(
     *         name="notification_type",
     *         in="query",
     *         description="Filtrar por tipo de notificación",
     *         required=false,
     *         @OA\Schema(type="string", enum={"wallet", "event", "message", "general"})
     *     ),
     *     @OA\Parameter(
     *         name="enabled",
     *         in="query",
     *         description="Filtrar por estado habilitado",
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
     *         description="Lista de configuraciones obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Configuraciones obtenidas exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/NotificationSettingCollection")
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
            $query = NotificationSetting::with(['user:id,name,email']);

            // Filtros
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->filled('channel')) {
                $query->where('channel', $request->channel);
            }

            if ($request->filled('notification_type')) {
                $query->where('notification_type', $request->notification_type);
            }

            if ($request->filled('enabled')) {
                $query->where('enabled', $request->boolean('enabled'));
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $query->orderBy($sortBy, $sortDirection);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $settings = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Configuraciones obtenidas exitosamente',
                'data' => new NotificationSettingCollection($settings)
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener configuraciones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener configuraciones',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/notification-settings",
     *     summary="Crear configuración de notificación",
     *     description="Crea una nueva configuración de notificación en el sistema",
     *     tags={"NotificationSettings"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreNotificationSettingRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Configuración creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Configuración creada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/NotificationSettingResource")
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
    public function store(StoreNotificationSettingRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $setting = NotificationSetting::create($request->validated());

            DB::commit();

            Log::info("Configuración de notificación creada: ID {$setting->id} para usuario {$setting->user_id}");

            return response()->json([
                'success' => true,
                'message' => 'Configuración creada exitosamente',
                'data' => new NotificationSettingResource($setting->load('user'))
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear configuración: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear configuración',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/notification-settings/{id}",
     *     summary="Obtener configuración de notificación",
     *     description="Obtiene los detalles de una configuración específica",
     *     tags={"NotificationSettings"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la configuración",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Configuración obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Configuración obtenida exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/NotificationSettingResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Configuración no encontrada"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function show(NotificationSetting $notificationSetting): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Configuración obtenida exitosamente',
                'data' => new NotificationSettingResource($notificationSetting->load('user'))
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener configuración: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener configuración',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/notification-settings/{id}",
     *     summary="Actualizar configuración de notificación",
     *     description="Actualiza una configuración existente",
     *     tags={"NotificationSettings"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la configuración",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateNotificationSettingRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Configuración actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Configuración actualizada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/NotificationSettingResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Configuración no encontrada"
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
    public function update(UpdateNotificationSettingRequest $request, NotificationSetting $notificationSetting): JsonResponse
    {
        try {
            DB::beginTransaction();

            $notificationSetting->update($request->validated());

            DB::commit();

            Log::info("Configuración actualizada: ID {$notificationSetting->id}");

            return response()->json([
                'success' => true,
                'message' => 'Configuración actualizada exitosamente',
                'data' => new NotificationSettingResource($notificationSetting->load('user'))
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar configuración: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar configuración',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/notification-settings/{id}",
     *     summary="Eliminar configuración de notificación",
     *     description="Elimina una configuración del sistema",
     *     tags={"NotificationSettings"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la configuración",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Configuración eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Configuración eliminada exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Configuración no encontrada"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function destroy(NotificationSetting $notificationSetting): JsonResponse
    {
        try {
            DB::beginTransaction();

            $settingId = $notificationSetting->id;
            $notificationSetting->delete();

            DB::commit();

            Log::info("Configuración eliminada: ID {$settingId}");

            return response()->json([
                'success' => true,
                'message' => 'Configuración eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar configuración: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar configuración',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/notification-settings/statistics",
     *     summary="Estadísticas de configuraciones",
     *     description="Obtiene estadísticas generales de configuraciones de notificaciones",
     *     tags={"NotificationSettings"},
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
     *                 @OA\Property(property="total", type="integer", example=120),
     *                 @OA\Property(property="enabled", type="integer", example=95),
     *                 @OA\Property(property="disabled", type="integer", example=25),
     *                 @OA\Property(property="by_channel", type="object"),
     *                 @OA\Property(property="by_type", type="object")
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
            $stats = $userId ? NotificationSetting::getUserStats($userId) : NotificationSetting::getStats();

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
     *     path="/api/v1/notification-settings/channels",
     *     summary="Canales disponibles",
     *     description="Obtiene la lista de canales de notificación disponibles",
     *     tags={"NotificationSettings"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Canales obtenidos exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Canales obtenidos exitosamente"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function channels(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Canales obtenidos exitosamente',
            'data' => NotificationSetting::CHANNELS
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/notification-settings/notification-types",
     *     summary="Tipos de notificación disponibles",
     *     description="Obtiene la lista de tipos de notificación disponibles",
     *     tags={"NotificationSettings"},
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
    public function notificationTypes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Tipos obtenidos exitosamente',
            'data' => NotificationSetting::NOTIFICATION_TYPES
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/notification-settings/{id}/toggle",
     *     summary="Cambiar estado de configuración",
     *     description="Habilita o deshabilita una configuración de notificación",
     *     tags={"NotificationSettings"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la configuración",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estado cambiado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Estado cambiado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/NotificationSettingResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Configuración no encontrada"
     *     )
     * )
     */
    public function toggle(NotificationSetting $notificationSetting): JsonResponse
    {
        try {
            $notificationSetting->toggle();

            Log::info("Estado de configuración cambiado: ID {$notificationSetting->id} - Habilitado: " . ($notificationSetting->enabled ? 'Sí' : 'No'));

            return response()->json([
                'success' => true,
                'message' => 'Estado cambiado exitosamente',
                'data' => new NotificationSettingResource($notificationSetting->load('user'))
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cambiar estado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/notification-settings/user/{user_id}",
     *     summary="Configuraciones de usuario",
     *     description="Obtiene todas las configuraciones de notificación de un usuario específico",
     *     tags={"NotificationSettings"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         description="ID del usuario",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Configuraciones obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Configuraciones obtenidas exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/NotificationSettingCollection")
     *         )
     *     )
     * )
     */
    public function userSettings(int $userId): JsonResponse
    {
        try {
            $settings = NotificationSetting::byUser($userId)->get();

            return response()->json([
                'success' => true,
                'message' => 'Configuraciones obtenidas exitosamente',
                'data' => NotificationSettingResource::collection($settings)
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener configuraciones del usuario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener configuraciones del usuario'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/notification-settings/create-defaults/{user_id}",
     *     summary="Crear configuraciones por defecto",
     *     description="Crea configuraciones por defecto para un usuario específico",
     *     tags={"NotificationSettings"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         description="ID del usuario",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Configuraciones por defecto creadas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Configuraciones por defecto creadas exitosamente"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="created", type="integer", example=16),
     *                 @OA\Property(property="settings", type="array", @OA\Items(ref="#/components/schemas/NotificationSettingResource"))
     *             )
     *         )
     *     )
     * )
     */
    public function createDefaults(int $userId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $created = NotificationSetting::createDefaultSettings($userId);
            $settings = NotificationSetting::byUser($userId)->get();

            DB::commit();

            Log::info("Configuraciones por defecto creadas para usuario: {$userId} - Total: {$created}");

            return response()->json([
                'success' => true,
                'message' => 'Configuraciones por defecto creadas exitosamente',
                'data' => [
                    'created' => $created,
                    'settings' => NotificationSettingResource::collection($settings)
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear configuraciones por defecto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear configuraciones por defecto'
            ], 500);
        }
    }
}
