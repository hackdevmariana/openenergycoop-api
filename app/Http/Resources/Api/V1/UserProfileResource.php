<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UserProfile",
 *     title="UserProfile",
 *     description="Modelo de perfil de usuario",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="avatar", type="string", example="/storage/avatars/user-123.jpg", nullable=true),
 *     @OA\Property(property="bio", type="string", example="Apasionado por la energía renovable y la sostenibilidad", nullable=true),
 *     @OA\Property(property="municipality_id", type="string", example="28001", nullable=true),
 *     @OA\Property(property="join_date", type="string", format="date", example="2023-06-15", nullable=true),
 *     @OA\Property(property="role_in_cooperative", type="string", example="miembro", nullable=true),
 *     @OA\Property(property="profile_completed", type="boolean", example=true),
 *     @OA\Property(property="newsletter_opt_in", type="boolean", example=true),
 *     @OA\Property(property="show_in_rankings", type="boolean", example=true),
 *     @OA\Property(property="co2_avoided_total", type="number", format="float", example=1234.56),
 *     @OA\Property(property="kwh_produced_total", type="number", format="float", example=2345.67),
 *     @OA\Property(property="points_total", type="integer", example=1500),
 *     @OA\Property(
 *         property="badges_earned",
 *         type="array",
 *         @OA\Items(type="string"),
 *         example={"first_kwh", "eco_warrior", "community_builder"}
 *     ),
 *     @OA\Property(property="birth_date", type="string", format="date", example="1985-03-22", nullable=true),
 *     @OA\Property(property="team_id", type="string", example="equipo-verde", nullable=true),
 *     @OA\Property(property="age", type="integer", example=38, nullable=true),
 *     @OA\Property(property="organization_rank", type="integer", example=15),
 *     @OA\Property(property="municipality_rank", type="integer", example=8),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(
 *         property="user",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=5),
 *         @OA\Property(property="name", type="string", example="María García"),
 *         @OA\Property(property="email", type="string", example="maria@example.com")
 *     ),
 *     @OA\Property(
 *         property="organization",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Cooperativa Energía Verde")
 *     )
 * )
 */
class UserProfileResource extends JsonResource
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
            'avatar' => $this->avatar ? url($this->avatar) : null,
            'bio' => $this->bio,
            'municipality_id' => $this->municipality_id,
            'join_date' => $this->join_date?->toDateString(),
            'role_in_cooperative' => $this->role_in_cooperative,
            'profile_completed' => $this->profile_completed,
            'newsletter_opt_in' => $this->newsletter_opt_in,
            'show_in_rankings' => $this->show_in_rankings,
            'co2_avoided_total' => (float) $this->co2_avoided_total,
            'kwh_produced_total' => (float) $this->kwh_produced_total,
            'points_total' => $this->points_total,
            'badges_earned' => $this->badges_earned ?? [],
            'birth_date' => $this->birth_date?->toDateString(),
            'team_id' => $this->team_id,
            'age' => $this->age,
            'organization_rank' => $this->organization_rank,
            'municipality_rank' => $this->municipality_rank,
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
            
            'organization' => $this->whenLoaded('organization', function () {
                return [
                    'id' => $this->organization->id,
                    'name' => $this->organization->name,
                ];
            }),
        ];
    }
}
