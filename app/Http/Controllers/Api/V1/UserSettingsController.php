<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UserSettings\UpdateUserSettingsRequest;
use App\Http\Resources\Api\V1\UserSettingsResource;
use App\Models\UserSettings;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="User Settings",
 *     description="Gestión de configuraciones de usuario"
 * )
 */
class UserSettingsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/user-settings",
     *     tags={"User Settings"},
     *     summary="Obtener configuraciones del usuario",
     *     description="Obtiene todas las configuraciones del usuario autenticado",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Configuraciones obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/UserSettingsResource")
     *         )
     *     )
     * )
     */
    public function show(): JsonResponse
    {
        $settings = UserSettings::firstOrCreate([
            'user_id' => auth()->id()
        ]);

        return response()->json([
            'data' => new UserSettingsResource($settings)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/user-settings",
     *     tags={"User Settings"},
     *     summary="Actualizar configuraciones del usuario",
     *     description="Actualiza las configuraciones del usuario autenticado",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="language", type="string", enum={"es", "en", "ca", "eu", "gl"}, example="es"),
     *             @OA\Property(property="timezone", type="string", example="Europe/Madrid"),
     *             @OA\Property(property="theme", type="string", enum={"light", "dark", "auto"}, example="light"),
     *             @OA\Property(property="notifications_enabled", type="boolean", example=true),
     *             @OA\Property(property="email_notifications", type="boolean", example=true),
     *             @OA\Property(property="push_notifications", type="boolean", example=true),
     *             @OA\Property(property="sms_notifications", type="boolean", example=false),
     *             @OA\Property(property="marketing_emails", type="boolean", example=true),
     *             @OA\Property(property="newsletter_subscription", type="boolean", example=true),
     *             @OA\Property(property="privacy_level", type="string", enum={"public", "friends", "private"}, example="public"),
     *             @OA\Property(property="profile_visibility", type="string", enum={"public", "registered", "private"}, example="public"),
     *             @OA\Property(property="show_achievements", type="boolean", example=true),
     *             @OA\Property(property="show_statistics", type="boolean", example=true),
     *             @OA\Property(property="show_activity", type="boolean", example=true),
     *             @OA\Property(property="date_format", type="string", example="d/m/Y"),
     *             @OA\Property(property="time_format", type="string", enum={"12", "24"}, example="24"),
     *             @OA\Property(property="currency", type="string", example="EUR"),
     *             @OA\Property(property="measurement_unit", type="string", enum={"metric", "imperial"}, example="metric"),
     *             @OA\Property(property="energy_unit", type="string", enum={"kWh", "MWh", "GWh"}, example="kWh"),
     *             @OA\Property(property="custom_settings", type="object", example={"dashboard_layout": "grid", "chart_type": "line"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Configuraciones actualizadas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/UserSettingsResource"),
     *             @OA\Property(property="message", type="string", example="Configuraciones actualizadas exitosamente")
     *         )
     *     )
     * )
     */
    public function update(UpdateUserSettingsRequest $request): JsonResponse
    {
        $settings = UserSettings::firstOrCreate([
            'user_id' => auth()->id()
        ]);

        $settings->update($request->validated());

        return response()->json([
            'data' => new UserSettingsResource($settings->fresh()),
            'message' => 'Configuraciones actualizadas exitosamente'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user-settings/notifications",
     *     tags={"User Settings"},
     *     summary="Obtener configuración de notificaciones",
     *     description="Obtiene solo las configuraciones de notificaciones del usuario",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Configuración de notificaciones obtenida"
     *     )
     * )
     */
    public function notifications(): JsonResponse
    {
        $settings = UserSettings::firstOrCreate([
            'user_id' => auth()->id()
        ]);

        return response()->json([
            'notifications_enabled' => $settings->notifications_enabled,
            'email_notifications' => $settings->email_notifications,
            'push_notifications' => $settings->push_notifications,
            'sms_notifications' => $settings->sms_notifications,
            'marketing_emails' => $settings->marketing_emails,
            'newsletter_subscription' => $settings->newsletter_subscription,
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/user-settings/notifications",
     *     tags={"User Settings"},
     *     summary="Actualizar configuración de notificaciones",
     *     description="Actualiza solo las configuraciones de notificaciones",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="notifications_enabled", type="boolean", example=true),
     *             @OA\Property(property="email_notifications", type="boolean", example=true),
     *             @OA\Property(property="push_notifications", type="boolean", example=true),
     *             @OA\Property(property="sms_notifications", type="boolean", example=false),
     *             @OA\Property(property="marketing_emails", type="boolean", example=true),
     *             @OA\Property(property="newsletter_subscription", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Configuración de notificaciones actualizada"
     *     )
     * )
     */
    public function updateNotifications(Request $request): JsonResponse
    {
        $request->validate([
            'notifications_enabled' => 'sometimes|boolean',
            'email_notifications' => 'sometimes|boolean',
            'push_notifications' => 'sometimes|boolean',
            'sms_notifications' => 'sometimes|boolean',
            'marketing_emails' => 'sometimes|boolean',
            'newsletter_subscription' => 'sometimes|boolean',
        ]);

        $settings = UserSettings::firstOrCreate([
            'user_id' => auth()->id()
        ]);

        $settings->update($request->only([
            'notifications_enabled',
            'email_notifications',
            'push_notifications',
            'sms_notifications',
            'marketing_emails',
            'newsletter_subscription',
        ]));

        return response()->json([
            'message' => 'Configuración de notificaciones actualizada exitosamente'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user-settings/privacy",
     *     tags={"User Settings"},
     *     summary="Obtener configuración de privacidad",
     *     description="Obtiene solo las configuraciones de privacidad del usuario",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Configuración de privacidad obtenida"
     *     )
     * )
     */
    public function privacy(): JsonResponse
    {
        $settings = UserSettings::firstOrCreate([
            'user_id' => auth()->id()
        ]);

        return response()->json([
            'privacy_level' => $settings->privacy_level,
            'profile_visibility' => $settings->profile_visibility,
            'show_achievements' => $settings->show_achievements,
            'show_statistics' => $settings->show_statistics,
            'show_activity' => $settings->show_activity,
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/user-settings/privacy",
     *     tags={"User Settings"},
     *     summary="Actualizar configuración de privacidad",
     *     description="Actualiza solo las configuraciones de privacidad",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="privacy_level", type="string", enum={"public", "friends", "private"}, example="public"),
     *             @OA\Property(property="profile_visibility", type="string", enum={"public", "registered", "private"}, example="public"),
     *             @OA\Property(property="show_achievements", type="boolean", example=true),
     *             @OA\Property(property="show_statistics", type="boolean", example=true),
     *             @OA\Property(property="show_activity", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Configuración de privacidad actualizada"
     *     )
     * )
     */
    public function updatePrivacy(Request $request): JsonResponse
    {
        $request->validate([
            'privacy_level' => 'sometimes|string|in:public,friends,private',
            'profile_visibility' => 'sometimes|string|in:public,registered,private',
            'show_achievements' => 'sometimes|boolean',
            'show_statistics' => 'sometimes|boolean',
            'show_activity' => 'sometimes|boolean',
        ]);

        $settings = UserSettings::firstOrCreate([
            'user_id' => auth()->id()
        ]);

        $settings->update($request->only([
            'privacy_level',
            'profile_visibility',
            'show_achievements',
            'show_statistics',
            'show_activity',
        ]));

        return response()->json([
            'message' => 'Configuración de privacidad actualizada exitosamente'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user-settings/reset",
     *     tags={"User Settings"},
     *     summary="Restablecer configuraciones por defecto",
     *     description="Restablece todas las configuraciones a sus valores por defecto",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Configuraciones restablecidas exitosamente"
     *     )
     * )
     */
    public function reset(): JsonResponse
    {
        $settings = UserSettings::firstOrCreate([
            'user_id' => auth()->id()
        ]);

        // Restablecer a valores por defecto
        $settings->update([
            'language' => 'es',
            'timezone' => 'Europe/Madrid',
            'theme' => 'light',
            'notifications_enabled' => true,
            'email_notifications' => true,
            'push_notifications' => true,
            'sms_notifications' => false,
            'marketing_emails' => true,
            'newsletter_subscription' => true,
            'privacy_level' => 'public',
            'profile_visibility' => 'public',
            'show_achievements' => true,
            'show_statistics' => true,
            'show_activity' => true,
            'date_format' => 'd/m/Y',
            'time_format' => '24',
            'currency' => 'EUR',
            'measurement_unit' => 'metric',
            'energy_unit' => 'kWh',
            'custom_settings' => null,
        ]);

        return response()->json([
            'data' => new UserSettingsResource($settings->fresh()),
            'message' => 'Configuraciones restablecidas exitosamente'
        ]);
    }
}