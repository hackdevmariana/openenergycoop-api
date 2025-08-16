<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use App\Contracts\Cacheable;
use App\Contracts\Publishable;
use App\Contracts\Multilingual;
use App\Traits\HasCaching;
use App\Traits\HasPublishing;
use App\Traits\HasMultilingual;
use App\Traits\HasOrganization;

class PageComponent extends Model implements Cacheable, Publishable, Multilingual
{
    use HasFactory, 
        HasCaching, 
        HasPublishing, 
        HasMultilingual, 
        HasOrganization;

    protected $fillable = [
        'page_id',
        'componentable_type',
        'componentable_id',
        'position',
        'parent_id',
        'language',
        'organization_id',
        'is_draft',
        'version',
        'published_at',
        'preview_token',
        'settings',
        'cache_enabled',
        'visibility_rules',
        'ab_test_group',
    ];

    protected $casts = [
        'is_draft' => 'boolean',
        'cache_enabled' => 'boolean',
        'published_at' => 'datetime',
        'settings' => 'array',
        'visibility_rules' => 'array',
        'position' => 'integer',
    ];

    /**
     * Available component types
     */
    public const COMPONENT_TYPES = [
        'Hero' => 'Hero Banner',
        'TextContent' => 'Contenido de Texto',
        'Banner' => 'Banner Publicitario',
        'Gallery' => 'Galería de Imágenes',
        'ContactForm' => 'Formulario de Contacto',
        'Newsletter' => 'Suscripción Newsletter',
        'Testimonials' => 'Testimonios',
        'FAQ' => 'Preguntas Frecuentes',
        'Team' => 'Equipo',
        'Services' => 'Servicios',
    ];

    /**
     * Relationships
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function componentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PageComponent::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(PageComponent::class, 'parent_id')
            ->orderBy('position');
    }

    /**
     * Scopes
     */
    public function scopeByComponentType(Builder $query, string $type): Builder
    {
        return $query->where('componentable_type', $type);
    }

    public function scopeByPosition(Builder $query, int $position): Builder
    {
        return $query->where('position', $position);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('visibility_rules')
              ->orWhereJsonLength('visibility_rules', 0);
        });
    }

    public function scopeInAbTestGroup(Builder $query, string $group): Builder
    {
        return $query->where('ab_test_group', $group);
    }

    /**
     * Business Logic Methods
     */
    public function getComponentTypeName(): string
    {
        $shortName = class_basename($this->componentable_type);
        return self::COMPONENT_TYPES[$shortName] ?? $this->componentable_type;
    }

    public function isVisible(): bool
    {
        // Check basic visibility
        if (!$this->isPublished()) {
            return false;
        }

        // Check visibility rules
        if (!empty($this->visibility_rules)) {
            return $this->evaluateVisibilityRules();
        }

        return true;
    }

    public function evaluateVisibilityRules(): bool
    {
        if (empty($this->visibility_rules)) {
            return true;
        }

        foreach ($this->visibility_rules as $rule) {
            switch ($rule['type']) {
                case 'auth_required':
                    if (!auth()->check()) {
                        return false;
                    }
                    break;
                    
                case 'role_required':
                    if (!auth()->check() || !auth()->user()->hasRole($rule['value'])) {
                        return false;
                    }
                    break;
                    
                case 'date_range':
                    $now = now();
                    if (isset($rule['start']) && $now->lt($rule['start'])) {
                        return false;
                    }
                    if (isset($rule['end']) && $now->gt($rule['end'])) {
                        return false;
                    }
                    break;
                    
                case 'device_type':
                    // This would need to be implemented based on your device detection logic
                    break;
            }
        }

        return true;
    }

    public function canBePublished(): bool
    {
        // Check if the componentable exists and can be published
        if (!$this->componentable) {
            return false;
        }

        // Check if componentable has its own validation
        if (method_exists($this->componentable, 'canBePublished')) {
            return $this->componentable->canBePublished();
        }

        return true;
    }

    public function duplicate(): self
    {
        $duplicate = $this->replicate();
        $duplicate->position = $this->getNextPosition();
        $duplicate->is_draft = true;
        $duplicate->published_at = null;
        $duplicate->preview_token = null;
        $duplicate->save();

        // Duplicate the componentable if it supports duplication
        if ($this->componentable && method_exists($this->componentable, 'duplicate')) {
            $duplicatedComponentable = $this->componentable->duplicate();
            $duplicate->componentable()->associate($duplicatedComponentable);
            $duplicate->save();
        }

        return $duplicate;
    }

    public function moveToPosition(int $newPosition): void
    {
        $oldPosition = $this->position;
        
        if ($newPosition === $oldPosition) {
            return;
        }

        // Move other components to make space
        if ($newPosition > $oldPosition) {
            // Moving down
            self::where('page_id', $this->page_id)
                ->where('position', '>', $oldPosition)
                ->where('position', '<=', $newPosition)
                ->decrement('position');
        } else {
            // Moving up
            self::where('page_id', $this->page_id)
                ->where('position', '>=', $newPosition)
                ->where('position', '<', $oldPosition)
                ->increment('position');
        }

        $this->update(['position' => $newPosition]);
    }

    public function getNextPosition(): int
    {
        return self::where('page_id', $this->page_id)->max('position') + 1;
    }

    public function generatePreviewUrl(): string
    {
        if (!$this->preview_token) {
            $this->update(['preview_token' => \Str::random(32)]);
        }

        return route('component.preview', [
            'component' => $this->id,
            'token' => $this->preview_token
        ]);
    }

    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->update(['settings' => $settings]);
    }

    /**
     * Boot method
     */
    protected static function boot(): void
    {
        parent::boot();
        
        // Auto-set position if not provided
        static::creating(function ($component) {
            if (is_null($component->position)) {
                $component->position = $component->getNextPosition();
            }
        });
        
        // Clear cache when component changes
        static::saved(function ($component) {
            if ($component->page) {
                $component->page->clearCache();
            }
        });
        
        static::deleted(function ($component) {
            if ($component->page) {
                $component->page->clearCache();
            }
        });
    }
}