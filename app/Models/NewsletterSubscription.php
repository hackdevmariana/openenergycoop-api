<?php

namespace App\Models;

use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class NewsletterSubscription extends Model
{
    use HasFactory, HasOrganization;

    protected $fillable = [
        'name',
        'email',
        'status',
        'subscription_source',
        'preferences',
        'tags',
        'language',
        'confirmed_at',
        'unsubscribed_at',
        'confirmation_token',
        'unsubscribe_token',
        'ip_address',
        'user_agent',
        'emails_sent',
        'emails_opened',
        'links_clicked',
        'last_email_sent_at',
        'last_email_opened_at',
        'organization_id',
    ];

    protected $casts = [
        'preferences' => 'array',
        'tags' => 'array',
        'confirmed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'last_email_sent_at' => 'datetime',
        'last_email_opened_at' => 'datetime',
        'emails_sent' => 'integer',
        'emails_opened' => 'integer',
        'links_clicked' => 'integer',
    ];

    const STATUSES = [
        'pending' => 'Pendiente de confirmación',
        'confirmed' => 'Confirmado',
        'unsubscribed' => 'Desuscrito',
        'bounced' => 'Email rebotado',
        'complained' => 'Marcado como spam',
    ];

    const LANGUAGES = [
        'es' => 'Español',
        'en' => 'English',
        'ca' => 'Català',
        'eu' => 'Euskera',
        'gl' => 'Galego',
    ];

    const SOURCES = [
        'website' => 'Sitio web',
        'api' => 'API',
        'import' => 'Importación',
        'manual' => 'Manual',
        'form' => 'Formulario',
        'landing' => 'Landing page',
        'social' => 'Redes sociales',
        'referral' => 'Referido',
    ];

    // Relationships
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'confirmed');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeUnsubscribed(Builder $query): Builder
    {
        return $query->where('status', 'unsubscribed');
    }

    public function scopeBounced(Builder $query): Builder
    {
        return $query->where('status', 'bounced');
    }

    public function scopeComplained(Builder $query): Builder
    {
        return $query->where('status', 'complained');
    }

    public function scopeByLanguage(Builder $query, string $language): Builder
    {
        return $query->where('language', $language);
    }

    public function scopeBySource(Builder $query, string $source): Builder
    {
        return $query->where('subscription_source', $source);
    }

    public function scopeWithTags(Builder $query, array $tags): Builder
    {
        return $query->whereJsonContains('tags', $tags);
    }

    public function scopeRecentSubscribers(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeEngaged(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('emails_opened', '>', 0)
              ->orWhere('links_clicked', '>', 0);
        });
    }

    // Business Logic Methods
    public function isActive(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isUnsubscribed(): bool
    {
        return $this->status === 'unsubscribed';
    }

    public function isBounced(): bool
    {
        return $this->status === 'bounced';
    }

    public function hasComplained(): bool
    {
        return $this->status === 'complained';
    }

    public function confirm(): void
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmation_token' => null,
        ]);
    }

    public function unsubscribe(string $reason = null): void
    {
        $this->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        if ($reason) {
            $this->addTag('unsubscribe_reason:' . $reason);
        }
    }

    public function resubscribe(): void
    {
        $this->update([
            'status' => 'confirmed',
            'unsubscribed_at' => null,
            'confirmed_at' => now(),
        ]);

        $this->removeTagsStartingWith('unsubscribe_reason:');
    }

    public function markAsBounced(): void
    {
        $this->update(['status' => 'bounced']);
    }

    public function markAsComplaint(): void
    {
        $this->update(['status' => 'complained']);
    }

    public function recordEmailSent(): void
    {
        $this->increment('emails_sent');
        $this->update(['last_email_sent_at' => now()]);
    }

    public function recordEmailOpened(): void
    {
        $this->increment('emails_opened');
        $this->update(['last_email_opened_at' => now()]);
    }

    public function recordLinkClicked(): void
    {
        $this->increment('links_clicked');
    }

    public function getOpenRate(): float
    {
        if ($this->emails_sent === 0) {
            return 0.0;
        }

        return round(($this->emails_opened / $this->emails_sent) * 100, 2);
    }

    public function getClickRate(): float
    {
        if ($this->emails_sent === 0) {
            return 0.0;
        }

        return round(($this->links_clicked / $this->emails_sent) * 100, 2);
    }

    public function getEngagementScore(): float
    {
        $openWeight = 0.6;
        $clickWeight = 0.4;

        return round(
            ($this->getOpenRate() * $openWeight) + ($this->getClickRate() * $clickWeight),
            2
        );
    }

    public function isEngaged(): bool
    {
        return $this->emails_opened > 0 || $this->links_clicked > 0;
    }

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status ?? 'Unknown';
    }

    public function getLanguageLabel(): string
    {
        return self::LANGUAGES[$this->language] ?? $this->language ?? 'Unknown';
    }

    public function getSourceLabel(): string
    {
        return self::SOURCES[$this->subscription_source] ?? $this->subscription_source ?? 'Unknown';
    }

    public function getDaysSinceSubscription(): int
    {
        return $this->created_at->diffInDays(now());
    }

    public function getDaysSinceLastEmail(): ?int
    {
        return $this->last_email_sent_at?->diffInDays(now());
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? []);
    }

    public function addTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->update(['tags' => $tags]);
        }
    }

    public function removeTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        $tags = array_filter($tags, fn($t) => $t !== $tag);
        $this->update(['tags' => array_values($tags)]);
    }

    public function removeTagsStartingWith(string $prefix): void
    {
        $tags = $this->tags ?? [];
        $tags = array_filter($tags, fn($t) => !str_starts_with($t, $prefix));
        $this->update(['tags' => array_values($tags)]);
    }

    public function getPreference(string $key, $default = null)
    {
        return data_get($this->preferences, $key, $default);
    }

    public function setPreference(string $key, $value): void
    {
        $preferences = $this->preferences ?? [];
        data_set($preferences, $key, $value);
        $this->update(['preferences' => $preferences]);
    }

    public function generateConfirmationToken(): string
    {
        $token = Str::random(64);
        $this->update(['confirmation_token' => $token]);
        return $token;
    }

    public function generateUnsubscribeToken(): string
    {
        $token = Str::random(64);
        $this->update(['unsubscribe_token' => $token]);
        return $token;
    }

    public function getConfirmationUrl(): string
    {
        $token = $this->confirmation_token ?? $this->generateConfirmationToken();
        return url("/newsletter/confirm/{$token}");
    }

    public function getUnsubscribeUrl(): string
    {
        $token = $this->unsubscribe_token ?? $this->generateUnsubscribeToken();
        return url("/newsletter/unsubscribe/{$token}");
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($subscription) {
            if (!$subscription->confirmation_token) {
                $subscription->confirmation_token = Str::random(64);
            }
            if (!$subscription->unsubscribe_token) {
                $subscription->unsubscribe_token = Str::random(64);
            }
            
            // Set default email tracking values
            if (is_null($subscription->emails_sent)) {
                $subscription->emails_sent = 0;
            }
            if (is_null($subscription->emails_opened)) {
                $subscription->emails_opened = 0;
            }
            if (is_null($subscription->links_clicked)) {
                $subscription->links_clicked = 0;
            }
        });
    }
}
