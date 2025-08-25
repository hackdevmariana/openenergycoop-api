<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class EnergySource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'source_type',
        'status',
        'energy_category',
        'installed_capacity_mw',
        'operational_capacity_mw',
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
        'equipment_details',
        'maintenance_schedule',
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
        'installed_capacity_mw' => 'decimal:2',
        'operational_capacity_mw' => 'decimal:2',
        'efficiency_rating' => 'decimal:2',
        'availability_factor' => 'decimal:2',
        'capacity_factor' => 'decimal:2',
        'annual_production_mwh' => 'decimal:2',
        'monthly_production_mwh' => 'decimal:2',
        'daily_production_mwh' => 'decimal:2',
        'hourly_production_mwh' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'commissioning_date' => 'date',
        'decommissioning_date' => 'date',
        'expected_lifespan_years' => 'integer',
        'construction_cost' => 'decimal:2',
        'operational_cost_per_mwh' => 'decimal:2',
        'maintenance_cost_per_mwh' => 'decimal:2',
        'approved_at' => 'datetime',
        'equipment_details' => 'array',
        'maintenance_schedule' => 'array',
        'performance_metrics' => 'array',
        'environmental_data' => 'array',
        'regulatory_documents' => 'array',
        'tags' => 'array',
    ];

    // Enums
    const SOURCE_TYPE_SOLAR = 'solar';
    const SOURCE_TYPE_WIND = 'wind';
    const SOURCE_TYPE_HYDROELECTRIC = 'hydroelectric';
    const SOURCE_TYPE_BIOMASS = 'biomass';
    const SOURCE_TYPE_GEOTHERMAL = 'geothermal';
    const SOURCE_TYPE_NUCLEAR = 'nuclear';
    const SOURCE_TYPE_FOSSIL_FUEL = 'fossil_fuel';
    const SOURCE_TYPE_HYBRID = 'hybrid';
    const SOURCE_TYPE_OTHER = 'other';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_DECOMMISSIONED = 'decommissioned';
    const STATUS_PLANNED = 'planned';
    const STATUS_UNDER_CONSTRUCTION = 'under_construction';

    const ENERGY_CATEGORY_RENEWABLE = 'renewable';
    const ENERGY_CATEGORY_NON_RENEWABLE = 'non_renewable';
    const ENERGY_CATEGORY_HYBRID = 'hybrid';

    public static function getSourceTypes(): array
    {
        return [
            self::SOURCE_TYPE_SOLAR => 'Solar',
            self::SOURCE_TYPE_WIND => 'Eólica',
            self::SOURCE_TYPE_HYDROELECTRIC => 'Hidroeléctrica',
            self::SOURCE_TYPE_BIOMASS => 'Biomasa',
            self::SOURCE_TYPE_GEOTHERMAL => 'Geotérmica',
            self::SOURCE_TYPE_NUCLEAR => 'Nuclear',
            self::SOURCE_TYPE_FOSSIL_FUEL => 'Combustible Fósil',
            self::SOURCE_TYPE_HYBRID => 'Híbrida',
            self::SOURCE_TYPE_OTHER => 'Otra',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Activa',
            self::STATUS_INACTIVE => 'Inactiva',
            self::STATUS_MAINTENANCE => 'Mantenimiento',
            self::STATUS_DECOMMISSIONED => 'Desmantelada',
            self::STATUS_PLANNED => 'Planificada',
            self::STATUS_UNDER_CONSTRUCTION => 'En Construcción',
        ];
    }

    public static function getEnergyCategories(): array
    {
        return [
            self::ENERGY_CATEGORY_RENEWABLE => 'Renovable',
            self::ENERGY_CATEGORY_NON_RENEWABLE => 'No Renovable',
            self::ENERGY_CATEGORY_HYBRID => 'Híbrida',
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

    public function installations(): HasMany
    {
        return $this->hasMany(EnergyInstallation::class);
    }

    public function meters(): HasMany
    {
        return $this->hasMany(EnergyMeter::class);
    }

    public function readings(): HasMany
    {
        return $this->hasMany(EnergyReading::class);
    }

    public function forecasts(): HasMany
    {
        return $this->hasMany(EnergyForecast::class);
    }

    public function productionProjects(): HasMany
    {
        return $this->hasMany(ProductionProject::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('source_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('energy_category', $category);
    }

    public function scopeRenewable($query)
    {
        return $query->where('energy_category', self::ENERGY_CATEGORY_RENEWABLE);
    }

    public function scopeNonRenewable($query)
    {
        return $query->where('energy_category', self::ENERGY_CATEGORY_NON_RENEWABLE);
    }

    public function scopeHybrid($query)
    {
        return $query->where('energy_category', self::ENERGY_CATEGORY_HYBRID);
    }

    public function scopeHighEfficiency($query, $minEfficiency = 80)
    {
        return $query->where('efficiency_rating', '>=', $minEfficiency);
    }

    public function scopeHighCapacity($query, $minCapacity = 100)
    {
        return $query->where('operational_capacity_mw', '>=', $minCapacity);
    }

    public function scopeByRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function scopeOperational($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeUnderConstruction($query)
    {
        return $query->where('status', self::STATUS_UNDER_CONSTRUCTION);
    }

    public function scopeMaintenance($query)
    {
        return $query->where('status', self::STATUS_MAINTENANCE);
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

    public function isDecommissioned(): bool
    {
        return $this->status === self::STATUS_DECOMMISSIONED;
    }

    public function isPlanned(): bool
    {
        return $this->status === self::STATUS_PLANNED;
    }

    public function isUnderConstruction(): bool
    {
        return $this->status === self::STATUS_UNDER_CONSTRUCTION;
    }

    public function isRenewable(): bool
    {
        return $this->energy_category === self::ENERGY_CATEGORY_RENEWABLE;
    }

    public function isNonRenewable(): bool
    {
        return $this->energy_category === self::ENERGY_CATEGORY_NON_RENEWABLE;
    }

    public function isHybrid(): bool
    {
        return $this->energy_category === self::ENERGY_CATEGORY_HYBRID;
    }

    public function isOperational(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    // Métodos de cálculo
    public function getUtilizationPercentage(): float
    {
        if ($this->installed_capacity_mw <= 0) {
            return 0;
        }
        
        return ($this->operational_capacity_mw / $this->installed_capacity_mw) * 100;
    }

    public function getAnnualEfficiency(): float
    {
        $hoursPerYear = 8760; // 24 * 365
        $theoreticalProduction = $this->installed_capacity_mw * $hoursPerYear;
        
        if ($theoreticalProduction <= 0) {
            return 0;
        }
        
        return ($this->annual_production_mwh / $theoreticalProduction) * 100;
    }

    public function getMonthlyAverage(): float
    {
        return $this->annual_production_mwh / 12;
    }

    public function getDailyAverage(): float
    {
        return $this->annual_production_mwh / 365;
    }

    public function getHourlyAverage(): float
    {
        return $this->annual_production_mwh / 8760;
    }

    public function getRemainingLifespan(): int
    {
        if (!$this->commissioning_date || !$this->expected_lifespan_years) {
            return 0;
        }
        
        $yearsSinceCommissioning = $this->commissioning_date->diffInYears(now());
        return max(0, $this->expected_lifespan_years - $yearsSinceCommissioning);
    }

    public function getAgeInYears(): int
    {
        if (!$this->commissioning_date) {
            return 0;
        }
        
        return $this->commissioning_date->diffInYears(now());
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

    // Métodos de formato
    public function getFormattedSourceType(): string
    {
        return self::getSourceTypes()[$this->source_type] ?? 'Desconocido';
    }

    public function getFormattedStatus(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getFormattedEnergyCategory(): string
    {
        return self::getEnergyCategories()[$this->energy_category] ?? 'Desconocida';
    }

    public function getFormattedInstalledCapacity(): string
    {
        return number_format($this->installed_capacity_mw, 2) . ' MW';
    }

    public function getFormattedOperationalCapacity(): string
    {
        return number_format($this->operational_capacity_mw, 2) . ' MW';
    }

    public function getFormattedEfficiencyRating(): string
    {
        return number_format($this->efficiency_rating, 2) . '%';
    }

    public function getFormattedAvailabilityFactor(): string
    {
        return number_format($this->availability_factor, 2) . '%';
    }

    public function getFormattedCapacityFactor(): string
    {
        return number_format($this->capacity_factor, 2) . '%';
    }

    public function getFormattedAnnualProduction(): string
    {
        return number_format($this->annual_production_mwh, 2) . ' MWh';
    }

    public function getFormattedMonthlyProduction(): string
    {
        return number_format($this->monthly_production_mwh, 2) . ' MWh';
    }

    public function getFormattedDailyProduction(): string
    {
        return number_format($this->daily_production_mwh, 2) . ' MWh';
    }

    public function getFormattedHourlyProduction(): string
    {
        return number_format($this->hourly_production_mwh, 2) . ' MWh';
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

    public function getFormattedAnnualEfficiency(): string
    {
        return number_format($this->getAnnualEfficiency(), 1) . '%';
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
            self::STATUS_DECOMMISSIONED => 'bg-red-100 text-red-800',
            self::STATUS_PLANNED => 'bg-blue-100 text-blue-800',
            self::STATUS_UNDER_CONSTRUCTION => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getSourceTypeBadgeClass(): string
    {
        return match($this->source_type) {
            self::SOURCE_TYPE_SOLAR => 'bg-yellow-100 text-yellow-800',
            self::SOURCE_TYPE_WIND => 'bg-blue-100 text-blue-800',
            self::SOURCE_TYPE_HYDROELECTRIC => 'bg-cyan-100 text-cyan-800',
            self::SOURCE_TYPE_BIOMASS => 'bg-green-100 text-green-800',
            self::SOURCE_TYPE_GEOTHERMAL => 'bg-red-100 text-red-800',
            self::SOURCE_TYPE_NUCLEAR => 'bg-purple-100 text-purple-800',
            self::SOURCE_TYPE_FOSSIL_FUEL => 'bg-gray-100 text-gray-800',
            self::SOURCE_TYPE_HYBRID => 'bg-indigo-100 text-indigo-800',
            self::SOURCE_TYPE_OTHER => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getEnergyCategoryBadgeClass(): string
    {
        return match($this->energy_category) {
            self::ENERGY_CATEGORY_RENEWABLE => 'bg-green-100 text-green-800',
            self::ENERGY_CATEGORY_NON_RENEWABLE => 'bg-red-100 text-red-800',
            self::ENERGY_CATEGORY_HYBRID => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getEfficiencyBadgeClass(): string
    {
        if ($this->efficiency_rating >= 90) {
            return 'bg-green-100 text-green-800';
        } elseif ($this->efficiency_rating >= 80) {
            return 'bg-blue-100 text-blue-800';
        } elseif ($this->efficiency_rating >= 70) {
            return 'bg-yellow-100 text-yellow-800';
        } elseif ($this->efficiency_rating >= 60) {
            return 'bg-orange-100 text-orange-800';
        } else {
            return 'bg-red-100 text-red-800';
        }
    }
}
