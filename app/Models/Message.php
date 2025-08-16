<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasOrganization;

class Message extends Model
{
    use HasFactory, HasOrganization;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'priority',
        'message_type',
        'ip_address',
        'user_agent',
        'read_at',
        'replied_at',
        'replied_by_user_id',
        'internal_notes',
        'assigned_to_user_id',
        'organization_id',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'replied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Message statuses
     */
    public const STATUSES = [
        'pending' => 'Pendiente',
        'read' => 'Leído',
        'replied' => 'Respondido',
        'archived' => 'Archivado',
        'spam' => 'Spam',
    ];

    /**
     * Message priorities
     */
    public const PRIORITIES = [
        'low' => 'Baja',
        'normal' => 'Normal',
        'high' => 'Alta',
        'urgent' => 'Urgente',
    ];

    /**
     * Message types
     */
    public const MESSAGE_TYPES = [
        'contact' => 'Contacto',
        'support' => 'Soporte',
        'complaint' => 'Queja',
        'suggestion' => 'Sugerencia',
    ];

    /**
     * Relationships
     */
    public function repliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by_user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    /**
     * Scopes
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->where('status', 'read');
    }

    public function scopeReplied(Builder $query): Builder
    {
        return $query->where('status', 'replied');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', 'archived');
    }

    public function scopeSpam(Builder $query): Builder
    {
        return $query->where('status', 'spam');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending']);
    }

    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('message_type', $type);
    }

    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to_user_id', $userId);
    }

    public function scopeByEmail(Builder $query, string $email): Builder
    {
        return $query->where('email', $email);
    }

    public function scopeOrderByPriority(Builder $query): Builder
    {
        return $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
                     ->orderBy('created_at', 'desc');
    }

    /**
     * Business Logic Methods
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    public function isReplied(): bool
    {
        return !is_null($this->replied_at);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAssigned(): bool
    {
        return !is_null($this->assigned_to_user_id);
    }

    public function isUrgent(): bool
    {
        return $this->priority === 'urgent';
    }

    public function isHighPriority(): bool
    {
        return in_array($this->priority, ['urgent', 'high']);
    }

    public function markAsRead(int $userId = null): void
    {
        $this->update([
            'status' => 'read',
            'read_at' => now(),
        ]);
    }

    public function markAsReplied(int $userId): void
    {
        $this->update([
            'status' => 'replied',
            'replied_at' => now(),
            'replied_by_user_id' => $userId,
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

    public function assignTo(int $userId): void
    {
        $this->update(['assigned_to_user_id' => $userId]);
    }

    public function unassign(): void
    {
        $this->update(['assigned_to_user_id' => null]);
    }

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status ?? 'Unknown';
    }

    public function getPriorityLabel(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority ?? 'Unknown';
    }

    public function getTypeLabel(): string
    {
        return self::MESSAGE_TYPES[$this->message_type] ?? $this->message_type ?? 'Unknown';
    }

    public function getFormattedPhone(): ?string
    {
        if (!$this->phone) {
            return null;
        }

        // Basic phone formatting (you can customize this)
        $phone = preg_replace('/[^0-9+]/', '', $this->phone);
        
        if (strlen($phone) === 9 && !str_starts_with($phone, '+')) {
            return '+34 ' . substr($phone, 0, 3) . ' ' . substr($phone, 3, 3) . ' ' . substr($phone, 6);
        }

        return $this->phone;
    }

    public function getResponseTime(): ?int
    {
        if (!$this->replied_at) {
            return null;
        }

        return $this->created_at->diffInHours($this->replied_at);
    }

    public function hasInternalNotes(): bool
    {
        return !empty($this->internal_notes);
    }

    public function getShortMessage(int $length = 100): string
    {
        return strlen($this->message) > $length 
            ? substr($this->message, 0, $length) . '...'
            : $this->message;
    }
}
