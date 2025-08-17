<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code', 'reference', 'batch_id', 'user_id', 'payment_id', 'invoice_id', 'energy_cooperative_id',
        'type', 'category', 'status', 'amount', 'fee', 'net_amount', 'currency',
        'exchange_rate', 'original_amount', 'original_currency',
        'from_account_type', 'from_account_id', 'to_account_type', 'to_account_id',
        'balance_before', 'balance_after',
        'energy_amount_kwh', 'energy_price_per_kwh', 'energy_contract_id', 'energy_delivery_date',
        'processed_at', 'settled_at', 'failed_at', 'cancelled_at', 'expires_at',
        'processor', 'processor_transaction_id', 'processor_response', 'authorization_code',
        'description', 'notes', 'metadata', 'failure_reason',
        'created_by_id', 'approved_by_id', 'approved_at', 'ip_address', 'user_agent',
        'is_internal', 'is_test', 'is_recurring', 'requires_approval', 'is_reversible',
        'parent_transaction_id', 'reversal_transaction_id'
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'fee' => 'decimal:4',
        'net_amount' => 'decimal:4',
        'exchange_rate' => 'decimal:6',
        'original_amount' => 'decimal:4',
        'balance_before' => 'decimal:4',
        'balance_after' => 'decimal:4',
        'energy_amount_kwh' => 'decimal:4',
        'energy_price_per_kwh' => 'decimal:6',
        'processed_at' => 'datetime',
        'settled_at' => 'datetime',
        'failed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'expires_at' => 'datetime',
        'energy_delivery_date' => 'datetime',
        'approved_at' => 'datetime',
        'processor_response' => 'array',
        'metadata' => 'array',
        'is_internal' => 'boolean',
        'is_test' => 'boolean',
        'is_recurring' => 'boolean',
        'requires_approval' => 'boolean',
        'is_reversible' => 'boolean',
    ];

    // Relaciones

    /**
     * Usuario de la transacción
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Pago asociado
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Factura asociada
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Cooperativa energética
     */
    public function energyCooperative(): BelongsTo
    {
        return $this->belongsTo(EnergyCooperative::class);
    }

    /**
     * Usuario que creó la transacción
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Usuario que aprobó la transacción
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    /**
     * Transacción padre
     */
    public function parentTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'parent_transaction_id');
    }

    /**
     * Transacciones hijas
     */
    public function childTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'parent_transaction_id');
    }

    /**
     * Transacción de reversión
     */
    public function reversalTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'reversal_transaction_id');
    }

    /**
     * Transacciones de wallet relacionadas
     */
    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Reembolsos relacionados
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    // Scopes

    /**
     * Scope para transacciones completadas
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope para transacciones pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para transacciones fallidas
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope por tipo de transacción
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope por categoría
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope para transacciones de un usuario
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para transacciones internas
     */
    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    /**
     * Scope para transacciones externas
     */
    public function scopeExternal($query)
    {
        return $query->where('is_internal', false);
    }

    /**
     * Scope para transacciones de prueba
     */
    public function scopeTest($query)
    {
        return $query->where('is_test', true);
    }

    /**
     * Scope para transacciones de producción
     */
    public function scopeProduction($query)
    {
        return $query->where('is_test', false);
    }

    /**
     * Scope para transacciones que requieren aprobación
     */
    public function scopeRequiringApproval($query)
    {
        return $query->where('requires_approval', true)->whereNull('approved_at');
    }

    /**
     * Scope para transacciones por lote
     */
    public function scopeByBatch($query, $batchId)
    {
        return $query->where('batch_id', $batchId);
    }

    // Métodos de ayuda

    /**
     * Verificar si la transacción está completada
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
     * Verificar si falló
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Verificar si está procesando
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Verificar si está cancelada
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Verificar si es reversible
     */
    public function isReversible(): bool
    {
        return $this->is_reversible && $this->isCompleted() && !$this->reversal_transaction_id;
    }

    /**
     * Verificar si requiere aprobación
     */
    public function requiresApproval(): bool
    {
        return $this->requires_approval && !$this->approved_at;
    }

    /**
     * Verificar si está aprobada
     */
    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    /**
     * Verificar si es una transacción energética
     */
    public function isEnergyTransaction(): bool
    {
        return in_array($this->type, ['energy_purchase', 'energy_sale']) || 
               $this->energy_amount_kwh !== null;
    }

    /**
     * Verificar si es interna
     */
    public function isInternal(): bool
    {
        return $this->is_internal;
    }

    /**
     * Verificar si es de prueba
     */
    public function isTest(): bool
    {
        return $this->is_test;
    }

    /**
     * Obtener el impacto en el balance
     */
    public function getBalanceImpact(): float
    {
        if ($this->balance_before !== null && $this->balance_after !== null) {
            return $this->balance_after - $this->balance_before;
        }
        
        // Calcular basado en el tipo
        return match($this->type) {
            'payment', 'deposit', 'bonus', 'energy_sale' => $this->net_amount,
            'refund', 'withdrawal', 'fee', 'penalty', 'energy_purchase' => -$this->net_amount,
            default => 0
        };
    }

    /**
     * Aprobar transacción
     */
    public function approve(User $approver, string $notes = null): bool
    {
        if (!$this->requiresApproval()) {
            return false;
        }

        return $this->update([
            'approved_by_id' => $approver->id,
            'approved_at' => now(),
            'notes' => $notes ? ($this->notes . "\n" . $notes) : $this->notes
        ]);
    }

    /**
     * Marcar como procesada
     */
    public function markAsProcessed(): void
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now()
        ]);
    }

    /**
     * Marcar como fallida
     */
    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => $reason
        ]);
    }

    /**
     * Cancelar transacción
     */
    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'failure_reason' => $reason
        ]);
    }

    /**
     * Obtener estado formateado
     */
    public function getFormattedStatus(): string
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'processing' => 'Procesando',
            'completed' => 'Completada',
            'failed' => 'Fallida',
            'cancelled' => 'Cancelada',
            'reversed' => 'Revertida',
            default => ucfirst($this->status)
        };
    }

    /**
     * Obtener tipo formateado
     */
    public function getFormattedType(): string
    {
        return match($this->type) {
            'payment' => 'Pago',
            'refund' => 'Reembolso',
            'transfer' => 'Transferencia',
            'fee' => 'Comisión',
            'commission' => 'Comisión',
            'bonus' => 'Bonus',
            'penalty' => 'Penalización',
            'adjustment' => 'Ajuste',
            'energy_purchase' => 'Compra de Energía',
            'energy_sale' => 'Venta de Energía',
            'subscription_fee' => 'Cuota de Suscripción',
            'membership_fee' => 'Cuota de Membresía',
            'deposit' => 'Depósito',
            'withdrawal' => 'Retiro',
            default => ucfirst($this->type)
        };
    }

    /**
     * Generar código único de transacción
     */
    public static function generateTransactionCode(): string
    {
        do {
            $code = 'TXN-' . date('Y') . '-' . str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('transaction_code', $code)->exists());

        return $code;
    }
}