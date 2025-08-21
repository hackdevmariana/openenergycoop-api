<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnergyBond extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'beneficiary_id',
        'organization_id',
        'amount_kwh',
        'start_date',
        'end_date',
        'type',
        'status',
        'eligibility_criteria',
        'usage_restrictions',
        'renewable_only',
        'priority_level',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'eligibility_criteria' => 'array',
        'usage_restrictions' => 'array',
        'renewable_only' => 'boolean',
        'priority_level' => 'integer',
        'amount_kwh' => 'decimal:4',
    ];

    // Enums
    const TYPE_TEMPORAL = 'temporal';
    const TYPE_SEASONAL = 'seasonal';

    const STATUS_ACTIVE = 'active';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PENDING = 'pending';

    public static function getTypes(): array
    {
        return [
            self::TYPE_TEMPORAL => 'Temporal',
            self::TYPE_SEASONAL => 'Estacional',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_ACCEPTED => 'Aceptado',
            self::STATUS_REJECTED => 'Rechazado',
            self::STATUS_PENDING => 'Pendiente',
        ];
    }

    // Relaciones
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(User::class, 'beneficiary_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(BondDonation::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(EnergyBondStatusLog::class, 'entity_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeRenewableOnly($query)
    {
        return $query->where('renewable_only', true);
    }

    // Métodos
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isExpired(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function getRemainingKwh(): float
    {
        return $this->amount_kwh - $this->donations->sum('amount_kwh');
    }

    public function canBeDonated(): bool
    {
        return $this->isActive() && !$this->isExpired() && $this->getRemainingKwh() > 0;
    }
}
