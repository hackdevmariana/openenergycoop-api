<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasMultilingual;

class SeoMetaData extends Model
{
    use HasFactory, HasMultilingual;

    protected $fillable = [
        'seoable_type',
        'seoable_id',
        'meta_title',
        'meta_description',
        'canonical_url',
        'robots',
        'og_title',
        'og_description',
        'og_image_path',
        'og_type',
        'twitter_title',
        'twitter_description',
        'twitter_image_path',
        'twitter_card',
        'structured_data',
        'focus_keyword',
        'additional_meta',
        'language',
    ];

    protected $casts = [
        'structured_data' => 'array',
        'additional_meta' => 'array',
    ];

    /**
     * Default robots values
     */
    public const ROBOTS_OPTIONS = [
        'index,follow' => 'Indexar y Seguir Enlaces',
        'index,nofollow' => 'Indexar, No Seguir Enlaces',
        'noindex,follow' => 'No Indexar, Seguir Enlaces',
        'noindex,nofollow' => 'No Indexar, No Seguir Enlaces',
    ];

    /**
     * Twitter card types
     */
    public const TWITTER_CARD_TYPES = [
        'summary' => 'Resumen',
        'summary_large_image' => 'Resumen con Imagen Grande',
        'app' => 'Aplicación',
        'player' => 'Reproductor',
    ];

    /**
     * Open Graph types
     */
    public const OG_TYPES = [
        'website' => 'Sitio Web',
        'article' => 'Artículo',
        'book' => 'Libro',
        'profile' => 'Perfil',
        'music.song' => 'Canción',
        'music.album' => 'Álbum',
        'video.movie' => 'Película',
        'video.episode' => 'Episodio',
    ];

    /**
     * Relationships
     */
    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scopes
     */
    public function scopeByKeyword(Builder $query, string $keyword): Builder
    {
        return $query->where('focus_keyword', 'like', "%{$keyword}%");
    }

    public function scopeByRobots(Builder $query, string $robots): Builder
    {
        return $query->where('robots', $robots);
    }

    /**
     * Business Logic Methods
     */
    public function getEffectiveTitle(): string
    {
        if ($this->meta_title) {
            return $this->meta_title;
        }

        // Try to get title from the seoable model
        if ($this->seoable && isset($this->seoable->title)) {
            return $this->seoable->title;
        }

        return config('app.name', 'OpenEnergyCoop');
    }

    public function getEffectiveDescription(): string
    {
        if ($this->meta_description) {
            return $this->meta_description;
        }

        // Try to get description from the seoable model
        if ($this->seoable) {
            if (isset($this->seoable->excerpt)) {
                return $this->seoable->excerpt;
            }
            
            if (isset($this->seoable->description)) {
                return $this->seoable->description;
            }
            
            if (isset($this->seoable->text)) {
                return \Str::limit(strip_tags($this->seoable->text), 160);
            }
        }

        return 'Cooperativa de energía renovable - ' . config('app.name');
    }

    public function getEffectiveImage(): ?string
    {
        if ($this->og_image_path) {
            return $this->og_image_path;
        }

        // Try to get image from the seoable model
        if ($this->seoable) {
            if (isset($this->seoable->featured_image)) {
                return $this->seoable->featured_image;
            }
            
            if (isset($this->seoable->image)) {
                return $this->seoable->image;
            }
            
            // Try to get from media library
            if (method_exists($this->seoable, 'getFirstMediaUrl')) {
                $imageUrl = $this->seoable->getFirstMediaUrl();
                if ($imageUrl) {
                    return $imageUrl;
                }
            }
        }

        return null;
    }

    public function getCanonicalUrl(): string
    {
        if ($this->canonical_url) {
            return $this->canonical_url;
        }

        // Generate canonical URL from seoable model
        if ($this->seoable && method_exists($this->seoable, 'getUrl')) {
            return url($this->seoable->getUrl());
        }

        return url()->current();
    }

