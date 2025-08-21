<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsumptionPoint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'installation_id',
        'name',
        'address',
        'serial_number',
        'meter_type',
        'active',
        'description',
        'location_type',
        'coordinates',
        'timezone',
        'tariff_type',
        'peak_hours',
        'off_peak_hours',
        'seasonal_variations',
        'demand_charge',
        'energy_charge',
        'tax_rate',
        'subsidy_rate',
        'smart_meter_features',
        'last_reading_date',
        'next_reading_date',
        'billing_cycle',
        'contract_number',
        'supplier_id',
    ];

    protected $casts = [
        'active' => 'boolean',
        'coordinates' => 'array',
        'peak_hours' => 'array',
        'off_peak_hours' => 'array',
        'seasonal_variations' => 'array',
        'demand_charge' => 'decimal:4',
        'energy_charge' => 'decimal:4',
        'tax_rate' => 'decimal:4',
        'subsidy_rate' => 'decimal:4',
        'smart_meter_features' => 'array',
        'last_reading_date' => 'datetime',
        'next_reading_date' => 'datetime',
    ];

    // Enums
    const METER_TYPE_SMART = 'smart';
    const METER_TYPE_ANALOG = 'analog';
    const METER_TYPE_DIGITAL = 'digital';
    const METER_TYPE_PREPAID = 'prepaid';

    const LOCATION_TYPE_RESIDENTIAL = 'residential';
    const LOCATION_TYPE_COMMERCIAL = 'commercial';
    const LOCATION_TYPE_INDUSTRIAL = 'industrial';
    const LOCATION_TYPE_AGRICULTURAL = 'agricultural';
    const LOCATION_TYPE_PUBLIC = 'public';

    const TARIFF_TYPE_SIMPLE = 'simple';
    const TARIFF_TYPE_TIME_OF_USE = 'time_of_use';
    const TARIFF_TYPE_DEMAND = 'demand';
    const TARIFF_TYPE_SEASONAL = 'seasonal';
    const TARIFF_TYPE_TIERED = 'tiered';

    const BILLING_CYCLE_MONTHLY = 'monthly';
    const BILLING_CYCLE_BI_MONTHLY = 'bi_monthly';
    const BILLING_CYCLE_QUARTERLY = 'quarterly';
    const BILLING_CYCLE_ANNUAL = 'annual';

    public static function getMeterTypes(): array
    {
        return [
            self::METER_TYPE_SMART => 'Inteligente',
            self::METER_TYPE_ANALOG => 'Analógico',
            self::METER_TYPE_DIGITAL => 'Digital',
            self::METER_TYPE_PREPAID => 'Prepago',
        ];
    }

    public static function getLocationTypes(): array
    {
        return [
            self::LOCATION_TYPE_RESIDENTIAL => 'Residencial',
            self::LOCATION_TYPE_COMMERCIAL => 'Comercial',
            self::LOCATION_TYPE_INDUSTRIAL => 'Industrial',
            self::LOCATION_TYPE_AGRICULTURAL => 'Agrícola',
            self::LOCATION_TYPE_PUBLIC => 'Público',
        ];
    }

    public static function getTariffTypes(): array
    {
        return [
            self::TARIFF_TYPE_SIMPLE => 'Simple',
            self::TARIFF_TYPE_TIME_OF_USE => 'Por Horario de Uso',
            self::TARIFF_TYPE_DEMAND => 'Por Demanda',
            self::TARIFF_TYPE_SEASONAL => 'Estacional',
            self::TARIFF_TYPE_TIERED => 'Por Escalones',
        ];
    }

    public static function getBillingCycles(): array
    {
        return [
            self::BILLING_CYCLE_MONTHLY => 'Mensual',
            self::BILLING_CYCLE_BI_MONTHLY => 'Bimestral',
            self::BILLING_CYCLE_QUARTERLY => 'Trimestral',
            self::BILLING_CYCLE_ANNUAL => 'Anual',
        ];
    }

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function installation(): BelongsTo
    {
        return $this->belongsTo(EnergyInstallation::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'supplier_id');
    }

    public function meters(): HasMany
    {
        return $this->hasMany(EnergyMeter::class, 'meterable_id')
                    ->where('meterable_type', self::class);
    }

    public function readings(): HasMany
    {
        return $this->hasMany(EnergyReading::class, 'meterable_id')
                    ->where('meterable_type', self::class);
    }

    public function forecasts(): HasMany
    {
        return $this->hasMany(EnergyForecast::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByInstallation($query, $installationId)
    {
        return $query->where('installation_id', $installationId);
    }

    public function scopeByMeterType($query, $meterType)
    {
        return $query->where('meter_type', $meterType);
    }

    public function scopeByLocationType($query, $locationType)
    {
        return $query->where('location_type', $locationType);
    }

    public function scopeByTariffType($query, $tariffType)
    {
        return $query->where('tariff_type', $tariffType);
    }

    public function scopeSmartMeters($query)
    {
        return $query->where('meter_type', self::METER_TYPE_SMART);
    }

    public function scopeResidential($query)
    {
        return $query->where('location_type', self::LOCATION_TYPE_RESIDENTIAL);
    }

    public function scopeCommercial($query)
    {
        return $query->where('location_type', self::LOCATION_TYPE_COMMERCIAL);
    }

    public function scopeIndustrial($query)
    {
        return $query->where('location_type', self::LOCATION_TYPE_INDUSTRIAL);
    }

    // Métodos
    public function isSmartMeter(): bool
    {
        return $this->meter_type === self::METER_TYPE_SMART;
    }

    public function isResidential(): bool
    {
        return $this->location_type === self::LOCATION_TYPE_RESIDENTIAL;
    }

    public function isCommercial(): bool
    {
        return $this->location_type === self::LOCATION_TYPE_COMMERCIAL;
    }

    public function isIndustrial(): bool
    {
        return $this->location_type === self::LOCATION_TYPE_INDUSTRIAL;
    }

    public function hasTimeOfUseTariff(): bool
    {
        return $this->tariff_type === self::TARIFF_TYPE_TIME_OF_USE;
    }

    public function hasDemandTariff(): bool
    {
        return $this->tariff_type === self::TARIFF_TYPE_DEMAND;
    }

    public function needsReading(): bool
    {
        if (!$this->next_reading_date) {
            return false;
        }
        
        return $this->next_reading_date->isPast();
    }

    public function getTotalConsumption(): float
    {
        return $this->readings()
            ->where('type', 'consumption')
            ->sum('delta_kwh');
    }

    public function getDailyConsumption(): float
    {
        $today = now()->startOfDay();
        return $this->readings()
            ->where('type', 'consumption')
            ->where('timestamp', '>=', $today)
            ->sum('delta_kwh');
    }

    public function getMonthlyConsumption(): float
    {
        $thisMonth = now()->startOfMonth();
        return $this->readings()
            ->where('type', 'consumption')
            ->where('timestamp', '>=', $thisMonth)
            ->sum('delta_kwh');
    }

    public function getYearlyConsumption(): float
    {
        $thisYear = now()->startOfYear();
        return $this->readings()
            ->where('type', 'consumption')
            ->where('timestamp', '>=', $thisYear)
            ->sum('delta_kwh');
    }

    public function getAverageDailyConsumption(): float
    {
        $totalConsumption = $this->getTotalConsumption();
        $daysSinceInstallation = $this->created_at->diffInDays(now());
        
        if ($daysSinceInstallation <= 0) {
            return 0;
        }
        
        return $totalConsumption / $daysSinceInstallation;
    }

    public function getPeakHourConsumption(): float
    {
        if (!$this->hasTimeOfUseTariff()) {
            return 0;
        }
        
        // Implementar lógica para calcular consumo en horas pico
        return 0; // Placeholder
    }

    public function getOffPeakHourConsumption(): float
    {
        if (!$this->hasTimeOfUseTariff()) {
            return 0;
        }
        
        // Implementar lógica para calcular consumo en horas valle
        return 0; // Placeholder
    }

    public function getDemandCharge(): float
    {
        if (!$this->hasDemandTariff()) {
            return 0;
        }
        
        return $this->demand_charge;
    }

    public function getTotalCost(): float
    {
        $totalConsumption = $this->getTotalConsumption();
        $energyCost = $totalConsumption * $this->energy_charge;
        $demandCost = $this->getDemandCharge();
        $taxAmount = ($energyCost + $demandCost) * $this->tax_rate;
        $subsidyAmount = ($energyCost + $demandCost) * $this->subsidy_rate;
        
        return $energyCost + $demandCost + $taxAmount - $subsidyAmount;
    }

    public function getFormattedTotalConsumption(): string
    {
        return number_format($this->getTotalConsumption(), 2) . ' kWh';
    }

    public function getFormattedDailyConsumption(): string
    {
        return number_format($this->getDailyConsumption(), 2) . ' kWh';
    }

    public function getFormattedMonthlyConsumption(): string
    {
        return number_format($this->getMonthlyConsumption(), 2) . ' kWh';
    }

    public function getFormattedYearlyConsumption(): string
    {
        return number_format($this->getYearlyConsumption(), 2) . ' kWh';
    }

    public function getFormattedAverageDailyConsumption(): string
    {
        return number_format($this->getAverageDailyConsumption(), 2) . ' kWh';
    }

    public function getFormattedTotalCost(): string
    {
        return '€' . number_format($this->getTotalCost(), 2);
    }

    public function getFormattedMeterType(): string
    {
        return self::getMeterTypes()[$this->meter_type] ?? 'Desconocido';
    }

    public function getFormattedLocationType(): string
    {
        return self::getLocationTypes()[$this->location_type] ?? 'Desconocido';
    }

    public function getFormattedTariffType(): string
    {
        return self::getTariffTypes()[$this->tariff_type] ?? 'Desconocido';
    }

    public function getFormattedBillingCycle(): string
    {
        return self::getBillingCycles()[$this->billing_cycle] ?? 'Desconocido';
    }

    public function getStatusBadgeClass(): string
    {
        if (!$this->active) {
            return 'bg-red-100 text-red-800';
        }
        
        if ($this->needsReading()) {
            return 'bg-yellow-100 text-yellow-800';
        }
        
        return 'bg-green-100 text-green-800';
    }

    public function getMeterTypeBadgeClass(): string
    {
        return match($this->meter_type) {
            self::METER_TYPE_SMART => 'bg-blue-100 text-blue-800',
            self::METER_TYPE_ANALOG => 'bg-gray-100 text-gray-800',
            self::METER_TYPE_DIGITAL => 'bg-green-100 text-green-800',
            self::METER_TYPE_PREPAID => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
