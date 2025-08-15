<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait HasCaching
{
    /**
     * Default cache duration in minutes
     */
    protected int $cacheDuration = 60;

    /**
     * Get the unique cache key for this model instance
     */
    public function getCacheKey(): string
    {
        return sprintf(
            '%s.%s.%s.%s',
            strtolower(class_basename($this)),
            $this->getKey(),
            $this->organization_id ?? 'global',
            $this->language ?? 'default'
        );
    }

    /**
     * Get cache tags for this model (for cache invalidation)
     */
    public function getCacheTags(): array
    {
        $tags = [
            strtolower(class_basename($this)),
            $this->getCacheKey(),
        ];

        if (isset($this->organization_id)) {
            $tags[] = "org.{$this->organization_id}";
        }

        if (isset($this->language)) {
            $tags[] = "lang.{$this->language}";
        }

        return $tags;
    }

    /**
     * Get cache duration in minutes
     */
    public function getCacheDuration(): int
    {
        return $this->cacheDuration;
    }

    /**
     * Determine if this model should be cached
     */
    public function shouldCache(): bool
    {
        // Don't cache draft content
        if (isset($this->is_draft) && $this->is_draft) {
            return false;
        }

        // Don't cache if explicitly disabled
        if (isset($this->cache_enabled) && !$this->cache_enabled) {
            return false;
        }

        return true;
    }

    /**
     * Get cached version of this model
     */
    public static function getCached($key, $callback = null)
    {
        $instance = new static();
        
        if (!$instance->shouldCache()) {
            return $callback ? $callback() : null;
        }

        // Try to use tags if supported, otherwise use simple caching
        try {
            return Cache::tags($instance->getCacheTags())
                ->remember($key, now()->addMinutes($instance->getCacheDuration()), $callback);
        } catch (\BadMethodCallException $e) {
            // Cache driver doesn't support tags, use simple remember
            return Cache::remember($key, now()->addMinutes($instance->getCacheDuration()), $callback);
        }
    }

    /**
     * Clear cache for this model
     */
    public function clearCache(): void
    {
        // Try to use tags if supported, otherwise just forget the key
        try {
            Cache::tags($this->getCacheTags())->flush();
        } catch (\BadMethodCallException $e) {
            // Cache driver doesn't support tags, just forget the specific key
            Cache::forget($this->getCacheKey());
            
            // Also try to forget some common related keys
            $relatedKeys = [
                strtolower(class_basename($this)) . '.list',
                strtolower(class_basename($this)) . '.count',
                'org.' . ($this->organization_id ?? 'global') . '.content',
            ];
            
            foreach ($relatedKeys as $key) {
                Cache::forget($key);
            }
        }
    }

    /**
     * Boot the trait
     */
    protected static function bootHasCaching(): void
    {
        static::saved(function ($model) {
            $model->clearCache();
        });

        static::deleted(function ($model) {
            $model->clearCache();
        });
    }
}
