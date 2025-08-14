<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionRequest extends Model
{
    use HasFactory;

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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Enums para status y type
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_IN_REVIEW = 'in_review';

    const TYPE_NEW_SUBSCRIPTION = 'new_subscription';
    const TYPE_OWNERSHIP_CHANGE = 'ownership_change';
    const TYPE_TENANT_REQUEST = 'tenant_request';

    /**
     * Obtener el usuario que hizo la solicitud
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener la cooperativa/organización
     */
    public function cooperative(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'cooperative_id');
    }

    /**
     * Marcar como enviada
     */
    public function markAsSubmitted(): void
    {
        $this->update(['submitted_at' => now()]);
    }

    /**
     * Marcar como procesada
     */
    public function markAsProcessed(): void
    {
        $this->update(['processed_at' => now()]);
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
     * Poner en revisión
     */
    public function markForReview(): void
    {
        $this->update(['status' => self::STATUS_IN_REVIEW]);
    }

    /**
     * Obtener solicitudes pendientes
     */
    public static function getPending(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('status', self::STATUS_PENDING)->get();
    }

    /**
     * Obtener solicitudes por estado
     */
    public static function getByStatus(string $status): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('status', $status)->get();
    }

    /**
     * Obtener solicitudes por tipo
     */
    public static function getByType(string $type): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('type', $type)->get();
    }
}
