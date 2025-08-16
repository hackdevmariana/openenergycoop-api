<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasOrganization;

class Collaborator extends Model
{
    use HasFactory, HasOrganization;

    protected $fillable = [
        'name',
        'logo',
        'url',
        'description',
        'order',
        'is_active',
        'collaborator_type',
        'organization_id',
        'is_draft',
        'published_at',
        'created_by_user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_draft' => 'boolean',
        'published_at' => 'datetime',
        'order' => 'integer',
    ];

    /**
     * Collaborator types
     */
    public const COLLABORATOR_TYPES = [
        'partner' => 'Partner',
        'sponsor' => 'Sponsor', 
        'member' => 'Member',
        'supporter' => 'Supporter',
    ];

    /**
     * Relationships
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Scopes
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_draft', false)
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('collaborator_type', $type);
    }

    public function scopeOrderedByPriority(Builder $query): Builder
    {
        return $query->orderBy('order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * Business Logic Methods
     */
    public function isPublished(): bool
    {
        return !$this->is_draft && 
               $this->published_at !== null && 
               $this->published_at <= now();
    }

    public function getTypeLabel(): string
    {
        return self::COLLABORATOR_TYPES[$this->collaborator_type] ?? $this->collaborator_type;
    }

    /**
     * Boot method
     */
    protected static function boot(): void
    {
        parent::boot();
        
        static::creating(function ($collaborator) {
            if (is_null($collaborator->published_at) && !$collaborator->is_draft) {
                $collaborator->published_at = now();
            }
        });
    }
}
