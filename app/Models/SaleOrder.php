<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'total_amount',
        'subtotal_amount',
        'tax_amount',
        'discount_amount',
        'shipping_amount',
        'final_amount',
        'currency',
        'payment_method',
        'payment_status',
        'shipping_address',
        'billing_address',
        'notes',
        'affiliate_id',
        'discount_code_id',
        'processed_at',
        'cancelled_at',
        'refunded_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'processed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    // Enums
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_FAILED = 'failed';
    const PAYMENT_STATUS_REFUNDED = 'refunded';
    const PAYMENT_STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

    const PAYMENT_METHOD_CREDIT_CARD = 'credit_card';
    const PAYMENT_METHOD_BANK_TRANSFER = 'bank_transfer';
    const PAYMENT_METHOD_CRYPTO = 'crypto';
    const PAYMENT_METHOD_WALLET = 'wallet';
    const PAYMENT_METHOD_SEPA = 'sepa';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Borrador',
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_CONFIRMED => 'Confirmado',
            self::STATUS_PROCESSING => 'Procesando',
            self::STATUS_SHIPPED => 'Enviado',
            self::STATUS_DELIVERED => 'Entregado',
            self::STATUS_CANCELLED => 'Cancelado',
            self::STATUS_REFUNDED => 'Reembolsado',
        ];
    }

    public static function getPaymentStatuses(): array
    {
        return [
            self::PAYMENT_STATUS_PENDING => 'Pendiente',
            self::PAYMENT_STATUS_PAID => 'Pagado',
            self::PAYMENT_STATUS_FAILED => 'Fallido',
            self::PAYMENT_STATUS_REFUNDED => 'Reembolsado',
            self::PAYMENT_STATUS_PARTIALLY_REFUNDED => 'Parcialmente Reembolsado',
        ];
    }

    public static function getPaymentMethods(): array
    {
        return [
            self::PAYMENT_METHOD_CREDIT_CARD => 'Tarjeta de Crédito',
            self::PAYMENT_METHOD_BANK_TRANSFER => 'Transferencia Bancaria',
            self::PAYMENT_METHOD_CRYPTO => 'Criptomoneda',
            self::PAYMENT_METHOD_WALLET => 'Wallet',
            self::PAYMENT_METHOD_SEPA => 'SEPA',
        ];
    }

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function discountCode(): BelongsTo
    {
        return $this->belongsTo(DiscountCode::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleOrderItem::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(SaleOrderStatusLog::class, 'entity_id');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Métodos
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isShipped(): bool
    {
        return $this->status === self::STATUS_SHIPPED;
    }

    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
        ]);
    }

    public function canBeRefunded(): bool
    {
        return $this->isPaid() && !$this->isRefunded();
    }

    public function getTotalItemsCount(): int
    {
        return $this->items->sum('quantity');
    }

    public function calculateTotals(): void
    {
        $this->subtotal_amount = $this->items->sum(function($item) {
            return $item->quantity * $item->unit_price;
        });

        $this->total_amount = $this->subtotal_amount + $this->tax_amount + $this->shipping_amount;
        $this->final_amount = $this->total_amount - $this->discount_amount;

        $this->save();
    }

    public function applyDiscountCode(DiscountCode $discountCode): float
    {
        if (!$discountCode->canApplyTo($this)) {
            return 0;
        }

        $discount = $discountCode->calculateDiscount($this->subtotal_amount);
        $this->discount_amount = $discount;
        $this->discount_code_id = $discountCode->id;
        
        $this->calculateTotals();
        
        return $discount;
    }

    public function markAsPaid(): void
    {
        $this->payment_status = self::PAYMENT_STATUS_PAID;
        $this->processed_at = now();
        $this->save();
    }

    public function cancel(): void
    {
        if (!$this->canBeCancelled()) {
            throw new \Exception('No se puede cancelar este pedido');
        }

        $this->status = self::STATUS_CANCELLED;
        $this->cancelled_at = now();
        $this->save();
    }

    public function getFormattedStatus(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getFormattedPaymentStatus(): string
    {
        return self::getPaymentStatuses()[$this->payment_status] ?? 'Desconocido';
    }
}
