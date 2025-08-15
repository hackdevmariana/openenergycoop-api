<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Builder;

/**
 * Interface for models that support publishing/draft functionality
 */
interface Publishable
{
    /**
     * Publish the model
     */
    public function publish(): void;

    /**
     * Unpublish the model (set as draft)
     */
    public function unpublish(): void;

    /**
     * Check if the model is published
     */
    public function isPublished(): bool;

    /**
     * Check if the model is in draft state
     */
    public function isDraft(): bool;

    /**
     * Check if the model can be published (validation)
     */
    public function canBePublished(): bool;

    /**
     * Schedule publication for a specific date
     */
    public function schedulePublication(\DateTime $date): void;

    /**
     * Scope to get only published models
     */
    public function scopePublished(Builder $query): Builder;

    /**
     * Scope to get only draft models
     */
    public function scopeDraft(Builder $query): Builder;

    /**
     * Scope to get models that should be published now
     */
    public function scopeShouldBePublished(Builder $query): Builder;
}
