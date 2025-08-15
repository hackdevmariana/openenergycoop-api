<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UserAchievement",
 *     title="UserAchievement",
 *     description="Modelo de logro de usuario",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="earned_at", type="string", format="date-time", example="2024-01-20T15:30:00Z"),
 *     @OA\Property(property="custom_message", type="string", example="¡Felicidades por tu primera producción de energía!", nullable=true),
 *     @OA\Property(property="reward_granted", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-20T15:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-20T15:30:00Z"),
 *     @OA\Property(
 *         property="user",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=5),
 *         @OA\Property(property="name", type="string", example="María García"),
 *         @OA\Property(property="email", type="string", example="maria@example.com")
 *     ),
 *     @OA\Property(
 *         property="achievement",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Primer kWh Generado"),
 *         @OA\Property(property="description", type="string", example="Generar tu primer kWh de energía renovable"),
 *         @OA\Property(property="icon", type="string", example="⚡"),
 *         @OA\Property(property="type", type="string", example="energy"),
 *         @OA\Property(property="points_reward", type="integer", example=100)
 *     )
 * )
 */
class UserAchievementResource extends JsonResource
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
            'earned_at' => $this->earned_at?->toISOString(),
            'custom_message' => $this->custom_message,
            'reward_granted' => $this->reward_granted,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relaciones
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            
            'achievement' => $this->whenLoaded('achievement', function () {
                return [
                    'id' => $this->achievement->id,
                    'name' => $this->achievement->name,
                    'description' => $this->achievement->description,
                    'icon' => $this->achievement->icon,
                    'type' => $this->achievement->type,
                    'points_reward' => $this->achievement->points_reward,
                ];
            }),
        ];
    }
}
