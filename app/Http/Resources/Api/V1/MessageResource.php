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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'subject' => $this->subject,
            'message' => $this->message,
            'status' => $this->status,
            'priority' => $this->priority,
            'message_type' => $this->message_type,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'read_at' => $this->read_at,
            'replied_at' => $this->replied_at,
            'replied_by_user_id' => $this->replied_by_user_id,
            'internal_notes' => $this->internal_notes,
            'assigned_to_user_id' => $this->assigned_to_user_id,
            'organization_id' => $this->organization_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'replied_by' => $this->whenLoaded('repliedBy'),
            'assigned_to' => $this->whenLoaded('assignedTo'),
            'organization' => $this->whenLoaded('organization'),

            // Computed properties
            'is_read' => $this->isRead(),
            'is_replied' => $this->isReplied(),
            'is_pending' => $this->isPending(),
            'is_assigned' => $this->isAssigned(),
            'is_urgent' => $this->isUrgent(),
            'is_high_priority' => $this->isHighPriority(),
            'status_label' => $this->getStatusLabel(),
            'priority_label' => $this->getPriorityLabel(),
            'type_label' => $this->getTypeLabel(),
            'formatted_phone' => $this->getFormattedPhone(),
            'response_time_hours' => $this->getResponseTime(),
            'has_internal_notes' => $this->hasInternalNotes(),
            'short_message' => $this->getShortMessage(),
        ];
    }
}
