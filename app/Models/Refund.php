<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'refund_code', 'external_refund_id', 'reference', 'user_id', 'payment_id', 'invoice_id', 
        'transaction_id', 'energy_cooperative_id',
        'type', 'reason', 'status', 'refund_amount', 'original_amount', 'processing_fee', 
        'net_refund_amount', 'currency', 'exchange_rate', 'original_currency_amount', 'original_currency',
        'refund_method', 'refund_destination', 'refund_details',
        'gateway', 'gateway_refund_id', 'gateway_response', 'gateway_status',
        'requested_at', 'approved_at', 'processed_at', 'completed_at', 'failed_at', 'expires_at',
        'description', 'customer_reason', 'internal_notes', 'supporting_documents',
        'energy_amount_kwh', 'energy_price_per_kwh', 'energy_service_date', 'energy_contract_id',
        'requested_by_id', 'approved_by_id', 'processed_by_id',
        'requires_approval', 'auto_approved', 'auto_approval_threshold',
        'is_chargeback', 'chargeback_id', 'chargeback_date', 'dispute_details',
        'request_ip', 'user_agent', 'is_test', 'audit_trail',
        'customer_notified', 'customer_notified_at', 'notification_history',
        'metadata', 'failure_reason', 'retry_count', 'next_retry_at'
    ];

    protected $casts = [
        'refund_amount' => 'decimal:4',
        'original_amount' => 'decimal:4',
        'processing_fee' => 'decimal:4',
        'net_refund_amount' => 'decimal:4',
        'exchange_rate' => 'decimal:6',
        'original_currency_amount' => 'decimal:4',
        'energy_amount_kwh' => 'decimal:4',
        'energy_price_per_kwh' => 'decimal:6',
        'auto_approval_threshold' => 'decimal:4',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'expires_at' => 'datetime',
        'energy_service_date' => 'datetime',
        'chargeback_date' => 'datetime',
        'customer_notified_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'refund_details' => 'array',
        'gateway_response' => 'array',
        'supporting_documents' => 'array',
        'audit_trail' => 'array',
        'notification_history' => 'array',
        'metadata' => 'array',
        'requires_approval' => 'boolean',
        'auto_approved' => 'boolean',
        'is_chargeback' => 'boolean',
        'is_test' => 'boolean',
        'customer_notified' => 'boolean',
        'retry_count' => 'integer',
    ];

    // Relaciones

    /**
     * Usuario que recibe el reembolso
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Pago original
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Factura relacionada
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Transacción relacionada
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Cooperativa energética
     */
    public function energyCooperative(): BelongsTo
    {
        return $this->belongsTo(EnergyCooperative::class);
    }

    /**
     * Usuario que solicitó el reembolso
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    /**
     * Usuario que aprobó el reembolso
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    /**
     * Usuario que procesó el reembolso
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_id');
    }

    // Scopes

    /**
     * Scope para reembolsos completados
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope para reembolsos pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para reembolsos que requieren aprobación
     */
    public function scopeRequiringApproval($query)
    {
        return $query->where('requires_approval', true)->whereNull('approved_at');
    }

    /**
     * Scope para reembolsos aprobados
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope por tipo de reembolso
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope por razón
     */
    public function scopeByReason($query, $reason)
    {
        return $query->where('reason', $reason);
    }

    /**
     * Scope para reembolsos de un usuario
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para chargebacks
     */
    public function scopeChargebacks($query)
    {
        return $query->where('is_chargeback', true);
    }

    /**
     * Scope para reembolsos de prueba
     */
    public function scopeTest($query)
    {
        return $query->where('is_test', true);
    }

    /**
     * Scope para reembolsos de producción
     */
    public function scopeProduction($query)
    {
        return $query->where('is_test', false);
    }

    /**
     * Scope para auto-aprobados
     */
    public function scopeAutoApproved($query)
    {
        return $query->where('auto_approved', true);
    }

    // Métodos de ayuda

    /**
     * Verificar si está completado
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Verificar si está pendiente
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Verificar si está aprobado
     */
    public function isApproved(): bool
    {
        return in_array($this->status, ['approved', 'processing', 'completed']);
    }

    /**
     * Verificar si está procesando
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Verificar si falló
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Verificar si requiere aprobación
     */
    public function requiresApproval(): bool
    {
        return $this->requires_approval && !$this->approved_at;
    }

    /**
     * Verificar si fue auto-aprobado
     */
    public function wasAutoApproved(): bool
    {
        return $this->auto_approved;
    }

    /**
     * Verificar si es un chargeback
     */
    public function isChargeback(): bool
    {
        return $this->is_chargeback;
    }

    /**
     * Verificar si es reembolso completo
     */
    public function isFullRefund(): bool
    {
        return $this->type === 'full' || $this->refund_amount >= $this->original_amount;
    }

    /**
     * Verificar si es reembolso parcial
     */
    public function isPartialRefund(): bool
    {
        return $this->type === 'partial' && $this->refund_amount < $this->original_amount;
    }

    /**
     * Verificar si está expirado
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    /**
     * Verificar si puede ser reintentado
     */
    public function canRetry(): bool
    {
        return $this->isFailed() && 
               $this->retry_count < 3 && 
               (!$this->next_retry_at || $this->next_retry_at <= now());
    }

    /**
     * Obtener porcentaje del reembolso
     */
    public function getRefundPercentage(): float
    {
        if ($this->original_amount == 0) {
            return 0;
        }
        
        return ($this->refund_amount / $this->original_amount) * 100;
    }

    /**
     * Aprobar reembolso
     */
    public function approve(User $approver, string $notes = null): bool
    {
        if (!$this->requiresApproval()) {
            return false;
        }

        return $this->update([
            'status' => 'approved',
            'approved_by_id' => $approver->id,
            'approved_at' => now(),
            'internal_notes' => $notes ? ($this->internal_notes . "\n" . $notes) : $this->internal_notes
        ]);
    }

    /**
     * Marcar como procesando
     */
    public function markAsProcessing(User $processor = null): void
    {
        $this->update([
            'status' => 'processing',
            'processed_at' => now(),
            'processed_by_id' => $processor?->id
        ]);
    }

    /**
     * Marcar como completado
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
    }

    /**
     * Marcar como fallido
     */
    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => $reason,
            'retry_count' => $this->retry_count + 1,
            'next_retry_at' => now()->addHours(24 * $this->retry_count) // Incrementar el tiempo entre reintentos
        ]);
    }

    /**
     * Notificar al cliente
     */
    public function notifyCustomer(): void
    {
        $this->update([
            'customer_notified' => true,
            'customer_notified_at' => now(),
            'notification_history' => array_merge($this->notification_history ?? [], [
                [
                    'type' => 'status_update',
                    'status' => $this->status,
                    'notified_at' => now()->toISOString()
                ]
            ])
        ]);
    }

    /**
     * Obtener estado formateado
     */
    public function getFormattedStatus(): string
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'approved' => 'Aprobado',
            'processing' => 'Procesando',
            'completed' => 'Completado',
            'failed' => 'Fallido',
            'cancelled' => 'Cancelado',
            'disputed' => 'En Disputa',
            'expired' => 'Expirado',
            default => ucfirst($this->status)
        };
    }

    /**
     * Obtener tipo formateado
     */
    public function getFormattedType(): string
    {
        return match($this->type) {
            'full' => 'Completo',
            'partial' => 'Parcial',
            'processing_fee' => 'Comisión de Procesamiento',
            'overpayment' => 'Sobrepago',
            'cancellation' => 'Cancelación',
            'dispute' => 'Disputa',
            'chargeback' => 'Chargeback',
            'energy_not_delivered' => 'Energía No Entregada',
            'service_failure' => 'Fallo del Servicio',
            default => ucfirst($this->type)
        };
    }

    /**
     * Obtener razón formateada
     */
    public function getFormattedReason(): string
    {
        return match($this->reason) {
            'customer_request' => 'Solicitud del Cliente',
            'service_cancellation' => 'Cancelación de Servicio',
            'energy_not_delivered' => 'Energía No Entregada',
            'service_failure' => 'Fallo del Servicio',
            'billing_error' => 'Error de Facturación',
            'overpayment' => 'Sobrepago',
            'dispute_resolution' => 'Resolución de Disputa',
            'chargeback' => 'Chargeback',
            'technical_error' => 'Error Técnico',
            'admin_adjustment' => 'Ajuste Administrativo',
            'duplicate_payment' => 'Pago Duplicado',
            default => ucfirst($this->reason)
        };
    }

    /**
     * Generar código único de reembolso
     */
    public static function generateRefundCode(): string
    {
        do {
            $code = 'RFD-' . date('Y') . '-' . str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('refund_code', $code)->exists());

        return $code;
    }
}