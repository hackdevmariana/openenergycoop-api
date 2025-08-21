<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnergyTradingOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'quantity_kwh',
        'price_per_kwh',
        'expires_at',
        'status',
        'energy_source_id',
        'delivery_date',
        'delivery_location',
        'delivery_type',
        'payment_terms',
        'counter_offers_allowed',
        'minimum_order_size',
        'maximum_order_size',
        'partial_fills_allowed',
        'fill_or_kill',
        'iceberg_order',
        'iceberg_visible_quantity',
        'stop_loss_price',
        'take_profit_price',
        'linked_orders',
        'notes',
        'external_reference',
        'matched_at',
        'filled_at',
        'cancelled_at',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'quantity_kwh' => 'decimal:4',
        'price_per_kwh' => 'decimal:4',
        'expires_at' => 'datetime',
        'delivery_date' => 'datetime',
        'delivery_location' => 'array',
        'payment_terms' => 'array',
        'counter_offers_allowed' => 'boolean',
        'minimum_order_size' => 'decimal:4',
        'maximum_order_size' => 'decimal:4',
        'partial_fills_allowed' => 'boolean',
        'fill_or_kill' => 'boolean',
        'iceberg_order' => 'boolean',
        'iceberg_visible_quantity' => 'decimal:4',
        'stop_loss_price' => 'decimal:4',
        'take_profit_price' => 'decimal:4',
        'linked_orders' => 'array',
        'matched_at' => 'datetime',
        'filled_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // Enums
    const TYPE_BUY = 'buy';
    const TYPE_SELL = 'sell';

    const STATUS_PENDING = 'pending';
    const STATUS_PARTIAL = 'partial';
    const STATUS_FILLED = 'filled';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';
    const STATUS_MATCHED = 'matched';

    const DELIVERY_TYPE_IMMEDIATE = 'immediate';
    const DELIVERY_TYPE_FORWARD = 'forward';
    const DELIVERY_TYPE_FLEXIBLE = 'flexible';
    const DELIVERY_TYPE_PHYSICAL = 'physical';
    const DELIVERY_TYPE_VIRTUAL = 'virtual';

    public static function getTypes(): array
    {
        return [
            self::TYPE_BUY => 'Compra',
            self::TYPE_SELL => 'Venta',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_PARTIAL => 'Parcial',
            self::STATUS_FILLED => 'Completada',
            self::STATUS_CANCELLED => 'Cancelada',
            self::STATUS_REJECTED => 'Rechazada',
            self::STATUS_EXPIRED => 'Expirada',
            self::STATUS_MATCHED => 'Emparejada',
        ];
    }

    public static function getDeliveryTypes(): array
    {
        return [
            self::DELIVERY_TYPE_IMMEDIATE => 'Inmediata',
            self::DELIVERY_TYPE_FORWARD => 'A Futuro',
            self::DELIVERY_TYPE_FLEXIBLE => 'Flexible',
            self::DELIVERY_TYPE_PHYSICAL => 'Física',
            self::DELIVERY_TYPE_VIRTUAL => 'Virtual',
        ];
    }

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function energySource(): BelongsTo
    {
        return $this->belongsTo(EnergySource::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(EnergyTradingMatch::class, 'buy_order_id')
                    ->orWhere('sell_order_id', $this->id);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(EnergyTradingTransaction::class);
    }

    public function linkedOrders(): HasMany
    {
        return $this->hasMany(EnergyTradingOrder::class, 'linked_orders');
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByEnergySource($query, $energySourceId)
    {
        return $query->where('energy_source_id', $energySourceId);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_PARTIAL,
            self::STATUS_MATCHED,
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeBuy($query)
    {
        return $query->where('type', self::TYPE_BUY);
    }

    public function scopeSell($query)
    {
        return $query->where('type', self::TYPE_SELL);
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeByPriceRange($query, $minPrice, $maxPrice)
    {
        return $query->whereBetween('price_per_kwh', [$minPrice, $maxPrice]);
    }

    public function scopeByQuantityRange($query, $minQuantity, $maxQuantity)
    {
        return $query->whereBetween('quantity_kwh', [$minQuantity, $maxQuantity]);
    }

    public function scopeByDeliveryDate($query, $deliveryDate)
    {
        return $query->whereDate('delivery_date', $deliveryDate);
    }

    public function scopeByDeliveryType($query, $deliveryType)
    {
        return $query->where('delivery_type', $deliveryType);
    }

    // Métodos
    public function isBuy(): bool
    {
        return $this->type === self::TYPE_BUY;
    }

    public function isSell(): bool
    {
        return $this->type === self::TYPE_SELL;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPartial(): bool
    {
        return $this->status === self::STATUS_PARTIAL;
    }

    public function isFilled(): bool
    {
        return $this->status === self::STATUS_FILLED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED || 
               ($this->expires_at && $this->expires_at->isPast());
    }

    public function isMatched(): bool
    {
        return $this->status === self::STATUS_MATCHED;
    }

    public function isActive(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_PARTIAL,
            self::STATUS_MATCHED,
        ]) && !$this->isExpired();
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_PARTIAL,
        ]);
    }

    public function canBeModified(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function getTotalValue(): float
    {
        return $this->quantity_kwh * $this->price_per_kwh;
    }

    public function getFilledQuantity(): float
    {
        return $this->matches()->sum('quantity_kwh');
    }

    public function getRemainingQuantity(): float
    {
        return max(0, $this->quantity_kwh - $this->getFilledQuantity());
    }

    public function getFillPercentage(): float
    {
        if ($this->quantity_kwh <= 0) {
            return 0;
        }
        
        return min(100, ($this->getFilledQuantity() / $this->quantity_kwh) * 100);
    }

    public function getAverageFillPrice(): float
    {
        $matches = $this->matches();
        if ($matches->count() === 0) {
            return $this->price_per_kwh;
        }
        
        $totalValue = $matches->sum(\DB::raw('quantity_kwh * price_per_kwh'));
        $totalQuantity = $matches->sum('quantity_kwh');
        
        return $totalQuantity > 0 ? $totalValue / $totalQuantity : 0;
    }

    public function getTimeToExpiry(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }
        
        return now()->diffInSeconds($this->expires_at, false);
    }

    public function isExpiringSoon(int $hours = 24): bool
    {
        $timeToExpiry = $this->getTimeToExpiry();
        if ($timeToExpiry === null) {
            return false;
        }
        
        return $timeToExpiry <= ($hours * 3600);
    }

    public function getProfitLoss(float $currentPrice): float
    {
        if ($this->isBuy()) {
            return ($currentPrice - $this->price_per_kwh) * $this->getFilledQuantity();
        } else {
            return ($this->price_per_kwh - $currentPrice) * $this->getFilledQuantity();
        }
    }

    public function getProfitLossPercentage(float $currentPrice): float
    {
        $profitLoss = $this->getProfitLoss($currentPrice);
        $totalValue = $this->getTotalValue();
        
        if ($totalValue <= 0) {
            return 0;
        }
        
        return ($profitLoss / $totalValue) * 100;
    }

    public function isStopLossTriggered(float $currentPrice): bool
    {
        if (!$this->stop_loss_price) {
            return false;
        }
        
        if ($this->isBuy()) {
            return $currentPrice <= $this->stop_loss_price;
        } else {
            return $currentPrice >= $this->stop_loss_price;
        }
    }

    public function isTakeProfitTriggered(float $currentPrice): bool
    {
        if (!$this->take_profit_price) {
            return false;
        }
        
        if ($this->isBuy()) {
            return $currentPrice >= $this->take_profit_price;
        } else {
            return $currentPrice <= $this->take_profit_price;
        }
    }

    public function getFormattedQuantity(): string
    {
        return number_format($this->quantity_kwh, 2) . ' kWh';
    }

    public function getFormattedPrice(): string
    {
        return '€' . number_format($this->price_per_kwh, 4) . '/kWh';
    }

    public function getFormattedTotalValue(): string
    {
        return '€' . number_format($this->getTotalValue(), 2);
    }

    public function getFormattedFilledQuantity(): string
    {
        return number_format($this->getFilledQuantity(), 2) . ' kWh';
    }

    public function getFormattedRemainingQuantity(): string
    {
        return number_format($this->getRemainingQuantity(), 2) . ' kWh';
    }

    public function getFormattedFillPercentage(): string
    {
        return number_format($this->getFillPercentage(), 1) . '%';
    }

    public function getFormattedAverageFillPrice(): string
    {
        return '€' . number_format($this->getAverageFillPrice(), 4) . '/kWh';
    }

    public function getFormattedExpiresAt(): string
    {
        if (!$this->expires_at) {
            return 'No expira';
        }
        
        return $this->expires_at->format('d/m/Y H:i:s');
    }

    public function getFormattedDeliveryDate(): string
    {
        if (!$this->delivery_date) {
            return 'No especificada';
        }
        
        return $this->delivery_date->format('d/m/Y H:i:s');
    }

    public function getFormattedType(): string
    {
        return self::getTypes()[$this->type] ?? 'Desconocido';
    }

    public function getFormattedStatus(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getFormattedDeliveryType(): string
    {
        return self::getDeliveryTypes()[$this->delivery_type] ?? 'Desconocido';
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-blue-100 text-blue-800',
            self::STATUS_PARTIAL => 'bg-yellow-100 text-yellow-800',
            self::STATUS_FILLED => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED => 'bg-gray-100 text-gray-800',
            self::STATUS_REJECTED => 'bg-red-100 text-red-800',
            self::STATUS_EXPIRED => 'bg-orange-100 text-orange-800',
            self::STATUS_MATCHED => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getTypeBadgeClass(): string
    {
        return match($this->type) {
            self::TYPE_BUY => 'bg-green-100 text-green-800',
            self::TYPE_SELL => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getDeliveryTypeBadgeClass(): string
    {
        return match($this->delivery_type) {
            self::DELIVERY_TYPE_IMMEDIATE => 'bg-blue-100 text-blue-800',
            self::DELIVERY_TYPE_FORWARD => 'bg-purple-100 text-purple-800',
            self::DELIVERY_TYPE_FLEXIBLE => 'bg-yellow-100 text-yellow-800',
            self::DELIVERY_TYPE_PHYSICAL => 'bg-green-100 text-green-800',
            self::DELIVERY_TYPE_VIRTUAL => 'bg-indigo-100 text-indigo-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getExpiryBadgeClass(): string
    {
        if ($this->isExpired()) {
            return 'bg-red-100 text-red-800';
        }
        
        if ($this->isExpiringSoon(1)) { // 1 hora
            return 'bg-red-100 text-red-800';
        }
        
        if ($this->isExpiringSoon(24)) { // 24 horas
            return 'bg-yellow-100 text-yellow-800';
        }
        
        return 'bg-green-100 text-green-800';
    }
}
