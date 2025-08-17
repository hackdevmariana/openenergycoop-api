<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(2, true);
        $types = array_keys(Tag::TYPES);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->optional(0.6)->sentence,
            'color' => $this->faker->optional(0.4)->hexColor,
            'icon' => $this->faker->optional(0.3)->randomElement([
                'heroicon-o-bolt',
                'heroicon-o-leaf',
                'heroicon-o-star',
                'heroicon-o-fire',
                'heroicon-o-sun',
                'heroicon-o-globe-europe-africa',
            ]),
            'type' => $this->faker->randomElement($types),
            'usage_count' => $this->faker->numberBetween(0, 100),
            'is_featured' => $this->faker->boolean(15), // 15% destacados
            'is_active' => $this->faker->boolean(95), // 95% activos
            'sort_order' => $this->faker->numberBetween(0, 100),
            'metadata' => [
                'created_by' => $this->faker->optional(0.3)->name,
                'category_notes' => $this->faker->optional(0.2)->sentence,
            ],
        ];
    }

    /**
     * Indicate that the tag is energy-related.
     */
    public function energySource(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'energy_source',
            'name' => $this->faker->randomElement([
                'Solar', 'Eólica', 'Hidráulica', 'Biomasa', 'Geotérmica', 'Nuclear'
            ]),
            'color' => $this->faker->randomElement(['#10B981', '#059669', '#34D399']),
            'icon' => 'heroicon-o-bolt',
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the tag is technology-related.
     */
    public function technology(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'technology',
            'name' => $this->faker->randomElement([
                'Fotovoltaico', 'Aerogenerador', 'Blockchain', 'IoT', 'AI', 'Smart Grid'
            ]),
            'color' => $this->faker->randomElement(['#3B82F6', '#1D4ED8', '#60A5FA']),
            'icon' => 'heroicon-o-cpu-chip',
        ]);
    }

    /**
     * Indicate that the tag is sustainability-related.
     */
    public function sustainability(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'sustainability',
            'name' => $this->faker->randomElement([
                'Carbono Neutral', 'Eco-Friendly', 'Renovable', 'Sostenible', 'Zero Waste'
            ]),
            'color' => $this->faker->randomElement(['#059669', '#065F46', '#10B981']),
            'icon' => 'heroicon-o-leaf',
            'is_featured' => $this->faker->boolean(40),
        ]);
    }

    /**
     * Indicate that the tag is region-related.
     */
    public function region(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'region',
            'name' => $this->faker->randomElement([
                'Madrid', 'Cataluña', 'Andalucía', 'Valencia', 'País Vasco', 'Galicia'
            ]),
            'color' => $this->faker->randomElement(['#8B5CF6', '#7C3AED', '#A78BFA']),
            'icon' => 'heroicon-o-map-pin',
        ]);
    }

    /**
     * Indicate that the tag is certification-related.
     */
    public function certification(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'certification',
            'name' => $this->faker->randomElement([
                'ISO 14001', 'LEED', 'Energy Star', 'FSC', 'BREEAM', 'CNMC'
            ]),
            'color' => $this->faker->randomElement(['#F59E0B', '#D97706', '#FCD34D']),
            'icon' => 'heroicon-o-shield-check',
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the tag is feature-related.
     */
    public function feature(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'feature',
            'name' => $this->faker->randomElement([
                'Alta Eficiencia', 'Autoconsumo', 'Almacenamiento', 'Monitoreo', 'Garantía Extendida'
            ]),
            'color' => $this->faker->randomElement(['#EF4444', '#DC2626', '#F87171']),
            'icon' => 'heroicon-o-star',
        ]);
    }

    /**
     * Indicate that the tag is price-related.
     */
    public function priceRange(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'price_range',
            'name' => $this->faker->randomElement([
                'Económico', 'Medio', 'Premium', 'Luxury', 'Budget'
            ]),
            'color' => $this->faker->randomElement(['#6B7280', '#4B5563', '#9CA3AF']),
            'icon' => 'heroicon-o-currency-euro',
        ]);
    }

    /**
     * Indicate that the tag is popular.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'usage_count' => $this->faker->numberBetween(50, 500),
            'is_featured' => $this->faker->boolean(60),
        ]);
    }

    /**
     * Indicate that the tag is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'usage_count' => $this->faker->numberBetween(20, 200),
            'sort_order' => $this->faker->numberBetween(1, 10),
        ]);
    }

    /**
     * Indicate that the tag is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'usage_count' => 0,
        ]);
    }

    /**
     * Create a specific tag by name.
     */
    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'slug' => Str::slug($name),
        ]);
    }

    /**
     * Create all energy source tags.
     */
    public function energySources(): array
    {
        $sources = ['Solar', 'Eólica', 'Hidráulica', 'Biomasa', 'Geotérmica'];
        $tags = [];

        foreach ($sources as $source) {
            $tags[] = $this->energySource()->withName($source)->create();
        }

        return $tags;
    }

    /**
     * Create sustainability tags.
     */
    public function sustainabilityTags(): array
    {
        $tags = ['Carbono Neutral', 'Eco-Friendly', 'Renovable', 'Sostenible'];
        $results = [];

        foreach ($tags as $tag) {
            $results[] = $this->sustainability()->withName($tag)->create();
        }

        return $results;
    }
}