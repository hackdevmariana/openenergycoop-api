<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Affiliate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'commission_type',
        'commission_value',
        'payout_method',
        'tier_level',
        'monthly_target',
        'performance_bonus',
        'payment_threshold',
        'is_active',
    ];

    protected $casts = [
        'commission_value' => 'decimal:2',
        'monthly_target' => 'decimal:2',
        'performance_bonus' => 'decimal:2',
        'payment_threshold' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Enums
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    const TIER_BRONZE = 'bronze';
    const TIER_SILVER = 'silver';
    const TIER_GOLD = 'gold';
    const TIER_PLATINUM = 'platinum';
    const TIER_DIAMOND = 'diamond';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_INACTIVE => 'Inactivo',
        ];
    }

    public static function getTierLevels(): array
    {
        return [
            self::TIER_BRONZE => 'Bronce',
            self::TIER_SILVER => 'Plata',
            self::TIER_GOLD => 'Oro',
            self::TIER_PLATINUM => 'Platino',
            self::TIER_DIAMOND => 'Diamante',
        ];
    }

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTier($query, $tier)
    {
        return $query->where('tier_level', $tier);
    }

    public function scopeByCommissionType($query, $type)
    {
        return $query->where('commission_type', $type);
    }

    // Métodos de validación
    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isInactive(): bool
    {
        return !$this->is_active;
    }

    // Métodos de cálculo
    public function calculateCommission(float $amount): float
    {
        if ($this->commission_type === 'percentage') {
            return ($amount * $this->commission_value) / 100;
        }
        return $this->commission_value;
    }

    public function getTierMultiplier(): float
    {
        return match($this->tier_level) {
            self::TIER_BRONZE => 1.0,
            self::TIER_SILVER => 1.1,
            self::TIER_GOLD => 1.25,
            self::TIER_PLATINUM => 1.5,
            self::TIER_DIAMOND => 2.0,
            default => 1.0,
        };
    }

    public function getDisplayName(): string
    {
        return $this->code ?? 'Sin código';
    }

    public function getTierDisplayName(): string
    {
        return self::getTierLevels()[$this->tier_level] ?? 'Desconocido';
    }

    public function getCommissionTypeDisplayName(): string
    {
        return match($this->commission_type) {
            'percentage' => 'Porcentaje',
            'fixed' => 'Fijo',
            default => 'Desconocido',
        };
    }

    public function getPayoutMethodDisplayName(): string
    {
        return match($this->payout_method) {
            'bank_transfer' => 'Transferencia Bancaria',
            'paypal' => 'PayPal',
            'check' => 'Cheque',
            'crypto' => 'Criptomonedas',
            default => 'Desconocido',
        };
    }
}
