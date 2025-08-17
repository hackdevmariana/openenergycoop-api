<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_code', 'external_id', 'user_id', 'invoice_id', 'type', 'status', 'method',
        'amount', 'fee', 'net_amount', 'currency', 'exchange_rate', 'original_amount', 'original_currency',
        'gateway', 'gateway_transaction_id', 'gateway_response', 'gateway_metadata',
        'card_last_four', 'card_brand', 'payment_method_id',
        'processed_at', 'failed_at', 'expires_at', 'authorized_at', 'captured_at',
        'description', 'reference', 'metadata', 'failure_reason', 'notes',
        'energy_cooperative_id', 'energy_contract_id', 'energy_amount_kwh',
        'created_by_id', 'ip_address', 'user_agent', 'is_test', 'is_recurring', 'parent_payment_id'
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'fee' => 'decimal:4',
        'net_amount' => 'decimal:4',
        'exchange_rate' => 'decimal:6',
        'original_amount' => 'decimal:4',
        'energy_amount_kwh' => 'decimal:4',
        'gateway_response' => 'array',
        'gateway_metadata' => 'array',
        'metadata' => 'array',
        'processed_at' => 'datetime',
        'failed_at' => 'datetime',
        'expires_at' => 'datetime',
        'authorized_at' => 'datetime',
        'captured_at' => 'datetime',
        'is_test' => 'boolean',
        'is_recurring' => 'boolean',
    ];

    // Relaciones

    /**
     * Usuario que realizó el pago
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Factura asociada al pago
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Cooperativa energética asociada
     */
    public function energyCooperative(): BelongsTo
    {
        return $this->belongsTo(EnergyCooperative::class);
    }

    /**
     * Usuario que creó el registro
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Pago padre (para pagos recurrentes)
     */
    public function parentPayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'parent_payment_id');
    }

    /**
     * Pagos hijos (para pagos recurrentes)
     */
    public function childPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'parent_payment_id');
    }

    /**
     * Transacciones asociadas al pago
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Reembolsos del pago
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    // Scopes

    /**
     * Scope para pagos completados
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope para pagos pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para pagos fallidos
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope para pagos de un usuario específico
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para pagos por método
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('method', $method);
    }

    /**
     * Scope para pagos por gateway
     */
    public function scopeByGateway($query, $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    /**
     * Scope para pagos de prueba
     */
    public function scopeTest($query)
    {
        return $query->where('is_test', true);
    }

    /**
     * Scope para pagos de producción
     */
    public function scopeProduction($query)
    {
        return $query->where('is_test', false);
    }

    // Métodos de ayuda

    /**
     * Verificar si el pago está completado
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Verificar si el pago está pendiente
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Verificar si el pago falló
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Verificar si el pago está en procesamiento
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Verificar si el pago es reembolsable
     */
    public function isRefundable(): bool
    {
        return $this->isCompleted() && 
               $this->refunds()->sum('refund_amount') < $this->amount;
    }

    /**
     * Obtener el monto total reembolsado
     */
    public function getTotalRefundedAmount(): float
    {
        return $this->refunds()->where('status', 'completed')->sum('refund_amount');
    }

    /**
     * Obtener el monto disponible para reembolso
     */
    public function getRefundableAmount(): float
    {
        return $this->amount - $this->getTotalRefundedAmount();
    }

    /**
     * Verificar si es un pago recurrente
     */
    public function isRecurring(): bool
    {
        return $this->is_recurring;
    }

    /**
     * Verificar si es un pago de prueba
     */
    public function isTest(): bool
    {
        return $this->is_test;
    }

    /**
     * Obtener información formateada del método de pago
     */
    public function getFormattedPaymentMethod(): string
    {
        return match($this->method) {
            'card' => $this->card_brand ? "{$this->card_brand} ****{$this->card_last_four}" : 'Tarjeta',
            'bank_transfer' => 'Transferencia Bancaria',
            'paypal' => 'PayPal',
            'stripe' => 'Stripe',
            'energy_credits' => 'Créditos Energéticos',
            'wallet' => 'Wallet',
            default => ucfirst($this->method)
        };
    }

    /**
     * Obtener el estado formateado
     */
    public function getFormattedStatus(): string
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'processing' => 'Procesando',
            'completed' => 'Completado',
            'failed' => 'Fallido',
            'cancelled' => 'Cancelado',
            'expired' => 'Expirado',
            default => ucfirst($this->status)
        };
    }

    /**
     * Generar código único para el pago
     */
    public static function generatePaymentCode(): string
    {
        do {
            $code = 'PAY-' . date('Y') . '-' . str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('payment_code', $code)->exists());

        return $code;
    }
}