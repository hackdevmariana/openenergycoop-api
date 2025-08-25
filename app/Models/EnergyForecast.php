<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;


class EnergyForecast extends Model
{
    use HasFactory;

    protected $fillable = [
        'forecast_number',
        'name',
        'description',
        'forecast_type',
        'forecast_horizon',
        'forecast_method',
        'forecast_status',
        'accuracy_level',
        'accuracy_score',
        'confidence_interval_lower',
        'confidence_interval_upper',
        'confidence_level',
        'source_id',
        'source_type',
        'target_id',
        'target_type',
        'forecast_start_time',
        'forecast_end_time',
        'generation_time',
        'valid_from',
        'valid_until',
        'expiry_time',
        'time_zone',
        'time_resolution',
        'forecast_periods',
        'total_forecasted_value',
        'forecast_unit',
        'baseline_value',
        'trend_value',
        'seasonal_value',
        'cyclical_value',
        'irregular_value',
        'forecast_data',
        'baseline_data',
        'trend_data',
        'seasonal_data',
        'cyclical_data',
        'irregular_data',
        'weather_data',
        'input_variables',
        'model_parameters',
        'validation_metrics',
        'performance_history',
        'tags',
        'created_by',
        'approved_by',
        'approved_at',
        'validated_by',
        'validated_at',
        'notes',
    ];

    protected $casts = [
        'accuracy_score' => 'decimal:2',
        'confidence_interval_lower' => 'decimal:2',
        'confidence_interval_upper' => 'decimal:2',
        'confidence_level' => 'decimal:2',
        'forecast_start_time' => 'datetime',
        'forecast_end_time' => 'datetime',
        'generation_time' => 'datetime',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'expiry_time' => 'datetime',
        'forecast_periods' => 'integer',
        'total_forecasted_value' => 'decimal:2',
        'baseline_value' => 'decimal:2',
        'trend_value' => 'decimal:2',
        'seasonal_value' => 'decimal:2',
        'cyclical_value' => 'decimal:2',
        'irregular_value' => 'decimal:2',
        'approved_at' => 'datetime',
        'validated_at' => 'datetime',
        'forecast_data' => 'array',
        'baseline_data' => 'array',
        'trend_data' => 'array',
        'seasonal_data' => 'array',
        'cyclical_data' => 'array',
        'irregular_data' => 'array',
        'weather_data' => 'array',
        'input_variables' => 'array',
        'model_parameters' => 'array',
        'validation_metrics' => 'array',
        'performance_history' => 'array',
        'tags' => 'array',
    ];

    // Enums
    const FORECAST_TYPE_DEMAND = 'demand';
    const FORECAST_TYPE_GENERATION = 'generation';
    const FORECAST_TYPE_CONSUMPTION = 'consumption';
    const FORECAST_TYPE_PRICE = 'price';
    const FORECAST_TYPE_WEATHER = 'weather';
    const FORECAST_TYPE_LOAD = 'load';
    const FORECAST_TYPE_RENEWABLE = 'renewable';
    const FORECAST_TYPE_STORAGE = 'storage';
    const FORECAST_TYPE_TRANSMISSION = 'transmission';
    const FORECAST_TYPE_OTHER = 'other';

    const FORECAST_HORIZON_HOURLY = 'hourly';
    const FORECAST_HORIZON_DAILY = 'daily';
    const FORECAST_HORIZON_WEEKLY = 'weekly';
    const FORECAST_HORIZON_MONTHLY = 'monthly';
    const FORECAST_HORIZON_QUARTERLY = 'quarterly';
    const FORECAST_HORIZON_YEARLY = 'yearly';
    const FORECAST_HORIZON_LONG_TERM = 'long_term';

    const FORECAST_METHOD_STATISTICAL = 'statistical';
    const FORECAST_METHOD_MACHINE_LEARNING = 'machine_learning';
    const FORECAST_METHOD_PHYSICAL_MODEL = 'physical_model';
    const FORECAST_METHOD_HYBRID = 'hybrid';
    const FORECAST_METHOD_EXPERT_JUDGMENT = 'expert_judgment';
    const FORECAST_METHOD_OTHER = 'other';

