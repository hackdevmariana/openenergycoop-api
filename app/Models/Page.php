<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
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

class Page extends Model implements HasMedia, Cacheable, Publishable, Multilingual
{
    use HasFactory, 
        InteractsWithMedia, 
        HasCaching, 
        HasPublishing, 
        HasMultilingual, 
        HasOrganization;

    protected $fillable = [
        'title',
        'slug',
        'route',
        'language',
        'organization_id',
        'is_draft',
        'template',
        'meta_data',
        'cache_duration',
        'requires_auth',
        'allowed_roles',
        'parent_id',
        'sort_order',
        'published_at',
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
        'meta_data' => 'array',
        'allowed_roles' => 'array',
        'search_keywords' => 'array',
        'requires_auth' => 'boolean',
        'is_draft' => 'boolean',
        'published_at' => 'datetime',
        'last_reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'cache_duration' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Available page templates
     */
    public const TEMPLATES = [
        'default' => 'Plantilla Por Defecto',
        'landing' => 'Página de Aterrizaje',
        'article_list' => 'Lista de Artículos',
        'contact' => 'Página de Contacto',
        'about' => 'Página Sobre Nosotros',
        'services' => 'Página de Servicios',
        'gallery' => 'Galería de Imágenes',
    ];

    /**
     * Relationships
     */
    public function components(): HasMany
    {
        return $this->hasMany(PageComponent::class)
            ->orderBy('position');
    }

    public function publishedComponents(): HasMany
    {
        return $this->hasMany(PageComponent::class)
            ->where('is_draft', false)
            ->orderBy('position');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id')
            ->orderBy('sort_order');
    }

    public function publishedChildren(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id')
            ->where('is_draft', false)
            ->orderBy('sort_order');
    }

    public function seoMetaData(): MorphOne
    {
        return $this->morphOne(SeoMetaData::class, 'seoable');
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
    public function scopeByTemplate(Builder $query, string $template): Builder
    {
        return $query->where('template', $template);
    }

    public function scopeRequiresAuth(Builder $query, bool $requiresAuth = true): Builder
    {
        return $query->where('requires_auth', $requiresAuth);
    }

    public function scopeByParent(Builder $query, ?int $parentId): Builder
    {
        return $query->where('parent_id', $parentId);
    }

    public function scopeRootPages(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    /**
     * Media Collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_images')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    /**
     * Business Logic Methods
     */
    public function getFullSlug(): string
    {
        if ($this->parent) {
            return $this->parent->getFullSlug() . '/' . $this->slug;
        }
        
        return $this->slug;
    }

    public function getBreadcrumb(): array
    {
        $breadcrumb = [];
        
        if ($this->parent) {
            $breadcrumb = $this->parent->getBreadcrumb();
        }
        
        $breadcrumb[] = [
            'title' => $this->title,
            'slug' => $this->slug,
            'url' => $this->getUrl(),
        ];
        
        return $breadcrumb;
    }

    public function getUrl(): string
    {
        if ($this->route) {
            // The route field stores the URL path, not a route name
            return $this->route;
        }
        
        return '/' . $this->getFullSlug();
    }

    public function canBePublished(): bool
    {
        // Override parent method with page-specific validation
        if (empty($this->title)) {
            return false;
        }

        if (empty($this->slug)) {
            return false;
        }

        // Check if page has at least one component
        if ($this->components()->count() === 0) {
            return false;
        }

        return true;
    }

    public function hasPublishedContent(): bool
    {
        return $this->publishedComponents()->exists();
    }

    public function getTemplateLabel(): string
    {
        return self::TEMPLATES[$this->template] ?? $this->template;
    }

    public function isHomePage(): bool
    {
        return $this->slug === 'home' || $this->route === 'home';
    }

    public function getEstimatedReadingTime(): ?int
    {
        $totalWords = 0;
        
        foreach ($this->publishedComponents as $component) {
            if ($component->componentable && method_exists($component->componentable, 'getWordCount')) {
                $totalWords += $component->componentable->getWordCount();
            }
        }
        
        // Average reading speed: 200 words per minute
        return $totalWords > 0 ? max(1, ceil($totalWords / 200)) : null;
    }

    /**
     * Generate page preview URL with token
     */
    public function generatePreviewUrl(): string
    {
        $token = \Str::random(32);
        
        // Store token temporarily (you might want to use cache or database)
        cache()->put("page_preview_{$this->id}", $token, now()->addHours(24));
        
        return route('page.preview', ['page' => $this->id, 'token' => $token]);
    }

    /**
     * Boot method
     */
    protected static function boot(): void
    {
        parent::boot();
        
        // Auto-generate slug if not provided
        static::creating(function ($page) {
            if (empty($page->slug) && !empty($page->title)) {
                $page->slug = \Str::slug($page->title);
            }
        });
        
        // Update cache duration based on template
        static::saving(function ($page) {
            if ($page->template === 'landing') {
                $page->cache_duration = 30; // Landing pages cache for 30 minutes
            } elseif ($page->template === 'article_list') {
                $page->cache_duration = 15; // Article lists cache for 15 minutes
            }
        });
    }
}