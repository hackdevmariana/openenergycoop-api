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
        'meter_id',
        'timestamp',
        'kwh',
        'delta_kwh',
        'type',
        'source',
        'validated_at',
        'quality_score',
        'anomaly_detected',
        'anomaly_type',
        'correction_applied',
        'weather_conditions',
        'notes',
        'external_reference',
        'batch_id',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'kwh' => 'decimal:4',
        'delta_kwh' => 'decimal:4',
        'validated_at' => 'datetime',
        'quality_score' => 'decimal:2',
        'anomaly_detected' => 'boolean',
        'correction_applied' => 'boolean',
        'weather_conditions' => 'array',
    ];

    // Enums
    const TYPE_PRODUCTION = 'production';
    const TYPE_CONSUMPTION = 'consumption';
    const TYPE_STORAGE = 'storage';
    const TYPE_DISTRIBUTION = 'distribution';
    const TYPE_EXPORT = 'export';
    const TYPE_IMPORT = 'import';

    const SOURCE_MANUAL = 'manual';
    const SOURCE_AUTOMATIC = 'automatic';
    const SOURCE_API_IMPORT = 'api_import';
    const SOURCE_SCADA = 'scada';
    const SOURCE_ESTIMATED = 'estimated';
    const SOURCE_CORRECTED = 'corrected';

    const ANOMALY_TYPE_SPIKE = 'spike';
    const ANOMALY_TYPE_DROP = 'drop';
    const ANOMALY_TYPE_ZERO = 'zero';
    const ANOMALY_TYPE_NEGATIVE = 'negative';
    const ANOMALY_TYPE_OUT_OF_RANGE = 'out_of_range';
    const ANOMALY_TYPE_COMMUNICATION_ERROR = 'communication_error';

    public static function getTypes(): array
    {
        return [
            self::TYPE_PRODUCTION => 'Producción',
            self::TYPE_CONSUMPTION => 'Consumo',
            self::TYPE_STORAGE => 'Almacenamiento',
            self::TYPE_DISTRIBUTION => 'Distribución',
            self::TYPE_EXPORT => 'Exportación',
            self::TYPE_IMPORT => 'Importación',
        ];
    }

    public static function getSources(): array
    {
        return [
            self::SOURCE_MANUAL => 'Manual',
            self::SOURCE_AUTOMATIC => 'Automático',
            self::SOURCE_API_IMPORT => 'API',
            self::SOURCE_SCADA => 'SCADA',
            self::SOURCE_ESTIMATED => 'Estimado',
            self::SOURCE_CORRECTED => 'Corregido',
        ];
    }

    public static function getAnomalyTypes(): array
    {
        return [
            self::ANOMALY_TYPE_SPIKE => 'Pico',
            self::ANOMALY_TYPE_DROP => 'Caída',
            self::ANOMALY_TYPE_ZERO => 'Cero',
            self::ANOMALY_TYPE_NEGATIVE => 'Negativo',
            self::ANOMALY_TYPE_OUT_OF_RANGE => 'Fuera de Rango',
            self::ANOMALY_TYPE_COMMUNICATION_ERROR => 'Error de Comunicación',
        ];
    }

    // Relaciones
    public function meter(): BelongsTo
    {
        return $this->belongsTo(EnergyMeter::class);
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }

    public function scopeByMeter($query, $meterId)
    {
        return $query->where('meter_id', $meterId);
    }

    public function scopeValidated($query)
    {
        return $query->whereNotNull('validated_at');
    }

    public function scopeUnvalidated($query)
    {
        return $query->whereNull('validated_at');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('timestamp', [$startDate, $endDate]);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('timestamp', $date);
    }

    public function scopeByHour($query, $hour)
    {
        return $query->whereHour('timestamp', $hour);
    }

    public function scopeByMonth($query, $month)
    {
        return $query->whereMonth('timestamp', $month);
    }

    public function scopeByYear($query, $year)
    {
        return $query->whereYear('timestamp', $year);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('timestamp', today());
    }

    public function scopeYesterday($query)
    {
        return $query->whereDate('timestamp', today()->subDay());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('timestamp', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('timestamp', now()->month)
                    ->whereYear('timestamp', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('timestamp', now()->year);
    }

    public function scopeAnomalies($query)
    {
        return $query->where('anomaly_detected', true);
    }

    public function scopeHighQuality($query, $minScore = 80)
    {
        return $query->where('quality_score', '>=', $minScore);
    }

    public function scopeLowQuality($query, $maxScore = 50)
    {
        return $query->where('quality_score', '<=', $maxScore);
    }

    public function scopeProduction($query)
    {
        return $query->where('type', self::TYPE_PRODUCTION);
    }

    public function scopeConsumption($query)
    {
        return $query->where('type', self::TYPE_CONSUMPTION);
    }

    public function scopeStorage($query)
    {
        return $query->where('type', self::TYPE_STORAGE);
    }

    public function scopeAutomatic($query)
    {
        return $query->where('source', self::SOURCE_AUTOMATIC);
    }

    public function scopeManual($query)
    {
        return $query->where('source', self::SOURCE_MANUAL);
    }

    // Métodos
    public function isProduction(): bool
    {
        return $this->type === self::TYPE_PRODUCTION;
    }

    public function isConsumption(): bool
    {
        return $this->type === self::TYPE_CONSUMPTION;
    }

    public function isStorage(): bool
    {
        return $this->type === self::TYPE_STORAGE;
    }

    public function isExport(): bool
    {
        return $this->type === self::TYPE_EXPORT;
    }

    public function isImport(): bool
    {
        return $this->type === self::TYPE_IMPORT;
    }

    public function isAutomatic(): bool
    {
        return $this->source === self::SOURCE_AUTOMATIC;
    }

    public function isManual(): bool
    {
        return $this->source === self::SOURCE_MANUAL;
    }

    public function isEstimated(): bool
    {
        return $this->source === self::SOURCE_ESTIMATED;
    }

    public function isCorrected(): bool
    {
        return $this->source === self::SOURCE_CORRECTED;
    }

    public function isAnomaly(): bool
    {
        return $this->anomaly_detected;
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

    public function getHourOfDay(): int
    {
        return (int) $this->timestamp->format('G');
    }

    public function getDayOfWeek(): int
    {
        return (int) $this->timestamp->format('N');
    }

    public function getDayOfYear(): int
    {
        return (int) $this->timestamp->format('z');
    }

    public function getWeekOfYear(): int
    {
        return (int) $this->timestamp->format('W');
    }

    public function getMonthOfYear(): int
    {
        return (int) $this->timestamp->format('n');
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

    public function getFormattedTimestamp(): string
    {
        return $this->timestamp->format('d/m/Y H:i:s');
    }

    public function getFormattedDate(): string
    {
        return $this->timestamp->format('d/m/Y');
    }

    public function getFormattedTime(): string
    {
        return $this->timestamp->format('H:i:s');
    }

    public function getFormattedKwh(): string
    {
        return number_format($this->kwh, 4) . ' kWh';
    }

    public function getFormattedDeltaKwh(): string
    {
        return number_format($this->delta_kwh, 4) . ' kWh';
    }

    public function getFormattedQualityScore(): string
    {
        return number_format($this->quality_score, 1) . '%';
    }

    public function getFormattedType(): string
    {
        return self::getTypes()[$this->type] ?? 'Desconocido';
    }

    public function getFormattedSource(): string
    {
        return self::getSources()[$this->source] ?? 'Desconocido';
    }

    public function getFormattedAnomalyType(): string
    {
        if (!$this->anomaly_detected) {
            return 'Ninguna';
        }
        
        return self::getAnomalyTypes()[$this->anomaly_type] ?? 'Desconocida';
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

    public function getStatusBadgeClass(): string
    {
        if ($this->anomaly_detected) {
            return 'bg-red-100 text-red-800';
        }
        
        if (!$this->isValidated()) {
            return 'bg-yellow-100 text-yellow-800';
        }
        
        if ($this->isLowQuality()) {
            return 'bg-orange-100 text-orange-800';
        }
        
        if ($this->isHighQuality()) {
            return 'bg-green-100 text-green-800';
        }
        
        return 'bg-blue-100 text-blue-800';
    }

    public function getQualityBadgeClass(): string
    {
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

    public function getTypeBadgeClass(): string
    {
        return match($this->type) {
            self::TYPE_PRODUCTION => 'bg-green-100 text-green-800',
            self::TYPE_CONSUMPTION => 'bg-red-100 text-red-800',
            self::TYPE_STORAGE => 'bg-blue-100 text-blue-800',
            self::TYPE_DISTRIBUTION => 'bg-purple-100 text-purple-800',
            self::TYPE_EXPORT => 'bg-yellow-100 text-yellow-800',
            self::TYPE_IMPORT => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getSourceBadgeClass(): string
    {
        return match($this->source) {
            self::SOURCE_AUTOMATIC => 'bg-green-100 text-green-800',
            self::SOURCE_MANUAL => 'bg-blue-100 text-blue-800',
            self::SOURCE_API_IMPORT => 'bg-purple-100 text-purple-800',
            self::SOURCE_SCADA => 'bg-indigo-100 text-indigo-800',
            self::SOURCE_ESTIMATED => 'bg-yellow-100 text-yellow-800',
            self::SOURCE_CORRECTED => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
