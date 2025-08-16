<?php

namespace Database\Factories;

use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Region>
 */
class RegionFactory extends Factory
{
    protected $model = Region::class;

    /**
     * Spanish regions (Comunidades Autónomas)
     */
    private static $spanishRegions = [
        'Andalucía',
        'Aragón',
        'Asturias',
        'Islas Baleares',
        'Canarias',
        'Cantabria',
        'Castilla-La Mancha',
        'Castilla y León',
        'Cataluña',
        'Extremadura',
        'Galicia',
        'La Rioja',
        'Madrid',
        'Murcia',
        'Navarra',
        'País Vasco',
        'Valencia',
        'Ceuta',
        'Melilla',
    ];

    public function definition(): array
    {
        $name = $this->faker->randomElement(self::$spanishRegions);
        
        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }

    /**
     * Create specific Spanish regions
     */
    public function andalucia(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Andalucía',
            'slug' => 'andalucia',
        ]);
    }

    public function madrid(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Madrid',
            'slug' => 'madrid',
        ]);
    }

    public function cataluna(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Cataluña',
            'slug' => 'cataluna',
        ]);
    }

    public function valencia(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Valencia',
            'slug' => 'valencia',
        ]);
    }

    public function paisVasco(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'País Vasco',
            'slug' => 'pais-vasco',
        ]);
    }

    public function galicia(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Galicia',
            'slug' => 'galicia',
        ]);
    }

    public function aragon(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Aragón',
            'slug' => 'aragon',
        ]);
    }

    public function castillaLeon(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Castilla y León',
            'slug' => 'castilla-y-leon',
        ]);
    }

    /**
     * Create all Spanish regions
     */
    public function allSpanishRegions(): array
    {
        return collect(self::$spanishRegions)->map(function ($name) {
            return [
                'name' => $name,
                'slug' => Str::slug($name),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();
    }
}