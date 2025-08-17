<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code', 'reference', 'user_id', 'related_user_id', 'transaction_id', 'energy_cooperative_id',
        'type', 'subtype', 'status', 'token_type', 'amount', 'rate', 'currency', 'equivalent_value',
        'balance_before', 'balance_after',
        'energy_amount_kwh', 'energy_price_per_kwh', 'energy_source', 'is_renewable', 
        'energy_generation_date', 'energy_consumption_date',
        'processed_at', 'expires_at', 'locked_until', 'available_at',
        'description', 'notes', 'metadata',
        'source_wallet_id', 'source_transaction_code', 'source_amount', 'source_token_type',
        'has_expiration', 'expiration_days', 'is_locked', 'lock_reason',
        'requires_approval', 'approved_by_id', 'approved_at', 'approval_notes',
        'created_by_id', 'ip_address', 'user_agent', 'is_internal', 'is_test',
        'is_reversible', 'reversal_transaction_id', 'reversed_at', 'reversal_reason',
        'batch_id', 'batch_sequence'
    ];

    protected $casts = [
        'amount' => 'decimal:6',
        'rate' => 'decimal:6',
        'equivalent_value' => 'decimal:4',
        'balance_before' => 'decimal:6',
        'balance_after' => 'decimal:6',
        'energy_amount_kwh' => 'decimal:4',
        'energy_price_per_kwh' => 'decimal:6',
        'source_amount' => 'decimal:6',
        'processed_at' => 'datetime',
        'expires_at' => 'datetime',
        'locked_until' => 'datetime',
        'available_at' => 'datetime',
        'energy_generation_date' => 'datetime',
        'energy_consumption_date' => 'datetime',
        'approved_at' => 'datetime',
        'reversed_at' => 'datetime',
        'metadata' => 'array',
        'has_expiration' => 'boolean',
        'is_locked' => 'boolean',
        'requires_approval' => 'boolean',
        'is_internal' => 'boolean',
        'is_test' => 'boolean',
        'is_reversible' => 'boolean',
        'is_renewable' => 'boolean',
        'expiration_days' => 'integer',
        'batch_sequence' => 'integer',
    ];

    // Relaciones

    /**
     * Usuario propietario del wallet
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Usuario relacionado (para transferencias)
     */
    public function relatedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }

    /**
     * Transacción principal relacionada
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
     * Usuario que aprobó la transacción
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    /**
     * Usuario que creó la transacción
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Transacción de reversión
     */
    public function reversalTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class, 'reversal_transaction_id');
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
     * Scope por tipo de token
     */
    public function scopeByTokenType($query, $tokenType)
    {
        return $query->where('token_type', $tokenType);
    }

    /**
     * Scope por tipo de transacción
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para créditos (entradas)
     */
    public function scopeCredits($query)
    {
        return $query->whereIn('type', ['credit', 'transfer_in', 'reward', 'bonus']);
    }

    /**
     * Scope para débitos (salidas)
     */
    public function scopeDebits($query)
    {
        return $query->whereIn('type', ['debit', 'transfer_out', 'purchase', 'penalty']);
    }

    /**
     * Scope para transacciones de un usuario
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para transacciones bloqueadas
     */
    public function scopeLocked($query)
    {
        return $query->where('is_locked', true);
    }

    /**
     * Scope para transacciones disponibles
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_locked', false)
                    ->where(function ($q) {
                        $q->whereNull('available_at')
                          ->orWhere('available_at', '<=', now());
                    });
    }

    /**
     * Scope para transacciones expiradas
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope para transacciones que expiran pronto
     */
    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('expires_at', '>', now())
                    ->where('expires_at', '<=', now()->addDays($days));
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
     * Scope para transacciones energéticas
     */
    public function scopeEnergyRelated($query)
    {
        return $query->whereNotNull('energy_amount_kwh');
    }

    /**
     * Scope por lote
     */
    public function scopeByBatch($query, $batchId)
    {
        return $query->where('batch_id', $batchId);
    }

    // Métodos de ayuda

    /**
     * Verificar si está completada
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
     * Verificar si está bloqueada
     */
    public function isLocked(): bool
    {
        return $this->is_locked || ($this->locked_until && $this->locked_until > now());
    }

    /**
     * Verificar si está disponible
     */
    public function isAvailable(): bool
    {
        return !$this->isLocked() && 
               (!$this->available_at || $this->available_at <= now()) &&
               !$this->isExpired();
    }

    /**
     * Verificar si está expirada
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    /**
     * Verificar si expira pronto
     */
    public function expiresSoon($days = 7): bool
    {
        return $this->expires_at && 
               $this->expires_at > now() && 
               $this->expires_at <= now()->addDays($days);
    }

    /**
     * Verificar si es un crédito (entrada)
     */
    public function isCredit(): bool
    {
        return in_array($this->type, ['credit', 'transfer_in', 'reward', 'bonus', 'energy_credit']);
    }

    /**
     * Verificar si es un débito (salida)
     */
    public function isDebit(): bool
    {
        return in_array($this->type, ['debit', 'transfer_out', 'purchase', 'penalty', 'energy_debit']);
    }

    /**
     * Verificar si es una transferencia
     */
    public function isTransfer(): bool
    {
        return in_array($this->type, ['transfer_in', 'transfer_out']);
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
     * Verificar si es de energía renovable
     */
    public function isRenewableEnergy(): bool
    {
        return $this->is_renewable === true;
    }

    /**
     * Obtener el impacto en el balance
     */
    public function getBalanceImpact(): float
    {
        if ($this->balance_before !== null && $this->balance_after !== null) {
            return $this->balance_after - $this->balance_before;
        }
        
        return $this->isCredit() ? $this->amount : -$this->amount;
    }

    /**
     * Obtener días hasta expiración
     */
    public function getDaysUntilExpiration(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }
        
        return max(0, now()->diffInDays($this->expires_at, false));
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
            'approval_notes' => $notes
        ]);
    }

    /**
     * Bloquear transacción
     */
    public function lock(string $reason = null, $until = null): void
    {
        $this->update([
            'is_locked' => true,
            'lock_reason' => $reason,
            'locked_until' => $until
        ]);
    }

    /**
     * Desbloquear transacción
     */
    public function unlock(): void
    {
        $this->update([
            'is_locked' => false,
            'lock_reason' => null,
            'locked_until' => null
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
            'expired' => 'Expirada',
            default => ucfirst($this->status)
        };
    }

    /**
     * Obtener tipo de token formateado
     */
    public function getFormattedTokenType(): string
    {
        return match($this->token_type) {
            'energy_credit' => 'Crédito Energético',
            'carbon_credit' => 'Crédito de Carbono',
            'loyalty_point' => 'Punto de Lealtad',
            'service_credit' => 'Crédito de Servicio',
            'cash_equivalent' => 'Equivalente en Efectivo',
            default => ucfirst($this->token_type)
        };
    }

    /**
     * Generar código único de transacción de wallet
     */
    public static function generateTransactionCode(): string
    {
        do {
            $code = 'WTX-' . date('Y') . '-' . str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('transaction_code', $code)->exists());

        return $code;
    }
}