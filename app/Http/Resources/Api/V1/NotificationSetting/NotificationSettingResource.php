<?php

namespace App\Http\Resources\Api\V1\NotificationSetting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = auth()->user();
        $isAdmin = $user && $user->hasRole('admin');
        $isManager = $user && ($user->hasRole('admin') || $user->hasRole('manager'));

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'channel' => $this->channel,
            'notification_type' => $this->notification_type,
            'enabled' => $this->enabled,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Campos calculados
            'channel_label' => $this->getChannelLabelAttribute(),
            'notification_type_label' => $this->getNotificationTypeLabelAttribute(),
            'status_badge_class' => $this->getStatusBadgeClassAttribute(),
            'channel_icon' => $this->getChannelIconAttribute(),
            'notification_type_icon' => $this->getNotificationTypeIconAttribute(),
            'channel_color' => $this->getChannelColorAttribute(),
            'notification_type_color' => $this->getNotificationTypeColorAttribute(),

            // Información del usuario (condicional)
            'user' => $this->when($isManager, function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'avatar' => $this->user->profile_photo_url ?? null,
                ];
            }),

            // Campos adicionales para administradores
            'user_email' => $this->when($isAdmin, $this->user->email ?? null),
            'user_name' => $this->when($isAdmin, $this->user->name ?? null),

            // Enlaces de acción
            'actions' => [
                'toggle' => route('api.v1.notification-settings.toggle', $this->id),
                'edit' => $this->when($isManager, route('api.v1.notification-settings.update', $this->id)),
                'delete' => $this->when($isAdmin, route('api.v1.notification-settings.destroy', $this->id)),
            ],

            // Metadatos
            'meta' => [
                'resource_type' => 'notification_setting',
                'api_version' => 'v1',
                'generated_at' => now()->toISOString(),
            ],
        ];
    }
}
