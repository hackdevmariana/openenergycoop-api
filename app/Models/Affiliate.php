<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Affiliate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'affiliate_code',
        'status',
        'tier',
        'commission_rate',
        'total_earnings',
        'pending_earnings',
        'paid_earnings',
        'total_referrals',
        'active_referrals',
        'converted_referrals',
        'conversion_rate',
        'joined_date',
        'last_activity_date',
        'payment_instructions',
        'payment_methods',
        'marketing_materials',
        'performance_metrics',
        'referred_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'pending_earnings' => 'decimal:2',
        'paid_earnings' => 'decimal:2',
        'conversion_rate' => 'decimal:2',
        'joined_date' => 'date',
        'last_activity_date' => 'date',
        'approved_at' => 'datetime',
        'payment_methods' => 'array',
        'marketing_materials' => 'array',
        'performance_metrics' => 'array',
    ];

    // Enums
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_REJECTED = 'rejected';

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
            self::STATUS_SUSPENDED => 'Suspendido',
            self::STATUS_PENDING_APPROVAL => 'Pendiente de Aprobación',
            self::STATUS_REJECTED => 'Rechazado',
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

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class, 'referred_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Affiliate::class, 'referred_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByTier($query, $tier)
    {
        return $query->where('tier', $tier);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeEligibleForPayout($query)
    {
        return $query->where('pending_earnings', '>', 0);
    }

    public function scopeHighPerformers($query)
    {
        return $query->where('conversion_rate', '>=', 10.0);
    }

    public function scopeByReferrer($query, $referrerId)
    {
        return $query->where('referred_by', $referrerId);
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', self::STATUS_PENDING_APPROVAL);
    }

    // Métodos de validación
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function isPendingApproval(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    public function isEligibleForPayout(): bool
    {
        return $this->pending_earnings > 0;
    }

    // Métodos de cálculo
    public function calculateCommission(float $amount): float
    {
        return ($amount * $this->commission_rate) / 100;
    }

    public function getTierMultiplier(): float
    {
        return match($this->tier) {
            self::TIER_BRONZE => 1.0,
            self::TIER_SILVER => 1.2,
            self::TIER_GOLD => 1.5,
            self::TIER_PLATINUM => 2.0,
            self::TIER_DIAMOND => 2.5,
            default => 1.0,
        };
    }

    public function getEffectiveCommissionRate(): float
    {
        $baseRate = $this->commission_rate;
        $tierMultiplier = $this->getTierMultiplier();
        
        return $baseRate * $tierMultiplier;
    }

    public function getTotalEarningsFormatted(): string
    {
        return '$' . number_format($this->total_earnings, 2);
    }

    public function getPendingEarningsFormatted(): string
    {
        return '$' . number_format($this->pending_earnings, 2);
    }

    public function getPaidEarningsFormatted(): string
    {
        return '$' . number_format($this->paid_earnings, 2);
    }

    public function getConversionRateFormatted(): string
    {
        return number_format($this->conversion_rate, 2) . '%';
    }

    public function getCommissionRateFormatted(): string
    {
        return number_format($this->commission_rate, 2) . '%';
    }

    public function getEffectiveCommissionRateFormatted(): string
    {
        return number_format($this->getEffectiveCommissionRate(), 2) . '%';
    }

    public function getJoinedDateFormatted(): string
    {
        return $this->joined_date ? $this->joined_date->format('d/m/Y') : 'N/A';
    }

    public function getLastActivityDateFormatted(): string
    {
        return $this->last_activity_date ? $this->last_activity_date->format('d/m/Y') : 'N/A';
    }

    public function getStatusFormatted(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getTierFormatted(): string
    {
        return self::getTierLevels()[$this->tier] ?? 'Desconocido';
    }

    public function getDaysSinceJoined(): int
    {
        if (!$this->joined_date) {
            return 0;
        }
        
        return now()->diffInDays($this->joined_date);
    }

    public function getDaysSinceLastActivity(): int
    {
        if (!$this->last_activity_date) {
            return 0;
        }
        
        return now()->diffInDays($this->last_activity_date);
    }

    public function getMonthlyProgress(): float
    {
        // Implementar lógica para calcular progreso mensual
        // basado en las ganancias del mes actual vs objetivo
        return 0; // Placeholder
    }

    // Métodos de negocio
    public function incrementReferral(): void
    {
        $this->total_referrals++;
        $this->save();
    }

    public function incrementActiveReferral(): void
    {
        $this->active_referrals++;
        $this->save();
    }

    public function incrementConvertedReferral(): void
    {
        $this->converted_referrals++;
        $this->updateConversionRate();
        $this->save();
    }

    public function addEarnings(float $amount): void
    {
        $this->total_earnings += $amount;
        $this->pending_earnings += $amount;
        $this->save();
    }

    public function markAsPaid(float $amount): void
    {
        $this->pending_earnings -= $amount;
        $this->paid_earnings += $amount;
        $this->save();
    }

    public function updateLastActivity(): void
    {
        $this->last_activity_date = now();
        $this->save();
    }

    protected function updateConversionRate(): void
    {
        if ($this->total_referrals > 0) {
            $this->conversion_rate = ($this->converted_referrals / $this->total_referrals) * 100;
        }
    }

    // Clases de badges para Filament
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_INACTIVE => 'gray',
            self::STATUS_SUSPENDED => 'danger',
            self::STATUS_PENDING_APPROVAL => 'warning',
            self::STATUS_REJECTED => 'danger',
            default => 'gray',
        };
    }

    public function getTierBadgeClass(): string
    {
        return match ($this->tier) {
            self::TIER_BRONZE => 'gray',
            self::TIER_SILVER => 'info',
            self::TIER_GOLD => 'warning',
            self::TIER_PLATINUM => 'primary',
            self::TIER_DIAMOND => 'success',
            default => 'gray',
        };
    }
}
