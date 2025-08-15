<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Achievement",
 *     title="Achievement",
 *     description="Modelo de logro/achievement",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Primer kWh Generado"),
 *     @OA\Property(property="description", type="string", example="Generar tu primer kWh de energía renovable"),
 *     @OA\Property(property="icon", type="string", example="⚡"),
 *     @OA\Property(property="type", type="string", enum={"energy", "participation", "community", "milestone"}, example="energy"),
 *     @OA\Property(
 *         property="criteria",
 *         type="object",
 *         description="Criterios para desbloquear el logro",
 *         example={"type": "kwh_produced", "value": 1}
 *     ),
 *     @OA\Property(property="points_reward", type="integer", example=100),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="sort_order", type="integer", example=1),
 *     @OA\Property(property="unlocks_count", type="integer", description="Número de usuarios que han obtenido este logro", example=25),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00Z")
 * )
 */
class AchievementResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'type' => $this->type,
            'criteria' => $this->criteria,
            'points_reward' => $this->points_reward,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'unlocks_count' => $this->whenCounted('userAchievements'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