    public function generateStructuredData(): array
    {
        if ($this->structured_data) {
            return $this->structured_data;
        }

        $data = [
            '@context' => 'https://schema.org',
            '@type' => $this->getSchemaType(),
            'name' => $this->getEffectiveTitle(),
            'description' => $this->getEffectiveDescription(),
            'url' => $this->getCanonicalUrl(),
        ];

        $image = $this->getEffectiveImage();
        if ($image) {
            $data['image'] = $image;
        }

        // Add organization data
        $data['publisher'] = [
            '@type' => 'Organization',
            'name' => config('app.name'),
            'url' => config('app.url'),
        ];

        // Add specific data based on seoable type
        if ($this->seoable) {
            $data = $this->addTypeSpecificData($data);
        }

        return $data;
    }

    protected function getSchemaType(): string
    {
        if ($this->seoable_type === 'App\\Models\\Article') {
            return 'Article';
        }
        
        if ($this->seoable_type === 'App\\Models\\Page') {
            return 'WebPage';
        }
        
        return 'Thing';
    }

    protected function addTypeSpecificData(array $data): array
    {
        switch ($this->seoable_type) {
            case 'App\\Models\\Article':
                if (isset($this->seoable->author)) {
                    $data['author'] = [
                        '@type' => 'Person',
                        'name' => $this->seoable->author->name,
                    ];
                }
                
                if (isset($this->seoable->published_at)) {
                    $data['datePublished'] = $this->seoable->published_at->toISOString();
                }
                
                if (isset($this->seoable->updated_at)) {
                    $data['dateModified'] = $this->seoable->updated_at->toISOString();
                }
                break;
        }

        return $data;
    }

    public function getMetaTags(): array
    {
        $tags = [
            'title' => $this->getEffectiveTitle(),
            'meta' => [
                ['name' => 'description', 'content' => $this->getEffectiveDescription()],
                ['name' => 'robots', 'content' => $this->robots],
            ],
            'og' => [
                ['property' => 'og:title', 'content' => $this->og_title ?: $this->getEffectiveTitle()],
                ['property' => 'og:description', 'content' => $this->og_description ?: $this->getEffectiveDescription()],
                ['property' => 'og:type', 'content' => $this->og_type],
                ['property' => 'og:url', 'content' => $this->getCanonicalUrl()],
            ],
            'twitter' => [
                ['name' => 'twitter:card', 'content' => $this->twitter_card],
                ['name' => 'twitter:title', 'content' => $this->twitter_title ?: $this->getEffectiveTitle()],
                ['name' => 'twitter:description', 'content' => $this->twitter_description ?: $this->getEffectiveDescription()],
            ],
            'link' => [
                ['rel' => 'canonical', 'href' => $this->getCanonicalUrl()],
            ],
        ];

        // Add images
        $ogImage = $this->og_image_path ?: $this->getEffectiveImage();
        if ($ogImage) {
            $tags['og'][] = ['property' => 'og:image', 'content' => $ogImage];
        }

        $twitterImage = $this->twitter_image_path ?: $ogImage;
        if ($twitterImage) {
            $tags['twitter'][] = ['name' => 'twitter:image', 'content' => $twitterImage];
        }

        // Add focus keyword
        if ($this->focus_keyword) {
            $tags['meta'][] = ['name' => 'keywords', 'content' => $this->focus_keyword];
        }

        // Add additional meta tags
        if ($this->additional_meta) {
            foreach ($this->additional_meta as $meta) {
                if (isset($meta['name']) && isset($meta['content'])) {
                    $tags['meta'][] = $meta;
                }
            }
        }

        return $tags;
    }

    /**
     * Static helper methods
     */
    public static function createForModel(Model $model, array $data = []): self
    {
        $data = array_merge([
            'seoable_type' => get_class($model),
            'seoable_id' => $model->getKey(),
            'language' => $model->language ?? app()->getLocale(),
        ], $data);

        return static::create($data);
    }

    public static function updateOrCreateForModel(Model $model, array $data = []): self
    {
        return static::updateOrCreate([
            'seoable_type' => get_class($model),
            'seoable_id' => $model->getKey(),
            'language' => $model->language ?? app()->getLocale(),
        ], $data);
    }
}