    const FORECAST_STATUS_DRAFT = 'draft';
    const FORECAST_STATUS_ACTIVE = 'active';
    const FORECAST_STATUS_VALIDATED = 'validated';
    const FORECAST_STATUS_EXPIRED = 'expired';
    const FORECAST_STATUS_SUPERSEDED = 'superseded';
    const FORECAST_STATUS_ARCHIVED = 'archived';

    const ACCURACY_LEVEL_LOW = 'low';
    const ACCURACY_LEVEL_MEDIUM = 'medium';
    const ACCURACY_LEVEL_HIGH = 'high';
    const ACCURACY_LEVEL_VERY_HIGH = 'very_high';

    public static function getForecastTypes(): array
    {
        return [
            self::FORECAST_TYPE_DEMAND => 'Demanda',
            self::FORECAST_TYPE_GENERATION => 'Generación',
            self::FORECAST_TYPE_CONSUMPTION => 'Consumo',
            self::FORECAST_TYPE_PRICE => 'Precio',
            self::FORECAST_TYPE_WEATHER => 'Clima',
            self::FORECAST_TYPE_LOAD => 'Carga',
            self::FORECAST_TYPE_RENEWABLE => 'Renovable',
            self::FORECAST_TYPE_STORAGE => 'Almacenamiento',
            self::FORECAST_TYPE_TRANSMISSION => 'Transmisión',
            self::FORECAST_TYPE_OTHER => 'Otro',
        ];
    }

    public static function getForecastHorizons(): array
    {
        return [
            self::FORECAST_HORIZON_HOURLY => 'Por Hora',
            self::FORECAST_HORIZON_DAILY => 'Diario',
            self::FORECAST_HORIZON_WEEKLY => 'Semanal',
            self::FORECAST_HORIZON_MONTHLY => 'Mensual',
            self::FORECAST_HORIZON_QUARTERLY => 'Trimestral',
            self::FORECAST_HORIZON_YEARLY => 'Anual',
            self::FORECAST_HORIZON_LONG_TERM => 'Largo Plazo',
        ];
    }

    public static function getForecastMethods(): array
    {
        return [
            self::FORECAST_METHOD_STATISTICAL => 'Estadístico',
            self::FORECAST_METHOD_MACHINE_LEARNING => 'Machine Learning',
            self::FORECAST_METHOD_PHYSICAL_MODEL => 'Modelo Físico',
            self::FORECAST_METHOD_HYBRID => 'Híbrido',
            self::FORECAST_METHOD_EXPERT_JUDGMENT => 'Juicio Experto',
            self::FORECAST_METHOD_OTHER => 'Otro',
        ];
    }

    public static function getForecastStatuses(): array
    {
        return [
            self::FORECAST_STATUS_DRAFT => 'Borrador',
            self::FORECAST_STATUS_ACTIVE => 'Activo',
            self::FORECAST_STATUS_VALIDATED => 'Validado',
            self::FORECAST_STATUS_EXPIRED => 'Expirado',
            self::FORECAST_STATUS_SUPERSEDED => 'Reemplazado',
            self::FORECAST_STATUS_ARCHIVED => 'Archivado',
        ];
    }

    public static function getAccuracyLevels(): array
    {
        return [
            self::ACCURACY_LEVEL_LOW => 'Baja',
            self::ACCURACY_LEVEL_MEDIUM => 'Media',
            self::ACCURACY_LEVEL_HIGH => 'Alta',
            self::ACCURACY_LEVEL_VERY_HIGH => 'Muy Alta',
        ];
    }

    // Relaciones
    public function source(): BelongsTo
    {
        return $this->belongsTo(EnergySource::class, 'source_id');
    }

    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // Scopes
    public function scopeByForecastType($query, $forecastType)
    {
        return $query->where('forecast_type', $forecastType);
    }

    public function scopeByForecastHorizon($query, $forecastHorizon)
    {
        return $query->where('forecast_horizon', $forecastHorizon);
    }

    public function scopeByForecastMethod($query, $forecastMethod)
    {
        return $query->where('forecast_method', $forecastMethod);
    }

    public function scopeByForecastStatus($query, $forecastStatus)
    {
        return $query->where('forecast_status', $forecastStatus);
    }

