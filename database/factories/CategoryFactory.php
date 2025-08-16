<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = array_keys(Category::CATEGORY_TYPES);
        
        return [
            'name' => $this->faker->words(2, true),
            'slug' => $this->faker->slug(2),
            'description' => $this->faker->optional(0.7)->paragraph(),
            'color' => $this->faker->optional(0.6)->hexColor(),
            'icon' => $this->faker->optional(0.5)->randomElement([
                'heroicon-o-folder',
                'heroicon-o-document-text',
                'heroicon-o-photo',
                'heroicon-o-question-mark-circle',
                'heroicon-o-calendar',
                'heroicon-o-shopping-bag',
                'heroicon-o-cog-6-tooth',
                'fa-folder',
                'fa-file',
                'fa-image',
                'fa-question',
                'fa-calendar',
                'fa-shopping-cart'
            ]),
            'parent_id' => null,
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
            'category_type' => $this->faker->randomElement($types),
            'organization_id' => Organization::factory(),
            'language' => $this->faker->randomElement(['es', 'en', 'ca', 'eu', 'gl']),
        ];
    }

    /**
     * Create an active category.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Create an inactive category.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a category in Spanish.
     */
    public function spanish(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'es',
        ]);
    }

    /**
     * Create a category in English.
     */
    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'en',
        ]);
    }

    /**
     * Create a category with specific type.
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'category_type' => $type,
        ]);
    }

    /**
     * Create an article category.
     */
    public function article(): static
    {
        return $this->ofType('article');
    }

    /**
     * Create a document category.
     */
    public function document(): static
    {
        return $this->ofType('document');
    }

    /**
     * Create a media category.
     */
    public function media(): static
    {
        return $this->ofType('media');
    }

    /**
     * Create an FAQ category.
     */
    public function faq(): static
    {
        return $this->ofType('faq');
    }

    /**
     * Create an event category.
     */
    public function event(): static
    {
        return $this->ofType('event');
    }

    /**
     * Create a product category.
     */
    public function product(): static
    {
        return $this->ofType('product');
    }

    /**
     * Create a service category.
     */
    public function service(): static
    {
        return $this->ofType('service');
    }

    /**
     * Create a root category (no parent).
     */
    public function root(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => null,
        ]);
    }

    /**
     * Create a child category of a specific parent.
     */
    public function childOf(Category $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'category_type' => $parent->category_type,
            'organization_id' => $parent->organization_id,
            'language' => $parent->language,
        ]);
    }

    /**
     * Create a category for specific organization.
     */
    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Create a category with custom sort order.
     */
    public function withOrder(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'sort_order' => $order,
        ]);
    }

    /**
     * Create a category with color and icon.
     */
    public function styled(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => $this->faker->hexColor(),
            'icon' => $this->faker->randomElement([
                'heroicon-o-folder',
                'heroicon-o-document-text',
                'heroicon-o-photo',
                'fa-folder',
                'fa-file',
                'fa-image'
            ]),
        ]);
    }

    /**
     * Create a category without styling.
     */
    public function unstyled(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => null,
            'icon' => null,
        ]);
    }

    /**
     * Create a category with description.
     */
    public function withDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Create a category without description.
     */
    public function withoutDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => null,
        ]);
    }

    /**
     * Create a category with specific name and slug.
     */
    public function named(string $name, ?string $slug = null): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'slug' => $slug ?: \Str::slug($name),
        ]);
    }

    /**
     * Create a hierarchical set of categories.
     */
    public function withChildren(int $count = 3): static
    {
        return $this->afterCreating(function (Category $category) use ($count) {
            Category::factory()
                ->count($count)
                ->childOf($category)
                ->create();
        });
    }

    /**
     * Create a deep hierarchy.
     */
    public function deepHierarchy(int $depth = 3): static
    {
        return $this->afterCreating(function (Category $category) use ($depth) {
            $current = $category;
            
            for ($i = 1; $i < $depth; $i++) {
                $current = Category::factory()
                    ->childOf($current)
                    ->create();
            }
        });
    }

    /**
     * Create categories with specific colors.
     */
    public function withColor(string $color): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => $color,
        ]);
    }

    /**
     * Create categories with specific icons.
     */
    public function withIcon(string $icon): static
    {
        return $this->state(fn (array $attributes) => [
            'icon' => $icon,
        ]);
    }

    /**
     * Create categories in sequence (different sort orders).
     */
    public function orderSequence(int $startOrder = 1): static
    {
        $order = $startOrder;
        
        return $this->state(function (array $attributes) use (&$order) {
            return [
                'sort_order' => $order++,
            ];
        });
    }

    /**
     * Create categories with unique slugs (in case of conflicts).
     */
    public function uniqueSlug(): static
    {
        return $this->state(fn (array $attributes) => [
            'slug' => $this->faker->unique()->slug(2) . '-' . $this->faker->randomNumber(4),
        ]);
    }

    /**
     * Create a category with special characters in the name.
     */
    public function withSpecialCharacters(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement([
                'Tecnología & Innovación',
                'Diseño Gráfico',
                'Música & Arte',
                'Ciencia & Naturaleza',
                'Salud & Bienestar',
                'Educación',
                'Programación',
                'Niños & Familia'
            ]),
        ]);
    }

    /**
     * Create categories mimicking a real CMS structure.
     */
    public function cmsStructure(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement([
                'Tecnología',
                'Diseño',
                'Marketing',
                'Desarrollo Web',
                'Redes Sociales',
                'E-commerce',
                'SEO',
                'Contenido',
                'Fotografía',
                'Vídeo',
                'Tutoriales',
                'Recursos',
                'Noticias',
                'Eventos',
                'Productos',
                'Servicios'
            ]),
        ]);
    }

    /**
     * Create a popular category with high sort order.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'sort_order' => $this->faker->numberBetween(1, 10), // Higher priority
            'is_active' => true,
        ]);
    }

    /**
     * Create an archived category.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a technology-themed category.
     */
    public function technology(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement([
                'Programación',
                'Inteligencia Artificial',
                'Desarrollo Web',
                'Apps Móviles',
                'Ciberseguridad',
                'Cloud Computing',
                'DevOps',
                'Bases de Datos'
            ]),
            'color' => $this->faker->randomElement(['#3B82F6', '#1E40AF', '#1D4ED8', '#2563EB']),
            'icon' => $this->faker->randomElement([
                'heroicon-o-computer-desktop',
                'heroicon-o-code-bracket',
                'heroicon-o-cpu-chip',
                'fa-laptop-code',
                'fa-server',
                'fa-database'
            ]),
            'category_type' => 'article',
        ]);
    }
}