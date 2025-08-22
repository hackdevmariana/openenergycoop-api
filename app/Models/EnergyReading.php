<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnergyReading extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reading_number',
        'meter_id',
        'installation_id',
        'consumption_point_id',
        'customer_id',
        'reading_type',
        'reading_source',
        'reading_status',
        'reading_timestamp',
        'reading_period',
        'reading_value',
        'reading_unit',
        'previous_reading_value',
        'consumption_value',
        'consumption_unit',
        'demand_value',
        'demand_unit',
        'power_factor',
        'voltage_value',
        'voltage_unit',
        'current_value',
        'current_unit',
        'frequency_value',
        'frequency_unit',
        'temperature',
        'temperature_unit',
        'humidity',
        'humidity_unit',
        'quality_score',
        'quality_notes',
        'validation_notes',
        'correction_notes',
        'raw_data',
        'processed_data',
        'alarms',
        'events',
        'tags',
        'read_by',
        'validated_by',
        'validated_at',
        'corrected_by',
        'corrected_at',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'reading_timestamp' => 'datetime',
        'reading_value' => 'decimal:4',
        'previous_reading_value' => 'decimal:4',
        'consumption_value' => 'decimal:4',
        'demand_value' => 'decimal:4',
        'power_factor' => 'decimal:3',
        'voltage_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'frequency_value' => 'decimal:2',
        'temperature' => 'decimal:2',
        'humidity' => 'decimal:2',
        'quality_score' => 'decimal:2',
        'validated_at' => 'datetime',
        'corrected_at' => 'datetime',
        'raw_data' => 'array',
        'processed_data' => 'array',
        'alarms' => 'array',
        'events' => 'array',
        'tags' => 'array',
    ];

    // Enums
    const READING_TYPE_INSTANTANEOUS = 'instantaneous';
    const READING_TYPE_INTERVAL = 'interval';
    const READING_TYPE_CUMULATIVE = 'cumulative';
    const READING_TYPE_DEMAND = 'demand';
    const READING_TYPE_ENERGY = 'energy';
    const READING_TYPE_POWER_FACTOR = 'power_factor';
    const READING_TYPE_VOLTAGE = 'voltage';
    const READING_TYPE_CURRENT = 'current';
    const READING_TYPE_FREQUENCY = 'frequency';
    const READING_TYPE_OTHER = 'other';

    const READING_SOURCE_MANUAL = 'manual';
    const READING_SOURCE_AUTOMATIC = 'automatic';
    const READING_SOURCE_REMOTE = 'remote';
    const READING_SOURCE_ESTIMATED = 'estimated';
    const READING_SOURCE_CALCULATED = 'calculated';
    const READING_SOURCE_IMPORTED = 'imported';

    const READING_STATUS_VALID = 'valid';
    const READING_STATUS_INVALID = 'invalid';
    const READING_STATUS_SUSPICIOUS = 'suspicious';
    const READING_STATUS_ESTIMATED = 'estimated';
    const READING_STATUS_CORRECTED = 'corrected';
    const READING_STATUS_MISSING = 'missing';

    public static function getReadingTypes(): array
    {
        return [
            self::READING_TYPE_INSTANTANEOUS => 'Instantáneo',
            self::READING_TYPE_INTERVAL => 'Intervalo',
            self::READING_TYPE_CUMULATIVE => 'Acumulativo',
            self::READING_TYPE_DEMAND => 'Demanda',
            self::READING_TYPE_ENERGY => 'Energía',
            self::READING_TYPE_POWER_FACTOR => 'Factor de Potencia',
            self::READING_TYPE_VOLTAGE => 'Voltaje',
            self::READING_TYPE_CURRENT => 'Corriente',
            self::READING_TYPE_FREQUENCY => 'Frecuencia',
            self::READING_TYPE_OTHER => 'Otro',
        ];
    }

    public static function getReadingSources(): array
    {
        return [
            self::READING_SOURCE_MANUAL => 'Manual',
            self::READING_SOURCE_AUTOMATIC => 'Automático',
            self::READING_SOURCE_REMOTE => 'Remoto',
            self::READING_SOURCE_ESTIMATED => 'Estimado',
            self::READING_SOURCE_CALCULATED => 'Calculado',
            self::READING_SOURCE_IMPORTED => 'Importado',
        ];
    }

    public static function getReadingStatuses(): array
    {
        return [
            self::READING_STATUS_VALID => 'Válido',
            self::READING_STATUS_INVALID => 'Inválido',
            self::READING_STATUS_SUSPICIOUS => 'Sospechoso',
            self::READING_STATUS_ESTIMATED => 'Estimado',
            self::READING_STATUS_CORRECTED => 'Corregido',
            self::READING_STATUS_MISSING => 'Faltante',
        ];
    }

    // Relaciones
    public function meter(): BelongsTo
    {
        return $this->belongsTo(EnergyMeter::class);
    }

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

    public function readBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'read_by');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function correctedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeByReadingType($query, $readingType)
    {
        return $query->where('reading_type', $readingType);
    }

    public function scopeByReadingSource($query, $readingSource)
    {
        return $query->where('reading_source', $readingSource);
    }

    public function scopeByReadingStatus($query, $readingStatus)
    {
        return $query->where('reading_status', $readingStatus);
    }

    public function scopeByMeter($query, $meterId)
    {
        return $query->where('meter_id', $meterId);
    }

    public function scopeByInstallation($query, $installationId)
    {
        return $query->where('installation_id', $installationId);
    }

    public function scopeByConsumptionPoint($query, $consumptionPointId)
    {
        return $query->where('consumption_point_id', $consumptionPointId);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeValid($query)
    {
        return $query->where('reading_status', self::READING_STATUS_VALID);
    }

    public function scopeInvalid($query)
    {
        return $query->where('reading_status', self::READING_STATUS_INVALID);
    }

    public function scopeSuspicious($query)
    {
        return $query->where('reading_status', self::READING_STATUS_SUSPICIOUS);
    }

    public function scopeEstimated($query)
    {
        return $query->where('reading_status', self::READING_STATUS_ESTIMATED);
    }

    public function scopeCorrected($query)
    {
        return $query->where('reading_status', self::READING_STATUS_CORRECTED);
    }

    public function scopeMissing($query)
    {
        return $query->where('reading_status', self::READING_STATUS_MISSING);
    }

    public function scopeValidated($query)
    {
        return $query->whereNotNull('validated_at');
    }

    public function scopeUnvalidated($query)
    {
        return $query->whereNull('validated_at');
    }

    public function scopeHasCorrection($query)
    {
        return $query->whereNotNull('corrected_at');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('reading_timestamp', [$startDate, $endDate]);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('reading_timestamp', $date);
    }

    public function scopeByHour($query, $hour)
    {
        return $query->whereHour('reading_timestamp', $hour);
    }

    public function scopeByMonth($query, $month)
    {
        return $query->whereMonth('reading_timestamp', $month);
    }

    public function scopeByYear($query, $year)
    {
        return $query->whereYear('reading_timestamp', $year);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('reading_timestamp', today());
    }

    public function scopeYesterday($query)
    {
        return $query->whereDate('reading_timestamp', today()->subDay());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('reading_timestamp', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('reading_timestamp', now()->month)
                    ->whereYear('reading_timestamp', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('reading_timestamp', now()->year);
    }

    public function scopeHighQuality($query, $minScore = 80)
    {
        return $query->where('quality_score', '>=', $minScore);
    }

    public function scopeLowQuality($query, $maxScore = 50)
    {
        return $query->where('quality_score', '<=', $maxScore);
    }

    public function scopeInstantaneous($query)
    {
        return $query->where('reading_type', self::READING_TYPE_INSTANTANEOUS);
    }

    public function scopeInterval($query)
    {
        return $query->where('reading_type', self::READING_TYPE_INTERVAL);
    }

    public function scopeCumulative($query)
    {
        return $query->where('reading_type', self::READING_TYPE_CUMULATIVE);
    }

    public function scopeDemand($query)
    {
        return $query->where('reading_type', self::READING_TYPE_DEMAND);
    }

    public function scopeEnergy($query)
    {
        return $query->where('reading_type', self::READING_TYPE_ENERGY);
    }

    public function scopePowerFactor($query)
    {
        return $query->where('reading_type', self::READING_TYPE_POWER_FACTOR);
    }

    public function scopeVoltage($query)
    {
        return $query->where('reading_type', self::READING_TYPE_VOLTAGE);
    }

    public function scopeCurrent($query)
    {
        return $query->where('reading_type', self::READING_TYPE_CURRENT);
    }

    public function scopeFrequency($query)
    {
        return $query->where('reading_type', self::READING_TYPE_FREQUENCY);
    }

    public function scopeAutomatic($query)
    {
        return $query->where('reading_source', self::READING_SOURCE_AUTOMATIC);
    }

    public function scopeManual($query)
    {
        return $query->where('reading_source', self::READING_SOURCE_MANUAL);
    }

    public function scopeRemote($query)
    {
        return $query->where('reading_source', self::READING_SOURCE_REMOTE);
    }

    public function scopeEstimatedSource($query)
    {
        return $query->where('reading_source', self::READING_SOURCE_ESTIMATED);
    }

    public function scopeCalculated($query)
    {
        return $query->where('reading_source', self::READING_SOURCE_CALCULATED);
    }

    public function scopeImported($query)
    {
        return $query->where('reading_source', self::READING_SOURCE_IMPORTED);
    }

    // Métodos de validación
    public function isValid(): bool
    {
        return $this->reading_status === self::READING_STATUS_VALID;
    }

    public function isInvalid(): bool
    {
        return $this->reading_status === self::READING_STATUS_INVALID;
    }

    public function isSuspicious(): bool
    {
        return $this->reading_status === self::READING_STATUS_SUSPICIOUS;
    }

    public function isEstimated(): bool
    {
        return $this->reading_status === self::READING_STATUS_ESTIMATED;
    }

    public function isCorrected(): bool
    {
        return $this->reading_status === self::READING_STATUS_CORRECTED;
    }

    public function isMissing(): bool
    {
        return $this->reading_status === self::READING_STATUS_MISSING;
    }

    public function isInstantaneous(): bool
    {
        return $this->reading_type === self::READING_TYPE_INSTANTANEOUS;
    }

    public function isInterval(): bool
    {
        return $this->reading_type === self::READING_TYPE_INTERVAL;
    }

    public function isCumulative(): bool
    {
        return $this->reading_type === self::READING_TYPE_CUMULATIVE;
    }

    public function isDemand(): bool
    {
        return $this->reading_type === self::READING_TYPE_DEMAND;
    }

    public function isEnergy(): bool
    {
        return $this->reading_type === self::READING_TYPE_ENERGY;
    }

    public function isPowerFactor(): bool
    {
        return $this->reading_type === self::READING_TYPE_POWER_FACTOR;
    }

    public function isVoltage(): bool
    {
        return $this->reading_type === self::READING_TYPE_VOLTAGE;
    }

    public function isCurrent(): bool
    {
        return $this->reading_type === self::READING_TYPE_CURRENT;
    }

    public function isFrequency(): bool
    {
        return $this->reading_type === self::READING_TYPE_FREQUENCY;
    }

    public function isAutomatic(): bool
    {
        return $this->reading_source === self::READING_SOURCE_AUTOMATIC;
    }

    public function isManual(): bool
    {
        return $this->reading_source === self::READING_SOURCE_MANUAL;
    }

    public function isRemote(): bool
    {
        return $this->reading_source === self::READING_SOURCE_REMOTE;
    }

    public function isCalculated(): bool
    {
        return $this->reading_source === self::READING_SOURCE_CALCULATED;
    }

    public function isImported(): bool
    {
        return $this->reading_source === self::READING_SOURCE_IMPORTED;
    }

    public function isHighQuality(): bool
    {
        return $this->quality_score >= 80;
    }

    public function isLowQuality(): bool
    {
        return $this->quality_score <= 50;
    }

    public function isValidated(): bool
    {
        return !is_null($this->validated_at);
    }

    public function hasCorrection(): bool
    {
        return !is_null($this->corrected_at);
    }

    public function hasAlarms(): bool
    {
        return !empty($this->alarms);
    }

    public function hasEvents(): bool
    {
        return !empty($this->events);
    }

    // Métodos de cálculo
    public function getHourOfDay(): int
    {
        return (int) $this->reading_timestamp->format('G');
    }

    public function getDayOfWeek(): int
    {
        return (int) $this->reading_timestamp->format('N');
    }

    public function getDayOfYear(): int
    {
        return (int) $this->reading_timestamp->format('z');
    }

    public function getWeekOfYear(): int
    {
        return (int) $this->reading_timestamp->format('W');
    }

    public function getMonthOfYear(): int
    {
        return (int) $this->reading_timestamp->format('n');
    }

    public function isPeakHour(): bool
    {
        $hour = $this->getHourOfDay();
        // Definir horas pico (ejemplo: 8-10 y 18-22)
        return ($hour >= 8 && $hour <= 10) || ($hour >= 18 && $hour <= 22);
    }

    public function isOffPeakHour(): bool
    {
        $hour = $this->getHourOfDay();
        // Definir horas valle (ejemplo: 23-7)
        return $hour >= 23 || $hour <= 7;
    }

    public function isWeekend(): bool
    {
        $dayOfWeek = $this->getDayOfWeek();
        return $dayOfWeek >= 6; // 6 = Sábado, 7 = Domingo
    }

    public function isBusinessDay(): bool
    {
        return !$this->isWeekend();
    }

    public function getSeason(): string
    {
        $month = $this->getMonthOfYear();
        
        if ($month >= 3 && $month <= 5) {
            return 'spring';
        } elseif ($month >= 6 && $month <= 8) {
            return 'summer';
        } elseif ($month >= 9 && $month <= 11) {
            return 'autumn';
        } else {
            return 'winter';
        }
    }

    public function getConsumptionDelta(): float
    {
        if ($this->previous_reading_value && $this->reading_value) {
            return $this->reading_value - $this->previous_reading_value;
        }
        
        return $this->consumption_value ?? 0;
    }

    public function getDemandValue(): float
    {
        return $this->demand_value ?? 0;
    }

    public function getPowerFactorValue(): float
    {
        return $this->power_factor ?? 0;
    }

    public function getVoltageValue(): float
    {
        return $this->voltage_value ?? 0;
    }

    public function getCurrentValue(): float
    {
        return $this->current_value ?? 0;
    }

    public function getFrequencyValue(): float
    {
        return $this->frequency_value ?? 0;
    }

    public function getTemperatureValue(): float
    {
        return $this->temperature ?? 0;
    }

    public function getHumidityValue(): float
    {
        return $this->humidity ?? 0;
    }

    // Métodos de formato
    public function getFormattedReadingType(): string
    {
        return self::getReadingTypes()[$this->reading_type] ?? 'Desconocido';
    }

    public function getFormattedReadingSource(): string
    {
        return self::getReadingSources()[$this->reading_source] ?? 'Desconocido';
    }

    public function getFormattedReadingStatus(): string
    {
        return self::getReadingStatuses()[$this->reading_status] ?? 'Desconocido';
    }

    public function getFormattedReadingTimestamp(): string
    {
        return $this->reading_timestamp->format('d/m/Y H:i:s');
    }

    public function getFormattedDate(): string
    {
        return $this->reading_timestamp->format('d/m/Y');
    }

    public function getFormattedTime(): string
    {
        return $this->reading_timestamp->format('H:i:s');
    }

    public function getFormattedReadingValue(): string
    {
        return number_format($this->reading_value, 4) . ' ' . $this->reading_unit;
    }

    public function getFormattedPreviousReadingValue(): string
    {
        return $this->previous_reading_value ? number_format($this->previous_reading_value, 4) . ' ' . $this->reading_unit : 'N/A';
    }

    public function getFormattedConsumptionValue(): string
    {
        return $this->consumption_value ? number_format($this->consumption_value, 4) . ' ' . ($this->consumption_unit ?? $this->reading_unit) : 'N/A';
    }

    public function getFormattedDemandValue(): string
    {
        return $this->demand_value ? number_format($this->demand_value, 4) . ' ' . ($this->demand_unit ?? $this->reading_unit) : 'N/A';
    }

    public function getFormattedPowerFactor(): string
    {
        return $this->power_factor ? number_format($this->power_factor, 3) : 'N/A';
    }

    public function getFormattedVoltageValue(): string
    {
        return $this->voltage_value ? number_format($this->voltage_value, 2) . ' ' . ($this->voltage_unit ?? 'V') : 'N/A';
    }

    public function getFormattedCurrentValue(): string
    {
        return $this->current_value ? number_format($this->current_value, 2) . ' ' . ($this->current_unit ?? 'A') : 'N/A';
    }

    public function getFormattedFrequencyValue(): string
    {
        return $this->frequency_value ? number_format($this->frequency_value, 2) . ' ' . ($this->frequency_unit ?? 'Hz') : 'N/A';
    }

    public function getFormattedTemperature(): string
    {
        return $this->temperature ? number_format($this->temperature, 2) . ' ' . ($this->temperature_unit ?? '°C') : 'N/A';
    }

    public function getFormattedHumidity(): string
    {
        return $this->humidity ? number_format($this->humidity, 2) . ' ' . ($this->humidity_unit ?? '%') : 'N/A';
    }

    public function getFormattedQualityScore(): string
    {
        return $this->quality_score ? number_format($this->quality_score, 1) . '%' : 'N/A';
    }

    public function getFormattedConsumptionDelta(): string
    {
        $delta = $this->getConsumptionDelta();
        return number_format($delta, 4) . ' ' . $this->reading_unit;
    }

    public function getFormattedSeason(): string
    {
        return match($this->getSeason()) {
            'spring' => 'Primavera',
            'summer' => 'Verano',
            'autumn' => 'Otoño',
            'winter' => 'Invierno',
            default => 'Desconocida',
        };
    }

    // Clases de badges para Filament
    public function getReadingStatusBadgeClass(): string
    {
        return match($this->reading_status) {
            self::READING_STATUS_VALID => 'bg-green-100 text-green-800',
            self::READING_STATUS_INVALID => 'bg-red-100 text-red-800',
            self::READING_STATUS_SUSPICIOUS => 'bg-yellow-100 text-yellow-800',
            self::READING_STATUS_ESTIMATED => 'bg-orange-100 text-orange-800',
            self::READING_STATUS_CORRECTED => 'bg-blue-100 text-blue-800',
            self::READING_STATUS_MISSING => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getReadingTypeBadgeClass(): string
    {
        return match($this->reading_type) {
            self::READING_TYPE_INSTANTANEOUS => 'bg-blue-100 text-blue-800',
            self::READING_TYPE_INTERVAL => 'bg-green-100 text-green-800',
            self::READING_TYPE_CUMULATIVE => 'bg-purple-100 text-purple-800',
            self::READING_TYPE_DEMAND => 'bg-yellow-100 text-yellow-800',
            self::READING_TYPE_ENERGY => 'bg-indigo-100 text-indigo-800',
            self::READING_TYPE_POWER_FACTOR => 'bg-pink-100 text-pink-800',
            self::READING_TYPE_VOLTAGE => 'bg-cyan-100 text-cyan-800',
            self::READING_TYPE_CURRENT => 'bg-orange-100 text-orange-800',
            self::READING_TYPE_FREQUENCY => 'bg-red-100 text-red-800',
            self::READING_TYPE_OTHER => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getReadingSourceBadgeClass(): string
    {
        return match($this->reading_source) {
            self::READING_SOURCE_AUTOMATIC => 'bg-green-100 text-green-800',
            self::READING_SOURCE_MANUAL => 'bg-blue-100 text-blue-800',
            self::READING_SOURCE_REMOTE => 'bg-purple-100 text-purple-800',
            self::READING_SOURCE_ESTIMATED => 'bg-yellow-100 text-yellow-800',
            self::READING_SOURCE_CALCULATED => 'bg-indigo-100 text-indigo-800',
            self::READING_SOURCE_IMPORTED => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getQualityBadgeClass(): string
    {
        if (!$this->quality_score) {
            return 'bg-gray-100 text-gray-800';
        }
        
        if ($this->quality_score >= 90) {
            return 'bg-green-100 text-green-800';
        } elseif ($this->quality_score >= 80) {
            return 'bg-blue-100 text-blue-800';
        } elseif ($this->quality_score >= 70) {
            return 'bg-yellow-100 text-yellow-800';
        } elseif ($this->quality_score >= 60) {
            return 'bg-orange-100 text-orange-800';
        } else {
            return 'bg-red-100 text-red-800';
        }
    }

    public function getValidationBadgeClass(): string
    {
        if ($this->isValidated()) {
            return 'bg-green-100 text-green-800';
        } else {
            return 'bg-yellow-100 text-yellow-800';
        }
    }

    public function getCorrectionBadgeClass(): string
    {
        if ($this->isCorrected()) {
            return 'bg-blue-100 text-blue-800';
        } else {
            return 'bg-gray-100 text-gray-800';
        }
    }
}
