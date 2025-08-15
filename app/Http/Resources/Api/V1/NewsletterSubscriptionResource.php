<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NewsletterSubscriptionResource',
    title: 'Newsletter Subscription Resource',
    description: 'Resource de suscripción a newsletter',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'usuario@email.com'),
        new OA\Property(property: 'name', type: 'string', nullable: true, example: 'María López'),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'unsubscribed', 'bounced'], example: 'active'),
        new OA\Property(property: 'subscribed_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'unsubscribed_at', type: 'string', format: 'date-time', nullable: true, example: null),
        new OA\Property(property: 'preferences', type: 'object', nullable: true, example: ['weekly' => true, 'monthly' => false]),
        new OA\Property(property: 'source', type: 'string', nullable: true, example: 'website'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
    ]
)]
class NewsletterSubscriptionResource extends JsonResource
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
