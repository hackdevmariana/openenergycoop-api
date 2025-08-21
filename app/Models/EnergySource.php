<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnergySource extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'co2_per_kwh',
        'icon',
        'description',
        'is_active',
        'color',
        'efficiency_rating',
        'capacity_factor',
        'installation_cost_per_kw',
        'maintenance_cost_per_kw',
        'lifespan_years',
        'renewable_percentage',
        'geographical_constraints',
        'weather_dependency',
        'storage_requirements',
    ];

    protected $casts = [
        'co2_per_kwh' => 'decimal:4',
        'is_active' => 'boolean',
        'efficiency_rating' => 'decimal:2',
        'capacity_factor' => 'decimal:2',
        'installation_cost_per_kw' => 'decimal:2',
        'maintenance_cost_per_kw' => 'decimal:2',
        'lifespan_years' => 'integer',
        'renewable_percentage' => 'decimal:2',
        'geographical_constraints' => 'array',
        'weather_dependency' => 'array',
        'storage_requirements' => 'array',
    ];

    // Enums
    const TYPE_SOLAR = 'solar';
    const TYPE_WIND = 'wind';
    const TYPE_HYDRO = 'hydro';
    const TYPE_BIOMASS = 'biomass';
    const TYPE_GEOTHERMAL = 'geothermal';
    const TYPE_NUCLEAR = 'nuclear';
    const TYPE_FOSSIL = 'fossil';
    const TYPE_HYBRID = 'hybrid';

    public static function getTypes(): array
    {
        return [
            self::TYPE_SOLAR => 'Solar',
            self::TYPE_WIND => 'Eólica',
            self::TYPE_HYDRO => 'Hidráulica',
            self::TYPE_BIOMASS => 'Biomasa',
            self::TYPE_GEOTHERMAL => 'Geotérmica',
            self::TYPE_NUCLEAR => 'Nuclear',
            self::TYPE_FOSSIL => 'Fósil',
            self::TYPE_HYBRID => 'Híbrida',
        ];
    }

    // Relaciones
    public function productionProjects(): BelongsToMany
    {
        return $this->belongsToMany(ProductionProject::class, 'production_project_energy_sources')
                    ->withPivot('percentage')
                    ->withTimestamps();
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRenewable($query)
    {
        return $query->where('renewable_percentage', '>', 0);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('slug', $type);
    }

    public function scopeLowCarbon($query, $maxCo2 = 0.1)
    {
        return $query->where('co2_per_kwh', '<=', $maxCo2);
    }

    public function scopeHighEfficiency($query, $minEfficiency = 80)
    {
        return $query->where('efficiency_rating', '>=', $minEfficiency);
    }

    // Métodos
    public function isRenewable(): bool
    {
        return $this->renewable_percentage > 0;
    }

    public function isLowCarbon(): bool
    {
        return $this->co2_per_kwh <= 0.1; // 100g CO2/kWh
    }

    public function isHighEfficiency(): bool
    {
        return $this->efficiency_rating >= 80;
    }

    public function getEnvironmentalImpact(): string
    {
        if ($this->co2_per_kwh <= 0.05) {
            return 'Muy Bajo';
        } elseif ($this->co2_per_kwh <= 0.1) {
            return 'Bajo';
        } elseif ($this->co2_per_kwh <= 0.3) {
            return 'Medio';
        } elseif ($this->co2_per_kwh <= 0.6) {
            return 'Alto';
        } else {
            return 'Muy Alto';
        }
    }

    public function getEfficiencyClass(): string
    {
        if ($this->efficiency_rating >= 90) {
            return 'A+';
        } elseif ($this->efficiency_rating >= 80) {
            return 'A';
        } elseif ($this->efficiency_rating >= 70) {
            return 'B';
        } elseif ($this->efficiency_rating >= 60) {
            return 'C';
        } else {
            return 'D';
        }
    }

    public function getFormattedCo2(): string
    {
        return number_format($this->co2_per_kwh, 3) . ' kg CO₂/kWh';
    }

    public function getFormattedEfficiency(): string
    {
        return number_format($this->efficiency_rating, 1) . '%';
    }

    public function getFormattedCapacityFactor(): string
    {
        return number_format($this->capacity_factor * 100, 1) . '%';
    }

    public function getFormattedInstallationCost(): string
    {
        return '€' . number_format($this->installation_cost_per_kw, 2) . '/kW';
    }

    public function getFormattedMaintenanceCost(): string
    {
        return '€' . number_format($this->maintenance_cost_per_kw, 2) . '/kW/año';
    }

    public function getFormattedRenewablePercentage(): string
    {
        return number_format($this->renewable_percentage, 1) . '%';
    }

    public function getEnvironmentalBadgeClass(): string
    {
        return match($this->getEnvironmentalImpact()) {
            'Muy Bajo' => 'bg-green-100 text-green-800',
            'Bajo' => 'bg-blue-100 text-blue-800',
            'Medio' => 'bg-yellow-100 text-yellow-800',
            'Alto' => 'bg-orange-100 text-orange-800',
            'Muy Alto' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getEfficiencyBadgeClass(): string
    {
        return match($this->getEfficiencyClass()) {
            'A+' => 'bg-green-100 text-green-800',
            'A' => 'bg-blue-100 text-blue-800',
            'B' => 'bg-yellow-100 text-yellow-800',
            'C' => 'bg-orange-100 text-orange-800',
            'D' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getTotalInstalledCapacity(): float
    {
        return $this->installations()
            ->where('active', true)
            ->sum('installed_power_kw');
    }

    public function getTotalAnnualProduction(): float
    {
        $capacity = $this->getTotalInstalledCapacity();
        $hoursPerYear = 8760; // 24 * 365
        $capacityFactor = $this->capacity_factor;
        
        return $capacity * $hoursPerYear * $capacityFactor;
    }

    public function getTotalAnnualCo2Saved(): float
    {
        $annualProduction = $this->getTotalAnnualProduction();
        $baselineCo2 = 0.5; // kg CO2/kWh para energía fósil
        
        return $annualProduction * ($baselineCo2 - $this->co2_per_kwh);
    }

    public function getFormattedTotalCapacity(): string
    {
        return number_format($this->getTotalInstalledCapacity(), 2) . ' kW';
    }

    public function getFormattedAnnualProduction(): string
    {
        return number_format($this->getTotalAnnualProduction(), 2) . ' kWh';
    }

    public function getFormattedAnnualCo2Saved(): string
    {
        return number_format($this->getTotalAnnualCo2Saved(), 2) . ' kg CO₂';
    }
}
