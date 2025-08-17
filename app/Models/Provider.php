<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'contact_info',
        'type',
        'is_active',
        'website',
        'email',
        'phone',
        'address',
        'logo_path',
        'rating',
        'total_reviews',
        'certifications',
        'operating_regions',
    ];

    protected $casts = [
        'contact_info' => 'array',
        'is_active' => 'boolean',
        'rating' => 'decimal:2',
        'certifications' => 'array',
        'operating_regions' => 'array',
    ];

    // Relaciones
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function tags(): MorphToMany
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

    public function scopeHighRated($query, float $minRating = 4.0)
    {
        return $query->where('rating', '>=', $minRating);
    }

    public function scopeInRegion($query, string $region)
    {
        return $query->whereJsonContains('operating_regions', $region);
    }

    // Métodos de negocio
    public function getTotalProductsCount(): int
    {
        return $this->products()->count();
    }

    public function getActiveProductsCount(): int
    {
        return $this->products()->where('is_active', true)->count();
    }

    public function getAverageProductPrice(): float
    {
        return $this->products()
            ->where('is_active', true)
            ->where('base_purchase_price', '>', 0)
            ->avg('base_purchase_price') ?? 0;
    }

    public function updateRating(): void
    {
        // Aquí iría la lógica para calcular el rating basado en reviews
        // Por ahora lo dejamos como placeholder
    }

    public function hasCertification(string $certification): bool
    {
        $certifications = $this->certifications ?? [];
        return in_array($certification, $certifications);
    }

    public function operatesInRegion(string $region): bool
    {
        $regions = $this->operating_regions ?? [];
        return in_array($region, $regions);
    }

    public function getLogoUrl(): ?string
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }

    // Constantes
    public const TYPES = [
        'energy' => 'Energía',
        'mining' => 'Minería',
        'physical_goods' => 'Productos Físicos',
        'charity' => 'Caridad',
        'storage' => 'Almacenamiento',
        'trading' => 'Trading',
    ];

    public const CERTIFICATIONS = [
        'iso_14001' => 'ISO 14001',
        'iso_50001' => 'ISO 50001',
        'leed' => 'LEED',
        'energy_star' => 'Energy Star',
        'renewable_energy' => 'Energía Renovable',
        'carbon_neutral' => 'Carbono Neutral',
        'fair_trade' => 'Comercio Justo',
    ];
}