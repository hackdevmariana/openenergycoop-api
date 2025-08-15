<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use App\Contracts\Cacheable;
use App\Contracts\Multilingual;
use App\Traits\HasCaching;
use App\Traits\HasMultilingual;
use App\Traits\HasOrganization;

class Category extends Model implements Cacheable, Multilingual
{
    use HasFactory, 
        HasCaching, 
        HasMultilingual, 
        HasOrganization;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'parent_id',
        'sort_order',
        'is_active',
        'category_type',
        'organization_id',
        'language',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Category types
     */
    public const CATEGORY_TYPES = [
        'article' => 'Artículos',
        'document' => 'Documentos',
        'media' => 'Medios',
        'faq' => 'Preguntas Frecuentes',
        'event' => 'Eventos',
        'product' => 'Productos',
        'service' => 'Servicios',
    ];

    /**
     * Relationships
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function activeChildren(): HasMany
    {
        return $this->children()->where('is_active', true);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function publishedArticles(): HasMany
    {
        return $this->articles()
            ->where('status', 'published')
            ->where('is_draft', false);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Scopes
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('category_type', $type);
    }

    public function scopeRootCategories(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeWithCounts(Builder $query): Builder
    {
        return $query->withCount([
            'articles',
            'publishedArticles',
            'documents',
            'children',
        ]);
    }

    /**
     * Business Logic Methods
     */
    public function getFullName(): string
    {
        if ($this->parent) {
            return $this->parent->getFullName() . ' > ' . $this->name;
        }
        
        return $this->name;
    }

    public function getBreadcrumb(): array
    {
        $breadcrumb = [];
        
        if ($this->parent) {
            $breadcrumb = $this->parent->getBreadcrumb();
        }
        
        $breadcrumb[] = [
            'name' => $this->name,
            'slug' => $this->slug,
            'url' => $this->getUrl(),
        ];
        
        return $breadcrumb;
    }

    public function getUrl(): string
    {
        return route('categories.show', ['category' => $this->slug]);
    }

    public function getTypeLabel(): string
    {
        return self::CATEGORY_TYPES[$this->category_type] ?? $this->category_type;
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    public function hasActiveChildren(): bool
    {
        return $this->activeChildren()->exists();
    }

    public function getDepth(): int
    {
        $depth = 0;
        $parent = $this->parent;
        
        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }
        
        return $depth;
    }

    public function getAllChildren(): \Illuminate\Database\Eloquent\Collection
    {
        $children = collect();
        
        foreach ($this->children as $child) {
            $children->push($child);
            $children = $children->merge($child->getAllChildren());
        }
        
        return $children;
    }

    public function getAllParents(): \Illuminate\Database\Eloquent\Collection
    {
        $parents = collect();
        $parent = $this->parent;
        
        while ($parent) {
            $parents->push($parent);
            $parent = $parent->parent;
        }
        
        return $parents->reverse();
    }

    public function isAncestorOf(Category $category): bool
    {
        return $category->getAllParents()->contains('id', $this->id);
    }

    public function isDescendantOf(Category $category): bool
    {
        return $this->getAllParents()->contains('id', $category->id);
    }

    public function getNextSortOrder(): int
    {
        return self::where('parent_id', $this->parent_id)
            ->where('category_type', $this->category_type)
            ->max('sort_order') + 1;
    }

    public function moveToPosition(int $newPosition): void
    {
        $oldPosition = $this->sort_order;
        
        if ($newPosition === $oldPosition) {
            return;
        }

        if ($newPosition > $oldPosition) {
            // Moving down
            self::where('parent_id', $this->parent_id)
                ->where('category_type', $this->category_type)
                ->where('sort_order', '>', $oldPosition)
                ->where('sort_order', '<=', $newPosition)
                ->decrement('sort_order');
        } else {
            // Moving up
            self::where('parent_id', $this->parent_id)
                ->where('category_type', $this->category_type)
                ->where('sort_order', '>=', $newPosition)
                ->where('sort_order', '<', $oldPosition)
                ->increment('sort_order');
        }

        $this->update(['sort_order' => $newPosition]);
    }

    public function getIconHtml(): string
    {
        if (!$this->icon) {
            return '';
        }

        // Support for different icon formats
        if (str_starts_with($this->icon, 'heroicon-')) {
            return "<x-{$this->icon} class=\"w-5 h-5\" />";
        }
        
        if (str_starts_with($this->icon, 'fa-')) {
            return "<i class=\"fas {$this->icon}\"></i>";
        }
        
        return "<i class=\"{$this->icon}\"></i>";
    }

    public function getColorStyle(): string
    {
        if (!$this->color) {
            return '';
        }
        
        return "background-color: {$this->color};";
    }

    /**
     * Boot method
     */
    protected static function boot(): void
    {
        parent::boot();
        
        // Auto-generate slug if not provided
        static::creating(function ($category) {
            if (empty($category->slug) && !empty($category->name)) {
                $category->slug = \Str::slug($category->name);
            }
            
            // Auto-set sort order
            if (is_null($category->sort_order)) {
                $category->sort_order = $category->getNextSortOrder();
            }
        });
        
        // Prevent circular references
        static::saving(function ($category) {
            if ($category->parent_id && $category->parent_id === $category->id) {
                throw new \InvalidArgumentException('A category cannot be its own parent');
            }
            
            if ($category->parent_id && $category->exists) {
                $parent = Category::find($category->parent_id);
                if ($parent && $parent->isDescendantOf($category)) {
                    throw new \InvalidArgumentException('Circular reference detected');
                }
            }
        });
    }
}