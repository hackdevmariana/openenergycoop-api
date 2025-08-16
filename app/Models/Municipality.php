<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Support\Str;
use Carbon\Carbon;

class Municipality extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'text',
        'province_id',
    ];

    protected $casts = [
        'province_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function region()
    {
        return $this->hasOneThrough(Region::class, Province::class, 'id', 'id', 'province_id', 'region_id');
    }

    public function weatherSnapshots(): HasMany
    {
        return $this->hasMany(WeatherSnapshot::class);
    }

    // Mutadores
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value . '-' . $this->province?->name);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->name . ', ' . $this->province?->name;
    }

    public function getIsOperatingAttribute(): bool
    {
        return !empty($this->text);
    }

    // Scopes
    public function scopeInProvince($query, $provinceId)
    {
        return $query->where('province_id', $provinceId);
    }

    public function scopeInRegion($query, $regionId)
    {
        return $query->whereHas('province', function ($q) use ($regionId) {
            $q->where('region_id', $regionId);
        });
    }

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    public function scopeOperating($query)
    {
        return $query->whereNotNull('text');
    }

    public function scopeWithWeatherData($query)
    {
        return $query->whereHas('weatherSnapshots');
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where('name', 'like', "%{$search}%")
                    ->orWhereHas('province', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
    }

    // Métodos de negocio
    public function isOperating(): bool
    {
        return !empty($this->text);
    }

    /**
     * Get latest weather snapshot
     */
    public function getLatestWeather()
    {
        return $this->weatherSnapshots()->latest('timestamp')->first();
    }

    /**
     * Get weather for specific date range
     */
    public function getWeatherInRange(Carbon $from, Carbon $to)
    {
        return $this->weatherSnapshots()
                   ->whereBetween('timestamp', [$from, $to])
                   ->orderBy('timestamp')
                   ->get();
    }

    /**
     * Get average weather conditions
     */
    public function getAverageWeatherConditions(Carbon $from = null, Carbon $to = null): array
    {
        $query = $this->weatherSnapshots();

        if ($from) {
            $query->where('timestamp', '>=', $from);
        }
        if ($to) {
            $query->where('timestamp', '<=', $to);
        }

        return [
            'avg_temperature' => round($query->avg('temperature'), 2),
            'avg_cloud_coverage' => round($query->avg('cloud_coverage'), 2),
            'avg_solar_radiation' => round($query->avg('solar_radiation'), 2),
            'min_temperature' => $query->min('temperature'),
            'max_temperature' => $query->max('temperature'),
            'peak_solar_radiation' => $query->max('solar_radiation'),
            'weather_readings' => $query->count(),
        ];
    }

    /**
     * Get solar energy potential score (0-100)
     */
    public function getSolarEnergyPotential(): float
    {
        $weather = $this->getAverageWeatherConditions();
        
        if ($weather['weather_readings'] === 0) {
            return 0.0;
        }

        // Calcular score basado en radiación solar y cobertura de nubes
        $solarScore = min(($weather['avg_solar_radiation'] ?? 0) / 1000 * 100, 100); // Normalizar a 100
        $cloudPenalty = ($weather['avg_cloud_coverage'] ?? 0) / 100 * 30; // Penalizar por nubes
        
        return max(round($solarScore - $cloudPenalty, 1), 0.0);
    }

    /**
     * Get peak solar hours per day (approximate)
     */
    public function getPeakSolarHours(): float
    {
        $avgRadiation = $this->weatherSnapshots()->avg('solar_radiation');
        
        if (!$avgRadiation) {
            return 0.0;
        }

        // Aproximación: radiación promedio / 1000 W/m² = horas pico solares
        return round($avgRadiation / 1000, 2);
    }

    /**
     * Check if municipality has recent weather data (last 7 days)
     */
    public function hasRecentWeatherData(): bool
    {
        return $this->getCachedData('has_recent_weather', function () {
            return $this->weatherSnapshots()
                       ->where('timestamp', '>=', now()->subDays(7))
                       ->exists();
        }, 1800); // Cache for 30 minutes
    }

    /**
     * Get weather data summary for dashboards
     */
    public function getWeatherSummary(): array
    {
        return $this->getCachedData('weather_summary', function () {
            $latest = $this->getLatestWeather();
            $conditions = $this->getAverageWeatherConditions(now()->subDays(30));
            
            return [
                'current' => $latest ? [
                    'temperature' => $latest->temperature,
                    'cloud_coverage' => $latest->cloud_coverage,
                    'solar_radiation' => $latest->solar_radiation,
                    'timestamp' => $latest->timestamp,
                ] : null,
                'monthly_avg' => $conditions,
                'solar_potential' => $this->getSolarEnergyPotential(),
                'peak_solar_hours' => $this->getPeakSolarHours(),
                'has_recent_data' => $this->hasRecentWeatherData(),
            ];
        }, 600); // Cache for 10 minutes
    }

    // Cache tags para invalidación
    public function getCacheTags(): array
    {
        return ['municipalities', "municipality:{$this->id}", "province:{$this->province_id}"];
    }
}