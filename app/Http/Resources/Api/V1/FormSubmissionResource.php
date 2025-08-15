<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'FormSubmissionResource',
    title: 'Form Submission Resource',
    description: 'Resource de envío de formulario',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'form_name', type: 'string', example: 'Formulario de Contacto'),
        new OA\Property(property: 'form_type', type: 'string', example: 'contact'),
        new OA\Property(property: 'form_data', type: 'object', example: ['mensaje' => 'Hola, necesito información']),
        new OA\Property(property: 'user_id', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'name', type: 'string', nullable: true, example: 'Juan Pérez'),
        new OA\Property(property: 'email', type: 'string', nullable: true, example: 'juan@email.com'),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'reviewed', 'processed', 'rejected'], example: 'pending'),
        new OA\Property(property: 'source_page', type: 'string', nullable: true, example: '/contacto'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
    ]
)]
class FormSubmissionResource extends JsonResource
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
