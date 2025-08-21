<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Affiliate extends Model
{
    use HasFactory, SoftDeletes;

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
        'total_earnings',
        'total_referrals',
    ];

    protected $casts = [
        'commission_value' => 'decimal:2',
        'monthly_target' => 'decimal:2',
        'performance_bonus' => 'decimal:2',
        'payment_threshold' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'total_referrals' => 'integer',
        'is_active' => 'boolean',
    ];

    // Enums
    const COMMISSION_TYPE_PERCENTAGE = 'percentage';
    const COMMISSION_TYPE_FIXED = 'fixed';

    const PAYOUT_METHOD_BANK = 'bank';
    const PAYOUT_METHOD_CRYPTO = 'crypto';
    const PAYOUT_METHOD_WALLET = 'wallet';

    const TIER_BRONZE = 'bronze';
    const TIER_SILVER = 'silver';
    const TIER_GOLD = 'gold';
    const TIER_PLATINUM = 'platinum';

    public static function getCommissionTypes(): array
    {
        return [
            self::COMMISSION_TYPE_PERCENTAGE => 'Porcentaje',
            self::COMMISSION_TYPE_FIXED => 'Fijo',
        ];
    }

    public static function getPayoutMethods(): array
    {
        return [
            self::PAYOUT_METHOD_BANK => 'Banco',
            self::PAYOUT_METHOD_CRYPTO => 'Criptomoneda',
            self::PAYOUT_METHOD_WALLET => 'Wallet',
        ];
    }

    public static function getTierLevels(): array
    {
        return [
            self::TIER_BRONZE => 'Bronce',
            self::TIER_SILVER => 'Plata',
            self::TIER_GOLD => 'Oro',
            self::TIER_PLATINUM => 'Platino',
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

    public function scopeEligibleForPayout($query)
    {
        return $query->where('total_earnings', '>=', \DB::raw('payment_threshold'));
    }

    // Métodos
    public function calculateCommission(float $amount): float
    {
        if ($this->commission_type === self::COMMISSION_TYPE_PERCENTAGE) {
            return ($amount * $this->commission_value) / 100;
        }
        
        return $this->commission_value;
    }

    public function getTierMultiplier(): float
    {
        return match($this->tier_level) {
            self::TIER_BRONZE => 1.0,
            self::TIER_SILVER => 1.2,
            self::TIER_GOLD => 1.5,
            self::TIER_PLATINUM => 2.0,
            default => 1.0,
        };
    }

    public function getEffectiveCommissionValue(): float
    {
        $baseValue = $this->commission_value;
        $tierMultiplier = $this->getTierMultiplier();
        
        return $baseValue * $tierMultiplier;
    }

    public function isEligibleForPayout(): bool
    {
        return $this->total_earnings >= $this->payment_threshold;
    }

    public function getMonthlyProgress(): float
    {
        if ($this->monthly_target <= 0) {
            return 0;
        }
        
        // Aquí se calcularía el progreso del mes actual
        $currentMonthEarnings = $this->getCurrentMonthEarnings();
        return min(100, ($currentMonthEarnings / $this->monthly_target) * 100);
    }

    protected function getCurrentMonthEarnings(): float
    {
        // Implementar lógica para obtener ganancias del mes actual
        return 0; // Placeholder
    }

    public function incrementReferral(): void
    {
        $this->total_referrals++;
        $this->save();
    }

    public function addEarnings(float $amount): void
    {
        $this->total_earnings += $amount;
        $this->save();
    }
}
