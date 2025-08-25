<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class ConsumptionPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'point_number',
        'name',
        'description',
        'point_type',
        'status',
        'customer_id',
        'installation_id',
        'location_address',
        'latitude',
        'longitude',
        'peak_demand_kw',
        'average_demand_kw',
        'annual_consumption_kwh',
        'monthly_consumption_kwh',
        'daily_consumption_kwh',
        'hourly_consumption_kwh',
        'connection_date',
        'disconnection_date',
        'meter_number',
        'meter_type',
        'meter_manufacturer',
        'meter_model',
        'meter_installation_date',
        'meter_last_calibration_date',
        'meter_next_calibration_date',
        'voltage_level',
        'voltage_unit',
        'current_rating',
        'current_unit',
        'phase_type',
        'connection_type',
        'technical_specifications',
        'safety_features',
        'load_profile',
        'consumption_patterns',
        'peak_hours',
        'off_peak_hours',
        'tags',
        'managed_by',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'peak_demand_kw' => 'decimal:2',
        'average_demand_kw' => 'decimal:2',
        'annual_consumption_kwh' => 'decimal:2',
        'monthly_consumption_kwh' => 'decimal:2',
        'daily_consumption_kwh' => 'decimal:2',
        'hourly_consumption_kwh' => 'decimal:2',
        'connection_date' => 'date',
        'disconnection_date' => 'date',
        'meter_installation_date' => 'date',
        'meter_last_calibration_date' => 'date',
        'meter_next_calibration_date' => 'date',
        'voltage_level' => 'decimal:2',
        'current_rating' => 'decimal:2',
        'approved_at' => 'datetime',
        'load_profile' => 'array',
        'consumption_patterns' => 'array',
        'peak_hours' => 'array',
        'off_peak_hours' => 'array',
        'tags' => 'array',
    ];

    // Enums
    const POINT_TYPE_RESIDENTIAL = 'residential';
    const POINT_TYPE_COMMERCIAL = 'commercial';
    const POINT_TYPE_INDUSTRIAL = 'industrial';
    const POINT_TYPE_AGRICULTURAL = 'agricultural';
    const POINT_TYPE_PUBLIC = 'public';
    const POINT_TYPE_STREET_LIGHTING = 'street_lighting';
    const POINT_TYPE_CHARGING_STATION = 'charging_station';
    const POINT_TYPE_OTHER = 'other';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_DISCONNECTED = 'disconnected';
    const STATUS_PLANNED = 'planned';
    const STATUS_DECOMMISSIONED = 'decommissioned';

    public static function getPointTypes(): array
    {
        return [
            self::POINT_TYPE_RESIDENTIAL => 'Residencial',
            self::POINT_TYPE_COMMERCIAL => 'Comercial',
            self::POINT_TYPE_INDUSTRIAL => 'Industrial',
            self::POINT_TYPE_AGRICULTURAL => 'Agrícola',
            self::POINT_TYPE_PUBLIC => 'Público',
            self::POINT_TYPE_STREET_LIGHTING => 'Alumbrado Público',
            self::POINT_TYPE_CHARGING_STATION => 'Estación de Carga',
            self::POINT_TYPE_OTHER => 'Otro',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_INACTIVE => 'Inactivo',
            self::STATUS_MAINTENANCE => 'Mantenimiento',
            self::STATUS_DISCONNECTED => 'Desconectado',
            self::STATUS_PLANNED => 'Planificado',
            self::STATUS_DECOMMISSIONED => 'Desmantelado',
        ];
    }

    // Relaciones
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function installation(): BelongsTo
    {
        return $this->belongsTo(EnergyInstallation::class);
    }

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
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPointType($query, $pointType)
    {
        return $query->where('point_type', $pointType);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByInstallation($query, $installationId)
    {
        return $query->where('installation_id', $installationId);
    }

    public function scopeByMeterType($query, $meterType)
    {
        return $query->where('meter_type', $meterType);
    }

    public function scopeByMeterNumber($query, $meterNumber)
    {
        return $query->where('meter_number', $meterNumber);
    }

    public function scopeResidential($query)
    {
        return $query->where('point_type', self::POINT_TYPE_RESIDENTIAL);
    }

    public function scopeCommercial($query)
    {
        return $query->where('point_type', self::POINT_TYPE_COMMERCIAL);
    }

    public function scopeIndustrial($query)
    {
        return $query->where('point_type', self::POINT_TYPE_INDUSTRIAL);
    }

    public function scopeAgricultural($query)
    {
        return $query->where('point_type', self::POINT_TYPE_AGRICULTURAL);
    }

    public function scopePublic($query)
    {
        return $query->where('point_type', self::POINT_TYPE_PUBLIC);
    }

    public function scopeStreetLighting($query)
    {
        return $query->where('point_type', self::POINT_TYPE_STREET_LIGHTING);
    }

    public function scopeChargingStation($query)
    {
        return $query->where('point_type', self::POINT_TYPE_CHARGING_STATION);
    }

    public function scopeMaintenance($query)
    {
        return $query->where('status', self::STATUS_MAINTENANCE);
    }

    public function scopeDisconnected($query)
    {
        return $query->where('status', self::STATUS_DISCONNECTED);
    }

    public function scopePlanned($query)
    {
        return $query->where('status', self::STATUS_PLANNED);
    }

    public function scopeDecommissioned($query)
    {
        return $query->where('status', self::STATUS_DECOMMISSIONED);
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

    public function isDisconnected(): bool
    {
        return $this->status === self::STATUS_DISCONNECTED;
    }

    public function isPlanned(): bool
    {
        return $this->status === self::STATUS_PLANNED;
    }

    public function isDecommissioned(): bool
    {
        return $this->status === self::STATUS_DECOMMISSIONED;
    }

    public function isResidential(): bool
    {
        return $this->point_type === self::POINT_TYPE_RESIDENTIAL;
    }

    public function isCommercial(): bool
    {
        return $this->point_type === self::POINT_TYPE_COMMERCIAL;
    }

    public function isIndustrial(): bool
    {
        return $this->point_type === self::POINT_TYPE_INDUSTRIAL;
    }

    public function isAgricultural(): bool
    {
        return $this->point_type === self::POINT_TYPE_AGRICULTURAL;
    }

    public function isPublic(): bool
    {
        return $this->point_type === self::POINT_TYPE_PUBLIC;
    }

    public function isStreetLighting(): bool
    {
        return $this->point_type === self::POINT_TYPE_STREET_LIGHTING;
    }

    public function isChargingStation(): bool
    {
        return $this->point_type === self::POINT_TYPE_CHARGING_STATION;
    }

    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    public function needsMeterCalibration(): bool
    {
        if (!$this->meter_next_calibration_date) {
            return false;
        }
        
        return $this->meter_next_calibration_date->isPast();
    }

    public function isConnected(): bool
    {
        return $this->status === self::STATUS_ACTIVE && !$this->disconnection_date;
    }

    public function hasDisconnectionDate(): bool
    {
        return $this->status === self::STATUS_DISCONNECTED || $this->disconnection_date;
    }

    // Métodos de cálculo
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
        $daysSinceConnection = $this->connection_date ? $this->connection_date->diffInDays(now()) : 0;
        
        if ($daysSinceConnection <= 0) {
            return 0;
        }
        
        return $totalConsumption / $daysSinceConnection;
    }

    public function getPeakHourConsumption(): float
    {
        if (!$this->peak_hours || !$this->hourly_consumption_kwh) {
            return 0;
        }
        
        // Implementar lógica para calcular consumo en horas pico
        return 0; // Placeholder
    }

    public function getOffPeakHourConsumption(): float
    {
        if (!$this->off_peak_hours || !$this->hourly_consumption_kwh) {
            return 0;
        }
        
        // Implementar lógica para calcular consumo en horas valle
        return 0; // Placeholder
    }

    public function getDemandFactor(): float
    {
        if ($this->average_demand_kw <= 0) {
            return 0;
        }
        
        return $this->peak_demand_kw / $this->average_demand_kw;
    }

    public function getUtilizationFactor(): float
    {
        if ($this->peak_demand_kw <= 0) {
            return 0;
        }
        
        return $this->average_demand_kw / $this->peak_demand_kw;
    }

    public function getAgeInYears(): int
    {
        if (!$this->connection_date) {
            return 0;
        }
        
        return $this->connection_date->diffInYears(now());
    }

    public function getMeterAgeInYears(): int
    {
        if (!$this->meter_installation_date) {
            return 0;
        }
        
        return $this->meter_installation_date->diffInYears(now());
    }

    public function getDaysUntilCalibration(): int
    {
        if (!$this->meter_next_calibration_date) {
            return 0;
        }
        
        return now()->diffInDays($this->meter_next_calibration_date, false);
    }

    // Métodos de formato
    public function getFormattedPointType(): string
    {
        return self::getPointTypes()[$this->point_type] ?? 'Desconocido';
    }

    public function getFormattedStatus(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getFormattedPeakDemand(): string
    {
        return $this->peak_demand_kw ? number_format($this->peak_demand_kw, 2) . ' kW' : 'N/A';
    }

    public function getFormattedAverageDemand(): string
    {
        return $this->average_demand_kw ? number_format($this->average_demand_kw, 2) . ' kW' : 'N/A';
    }

    public function getFormattedAnnualConsumption(): string
    {
        return $this->annual_consumption_kwh ? number_format($this->annual_consumption_kwh, 2) . ' kWh' : 'N/A';
    }

    public function getFormattedMonthlyConsumption(): string
    {
        return $this->monthly_consumption_kwh ? number_format($this->monthly_consumption_kwh, 2) . ' kWh' : 'N/A';
    }

    public function getFormattedDailyConsumption(): string
    {
        return $this->daily_consumption_kwh ? number_format($this->daily_consumption_kwh, 2) . ' kWh' : 'N/A';
    }

    public function getFormattedHourlyConsumption(): string
    {
        return $this->hourly_consumption_kwh ? number_format($this->hourly_consumption_kwh, 2) . ' kWh' : 'N/A';
    }

    public function getFormattedVoltageLevel(): string
    {
        return $this->voltage_level ? number_format($this->voltage_level, 2) . ' ' . ($this->voltage_unit ?? 'V') : 'N/A';
    }

    public function getFormattedCurrentRating(): string
    {
        return $this->current_rating ? number_format($this->current_rating, 2) . ' ' . ($this->current_unit ?? 'A') : 'N/A';
    }

    public function getFormattedConnectionDate(): string
    {
        return $this->connection_date ? $this->connection_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedDisconnectionDate(): string
    {
        return $this->disconnection_date ? $this->disconnection_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedMeterInstallationDate(): string
    {
        return $this->meter_installation_date ? $this->meter_installation_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedMeterLastCalibrationDate(): string
    {
        return $this->meter_last_calibration_date ? $this->meter_last_calibration_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedMeterNextCalibrationDate(): string
    {
        return $this->meter_next_calibration_date ? $this->meter_next_calibration_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedTotalConsumption(): string
    {
        return number_format($this->getTotalConsumption(), 2) . ' kWh';
    }

    public function getFormattedYearlyConsumption(): string
    {
        return number_format($this->getYearlyConsumption(), 2) . ' kWh';
    }

    public function getFormattedAverageDailyConsumption(): string
    {
        return number_format($this->getAverageDailyConsumption(), 2) . ' kWh';
    }

    public function getFormattedDemandFactor(): string
    {
        return number_format($this->getDemandFactor(), 2);
    }

    public function getFormattedUtilizationFactor(): string
    {
        return number_format($this->getUtilizationFactor(), 2);
    }

    // Clases de badges para Filament
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'bg-green-100 text-green-800',
            self::STATUS_INACTIVE => 'bg-gray-100 text-gray-800',
            self::STATUS_MAINTENANCE => 'bg-yellow-100 text-yellow-800',
            self::STATUS_DISCONNECTED => 'bg-red-100 text-red-800',
            self::STATUS_PLANNED => 'bg-blue-100 text-blue-800',
            self::STATUS_DECOMMISSIONED => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getPointTypeBadgeClass(): string
    {
        return match($this->point_type) {
            self::POINT_TYPE_RESIDENTIAL => 'bg-blue-100 text-blue-800',
            self::POINT_TYPE_COMMERCIAL => 'bg-green-100 text-green-800',
            self::POINT_TYPE_INDUSTRIAL => 'bg-purple-100 text-purple-800',
            self::POINT_TYPE_AGRICULTURAL => 'bg-yellow-100 text-yellow-800',
            self::POINT_TYPE_PUBLIC => 'bg-indigo-100 text-indigo-800',
            self::POINT_TYPE_STREET_LIGHTING => 'bg-pink-100 text-pink-800',
            self::POINT_TYPE_CHARGING_STATION => 'bg-cyan-100 text-cyan-800',
            self::POINT_TYPE_OTHER => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
