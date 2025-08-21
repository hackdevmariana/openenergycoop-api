<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnergyForecast extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'forecastable_id',
        'forecastable_type',
        'forecast_type',
        'horizon_hours',
        'target_date',
        'generated_at',
        'confidence_level',
        'weather_conditions',
        'seasonal_factors',
        'historical_patterns',
        'market_conditions',
        'algorithm_version',
        'input_data_sources',
        'forecasted_values',
        'actual_values',
        'accuracy_metrics',
        'notes',
        'is_active',
        'expires_at',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'target_date' => 'datetime',
        'generated_at' => 'datetime',
        'confidence_level' => 'decimal:2',
        'weather_conditions' => 'array',
        'seasonal_factors' => 'array',
        'historical_patterns' => 'array',
        'market_conditions' => 'array',
        'forecasted_values' => 'array',
        'actual_values' => 'array',
        'accuracy_metrics' => 'array',
        'input_data_sources' => 'array',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Enums
    const FORECAST_TYPE_PRODUCTION = 'production';
    const FORECAST_TYPE_CONSUMPTION = 'consumption';
    const FORECAST_TYPE_DEMAND = 'demand';
    const FORECAST_TYPE_PRICE = 'price';
    const FORECAST_TYPE_WEATHER = 'weather';
    const FORECAST_TYPE_AVAILABILITY = 'availability';
    const FORECAST_TYPE_MAINTENANCE = 'maintenance';

    const HORIZON_1_HOUR = 1;
    const HORIZON_6_HOURS = 6;
    const HORIZON_12_HOURS = 12;
    const HORIZON_24_HOURS = 24;
    const HORIZON_48_HOURS = 48;
    const HORIZON_72_HOURS = 72;
    const HORIZON_1_WEEK = 168;
    const HORIZON_1_MONTH = 720;

    public static function getForecastTypes(): array
    {
        return [
            self::FORECAST_TYPE_PRODUCTION => 'Producción',
            self::FORECAST_TYPE_CONSUMPTION => 'Consumo',
            self::FORECAST_TYPE_DEMAND => 'Demanda',
            self::FORECAST_TYPE_PRICE => 'Precio',
            self::FORECAST_TYPE_WEATHER => 'Clima',
            self::FORECAST_TYPE_AVAILABILITY => 'Disponibilidad',
            self::FORECAST_TYPE_MAINTENANCE => 'Mantenimiento',
        ];
    }

    public static function getHorizons(): array
    {
        return [
            self::HORIZON_1_HOUR => '1 Hora',
            self::HORIZON_6_HOURS => '6 Horas',
            self::HORIZON_12_HOURS => '12 Horas',
            self::HORIZON_24_HOURS => '24 Horas',
            self::HORIZON_48_HOURS => '48 Horas',
            self::HORIZON_72_HOURS => '72 Horas',
            self::HORIZON_1_WEEK => '1 Semana',
            self::HORIZON_1_MONTH => '1 Mes',
        ];
    }

    // Relaciones
    public function forecastable(): MorphTo
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('forecast_type', $type);
    }

    public function scopeByHorizon($query, $horizon)
    {
        return $query->where('horizon_hours', $horizon);
    }

    public function scopeByTargetDate($query, $date)
    {
        return $query->whereDate('target_date', $date);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('target_date', [$startDate, $endDate]);
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopePendingApproval($query)
    {
        return $query->whereNull('approved_at');
    }

    public function scopeHighConfidence($query, $minConfidence = 80)
    {
        return $query->where('confidence_level', '>=', $minConfidence);
    }

    public function scopeLowConfidence($query, $maxConfidence = 50)
    {
        return $query->where('confidence_level', '<=', $maxConfidence);
    }

    public function scopeProduction($query)
    {
        return $query->where('forecast_type', self::FORECAST_TYPE_PRODUCTION);
    }

    public function scopeConsumption($query)
    {
        return $query->where('forecast_type', self::FORECAST_TYPE_CONSUMPTION);
    }

    public function scopeDemand($query)
    {
        return $query->where('forecast_type', self::FORECAST_TYPE_DEMAND);
    }

    public function scopePrice($query)
    {
        return $query->where('forecast_type', self::FORECAST_TYPE_PRICE);
    }

    public function scopeWeather($query)
    {
        return $query->where('forecast_type', self::FORECAST_TYPE_WEATHER);
    }

    public function scopeShortTerm($query)
    {
        return $query->where('horizon_hours', '<=', self::HORIZON_24_HOURS);
    }

    public function scopeMediumTerm($query)
    {
        return $query->whereBetween('horizon_hours', [
            self::HORIZON_24_HOURS + 1,
            self::HORIZON_1_WEEK
        ]);
    }

    public function scopeLongTerm($query)
    {
        return $query->where('horizon_hours', '>', self::HORIZON_1_WEEK);
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

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isProduction(): bool
    {
        return $this->forecast_type === self::FORECAST_TYPE_PRODUCTION;
    }

    public function isConsumption(): bool
    {
        return $this->forecast_type === self::FORECAST_TYPE_CONSUMPTION;
    }

    public function isDemand(): bool
    {
        return $this->forecast_type === self::FORECAST_TYPE_DEMAND;
    }

    public function isPrice(): bool
    {
        return $this->forecast_type === self::FORECAST_TYPE_PRICE;
    }

    public function isWeather(): bool
    {
        return $this->forecast_type === self::FORECAST_TYPE_WEATHER;
    }

    public function isShortTerm(): bool
    {
        return $this->horizon_hours <= self::HORIZON_24_HOURS;
    }

    public function isMediumTerm(): bool
    {
        return $this->horizon_hours > self::HORIZON_24_HOURS && 
               $this->horizon_hours <= self::HORIZON_1_WEEK;
    }

    public function isLongTerm(): bool
    {
        return $this->horizon_hours > self::HORIZON_1_WEEK;
    }

    public function isHighConfidence(): bool
    {
        return $this->confidence_level >= 80;
    }

    public function isLowConfidence(): bool
    {
        return $this->confidence_level <= 50;
    }

    public function getTimeToTarget(): ?int
    {
        if (!$this->target_date) {
            return null;
        }
        
        return now()->diffInSeconds($this->target_date, false);
    }

    public function isTargetDatePast(): bool
    {
        return $this->target_date && $this->target_date->isPast();
    }

    public function isTargetDateToday(): bool
    {
        return $this->target_date && $this->target_date->isToday();
    }

    public function isTargetDateFuture(): bool
    {
        return $this->target_date && $this->target_date->isFuture();
    }

    public function getForecastedValueForHour(int $hour): ?float
    {
        if (!isset($this->forecasted_values[$hour])) {
            return null;
        }
        
        return $this->forecasted_values[$hour];
    }

    public function getActualValueForHour(int $hour): ?float
    {
        if (!isset($this->actual_values[$hour])) {
            return null;
        }
        
        return $this->actual_values[$hour];
    }

    public function getTotalForecastedValue(): float
    {
        if (!is_array($this->forecasted_values)) {
            return 0;
        }
        
        return array_sum($this->forecasted_values);
    }

    public function getTotalActualValue(): float
    {
        if (!is_array($this->actual_values)) {
            return 0;
        }
        
        return array_sum($this->actual_values);
    }

    public function getAverageForecastedValue(): float
    {
        $total = $this->getTotalForecastedValue();
        $count = count($this->forecasted_values);
        
        return $count > 0 ? $total / $count : 0;
    }

    public function getAverageActualValue(): float
    {
        $total = $this->getTotalActualValue();
        $count = count($this->actual_values);
        
        return $count > 0 ? $total / $count : 0;
    }

    public function getPeakForecastedValue(): float
    {
        if (!is_array($this->forecasted_values) || empty($this->forecasted_values)) {
            return 0;
        }
        
        return max($this->forecasted_values);
    }

    public function getPeakActualValue(): float
    {
        if (!is_array($this->actual_values) || empty($this->actual_values)) {
            return 0;
        }
        
        return max($this->actual_values);
    }

    public function getPeakHour(): ?int
    {
        if (!is_array($this->forecasted_values) || empty($this->forecasted_values)) {
            return null;
        }
        
        return array_search(max($this->forecasted_values), $this->forecasted_values);
    }

    public function getValleyHour(): ?int
    {
        if (!is_array($this->forecasted_values) || empty($this->forecasted_values)) {
            return null;
        }
        
        return array_search(min($this->forecasted_values), $this->forecasted_values);
    }

    public function getAccuracyScore(): float
    {
        if (!isset($this->accuracy_metrics['overall_score'])) {
            return 0;
        }
        
        return $this->accuracy_metrics['overall_score'];
    }

    public function getMeanAbsoluteError(): float
    {
        if (!isset($this->accuracy_metrics['mae'])) {
            return 0;
        }
        
        return $this->accuracy_metrics['mae'];
    }

    public function getRootMeanSquareError(): float
    {
        if (!isset($this->accuracy_metrics['rmse'])) {
            return 0;
        }
        
        return $this->accuracy_metrics['rmse'];
    }

    public function getMeanAbsolutePercentageError(): float
    {
        if (!isset($this->accuracy_metrics['mape'])) {
            return 0;
        }
        
        return $this->accuracy_metrics['mape'];
    }

    public function getAccuracyClass(): string
    {
        $score = $this->getAccuracyScore();
        
        if ($score >= 90) {
            return 'A+';
        } elseif ($score >= 80) {
            return 'A';
        } elseif ($score >= 70) {
            return 'B';
        } elseif ($score >= 60) {
            return 'C';
        } else {
            return 'D';
        }
    }

    public function getConfidenceClass(): string
    {
        if ($this->confidence_level >= 90) {
            return 'Muy Alta';
        } elseif ($this->confidence_level >= 80) {
            return 'Alta';
        } elseif ($this->confidence_level >= 70) {
            return 'Media';
        } elseif ($this->confidence_level >= 60) {
            return 'Baja';
        } else {
            return 'Muy Baja';
        }
    }

    public function getFormattedTargetDate(): string
    {
        if (!$this->target_date) {
            return 'No especificada';
        }
        
        return $this->target_date->format('d/m/Y H:i:s');
    }

    public function getFormattedGeneratedAt(): string
    {
        if (!$this->generated_at) {
            return 'No especificada';
        }
        
        return $this->generated_at->format('d/m/Y H:i:s');
    }

    public function getFormattedExpiresAt(): string
    {
        if (!$this->expires_at) {
            return 'No expira';
        }
        
        return $this->expires_at->format('d/m/Y H:i:s');
    }

    public function getFormattedHorizon(): string
    {
        return self::getHorizons()[$this->horizon_hours] ?? 'Desconocido';
    }

    public function getFormattedForecastType(): string
    {
        return self::getForecastTypes()[$this->forecast_type] ?? 'Desconocido';
    }

    public function getFormattedConfidenceLevel(): string
    {
        return number_format($this->confidence_level, 1) . '%';
    }

    public function getFormattedTotalForecastedValue(): string
    {
        return number_format($this->getTotalForecastedValue(), 2) . ' kWh';
    }

    public function getFormattedTotalActualValue(): string
    {
        return number_format($this->getTotalActualValue(), 2) . ' kWh';
    }

    public function getFormattedPeakForecastedValue(): string
    {
        return number_format($this->getPeakForecastedValue(), 2) . ' kWh';
    }

    public function getFormattedPeakActualValue(): string
    {
        return number_format($this->getPeakActualValue(), 2) . ' kWh';
    }

    public function getFormattedAccuracyScore(): string
    {
        return number_format($this->getAccuracyScore(), 1) . '%';
    }

    public function getFormattedMeanAbsoluteError(): string
    {
        return number_format($this->getMeanAbsoluteError(), 4);
    }

    public function getFormattedRootMeanSquareError(): string
    {
        return number_format($this->getRootMeanSquareError(), 4);
    }

    public function getFormattedMeanAbsolutePercentageError(): string
    {
        return number_format($this->getMeanAbsolutePercentageError(), 2) . '%';
    }

    public function getStatusBadgeClass(): string
    {
        if (!$this->is_active) {
            return 'bg-red-100 text-red-800';
        }
        
        if (!$this->isApproved()) {
            return 'bg-yellow-100 text-yellow-800';
        }
        
        if ($this->isExpired()) {
            return 'bg-gray-100 text-gray-800';
        }
        
        if ($this->isTargetDatePast()) {
            return 'bg-blue-100 text-blue-800';
        }
        
        return 'bg-green-100 text-green-800';
    }

    public function getConfidenceBadgeClass(): string
    {
        return match($this->getConfidenceClass()) {
            'Muy Alta' => 'bg-green-100 text-green-800',
            'Alta' => 'bg-blue-100 text-blue-800',
            'Media' => 'bg-yellow-100 text-yellow-800',
            'Baja' => 'bg-orange-100 text-orange-800',
            'Muy Baja' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getAccuracyBadgeClass(): string
    {
        return match($this->getAccuracyClass()) {
            'A+' => 'bg-green-100 text-green-800',
            'A' => 'bg-blue-100 text-blue-800',
            'B' => 'bg-yellow-100 text-yellow-800',
            'C' => 'bg-orange-100 text-orange-800',
            'D' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getTypeBadgeClass(): string
    {
        return match($this->forecast_type) {
            self::FORECAST_TYPE_PRODUCTION => 'bg-green-100 text-green-800',
            self::FORECAST_TYPE_CONSUMPTION => 'bg-red-100 text-red-800',
            self::FORECAST_TYPE_DEMAND => 'bg-blue-100 text-blue-800',
            self::FORECAST_TYPE_PRICE => 'bg-yellow-100 text-yellow-800',
            self::FORECAST_TYPE_WEATHER => 'bg-cyan-100 text-cyan-800',
            self::FORECAST_TYPE_AVAILABILITY => 'bg-purple-100 text-purple-800',
            self::FORECAST_TYPE_MAINTENANCE => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getHorizonBadgeClass(): string
    {
        if ($this->isShortTerm()) {
            return 'bg-green-100 text-green-800';
        } elseif ($this->isMediumTerm()) {
            return 'bg-yellow-100 text-yellow-800';
        } elseif ($this->isLongTerm()) {
            return 'bg-orange-100 text-orange-800';
        }
        
        return 'bg-gray-100 text-gray-800';
    }
}
