<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class AppSetting extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'organization_id',
        'name',
        'slogan',
        'primary_color',
        'secondary_color',
        'locale',
        'custom_js',
        'favicon_path',
    ];

    protected $casts = [
        'organization_id' => 'integer',
        'primary_color' => 'string',
        'secondary_color' => 'string',
        'locale' => 'string',
        'custom_js' => 'string',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
        $this->addMediaCollection('favicon')->singleFile();
    }

    /**
     * Cache duration in minutes
     */
    public const CACHE_DURATION = 30;

    /**
     * Default settings for organizations without custom settings
     */
    public const DEFAULT_SETTINGS = [
        'name' => 'OpenEnergyCoop',
        'slogan' => 'Energía renovable para todos',
        'primary_color' => '#10B981',
        'secondary_color' => '#059669',
        'locale' => 'es',
        'custom_js' => null,
    ];

    protected static function booted(): void
    {
        static::saved(function ($model) {
            // Clear cache for specific organization and global cache
            Cache::forget("app.settings.{$model->organization_id}");
            Cache::forget('app.settings.global');
            Cache::forget('app.settings');
        });
        
        static::deleted(function ($model) {
            // Clear cache for specific organization and global cache
            Cache::forget("app.settings.{$model->organization_id}");
            Cache::forget('app.settings.global');
            Cache::forget('app.settings');
        });
    }

    /**
     * Get cached settings for a specific organization
     */
    public static function forOrg(int $orgId): Collection
    {
        $cacheKey = "app.settings.{$orgId}";
        
        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_DURATION), function () use ($orgId) {
            $settings = self::where('organization_id', $orgId)->first();

            if (!$settings) {
                // Return default settings if no custom settings found
                return collect(self::DEFAULT_SETTINGS);
            }

            // Convert model to collection and add media URLs
            $settingsData = collect($settings->toArray());
            
            // Add media URLs
            if ($settings->getFirstMedia('logo')) {
                $settingsData['logo_url'] = $settings->getFirstMediaUrl('logo');
            }
            
            if ($settings->getFirstMedia('favicon')) {
                $settingsData['favicon_url'] = $settings->getFirstMediaUrl('favicon');
            }

            // Add favicon_path if available
            if ($settings->favicon_path) {
                $settingsData['favicon_path'] = $settings->favicon_path;
            }

            return $settingsData;
        });
    }

    /**
     * Get cached settings for the global/default organization
     */
    public static function global(): Collection
    {
        $cacheKey = 'app.settings.global';
        
        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_DURATION), function () {
            // Get the first available settings or return defaults
            $settings = self::first();

            if (!$settings) {
                return collect(self::DEFAULT_SETTINGS);
            }

            // Convert model to collection and add media URLs
            $settingsData = collect($settings->toArray());
            
            // Add media URLs
            if ($settings->getFirstMedia('logo')) {
                $settingsData['logo_url'] = $settings->getFirstMediaUrl('logo');
            }
            
            if ($settings->getFirstMedia('favicon')) {
                $settingsData['favicon_url'] = $settings->getFirstMediaUrl('favicon');
            }

            // Add favicon_path if available
            if ($settings->favicon_path) {
                $settingsData['favicon_path'] = $settings->favicon_path;
            }

            return $settingsData;
        });
    }

    /**
     * Get a specific setting value for an organization
     */
    public static function getSetting(string $key, ?int $orgId = null, $default = null)
    {
        $settings = $orgId ? self::forOrg($orgId) : self::global();
        return $settings->get($key, $default);
    }

    /**
     * Set settings in Laravel config for easy access
     */
    public static function loadIntoConfig(?int $orgId = null): void
    {
        $settings = $orgId ? self::forOrg($orgId) : self::global();
        config(['appsettings' => $settings->toArray()]);
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(?int $orgId = null): void
    {
        if ($orgId) {
            Cache::forget("app.settings.{$orgId}");
        } else {
            // Clear all organization caches (this is expensive, use sparingly)
            Cache::flush(); // In production, consider a more targeted approach
        }
        
        Cache::forget('app.settings.global');
        Cache::forget('app.settings');
    }

    /**
     * Warm up cache for an organization
     */
    public static function warmCache(?int $orgId = null): Collection
    {
        return $orgId ? self::forOrg($orgId) : self::global();
    }

    /**
     * Get all organizations with custom settings (for cache warming)
     */
    public static function getOrganizationsWithSettings(): Collection
    {
        return self::whereNotNull('organization_id')
            ->distinct('organization_id')
            ->pluck('organization_id');
    }
}
