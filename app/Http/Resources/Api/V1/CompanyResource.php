<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Company",
 *     title="Company",
 *     description="Modelo de empresa",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Energía Verde S.L."),
 *     @OA\Property(property="cif", type="string", example="B12345678"),
 *     @OA\Property(property="contact_person", type="string", example="Juan Pérez García"),
 *     @OA\Property(property="company_address", type="string", example="Calle Mayor 123, 28001 Madrid, España"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(
 *         property="subscription_requests_count",
 *         type="integer",
 *         description="Número de solicitudes de suscripción",
 *         example=5
 *     ),
 *     @OA\Property(
 *         property="users_count",
 *         type="integer",
 *         description="Número de usuarios asociados",
 *         example=3
 *     )
 * )
 */
class CompanyResource extends JsonResource
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
            'cif' => $this->cif,
            'contact_person' => $this->contact_person,
            'company_address' => $this->company_address,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Relaciones cargadas si están disponibles
            'subscription_requests_count' => $this->whenCounted('subscriptionRequests'),
            'users_count' => $this->whenCounted('users'),
        ];
    }
}
