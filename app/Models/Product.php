<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'name',
        'slug',
        'description',
        'type',
        'base_purchase_price',
        'base_sale_price',
        'commission_type',
        'commission_value',
        'surcharge_type',
        'surcharge_value',
        'unit',
        'is_active',
        'start_date',
        'end_date',
        'metadata',
        'renewable_percentage',
        'carbon_footprint',
        'geographical_zone',
        'image_path',
        'features',
        'stock_quantity',
        'weight',
        'dimensions',
        'warranty_info',
        'estimated_lifespan_years',
        'meta_title',
        'meta_description',
        'keywords',
    ];

    protected $casts = [
        'base_purchase_price' => 'decimal:2',
        'base_sale_price' => 'decimal:2',
        'commission_value' => 'decimal:4',
        'surcharge_value' => 'decimal:4',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'metadata' => 'array',
        'renewable_percentage' => 'decimal:2',
        'carbon_footprint' => 'decimal:4',
        'features' => 'array',
        'weight' => 'decimal:3',
        'dimensions' => 'array',
        'keywords' => 'array',
    ];

    // Boot method para auto-generar slug
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    // Relaciones
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function userAssets(): HasMany
    {
        return $this->hasMany(UserAsset::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tags')
            ->withPivot(['relevance_score', 'is_primary', 'sort_order', 'metadata'])
            ->withTimestamps();
    }

    public function taggables(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            });
    }

    public function scopeRenewable($query, float $minPercentage = 50.0)
    {
        return $query->where('renewable_percentage', '>=', $minPercentage);
    }

    public function scopeInZone($query, string $zone)
    {
        return $query->where('geographical_zone', $zone);
    }

    public function scopePriceRange($query, float $min = null, float $max = null)
    {
        if ($min !== null) {
            $query->where('base_purchase_price', '>=', $min);
        }
        if ($max !== null) {
            $query->where('base_purchase_price', '<=', $max);
        }
        return $query;
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhereJsonContains('keywords', $search);
        });
    }

    // Métodos de negocio
    public function calculateFinalPrice(float $quantity = 1.0): array
    {
        $basePrice = $this->base_purchase_price * $quantity;
        $commission = $this->calculateCommission($basePrice);
        $surcharge = $this->calculateSurcharge($basePrice);
        $finalPrice = $basePrice + $commission + $surcharge;

        return [
            'base_price' => $basePrice,
            'commission' => $commission,
            'surcharge' => $surcharge,
            'final_price' => $finalPrice,
            'unit_price' => $finalPrice / $quantity,
        ];
    }

    public function calculateCommission(float $amount): float
    {
        return match ($this->commission_type) {
            'percentage' => $amount * ($this->commission_value / 100),
            'fixed' => $this->commission_value,
            default => 0,
        };
    }

    public function calculateSurcharge(float $amount): float
    {
        return match ($this->surcharge_type) {
            'percentage' => $amount * ($this->surcharge_value / 100),
            'fixed' => $this->surcharge_value,
            default => 0,
        };
    }

    public function isAvailable(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        
        if ($this->start_date && $this->start_date->isFuture()) {
            return false;
        }

        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }

        return true;
    }

    public function hasStock(float $quantity = 1): bool
    {
        if ($this->stock_quantity === null) {
            return true; // Stock ilimitado
        }

        return $this->stock_quantity >= $quantity;
    }

    public function getSustainabilityScore(): float
    {
        $score = 0;

        // Porcentaje renovable (40% del score)
        if ($this->renewable_percentage) {
            $score += ($this->renewable_percentage / 100) * 40;
        }

        // Huella de carbono (40% del score, invertido)
        if ($this->carbon_footprint) {
            // Asumimos que 0.1 es muy bajo y 1.0 es muy alto
            $carbonScore = max(0, 1 - ($this->carbon_footprint / 1.0));
            $score += $carbonScore * 40;
        }

        // Certificaciones del proveedor (20% del score)
        $certifications = $this->provider->certifications ?? [];
        $certificationScore = min(1, count($certifications) / 3) * 20;
        $score += $certificationScore;

        return round($score, 2);
    }

    public function getImageUrl(): ?string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    public function reduceStock(float $quantity): bool
    {
        if ($this->stock_quantity === null) {
            return true; // Stock ilimitado
        }

        if ($this->stock_quantity < $quantity) {
            return false;
        }

        $this->decrement('stock_quantity', $quantity);
        return true;
    }

    public function getPrimaryTag(): ?Tag
    {
        return $this->tags()->wherePivot('is_primary', true)->first();
    }

    // Constantes
    public const TYPES = [
        'physical' => 'Producto Físico',
        'energy_kwh' => 'Energía kWh',
        'production_right' => 'Derecho de Producción',
        'storage_capacity' => 'Capacidad de Almacenamiento',
        'mining_ths' => 'Minería TH/s',
        'energy_bond' => 'Bono Energético',
    ];

    public const COMMISSION_TYPES = [
        'percentage' => 'Porcentaje',
        'fixed' => 'Fijo',
        'none' => 'Sin Comisión',
    ];

    public const SURCHARGE_TYPES = [
        'percentage' => 'Porcentaje',
        'fixed' => 'Fijo',
        'none' => 'Sin Recargo',
    ];

    public const UNITS = [
        'unit' => 'Unidad',
        'kWh' => 'kWh',
        'TH/s' => 'TH/s',
        'kg' => 'Kilogramo',
        'ton' => 'Tonelada',
        'MWh' => 'MWh',
        'GWh' => 'GWh',
        'percentage' => 'Porcentaje',
    ];
}