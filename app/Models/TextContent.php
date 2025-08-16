<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;
use App\Contracts\Cacheable;
use App\Contracts\Publishable;
use App\Contracts\Multilingual;
use App\Traits\HasCaching;
use App\Traits\HasPublishing;
use App\Traits\HasMultilingual;
use App\Traits\HasOrganization;

class TextContent extends Model implements Cacheable, Publishable, Multilingual
{
    use HasFactory, 
        HasCaching, 
        HasPublishing, 
        HasMultilingual, 
        HasOrganization;

    protected $fillable = [
        'slug',
        'title',
        'subtitle',
        'text',
        'version',
        'language',
        'organization_id',
        'is_draft',
        'published_at',
        'author_id',
        'parent_id',
        'excerpt',
        'reading_time',
        'seo_focus_keyword',
        'number_of_views',
        'search_keywords',
        'internal_notes',
        'last_reviewed_at',
        'accessibility_notes',
        'reading_level',
        'created_by_user_id',
        'updated_by_user_id',
        'approved_by_user_id',
        'approved_at',
    ];

    protected $casts = [
        'is_draft' => 'boolean',
        'published_at' => 'datetime',
        'last_reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'search_keywords' => 'array',
        'reading_time' => 'integer',
        'number_of_views' => 'integer',
    ];

    /**
     * Relationships
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(TextContent::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(TextContent::class, 'parent_id');
    }

    public function pageComponents(): MorphMany
    {
        return $this->morphMany(PageComponent::class, 'componentable');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    /**
     * Business Logic Methods
     */
    public function getWordCount(): int
    {
        return str_word_count(strip_tags($this->text));
    }

    public function canBePublished(): bool
    {
        return !empty($this->text) && !empty($this->slug);
    }

    public function incrementViews(): void
    {
        $this->increment('number_of_views');
    }

    /**
     * Boot method
     */
    protected static function boot(): void
    {
        parent::boot();
        
        // Auto-generate slug if not provided
        static::creating(function ($content) {
            if (empty($content->slug) && !empty($content->title)) {
                $content->slug = \Str::slug($content->title);
            }
        });
    }
}