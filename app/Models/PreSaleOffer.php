<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreSaleOffer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'offer_number',
        'title',
        'description',
        'offer_type',
        'status',
        'start_date',
        'end_date',
        'early_bird_end_date',
        'founder_end_date',
        'total_units_available',
        'units_reserved',
        'units_sold',
        'early_bird_price',
        'founder_price',
        'regular_price',
        'final_price',
        'savings_percentage',
        'savings_amount',
        'max_units_per_customer',
        'is_featured',
        'is_public',
        'terms_conditions',
        'delivery_timeline',
        'risk_disclosure',
        'included_features',
        'excluded_features',
        'bonus_items',
        'early_access_benefits',
        'founder_benefits',
        'marketing_materials',
        'tags',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'early_bird_end_date' => 'date',
        'founder_end_date' => 'date',
        'total_units_available' => 'integer',
        'units_reserved' => 'integer',
        'units_sold' => 'integer',
        'early_bird_price' => 'decimal:2',
        'founder_price' => 'decimal:2',
        'regular_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'savings_percentage' => 'decimal:2',
        'savings_amount' => 'decimal:2',
        'max_units_per_customer' => 'integer',
        'is_featured' => 'boolean',
        'is_public' => 'boolean',
        'approved_at' => 'datetime',
        'included_features' => 'array',
        'excluded_features' => 'array',
        'bonus_items' => 'array',
        'early_access_benefits' => 'array',
        'founder_benefits' => 'array',
        'marketing_materials' => 'array',
        'tags' => 'array',
    ];

    // Enums
    const OFFER_TYPE_EARLY_BIRD = 'early_bird';
    const OFFER_TYPE_FOUNDER = 'founder';
    const OFFER_TYPE_LIMITED_TIME = 'limited_time';
    const OFFER_TYPE_EXCLUSIVE = 'exclusive';
    const OFFER_TYPE_BETA = 'beta';
    const OFFER_TYPE_PILOT = 'pilot';

    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_PAUSED = 'paused';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    public static function getOfferTypes(): array
    {
        return [
            self::OFFER_TYPE_EARLY_BIRD => 'Early Bird',
            self::OFFER_TYPE_FOUNDER => 'Fundador',
            self::OFFER_TYPE_LIMITED_TIME => 'Tiempo Limitado',
            self::OFFER_TYPE_EXCLUSIVE => 'Exclusivo',
            self::OFFER_TYPE_BETA => 'Beta',
            self::OFFER_TYPE_PILOT => 'Piloto',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Borrador',
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_PAUSED => 'Pausado',
            self::STATUS_EXPIRED => 'Expirado',
            self::STATUS_CANCELLED => 'Cancelado',
            self::STATUS_COMPLETED => 'Completado',
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

    public function purchases(): HasMany
    {
        return $this->hasMany(PreSalePurchase::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(PreSaleOfferStatusLog::class, 'entity_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('offer_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAvailable($query)
    {
        $now = now();
        return $query->where('status', self::STATUS_ACTIVE)
                    ->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now);
    }

    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now());
    }

    public function scopeEarlyBird($query)
    {
        $now = now();
        return $query->where('early_bird_end_date', '>=', $now);
    }

    public function scopeFounder($query)
    {
        $now = now();
        return $query->where('founder_end_date', '>=', $now);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate]);
    }

    // Métodos de validación
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPaused(): bool
    {
        return $this->status === self::STATUS_PAUSED;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED || 
               ($this->end_date && $this->end_date->isPast());
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isPublic(): bool
    {
        return $this->is_public;
    }

    public function isFeatured(): bool
    {
        return $this->is_featured;
    }

    public function isStarted(): bool
    {
        return $this->start_date && $this->start_date->isPast();
    }

    public function isEnded(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function isAvailable(): bool
    {
        return $this->isActive() && $this->isStarted() && !$this->isEnded();
    }

    public function isEarlyBird(): bool
    {
        return $this->early_bird_end_date && $this->early_bird_end_date->isFuture();
    }

    public function isFounder(): bool
    {
        return $this->founder_end_date && $this->founder_end_date->isFuture();
    }

    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    // Métodos de cálculo
    public function getAvailableUnits(): int
    {
        return max(0, $this->total_units_available - $this->units_reserved - $this->units_sold);
    }

    public function getReservationPercentage(): float
    {
        if ($this->total_units_available <= 0) {
            return 0;
        }
        
        return min(100, (($this->units_reserved + $this->units_sold) / $this->total_units_available) * 100);
    }

    public function getSoldPercentage(): float
    {
        if ($this->total_units_available <= 0) {
            return 0;
        }
        
        return min(100, ($this->units_sold / $this->total_units_available) * 100);
    }

    public function getCurrentPrice(): float
    {
        $now = now();
        
        if ($this->isEarlyBird()) {
            return $this->early_bird_price;
        }
        
        if ($this->isFounder()) {
            return $this->founder_price ?? $this->regular_price;
        }
        
        return $this->regular_price;
    }

    public function getSavingsAmount(): float
    {
        if ($this->savings_amount) {
            return $this->savings_amount;
        }
        
        if ($this->savings_percentage) {
            return ($this->regular_price * $this->savings_percentage) / 100;
        }
        
        return $this->regular_price - $this->getCurrentPrice();
    }

    public function getSavingsPercentage(): float
    {
        if ($this->savings_percentage) {
            return $this->savings_percentage;
        }
        
        if ($this->regular_price > 0) {
            return (($this->regular_price - $this->getCurrentPrice()) / $this->regular_price) * 100;
        }
        
        return 0;
    }

    public function canReserve(int $units): bool
    {
        return $this->isAvailable() && $this->getAvailableUnits() >= $units;
    }

    public function canPurchase(int $units): bool
    {
        return $this->isAvailable() && $this->getAvailableUnits() >= $units;
    }

    public function reserveUnits(int $units): bool
    {
        if (!$this->canReserve($units)) {
            return false;
        }

        $this->units_reserved += $units;
        $this->save();

        return true;
    }

    public function releaseUnits(int $units): bool
    {
        if ($this->units_reserved < $units) {
            return false;
        }

        $this->units_reserved -= $units;
        $this->save();

        return true;
    }

    public function sellUnits(int $units): bool
    {
        if (!$this->canPurchase($units)) {
            return false;
        }

        $this->units_sold += $units;
        $this->save();

        return true;
    }

    public function getDaysUntilStart(): int
    {
        if (!$this->start_date) {
            return 0;
        }
        
        return now()->diffInDays($this->start_date, false);
    }

    public function getDaysUntilEnd(): int
    {
        if (!$this->end_date) {
            return 0;
        }
        
        return now()->diffInDays($this->end_date, false);
    }

    public function getDaysUntilEarlyBirdEnd(): int
    {
        if (!$this->early_bird_end_date) {
            return 0;
        }
        
        return now()->diffInDays($this->early_bird_end_date, false);
    }

    public function getDaysUntilFounderEnd(): int
    {
        if (!$this->founder_end_date) {
            return 0;
        }
        
        return now()->diffInDays($this->founder_end_date, false);
    }

    // Métodos de formato
    public function getFormattedCurrentPrice(): string
    {
        return '$' . number_format($this->getCurrentPrice(), 2);
    }

    public function getFormattedRegularPrice(): string
    {
        return '$' . number_format($this->regular_price, 2);
    }

    public function getFormattedEarlyBirdPrice(): string
    {
        return '$' . number_format($this->early_bird_price, 2);
    }

    public function getFormattedFounderPrice(): string
    {
        return $this->founder_price ? '$' . number_format($this->founder_price, 2) : 'N/A';
    }

    public function getFormattedSavingsAmount(): string
    {
        return '$' . number_format($this->getSavingsAmount(), 2);
    }

    public function getFormattedSavingsPercentage(): string
    {
        return number_format($this->getSavingsPercentage(), 2) . '%';
    }

    public function getFormattedStartDate(): string
    {
        return $this->start_date ? $this->start_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedEndDate(): string
    {
        return $this->end_date ? $this->end_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedEarlyBirdEndDate(): string
    {
        return $this->early_bird_end_date ? $this->early_bird_end_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedFounderEndDate(): string
    {
        return $this->founder_end_date ? $this->founder_end_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedOfferType(): string
    {
        return self::getOfferTypes()[$this->offer_type] ?? 'Desconocido';
    }

    public function getFormattedStatus(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getFormattedAvailableUnits(): string
    {
        return number_format($this->getAvailableUnits()) . ' de ' . number_format($this->total_units_available);
    }

    // Clases de badges para Filament
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-800',
            self::STATUS_ACTIVE => 'bg-green-100 text-green-800',
            self::STATUS_PAUSED => 'bg-yellow-100 text-yellow-800',
            self::STATUS_EXPIRED => 'bg-red-100 text-red-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            self::STATUS_COMPLETED => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getOfferTypeBadgeClass(): string
    {
        return match($this->offer_type) {
            self::OFFER_TYPE_EARLY_BIRD => 'bg-yellow-100 text-yellow-800',
            self::OFFER_TYPE_FOUNDER => 'bg-purple-100 text-purple-800',
            self::OFFER_TYPE_LIMITED_TIME => 'bg-red-100 text-red-800',
            self::OFFER_TYPE_EXCLUSIVE => 'bg-indigo-100 text-indigo-800',
            self::OFFER_TYPE_BETA => 'bg-blue-100 text-blue-800',
            self::OFFER_TYPE_PILOT => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getFeaturedBadgeClass(): string
    {
        return $this->is_featured ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800';
    }

    public function getPublicBadgeClass(): string
    {
        return $this->is_public ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
    }
}
