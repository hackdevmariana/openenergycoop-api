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
        'discount_type',
        'discount_value',
        'valid_from',
        'valid_to',
        'usage_limit',
        'current_usage',
        'affiliate_id',
        'applies_to_type',
        'applies_to_id',
        'is_active',
        'description',
        'minimum_order_amount',
        'maximum_discount_amount',
        'is_first_time_only',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'usage_limit' => 'integer',
        'current_usage' => 'integer',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
        'is_active' => 'boolean',
        'minimum_order_amount' => 'decimal:2',
        'maximum_discount_amount' => 'decimal:2',
        'is_first_time_only' => 'boolean',
    ];

    // Enums
    const DISCOUNT_TYPE_PERCENTAGE = 'percentage';
    const DISCOUNT_TYPE_FIXED = 'fixed';

    const APPLIES_TO_TYPE_PRODUCT = 'product';
    const APPLIES_TO_TYPE_PROVIDER = 'provider';
    const APPLIES_TO_TYPE_CATEGORY = 'category';
    const APPLIES_TO_TYPE_ALL = 'all';

    public static function getDiscountTypes(): array
    {
        return [
            self::DISCOUNT_TYPE_PERCENTAGE => 'Porcentaje',
            self::DISCOUNT_TYPE_FIXED => 'Fijo',
        ];
    }

    public static function getAppliesToTypes(): array
    {
        return [
            self::APPLIES_TO_TYPE_PRODUCT => 'Producto',
            self::APPLIES_TO_TYPE_PROVIDER => 'Proveedor',
            self::APPLIES_TO_TYPE_CATEGORY => 'Categoría',
            self::APPLIES_TO_TYPE_ALL => 'Todo',
        ];
    }

    // Relaciones
    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function applicableEntity(): BelongsTo
    {
        return $this->morphTo('applies_to');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        $now = now();
        return $query->where('valid_from', '<=', $now)
                    ->where('valid_to', '>=', $now);
    }

    public function scopeAvailable($query)
    {
        return $query->active()
                    ->valid()
                    ->where(function($q) {
                        $q->whereNull('usage_limit')
                          ->orWhere('current_usage', '<', \DB::raw('usage_limit'));
                    });
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('code', strtoupper($code));
    }

    // Métodos
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        if ($this->valid_from && $this->valid_from->isFuture()) {
            return false;
        }

        if ($this->valid_to && $this->valid_to->isPast()) {
            return false;
        }

        if ($this->usage_limit && $this->current_usage >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $orderAmount): float
    {
        if (!$this->isValid()) {
            return 0;
        }

        if ($this->minimum_order_amount && $orderAmount < $this->minimum_order_amount) {
            return 0;
        }

        $discount = 0;
        if ($this->discount_type === self::DISCOUNT_TYPE_PERCENTAGE) {
            $discount = ($orderAmount * $this->discount_value) / 100;
        } else {
            $discount = $this->discount_value;
        }

        // Aplicar límite máximo de descuento
        if ($this->maximum_discount_amount) {
            $discount = min($discount, $this->maximum_discount_amount);
        }

        return $discount;
    }

    public function canApplyTo($entity): bool
    {
        if ($this->applies_to_type === self::APPLIES_TO_TYPE_ALL) {
            return true;
        }

        if (!$entity) {
            return false;
        }

        $entityClass = get_class($entity);
        $expectedClass = match($this->applies_to_type) {
            self::APPLIES_TO_TYPE_PRODUCT => Product::class,
            self::APPLIES_TO_TYPE_PROVIDER => Provider::class,
            self::APPLIES_TO_TYPE_CATEGORY => Category::class,
            default => null,
        };

        if (!$expectedClass) {
            return false;
        }

        return $entityClass === $expectedClass && $entity->id == $this->applies_to_id;
    }

    public function incrementUsage(): void
    {
        $this->current_usage++;
        $this->save();
    }

    public function getRemainingUses(): ?int
    {
        if (!$this->usage_limit) {
            return null; // Sin límite
        }
        
        return max(0, $this->usage_limit - $this->current_usage);
    }

    public function getFormattedDiscount(): string
    {
        if ($this->discount_type === self::DISCOUNT_TYPE_PERCENTAGE) {
            return $this->discount_value . '%';
        }
        
        return '€' . number_format($this->discount_value, 2);
    }

    public function getValidityStatus(): string
    {
        if (!$this->is_active) {
            return 'Inactivo';
        }

        $now = now();
        
        if ($this->valid_from && $this->valid_from->isFuture()) {
            return 'Pendiente de activación';
        }

        if ($this->valid_to && $this->valid_to->isPast()) {
            return 'Expirado';
        }

        if ($this->usage_limit && $this->current_usage >= $this->usage_limit) {
            return 'Límite alcanzado';
        }

        return 'Válido';
    }
}
