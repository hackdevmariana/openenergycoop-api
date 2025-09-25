<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class EnergyPool extends Model
{
    use HasFactory;

    protected $fillable = [
        'pool_number',
        'name',
        'description',
        'pool_type',
        'status',
        'energy_category',
        'total_capacity_mw',
        'available_capacity_mw',
        'reserved_capacity_mw',
        'utilized_capacity_mw',
        'efficiency_rating',
        'availability_factor',
        'capacity_factor',
        'annual_production_mwh',
        'monthly_production_mwh',
        'daily_production_mwh',
        'hourly_production_mwh',
        'location_address',
        'latitude',
        'longitude',
        'region',
        'country',
        'commissioning_date',
        'decommissioning_date',
        'expected_lifespan_years',
        'construction_cost',
        'operational_cost_per_mwh',
        'maintenance_cost_per_mwh',
        'technical_specifications',
        'environmental_impact',
        'regulatory_compliance',
        'safety_features',
        'pool_members',
        'pool_operators',
        'pool_governance',
        'trading_rules',
        'settlement_procedures',
        'risk_management',
        'performance_metrics',
        'environmental_data',
        'regulatory_documents',
        'tags',
        'managed_by',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'total_capacity_mw' => 'decimal:2',
        'available_capacity_mw' => 'decimal:2',
        'reserved_capacity_mw' => 'decimal:2',
        'utilized_capacity_mw' => 'decimal:2',
        'efficiency_rating' => 'decimal:2',
        'availability_factor' => 'decimal:2',
        'capacity_factor' => 'decimal:2',
        'annual_production_mwh' => 'decimal:2',
        'monthly_production_mwh' => 'decimal:2',
        'daily_production_mwh' => 'decimal:2',
        'hourly_production_mwh' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'expected_lifespan_years' => 'integer',
        'construction_cost' => 'decimal:2',
        'operational_cost_per_mwh' => 'decimal:2',
        'maintenance_cost_per_mwh' => 'decimal:2',
        'commissioning_date' => 'date',
        'decommissioning_date' => 'date',
        'approved_at' => 'datetime',
        'pool_members' => 'array',
        'pool_operators' => 'array',
        'pool_governance' => 'array',
        'trading_rules' => 'array',
        'settlement_procedures' => 'array',
        'risk_management' => 'array',
        'performance_metrics' => 'array',
        'environmental_data' => 'array',
        'regulatory_documents' => 'array',
        'tags' => 'array',
    ];

    // Enums
    const POOL_TYPE_TRADING = 'trading';
    const POOL_TYPE_RESERVE = 'reserve';
    const POOL_TYPE_BALANCING = 'balancing';
    const POOL_TYPE_ANCILLARY = 'ancillary';
    const POOL_TYPE_CAPACITY = 'capacity';
    const POOL_TYPE_DEMAND_RESPONSE = 'demand_response';
    const POOL_TYPE_VIRTUAL = 'virtual';
    const POOL_TYPE_HYBRID = 'hybrid';
    const POOL_TYPE_OTHER = 'other';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_CLOSED = 'closed';
    const STATUS_PLANNED = 'planned';

    const ENERGY_CATEGORY_RENEWABLE = 'renewable';
    const ENERGY_CATEGORY_NON_RENEWABLE = 'non_renewable';
    const ENERGY_CATEGORY_HYBRID = 'hybrid';
    const ENERGY_CATEGORY_STORAGE = 'storage';
    const ENERGY_CATEGORY_DEMAND = 'demand';
    const ENERGY_CATEGORY_OTHER = 'other';

    // Algoritmos de distribución
    const DISTRIBUTION_ALGORITHM_PROPORTIONAL = 'proportional';
    const DISTRIBUTION_ALGORITHM_EQUAL_SHARE = 'equal_share';
    const DISTRIBUTION_ALGORITHM_PRIORITY_BASED = 'priority_based';
    const DISTRIBUTION_ALGORITHM_TIME_BASED = 'time_based';
    const DISTRIBUTION_ALGORITHM_CAPACITY_BASED = 'capacity_based';
    const DISTRIBUTION_ALGORITHM_DEMAND_DRIVEN = 'demand_driven';
    const DISTRIBUTION_ALGORITHM_COST_OPTIMIZED = 'cost_optimized';
    const DISTRIBUTION_ALGORITHM_RENEWABLES_FIRST = 'renewables_first';
    const DISTRIBUTION_ALGORITHM_LOAD_FOLLOWING = 'load_following';
    const DISTRIBUTION_ALGORITHM_MARKET_BASED = 'market_based';
    const DISTRIBUTION_ALGORITHM_AUCTION_BASED = 'auction_based';
    const DISTRIBUTION_ALGORITHM_BLOCKCHAIN_BASED = 'blockchain_based';
    const DISTRIBUTION_ALGORITHM_AI_OPTIMIZED = 'ai_optimized';
    const DISTRIBUTION_ALGORITHM_CUSTOM = 'custom';

    public static function getPoolTypes(): array
    {
        return [
            self::POOL_TYPE_TRADING => 'Trading',
            self::POOL_TYPE_RESERVE => 'Reserva',
            self::POOL_TYPE_BALANCING => 'Balanceo',
            self::POOL_TYPE_ANCILLARY => 'Auxiliar',
            self::POOL_TYPE_CAPACITY => 'Capacidad',
            self::POOL_TYPE_DEMAND_RESPONSE => 'Respuesta a la Demanda',
            self::POOL_TYPE_VIRTUAL => 'Virtual',
            self::POOL_TYPE_HYBRID => 'Híbrido',
            self::POOL_TYPE_OTHER => 'Otro',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_INACTIVE => 'Inactivo',
            self::STATUS_MAINTENANCE => 'Mantenimiento',
            self::STATUS_SUSPENDED => 'Suspendido',
            self::STATUS_CLOSED => 'Cerrado',
            self::STATUS_PLANNED => 'Planificado',
        ];
    }

    public static function getEnergyCategories(): array
    {
        return [
            self::ENERGY_CATEGORY_RENEWABLE => 'Renovable',
            self::ENERGY_CATEGORY_NON_RENEWABLE => 'No Renovable',
            self::ENERGY_CATEGORY_HYBRID => 'Híbrido',
            self::ENERGY_CATEGORY_STORAGE => 'Almacenamiento',
            self::ENERGY_CATEGORY_DEMAND => 'Demanda',
            self::ENERGY_CATEGORY_OTHER => 'Otro',
        ];
    }

    public static function getDistributionAlgorithms(): array
    {
        return [
            self::DISTRIBUTION_ALGORITHM_PROPORTIONAL => 'Proporcional',
            self::DISTRIBUTION_ALGORITHM_EQUAL_SHARE => 'Reparto Igualitario',
            self::DISTRIBUTION_ALGORITHM_PRIORITY_BASED => 'Basado en Prioridad',
            self::DISTRIBUTION_ALGORITHM_TIME_BASED => 'Basado en Tiempo',
            self::DISTRIBUTION_ALGORITHM_CAPACITY_BASED => 'Basado en Capacidad',
            self::DISTRIBUTION_ALGORITHM_DEMAND_DRIVEN => 'Basado en Demanda',
            self::DISTRIBUTION_ALGORITHM_COST_OPTIMIZED => 'Optimización de Costos',
            self::DISTRIBUTION_ALGORITHM_RENEWABLES_FIRST => 'Renovables Primero',
            self::DISTRIBUTION_ALGORITHM_LOAD_FOLLOWING => 'Seguimiento de Carga',
            self::DISTRIBUTION_ALGORITHM_MARKET_BASED => 'Basado en Mercado',
            self::DISTRIBUTION_ALGORITHM_AUCTION_BASED => 'Basado en Subasta',
            self::DISTRIBUTION_ALGORITHM_BLOCKCHAIN_BASED => 'Basado en Blockchain',
            self::DISTRIBUTION_ALGORITHM_AI_OPTIMIZED => 'Optimizado por IA',
            self::DISTRIBUTION_ALGORITHM_CUSTOM => 'Personalizado',
        ];
    }

    // Relaciones
    public function managedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'managed_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function forecasts(): HasMany
    {
        return $this->hasMany(EnergyForecast::class);
    }

    public function tradingOrders(): HasMany
    {
        return $this->hasMany(EnergyTradingOrder::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(EnergyTransfer::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPoolType($query, $poolType)
    {
        return $query->where('pool_type', $poolType);
    }

    public function scopeByEnergyCategory($query, $energyCategory)
    {
        return $query->where('energy_category', $energyCategory);
    }

    public function scopeByRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function scopeByManagedBy($query, $managedBy)
    {
        return $query->where('managed_by', $managedBy);
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopePendingApproval($query)
    {
        return $query->whereNull('approved_at');
    }

    public function scopeMaintenance($query)
    {
        return $query->where('status', self::STATUS_MAINTENANCE);
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', self::STATUS_SUSPENDED);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    public function scopePlanned($query)
    {
        return $query->where('status', self::STATUS_PLANNED);
    }

    public function scopeTrading($query)
    {
        return $query->where('pool_type', self::POOL_TYPE_TRADING);
    }

    public function scopeReserve($query)
    {
        return $query->where('pool_type', self::POOL_TYPE_RESERVE);
    }

    public function scopeBalancing($query)
    {
        return $query->where('pool_type', self::POOL_TYPE_BALANCING);
    }

    public function scopeAncillary($query)
    {
        return $query->where('pool_type', self::POOL_TYPE_ANCILLARY);
    }

    public function scopeCapacity($query)
    {
        return $query->where('pool_type', self::POOL_TYPE_CAPACITY);
    }

    public function scopeDemandResponse($query)
    {
        return $query->where('pool_type', self::POOL_TYPE_DEMAND_RESPONSE);
    }

    public function scopeVirtual($query)
    {
        return $query->where('pool_type', self::POOL_TYPE_VIRTUAL);
    }

    public function scopeHybrid($query)
    {
        return $query->where('pool_type', self::POOL_TYPE_HYBRID);
    }

    public function scopeRenewable($query)
    {
        return $query->where('energy_category', self::ENERGY_CATEGORY_RENEWABLE);
    }

    public function scopeNonRenewable($query)
    {
        return $query->where('energy_category', self::ENERGY_CATEGORY_NON_RENEWABLE);
    }

    public function scopeStorage($query)
    {
        return $query->where('energy_category', self::ENERGY_CATEGORY_STORAGE);
    }

    public function scopeDemand($query)
    {
        return $query->where('energy_category', self::ENERGY_CATEGORY_DEMAND);
    }

    public function scopeHighEfficiency($query, $minEfficiency = 80)
    {
        return $query->where('efficiency_rating', '>=', $minEfficiency);
    }

    public function scopeHighAvailability($query, $minAvailability = 90)
    {
        return $query->where('availability_factor', '>=', $minAvailability);
    }

    public function scopeHighCapacityFactor($query, $minCapacityFactor = 70)
    {
        return $query->where('capacity_factor', '>=', $minCapacityFactor);
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

    public function isMaintenance(): bool
    {
        return $this->status === self::STATUS_MAINTENANCE;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function isPlanned(): bool
    {
        return $this->status === self::STATUS_PLANNED;
    }

    public function isTrading(): bool
    {
        return $this->pool_type === self::POOL_TYPE_TRADING;
    }

    public function isReserve(): bool
    {
        return $this->pool_type === self::POOL_TYPE_RESERVE;
    }

    public function isBalancing(): bool
    {
        return $this->pool_type === self::POOL_TYPE_BALANCING;
    }

    public function isAncillary(): bool
    {
        return $this->pool_type === self::POOL_TYPE_ANCILLARY;
    }

    public function isCapacity(): bool
    {
        return $this->pool_type === self::POOL_TYPE_CAPACITY;
    }

    public function isDemandResponse(): bool
    {
        return $this->pool_type === self::POOL_TYPE_DEMAND_RESPONSE;
    }

    public function isVirtual(): bool
    {
        return $this->pool_type === self::POOL_TYPE_VIRTUAL;
    }

    public function isHybrid(): bool
    {
        return $this->pool_type === self::POOL_TYPE_HYBRID;
    }

    public function isRenewable(): bool
    {
        return $this->energy_category === self::ENERGY_CATEGORY_RENEWABLE;
    }

    public function isNonRenewable(): bool
    {
        return $this->energy_category === self::ENERGY_CATEGORY_NON_RENEWABLE;
    }

    public function isStorage(): bool
    {
        return $this->energy_category === self::ENERGY_CATEGORY_STORAGE;
    }

    public function isDemand(): bool
    {
        return $this->energy_category === self::ENERGY_CATEGORY_DEMAND;
    }

    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    public function isCommissioned(): bool
    {
        return !is_null($this->commissioning_date);
    }

    public function isDecommissioned(): bool
    {
        return !is_null($this->decommissioning_date);
    }

    public function hasAvailableCapacity(): bool
    {
        return $this->available_capacity_mw > 0;
    }

    public function isFullyUtilized(): bool
    {
        return $this->utilized_capacity_mw >= $this->total_capacity_mw;
    }

    public function isHighEfficiency(): bool
    {
        return $this->efficiency_rating >= 80;
    }

    public function isHighAvailability(): bool
    {
        return $this->availability_factor >= 90;
    }

    public function isHighCapacityFactor(): bool
    {
        return $this->capacity_factor >= 70;
    }

    // Métodos de cálculo
    public function getUtilizationPercentage(): float
    {
        if ($this->total_capacity_mw <= 0) {
            return 0;
        }
        
        return min(100, ($this->utilized_capacity_mw / $this->total_capacity_mw) * 100);
    }

    public function getReservationPercentage(): float
    {
        if ($this->total_capacity_mw <= 0) {
            return 0;
        }
        
        return min(100, ($this->reserved_capacity_mw / $this->total_capacity_mw) * 100);
    }

    public function getAvailablePercentage(): float
    {
        if ($this->total_capacity_mw <= 0) {
            return 0;
        }
        
        return max(0, 100 - $this->getUtilizationPercentage() - $this->getReservationPercentage());
    }

    public function getAgeInYears(): int
    {
        if (!$this->commissioning_date) {
            return 0;
        }
        
        return $this->commissioning_date->diffInYears(now());
    }

    public function getRemainingLifespan(): int
    {
        if (!$this->expected_lifespan_years) {
            return 0;
        }
        
        return max(0, $this->expected_lifespan_years - $this->getAgeInYears());
    }

    public function getTotalAnnualCost(): float
    {
        $operationalCost = $this->annual_production_mwh * ($this->operational_cost_per_mwh ?? 0);
        $maintenanceCost = $this->annual_production_mwh * ($this->maintenance_cost_per_mwh ?? 0);
        
        return $operationalCost + $maintenanceCost;
    }

    public function getCostPerMwh(): float
    {
        if ($this->annual_production_mwh <= 0) {
            return 0;
        }
        
        return $this->getTotalAnnualCost() / $this->annual_production_mwh;
    }

    public function getDailyProduction(): float
    {
        return $this->daily_production_mwh ?? 0;
    }

    public function getMonthlyProduction(): float
    {
        return $this->monthly_production_mwh ?? 0;
    }

    public function getAnnualProduction(): float
    {
        return $this->annual_production_mwh ?? 0;
    }

    public function getHourlyProduction(): float
    {
        return $this->hourly_production_mwh ?? 0;
    }

    // Métodos de formato
    public function getFormattedPoolType(): string
    {
        return self::getPoolTypes()[$this->pool_type] ?? 'Desconocido';
    }

    public function getFormattedStatus(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getFormattedEnergyCategory(): string
    {
        return self::getEnergyCategories()[$this->energy_category] ?? 'Desconocido';
    }

    public function getFormattedTotalCapacity(): string
    {
        return number_format($this->total_capacity_mw, 2) . ' MW';
    }

    public function getFormattedAvailableCapacity(): string
    {
        return number_format($this->available_capacity_mw, 2) . ' MW';
    }

    public function getFormattedReservedCapacity(): string
    {
        return number_format($this->reserved_capacity_mw, 2) . ' MW';
    }

    public function getFormattedUtilizedCapacity(): string
    {
        return number_format($this->utilized_capacity_mw, 2) . ' MW';
    }

    public function getFormattedEfficiencyRating(): string
    {
        return $this->efficiency_rating ? number_format($this->efficiency_rating, 2) . '%' : 'N/A';
    }

    public function getFormattedAvailabilityFactor(): string
    {
        return $this->availability_factor ? number_format($this->availability_factor, 2) . '%' : 'N/A';
    }

    public function getFormattedCapacityFactor(): string
    {
        return $this->capacity_factor ? number_format($this->capacity_factor, 2) . '%' : 'N/A';
    }

    public function getFormattedAnnualProduction(): string
    {
        return $this->annual_production_mwh ? number_format($this->annual_production_mwh, 2) . ' MWh' : 'N/A';
    }

    public function getFormattedMonthlyProduction(): string
    {
        return $this->monthly_production_mwh ? number_format($this->monthly_production_mwh, 2) . ' MWh' : 'N/A';
    }

    public function getFormattedDailyProduction(): string
    {
        return $this->daily_production_mwh ? number_format($this->daily_production_mwh, 2) . ' MWh' : 'N/A';
    }

    public function getFormattedHourlyProduction(): string
    {
        return $this->hourly_production_mwh ? number_format($this->hourly_production_mwh, 2) . ' MWh' : 'N/A';
    }

    public function getFormattedConstructionCost(): string
    {
        return $this->construction_cost ? '$' . number_format($this->construction_cost, 2) : 'N/A';
    }

    public function getFormattedOperationalCost(): string
    {
        return $this->operational_cost_per_mwh ? '$' . number_format($this->operational_cost_per_mwh, 2) . '/MWh' : 'N/A';
    }

    public function getFormattedMaintenanceCost(): string
    {
        return $this->maintenance_cost_per_mwh ? '$' . number_format($this->maintenance_cost_per_mwh, 2) . '/MWh' : 'N/A';
    }

    public function getFormattedCommissioningDate(): string
    {
        return $this->commissioning_date ? $this->commissioning_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedDecommissioningDate(): string
    {
        return $this->decommissioning_date ? $this->decommissioning_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedUtilizationPercentage(): string
    {
        return number_format($this->getUtilizationPercentage(), 1) . '%';
    }

    public function getFormattedReservationPercentage(): string
    {
        return number_format($this->getReservationPercentage(), 1) . '%';
    }

    public function getFormattedAvailablePercentage(): string
    {
        return number_format($this->getAvailablePercentage(), 1) . '%';
    }

    public function getFormattedAgeInYears(): string
    {
        return $this->getAgeInYears() . ' años';
    }

    public function getFormattedRemainingLifespan(): string
    {
        $remaining = $this->getRemainingLifespan();
        if ($remaining > 0) {
            return $remaining . ' años';
        } else {
            return 'Vencido';
        }
    }

    public function getFormattedTotalAnnualCost(): string
    {
        return '$' . number_format($this->getTotalAnnualCost(), 2);
    }

    public function getFormattedCostPerMwh(): string
    {
        return '$' . number_format($this->getCostPerMwh(), 2) . '/MWh';
    }

    // Clases de badges para Filament
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'bg-green-100 text-green-800',
            self::STATUS_INACTIVE => 'bg-gray-100 text-gray-800',
            self::STATUS_MAINTENANCE => 'bg-yellow-100 text-yellow-800',
            self::STATUS_SUSPENDED => 'bg-orange-100 text-orange-800',
            self::STATUS_CLOSED => 'bg-red-100 text-red-800',
            self::STATUS_PLANNED => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getPoolTypeBadgeClass(): string
    {
        return match($this->pool_type) {
            self::POOL_TYPE_TRADING => 'bg-blue-100 text-blue-800',
            self::POOL_TYPE_RESERVE => 'bg-green-100 text-green-800',
            self::POOL_TYPE_BALANCING => 'bg-yellow-100 text-yellow-800',
            self::POOL_TYPE_ANCILLARY => 'bg-purple-100 text-purple-800',
            self::POOL_TYPE_CAPACITY => 'bg-indigo-100 text-indigo-800',
            self::POOL_TYPE_DEMAND_RESPONSE => 'bg-pink-100 text-pink-800',
            self::POOL_TYPE_VIRTUAL => 'bg-cyan-100 text-cyan-800',
            self::POOL_TYPE_HYBRID => 'bg-orange-100 text-orange-800',
            self::POOL_TYPE_OTHER => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getEnergyCategoryBadgeClass(): string
    {
        return match($this->energy_category) {
            self::ENERGY_CATEGORY_RENEWABLE => 'bg-green-100 text-green-800',
            self::ENERGY_CATEGORY_NON_RENEWABLE => 'bg-red-100 text-red-800',
            self::ENERGY_CATEGORY_HYBRID => 'bg-blue-100 text-blue-800',
            self::ENERGY_CATEGORY_STORAGE => 'bg-purple-100 text-purple-800',
            self::ENERGY_CATEGORY_DEMAND => 'bg-yellow-100 text-yellow-800',
            self::ENERGY_CATEGORY_OTHER => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getEfficiencyBadgeClass(): string
    {
        if (!$this->efficiency_rating) {
            return 'bg-gray-100 text-gray-800';
        }
        
        if ($this->efficiency_rating >= 90) {
            return 'bg-green-100 text-green-800';
        } elseif ($this->efficiency_rating >= 80) {
            return 'bg-blue-100 text-blue-800';
        } elseif ($this->efficiency_rating >= 70) {
            return 'bg-yellow-100 text-yellow-800';
        } else {
            return 'bg-red-100 text-red-800';
        }
    }

    public function getAvailabilityBadgeClass(): string
    {
        if (!$this->availability_factor) {
            return 'bg-gray-100 text-gray-800';
        }
        
        if ($this->availability_factor >= 95) {
            return 'bg-green-100 text-green-800';
        } elseif ($this->availability_factor >= 90) {
            return 'bg-blue-100 text-blue-800';
        } elseif ($this->availability_factor >= 80) {
            return 'bg-yellow-100 text-yellow-800';
        } else {
            return 'bg-red-100 text-red-800';
        }
    }

    public function getCapacityFactorBadgeClass(): string
    {
        if (!$this->capacity_factor) {
            return 'bg-gray-100 text-gray-800';
        }
        
        if ($this->capacity_factor >= 80) {
            return 'bg-green-100 text-green-800';
        } elseif ($this->capacity_factor >= 70) {
            return 'bg-blue-100 text-blue-800';
        } elseif ($this->capacity_factor >= 60) {
            return 'bg-yellow-100 text-yellow-800';
        } else {
            return 'bg-red-100 text-red-800';
        }
    }
}
