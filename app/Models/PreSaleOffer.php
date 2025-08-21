<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreSaleOffer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'production_project_id',
        'title',
        'slug',
        'description',
        'total_units_available',
        'units_reserved',
        'price_per_unit',
        'reservation_amount',
        'starts_at',
        'ends_at',
        'is_active',
        'visibility',
        'product_id',
        'goal_kwh',
        'goal_amount',
        'current_amount',
        'status',
        'image',
        'co2_per_kwh',
        'total_generated_value',
    ];

    protected $casts = [
        'total_units_available' => 'integer',
        'units_reserved' => 'integer',
        'price_per_unit' => 'decimal:2',
        'reservation_amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'goal_kwh' => 'decimal:4',
        'goal_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'co2_per_kwh' => 'decimal:4',
        'total_generated_value' => 'decimal:2',
    ];

    // Enums
    const VISIBILITY_PUBLIC = 'public';
    const VISIBILITY_PRIVATE = 'private';
    const VISIBILITY_INVITE_ONLY = 'invite_only';

    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_PAUSED = 'paused';
    const STATUS_ENDED = 'ended';

    public static function getVisibilityOptions(): array
    {
        return [
            self::VISIBILITY_PUBLIC => 'Público',
            self::VISIBILITY_PRIVATE => 'Privado',
            self::VISIBILITY_INVITE_ONLY => 'Solo por Invitación',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Borrador',
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_PAUSED => 'Pausado',
            self::STATUS_ENDED => 'Finalizado',
        ];
    }

    // Relaciones
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function productionProject(): BelongsTo
    {
        return $this->belongsTo(ProductionProject::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function priceTiers(): HasMany
    {
        return $this->hasMany(PreSalePriceTier::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(PreSalePurchase::class);
    }

    public function invitationTokens(): HasMany
    {
        return $this->hasMany(PreSaleOfferInvitationToken::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'pre_sale_offer_tags');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'pre_sale_offer_products');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(ProductionProjectStatusLog::class, 'entity_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', self::VISIBILITY_PUBLIC);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAvailable($query)
    {
        $now = now();
        return $query->where('is_active', true)
                    ->where('starts_at', '<=', $now)
                    ->where('ends_at', '>=', $now)
                    ->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    // Métodos
    public function isActive(): bool
    {
        return $this->is_active && $this->status === self::STATUS_ACTIVE;
    }

    public function isPublic(): bool
    {
        return $this->visibility === self::VISIBILITY_PUBLIC;
    }

    public function isPrivate(): bool
    {
        return $this->visibility === self::VISIBILITY_PRIVATE;
    }

    public function isInviteOnly(): bool
    {
        return $this->visibility === self::VISIBILITY_INVITE_ONLY;
    }

    public function isStarted(): bool
    {
        return $this->starts_at && $this->starts_at->isPast();
    }

    public function isEnded(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    public function isAvailable(): bool
    {
        return $this->isActive() && $this->isStarted() && !$this->isEnded();
    }

    public function getAvailableUnits(): int
    {
        return max(0, $this->total_units_available - $this->units_reserved);
    }

    public function getReservationPercentage(): float
    {
        if ($this->total_units_available <= 0) {
            return 0;
        }
        
        return min(100, ($this->units_reserved / $this->total_units_available) * 100);
    }

    public function getProgressPercentage(): float
    {
        if ($this->goal_amount <= 0) {
            return 0;
        }
        
        return min(100, ($this->current_amount / $this->goal_amount) * 100);
    }

    public function getCurrentPricePerUnit(): float
    {
        // Obtener el precio del tier actual basado en unidades reservadas
        $currentTier = $this->priceTiers()
            ->where('from_unit', '<=', $this->units_reserved)
            ->where('to_unit', '>=', $this->units_reserved)
            ->first();

        return $currentTier ? $currentTier->price_per_unit : $this->price_per_unit;
    }

    public function canReserve(int $units): bool
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

    public function getFormattedGoal(): string
    {
        if ($this->goal_kwh) {
            return number_format($this->goal_kwh, 2) . ' kWh';
        }
        
        if ($this->goal_amount) {
            return '€' . number_format($this->goal_amount, 2);
        }
        
        return number_format($this->total_units_available) . ' unidades';
    }

    public function getFormattedCurrentAmount(): string
    {
        if ($this->goal_kwh) {
            return number_format($this->current_amount, 2) . ' kWh';
        }
        
        return '€' . number_format($this->current_amount, 2);
    }

    public function getFormattedPrice(): string
    {
        return '€' . number_format($this->price_per_unit, 2);
    }

    public function getFormattedReservationAmount(): string
    {
        if (!$this->reservation_amount) {
            return 'No aplica';
        }
        
        return '€' . number_format($this->reservation_amount, 2);
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-800',
            self::STATUS_ACTIVE => 'bg-green-100 text-green-800',
            self::STATUS_PAUSED => 'bg-yellow-100 text-yellow-800',
            self::STATUS_ENDED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
