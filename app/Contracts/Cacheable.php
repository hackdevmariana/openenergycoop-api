<?php

namespace App\Contracts;

/**
 * Interface for models that support intelligent caching
 */
interface Cacheable
{
    /**
     * Get the unique cache key for this model instance
     */
    public function getCacheKey(): string;

    /**
     * Get cache tags for this model (for cache invalidation)
     */
    public function getCacheTags(): array;

    /**
     * Get cache duration in minutes
     */
    public function getCacheDuration(): int;

    /**
     * Determine if this model should be cached
     */
    public function shouldCache(): bool;

    /**
     * Get cached version of this model
     */
    public static function getCached($key, $callback = null);

    /**
     * Clear cache for this model
     */
    public function clearCache(): void;
}
