<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class EnergyMeter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'meter_number',
        'name',
        'description',
        'meter_type',
        'status',
        'meter_category',
        'manufacturer',
        'model',
        'serial_number',
        'firmware_version',
        'hardware_version',
        'installation_id',
        'consumption_point_id',
        'customer_id',
        'location_address',
        'latitude',
        'longitude',
        'installation_date',
        'commissioning_date',
        'last_calibration_date',
        'next_calibration_date',
        'warranty_expiry_date',
        'voltage_rating',
        'voltage_unit',
        'current_rating',
        'current_unit',
        'phase_type',
        'connection_type',
        'accuracy_class',
        'measurement_range_min',
        'measurement_range_max',
        'measurement_unit',
        'pulse_constant',
        'pulse_unit',
        'is_smart_meter',
        'has_remote_reading',
        'has_two_way_communication',
        'communication_protocol',
        'communication_frequency',
        'data_logging_interval',
        'data_retention_days',
        'technical_specifications',
        'calibration_requirements',
        'maintenance_requirements',
        'safety_features',
        'meter_features',
        'communication_settings',
        'alarm_settings',
        'data_formats',
        'tags',
        'installed_by',
        'managed_by',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'installation_date' => 'date',
        'commissioning_date' => 'date',
        'last_calibration_date' => 'date',
        'next_calibration_date' => 'date',
        'warranty_expiry_date' => 'date',
        'voltage_rating' => 'decimal:2',
        'current_rating' => 'decimal:2',
        'accuracy_class' => 'decimal:2',
        'measurement_range_min' => 'decimal:2',
        'measurement_range_max' => 'decimal:2',
        'pulse_constant' => 'decimal:2',
        'is_smart_meter' => 'boolean',
        'has_remote_reading' => 'boolean',
        'has_two_way_communication' => 'boolean',
        'data_retention_days' => 'integer',
        'approved_at' => 'datetime',
        'meter_features' => 'array',
        'communication_settings' => 'array',
        'alarm_settings' => 'array',
        'data_formats' => 'array',
        'tags' => 'array',
    ];

    // Enums
    const METER_TYPE_SMART_METER = 'smart_meter';
    const METER_TYPE_DIGITAL_METER = 'digital_meter';
    const METER_TYPE_ANALOG_METER = 'analog_meter';
    const METER_TYPE_PREPAID_METER = 'prepaid_meter';
    const METER_TYPE_POSTPAID_METER = 'postpaid_meter';
    const METER_TYPE_BI_DIRECTIONAL = 'bi_directional';
    const METER_TYPE_NET_METER = 'net_meter';
    const METER_TYPE_SUB_METER = 'sub_meter';
    const METER_TYPE_OTHER = 'other';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_FAULTY = 'faulty';
    const STATUS_REPLACED = 'replaced';
    const STATUS_DECOMMISSIONED = 'decommissioned';
    const STATUS_CALIBRATING = 'calibrating';

    const METER_CATEGORY_ELECTRICITY = 'electricity';
    const METER_CATEGORY_WATER = 'water';
    const METER_CATEGORY_GAS = 'gas';
    const METER_CATEGORY_HEAT = 'heat';
    const METER_CATEGORY_STEAM = 'steam';
    const METER_CATEGORY_COMPRESSED_AIR = 'compressed_air';
    const METER_CATEGORY_OTHER = 'other';

    public static function getMeterTypes(): array
    {
        return [
            self::METER_TYPE_SMART_METER => 'Medidor Inteligente',
            self::METER_TYPE_DIGITAL_METER => 'Medidor Digital',
            self::METER_TYPE_ANALOG_METER => 'Medidor Analógico',
            self::METER_TYPE_PREPAID_METER => 'Medidor Prepago',
            self::METER_TYPE_POSTPAID_METER => 'Medidor Postpago',
            self::METER_TYPE_BI_DIRECTIONAL => 'Medidor Bidireccional',
            self::METER_TYPE_NET_METER => 'Medidor Neto',
            self::METER_TYPE_SUB_METER => 'Submedidor',
            self::METER_TYPE_OTHER => 'Otro',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_INACTIVE => 'Inactivo',
            self::STATUS_MAINTENANCE => 'Mantenimiento',
            self::STATUS_FAULTY => 'Defectuoso',
            self::STATUS_REPLACED => 'Reemplazado',
            self::STATUS_DECOMMISSIONED => 'Desmantelado',
            self::STATUS_CALIBRATING => 'Calibrando',
        ];
    }

    public static function getMeterCategories(): array
    {
        return [
            self::METER_CATEGORY_ELECTRICITY => 'Electricidad',
            self::METER_CATEGORY_WATER => 'Agua',
            self::METER_CATEGORY_GAS => 'Gas',
            self::METER_CATEGORY_HEAT => 'Calor',
            self::METER_CATEGORY_STEAM => 'Vapor',
            self::METER_CATEGORY_COMPRESSED_AIR => 'Aire Comprimido',
            self::METER_CATEGORY_OTHER => 'Otro',
        ];
    }

    // Relaciones
    public function installation(): BelongsTo
    {
        return $this->belongsTo(EnergyInstallation::class);
    }

    public function consumptionPoint(): BelongsTo
    {
        return $this->belongsTo(ConsumptionPoint::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function installedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'installed_by');
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

    public function meterable(): MorphTo
    {
        return $this->morphTo();
    }

    public function readings(): HasMany
    {
        return $this->hasMany(EnergyReading::class, 'meter_id');
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

    public function scopeByMeterType($query, $meterType)
    {
        return $query->where('meter_type', $meterType);
    }

    public function scopeByMeterCategory($query, $meterCategory)
    {
        return $query->where('meter_category', $meterCategory);
    }

    public function scopeByManufacturer($query, $manufacturer)
    {
        return $query->where('manufacturer', $manufacturer);
    }

    public function scopeByModel($query, $model)
    {
        return $query->where('model', $model);
    }

    public function scopeBySerialNumber($query, $serialNumber)
    {
        return $query->where('serial_number', $serialNumber);
    }

    public function scopeByMeterNumber($query, $meterNumber)
    {
        return $query->where('meter_number', $meterNumber);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByInstallation($query, $installationId)
    {
        return $query->where('installation_id', $installationId);
    }

    public function scopeByConsumptionPoint($query, $consumptionPointId)
    {
        return $query->where('consumption_point_id', $consumptionPointId);
    }

    public function scopeSmartMeters($query)
    {
        return $query->where('is_smart_meter', true);
    }

    public function scopeRemoteReading($query)
    {
        return $query->where('has_remote_reading', true);
    }

    public function scopeTwoWayCommunication($query)
    {
        return $query->where('has_two_way_communication', true);
    }

    public function scopeNeedsCalibration($query)
    {
        return $query->where('next_calibration_date', '<=', now());
    }

    public function scopeMaintenance($query)
    {
        return $query->where('status', self::STATUS_MAINTENANCE);
    }

    public function scopeFaulty($query)
    {
        return $query->where('status', self::STATUS_FAULTY);
    }

    public function scopeCalibrating($query)
    {
        return $query->where('status', self::STATUS_CALIBRATING);
    }

    public function scopeElectricity($query)
    {
        return $query->where('meter_category', self::METER_CATEGORY_ELECTRICITY);
    }

    public function scopeWater($query)
    {
        return $query->where('meter_category', self::METER_CATEGORY_WATER);
    }

    public function scopeGas($query)
    {
        return $query->where('meter_category', self::METER_CATEGORY_GAS);
    }

    public function scopeHeat($query)
    {
        return $query->where('meter_category', self::METER_CATEGORY_HEAT);
    }

    public function scopeSteam($query)
    {
        return $query->where('meter_category', self::METER_CATEGORY_STEAM);
    }

    public function scopeCompressedAir($query)
    {
        return $query->where('meter_category', self::METER_CATEGORY_COMPRESSED_AIR);
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

    public function isFaulty(): bool
    {
        return $this->status === self::STATUS_FAULTY;
    }

    public function isReplaced(): bool
    {
        return $this->status === self::STATUS_REPLACED;
    }

    public function isDecommissioned(): bool
    {
        return $this->status === self::STATUS_DECOMMISSIONED;
    }

    public function isCalibrating(): bool
    {
        return $this->status === self::STATUS_CALIBRATING;
    }

    public function isSmartMeter(): bool
    {
        return $this->is_smart_meter;
    }

    public function hasRemoteReading(): bool
    {
        return $this->has_remote_reading;
    }

    public function hasTwoWayCommunication(): bool
    {
        return $this->has_two_way_communication;
    }

    public function isElectricity(): bool
    {
        return $this->meter_category === self::METER_CATEGORY_ELECTRICITY;
    }

    public function isWater(): bool
    {
        return $this->meter_category === self::METER_CATEGORY_WATER;
    }

    public function isGas(): bool
    {
        return $this->meter_category === self::METER_CATEGORY_GAS;
    }

    public function isHeat(): bool
    {
        return $this->meter_category === self::METER_CATEGORY_HEAT;
    }

    public function isSteam(): bool
    {
        return $this->meter_category === self::METER_CATEGORY_STEAM;
    }

    public function isCompressedAir(): bool
    {
        return $this->meter_category === self::METER_CATEGORY_COMPRESSED_AIR;
    }

    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    public function needsCalibration(): bool
    {
        if (!$this->next_calibration_date) {
            return false;
        }
        
        return $this->next_calibration_date->isPast();
    }

    public function isUnderWarranty(): bool
    {
        if (!$this->warranty_expiry_date) {
            return false;
        }
        
        return $this->warranty_expiry_date->isFuture();
    }

    public function isCommissioned(): bool
    {
        return !is_null($this->commissioning_date);
    }

    // Métodos de cálculo
    public function getTotalReadings(): int
    {
        return $this->readings()->count();
    }

    public function getTotalEnergy(): float
    {
        return $this->readings()->sum('delta_kwh');
    }

    public function getLastReading(): ?EnergyReading
    {
        return $this->readings()->latest('timestamp')->first();
    }

    public function getLastReadingValue(): float
    {
        $lastReading = $this->getLastReading();
        return $lastReading ? $lastReading->kwh : 0;
    }

    public function getLastReadingTimestamp(): ?string
    {
        $lastReading = $this->getLastReading();
        return $lastReading ? $lastReading->timestamp : null;
    }

    public function getDailyEnergy(): float
    {
        $today = now()->startOfDay();
        return $this->readings()
            ->where('timestamp', '>=', $today)
            ->sum('delta_kwh');
    }

    public function getMonthlyEnergy(): float
    {
        $thisMonth = now()->startOfMonth();
        return $this->readings()
            ->where('timestamp', '>=', $thisMonth)
            ->sum('delta_kwh');
    }

    public function getYearlyEnergy(): float
    {
        $thisYear = now()->startOfYear();
        return $this->readings()
            ->where('timestamp', '>=', $thisYear)
            ->sum('delta_kwh');
    }

    public function getAverageDailyEnergy(): float
    {
        $totalEnergy = $this->getTotalEnergy();
        $daysSinceInstallation = $this->installation_date ? $this->installation_date->diffInDays(now()) : 0;
        
        if ($daysSinceInstallation <= 0) {
            return 0;
        }
        
        return $totalEnergy / $daysSinceInstallation;
    }

    public function getPeakHourEnergy(): float
    {
        // Implementar lógica para calcular energía en horas pico
        return 0; // Placeholder
    }

    public function getOffPeakHourEnergy(): float
    {
        // Implementar lógica para calcular energía en horas valle
        return 0; // Placeholder
    }

    public function getEfficiencyRating(): float
    {
        // Calcular eficiencia basada en la precisión y características
        $accuracyScore = $this->accuracy_class ? (100 - ($this->accuracy_class * 10)) : 60;
        $smartMeterScore = $this->is_smart_meter ? 100 : 70;
        $remoteReadingScore = $this->has_remote_reading ? 100 : 70;
        $twoWayScore = $this->has_two_way_communication ? 100 : 70;

        return ($accuracyScore + $smartMeterScore + $remoteReadingScore + $twoWayScore) / 4;
    }

    public function getAgeInYears(): int
    {
        if (!$this->installation_date) {
            return 0;
        }
        
        return $this->installation_date->diffInYears(now());
    }

    public function getDaysUntilCalibration(): int
    {
        if (!$this->next_calibration_date) {
            return 0;
        }
        
        return now()->diffInDays($this->next_calibration_date, false);
    }

    public function getDaysUntilWarrantyExpiry(): int
    {
        if (!$this->warranty_expiry_date) {
            return 0;
        }
        
        return now()->diffInDays($this->warranty_expiry_date, false);
    }

    public function getMeasurementRange(): string
    {
        if ($this->measurement_range_min && $this->measurement_range_max) {
            $unit = $this->measurement_unit ?? '';
            return number_format($this->measurement_range_min, 2) . ' - ' . number_format($this->measurement_range_max, 2) . ' ' . $unit;
        }
        
        return 'N/A';
    }

    // Métodos de formato
    public function getFormattedMeterType(): string
    {
        return self::getMeterTypes()[$this->meter_type] ?? 'Desconocido';
    }

    public function getFormattedStatus(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getFormattedMeterCategory(): string
    {
        return self::getMeterCategories()[$this->meter_category] ?? 'Desconocido';
    }

    public function getFormattedVoltageRating(): string
    {
        return $this->voltage_rating ? number_format($this->voltage_rating, 2) . ' ' . ($this->voltage_unit ?? 'V') : 'N/A';
    }

    public function getFormattedCurrentRating(): string
    {
        return $this->current_rating ? number_format($this->current_rating, 2) . ' ' . ($this->current_unit ?? 'A') : 'N/A';
    }

    public function getFormattedAccuracyClass(): string
    {
        return $this->accuracy_class ? number_format($this->accuracy_class, 2) . '%' : 'N/A';
    }

    public function getFormattedPulseConstant(): string
    {
        return $this->pulse_constant ? number_format($this->pulse_constant, 2) . ' ' . ($this->pulse_unit ?? '') : 'N/A';
    }

    public function getFormattedInstallationDate(): string
    {
        return $this->installation_date ? $this->installation_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedCommissioningDate(): string
    {
        return $this->commissioning_date ? $this->commissioning_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedLastCalibrationDate(): string
    {
        return $this->last_calibration_date ? $this->last_calibration_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedNextCalibrationDate(): string
    {
        return $this->next_calibration_date ? $this->next_calibration_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedWarrantyExpiryDate(): string
    {
        return $this->warranty_expiry_date ? $this->warranty_expiry_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedTotalEnergy(): string
    {
        return number_format($this->getTotalEnergy(), 2) . ' kWh';
    }

    public function getFormattedDailyEnergy(): string
    {
        return number_format($this->getDailyEnergy(), 2) . ' kWh';
    }

    public function getFormattedMonthlyEnergy(): string
    {
        return number_format($this->getMonthlyEnergy(), 2) . ' kWh';
    }

    public function getFormattedYearlyEnergy(): string
    {
        return number_format($this->getYearlyEnergy(), 2) . ' kWh';
    }

    public function getFormattedAverageDailyEnergy(): string
    {
        return number_format($this->getAverageDailyEnergy(), 2) . ' kWh';
    }

    public function getFormattedEfficiencyRating(): string
    {
        return number_format($this->getEfficiencyRating(), 1) . '%';
    }

    public function getFormattedAgeInYears(): string
    {
        return $this->getAgeInYears() . ' años';
    }

    public function getFormattedDaysUntilCalibration(): string
    {
        $days = $this->getDaysUntilCalibration();
        if ($days > 0) {
            return $days . ' días';
        } elseif ($days < 0) {
            return 'Vencido hace ' . abs($days) . ' días';
        } else {
            return 'Hoy';
        }
    }

    public function getFormattedDaysUntilWarrantyExpiry(): string
    {
        $days = $this->getDaysUntilWarrantyExpiry();
        if ($days > 0) {
            return $days . ' días';
        } elseif ($days < 0) {
            return 'Vencida hace ' . abs($days) . ' días';
        } else {
            return 'Hoy';
        }
    }

    // Clases de badges para Filament
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'bg-green-100 text-green-800',
            self::STATUS_INACTIVE => 'bg-gray-100 text-gray-800',
            self::STATUS_MAINTENANCE => 'bg-yellow-100 text-yellow-800',
            self::STATUS_FAULTY => 'bg-red-100 text-red-800',
            self::STATUS_REPLACED => 'bg-blue-100 text-blue-800',
            self::STATUS_DECOMMISSIONED => 'bg-gray-100 text-gray-800',
            self::STATUS_CALIBRATING => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getMeterTypeBadgeClass(): string
    {
        return match($this->meter_type) {
            self::METER_TYPE_SMART_METER => 'bg-blue-100 text-blue-800',
            self::METER_TYPE_DIGITAL_METER => 'bg-green-100 text-green-800',
            self::METER_TYPE_ANALOG_METER => 'bg-gray-100 text-gray-800',
            self::METER_TYPE_PREPAID_METER => 'bg-orange-100 text-orange-800',
            self::METER_TYPE_POSTPAID_METER => 'bg-purple-100 text-purple-800',
            self::METER_TYPE_BI_DIRECTIONAL => 'bg-indigo-100 text-indigo-800',
            self::METER_TYPE_NET_METER => 'bg-pink-100 text-pink-800',
            self::METER_TYPE_SUB_METER => 'bg-cyan-100 text-cyan-800',
            self::METER_TYPE_OTHER => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getMeterCategoryBadgeClass(): string
    {
        return match($this->meter_category) {
            self::METER_CATEGORY_ELECTRICITY => 'bg-yellow-100 text-yellow-800',
            self::METER_CATEGORY_WATER => 'bg-blue-100 text-blue-800',
            self::METER_CATEGORY_GAS => 'bg-orange-100 text-orange-800',
            self::METER_CATEGORY_HEAT => 'bg-red-100 text-red-800',
            self::METER_CATEGORY_STEAM => 'bg-gray-100 text-gray-800',
            self::METER_CATEGORY_COMPRESSED_AIR => 'bg-cyan-100 text-cyan-800',
            self::METER_CATEGORY_OTHER => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getAccuracyBadgeClass(): string
    {
        if (!$this->accuracy_class) {
            return 'bg-gray-100 text-gray-800';
        }
        
        if ($this->accuracy_class <= 0.5) {
            return 'bg-green-100 text-green-800';
        } elseif ($this->accuracy_class <= 1.0) {
            return 'bg-blue-100 text-blue-800';
        } elseif ($this->accuracy_class <= 2.0) {
            return 'bg-yellow-100 text-yellow-800';
        } else {
            return 'bg-red-100 text-red-800';
        }
    }

    public function getSmartMeterBadgeClass(): string
    {
        return $this->is_smart_meter ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
    }

    public function getRemoteReadingBadgeClass(): string
    {
        return $this->has_remote_reading ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
    }

    public function getTwoWayCommunicationBadgeClass(): string
    {
        return $this->has_two_way_communication ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
    }
}
