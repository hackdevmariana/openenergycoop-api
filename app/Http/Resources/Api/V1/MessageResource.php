<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MessageResource',
    title: 'Message Resource',
    description: 'Resource de mensaje',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Juan Pérez'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'juan@email.com'),
        new OA\Property(property: 'subject', type: 'string', example: 'Consulta sobre membresía'),
        new OA\Property(property: 'message', type: 'string', example: 'Hola, me gustaría saber más sobre cómo unirme...'),
        new OA\Property(property: 'phone', type: 'string', nullable: true, example: '+34 600 123 456'),
        new OA\Property(property: 'is_read', type: 'boolean', example: false),
        new OA\Property(property: 'replied_at', type: 'string', format: 'date-time', nullable: true, example: null),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
    ]
)]
class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
