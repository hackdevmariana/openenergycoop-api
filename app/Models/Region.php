<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Support\Str;

class Region extends Model
{
    use HasFactory;

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

    public function municipalities()
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
        return $this->provinces()->count();
    }

    public function getMunicipalitiesCount(): int
    {
        return $this->municipalities()->count();
    }

    /**
     * Get provinces with their municipality counts
     */
    public function getProvincesWithCounts()
    {
        return $this->provinces()
                   ->withCount('municipalities')
                   ->orderBy('name')
                   ->get();
    }

    /**
     * Check if region has any weather data
     */
    public function hasWeatherData(): bool
    {
        return WeatherSnapshot::whereHas('municipality.province', function ($query) {
            $query->where('region_id', $this->id);
        })->exists();
    }

    /**
     * Get latest weather snapshot for the region
     */
    public function getLatestWeatherSnapshot()
    {
        return WeatherSnapshot::whereHas('municipality.province', function ($query) {
            $query->where('region_id', $this->id);
        })->latest('timestamp')->first();
    }

    /**
     * Get average weather data for the region
     */
    public function getAverageWeatherData(?\Carbon\Carbon $from = null, ?\Carbon\Carbon $to = null): array
    {
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
    }


}