<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'type',
        'usage_count',
        'is_featured',
        'is_active',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    // Boot method para auto-generar slug
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });

        static::updating(function ($tag) {
            if ($tag->isDirty('name') && empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    // Relaciones
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_tags')
            ->withPivot(['relevance_score', 'is_primary', 'sort_order', 'metadata'])
            ->withTimestamps();
    }

    public function taggables(): HasMany
    {
        return $this->hasMany(Taggable::class);
    }

    // Relaciones polimórficas
    public function providers(): MorphToMany
    {
        return $this->morphedByMany(Provider::class, 'taggable');
    }

    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'taggable');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopePopular($query, int $minUsage = 10)
    {
        return $query->where('usage_count', '>=', $minUsage);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Métodos de negocio
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    public function decrementUsage(): void
    {
        $this->decrement('usage_count');
    }

    public function getRelatedTags(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        // Encontrar tags relacionados basados en productos compartidos
        $productIds = $this->products()->pluck('products.id');

        return static::whereHas('products', function ($query) use ($productIds) {
            $query->whereIn('products.id', $productIds);
        })
        ->where('id', '!=', $this->id)
        ->withCount('products')
        ->orderBy('products_count', 'desc')
        ->limit($limit)
        ->get();
    }

    public function getPopularProducts(int $limit = 5)
    {
        return $this->products()
            ->where('is_active', true)
            ->withCount('userAssets')
            ->orderBy('user_assets_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getSimilarTags(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        // Encontrar tags similares por tipo y nombre
        return static::where('type', $this->type)
            ->where('id', '!=', $this->id)
            ->where('is_active', true)
            ->where(function ($query) {
                $words = explode(' ', $this->name);
                foreach ($words as $word) {
                    if (strlen($word) > 3) {
                        $query->orWhere('name', 'like', "%{$word}%");
                    }
                }
            })
            ->orderBy('usage_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public function attachToModel(Model $model, array $attributes = []): void
    {
        $defaultAttributes = [
            'weight' => 1.0,
            'sort_order' => 0,
        ];

        Taggable::updateOrCreate([
            'tag_id' => $this->id,
            'taggable_type' => get_class($model),
            'taggable_id' => $model->id,
        ], array_merge($defaultAttributes, $attributes));

        $this->incrementUsage();
    }

    public function detachFromModel(Model $model): void
    {
        $deleted = Taggable::where([
            'tag_id' => $this->id,
            'taggable_type' => get_class($model),
            'taggable_id' => $model->id,
        ])->delete();

        if ($deleted > 0) {
            $this->decrementUsage();
        }
    }

    public function getColorWithDefault(): string
    {
        if ($this->color) {
            return $this->color;
        }

        // Colores por defecto según tipo
        return match ($this->type) {
            'energy_source' => '#10B981', // Verde
            'technology' => '#3B82F6',    // Azul
            'sustainability' => '#059669', // Verde oscuro
            'region' => '#8B5CF6',        // Púrpura
            'certification' => '#F59E0B', // Amarillo
            'feature' => '#EF4444',       // Rojo
            'target_audience' => '#EC4899', // Rosa
            'price_range' => '#6B7280',   // Gris
            'difficulty' => '#F97316',    // Naranja
            default => '#6B7280',         // Gris por defecto
        };
    }

    public function getIconWithDefault(): string
    {
        if ($this->icon) {
            return $this->icon;
        }

        // Iconos por defecto según tipo
        return match ($this->type) {
            'energy_source' => 'heroicon-o-bolt',
            'technology' => 'heroicon-o-cpu-chip',
            'sustainability' => 'heroicon-o-leaf',
            'region' => 'heroicon-o-map-pin',
            'certification' => 'heroicon-o-shield-check',
            'feature' => 'heroicon-o-star',
            'target_audience' => 'heroicon-o-users',
            'price_range' => 'heroicon-o-currency-euro',
            'difficulty' => 'heroicon-o-chart-bar',
            default => 'heroicon-o-tag',
        };
    }

    public function canBeDeleted(): bool
    {
        return $this->usage_count === 0;
    }

    // Métodos estáticos
    public static function findOrCreateByName(string $name, string $type = 'general'): self
    {
        $slug = Str::slug($name);
        
        return static::firstOrCreate([
            'slug' => $slug,
        ], [
            'name' => $name,
            'type' => $type,
            'is_active' => true,
        ]);
    }

    public static function getPopularByType(string $type, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('type', $type)
            ->where('is_active', true)
            ->orderBy('usage_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function searchTags(string $query, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->orderBy('usage_count', 'desc')
            ->limit($limit)
            ->get();
    }

    // Constantes
    public const TYPES = [
        'general' => 'General',
        'energy_source' => 'Fuente de Energía',
        'technology' => 'Tecnología',
        'sustainability' => 'Sostenibilidad',
        'region' => 'Región',
        'certification' => 'Certificación',
        'feature' => 'Característica',
        'target_audience' => 'Público Objetivo',
        'price_range' => 'Rango de Precio',
        'difficulty' => 'Dificultad',
    ];
}