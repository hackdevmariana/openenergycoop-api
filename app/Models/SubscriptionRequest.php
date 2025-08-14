<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionRequest extends Model
{
    use HasFactory;

    // Estados de la solicitud
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_IN_REVIEW = 'in_review';

    // Tipos de solicitud
    const TYPE_NEW_SUBSCRIPTION = 'new_subscription';
    const TYPE_OWNERSHIP_CHANGE = 'ownership_change';
    const TYPE_TENANT_REQUEST = 'tenant_request';

    protected $fillable = [
        'user_id',
        'cooperative_id',
        'status',
        'type',
        'submitted_at',
        'processed_at',
        'notes',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    /**
     * Relación con el usuario que hizo la solicitud
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la cooperativa/organización
     */
    public function cooperative(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'cooperative_id');
    }

    /**
     * Scope para filtrar por estado
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para filtrar por tipo
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para filtrar por usuario
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para filtrar por cooperativa
     */
    public function scopeByCooperative(Builder $query, int $cooperativeId): Builder
    {
        return $query->where('cooperative_id', $cooperativeId);
    }

    /**
     * Scope para búsqueda en notas
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where('notes', 'like', "%{$search}%");
    }

    /**
     * Aprobar la solicitud
     */
    public function approve(): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'processed_at' => now(),
        ]);
    }

    /**
     * Rechazar la solicitud
     */
    public function reject(): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'processed_at' => now(),
        ]);
    }

    /**
     * Poner en revisión la solicitud
     */
    public function markForReview(): void
    {
        $this->update([
            'status' => self::STATUS_IN_REVIEW,
        ]);
    }

    /**
     * Verificar si la solicitud está pendiente
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verificar si la solicitud está aprobada
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Verificar si la solicitud está rechazada
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Verificar si la solicitud está en revisión
     */
    public function isInReview(): bool
    {
        return $this->status === self::STATUS_IN_REVIEW;
    }

    /**
     * Verificar si la solicitud puede ser procesada
     */
    public function canBeProcessed(): bool
    {
        return $this->isPending() || $this->isInReview();
    }

    /**
     * Obtener el estado en español
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_APPROVED => 'Aprobada',
            self::STATUS_REJECTED => 'Rechazada',
            self::STATUS_IN_REVIEW => 'En Revisión',
            default => 'Desconocido',
        };
    }

    /**
     * Obtener el tipo en español
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_NEW_SUBSCRIPTION => 'Nueva Suscripción',
            self::TYPE_OWNERSHIP_CHANGE => 'Cambio de Titularidad',
            self::TYPE_TENANT_REQUEST => 'Solicitud de Arrendatario',
            default => 'Desconocido',
        };
    }
}
