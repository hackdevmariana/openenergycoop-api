<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasOrganization;

class Banner extends Model
{
    use HasFactory, HasOrganization;

    protected $fillable = [
        'image',
        'mobile_image',
        'internal_link',
        'url',
        'position',
        'active',
        'alt_text',
        'title',
        'description',
        'exhibition_beginning',
        'exhibition_end',
        'banner_type',
        'display_rules',
        'click_count',
        'impression_count',
        'organization_id',
        'is_draft',
        'published_at',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'active' => 'boolean',
        'is_draft' => 'boolean',
        'exhibition_beginning' => 'datetime',
        'exhibition_end' => 'datetime',
        'published_at' => 'datetime',
        'display_rules' => 'array',
        'position' => 'integer',
        'click_count' => 'integer',
        'impression_count' => 'integer',
    ];

    /**
     * Banner types
     */
    public const BANNER_TYPES = [
        'header' => 'Header',
        'sidebar' => 'Sidebar',
        'footer' => 'Footer',
        'popup' => 'Popup',
        'inline' => 'Inline',
    ];

    /**
     * Relationships
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
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
        return $query->where('active', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('banner_type', $type);
    }

    public function scopeByPosition(Builder $query, string $position): Builder
    {
        return $query->where('position', $position);
    }

    public function scopeCurrentlyDisplaying(Builder $query): Builder
    {
        $now = now();
        return $query->where(function ($q) use ($now) {
            $q->whereNull('exhibition_beginning')
              ->orWhere('exhibition_beginning', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('exhibition_end')
              ->orWhere('exhibition_end', '>=', $now);
        });
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

    public function isCurrentlyDisplaying(): bool
    {
        $now = now();
        
        $startOk = is_null($this->exhibition_beginning) || $this->exhibition_beginning <= $now;
        $endOk = is_null($this->exhibition_end) || $this->exhibition_end >= $now;
        
        return $startOk && $endOk;
    }

    public function incrementClicks(): void
    {
        $this->increment('click_count');
    }

    public function incrementImpressions(): void
    {
        $this->increment('impression_count');
    }

    public function getClickThroughRate(): float
    {
        if (!$this->impression_count || $this->impression_count === 0) {
            return 0.0;
        }
        
        return round(($this->click_count / $this->impression_count) * 100, 2);
    }

    public function getTypeLabel(): string
    {
        return self::BANNER_TYPES[$this->banner_type] ?? $this->banner_type;
    }

    /**
     * Boot method
     */
    protected static function boot(): void
    {
        parent::boot();
        
        static::creating(function ($banner) {
            if (is_null($banner->published_at) && !$banner->is_draft) {
                $banner->published_at = now();
            }
        });
    }
}
