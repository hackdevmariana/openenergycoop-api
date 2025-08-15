<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;

trait HasMultilingual
{
    /**
     * Available languages for the application
     */
    protected array $availableLanguages = ['es', 'en', 'ca', 'eu'];

    /**
     * Default fallback language
     */
    protected string $fallbackLanguage = 'es';

    /**
     * Get available languages for this model
     */
    public function getAvailableLanguages(): array
    {
        return $this->availableLanguages;
    }

    /**
     * Get the model in a specific language
     */
    public function inLanguage(string $language)
    {
        return static::where($this->getKeyName(), $this->getKey())
            ->where('language', $language)
            ->first();
    }

    /**
     * Check if the model has content in a specific language
     */
    public function hasLanguage(string $language): bool
    {
        return static::where($this->getKeyName(), $this->getKey())
            ->where('language', $language)
            ->exists();
    }

    /**
     * Get fallback content for a specific language
     */
    public function getFallbackContent(string $targetLanguage)
    {
        // First try to get content in target language
        $content = $this->inLanguage($targetLanguage);
        
        if ($content) {
            return $content;
        }

        // Try fallback language
        $fallback = $this->inLanguage($this->fallbackLanguage);
        
        if ($fallback) {
            return $fallback;
        }

        // Return first available language
        return static::where($this->getKeyName(), $this->getKey())
            ->first();
    }

    /**
     * Get all language versions of this content
     */
    public function getAllLanguageVersions()
    {
        $baseQuery = static::query();
        
        // For models that have a base content ID (like translations)
        if (isset($this->translatable_id)) {
            return $baseQuery->where('translatable_id', $this->translatable_id);
        }
        
        // For models that share the same slug across languages
        if (isset($this->slug)) {
            return $baseQuery->where('slug', $this->slug)
                ->where('organization_id', $this->organization_id);
        }

        // Default: just this instance
        return $baseQuery->where($this->getKeyName(), $this->getKey());
    }

    /**
     * Scope to filter by language
     */
    public function scopeInLanguage(Builder $query, string $language): Builder
    {
        return $query->where('language', $language);
    }

    /**
     * Scope to get models with fallback language
     */
    public function scopeWithFallback(Builder $query, string $language, string $fallback = null): Builder
    {
        $fallback = $fallback ?? $this->fallbackLanguage;
        
        return $query->where(function ($q) use ($language, $fallback) {
            $q->where('language', $language)
              ->orWhere('language', $fallback);
        });
    }

    /**
     * Scope to get models in current app language
     */
    public function scopeInCurrentLanguage(Builder $query): Builder
    {
        return $query->inLanguage(App::getLocale());
    }

    /**
     * Scope to get models with current language and fallback
     */
    public function scopeWithCurrentLanguageFallback(Builder $query): Builder
    {
        return $query->withFallback(App::getLocale());
    }

    /**
     * Get the language attribute with fallback
     */
    public function getLanguageAttribute($value): string
    {
        return $value ?? $this->fallbackLanguage;
    }

    /**
     * Check if this is the fallback language version
     */
    public function isFallbackLanguage(): bool
    {
        return $this->language === $this->fallbackLanguage;
    }

    /**
     * Get missing languages for this content
     */
    public function getMissingLanguages(): array
    {
        $existing = $this->getAllLanguageVersions()
            ->pluck('language')
            ->toArray();
            
        return array_diff($this->availableLanguages, $existing);
    }

    /**
     * Boot the trait
     */
    protected static function bootHasMultilingual(): void
    {
        // Set default language if not provided
        static::creating(function ($model) {
            if (empty($model->language)) {
                $model->language = App::getLocale() ?? $model->fallbackLanguage;
            }
        });
    }
}
