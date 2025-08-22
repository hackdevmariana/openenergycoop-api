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
        'order_number',
        'order_type',
        'order_status',
        'order_side',
        'trader_id',
        'pool_id',
        'counterparty_id',
        'quantity_mwh',
        'filled_quantity_mwh',
        'remaining_quantity_mwh',
        'price_per_mwh',
        'total_value',
        'filled_value',
        'remaining_value',
        'price_type',
        'price_index',
        'price_adjustment',
        'valid_from',
        'valid_until',
        'execution_time',
        'expiry_time',
        'execution_type',
        'priority',
        'is_negotiable',
        'negotiation_terms',
        'special_conditions',
        'delivery_requirements',
        'payment_terms',
        'order_conditions',
        'order_restrictions',
        'order_metadata',
        'tags',
        'created_by',
        'approved_by',
        'approved_at',
        'executed_by',
        'executed_at',
        'notes',
    ];

    protected $casts = [
        'quantity_mwh' => 'decimal:2',
        'filled_quantity_mwh' => 'decimal:2',
        'remaining_quantity_mwh' => 'decimal:2',
        'price_per_mwh' => 'decimal:2',
        'total_value' => 'decimal:2',
        'filled_value' => 'decimal:2',
        'remaining_value' => 'decimal:2',
        'price_adjustment' => 'decimal:2',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'execution_time' => 'datetime',
        'expiry_time' => 'datetime',
        'approved_at' => 'datetime',
        'executed_at' => 'datetime',
        'is_negotiable' => 'boolean',
        'order_conditions' => 'array',
        'order_restrictions' => 'array',
        'order_metadata' => 'array',
        'tags' => 'array',
    ];

    // Enums
    const ORDER_TYPE_BUY = 'buy';
    const ORDER_TYPE_SELL = 'sell';
    const ORDER_TYPE_BID = 'bid';
    const ORDER_TYPE_ASK = 'ask';
    const ORDER_TYPE_MARKET = 'market';
    const ORDER_TYPE_LIMIT = 'limit';
    const ORDER_TYPE_STOP = 'stop';
    const ORDER_TYPE_STOP_LIMIT = 'stop_limit';
    const ORDER_TYPE_OTHER = 'other';

    const ORDER_STATUS_PENDING = 'pending';
    const ORDER_STATUS_ACTIVE = 'active';
    const ORDER_STATUS_FILLED = 'filled';
    const ORDER_STATUS_PARTIALLY_FILLED = 'partially_filled';
    const ORDER_STATUS_CANCELLED = 'cancelled';
    const ORDER_STATUS_REJECTED = 'rejected';
    const ORDER_STATUS_EXPIRED = 'expired';
    const ORDER_STATUS_COMPLETED = 'completed';

    const ORDER_SIDE_BUY = 'buy';
    const ORDER_SIDE_SELL = 'sell';

    const PRICE_TYPE_FIXED = 'fixed';
    const PRICE_TYPE_FLOATING = 'floating';
    const PRICE_TYPE_INDEXED = 'indexed';
    const PRICE_TYPE_FORMULA = 'formula';
    const PRICE_TYPE_OTHER = 'other';

    const EXECUTION_TYPE_IMMEDIATE = 'immediate';
    const EXECUTION_TYPE_GOOD_TILL_CANCELLED = 'good_till_cancelled';
    const EXECUTION_TYPE_GOOD_TILL_DATE = 'good_till_date';
    const EXECUTION_TYPE_FILL_OR_KILL = 'fill_or_kill';
    const EXECUTION_TYPE_ALL_OR_NOTHING = 'all_or_nothing';
    const EXECUTION_TYPE_OTHER = 'other';

    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';
    const PRIORITY_CRITICAL = 'critical';

    public static function getOrderTypes(): array
    {
        return [
            self::ORDER_TYPE_BUY => 'Compra',
            self::ORDER_TYPE_SELL => 'Venta',
            self::ORDER_TYPE_BID => 'Oferta',
            self::ORDER_TYPE_ASK => 'Demanda',
            self::ORDER_TYPE_MARKET => 'Mercado',
            self::ORDER_TYPE_LIMIT => 'Límite',
            self::ORDER_TYPE_STOP => 'Stop',
            self::ORDER_TYPE_STOP_LIMIT => 'Stop Límite',
            self::ORDER_TYPE_OTHER => 'Otro',
        ];
    }

    public static function getOrderStatuses(): array
    {
        return [
            self::ORDER_STATUS_PENDING => 'Pendiente',
            self::ORDER_STATUS_ACTIVE => 'Activo',
            self::ORDER_STATUS_FILLED => 'Completado',
            self::ORDER_STATUS_PARTIALLY_FILLED => 'Parcialmente Completado',
            self::ORDER_STATUS_CANCELLED => 'Cancelado',
            self::ORDER_STATUS_REJECTED => 'Rechazado',
            self::ORDER_STATUS_EXPIRED => 'Expirado',
            self::ORDER_STATUS_COMPLETED => 'Completado',
        ];
    }

    public static function getOrderSides(): array
    {
        return [
            self::ORDER_SIDE_BUY => 'Compra',
            self::ORDER_SIDE_SELL => 'Venta',
        ];
    }

    public static function getPriceTypes(): array
    {
        return [
            self::PRICE_TYPE_FIXED => 'Fijo',
            self::PRICE_TYPE_FLOATING => 'Flotante',
            self::PRICE_TYPE_INDEXED => 'Indexado',
            self::PRICE_TYPE_FORMULA => 'Fórmula',
            self::PRICE_TYPE_OTHER => 'Otro',
        ];
    }

    public static function getExecutionTypes(): array
    {
        return [
            self::EXECUTION_TYPE_IMMEDIATE => 'Inmediato',
            self::EXECUTION_TYPE_GOOD_TILL_CANCELLED => 'Bueno hasta Cancelar',
            self::EXECUTION_TYPE_GOOD_TILL_DATE => 'Bueno hasta Fecha',
            self::EXECUTION_TYPE_FILL_OR_KILL => 'Llenar o Cancelar',
            self::EXECUTION_TYPE_ALL_OR_NOTHING => 'Todo o Nada',
            self::EXECUTION_TYPE_OTHER => 'Otro',
        ];
    }

    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Baja',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'Alta',
            self::PRIORITY_URGENT => 'Urgente',
            self::PRIORITY_CRITICAL => 'Crítica',
        ];
    }

    // Relaciones
    public function trader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trader_id');
    }

    public function pool(): BelongsTo
    {
        return $this->belongsTo(EnergyPool::class);
    }

    public function counterparty(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counterparty_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function executedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
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

    // Scopes
    public function scopeByOrderType($query, $orderType)
    {
        return $query->where('order_type', $orderType);
    }

    public function scopeByOrderStatus($query, $orderStatus)
    {
        return $query->where('order_status', $orderStatus);
    }

    public function scopeByOrderSide($query, $orderSide)
    {
        return $query->where('order_side', $orderSide);
    }

    public function scopeByTrader($query, $traderId)
    {
        return $query->where('trader_id', $traderId);
    }

    public function scopeByPool($query, $poolId)
    {
        return $query->where('pool_id', $poolId);
    }

    public function scopeByCounterparty($query, $counterpartyId)
    {
        return $query->where('counterparty_id', $counterpartyId);
    }

    public function scopeByPriceType($query, $priceType)
    {
        return $query->where('price_type', $priceType);
    }

    public function scopeByExecutionType($query, $executionType)
    {
        return $query->where('execution_type', $executionType);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('order_status', [
            self::ORDER_STATUS_PENDING,
            self::ORDER_STATUS_ACTIVE,
            self::ORDER_STATUS_PARTIALLY_FILLED,
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('order_status', self::ORDER_STATUS_PENDING);
    }

    public function scopeActiveStatus($query)
    {
        return $query->where('order_status', self::ORDER_STATUS_ACTIVE);
    }

    public function scopeFilled($query)
    {
        return $query->where('order_status', self::ORDER_STATUS_FILLED);
    }

    public function scopePartiallyFilled($query)
    {
        return $query->where('order_status', self::ORDER_STATUS_PARTIALLY_FILLED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('order_status', self::ORDER_STATUS_CANCELLED);
    }

    public function scopeRejected($query)
    {
        return $query->where('order_status', self::ORDER_STATUS_REJECTED);
    }

    public function scopeExpiredStatus($query)
    {
        return $query->where('order_status', self::ORDER_STATUS_EXPIRED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('order_status', self::ORDER_STATUS_COMPLETED);
    }

    public function scopeBuy($query)
    {
        return $query->where('order_side', self::ORDER_SIDE_BUY);
    }

    public function scopeSell($query)
    {
        return $query->where('order_side', self::ORDER_SIDE_SELL);
    }

    public function scopeBid($query)
    {
        return $query->where('order_type', self::ORDER_TYPE_BID);
    }

    public function scopeAsk($query)
    {
        return $query->where('order_type', self::ORDER_TYPE_ASK);
    }

    public function scopeMarket($query)
    {
        return $query->where('order_type', self::ORDER_TYPE_MARKET);
    }

    public function scopeLimit($query)
    {
        return $query->where('order_type', self::ORDER_TYPE_LIMIT);
    }

    public function scopeStop($query)
    {
        return $query->where('order_type', self::ORDER_TYPE_STOP);
    }

    public function scopeStopLimit($query)
    {
        return $query->where('order_type', self::ORDER_TYPE_STOP_LIMIT);
    }

    public function scopeFixedPrice($query)
    {
        return $query->where('price_type', self::PRICE_TYPE_FIXED);
    }

    public function scopeFloatingPrice($query)
    {
        return $query->where('price_type', self::PRICE_TYPE_FLOATING);
    }

    public function scopeIndexedPrice($query)
    {
        return $query->where('price_type', self::PRICE_TYPE_INDEXED);
    }

    public function scopeFormulaPrice($query)
    {
        return $query->where('price_type', self::PRICE_TYPE_FORMULA);
    }

    public function scopeImmediateExecution($query)
    {
        return $query->where('execution_type', self::EXECUTION_TYPE_IMMEDIATE);
    }

    public function scopeGoodTillCancelled($query)
    {
        return $query->where('execution_type', self::EXECUTION_TYPE_GOOD_TILL_CANCELLED);
    }

    public function scopeGoodTillDate($query)
    {
        return $query->where('execution_type', self::EXECUTION_TYPE_GOOD_TILL_DATE);
    }

    public function scopeFillOrKill($query)
    {
        return $query->where('execution_type', self::EXECUTION_TYPE_FILL_OR_KILL);
    }

    public function scopeAllOrNothing($query)
    {
        return $query->where('execution_type', self::EXECUTION_TYPE_ALL_OR_NOTHING);
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', [
            self::PRIORITY_HIGH,
            self::PRIORITY_URGENT,
            self::PRIORITY_CRITICAL,
        ]);
    }

    public function scopeNegotiable($query)
    {
        return $query->where('is_negotiable', true);
    }

    public function scopeByPriceRange($query, $minPrice, $maxPrice)
    {
        return $query->whereBetween('price_per_mwh', [$minPrice, $maxPrice]);
    }

    public function scopeByQuantityRange($query, $minQuantity, $maxQuantity)
    {
        return $query->whereBetween('quantity_mwh', [$minQuantity, $maxQuantity]);
    }

    public function scopeByValidFrom($query, $date)
    {
        return $query->whereDate('valid_from', $date);
    }

    public function scopeByValidUntil($query, $date)
    {
        return $query->whereDate('valid_until', $date);
    }

    public function scopeByExecutionTime($query, $date)
    {
        return $query->whereDate('execution_time', $date);
    }

    public function scopeByExpiryTime($query, $date)
    {
        return $query->whereDate('expiry_time', $date);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_time', '<=', now());
    }

    public function scopeNotExpired($query)
    {
        return $query->where('expiry_time', '>', now());
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopePendingApproval($query)
    {
        return $query->whereNull('approved_at');
    }

    public function scopeExecuted($query)
    {
        return $query->whereNotNull('executed_at');
    }

    public function scopeNotExecuted($query)
    {
        return $query->whereNull('executed_at');
    }

    // Métodos de validación
    public function isBuy(): bool
    {
        return $this->order_side === self::ORDER_SIDE_BUY;
    }

    public function isSell(): bool
    {
        return $this->order_side === self::ORDER_SIDE_SELL;
    }

    public function isBid(): bool
    {
        return $this->order_type === self::ORDER_TYPE_BID;
    }

    public function isAsk(): bool
    {
        return $this->order_type === self::ORDER_TYPE_ASK;
    }

    public function isMarket(): bool
    {
        return $this->order_type === self::ORDER_TYPE_MARKET;
    }

    public function isLimit(): bool
    {
        return $this->order_type === self::ORDER_TYPE_LIMIT;
    }

    public function isStop(): bool
    {
        return $this->order_type === self::ORDER_TYPE_STOP;
    }

    public function isStopLimit(): bool
    {
        return $this->order_type === self::ORDER_TYPE_STOP_LIMIT;
    }

    public function isPending(): bool
    {
        return $this->order_status === self::ORDER_STATUS_PENDING;
    }

    public function isActive(): bool
    {
        return $this->order_status === self::ORDER_STATUS_ACTIVE;
    }

    public function isFilled(): bool
    {
        return $this->order_status === self::ORDER_STATUS_FILLED;
    }

    public function isPartiallyFilled(): bool
    {
        return $this->order_status === self::ORDER_STATUS_PARTIALLY_FILLED;
    }

    public function isCancelled(): bool
    {
        return $this->order_status === self::ORDER_STATUS_CANCELLED;
    }

    public function isRejected(): bool
    {
        return $this->order_status === self::ORDER_STATUS_REJECTED;
    }

    public function isExpired(): bool
    {
        return $this->order_status === self::ORDER_STATUS_EXPIRED || 
               ($this->expiry_time && $this->expiry_time->isPast());
    }

    public function isCompleted(): bool
    {
        return $this->order_status === self::ORDER_STATUS_COMPLETED;
    }

    public function isFixedPrice(): bool
    {
        return $this->price_type === self::PRICE_TYPE_FIXED;
    }

    public function isFloatingPrice(): bool
    {
        return $this->price_type === self::PRICE_TYPE_FLOATING;
    }

    public function isIndexedPrice(): bool
    {
        return $this->price_type === self::PRICE_TYPE_INDEXED;
    }

    public function isFormulaPrice(): bool
    {
        return $this->price_type === self::PRICE_TYPE_FORMULA;
    }

    public function isImmediateExecution(): bool
    {
        return $this->execution_type === self::EXECUTION_TYPE_IMMEDIATE;
    }

    public function isGoodTillCancelled(): bool
    {
        return $this->execution_type === self::EXECUTION_TYPE_GOOD_TILL_CANCELLED;
    }

    public function isGoodTillDate(): bool
    {
        return $this->execution_type === self::EXECUTION_TYPE_GOOD_TILL_DATE;
    }

    public function isFillOrKill(): bool
    {
        return $this->execution_type === self::EXECUTION_TYPE_FILL_OR_KILL;
    }

    public function isAllOrNothing(): bool
    {
        return $this->execution_type === self::EXECUTION_TYPE_ALL_OR_NOTHING;
    }

    public function isHighPriority(): bool
    {
        return in_array($this->priority, [
            self::PRIORITY_HIGH,
            self::PRIORITY_URGENT,
            self::PRIORITY_CRITICAL,
        ]);
    }

    public function isLowPriority(): bool
    {
        return $this->priority === self::PRIORITY_LOW;
    }

    public function isNormalPriority(): bool
    {
        return $this->priority === self::PRIORITY_NORMAL;
    }

    public function isNegotiable(): bool
    {
        return $this->is_negotiable;
    }

    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    public function isExecuted(): bool
    {
        return !is_null($this->executed_at);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->order_status, [
            self::ORDER_STATUS_PENDING,
            self::ORDER_STATUS_ACTIVE,
            self::ORDER_STATUS_PARTIALLY_FILLED,
        ]);
    }

    public function canBeModified(): bool
    {
        return $this->order_status === self::ORDER_STATUS_PENDING;
    }

    public function isActiveStatus(): bool
    {
        return in_array($this->order_status, [
            self::ORDER_STATUS_PENDING,
            self::ORDER_STATUS_ACTIVE,
            self::ORDER_STATUS_PARTIALLY_FILLED,
        ]) && !$this->isExpired();
    }

    // Métodos de cálculo
    public function getFillPercentage(): float
    {
        if ($this->quantity_mwh <= 0) {
            return 0;
        }
        
        return min(100, ($this->filled_quantity_mwh / $this->quantity_mwh) * 100);
    }

    public function getTimeToExpiry(): ?int
    {
        if (!$this->expiry_time) {
            return null;
        }
        
        return now()->diffInSeconds($this->expiry_time, false);
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
            return ($currentPrice - $this->price_per_mwh) * $this->filled_quantity_mwh;
        } else {
            return ($this->price_per_mwh - $currentPrice) * $this->filled_quantity_mwh;
        }
    }

    public function getProfitLossPercentage(float $currentPrice): float
    {
        $profitLoss = $this->getProfitLoss($currentPrice);
        $totalValue = $this->total_value;
        
        if ($totalValue <= 0) {
            return 0;
        }
        
        return ($profitLoss / $totalValue) * 100;
    }

    public function getAdjustedPrice(): float
    {
        $basePrice = $this->price_per_mwh;
        $adjustment = $this->price_adjustment ?? 0;
        
        return $basePrice + $adjustment;
    }

    public function getTotalAdjustedValue(): float
    {
        return $this->quantity_mwh * $this->getAdjustedPrice();
    }

    // Métodos de formato
    public function getFormattedOrderType(): string
    {
        return self::getOrderTypes()[$this->order_type] ?? 'Desconocido';
    }

    public function getFormattedOrderStatus(): string
    {
        return self::getOrderStatuses()[$this->order_status] ?? 'Desconocido';
    }

    public function getFormattedOrderSide(): string
    {
        return self::getOrderSides()[$this->order_side] ?? 'Desconocido';
    }

    public function getFormattedPriceType(): string
    {
        return self::getPriceTypes()[$this->price_type] ?? 'Desconocido';
    }

    public function getFormattedExecutionType(): string
    {
        return self::getExecutionTypes()[$this->execution_type] ?? 'Desconocido';
    }

    public function getFormattedPriority(): string
    {
        return self::getPriorities()[$this->priority] ?? 'Desconocida';
    }

    public function getFormattedQuantity(): string
    {
        return number_format($this->quantity_mwh, 2) . ' MWh';
    }

    public function getFormattedFilledQuantity(): string
    {
        return number_format($this->filled_quantity_mwh, 2) . ' MWh';
    }

    public function getFormattedRemainingQuantity(): string
    {
        return number_format($this->remaining_quantity_mwh, 2) . ' MWh';
    }

    public function getFormattedPrice(): string
    {
        return '$' . number_format($this->price_per_mwh, 2) . '/MWh';
    }

    public function getFormattedAdjustedPrice(): string
    {
        return '$' . number_format($this->getAdjustedPrice(), 2) . '/MWh';
    }

    public function getFormattedTotalValue(): string
    {
        return '$' . number_format($this->total_value, 2);
    }

    public function getFormattedFilledValue(): string
    {
        return '$' . number_format($this->filled_value, 2);
    }

    public function getFormattedRemainingValue(): string
    {
        return '$' . number_format($this->remaining_value, 2);
    }

    public function getFormattedTotalAdjustedValue(): string
    {
        return '$' . number_format($this->getTotalAdjustedValue(), 2);
    }

    public function getFormattedPriceAdjustment(): string
    {
        if (!$this->price_adjustment) {
            return 'N/A';
        }
        
        $sign = $this->price_adjustment >= 0 ? '+' : '';
        return $sign . '$' . number_format($this->price_adjustment, 2);
    }

    public function getFormattedValidFrom(): string
    {
        return $this->valid_from->format('d/m/Y H:i:s');
    }

    public function getFormattedValidUntil(): string
    {
        return $this->valid_until ? $this->valid_until->format('d/m/Y H:i:s') : 'N/A';
    }

    public function getFormattedExecutionTime(): string
    {
        return $this->execution_time ? $this->execution_time->format('d/m/Y H:i:s') : 'N/A';
    }

    public function getFormattedExpiryTime(): string
    {
        return $this->expiry_time ? $this->expiry_time->format('d/m/Y H:i:s') : 'N/A';
    }

    public function getFormattedApprovedAt(): string
    {
        return $this->approved_at ? $this->approved_at->format('d/m/Y H:i:s') : 'N/A';
    }

    public function getFormattedExecutedAt(): string
    {
        return $this->executed_at ? $this->executed_at->format('d/m/Y H:i:s') : 'N/A';
    }

    public function getFormattedFillPercentage(): string
    {
        return number_format($this->getFillPercentage(), 1) . '%';
    }

    public function getFormattedProfitLoss(float $currentPrice): string
    {
        $profitLoss = $this->getProfitLoss($currentPrice);
        $sign = $profitLoss >= 0 ? '+' : '';
        return $sign . '$' . number_format($profitLoss, 2);
    }

    public function getFormattedProfitLossPercentage(float $currentPrice): string
    {
        $percentage = $this->getProfitLossPercentage($currentPrice);
        $sign = $percentage >= 0 ? '+' : '';
        return $sign . number_format($percentage, 2) . '%';
    }

    // Clases de badges para Filament
    public function getOrderStatusBadgeClass(): string
    {
        return match($this->order_status) {
            self::ORDER_STATUS_PENDING => 'bg-blue-100 text-blue-800',
            self::ORDER_STATUS_ACTIVE => 'bg-green-100 text-green-800',
            self::ORDER_STATUS_FILLED => 'bg-green-100 text-green-800',
            self::ORDER_STATUS_PARTIALLY_FILLED => 'bg-yellow-100 text-yellow-800',
            self::ORDER_STATUS_CANCELLED => 'bg-gray-100 text-gray-800',
            self::ORDER_STATUS_REJECTED => 'bg-red-100 text-red-800',
            self::ORDER_STATUS_EXPIRED => 'bg-orange-100 text-orange-800',
            self::ORDER_STATUS_COMPLETED => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getOrderTypeBadgeClass(): string
    {
        return match($this->order_type) {
            self::ORDER_TYPE_BUY => 'bg-green-100 text-green-800',
            self::ORDER_TYPE_SELL => 'bg-red-100 text-red-800',
            self::ORDER_TYPE_BID => 'bg-blue-100 text-blue-800',
            self::ORDER_TYPE_ASK => 'bg-orange-100 text-orange-800',
            self::ORDER_TYPE_MARKET => 'bg-purple-100 text-purple-800',
            self::ORDER_TYPE_LIMIT => 'bg-indigo-100 text-indigo-800',
            self::ORDER_TYPE_STOP => 'bg-yellow-100 text-yellow-800',
            self::ORDER_TYPE_STOP_LIMIT => 'bg-pink-100 text-pink-800',
            self::ORDER_TYPE_OTHER => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getOrderSideBadgeClass(): string
    {
        return match($this->order_side) {
            self::ORDER_SIDE_BUY => 'bg-green-100 text-green-800',
            self::ORDER_SIDE_SELL => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getPriceTypeBadgeClass(): string
    {
        return match($this->price_type) {
            self::PRICE_TYPE_FIXED => 'bg-blue-100 text-blue-800',
            self::PRICE_TYPE_FLOATING => 'bg-yellow-100 text-yellow-800',
            self::PRICE_TYPE_INDEXED => 'bg-green-100 text-green-800',
            self::PRICE_TYPE_FORMULA => 'bg-purple-100 text-purple-800',
            self::PRICE_TYPE_OTHER => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getExecutionTypeBadgeClass(): string
    {
        return match($this->execution_type) {
            self::EXECUTION_TYPE_IMMEDIATE => 'bg-blue-100 text-blue-800',
            self::EXECUTION_TYPE_GOOD_TILL_CANCELLED => 'bg-green-100 text-green-800',
            self::EXECUTION_TYPE_GOOD_TILL_DATE => 'bg-yellow-100 text-yellow-800',
            self::EXECUTION_TYPE_FILL_OR_KILL => 'bg-orange-100 text-orange-800',
            self::EXECUTION_TYPE_ALL_OR_NOTHING => 'bg-purple-100 text-purple-800',
            self::EXECUTION_TYPE_OTHER => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getPriorityBadgeClass(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'bg-gray-100 text-gray-800',
            self::PRIORITY_NORMAL => 'bg-blue-100 text-blue-800',
            self::PRIORITY_HIGH => 'bg-yellow-100 text-yellow-800',
            self::PRIORITY_URGENT => 'bg-orange-100 text-orange-800',
            self::PRIORITY_CRITICAL => 'bg-red-100 text-red-800',
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

    public function getNegotiableBadgeClass(): string
    {
        return $this->is_negotiable ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
    }
}
