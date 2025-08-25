<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class SaleOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'affiliate_id',
        'order_type',
        'status',
        'payment_status',
        'shipping_status',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'refunded_amount',
        'outstanding_amount',
        'currency',
        'exchange_rate',
        'payment_method',
        'payment_reference',
        'payment_date',
        'shipping_method',
        'tracking_number',
        'shipped_date',
        'delivered_date',
        'expected_delivery_date',
        'shipping_address',
        'billing_address',
        'special_instructions',
        'internal_notes',
        'order_items',
        'applied_discounts',
        'shipping_details',
        'customer_notes',
        'tags',
        'created_by',
        'processed_by',
        'shipped_by',
        'delivered_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'payment_date' => 'datetime',
        'shipped_date' => 'datetime',
        'delivered_date' => 'datetime',
        'expected_delivery_date' => 'date',
        'order_items' => 'array',
        'applied_discounts' => 'array',
        'shipping_details' => 'array',
        'customer_notes' => 'array',
        'tags' => 'array',
    ];

    // Enums
    const ORDER_TYPE_STANDARD = 'standard';
    const ORDER_TYPE_PRE_ORDER = 'pre_order';
    const ORDER_TYPE_SUBSCRIPTION = 'subscription';
    const ORDER_TYPE_WHOLESALE = 'wholesale';
    const ORDER_TYPE_BULK = 'bulk';
    const ORDER_TYPE_CUSTOM = 'custom';

    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_ON_HOLD = 'on_hold';

    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PARTIAL = 'partial';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_FAILED = 'failed';
    const PAYMENT_STATUS_REFUNDED = 'refunded';
    const PAYMENT_STATUS_CANCELLED = 'cancelled';

    const SHIPPING_STATUS_PENDING = 'pending';
    const SHIPPING_STATUS_PROCESSING = 'processing';
    const SHIPPING_STATUS_SHIPPED = 'shipped';
    const SHIPPING_STATUS_DELIVERED = 'delivered';
    const SHIPPING_STATUS_RETURNED = 'returned';
    const SHIPPING_STATUS_LOST = 'lost';

    public static function getOrderTypes(): array
    {
        return [
            self::ORDER_TYPE_STANDARD => 'Estándar',
            self::ORDER_TYPE_PRE_ORDER => 'Pre-orden',
            self::ORDER_TYPE_SUBSCRIPTION => 'Suscripción',
            self::ORDER_TYPE_WHOLESALE => 'Mayorista',
            self::ORDER_TYPE_BULK => 'Al Por Mayor',
            self::ORDER_TYPE_CUSTOM => 'Personalizado',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_CONFIRMED => 'Confirmado',
            self::STATUS_PROCESSING => 'Procesando',
            self::STATUS_SHIPPED => 'Enviado',
            self::STATUS_DELIVERED => 'Entregado',
            self::STATUS_CANCELLED => 'Cancelado',
            self::STATUS_REFUNDED => 'Reembolsado',
            self::STATUS_ON_HOLD => 'En Espera',
        ];
    }

    public static function getPaymentStatuses(): array
    {
        return [
            self::PAYMENT_STATUS_PENDING => 'Pendiente',
            self::PAYMENT_STATUS_PARTIAL => 'Parcial',
            self::PAYMENT_STATUS_PAID => 'Pagado',
            self::PAYMENT_STATUS_FAILED => 'Fallido',
            self::PAYMENT_STATUS_REFUNDED => 'Reembolsado',
            self::PAYMENT_STATUS_CANCELLED => 'Cancelado',
        ];
    }

    public static function getShippingStatuses(): array
    {
        return [
            self::SHIPPING_STATUS_PENDING => 'Pendiente',
            self::SHIPPING_STATUS_PROCESSING => 'Procesando',
            self::SHIPPING_STATUS_SHIPPED => 'Enviado',
            self::SHIPPING_STATUS_DELIVERED => 'Entregado',
            self::SHIPPING_STATUS_RETURNED => 'Devuelto',
            self::SHIPPING_STATUS_LOST => 'Perdido',
        ];
    }

    // Relaciones
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function shippedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shipped_by');
    }

    public function deliveredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
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

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByOrderType($query, $orderType)
    {
        return $query->where('order_type', $orderType);
    }

    public function scopeByPaymentStatus($query, $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    public function scopeByShippingStatus($query, $shippingStatus)
    {
        return $query->where('shipping_status', $shippingStatus);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeShipped($query)
    {
        return $query->where('status', self::STATUS_SHIPPED);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeOnHold($query)
    {
        return $query->where('status', self::STATUS_ON_HOLD);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeByAffiliate($query, $affiliateId)
    {
        return $query->where('affiliate_id', $affiliateId);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PAID);
    }

    public function scopePendingPayment($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PENDING);
    }

    public function scopePartialPayment($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PARTIAL);
    }

    // Métodos de validación
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



    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    public function isOnHold(): bool
    {
        return $this->status === self::STATUS_ON_HOLD;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    public function isPartiallyPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PARTIAL;
    }

    public function isPaymentPending(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PENDING;
    }

    public function isPaymentFailed(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_FAILED;
    }

    public function isShipped(): bool
    {
        return $this->shipping_status === self::SHIPPING_STATUS_SHIPPED;
    }

    public function isDelivered(): bool
    {
        return $this->shipping_status === self::SHIPPING_STATUS_DELIVERED;
    }

    public function isReturned(): bool
    {
        return $this->shipping_status === self::SHIPPING_STATUS_RETURNED;
    }

    public function isLost(): bool
    {
        return $this->shipping_status === self::SHIPPING_STATUS_LOST;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_PROCESSING,
        ]);
    }

    public function canBeRefunded(): bool
    {
        return $this->isPaid() && !$this->isRefunded();
    }

    public function canBeShipped(): bool
    {
        return in_array($this->status, [
            self::STATUS_CONFIRMED,
            self::STATUS_PROCESSING,
        ]);
    }

    public function canBeDelivered(): bool
    {
        return $this->isShipped();
    }

    // Métodos de cálculo
    public function getTotalItemsCount(): int
    {
        if ($this->order_items) {
            return collect($this->order_items)->sum('quantity');
        }
        
        return $this->items->sum('quantity');
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->getSubtotalFromItems();
        $this->total_amount = $this->subtotal + $this->tax_amount + $this->shipping_amount - $this->discount_amount;
        $this->outstanding_amount = $this->total_amount - $this->paid_amount;
        
        $this->save();
    }

    protected function getSubtotalFromItems(): float
    {
        if ($this->order_items) {
            return collect($this->order_items)->sum(function($item) {
                return $item['quantity'] * $item['unit_price'];
            });
        }
        
        return $this->items->sum(function($item) {
            return $item->quantity * $item->unit_price;
        });
    }

    public function applyDiscount(float $discountAmount): void
    {
        $this->discount_amount = $discountAmount;
        $this->calculateTotals();
    }

    public function markAsPaid(float $amount = null): void
    {
        $amount = $amount ?? $this->outstanding_amount;
        $this->paid_amount += $amount;
        
        if ($this->paid_amount >= $this->total_amount) {
            $this->payment_status = self::PAYMENT_STATUS_PAID;
        } else {
            $this->payment_status = self::PAYMENT_STATUS_PARTIAL;
        }
        
        $this->payment_date = now();
        $this->calculateTotals();
        $this->save();
    }

    public function markAsShipped(string $trackingNumber = null): void
    {
        if (!$this->canBeShipped()) {
            throw new \Exception('No se puede enviar este pedido');
        }

        $this->status = self::STATUS_SHIPPED;
        $this->shipping_status = self::SHIPPING_STATUS_SHIPPED;
        $this->tracking_number = $trackingNumber;
        $this->shipped_date = now();
        $this->save();
    }

    public function markAsDelivered(): void
    {
        if (!$this->canBeDelivered()) {
            throw new \Exception('No se puede marcar como entregado este pedido');
        }

        $this->status = self::STATUS_DELIVERED;
        $this->shipping_status = self::SHIPPING_STATUS_DELIVERED;
        $this->delivered_date = now();
        $this->save();
    }

    public function cancel(): void
    {
        if (!$this->canBeCancelled()) {
            throw new \Exception('No se puede cancelar este pedido');
        }

        $this->status = self::STATUS_CANCELLED;
        $this->payment_status = self::PAYMENT_STATUS_CANCELLED;
        $this->save();
    }

    public function putOnHold(): void
    {
        $this->status = self::STATUS_ON_HOLD;
        $this->save();
    }

    public function resumeFromHold(): void
    {
        if ($this->status === self::STATUS_ON_HOLD) {
            $this->status = self::STATUS_PENDING;
            $this->save();
        }
    }

    // Métodos de formato
    public function getFormattedOrderType(): string
    {
        return self::getOrderTypes()[$this->order_type] ?? 'Desconocido';
    }

    public function getFormattedStatus(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getFormattedPaymentStatus(): string
    {
        return self::getPaymentStatuses()[$this->payment_status] ?? 'Desconocido';
    }

    public function getFormattedShippingStatus(): string
    {
        return self::getShippingStatuses()[$this->shipping_status] ?? 'Desconocido';
    }

    public function getFormattedSubtotal(): string
    {
        return $this->currency . ' ' . number_format($this->subtotal, 2);
    }

    public function getFormattedTotalAmount(): string
    {
        return $this->currency . ' ' . number_format($this->total_amount, 2);
    }

    public function getFormattedPaidAmount(): string
    {
        return $this->currency . ' ' . number_format($this->paid_amount, 2);
    }

    public function getFormattedOutstandingAmount(): string
    {
        return $this->currency . ' ' . number_format($this->outstanding_amount, 2);
    }

    public function getFormattedShippedDate(): string
    {
        return $this->shipped_date ? $this->shipped_date->format('d/m/Y H:i') : 'N/A';
    }

    public function getFormattedDeliveredDate(): string
    {
        return $this->delivered_date ? $this->delivered_date->format('d/m/Y H:i') : 'N/A';
    }

    public function getFormattedExpectedDeliveryDate(): string
    {
        return $this->expected_delivery_date ? $this->expected_delivery_date->format('d/m/Y') : 'N/A';
    }

    // Clases de badges para Filament
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_CONFIRMED => 'info',
            self::STATUS_PROCESSING => 'primary',
            self::STATUS_SHIPPED => 'success',
            self::STATUS_DELIVERED => 'success',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_REFUNDED => 'gray',
            self::STATUS_ON_HOLD => 'orange',
            default => 'gray',
        };
    }

    public function getPaymentStatusBadgeClass(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_STATUS_PENDING => 'warning',
            self::PAYMENT_STATUS_PARTIAL => 'info',
            self::PAYMENT_STATUS_PAID => 'success',
            self::PAYMENT_STATUS_FAILED => 'danger',
            self::PAYMENT_STATUS_REFUNDED => 'gray',
            self::PAYMENT_STATUS_CANCELLED => 'danger',
            default => 'gray',
        };
    }

    public function getShippingStatusBadgeClass(): string
    {
        return match ($this->shipping_status) {
            self::SHIPPING_STATUS_PENDING => 'gray',
            self::SHIPPING_STATUS_PROCESSING => 'info',
            self::SHIPPING_STATUS_SHIPPED => 'primary',
            self::SHIPPING_STATUS_DELIVERED => 'success',
            self::SHIPPING_STATUS_RETURNED => 'warning',
            self::SHIPPING_STATUS_LOST => 'danger',
            default => 'gray',
        };
    }
}
