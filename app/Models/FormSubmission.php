<?php

namespace App\Models;

use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends Model
{
    use HasFactory, HasOrganization;

    protected $fillable = [
        'form_name',
        'fields',
        'status',
        'source_url',
        'referrer',
        'ip_address',
        'user_agent',
        'processed_at',
        'processed_by_user_id',
        'processing_notes',
        'organization_id',
    ];

    protected $casts = [
        'fields' => 'array',
        'processed_at' => 'datetime',
    ];

    const STATUSES = [
        'pending' => 'Pendiente',
        'processed' => 'Procesado',
        'archived' => 'Archivado',
        'spam' => 'Spam',
    ];

    const FORM_TYPES = [
        'contact' => 'Formulario de Contacto',
        'newsletter' => 'Suscripción Newsletter',
        'survey' => 'Encuesta',
        'feedback' => 'Retroalimentación',
        'support' => 'Soporte',
        'registration' => 'Registro',
        'quote' => 'Solicitud de Cotización',
        'partnership' => 'Solicitud de Colaboración',
        'job_application' => 'Aplicación de Trabajo',
        'volunteer' => 'Voluntariado',
        'general' => 'General',
    ];

    // Relationships
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }

    // Scopes
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessed(Builder $query): Builder
    {
        return $query->where('status', 'processed');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', 'archived');
    }

    public function scopeSpam(Builder $query): Builder
    {
        return $query->where('status', 'spam');
    }

    public function scopeByFormType(Builder $query, string $formType): Builder
    {
        return $query->where('form_name', $formType);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeFromIp(Builder $query, string $ip): Builder
    {
        return $query->where('ip_address', $ip);
    }

    public function scopeProcessedBy(Builder $query, int $userId): Builder
    {
        return $query->where('processed_by_user_id', $userId);
    }

    public function scopeUnprocessed(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    // Business Logic Methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function isSpam(): bool
    {
        return $this->status === 'spam';
    }

    public function hasBeenProcessed(): bool
    {
        return !is_null($this->processed_at);
    }

    public function markAsProcessed(int $userId, string $notes = null): void
    {
        $this->update([
            'status' => 'processed',
            'processed_at' => now(),
            'processed_by_user_id' => $userId,
            'processing_notes' => $notes,
        ]);
    }

    public function markAsSpam(): void
    {
        $this->update(['status' => 'spam']);
    }

    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    public function reopen(): void
    {
        $this->update([
            'status' => 'pending',
            'processed_at' => null,
            'processed_by_user_id' => null,
            'processing_notes' => null,
        ]);
    }

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status ?? 'Unknown';
    }

    public function getFormTypeLabel(): string
    {
        return self::FORM_TYPES[$this->form_name] ?? $this->form_name ?? 'Unknown';
    }

    public function getField(string $key, $default = null)
    {
        return data_get($this->fields, $key, $default);
    }

    public function setField(string $key, $value): void
    {
        $fields = $this->fields ?? [];
        data_set($fields, $key, $value);
        $this->update(['fields' => $fields]);
    }

    public function getName(): ?string
    {
        return $this->getField('name') ?? $this->getField('full_name') ?? $this->getField('nombre');
    }

    public function getEmail(): ?string
    {
        return $this->getField('email') ?? $this->getField('email_address') ?? $this->getField('correo');
    }

    public function getPhone(): ?string
    {
        return $this->getField('phone') ?? $this->getField('phone_number') ?? $this->getField('telefono');
    }

    public function getMessage(): ?string
    {
        return $this->getField('message') ?? $this->getField('content') ?? $this->getField('mensaje') ?? $this->getField('comments');
    }

    public function getSubject(): ?string
    {
        return $this->getField('subject') ?? $this->getField('topic') ?? $this->getField('asunto');
    }

    public function getDaysSinceSubmission(): int
    {
        return $this->created_at->diffInDays(now());
    }

    public function getProcessingTime(): ?int
    {
        if (!$this->processed_at) {
            return null;
        }

        return $this->created_at->diffInHours($this->processed_at);
    }

    public function getSourceDomain(): ?string
    {
        if (!$this->source_url) {
            return null;
        }

        $parsed = parse_url($this->source_url);
        return $parsed['host'] ?? null;
    }

    public function getReferrerDomain(): ?string
    {
        if (!$this->referrer) {
            return null;
        }

        $parsed = parse_url($this->referrer);
        return $parsed['host'] ?? null;
    }

    public function getUserAgent(): ?string
    {
        return $this->user_agent;
    }

    public function getBrowser(): ?string
    {
        if (!$this->user_agent) {
            return null;
        }

        // Simple browser detection
        if (strpos($this->user_agent, 'Chrome') !== false) {
            return 'Chrome';
        } elseif (strpos($this->user_agent, 'Firefox') !== false) {
            return 'Firefox';
        } elseif (strpos($this->user_agent, 'Safari') !== false) {
            return 'Safari';
        } elseif (strpos($this->user_agent, 'Edge') !== false) {
            return 'Edge';
        }

        return 'Other';
    }

    public function isMobile(): bool
    {
        if (!$this->user_agent) {
            return false;
        }

        return preg_match('/Mobile|Android|iPhone|iPad/', $this->user_agent) === 1;
    }

    public function hasRequiredFields(): bool
    {
        return !is_null($this->getName()) || !is_null($this->getEmail());
    }

    public function isPotentialSpam(): bool
    {
        $fields = $this->fields ?? [];
        $allText = implode(' ', array_filter($fields, 'is_string'));

        // Check for common spam patterns
        $spamPatterns = [
            '/\b(viagra|cialis|casino|poker|loan|mortgage)\b/i',
            '/\b(click here|visit now|act now|limited time)\b/i',
            '/https?:\/\/[^\s]+.*https?:\/\/[^\s]+/', // Multiple URLs
        ];

        foreach ($spamPatterns as $pattern) {
            if (preg_match($pattern, $allText)) {
                return true;
            }
        }

        return false;
    }

    public function getFieldCount(): int
    {
        return count($this->fields ?? []);
    }

    public function hasField(string $key): bool
    {
        return array_key_exists($key, $this->fields ?? []);
    }
}