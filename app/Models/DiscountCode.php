<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscountCode extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'minimum_purchase_amount',
        'maximum_discount_amount',
        'status',
        'start_date',
        'end_date',
        'usage_limit',
        'usage_count',
        'per_user_limit',
        'is_first_time_only',
        'is_new_customer_only',
        'applicable_products',
        'excluded_products',
        'applicable_categories',
        'excluded_categories',
        'applicable_user_groups',
        'excluded_user_groups',
        'can_be_combined',
        'combination_rules',
        'terms_conditions',
        'usage_instructions',
        'tags',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'minimum_purchase_amount' => 'decimal:2',
        'maximum_discount_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'per_user_limit' => 'integer',
        'is_first_time_only' => 'boolean',
        'is_new_customer_only' => 'boolean',
        'can_be_combined' => 'boolean',
        'approved_at' => 'datetime',
        'applicable_products' => 'array',
        'excluded_products' => 'array',
        'applicable_categories' => 'array',
        'excluded_categories' => 'array',
        'applicable_user_groups' => 'array',
        'excluded_user_groups' => 'array',
        'combination_rules' => 'array',
        'tags' => 'array',
    ];

    // Enums
    const DISCOUNT_TYPE_PERCENTAGE = 'percentage';
    const DISCOUNT_TYPE_FIXED_AMOUNT = 'fixed_amount';
    const DISCOUNT_TYPE_FREE_SHIPPING = 'free_shipping';
    const DISCOUNT_TYPE_BUY_ONE_GET_ONE = 'buy_one_get_one';
    const DISCOUNT_TYPE_TIERED = 'tiered';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_EXPIRED = 'expired';
    const STATUS_DEPLETED = 'depleted';

    public static function getDiscountTypes(): array
    {
        return [
            self::DISCOUNT_TYPE_PERCENTAGE => 'Porcentaje',
            self::DISCOUNT_TYPE_FIXED_AMOUNT => 'Monto Fijo',
            self::DISCOUNT_TYPE_FREE_SHIPPING => 'Envío Gratis',
            self::DISCOUNT_TYPE_BUY_ONE_GET_ONE => 'Compra Uno Lleva Otro',
            self::DISCOUNT_TYPE_TIERED => 'Por Niveles',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_INACTIVE => 'Inactivo',
            self::STATUS_EXPIRED => 'Expirado',
            self::STATUS_DEPLETED => 'Agotado',
        ];
    }

    // Relaciones
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeValid($query)
    {
        $now = now();
        return $query->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                    ->where(function($q) {
                        $q->whereNull('usage_limit')
                          ->orWhere('usage_count', '<', \DB::raw('usage_limit'));
                    });
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('code', strtoupper($code));
    }

    public function scopeByType($query, $type)
    {
        return $query->where('discount_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now());
    }

    public function scopeDepleted($query)
    {
        return $query->where('status', self::STATUS_DEPLETED);
    }

    public function scopeFirstTimeOnly($query)
    {
        return $query->where('is_first_time_only', true);
    }

    public function scopeNewCustomerOnly($query)
    {
        return $query->where('is_new_customer_only', true);
    }

    public function scopeCanBeCombined($query)
    {
        return $query->where('can_be_combined', true);
    }

    // Métodos de validación
    public function isValid(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        $now = now();
        if ($this->start_date && $this->start_date->isFuture()) {
            return false;
        }

        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED || 
               ($this->end_date && $this->end_date->isPast());
    }

    public function isDepleted(): bool
    {
        return $this->status === self::STATUS_DEPLETED || 
               ($this->usage_limit && $this->usage_count >= $this->usage_limit);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    public function isFirstTimeOnly(): bool
    {
        return $this->is_first_time_only;
    }

    public function isNewCustomerOnly(): bool
    {
        return $this->is_new_customer_only;
    }

    public function canBeCombined(): bool
    {
        return $this->can_be_combined;
    }

    // Métodos de cálculo
    public function calculateDiscount(float $orderAmount): float
    {
        if (!$this->isValid()) {
            return 0;
        }

        // Verificar monto mínimo de compra
        if ($this->minimum_purchase_amount && $orderAmount < $this->minimum_purchase_amount) {
            return 0;
        }

        $discount = 0;
        switch ($this->discount_type) {
            case self::DISCOUNT_TYPE_PERCENTAGE:
                $discount = ($orderAmount * $this->discount_value) / 100;
                break;
            case self::DISCOUNT_TYPE_FIXED_AMOUNT:
                $discount = $this->discount_value;
                break;
            case self::DISCOUNT_TYPE_FREE_SHIPPING:
                // Implementar lógica para envío gratis
                $discount = 0;
                break;
            case self::DISCOUNT_TYPE_BUY_ONE_GET_ONE:
                // Implementar lógica para BOGO
                $discount = 0;
                break;
            case self::DISCOUNT_TYPE_TIERED:
                // Implementar lógica para descuentos por niveles
                $discount = 0;
                break;
        }

        // Aplicar límite máximo de descuento
        if ($this->maximum_discount_amount && $discount > $this->maximum_discount_amount) {
            $discount = $this->maximum_discount_amount;
        }

        return $discount;
    }

    public function canApplyTo($entity, $user = null): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Verificar si es solo para nuevos clientes
        if ($this->is_new_customer_only && $user && $user->orders()->count() > 0) {
            return false;
        }

        // Verificar productos aplicables
        if ($this->applicable_products && !in_array($entity->id, $this->applicable_products)) {
            return false;
        }

        // Verificar productos excluidos
        if ($this->excluded_products && in_array($entity->id, $this->excluded_products)) {
            return false;
        }

        // Verificar categorías aplicables
        if ($this->applicable_categories && !in_array($entity->category_id, $this->applicable_categories)) {
            return false;
        }

        // Verificar categorías excluidas
        if ($this->excluded_categories && in_array($entity->category_id, $this->excluded_categories)) {
            return false;
        }

        return true;
    }

    public function incrementUsage(): void
    {
        $this->usage_count++;
        
        // Verificar si se agotó
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            $this->status = self::STATUS_DEPLETED;
        }
        
        $this->save();
    }

    public function getRemainingUses(): ?int
    {
        if (!$this->usage_limit) {
            return null; // Sin límite
        }
        
        return max(0, $this->usage_limit - $this->usage_count);
    }

    public function getUsagePercentage(): float
    {
        if (!$this->usage_limit) {
            return 0;
        }
        
        return ($this->usage_count / $this->usage_limit) * 100;
    }

    public function getDaysUntilExpiry(): int
    {
        if (!$this->end_date) {
            return 0;
        }
        
        return now()->diffInDays($this->end_date, false);
    }

    public function getDaysSinceStart(): int
    {
        if (!$this->start_date) {
            return 0;
        }
        
        return now()->diffInDays($this->start_date);
    }

    // Métodos de formato
    public function getFormattedDiscount(): string
    {
        switch ($this->discount_type) {
            case self::DISCOUNT_TYPE_PERCENTAGE:
                return number_format($this->discount_value, 2) . '%';
            case self::DISCOUNT_TYPE_FIXED_AMOUNT:
                return '$' . number_format($this->discount_value, 2);
            case self::DISCOUNT_TYPE_FREE_SHIPPING:
                return 'Envío Gratis';
            case self::DISCOUNT_TYPE_BUY_ONE_GET_ONE:
                return 'Compra Uno Lleva Otro';
            case self::DISCOUNT_TYPE_TIERED:
                return 'Por Niveles';
            default:
                return 'Desconocido';
        }
    }

    public function getFormattedMinimumPurchase(): string
    {
        if (!$this->minimum_purchase_amount) {
            return 'Sin mínimo';
        }
        
        return '$' . number_format($this->minimum_purchase_amount, 2);
    }

    public function getFormattedMaximumDiscount(): string
    {
        if (!$this->maximum_discount_amount) {
            return 'Sin límite';
        }
        
        return '$' . number_format($this->maximum_discount_amount, 2);
    }

    public function getFormattedStartDate(): string
    {
        return $this->start_date ? $this->start_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedEndDate(): string
    {
        return $this->end_date ? $this->end_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedDiscountType(): string
    {
        return self::getDiscountTypes()[$this->discount_type] ?? 'Desconocido';
    }

    public function getFormattedStatus(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getValidityStatus(): string
    {
        if (!$this->is_active) {
            return 'Inactivo';
        }

        if ($this->isExpired()) {
            return 'Expirado';
        }

        if ($this->isDepleted()) {
            return 'Agotado';
        }

        if ($this->start_date && $this->start_date->isFuture()) {
            return 'Pendiente de activación';
        }

        return 'Válido';
    }

    // Clases de badges para Filament
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_INACTIVE => 'gray',
            self::STATUS_EXPIRED => 'danger',
            self::STATUS_DEPLETED => 'warning',
            default => 'gray',
        };
    }

    public function getDiscountTypeBadgeClass(): string
    {
        return match ($this->discount_type) {
            self::DISCOUNT_TYPE_PERCENTAGE => 'primary',
            self::DISCOUNT_TYPE_FIXED_AMOUNT => 'success',
            self::DISCOUNT_TYPE_FREE_SHIPPING => 'info',
            self::DISCOUNT_TYPE_BUY_ONE_GET_ONE => 'warning',
            self::DISCOUNT_TYPE_TIERED => 'purple',
            default => 'gray',
        };
    }
}
