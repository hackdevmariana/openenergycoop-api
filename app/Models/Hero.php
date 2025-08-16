<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

class Hero extends Model implements HasMedia, Cacheable, Publishable, Multilingual
{
    use HasFactory, 
        InteractsWithMedia, 
        HasCaching, 
        HasPublishing, 
        HasMultilingual, 
        HasOrganization;

    protected $fillable = [
        'image',
        'mobile_image',
        'text',
        'subtext',
        'text_button',
        'internal_link',
        'cta_link_external',
        'position',
        'exhibition_beginning',
        'exhibition_end',
        'active',
        'video_url',
        'video_background',
        'text_align',
        'overlay_opacity',
        'animation_type',
        'cta_style',
        'priority',
        'language',
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
        'position' => 'integer',
        'overlay_opacity' => 'integer',
        'priority' => 'integer',
    ];

    /**
     * Text alignment options
     */
    public const TEXT_ALIGNMENTS = [
        'left' => 'Izquierda',
        'center' => 'Centro',
        'right' => 'Derecha',
    ];

    /**
     * CTA button styles
     */
    public const CTA_STYLES = [
        'primary' => 'Primario',
        'secondary' => 'Secundario',
        'outline' => 'Contorno',
        'ghost' => 'Fantasma',
        'link' => 'Enlace',
    ];

    /**
     * Animation types
     */
    public const ANIMATION_TYPES = [
        'fade' => 'Desvanecimiento',
        'slide_left' => 'Deslizar Izquierda',
        'slide_right' => 'Deslizar Derecha',
        'slide_up' => 'Deslizar Arriba',
        'slide_down' => 'Deslizar Abajo',
        'zoom' => 'Zoom',
        'bounce' => 'Rebote',
        'none' => 'Sin Animación',
    ];

    /**
     * Relationships
     */
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

    /**
     * Scopes
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeInExhibitionPeriod(Builder $query): Builder
    {
        $now = now();
        
        return $query->where(function ($q) use ($now) {
            $q->where(function ($subQ) use ($now) {
                $subQ->whereNull('exhibition_beginning')
                     ->orWhere('exhibition_beginning', '<=', $now);
            })->where(function ($subQ) use ($now) {
                $subQ->whereNull('exhibition_end')
                     ->orWhere('exhibition_end', '>=', $now);
            });
        });
    }

    public function scopeByPriority(Builder $query): Builder
    {
        return $query->orderBy('priority', 'desc')
            ->orderBy('position')
            ->orderBy('created_at', 'desc');
    }

    public function scopeForSlideshow(Builder $query): Builder
    {
        return $query->active()
            ->published()
            ->inExhibitionPeriod()
            ->byPriority();
    }

    /**
     * Media Collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('hero_images')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('mobile_images')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('background_videos')
            ->singleFile()
            ->acceptsMimeTypes(['video/mp4', 'video/webm']);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(200)
            ->sharpen(10)
            ->performOnCollections('hero_images', 'mobile_images');

        $this->addMediaConversion('optimized')
            ->width(1920)
            ->height(1080)
            ->quality(85)
            ->performOnCollections('hero_images');

        $this->addMediaConversion('mobile_optimized')
            ->width(768)
            ->height(512)
            ->quality(85)
            ->performOnCollections('mobile_images');
    }

    /**
     * Business Logic Methods
     */
    public function isInExhibitionPeriod(): bool
    {
        $now = now();
        
        if ($this->exhibition_beginning && $now->lt($this->exhibition_beginning)) {
            return false;
        }
        
        if ($this->exhibition_end && $now->gt($this->exhibition_end)) {
            return false;
        }
        
        return true;
    }

    public function canBePublished(): bool
    {
        // Override parent method with hero-specific validation
        if (empty($this->text) && empty($this->image) && empty($this->video_url)) {
            return false;
        }

        return true;
    }

    public function getDisplayText(): string
    {
        return $this->text ?: '';
    }

    public function getDisplaySubtext(): string
    {
        return $this->subtext ?: '';
    }

    public function getCtaUrl(): ?string
    {
        if ($this->cta_link_external) {
            return $this->cta_link_external;
        }
        
        if ($this->internal_link) {
            // Handle different types of internal links
            if (str_starts_with($this->internal_link, 'route:')) {
                $route = substr($this->internal_link, 6);
                return route($route);
            }
            
            return $this->internal_link;
        }
        
        return null;
    }

    public function hasVideo(): bool
    {
        return !empty($this->video_url) || !empty($this->video_background);
    }

    public function getVideoUrl(): ?string
    {
        return $this->video_url ?: $this->video_background;
    }

    public function getImageUrl(?string $conversion = null): ?string
    {
        if ($this->hasMedia('hero_images')) {
            $media = $this->getFirstMedia('hero_images');
            return $conversion ? $media->getUrl($conversion) : $media->getUrl();
        }
        
        return $this->image;
    }

    public function getMobileImageUrl(?string $conversion = null): ?string
    {
        if ($this->hasMedia('mobile_images')) {
            $media = $this->getFirstMedia('mobile_images');
            return $conversion ? $media->getUrl($conversion) : $media->getUrl();
        }
        
        return $this->mobile_image ?: $this->getImageUrl($conversion);
    }

    public function getTextAlignmentClass(): string
    {
        return match($this->text_align) {
            'left' => 'text-left',
            'right' => 'text-right',
            default => 'text-center',
        };
    }

    public function getCtaStyleClass(): string
    {
        return match($this->cta_style) {
            'secondary' => 'btn-secondary',
            'outline' => 'btn-outline',
            'ghost' => 'btn-ghost',
            'link' => 'btn-link',
            default => 'btn-primary',
        };
    }

    public function getAnimationClass(): string
    {
        if (!$this->animation_type || $this->animation_type === 'none') {
            return '';
        }
        
        return 'animate-' . str_replace('_', '-', $this->animation_type);
    }

    public function getOverlayStyle(): string
    {
        if ($this->overlay_opacity <= 0) {
            return '';
        }
        
        $opacity = $this->overlay_opacity / 100;
        return "background: rgba(0, 0, 0, {$opacity});";
    }

    public function duplicate(): self
    {
        $duplicate = $this->replicate();
        $duplicate->position = $this->getNextPosition();
        $duplicate->is_draft = true;
        $duplicate->published_at = null;
        $duplicate->active = false;
        $duplicate->save();

        // Duplicate media
        foreach ($this->media as $media) {
            $media->copy($duplicate, $media->collection_name);
        }

        return $duplicate;
    }

    public function getNextPosition(): int
    {
        $query = self::query();
        
        if ($this->organization_id) {
            $query->where('organization_id', $this->organization_id);
        } else {
            $query->whereNull('organization_id');
        }
        
        if ($this->language) {
            $query->where('language', $this->language);
        }
        
        return ($query->max('position') ?? 0) + 1;
    }

    public function getWordCount(): int
    {
        $text = strip_tags($this->text . ' ' . $this->subtext . ' ' . $this->text_button);
        return str_word_count($text);
    }

    /**
     * Boot method
     */
    protected static function boot(): void
    {
        parent::boot();
        
        // Auto-set position if not provided
        static::creating(function ($hero) {
            if (is_null($hero->position) || $hero->position === 0) {
                $hero->position = $hero->getNextPosition();
            }
        });
    }
}