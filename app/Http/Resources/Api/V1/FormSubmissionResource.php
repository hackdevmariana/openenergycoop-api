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
        return [
            'id' => $this->id,
            'form_name' => $this->form_name,
            'fields' => $this->fields,
            'status' => $this->status,
            'source_url' => $this->source_url,
            'referrer' => $this->referrer,
            'ip_address' => $this->when(
                auth()->check(), // Only show IP to authenticated users
                $this->ip_address
            ),
            'user_agent' => $this->when(
                auth()->check(), // Only show user agent to authenticated users
                $this->user_agent
            ),
            'processed_at' => $this->processed_at,
            'processed_by_user_id' => $this->processed_by_user_id,
            'processing_notes' => $this->processing_notes,
            'organization_id' => $this->organization_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'processed_by' => $this->whenLoaded('processedBy'),
            'organization' => $this->whenLoaded('organization'),

            // Computed properties
            'status_label' => $this->getStatusLabel(),
            'form_type_label' => $this->getFormTypeLabel(),
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'phone' => $this->getPhone(),
            'message' => $this->getMessage(),
            'subject' => $this->getSubject(),
            'is_pending' => $this->isPending(),
            'is_processed' => $this->isProcessed(),
            'is_archived' => $this->isArchived(),
            'is_spam' => $this->isSpam(),
            'has_been_processed' => $this->hasBeenProcessed(),
            'days_since_submission' => $this->getDaysSinceSubmission(),
            'processing_time_hours' => $this->getProcessingTime(),
            'source_domain' => $this->getSourceDomain(),
            'referrer_domain' => $this->getReferrerDomain(),
            'browser' => $this->getBrowser(),
            'is_mobile' => $this->isMobile(),
            'has_required_fields' => $this->hasRequiredFields(),
            'is_potential_spam' => $this->isPotentialSpam(),
            'field_count' => $this->getFieldCount(),
        ];
    }
}
