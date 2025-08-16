<?php

namespace Database\Factories;

use App\Models\Province;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Province>
 */
class ProvinceFactory extends Factory
{
    protected $model = Province::class;

    /**
     * Spanish provinces by region
     */
    private static $spanishProvinces = [
        'Andalucía' => ['Almería', 'Cádiz', 'Córdoba', 'Granada', 'Huelva', 'Jaén', 'Málaga', 'Sevilla'],
        'Aragón' => ['Huesca', 'Teruel', 'Zaragoza'],
        'Asturias' => ['Asturias'],
        'Islas Baleares' => ['Islas Baleares'],
        'Canarias' => ['Las Palmas', 'Santa Cruz de Tenerife'],
        'Cantabria' => ['Cantabria'],
        'Castilla-La Mancha' => ['Albacete', 'Ciudad Real', 'Cuenca', 'Guadalajara', 'Toledo'],
        'Castilla y León' => ['Ávila', 'Burgos', 'León', 'Palencia', 'Salamanca', 'Segovia', 'Soria', 'Valladolid', 'Zamora'],
        'Cataluña' => ['Barcelona', 'Girona', 'Lleida', 'Tarragona'],
        'Extremadura' => ['Badajoz', 'Cáceres'],
        'Galicia' => ['A Coruña', 'Lugo', 'Ourense', 'Pontevedra'],
        'La Rioja' => ['La Rioja'],
        'Madrid' => ['Madrid'],
        'Murcia' => ['Murcia'],
        'Navarra' => ['Navarra'],
        'País Vasco' => ['Álava', 'Gipuzkoa', 'Bizkaia'],
        'Valencia' => ['Alicante', 'Castellón', 'Valencia'],
        'Ceuta' => ['Ceuta'],
        'Melilla' => ['Melilla'],
    ];

    public function definition(): array
    {
        $region = Region::factory()->create();
        $regionName = $region->name;
        
        // Si la región tiene provincias definidas, usar una aleatoria
        if (isset(self::$spanishProvinces[$regionName])) {
            $provinceName = $this->faker->randomElement(self::$spanishProvinces[$regionName]);
        } else {
            $provinceName = $this->faker->city(); // Fallback
        }

        return [
            'name' => $provinceName,
            'slug' => Str::slug($provinceName),
            'region_id' => $region->id,
        ];
    }

    /**
     * Create province for specific region
     */
    public function forRegion(Region $region): static
    {
        return $this->state(function (array $attributes) use ($region) {
            $regionName = $region->name;
            
            if (isset(self::$spanishProvinces[$regionName])) {
                $provinceName = $this->faker->randomElement(self::$spanishProvinces[$regionName]);
            } else {
                $provinceName = $this->faker->city();
            }

            return [
                'name' => $provinceName,
                'slug' => Str::slug($provinceName),
                'region_id' => $region->id,
            ];
        });
    }

    /**
     * Create specific provinces
     */
    public function madrid(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Madrid',
            'slug' => 'madrid',
        ]);
    }

    public function barcelona(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Barcelona',
            'slug' => 'barcelona',
        ]);
    }

    public function valencia(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Valencia',
            'slug' => 'valencia',
        ]);
    }

    public function sevilla(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Sevilla',
            'slug' => 'sevilla',
        ]);
    }

    public function zaragoza(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Zaragoza',
            'slug' => 'zaragoza',
        ]);
    }

    public function malaga(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Málaga',
            'slug' => 'malaga',
        ]);
    }

    public function bizkaia(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Bizkaia',
            'slug' => 'bizkaia',
        ]);
    }

    /**
     * Get all provinces for a region
     */
    public static function getProvincesForRegion(string $regionName): array
    {
        return self::$spanishProvinces[$regionName] ?? [];
    }

    /**
     * Create all provinces for a region
     */
    public function allForRegion(Region $region): array
    {
        $regionName = $region->name;
        $provinces = self::$spanishProvinces[$regionName] ?? [];

        return collect($provinces)->map(function ($provinceName) use ($region) {
            return [
                'name' => $provinceName,
                'slug' => Str::slug($provinceName),
                'region_id' => $region->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();
    }
}