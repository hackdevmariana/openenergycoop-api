<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UserDevice\StoreUserDeviceRequest;
use App\Http\Requests\Api\V1\UserDevice\UpdateUserDeviceRequest;
use App\Http\Resources\Api\V1\UserDeviceResource;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="User Devices",
 *     description="Gestión de dispositivos de usuario"
 * )
 */
class UserDeviceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/user-devices",
     *     tags={"User Devices"},
     *     summary="Listar dispositivos del usuario autenticado",
     *     description="Obtiene todos los dispositivos registrados del usuario actual",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="active_only",
     *         in="query",
     *         description="Solo dispositivos activos",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de dispositivos obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/UserDeviceResource"))
     *         )
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = UserDevice::query()
            ->forUser(auth()->id())
            ->orderByDesc('is_current')
            ->orderByDesc('last_seen_at');

        if ($request->boolean('active_only')) {
            $query->active();
        }

        $devices = $query->get();

        return UserDeviceResource::collection($devices);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user-devices",
     *     tags={"User Devices"},
     *     summary="Registrar nuevo dispositivo",
     *     description="Registra un nuevo dispositivo para el usuario autenticado",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"device_name", "device_type", "platform"},
     *             @OA\Property(property="device_name", type="string", maxLength=255, example="iPhone 15 Pro"),
     *             @OA\Property(property="device_type", type="string", enum={"mobile", "tablet", "desktop", "tv", "watch", "other"}, example="mobile"),
     *             @OA\Property(property="platform", type="string", enum={"ios", "android", "windows", "macos", "linux", "web", "other"}, example="ios"),
     *             @OA\Property(property="browser", type="string", maxLength=100, example="Safari"),
     *             @OA\Property(property="browser_version", type="string", maxLength=50, example="17.2"),
     *             @OA\Property(property="os_version", type="string", maxLength=50, example="iOS 17.2.1"),
     *             @OA\Property(property="push_token", type="string", maxLength=500, example="abc123..."),
     *             @OA\Property(property="is_current", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Dispositivo registrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/UserDeviceResource"),
     *             @OA\Property(property="message", type="string", example="Dispositivo registrado exitosamente")
     *         )
     *     )
     * )
     */
    public function store(StoreUserDeviceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();
        $validated['last_seen_at'] = now();

        // Registrar dispositivo usando el método del modelo
        $device = UserDevice::registerDevice(
            auth()->id(),
            $validated['device_name'],
            $validated['device_type'],
            $validated['platform'],
            array_filter([
                'browser' => $validated['browser'] ?? null,
                'browser_version' => $validated['browser_version'] ?? null,
                'os_version' => $validated['os_version'] ?? null,
                'push_token' => $validated['push_token'] ?? null,
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
            ])
        );

        // Si se marca como dispositivo actual, actualizar
        if ($request->boolean('is_current')) {
            $device->setCurrent();
        }

        return response()->json([
            'data' => new UserDeviceResource($device),
            'message' => 'Dispositivo registrado exitosamente'
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user-devices/{id}",
     *     tags={"User Devices"},
     *     summary="Obtener dispositivo específico",
     *     description="Obtiene los detalles de un dispositivo específico del usuario",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del dispositivo",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dispositivo obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/UserDeviceResource")
     *         )
     *     )
     * )
     */
    public function show(UserDevice $userDevice): JsonResponse
    {
        // Verificar que el dispositivo pertenece al usuario autenticado
        if ($userDevice->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Dispositivo no encontrado'
            ], 404);
        }

        return response()->json([
            'data' => new UserDeviceResource($userDevice)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/user-devices/{id}",
     *     tags={"User Devices"},
     *     summary="Actualizar dispositivo",
     *     description="Actualiza los datos de un dispositivo existente",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del dispositivo",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="device_name", type="string", maxLength=255, example="iPhone 15 Pro Max"),
     *             @OA\Property(property="push_token", type="string", maxLength=500, example="xyz789..."),
     *             @OA\Property(property="is_current", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dispositivo actualizado exitosamente"
     *     )
     * )
     */
    public function update(UpdateUserDeviceRequest $request, UserDevice $userDevice): JsonResponse
    {
        // Verificar que el dispositivo pertenece al usuario autenticado
        if ($userDevice->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Dispositivo no encontrado'
            ], 404);
        }

        $validated = $request->validated();
        $userDevice->update($validated);

        // Si se marca como dispositivo actual, actualizar
        if ($request->boolean('is_current')) {
            $userDevice->setCurrent();
        }

        // Actualizar última actividad
        $userDevice->updateLastSeen();

        return response()->json([
            'data' => new UserDeviceResource($userDevice->fresh()),
            'message' => 'Dispositivo actualizado exitosamente'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/user-devices/{id}",
     *     tags={"User Devices"},
     *     summary="Revocar dispositivo",
     *     description="Revoca un dispositivo (soft delete)",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del dispositivo",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dispositivo revocado exitosamente"
     *     )
     * )
     */
    public function destroy(UserDevice $userDevice): JsonResponse
    {
        // Verificar que el dispositivo pertenece al usuario autenticado
        if ($userDevice->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Dispositivo no encontrado'
            ], 404);
        }

        $userDevice->revoke();

        return response()->json([
            'message' => 'Dispositivo revocado exitosamente'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user-devices/{id}/set-current",
     *     tags={"User Devices"},
     *     summary="Establecer dispositivo como actual",
     *     description="Marca un dispositivo como el dispositivo actual del usuario",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del dispositivo",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dispositivo establecido como actual"
     *     )
     * )
     */
    public function setCurrent(UserDevice $userDevice): JsonResponse
    {
        // Verificar que el dispositivo pertenece al usuario autenticado
        if ($userDevice->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Dispositivo no encontrado'
            ], 404);
        }

        $userDevice->setCurrent();

        return response()->json([
            'data' => new UserDeviceResource($userDevice->fresh()),
            'message' => 'Dispositivo establecido como actual'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user-devices/{id}/update-activity",
     *     tags={"User Devices"},
     *     summary="Actualizar actividad del dispositivo",
     *     description="Actualiza la última actividad del dispositivo",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del dispositivo",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Actividad actualizada"
     *     )
     * )
     */
    public function updateActivity(UserDevice $userDevice): JsonResponse
    {
        // Verificar que el dispositivo pertenece al usuario autenticado
        if ($userDevice->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Dispositivo no encontrado'
            ], 404);
        }

        $userDevice->updateLastSeen();

        return response()->json([
            'message' => 'Actividad actualizada'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user-devices/current",
     *     tags={"User Devices"},
     *     summary="Obtener dispositivo actual",
     *     description="Obtiene el dispositivo marcado como actual del usuario",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dispositivo actual obtenido"
     *     )
     * )
     */
    public function current(): JsonResponse
    {
        $currentDevice = UserDevice::forUser(auth()->id())
            ->current()
            ->first();

        if (!$currentDevice) {
            return response()->json([
                'message' => 'No hay dispositivo actual configurado'
            ], 404);
        }

        return response()->json([
            'data' => new UserDeviceResource($currentDevice)
        ]);
    }
}