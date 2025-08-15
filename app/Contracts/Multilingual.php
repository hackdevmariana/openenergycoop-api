<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Builder;

/**
 * Interface for models that support multiple languages
 */
interface Multilingual
{
    /**
     * Get available languages for this model
     */
    public function getAvailableLanguages(): array;

    /**
     * Get the model in a specific language
     */
    public function inLanguage(string $language);

    /**
     * Check if the model has content in a specific language
     */
    public function hasLanguage(string $language): bool;

    /**
     * Get fallback language content
     */
    public function getFallbackContent(string $targetLanguage);

    /**
     * Scope to filter by language
     */
    public function scopeInLanguage(Builder $query, string $language): Builder;

    /**
     * Scope to get models with fallback language
     */
    public function scopeWithFallback(Builder $query, string $language, string $fallback = 'es'): Builder;
}