    public function scopeByAccuracyLevel($query, $accuracyLevel)
    {
        return $query->where('accuracy_level', $accuracyLevel);
    }

    public function scopeBySource($query, $sourceId)
    {
        return $query->where('source_id', $sourceId);
    }

    public function scopeByTarget($query, $targetId, $targetType = null)
    {
        $query = $query->where('target_id', $targetId);
        if ($targetType) {
            $query->where('target_type', $targetType);
        }
        return $query;
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('forecast_start_time', [$startDate, $endDate]);
    }

    public function scopeByGenerationTime($query, $date)
    {
        return $query->whereDate('generation_time', $date);
    }

    public function scopeByValidFrom($query, $date)
    {
        return $query->whereDate('valid_from', $date);
    }

    public function scopeByValidUntil($query, $date)
    {
        return $query->whereDate('valid_until', $date);
    }

    public function scopeByExpiryTime($query, $date)
    {
        return $query->whereDate('expiry_time', $date);
    }

    public function scopeDraft($query)
    {
        return $query->where('forecast_status', self::FORECAST_STATUS_DRAFT);
    }

    public function scopeActive($query)
    {
        return $query->where('forecast_status', self::FORECAST_STATUS_ACTIVE);
    }

    public function scopeValidatedStatus($query)
    {
        return $query->where('forecast_status', self::FORECAST_STATUS_VALIDATED);
    }

    public function scopeExpiredStatus($query)
    {
        return $query->where('forecast_status', self::FORECAST_STATUS_EXPIRED);
    }

    public function scopeSuperseded($query)
    {
        return $query->where('forecast_status', self::FORECAST_STATUS_SUPERSEDED);
    }

    public function scopeArchived($query)
    {
        return $query->where('forecast_status', self::FORECAST_STATUS_ARCHIVED);
    }

    public function scopeDemand($query)
    {
        return $query->where('forecast_type', self::FORECAST_TYPE_DEMAND);
    }

    public function scopeGeneration($query)
    {
        return $query->where('forecast_type', self::FORECAST_TYPE_GENERATION);
    }

    public function scopeConsumption($query)
    {
        return $query->where('forecast_type', self::FORECAST_TYPE_CONSUMPTION);
    }

    public function scopePrice($query)
    {
        return $query->where('forecast_type', self::FORECAST_TYPE_PRICE);
    }

    public function scopeWeather($query)
    {
        return $query->where('forecast_type', self::FORECAST_TYPE_WEATHER);
    }

    public function scopeLoad($query)
    {
        return $query->where('forecast_type', self::FORECAST_TYPE_LOAD);
    }

    public function scopeRenewable($query)
    {
        return $query->where('forecast_type', self::FORECAST_TYPE_RENEWABLE);
    }

    public function scopeStorage($query)
    {
        return $query->where('forecast_type', self::FORECAST_TYPE_STORAGE);
    }

    public function scopeTransmission($query)
    {
        return $query->where('forecast_type', self::FORECAST_TYPE_TRANSMISSION);
    }

    public function scopeHourly($query)
    {
        return $query->where('forecast_horizon', self::FORECAST_HORIZON_HOURLY);
    }

    public function scopeDaily($query)
    {
        return $query->where('forecast_horizon', self::FORECAST_HORIZON_DAILY);
    }

    public function scopeWeekly($query)
    {
        return $query->where('forecast_horizon', self::FORECAST_HORIZON_WEEKLY);
    }

    public function scopeMonthly($query)
    {
        return $query->where('forecast_horizon', self::FORECAST_HORIZON_MONTHLY);
    }

    public function scopeQuarterly($query)
    {
        return $query->where('forecast_horizon', self::FORECAST_HORIZON_QUARTERLY);
    }

    public function scopeYearly($query)
    {
        return $query->where('forecast_horizon', self::FORECAST_HORIZON_YEARLY);
    }

    public function scopeLongTerm($query)
    {
        return $query->where('forecast_horizon', self::FORECAST_HORIZON_LONG_TERM);
    }

    public function scopeStatistical($query)
    {
        return $query->where('forecast_method', self::FORECAST_METHOD_STATISTICAL);
    }

