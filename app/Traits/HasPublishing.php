<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

trait HasPublishing
{
    /**
     * Publish the model
     */
    public function publish(): void
    {
        $this->update([
            'is_draft' => false,
            'published_at' => $this->published_at ?? now(),
        ]);

        // Clear cache after publishing
        if (method_exists($this, 'clearCache')) {
            $this->clearCache();
        }
    }

    /**
     * Unpublish the model (set as draft)
     */
    public function unpublish(): void
    {
        $this->update([
            'is_draft' => true,
            'published_at' => null,
        ]);

        // Clear cache after unpublishing
        if (method_exists($this, 'clearCache')) {
            $this->clearCache();
        }
    }

    /**
     * Check if the model is published
     */
    public function isPublished(): bool
    {
        if ($this->is_draft) {
            return false;
        }

        // Check if publication date has passed
        if ($this->published_at && $this->published_at->isFuture()) {
            return false;
        }

        // Check exhibition period for Hero, Banner, etc.
        if (isset($this->exhibition_end) && $this->exhibition_end && Carbon::parse($this->exhibition_end)->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the model is in draft state
     */
    public function isDraft(): bool
    {
        return (bool) $this->is_draft;
    }

    /**
     * Check if the model can be published (validation)
     */
    public function canBePublished(): bool
    {
        // Basic validation - can be overridden in models
        if (isset($this->title) && empty($this->title)) {
            return false;
        }

        if (isset($this->content) && empty($this->content)) {
            return false;
        }

        return true;
    }

    /**
     * Schedule publication for a specific date
     */
    public function schedulePublication(\DateTime $date): void
    {
        $this->update([
            'published_at' => $date,
            'is_draft' => false,
        ]);
    }

    /**
     * Scope to get only published models
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_draft', false)
            ->where(function ($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now());
            })
            ->where(function ($q) {
                // Check exhibition_end if exists
                if ($this->getConnection()->getSchemaBuilder()->hasColumn($this->getTable(), 'exhibition_end')) {
                    $q->whereNull('exhibition_end')
                      ->orWhere('exhibition_end', '>=', now());
                }
            });
    }

    /**
     * Scope to get only draft models
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('is_draft', true);
    }

    /**
     * Scope to get models that should be published now
     */
    public function scopeShouldBePublished(Builder $query): Builder
    {
        return $query->where('is_draft', false)
            ->where('published_at', '<=', now());
    }

    /**
     * Scope to get active content (published and within exhibition period)
     */
    public function scopeActive(Builder $query): Builder
    {
        $query = $this->scopePublished($query);

        // Check exhibition period if columns exist
        if ($this->getConnection()->getSchemaBuilder()->hasColumn($this->getTable(), 'exhibition_beginning')) {
            $query->where(function ($q) {
                $q->whereNull('exhibition_beginning')
                  ->orWhere('exhibition_beginning', '<=', now());
            });
        }

        if (isset($this->active)) {
            $query->where('active', true);
        }

        return $query;
    }

    /**
     * Boot the trait
     */
    protected static function bootHasPublishing(): void
    {
        // Auto-set published_at when publishing
        static::updating(function ($model) {
            if ($model->isDirty('is_draft') && !$model->is_draft && !$model->published_at) {
                $model->published_at = now();
            }
        });
    }
}
