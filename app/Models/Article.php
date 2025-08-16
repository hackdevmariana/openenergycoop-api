<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;
use App\Contracts\Cacheable;
use App\Contracts\Publishable;
use App\Contracts\Multilingual;
use App\Traits\HasCaching;
use App\Traits\HasPublishing;
use App\Traits\HasMultilingual;
use App\Traits\HasOrganization;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Article extends Model implements HasMedia, Cacheable, Publishable, Multilingual
{
    use HasFactory, 
        InteractsWithMedia, 
        HasCaching, 
        HasPublishing, 
        HasMultilingual, 
        HasOrganization;

    protected $fillable = [
        'title',
        'subtitle',
        'text',
        'excerpt',
        'featured_image',
        'slug',
        'author_id',
        'published_at',
        'scheduled_at',
        'category_id',
        'comment_enabled',
        'featured',
        'status',
        'reading_time',
        'seo_focus_keyword',
        'related_articles',
        'social_shares_count',
        'number_of_views',
        'organization_id',
        'language',
        'is_draft',
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
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'comment_enabled' => 'boolean',
        'featured' => 'boolean',
        'is_draft' => 'boolean',
        'related_articles' => 'array',
        'search_keywords' => 'array',
        'social_shares_count' => 'integer',
        'number_of_views' => 'integer',
        'reading_time' => 'integer',
        'last_reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Article statuses
     */
    public const STATUSES = [
        'draft' => 'Borrador',
        'review' => 'En Revisión',
        'published' => 'Publicado',
        'archived' => 'Archivado',
    ];

    /**
     * Relationships
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->orderBy('created_at', 'desc');
    }

    public function approvedComments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc');
    }

    public function seoMetaData(): MorphOne
    {
        return $this->morphOne(SeoMetaData::class, 'seoable');
    }

    public function tags(): MorphMany
    {
        return $this->morphMany(Taggable::class, 'taggable');
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
     * Scopes
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where('is_draft', false)
            ->where(function ($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByAuthor(Builder $query, int $authorId): Builder
    {
        return $query->where('author_id', $authorId);
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>', now());
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->orderBy('number_of_views', 'desc');
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('published_at', 'desc');
    }

    /**
     * Media Collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_images')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('article_images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('article_files')
            ->acceptsMimeTypes(['application/pdf', 'application/msword', 'text/plain']);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(200)
            ->sharpen(10)
            ->performOnCollections('featured_images', 'article_images');

        $this->addMediaConversion('large')
            ->width(1200)
            ->height(800)
            ->quality(85)
            ->performOnCollections('featured_images', 'article_images');
    }

    /**
     * Business Logic Methods
     */
    public function canBePublished(): bool
    {
        if (empty($this->title) || empty($this->text) || empty($this->slug)) {
            return false;
        }

        if ($this->status === 'archived') {
            return false;
        }

        return true;
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && 
               !$this->is_draft && 
               ($this->published_at === null || $this->published_at->isPast());
    }

    public function isScheduled(): bool
    {
        return $this->scheduled_at && $this->scheduled_at->isFuture();
    }

    public function incrementViews(): void
    {
        $this->increment('number_of_views');
    }

    public function incrementShares(): void
    {
        $this->increment('social_shares_count');
    }

    public function getWordCount(): int
    {
        return str_word_count(strip_tags($this->text));
    }

    public function calculateReadingTime(): int
    {
        $wordCount = $this->getWordCount();
        // Average reading speed: 200 words per minute
        return max(1, ceil($wordCount / 200));
    }

    public function getExcerpt(int $length = 160): string
    {
        if ($this->excerpt) {
            return $this->excerpt;
        }

        return \Str::limit(strip_tags($this->text), $length);
    }

    public function getFeaturedImageUrl(?string $conversion = null): ?string
    {
        if ($this->hasMedia('featured_images')) {
            $media = $this->getFirstMedia('featured_images');
            return $conversion ? $media->getUrl($conversion) : $media->getUrl();
        }
        
        return $this->featured_image;
    }

    public function getUrl(): string
    {
        return route('articles.show', ['article' => $this->slug]);
    }

    public function getRelatedArticles(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        // First try to get manually related articles
        if ($this->related_articles && is_array($this->related_articles)) {
            $related = self::whereIn('id', $this->related_articles)
                ->published()
                ->inLanguage($this->language)
                ->limit($limit)
                ->get();
            
            if ($related->count() >= $limit) {
                return $related;
            }
        }

        // Then get articles from same category
        $categoryRelated = self::where('category_id', $this->category_id)
            ->where('id', '!=', $this->id)
            ->published()
            ->inLanguage($this->language)
            ->recent()
            ->limit($limit)
            ->get();

        return $categoryRelated;
    }

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'review' => 'yellow',
            'published' => 'green',
            'archived' => 'red',
            default => 'gray',
        };
    }

    public function archive(): void
    {
        $this->update([
            'status' => 'archived',
            'is_draft' => true,
        ]);
    }

    public function unarchive(): void
    {
        $this->update([
            'status' => 'draft',
        ]);
    }

    public function duplicate(): self
    {
        $duplicate = $this->replicate();
        $duplicate->title = $this->title . ' (Copia)';
        $duplicate->slug = $this->slug . '-copia-' . time();
        $duplicate->status = 'draft';
        $duplicate->is_draft = true;
        $duplicate->published_at = null;
        $duplicate->scheduled_at = null;
        $duplicate->number_of_views = 0;
        $duplicate->social_shares_count = 0;
        $duplicate->save();

        // Duplicate media
        foreach ($this->media as $media) {
            $media->copy($duplicate, $media->collection_name);
        }

        return $duplicate;
    }

    /**
     * Boot method
     */
    protected static function boot(): void
    {
        parent::boot();
        
        // Auto-generate slug if not provided
        static::creating(function ($article) {
            if (empty($article->slug) && !empty($article->title)) {
                $article->slug = \Str::slug($article->title);
            }
            
            // Auto-calculate reading time
            if (is_null($article->reading_time) && !empty($article->text)) {
                $article->reading_time = $article->calculateReadingTime();
            }
        });
        
        // Update reading time when text changes
        static::updating(function ($article) {
            if ($article->isDirty('text')) {
                $article->reading_time = $article->calculateReadingTime();
            }
        });
    }
}