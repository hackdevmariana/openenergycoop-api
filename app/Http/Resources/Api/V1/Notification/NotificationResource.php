<?php

namespace App\Http\Resources\Api\V1\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'read_at' => $this->read_at?->toISOString(),
            'delivered_at' => $this->delivered_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Campos calculados
            'is_read' => $this->isRead(),
            'is_delivered' => $this->isDelivered(),
            'time_ago' => $this->getTimeAgoAttribute(),
            'days_old' => $this->created_at->diffInDays(now()),

            // Badges y clases CSS
            'type_badge_class' => $this->getTypeBadgeClassAttribute(),
            'type_icon' => $this->getTypeIconAttribute(),
            'type_color' => $this->getTypeColorAttribute(),
            'status_badge_class' => $this->isRead() ? 'success' : 'warning',
            'status_text' => $this->isRead() ? 'Leída' : 'No leída',

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
            'deleted_at' => $this->when($isAdmin, $this->deleted_at?->toISOString()),
            'user_email' => $this->when($isAdmin, $this->user->email ?? null),
            'user_name' => $this->when($isAdmin, $this->user->name ?? null),

            // Enlaces de acción
            'actions' => [
                'mark_as_read' => $this->when(!$this->isRead(), route('api.v1.notifications.mark-read', $this->id)),
                'mark_as_delivered' => $this->when(!$this->isDelivered(), route('api.v1.notifications.mark-delivered', $this->id)),
                'edit' => $this->when($isManager, route('api.v1.notifications.update', $this->id)),
                'delete' => $this->when($isAdmin, route('api.v1.notifications.destroy', $this->id)),
            ],

            // Metadatos
            'meta' => [
                'resource_type' => 'notification',
                'api_version' => 'v1',
                'generated_at' => now()->toISOString(),
            ],
        ];
    }
}