    public function scopeMachineLearning($query)
    {
        return $query->where('forecast_method', self::FORECAST_METHOD_MACHINE_LEARNING);
    }

    public function scopePhysicalModel($query)
    {
        return $query->where('forecast_method', self::FORECAST_METHOD_PHYSICAL_MODEL);
    }

    public function scopeHybrid($query)
    {
        return $query->where('forecast_method', self::FORECAST_METHOD_HYBRID);
    }

    public function scopeExpertJudgment($query)
    {
        return $query->where('forecast_method', self::FORECAST_METHOD_EXPERT_JUDGMENT);
    }

    public function scopeHighAccuracy($query)
    {
        return $query->whereIn('accuracy_level', [
            self::ACCURACY_LEVEL_HIGH,
            self::ACCURACY_LEVEL_VERY_HIGH,
        ]);
    }

    public function scopeMediumAccuracy($query)
    {
        return $query->where('accuracy_level', self::ACCURACY_LEVEL_MEDIUM);
    }

    public function scopeLowAccuracy($query)
    {
        return $query->where('accuracy_level', self::ACCURACY_LEVEL_LOW);
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopePendingApproval($query)
    {
        return $query->whereNull('approved_at');
    }

    public function scopeValidated($query)
    {
        return $query->whereNotNull('validated_at');
    }

    public function scopePendingValidation($query)
    {
        return $query->whereNull('validated_at');
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_time', '<=', now());
    }

    public function scopeNotExpired($query)
    {
        return $query->where('expiry_time', '>', now());
    }

    public function scopeByAccuracyScore($query, $minScore)
    {
        return $query->where('accuracy_score', '>=', $minScore);
    }

    public function scopeByConfidenceLevel($query, $minConfidence)
    {
        return $query->where('confidence_level', '>=', $minConfidence);
    }

    // Métodos de validación
    public function isDraft(): bool
    {
        return $this->forecast_status === self::FORECAST_STATUS_DRAFT;
    }

    public function isActive(): bool
    {
        return $this->forecast_status === self::FORECAST_STATUS_ACTIVE;
    }

    public function isValidatedStatus(): bool
    {
        return $this->forecast_status === self::FORECAST_STATUS_VALIDATED;
    }

    public function isExpired(): bool
    {
        return $this->forecast_status === self::FORECAST_STATUS_EXPIRED;
    }

    public function isSuperseded(): bool
    {
        return $this->forecast_status === self::FORECAST_STATUS_SUPERSEDED;
    }

    public function isArchived(): bool
    {
        return $this->forecast_status === self::FORECAST_STATUS_ARCHIVED;
    }

    public function isDemand(): bool
    {
        return $this->forecast_type === self::FORECAST_TYPE_DEMAND;
    }

    public function isGeneration(): bool
    {
        return $this->forecast_type === self::FORECAST_TYPE_GENERATION;
    }

    public function isConsumption(): bool
    {
        return $this->forecast_type === self::FORECAST_TYPE_CONSUMPTION;
    }

    public function isPrice(): bool
    {
        return $this->forecast_type === self::FORECAST_TYPE_PRICE;
    }

    public function isWeather(): bool
    {
        return $this->forecast_type === self::FORECAST_TYPE_WEATHER;
    }

    public function isLoad(): bool
    {
        return $this->forecast_type === self::FORECAST_TYPE_LOAD;
    }

    public function isRenewable(): bool
    {
        return $this->forecast_type === self::FORECAST_TYPE_RENEWABLE;
    }

    public function isStorage(): bool
    {
        return $this->forecast_type === self::FORECAST_TYPE_STORAGE;
    }

    public function isTransmission(): bool
    {
        return $this->forecast_type === self::FORECAST_TYPE_TRANSMISSION;
    }

    public function isHourly(): bool
    {
        return $this->forecast_horizon === self::FORECAST_HORIZON_HOURLY;
    }

    public function isDaily(): bool
    {
        return $this->forecast_horizon === self::FORECAST_HORIZON_DAILY;
    }

    public function isWeekly(): bool
    {
        return $this->forecast_horizon === self::FORECAST_HORIZON_WEEKLY;
    }

    public function isMonthly(): bool
    {
        return $this->forecast_horizon === self::FORECAST_HORIZON_MONTHLY;
    }

    public function isQuarterly(): bool
    {
        return $this->forecast_horizon === self::FORECAST_HORIZON_QUARTERLY;
    }

    public function isYearly(): bool
    {
        return $this->forecast_horizon === self::FORECAST_HORIZON_YEARLY;
    }

    public function isLongTerm(): bool
    {
        return $this->forecast_horizon === self::FORECAST_HORIZON_LONG_TERM;
    }

    public function isStatistical(): bool
    {
        return $this->forecast_method === self::FORECAST_METHOD_STATISTICAL;
    }

    public function isMachineLearning(): bool
    {
        return $this->forecast_method === self::FORECAST_METHOD_MACHINE_LEARNING;
    }

    public function isPhysicalModel(): bool
    {
        return $this->forecast_method === self::FORECAST_METHOD_PHYSICAL_MODEL;
    }

    public function isHybrid(): bool
    {
        return $this->forecast_method === self::FORECAST_METHOD_HYBRID;
    }

    public function isExpertJudgment(): bool
    {
        return $this->forecast_method === self::FORECAST_METHOD_EXPERT_JUDGMENT;
    }

    public function isHighAccuracy(): bool
    {
        return in_array($this->accuracy_level, [
            self::ACCURACY_LEVEL_HIGH,
            self::ACCURACY_LEVEL_VERY_HIGH,
        ]);
    }

    public function isMediumAccuracy(): bool
    {
        return $this->accuracy_level === self::ACCURACY_LEVEL_MEDIUM;
    }

    public function isLowAccuracy(): bool
    {
        return $this->accuracy_level === self::ACCURACY_LEVEL_LOW;
    }

    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    public function isValidated(): bool
    {
        return !is_null($this->validated_at);
    }

    public function isExpiredTime(): bool
    {
        return $this->expiry_time && $this->expiry_time->isPast();
    }

    public function isShortTerm(): bool
    {
        return in_array($this->forecast_horizon, [
            self::FORECAST_HORIZON_HOURLY,
            self::FORECAST_HORIZON_DAILY,
        ]);
    }

    public function isMediumTerm(): bool
    {
        return in_array($this->forecast_horizon, [
            self::FORECAST_HORIZON_WEEKLY,
            self::FORECAST_HORIZON_MONTHLY,
        ]);
    }

    public function isLongTermHorizon(): bool
    {
        return in_array($this->forecast_horizon, [
            self::FORECAST_HORIZON_QUARTERLY,
            self::FORECAST_HORIZON_YEARLY,
            self::FORECAST_HORIZON_LONG_TERM,
        ]);
    }

    // Métodos de cálculo
    public function getForecastDuration(): int
    {
        if (!$this->forecast_start_time || !$this->forecast_end_time) {
            return 0;
        }
        
        return $this->forecast_start_time->diffInHours($this->forecast_end_time);
    }

    public function getTimeToExpiry(): ?int
    {
        if (!$this->expiry_time) {
            return null;
        }
        
        return now()->diffInSeconds($this->expiry_time, false);
    }

    public function isExpiringSoon(int $hours = 24): bool
    {
        $timeToExpiry = $this->getTimeToExpiry();
        if ($timeToExpiry === null) {
            return false;
        }
        
        return $timeToExpiry <= ($hours * 3600);
    }

    public function getConfidenceInterval(): array
    {
        return [
            'lower' => $this->confidence_interval_lower,
            'upper' => $this->confidence_interval_upper,
        ];
    }

    public function getConfidenceRange(): float
    {
        if (!$this->confidence_interval_lower || !$this->confidence_interval_upper) {
            return 0;
        }
        
        return $this->confidence_interval_upper - $this->confidence_interval_lower;
    }

    public function getForecastDataForPeriod(int $period): ?array
    {
        if (!isset($this->forecast_data[$period])) {
            return null;
        }
        
        return $this->forecast_data[$period];
    }

    public function getBaselineDataForPeriod(int $period): ?array
    {
        if (!isset($this->baseline_data[$period])) {
            return null;
        }
        
        return $this->baseline_data[$period];
    }

    public function getTrendDataForPeriod(int $period): ?array
    {
        if (!isset($this->trend_data[$period])) {
            return null;
        }
        
        return $this->trend_data[$period];
    }

    public function getSeasonalDataForPeriod(int $period): ?array
    {
        if (!isset($this->seasonal_data[$period])) {
            return null;
        }
        
        return $this->seasonal_data[$period];
    }

    public function getCyclicalDataForPeriod(int $period): ?array
    {
        if (!isset($this->cyclical_data[$period])) {
            return null;
        }
        
        return $this->cyclical_data[$period];
    }

    public function getIrregularDataForPeriod(int $period): ?array
    {
        if (!isset($this->irregular_data[$period])) {
            return null;
        }
        
        return $this->irregular_data[$period];
    }

    public function getWeatherDataForPeriod(int $period): ?array
    {
        if (!isset($this->weather_data[$period])) {
            return null;
        }
        
        return $this->weather_data[$period];
    }

    public function getTotalForecastedValue(): float
    {
        return $this->total_forecasted_value ?? 0;
    }

    public function getBaselineValue(): float
    {
        return $this->baseline_value ?? 0;
    }

    public function getTrendValue(): float
    {
        return $this->trend_value ?? 0;
    }

    public function getSeasonalValue(): float
    {
        return $this->seasonal_value ?? 0;
    }

    public function getCyclicalValue(): float
    {
        return $this->cyclical_value ?? 0;
    }

    public function getIrregularValue(): float
    {
        return $this->irregular_value ?? 0;
    }

    public function getAccuracyScore(): float
    {
        return $this->accuracy_score ?? 0;
    }

    public function getConfidenceLevel(): float
    {
        return $this->confidence_level ?? 0;
    }

    // Métodos de formato
    public function getFormattedForecastType(): string
    {
        return self::getForecastTypes()[$this->forecast_type] ?? 'Desconocido';
    }

    public function getFormattedForecastHorizon(): string
    {
        return self::getForecastHorizons()[$this->forecast_horizon] ?? 'Desconocido';
    }

    public function getFormattedForecastMethod(): string
    {
        return self::getForecastMethods()[$this->forecast_type] ?? 'Desconocido';
    }

    public function getFormattedForecastStatus(): string
    {
        return self::getForecastStatuses()[$this->forecast_status] ?? 'Desconocido';
    }

    public function getFormattedAccuracyLevel(): string
    {
        return self::getAccuracyLevels()[$this->accuracy_level] ?? 'Desconocido';
    }

    public function getFormattedForecastStartTime(): string
    {
        return $this->forecast_start_time->format('d/m/Y H:i:s');
    }

    public function getFormattedForecastEndTime(): string
    {
        return $this->forecast_end_time->format('d/m/Y H:i:s');
    }

    public function getFormattedGenerationTime(): string
    {
        return $this->generation_time->format('d/m/Y H:i:s');
    }

    public function getFormattedValidFrom(): string
    {
        return $this->valid_from->format('d/m/Y H:i:s');
    }

    public function getFormattedValidUntil(): string
    {
        return $this->valid_until ? $this->valid_until->format('d/m/Y H:i:s') : 'N/A';
    }

    public function getFormattedExpiryTime(): string
    {
        return $this->expiry_time ? $this->expiry_time->format('d/m/Y H:i:s') : 'N/A';
    }

    public function getFormattedApprovedAt(): string
    {
        return $this->approved_at ? $this->approved_at->format('d/m/Y H:i:s') : 'N/A';
    }

    public function getFormattedValidatedAt(): string
    {
        return $this->validated_at ? $this->validated_at->format('d/m/Y H:i:s') : 'N/A';
    }

    public function getFormattedTotalForecastedValue(): string
    {
        if (!$this->total_forecasted_value) {
            return 'N/A';
        }
        
        $unit = $this->forecast_unit ?? 'kWh';
        return number_format($this->total_forecasted_value, 2) . ' ' . $unit;
    }

    public function getFormattedBaselineValue(): string
    {
        if (!$this->baseline_value) {
            return 'N/A';
        }
        
        $unit = $this->forecast_unit ?? 'kWh';
        return number_format($this->baseline_value, 2) . ' ' . $unit;
    }

    public function getFormattedTrendValue(): string
    {
        if (!$this->trend_value) {
            return 'N/A';
        }
        
        $unit = $this->forecast_unit ?? 'kWh';
        return number_format($this->trend_value, 2) . ' ' . $unit;
    }

    public function getFormattedSeasonalValue(): string
    {
        if (!$this->seasonal_value) {
            return 'N/A';
        }
        
        $unit = $this->forecast_unit ?? 'kWh';
        return number_format($this->seasonal_value, 2) . ' ' . $unit;
    }

    public function getFormattedCyclicalValue(): string
    {
        if (!$this->cyclical_value) {
            return 'N/A';
        }
        
        $unit = $this->forecast_unit ?? 'kWh';
        return number_format($this->cyclical_value, 2) . ' ' . $unit;
    }

    public function getFormattedIrregularValue(): string
    {
        if (!$this->irregular_value) {
            return 'N/A';
        }
        
        $unit = $this->forecast_unit ?? 'kWh';
        return number_format($this->irregular_value, 2) . ' ' . $unit;
    }

    public function getFormattedAccuracyScore(): string
    {
        return $this->accuracy_score ? number_format($this->accuracy_score, 2) . '%' : 'N/A';
    }

    public function getFormattedConfidenceLevel(): string
    {
        return $this->confidence_level ? number_format($this->confidence_level, 2) . '%' : 'N/A';
    }

    public function getFormattedConfidenceInterval(): string
    {
        if (!$this->confidence_interval_lower || !$this->confidence_interval_upper) {
            return 'N/A';
        }
        
        $unit = $this->forecast_unit ?? 'kWh';
        return number_format($this->confidence_interval_lower, 2) . ' - ' . 
               number_format($this->confidence_interval_upper, 2) . ' ' . $unit;
    }

    public function getFormattedConfidenceRange(): string
    {
        $range = $this->getConfidenceRange();
        if ($range <= 0) {
            return 'N/A';
        }
        
        $unit = $this->forecast_unit ?? 'kWh';
        return '±' . number_format($range / 2, 2) . ' ' . $unit;
    }

    public function getFormattedForecastDuration(): string
    {
        $duration = $this->getForecastDuration();
        if ($duration <= 0) {
            return 'N/A';
        }
        
        if ($duration < 24) {
            return $duration . ' horas';
        } elseif ($duration < 168) {
            return number_format($duration / 24, 1) . ' días';
        } else {
            return number_format($duration / 168, 1) . ' semanas';
        }
    }

    public function getFormattedTimeToExpiry(): string
    {
        $timeToExpiry = $this->getTimeToExpiry();
        if ($timeToExpiry === null) {
            return 'No expira';
        }
        
        if ($timeToExpiry <= 0) {
            return 'Expirado';
        }
        
        if ($timeToExpiry < 3600) {
            return number_format($timeToExpiry / 60, 0) . ' minutos';
        } elseif ($timeToExpiry < 86400) {
            return number_format($timeToExpiry / 3600, 1) . ' horas';
        } else {
            return number_format($timeToExpiry / 86400, 1) . ' días';
        }
    }

    // Clases de badges para Filament
    public function getForecastStatusBadgeClass(): string
    {
        return match($this->forecast_status) {
            self::FORECAST_STATUS_DRAFT => 'bg-gray-100 text-gray-800',
            self::FORECAST_STATUS_ACTIVE => 'bg-green-100 text-green-800',
            self::FORECAST_STATUS_VALIDATED => 'bg-blue-100 text-blue-800',
            self::FORECAST_STATUS_EXPIRED => 'bg-red-100 text-red-800',
            self::FORECAST_STATUS_SUPERSEDED => 'bg-orange-100 text-orange-800',
            self::FORECAST_STATUS_ARCHIVED => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getForecastTypeBadgeClass(): string
    {
        return match($this->forecast_type) {
            self::FORECAST_TYPE_DEMAND => 'bg-blue-100 text-blue-800',
            self::FORECAST_TYPE_GENERATION => 'bg-green-100 text-green-800',
            self::FORECAST_TYPE_CONSUMPTION => 'bg-red-100 text-red-800',
            self::FORECAST_TYPE_PRICE => 'bg-yellow-100 text-yellow-800',
            self::FORECAST_TYPE_WEATHER => 'bg-cyan-100 text-cyan-800',
            self::FORECAST_TYPE_LOAD => 'bg-purple-100 text-purple-800',
            self::FORECAST_TYPE_RENEWABLE => 'bg-green-100 text-green-800',
            self::FORECAST_TYPE_STORAGE => 'bg-indigo-100 text-indigo-800',
            self::FORECAST_TYPE_TRANSMISSION => 'bg-orange-100 text-orange-800',
            self::FORECAST_TYPE_OTHER => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getForecastHorizonBadgeClass(): string
    {
        return match($this->forecast_horizon) {
            self::FORECAST_HORIZON_HOURLY => 'bg-green-100 text-green-800',
            self::FORECAST_HORIZON_DAILY => 'bg-blue-100 text-blue-800',
            self::FORECAST_HORIZON_WEEKLY => 'bg-yellow-100 text-yellow-800',
            self::FORECAST_HORIZON_MONTHLY => 'bg-orange-100 text-orange-800',
            self::FORECAST_HORIZON_QUARTERLY => 'bg-purple-100 text-purple-800',
            self::FORECAST_HORIZON_YEARLY => 'bg-indigo-100 text-indigo-800',
            self::FORECAST_HORIZON_LONG_TERM => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getForecastMethodBadgeClass(): string
    {
        return match($this->forecast_method) {
            self::FORECAST_METHOD_STATISTICAL => 'bg-blue-100 text-blue-800',
            self::FORECAST_METHOD_MACHINE_LEARNING => 'bg-purple-100 text-purple-800',
            self::FORECAST_METHOD_PHYSICAL_MODEL => 'bg-green-100 text-green-800',
            self::FORECAST_METHOD_HYBRID => 'bg-orange-100 text-orange-800',
            self::FORECAST_METHOD_EXPERT_JUDGMENT => 'bg-yellow-100 text-yellow-800',
            self::FORECAST_METHOD_OTHER => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getAccuracyLevelBadgeClass(): string
    {
        return match($this->accuracy_level) {
            self::ACCURACY_LEVEL_LOW => 'bg-red-100 text-red-800',
            self::ACCURACY_LEVEL_MEDIUM => 'bg-yellow-100 text-yellow-800',
            self::ACCURACY_LEVEL_HIGH => 'bg-blue-100 text-blue-800',
            self::ACCURACY_LEVEL_VERY_HIGH => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getAccuracyScoreBadgeClass(): string
    {
        if (!$this->accuracy_score) {
            return 'bg-gray-100 text-gray-800';
        }
        
        if ($this->accuracy_score >= 90) {
            return 'bg-green-100 text-green-800';
        } elseif ($this->accuracy_score >= 80) {
            return 'bg-blue-100 text-blue-800';
        } elseif ($this->accuracy_score >= 70) {
            return 'bg-yellow-100 text-yellow-800';
        } else {
            return 'bg-red-100 text-red-800';
        }
    }

    public function getConfidenceLevelBadgeClass(): string
    {
        if (!$this->confidence_level) {
            return 'bg-gray-100 text-gray-800';
        }
        
        if ($this->confidence_level >= 90) {
            return 'bg-green-100 text-green-800';
        } elseif ($this->confidence_level >= 80) {
            return 'bg-blue-100 text-blue-800';
        } elseif ($this->confidence_level >= 70) {
            return 'bg-yellow-100 text-yellow-800';
        } else {
            return 'bg-red-100 text-red-800';
        }
    }

    public function getExpiryBadgeClass(): string
    {
        if ($this->isExpired()) {
            return 'bg-red-100 text-red-800';
        }
        
        if ($this->isExpiringSoon(1)) { // 1 hora
            return 'bg-red-100 text-red-800';
        }
        
        if ($this->isExpiringSoon(24)) { // 24 horas
            return 'bg-yellow-100 text-yellow-800';
        }
        
        return 'bg-green-100 text-green-800';
    }
}
