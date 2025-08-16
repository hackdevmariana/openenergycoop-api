<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasCaching;
use Illuminate\Support\Str;

class Region extends Model
{
    use HasFactory, HasCaching;

    protected $fillable = [
        'name',
        'slug',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class);
    }

    public function municipalities(): HasMany
    {
        return $this->hasManyThrough(Municipality::class, Province::class);
    }

    // Mutadores
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    // Scopes
    public function scopeWithProvinceCount($query)
    {
        return $query->withCount('provinces');
    }

    public function scopeWithMunicipalityCount($query)
    {
        return $query->withCount('municipalities');
    }

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    // Métodos de negocio
    public function getProvincesCount(): int
    {
        return $this->getCachedCount('provinces_count', function () {
            return $this->provinces()->count();
        });
    }

    public function getMunicipalitiesCount(): int
    {
        return $this->getCachedCount('municipalities_count', function () {
            return $this->municipalities()->count();
        });
    }

    /**
     * Get provinces with their municipality counts
     */
    public function getProvincesWithCounts()
    {
        return $this->getCachedData('provinces_with_counts', function () {
            return $this->provinces()
                       ->withCount('municipalities')
                       ->orderBy('name')
                       ->get();
        });
    }

    /**
     * Check if region has any weather data
     */
    public function hasWeatherData(): bool
    {
        return $this->getCachedData('has_weather_data', function () {
            return WeatherSnapshot::whereHas('municipality.province', function ($query) {
                $query->where('region_id', $this->id);
            })->exists();
        });
    }

    /**
     * Get latest weather snapshot for the region
     */
    public function getLatestWeatherSnapshot()
    {
        return $this->getCachedData('latest_weather', function () {
            return WeatherSnapshot::whereHas('municipality.province', function ($query) {
                $query->where('region_id', $this->id);
            })->latest('timestamp')->first();
        }, 300); // Cache for 5 minutes
    }

    /**
     * Get average weather data for the region
     */
    public function getAverageWeatherData(?\Carbon\Carbon $from = null, ?\Carbon\Carbon $to = null): array
    {
        $cacheKey = 'avg_weather_' . ($from?->format('Y-m-d') ?? 'all') . '_' . ($to?->format('Y-m-d') ?? 'all');
        
        return $this->getCachedData($cacheKey, function () use ($from, $to) {
            $query = WeatherSnapshot::whereHas('municipality.province', function ($query) {
                $query->where('region_id', $this->id);
            });

            if ($from) {
                $query->where('timestamp', '>=', $from);
            }
            if ($to) {
                $query->where('timestamp', '<=', $to);
            }

            return [
                'avg_temperature' => $query->avg('temperature'),
                'avg_cloud_coverage' => $query->avg('cloud_coverage'),
                'avg_solar_radiation' => $query->avg('solar_radiation'),
                'data_points' => $query->count(),
            ];
        }, 1800); // Cache for 30 minutes
    }

    // Cache tags para invalidación
    public function getCacheTags(): array
    {
        return ['regions', "region:{$this->id}"];
    }
}