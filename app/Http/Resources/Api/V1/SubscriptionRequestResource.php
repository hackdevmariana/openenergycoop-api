<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="SubscriptionRequest",
 *     title="SubscriptionRequest",
 *     description="Modelo de solicitud de suscripción",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="cooperative_id", type="integer", example=1),
 *     @OA\Property(property="status", type="string", example="pending"),
 *     @OA\Property(property="type", type="string", example="new_subscription"),
 *     @OA\Property(property="submitted_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="processed_at", type="string", format="date-time", example="2024-01-15T11:00:00Z"),
 *     @OA\Property(property="notes", type="string", example="Solicitud de alta para nueva vivienda"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(
 *         property="user",
 *         ref="#/components/schemas/User",
 *         description="Usuario que hizo la solicitud"
 *     ),
 *     @OA\Property(
 *         property="cooperative",
 *         ref="#/components/schemas/Organization",
 *         description="Cooperativa/organización"
 *     )
 * )
 */
class SubscriptionRequestResource extends JsonResource
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
            'user_id' => $this->user_id,
            'cooperative_id' => $this->cooperative_id,
            'status' => $this->status,
            'type' => $this->type,
            'submitted_at' => $this->submitted_at?->toISOString(),
            'processed_at' => $this->processed_at?->toISOString(),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Relaciones cargadas si están disponibles
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'cooperative' => $this->whenLoaded('cooperative', function () {
                return [
                    'id' => $this->cooperative->id,
                    'name' => $this->cooperative->name,
                ];
            }),
        ];
    }
}
