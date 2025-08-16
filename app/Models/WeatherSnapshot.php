<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasCaching;
use Carbon\Carbon;

class WeatherSnapshot extends Model
{
    use HasFactory, HasCaching;

    protected $fillable = [
        'municipality_id',
        'temperature',
        'cloud_coverage',
        'solar_radiation',
        'timestamp',
    ];

    protected $casts = [
        'municipality_id' => 'integer',
        'temperature' => 'decimal:2',
        'cloud_coverage' => 'decimal:2',
        'solar_radiation' => 'decimal:2',
        'timestamp' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function province(): BelongsTo
    {
        return $this->hasOneThrough(Province::class, Municipality::class, 'id', 'id', 'municipality_id', 'province_id');
    }

    public function region(): BelongsTo
    {
        return $this->hasOneThrough(Region::class, Municipality::class, 'id', 'id', 'municipality_id', 'province_id');
    }

    // Scopes
    public function scopeForMunicipality($query, $municipalityId)
    {
        return $query->where('municipality_id', $municipalityId);
    }

    public function scopeInProvince($query, $provinceId)
    {
        return $query->whereHas('municipality', function ($q) use ($provinceId) {
            $q->where('province_id', $provinceId);
        });
    }

    public function scopeInRegion($query, $regionId)
    {
        return $query->whereHas('municipality.province', function ($q) use ($regionId) {
            $q->where('region_id', $regionId);
        });
    }

    public function scopeInDateRange($query, Carbon $from, Carbon $to)
    {
        return $query->whereBetween('timestamp', [$from, $to]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('timestamp', today());
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

    public function scopeLatest($query)
    {
        return $query->latest('timestamp');
    }

    public function scopeWithGoodSolarConditions($query, float $minRadiation = 500)
    {
        return $query->where('solar_radiation', '>=', $minRadiation)
                    ->where('cloud_coverage', '<=', 50);
    }

    public function scopeOptimalSolarConditions($query)
    {
        return $query->where('solar_radiation', '>=', 800)
                    ->where('cloud_coverage', '<=', 20);
    }

    public function scopeRecentReadings($query, int $hours = 24)
    {
        return $query->where('timestamp', '>=', now()->subHours($hours));
    }

    // Accessors
    public function getTemperatureDisplayAttribute(): string
    {
        return $this->temperature ? $this->temperature . '°C' : 'N/A';
    }

    public function getCloudCoverageDisplayAttribute(): string
    {
        return $this->cloud_coverage ? $this->cloud_coverage . '%' : 'N/A';
    }

    public function getSolarRadiationDisplayAttribute(): string
    {
        return $this->solar_radiation ? $this->solar_radiation . ' W/m²' : 'N/A';
    }

    public function getAgeAttribute(): string
    {
        return $this->timestamp->diffForHumans();
    }

    public function getIsRecentAttribute(): bool
    {
        return $this->timestamp->isAfter(now()->subHours(6));
    }

    // Métodos de negocio
    public function isOptimalForSolar(): bool
    {
        return $this->solar_radiation >= 800 && $this->cloud_coverage <= 20;
    }

    public function isGoodForSolar(): bool
    {
        return $this->solar_radiation >= 500 && $this->cloud_coverage <= 50;
    }

    public function getSolarConditionRating(): string
    {
        if ($this->isOptimalForSolar()) {
            return 'excellent';
        }
        
        if ($this->isGoodForSolar()) {
            return 'good';
        }
        
        if ($this->solar_radiation >= 300) {
            return 'fair';
        }
        
        return 'poor';
    }

    public function getTemperatureRange(): string
    {
        if (!$this->temperature) {
            return 'unknown';
        }

        $temp = (float) $this->temperature;

        if ($temp < 0) return 'very_cold';
        if ($temp < 10) return 'cold';
        if ($temp < 20) return 'cool';
        if ($temp < 30) return 'warm';
        if ($temp < 40) return 'hot';
        
        return 'very_hot';
    }

    /**
     * Calculate estimated solar energy generation potential (kWh/kWp)
     */
    public function getEstimatedSolarGeneration(float $systemSizeKwp = 1.0): float
    {
        if (!$this->solar_radiation) {
            return 0.0;
        }

        // Fórmula simplificada: (Radiación solar / 1000) * Tamaño sistema * Factor eficiencia
        $efficiencyFactor = 0.75; // Factor típico considerando pérdidas del sistema
        
        return round(($this->solar_radiation / 1000) * $systemSizeKwp * $efficiencyFactor, 3);
    }

    /**
     * Get weather condition summary
     */
    public function getConditionSummary(): array
    {
        return [
            'temperature' => [
                'value' => $this->temperature,
                'display' => $this->temperature_display,
                'range' => $this->getTemperatureRange(),
            ],
            'solar' => [
                'radiation' => $this->solar_radiation,
                'display' => $this->solar_radiation_display,
                'rating' => $this->getSolarConditionRating(),
                'optimal' => $this->isOptimalForSolar(),
                'estimated_generation' => $this->getEstimatedSolarGeneration(),
            ],
            'clouds' => [
                'coverage' => $this->cloud_coverage,
                'display' => $this->cloud_coverage_display,
            ],
            'timestamp' => [
                'value' => $this->timestamp,
                'age' => $this->age,
                'is_recent' => $this->is_recent,
            ],
        ];
    }

    /**
     * Static method to get aggregated weather stats
     */
    public static function getRegionalStats($regionId, Carbon $from = null, Carbon $to = null): array
    {
        $query = static::inRegion($regionId);

        if ($from) {
            $query->where('timestamp', '>=', $from);
        }
        if ($to) {
            $query->where('timestamp', '<=', $to);
        }

        return [
            'total_readings' => $query->count(),
            'avg_temperature' => round($query->avg('temperature'), 2),
            'avg_solar_radiation' => round($query->avg('solar_radiation'), 2),
            'avg_cloud_coverage' => round($query->avg('cloud_coverage'), 2),
            'peak_solar_radiation' => $query->max('solar_radiation'),
            'min_temperature' => $query->min('temperature'),
            'max_temperature' => $query->max('temperature'),
            'optimal_solar_hours' => $query->optimalSolarConditions()->count(),
            'good_solar_hours' => $query->withGoodSolarConditions()->count(),
        ];
    }

    // Cache tags para invalidación
    public function getCacheTags(): array
    {
        return ['weather_snapshots', "municipality:{$this->municipality_id}"];
    }
}