<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnergyPool extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'participants',
        'shared_capacity',
        'distribution_algorithm',
        'is_active',
        'organization_id',
        'energy_source_id',
        'pool_type',
        'min_participants',
        'max_participants',
        'entry_fee',
        'monthly_fee',
        'profit_sharing_percentage',
        'risk_level',
        'expected_return_rate',
        'lock_in_period_months',
        'auto_reinvest',
        'pool_manager_id',
        'pool_rules',
        'performance_history',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'participants' => 'array',
        'shared_capacity' => 'decimal:4',
        'is_active' => 'boolean',
        'min_participants' => 'integer',
        'max_participants' => 'integer',
        'entry_fee' => 'decimal:2',
        'monthly_fee' => 'decimal:2',
        'profit_sharing_percentage' => 'decimal:2',
        'expected_return_rate' => 'decimal:2',
        'lock_in_period_months' => 'integer',
        'auto_reinvest' => 'boolean',
        'pool_rules' => 'array',
        'performance_history' => 'array',
        'approved_at' => 'datetime',
    ];

    // Enums
    const DISTRIBUTION_ALGORITHM_EQUAL = 'equal';
    const DISTRIBUTION_ALGORITHM_WEIGHTED = 'weighted';
    const DISTRIBUTION_ALGORITHM_DEMAND_BASED = 'demand_based';
    const DISTRIBUTION_ALGORITHM_PERFORMANCE_BASED = 'performance_based';
    const DISTRIBUTION_ALGORITHM_TIME_BASED = 'time_based';

    const POOL_TYPE_SOLAR = 'solar';
    const POOL_TYPE_WIND = 'wind';
    const POOL_TYPE_HYDRO = 'hydro';
    const POOL_TYPE_BIOMASS = 'biomass';
    const POOL_TYPE_HYBRID = 'hybrid';
    const POOL_TYPE_STORAGE = 'storage';
    const POOL_TYPE_TRADING = 'trading';

    const RISK_LEVEL_LOW = 'low';
    const RISK_LEVEL_MEDIUM = 'medium';
    const RISK_LEVEL_HIGH = 'high';
    const RISK_LEVEL_VERY_HIGH = 'very_high';

    public static function getDistributionAlgorithms(): array
    {
        return [
            self::DISTRIBUTION_ALGORITHM_EQUAL => 'Igual',
            self::DISTRIBUTION_ALGORITHM_WEIGHTED => 'Ponderado',
            self::DISTRIBUTION_ALGORITHM_DEMAND_BASED => 'Basado en Demanda',
            self::DISTRIBUTION_ALGORITHM_PERFORMANCE_BASED => 'Basado en Rendimiento',
            self::DISTRIBUTION_ALGORITHM_TIME_BASED => 'Basado en Tiempo',
        ];
    }

    public static function getPoolTypes(): array
    {
        return [
            self::POOL_TYPE_SOLAR => 'Solar',
            self::POOL_TYPE_WIND => 'Eólica',
            self::POOL_TYPE_HYDRO => 'Hidráulica',
            self::POOL_TYPE_BIOMASS => 'Biomasa',
            self::POOL_TYPE_HYBRID => 'Híbrida',
            self::POOL_TYPE_STORAGE => 'Almacenamiento',
            self::POOL_TYPE_TRADING => 'Trading',
        ];
    }

    public static function getRiskLevels(): array
    {
        return [
            self::RISK_LEVEL_LOW => 'Bajo',
            self::RISK_LEVEL_MEDIUM => 'Medio',
            self::RISK_LEVEL_HIGH => 'Alto',
            self::RISK_LEVEL_VERY_HIGH => 'Muy Alto',
        ];
    }

    // Relaciones
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function energySource()
    {
        return $this->belongsTo(EnergySource::class);
    }

    public function poolManager()
    {
        return $this->belongsTo(User::class, 'pool_manager_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'energy_pool_participants')
                    ->withPivot('investment_amount', 'percentage', 'joined_at', 'status')
                    ->withTimestamps();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(EnergyPoolTransaction::class);
    }

    public function forecasts(): HasMany
    {
        return $this->hasMany(EnergyForecast::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('pool_type', $type);
    }

    public function scopeByRiskLevel($query, $riskLevel)
    {
        return $query->where('risk_level', $riskLevel);
    }

    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeByEnergySource($query, $energySourceId)
    {
        return $query->where('energy_source_id', $energySourceId);
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopePendingApproval($query)
    {
        return $query->whereNull('approved_at');
    }

    public function scopeLowRisk($query)
    {
        return $query->whereIn('risk_level', [self::RISK_LEVEL_LOW, self::RISK_LEVEL_MEDIUM]);
    }

    public function scopeHighRisk($query)
    {
        return $query->whereIn('risk_level', [self::RISK_LEVEL_HIGH, self::RISK_LEVEL_VERY_HIGH]);
    }

    public function scopeOpenForParticipation($query)
    {
        return $query->where('is_active', true)
                    ->whereNotNull('approved_at')
                    ->whereRaw('(SELECT COUNT(*) FROM energy_pool_participants WHERE energy_pool_id = energy_pools.id AND status = "active") < max_participants');
    }

    // Métodos
    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    public function isOpenForParticipation(): bool
    {
        if (!$this->isActive() || !$this->isApproved()) {
            return false;
        }

        $currentParticipants = $this->getActiveParticipantsCount();
        return $currentParticipants < $this->max_participants;
    }

    public function isFull(): bool
    {
        $currentParticipants = $this->getActiveParticipantsCount();
        return $currentParticipants >= $this->max_participants;
    }

    public function isLowRisk(): bool
    {
        return in_array($this->risk_level, [self::RISK_LEVEL_LOW, self::RISK_LEVEL_MEDIUM]);
    }

    public function isHighRisk(): bool
    {
        return in_array($this->risk_level, [self::RISK_LEVEL_HIGH, self::RISK_LEVEL_VERY_HIGH]);
    }

    public function getActiveParticipantsCount(): int
    {
        return $this->participants()
            ->wherePivot('status', 'active')
            ->count();
    }

    public function getTotalInvestment(): float
    {
        return $this->participants()
            ->wherePivot('status', 'active')
            ->sum('investment_amount');
    }

    public function getAvailableCapacity(): float
    {
        $usedCapacity = $this->getTotalInvestment();
        return max(0, $this->shared_capacity - $usedCapacity);
    }

    public function getUtilizationPercentage(): float
    {
        if ($this->shared_capacity <= 0) {
            return 0;
        }
        
        return min(100, ($this->getTotalInvestment() / $this->shared_capacity) * 100);
    }

    public function getParticipantPercentage(User $user): float
    {
        $participant = $this->participants()
            ->where('user_id', $user->id)
            ->wherePivot('status', 'active')
            ->first();

        if (!$participant) {
            return 0;
        }

        return $participant->pivot->percentage;
    }

    public function getParticipantInvestment(User $user): float
    {
        $participant = $this->participants()
            ->where('user_id', $user->id)
            ->wherePivot('status', 'active')
            ->first();

        if (!$participant) {
            return 0;
        }

        return $participant->pivot->investment_amount;
    }

    public function canUserJoin(User $user): bool
    {
        // Verificar si el usuario ya es participante
        if ($this->participants()->where('user_id', $user->id)->exists()) {
            return false;
        }

        // Verificar si el pool está abierto para participación
        if (!$this->isOpenForParticipation()) {
            return false;
        }

        // Verificar si el usuario cumple con los requisitos del pool
        return $this->checkUserEligibility($user);
    }

    protected function checkUserEligibility(User $user): bool
    {
        // Implementar lógica de elegibilidad según las reglas del pool
        // Por ejemplo, verificar balance mínimo, historial crediticio, etc.
        return true; // Placeholder
    }

    public function addParticipant(User $user, float $investmentAmount): bool
    {
        if (!$this->canUserJoin($user)) {
            return false;
        }

        $percentage = ($investmentAmount / $this->shared_capacity) * 100;

        $this->participants()->attach($user->id, [
            'investment_amount' => $investmentAmount,
            'percentage' => $percentage,
            'joined_at' => now(),
            'status' => 'active',
        ]);

        return true;
    }

    public function removeParticipant(User $user): bool
    {
        $participant = $this->participants()
            ->where('user_id', $user->id)
            ->wherePivot('status', 'active')
            ->first();

        if (!$participant) {
            return false;
        }

        // Verificar si se puede salir del pool (lock-in period)
        if ($this->isLockedIn($user)) {
            return false;
        }

        $this->participants()->updateExistingPivot($user->id, [
            'status' => 'inactive',
            'left_at' => now(),
        ]);

        return true;
    }

    public function isLockedIn(User $user): bool
    {
        $participant = $this->participants()
            ->where('user_id', $user->id)
            ->wherePivot('status', 'active')
            ->first();

        if (!$participant || $this->lock_in_period_months <= 0) {
            return false;
        }

        $joinedAt = $participant->pivot->joined_at;
        $lockInEnd = $joinedAt->addMonths($this->lock_in_period_months);

        return now()->isBefore($lockInEnd);
    }

    public function getExpectedMonthlyReturn(): float
    {
        $totalInvestment = $this->getTotalInvestment();
        return ($totalInvestment * $this->expected_return_rate) / 12;
    }

    public function getExpectedAnnualReturn(): float
    {
        $totalInvestment = $this->getTotalInvestment();
        return $totalInvestment * $this->expected_return_rate;
    }

    public function getFormattedSharedCapacity(): string
    {
        return number_format($this->shared_capacity, 2) . ' kWh';
    }

    public function getFormattedTotalInvestment(): string
    {
        return '€' . number_format($this->getTotalInvestment(), 2);
    }

    public function getFormattedAvailableCapacity(): string
    {
        return number_format($this->getAvailableCapacity(), 2) . ' kWh';
    }

    public function getFormattedUtilizationPercentage(): string
    {
        return number_format($this->getUtilizationPercentage(), 1) . '%';
    }

    public function getFormattedEntryFee(): string
    {
        return '€' . number_format($this->entry_fee, 2);
    }

    public function getFormattedMonthlyFee(): string
    {
        return '€' . number_format($this->monthly_fee, 2);
    }

    public function getFormattedProfitSharingPercentage(): string
    {
        return number_format($this->profit_sharing_percentage, 1) . '%';
    }

    public function getFormattedExpectedReturnRate(): string
    {
        return number_format($this->expected_return_rate * 100, 2) . '%';
    }

    public function getFormattedExpectedMonthlyReturn(): string
    {
        return '€' . number_format($this->getExpectedMonthlyReturn(), 2);
    }

    public function getFormattedExpectedAnnualReturn(): string
    {
        return '€' . number_format($this->getExpectedAnnualReturn(), 2);
    }

    public function getFormattedPoolType(): string
    {
        return self::getPoolTypes()[$this->pool_type] ?? 'Desconocido';
    }

    public function getFormattedDistributionAlgorithm(): string
    {
        return self::getDistributionAlgorithms()[$this->distribution_algorithm] ?? 'Desconocido';
    }

    public function getFormattedRiskLevel(): string
    {
        return self::getRiskLevels()[$this->risk_level] ?? 'Desconocido';
    }

    public function getStatusBadgeClass(): string
    {
        if (!$this->is_active) {
            return 'bg-red-100 text-red-800';
        }
        
        if (!$this->isApproved()) {
            return 'bg-yellow-100 text-yellow-800';
        }
        
        if ($this->isFull()) {
            return 'bg-gray-100 text-gray-800';
        }
        
        if ($this->isOpenForParticipation()) {
            return 'bg-green-100 text-green-800';
        }
        
        return 'bg-blue-100 text-blue-800';
    }

    public function getRiskBadgeClass(): string
    {
        return match($this->risk_level) {
            self::RISK_LEVEL_LOW => 'bg-green-100 text-green-800',
            self::RISK_LEVEL_MEDIUM => 'bg-blue-100 text-blue-800',
            self::RISK_LEVEL_HIGH => 'bg-yellow-100 text-yellow-800',
            self::RISK_LEVEL_VERY_HIGH => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getTypeBadgeClass(): string
    {
        return match($this->pool_type) {
            self::POOL_TYPE_SOLAR => 'bg-yellow-100 text-yellow-800',
            self::POOL_TYPE_WIND => 'bg-blue-100 text-blue-800',
            self::POOL_TYPE_HYDRO => 'bg-cyan-100 text-cyan-800',
            self::POOL_TYPE_BIOMASS => 'bg-green-100 text-green-800',
            self::POOL_TYPE_HYBRID => 'bg-purple-100 text-purple-800',
            self::POOL_TYPE_STORAGE => 'bg-indigo-100 text-indigo-800',
            self::POOL_TYPE_TRADING => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
