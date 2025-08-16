<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasCaching;
use Illuminate\Support\Str;

class Province extends Model
{
    use HasFactory, HasCaching;

    protected $fillable = [
        'name',
        'slug',
        'region_id',
    ];

    protected $casts = [
        'region_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function municipalities(): HasMany
    {
        return $this->hasMany(Municipality::class);
    }

    public function weatherSnapshots(): HasMany
    {
        return $this->hasManyThrough(WeatherSnapshot::class, Municipality::class);
    }

    // Mutadores
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    // Scopes
    public function scopeInRegion($query, $regionId)
    {
        return $query->where('region_id', $regionId);
    }

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    public function scopeWithMunicipalityCount($query)
    {
        return $query->withCount('municipalities');
    }

    public function scopeWithWeatherData($query)
    {
        return $query->whereHas('weatherSnapshots');
    }

    // Métodos de negocio
    public function getMunicipalitiesCount(): int
    {
        return $this->getCachedCount('municipalities_count', function () {
            return $this->municipalities()->count();
        });
    }

    public function getOperatingMunicipalitiesCount(): int
    {
        return $this->getCachedCount('operating_municipalities_count', function () {
            return $this->municipalities()->whereNotNull('text')->count();
        });
    }

    /**
     * Get municipalities where the cooperative operates
     */
    public function getOperatingMunicipalities()
    {
        return $this->getCachedData('operating_municipalities', function () {
            return $this->municipalities()
                       ->whereNotNull('text')
                       ->orderBy('name')
                       ->get();
        });
    }

    /**
     * Get latest weather data for the province
     */
    public function getLatestWeatherData()
    {
        return $this->getCachedData('latest_weather', function () {
            return $this->weatherSnapshots()
                       ->with('municipality')
                       ->latest('timestamp')
                       ->limit(10)
                       ->get();
        }, 300); // Cache for 5 minutes
    }

    /**
     * Get average weather conditions for the province
     */
    public function getAverageWeatherConditions(?\Carbon\Carbon $from = null, ?\Carbon\Carbon $to = null): array
    {
        $cacheKey = 'weather_avg_' . ($from?->format('Y-m-d') ?? 'all') . '_' . ($to?->format('Y-m-d') ?? 'all');
        
        return $this->getCachedData($cacheKey, function () use ($from, $to) {
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
                'max_solar_radiation' => $query->max('solar_radiation'),
                'data_points' => $query->count(),
            ];
        }, 1800); // Cache for 30 minutes
    }

    /**
     * Get best municipalities for solar energy (highest solar radiation)
     */
    public function getBestSolarMunicipalities(int $limit = 5): array
    {
        return $this->getCachedData("best_solar_municipalities_{$limit}", function () use ($limit) {
            return $this->weatherSnapshots()
                       ->selectRaw('municipality_id, AVG(solar_radiation) as avg_solar_radiation')
                       ->whereNotNull('solar_radiation')
                       ->groupBy('municipality_id')
                       ->orderByDesc('avg_solar_radiation')
                       ->limit($limit)
                       ->with('municipality')
                       ->get()
                       ->map(function ($snapshot) {
                           return [
                               'municipality' => $snapshot->municipality->name,
                               'avg_solar_radiation' => round($snapshot->avg_solar_radiation, 2),
                           ];
                       })
                       ->toArray();
        }, 3600); // Cache for 1 hour
    }

    /**
     * Check if province has weather monitoring
     */
    public function hasWeatherMonitoring(): bool
    {
        return $this->getCachedData('has_weather_monitoring', function () {
            return $this->weatherSnapshots()->exists();
        });
    }

    // Cache tags para invalidación
    public function getCacheTags(): array
    {
        return ['provinces', "province:{$this->id}", "region:{$this->region_id}"];
    }
}