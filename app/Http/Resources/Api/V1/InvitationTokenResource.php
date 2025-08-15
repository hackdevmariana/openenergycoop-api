<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="InvitationToken",
 *     title="InvitationToken",
 *     description="Modelo de token de invitación",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="token", type="string", example="abc123def456ghi789jkl012mno345pq"),
 *     @OA\Property(property="email", type="string", format="email", example="usuario@example.com", nullable=true),
 *     @OA\Property(property="status", type="string", enum={"pending", "used", "expired", "revoked"}, example="pending"),
 *     @OA\Property(property="expires_at", type="string", format="date-time", example="2024-02-15T10:30:00Z", nullable=true),
 *     @OA\Property(property="used_at", type="string", format="date-time", example="2024-01-20T15:45:00Z", nullable=true),
 *     @OA\Property(property="invitation_url", type="string", example="https://app.example.com/invitation/abc123def456"),
 *     @OA\Property(property="is_valid", type="boolean", example=true),
 *     @OA\Property(property="is_expired", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(
 *         property="organization",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Cooperativa Energía Verde")
 *     ),
 *     @OA\Property(
 *         property="organization_role",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=2),
 *         @OA\Property(property="name", type="string", example="Miembro"),
 *         @OA\Property(property="slug", type="string", example="miembro")
 *     ),
 *     @OA\Property(
 *         property="invited_by",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=5),
 *         @OA\Property(property="name", type="string", example="Juan Pérez")
 *     )
 * )
 */
class InvitationTokenResource extends JsonResource
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
            'token' => $this->token,
            'email' => $this->email,
            'status' => $this->status,
            'expires_at' => $this->expires_at?->toISOString(),
            'used_at' => $this->used_at?->toISOString(),
            'invitation_url' => $this->invitation_url,
            'is_valid' => $this->isValid(),
            'is_expired' => $this->isExpired(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relaciones
            'organization' => $this->whenLoaded('organization', function () {
                return [
                    'id' => $this->organization->id,
                    'name' => $this->organization->name,
                ];
            }),
            
            'organization_role' => $this->whenLoaded('organizationRole', function () {
                return [
                    'id' => $this->organizationRole->id,
                    'name' => $this->organizationRole->name,
                    'slug' => $this->organizationRole->slug,
                ];
            }),
            
            'invited_by' => $this->whenLoaded('invitedByUser', function () {
                return [
                    'id' => $this->invitedByUser->id,
                    'name' => $this->invitedByUser->name,
                ];
            }),
        ];
    }
}
