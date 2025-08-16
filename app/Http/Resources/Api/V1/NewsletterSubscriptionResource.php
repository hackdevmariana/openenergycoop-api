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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
            'subscription_source' => $this->subscription_source,
            'preferences' => $this->preferences,
            'tags' => $this->tags,
            'language' => $this->language,
            'confirmed_at' => $this->confirmed_at,
            'unsubscribed_at' => $this->unsubscribed_at,
            'confirmation_token' => $this->when(
                auth()->user()?->can('manage newsletters'),
                $this->confirmation_token
            ),
            'unsubscribe_token' => $this->when(
                auth()->user()?->can('manage newsletters'),
                $this->unsubscribe_token
            ),
            'ip_address' => $this->when(
                auth()->user()?->can('manage newsletters'),
                $this->ip_address
            ),
            'user_agent' => $this->when(
                auth()->user()?->can('manage newsletters'),
                $this->user_agent
            ),
            'emails_sent' => $this->emails_sent,
            'emails_opened' => $this->emails_opened,
            'links_clicked' => $this->links_clicked,
            'last_email_sent_at' => $this->last_email_sent_at,
            'last_email_opened_at' => $this->last_email_opened_at,
            'organization_id' => $this->organization_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'organization' => $this->whenLoaded('organization'),

            // Computed properties
            'is_active' => $this->isActive(),
            'is_pending' => $this->isPending(),
            'is_unsubscribed' => $this->isUnsubscribed(),
            'is_bounced' => $this->isBounced(),
            'has_complained' => $this->hasComplained(),
            'is_engaged' => $this->isEngaged(),
            'status_label' => $this->getStatusLabel(),
            'language_label' => $this->getLanguageLabel(),
            'source_label' => $this->getSourceLabel(),
            'open_rate' => $this->getOpenRate(),
            'click_rate' => $this->getClickRate(),
            'engagement_score' => $this->getEngagementScore(),
            'days_since_subscription' => $this->getDaysSinceSubscription(),
            'days_since_last_email' => $this->getDaysSinceLastEmail(),
            'confirmation_url' => $this->when(
                $this->isPending(),
                $this->getConfirmationUrl()
            ),
            'unsubscribe_url' => $this->getUnsubscribeUrl(),
        ];
    }
}
