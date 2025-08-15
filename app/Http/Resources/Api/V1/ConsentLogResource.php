<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ConsentLogResource",
 *     title="Consent Log Resource",
 *     description="Recurso de registro de consentimiento",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="consent_type", type="string", example="privacy_policy"),
 *     @OA\Property(property="consent_given", type="boolean", example=true),
 *     @OA\Property(property="version", type="string", example="1.0"),
 *     @OA\Property(property="purpose", type="string", example="Procesamiento de datos personales"),
 *     @OA\Property(property="legal_basis", type="string", example="Artículo 6.1.a GDPR"),
 *     @OA\Property(property="data_categories", type="array", @OA\Items(type="string"), example={"personal_data", "contact_info"}),
 *     @OA\Property(property="retention_period", type="string", example="5 años"),
 *     @OA\Property(property="third_parties", type="array", @OA\Items(type="string"), example={"Google Analytics"}),
 *     @OA\Property(property="withdrawal_method", type="string", example="Contactar a privacy@example.com"),
 *     @OA\Property(property="ip_address", type="string", example="192.168.1.100"),
 *     @OA\Property(property="user_agent", type="string", example="Mozilla/5.0..."),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="revoked_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="revocation_reason", type="string", nullable=true)
 * )
 */
class ConsentLogResource extends JsonResource
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
            'consent_type' => $this->consent_type,
            'consent_given' => $this->consent_given,
            'version' => $this->version,
            'purpose' => $this->purpose,
            'legal_basis' => $this->legal_basis,
            'data_categories' => $this->data_categories,
            'retention_period' => $this->retention_period,
            'third_parties' => $this->third_parties,
            'withdrawal_method' => $this->withdrawal_method,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'metadata' => $this->metadata,
            'is_active' => !$this->revoked_at && $this->consent_given,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'revoked_at' => $this->revoked_at,
            'revocation_reason' => $this->revocation_reason,
        ];
    }
}