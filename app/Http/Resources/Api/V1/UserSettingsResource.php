<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserSettingsResource",
 *     title="User Settings Resource",
 *     description="Recurso de configuraciones de usuario",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="language", type="string", example="es"),
 *     @OA\Property(property="timezone", type="string", example="Europe/Madrid"),
 *     @OA\Property(property="theme", type="string", example="light"),
 *     @OA\Property(property="notifications_enabled", type="boolean", example=true),
 *     @OA\Property(property="email_notifications", type="boolean", example=true),
 *     @OA\Property(property="push_notifications", type="boolean", example=true),
 *     @OA\Property(property="sms_notifications", type="boolean", example=false),
 *     @OA\Property(property="marketing_emails", type="boolean", example=true),
 *     @OA\Property(property="newsletter_subscription", type="boolean", example=true),
 *     @OA\Property(property="privacy_level", type="string", example="public"),
 *     @OA\Property(property="profile_visibility", type="string", example="public"),
 *     @OA\Property(property="show_achievements", type="boolean", example=true),
 *     @OA\Property(property="show_statistics", type="boolean", example=true),
 *     @OA\Property(property="show_activity", type="boolean", example=true),
 *     @OA\Property(property="date_format", type="string", example="d/m/Y"),
 *     @OA\Property(property="time_format", type="string", example="24"),
 *     @OA\Property(property="currency", type="string", example="EUR"),
 *     @OA\Property(property="measurement_unit", type="string", example="metric"),
 *     @OA\Property(property="energy_unit", type="string", example="kWh"),
 *     @OA\Property(property="custom_settings", type="object", example={"dashboard_layout": "grid"}),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class UserSettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            
            // Configuraciones generales
            'language' => $this->language,
            'timezone' => $this->timezone,
            'theme' => $this->theme,
            
            // Notificaciones
            'notifications_enabled' => $this->notifications_enabled,
            'email_notifications' => $this->email_notifications,
            'push_notifications' => $this->push_notifications,
            'sms_notifications' => $this->sms_notifications,
            'marketing_emails' => $this->marketing_emails,
            'newsletter_subscription' => $this->newsletter_subscription,
            
            // Privacidad
            'privacy_level' => $this->privacy_level,
            'profile_visibility' => $this->profile_visibility,
            'show_achievements' => $this->show_achievements,
            'show_statistics' => $this->show_statistics,
            'show_activity' => $this->show_activity,
            
            // Formato y visualización
            'date_format' => $this->date_format,
            'time_format' => $this->time_format,
            'currency' => $this->currency,
            'measurement_unit' => $this->measurement_unit,
            'energy_unit' => $this->energy_unit,
            
            // Configuraciones personalizadas
            'custom_settings' => $this->custom_settings,
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}