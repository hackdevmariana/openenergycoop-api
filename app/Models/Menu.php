<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use App\Contracts\Cacheable;
use App\Contracts\Publishable;
use App\Contracts\Multilingual;
use App\Traits\HasCaching;
use App\Traits\HasPublishing;
use App\Traits\HasMultilingual;
use App\Traits\HasOrganization;

class Menu extends Model implements Cacheable, Publishable, Multilingual
{
    use HasFactory, 
        HasCaching, 
        HasPublishing, 
        HasMultilingual, 
        HasOrganization;

    protected $fillable = [
        'icon',
        'text',
        'internal_link',
        'external_link',
        'target_blank',
        'parent_id',
        'order',
        'permission',
        'menu_group',
        'css_classes',
        'visibility_rules',
        'badge_text',
        'badge_color',
        'language',
        'organization_id',
        'is_draft',
        'is_active',
        'published_at',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'target_blank' => 'boolean',
        'is_draft' => 'boolean',
        'is_active' => 'boolean',
        'published_at' => 'datetime',
        'visibility_rules' => 'array',
        'order' => 'integer',
    ];

    /**
     * Menu groups
     */
    public const MENU_GROUPS = [
        'header' => 'Cabecera',
        'footer' => 'Pie de Página',
        'sidebar' => 'Barra Lateral',
        'mobile' => 'Menú Móvil',
    ];

    /**
     * Relationships
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')
            ->orderBy('order');
    }

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
    public function scopeByGroup(Builder $query, string $group): Builder
    {
        return $query->where('menu_group', $group);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRootItems(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('text');
    }

    /**
     * Business Logic Methods
     */
    public function getUrl(): string
    {
        if ($this->external_link) {
            return $this->external_link;
        }

        if ($this->internal_link) {
            // Handle different types of internal links
            if (str_starts_with($this->internal_link, 'route:')) {
                $route = substr($this->internal_link, 6);
                return route($route);
            }
            
            return $this->internal_link;
        }

        return '#';
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    public function isExternal(): bool
    {
        return !empty($this->external_link);
    }

    public function canBePublished(): bool
    {
        return !empty($this->text) && (!empty($this->internal_link) || !empty($this->external_link));
    }

    /**
     * Boot method
     */
    protected static function boot(): void
    {
        parent::boot();
        
        // Auto-set order if not provided
        static::creating(function ($menu) {
            if (is_null($menu->order)) {
                $maxOrder = self::where('menu_group', $menu->menu_group)
                    ->where('parent_id', $menu->parent_id)
                    ->max('order');
                $menu->order = ($maxOrder ?? 0) + 1;
            }
        });
    }
}