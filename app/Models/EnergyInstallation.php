<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnergyInstallation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'municipality_id',
        'address',
        'coordinates',
        'description',
        'installed_power_kw',
        'active',
        'commissioning_date',
        'decommissioning_date',
        'cooperative_owned',
        'organization_id',
        'energy_source_id',
        'efficiency_rating',
        'maintenance_schedule',
        'last_maintenance_date',
        'next_maintenance_date',
        'warranty_expiry_date',
        'insurance_expiry_date',
        'certification_status',
        'grid_connection_type',
        'battery_storage_capacity',
        'monitoring_system',
        'weather_station_id',
    ];

    protected $casts = [
        'coordinates' => 'array',
        'installed_power_kw' => 'decimal:2',
        'active' => 'boolean',
        'cooperative_owned' => 'boolean',
        'commissioning_date' => 'datetime',
        'decommissioning_date' => 'datetime',
        'efficiency_rating' => 'decimal:2',
        'maintenance_schedule' => 'array',
        'last_maintenance_date' => 'datetime',
        'next_maintenance_date' => 'datetime',
        'warranty_expiry_date' => 'datetime',
        'insurance_expiry_date' => 'datetime',
        'battery_storage_capacity' => 'decimal:2',
        'monitoring_system' => 'array',
    ];

    // Enums
    const TYPE_PRODUCTION = 'production';
    const TYPE_CONSUMPTION = 'consumption';
    const TYPE_MIXED = 'mixed';
    const TYPE_STORAGE = 'storage';
    const TYPE_DISTRIBUTION = 'distribution';

    const GRID_CONNECTION_GRID_TIED = 'grid_tied';
    const GRID_CONNECTION_OFF_GRID = 'off_grid';
    const GRID_CONNECTION_HYBRID = 'hybrid';
    const GRID_CONNECTION_MICROGRID = 'microgrid';

    const CERTIFICATION_STATUS_PENDING = 'pending';
    const CERTIFICATION_STATUS_CERTIFIED = 'certified';
    const CERTIFICATION_STATUS_EXPIRED = 'expired';
    const CERTIFICATION_STATUS_REVOKED = 'revoked';

    public static function getTypes(): array
    {
        return [
            self::TYPE_PRODUCTION => 'Producción',
            self::TYPE_CONSUMPTION => 'Consumo',
            self::TYPE_MIXED => 'Mixta',
            self::TYPE_STORAGE => 'Almacenamiento',
            self::TYPE_DISTRIBUTION => 'Distribución',
        ];
    }

    public static function getGridConnectionTypes(): array
    {
        return [
            self::GRID_CONNECTION_GRID_TIED => 'Conectada a la Red',
            self::GRID_CONNECTION_OFF_GRID => 'Fuera de la Red',
            self::GRID_CONNECTION_HYBRID => 'Híbrida',
            self::GRID_CONNECTION_MICROGRID => 'Microred',
        ];
    }

    public static function getCertificationStatuses(): array
    {
        return [
            self::CERTIFICATION_STATUS_PENDING => 'Pendiente',
            self::CERTIFICATION_STATUS_CERTIFIED => 'Certificada',
            self::CERTIFICATION_STATUS_EXPIRED => 'Expirada',
            self::CERTIFICATION_STATUS_REVOKED => 'Revocada',
        ];
    }

    // Relaciones
    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function energySource(): BelongsTo
    {
        return $this->belongsTo(EnergySource::class);
    }

    public function productionProjects(): HasMany
    {
        return $this->hasMany(ProductionProject::class);
    }

    public function consumptionPoints(): HasMany
    {
        return $this->hasMany(ConsumptionPoint::class);
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

    public function maintenanceTasks(): HasMany
    {
        return $this->hasMany(MaintenanceTask::class);
    }

    public function forecasts(): HasMany
    {
        return $this->hasMany(EnergyForecast::class);
    }

    public function weatherStation(): BelongsTo
    {
        return $this->belongsTo(WeatherSnapshot::class, 'weather_station_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeByMunicipality($query, $municipalityId)
    {
        return $query->where('municipality_id', $municipalityId);
    }

    public function scopeByEnergySource($query, $energySourceId)
    {
        return $query->where('energy_source_id', $energySourceId);
    }

    public function scopeProduction($query)
    {
        return $query->whereIn('type', [self::TYPE_PRODUCTION, self::TYPE_MIXED]);
    }

    public function scopeConsumption($query)
    {
        return $query->whereIn('type', [self::TYPE_CONSUMPTION, self::TYPE_MIXED]);
    }

    public function scopeStorage($query)
    {
        return $query->where('type', self::TYPE_STORAGE);
    }

    public function scopeCooperativeOwned($query)
    {
        return $query->where('cooperative_owned', true);
    }

    // Métodos
    public function isProduction(): bool
    {
        return in_array($this->type, [self::TYPE_PRODUCTION, self::TYPE_MIXED]);
    }

    public function isConsumption(): bool
    {
        return in_array($this->type, [self::TYPE_CONSUMPTION, self::TYPE_MIXED]);
    }

    public function isStorage(): bool
    {
        return $this->type === self::TYPE_STORAGE;
    }

    public function isGridTied(): bool
    {
        return $this->grid_connection_type === self::GRID_CONNECTION_GRID_TIED;
    }

    public function isOffGrid(): bool
    {
        return $this->grid_connection_type === self::GRID_CONNECTION_OFF_GRID;
    }

    public function isCertified(): bool
    {
        return $this->certification_status === self::CERTIFICATION_STATUS_CERTIFIED;
    }

    public function needsMaintenance(): bool
    {
        if (!$this->next_maintenance_date) {
            return false;
        }
        
        return $this->next_maintenance_date->isPast();
    }

    public function isUnderWarranty(): bool
    {
        if (!$this->warranty_expiry_date) {
            return false;
        }
        
        return $this->warranty_expiry_date->isFuture();
    }

    public function isInsured(): bool
    {
        if (!$this->insurance_expiry_date) {
            return false;
        }
        
        return $this->insurance_expiry_date->isFuture();
    }

    public function getAgeInYears(): int
    {
        if (!$this->commissioning_date) {
            return 0;
        }
        
        return $this->commissioning_date->diffInYears(now());
    }

    public function getTotalProduction(): float
    {
        return $this->readings()
            ->where('type', 'production')
            ->sum('delta_kwh');
    }

    public function getTotalConsumption(): float
    {
        return $this->readings()
            ->where('type', 'consumption')
            ->sum('delta_kwh');
    }

    public function getDailyProduction(): float
    {
        $today = now()->startOfDay();
        return $this->readings()
            ->where('type', 'production')
            ->where('timestamp', '>=', $today)
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

    public function getMonthlyProduction(): float
    {
        $thisMonth = now()->startOfMonth();
        return $this->readings()
            ->where('type', 'production')
            ->where('timestamp', '>=', $thisMonth)
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

    public function getNetProduction(): float
    {
        return $this->getTotalProduction() - $this->getTotalConsumption();
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

    public function getFormattedPower(): string
    {
        return number_format($this->installed_power_kw, 2) . ' kW';
    }

    public function getFormattedEfficiency(): string
    {
        return number_format($this->efficiency_rating, 1) . '%';
    }

    public function getFormattedStorageCapacity(): string
    {
        if (!$this->battery_storage_capacity) {
            return 'No aplica';
        }
        
        return number_format($this->battery_storage_capacity, 2) . ' kWh';
    }

    public function getFormattedTotalProduction(): string
    {
        return number_format($this->getTotalProduction(), 2) . ' kWh';
    }

    public function getFormattedTotalConsumption(): string
    {
        return number_format($this->getTotalConsumption(), 2) . ' kWh';
    }

    public function getFormattedNetProduction(): string
    {
        return number_format($this->getNetProduction(), 2) . ' kWh';
    }

    public function getStatusBadgeClass(): string
    {
        if (!$this->active) {
            return 'bg-red-100 text-red-800';
        }
        
        if ($this->needsMaintenance()) {
            return 'bg-yellow-100 text-yellow-800';
        }
        
        if ($this->isUnderWarranty()) {
            return 'bg-green-100 text-green-800';
        }
        
        return 'bg-blue-100 text-blue-800';
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
}
