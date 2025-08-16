<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Page>
 */
class PageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Page::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(3);
        
        return [
            'title' => $title,
            'slug' => \Str::slug($title),
            'route' => null,
            'language' => $this->faker->randomElement(['es', 'en', 'ca', 'eu', 'gl']),
            'organization_id' => Organization::factory(),
            'is_draft' => $this->faker->boolean(70), // 70% chance of being draft
            'template' => $this->faker->randomElement(array_keys(Page::TEMPLATES)),
            'meta_data' => [
                'title' => $title,
                'description' => $this->faker->paragraph(),
                'keywords' => implode(', ', $this->faker->words(5)),
            ],
            'cache_duration' => $this->faker->numberBetween(15, 120),
            'requires_auth' => $this->faker->boolean(20), // 20% chance of requiring auth
            'allowed_roles' => $this->faker->optional(0.3)->randomElements(['admin', 'editor', 'viewer', 'manager'], $this->faker->numberBetween(1, 3)),
            'parent_id' => null,
            'sort_order' => $this->faker->numberBetween(0, 100),
            'published_at' => $this->faker->optional(0.6)->dateTimeBetween('-1 year', 'now'),
            'search_keywords' => $this->faker->optional(0.7)->words(8),
            'internal_notes' => $this->faker->optional(0.4)->paragraph(),
            'last_reviewed_at' => $this->faker->optional(0.5)->dateTimeBetween('-6 months', 'now'),
            'accessibility_notes' => $this->faker->optional(0.3)->sentence(),
            'reading_level' => $this->faker->optional(0.6)->randomElement(['beginner', 'intermediate', 'advanced']),
            'created_by_user_id' => User::factory(),
            'updated_by_user_id' => null,
            'approved_by_user_id' => null,
            'approved_at' => null,
        ];
    }

    /**
     * Create a published page.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => false,
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Create a draft page.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => true,
            'published_at' => null,
        ]);
    }

    /**
     * Create a page in Spanish.
     */
    public function spanish(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'es',
            'title' => 'Página de ' . $this->faker->word(),
            'meta_data' => [
                'title' => 'Meta título en español',
                'description' => 'Meta descripción en español para SEO',
                'keywords' => 'energía, renovable, sostenible, verde',
            ],
        ]);
    }

    /**
     * Create a page in English.
     */
    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'en',
            'title' => $this->faker->words(3, true) . ' Page',
            'meta_data' => [
                'title' => 'Meta title in English',
                'description' => 'Meta description in English for SEO',
                'keywords' => 'energy, renewable, sustainable, green',
            ],
        ]);
    }

    /**
     * Create a home page.
     */
    public function homePage(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Inicio',
            'slug' => 'home',
            'route' => '/home',
            'template' => 'landing',
            'is_draft' => false,
            'published_at' => now(),
            'sort_order' => 0,
            'parent_id' => null,
        ]);
    }

    /**
     * Create a landing page.
     */
    public function landingPage(): static
    {
        return $this->state(fn (array $attributes) => [
            'template' => 'landing',
            'parent_id' => null,
            'cache_duration' => 30,
            'meta_data' => array_merge($attributes['meta_data'] ?? [], [
                'landing_type' => 'conversion',
                'call_to_action' => 'Únete Ahora',
            ]),
        ]);
    }

    /**
     * Create a contact page.
     */
    public function contactPage(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Contacto',
            'slug' => 'contact',
            'template' => 'contact',
            'meta_data' => [
                'title' => 'Contacto',
                'description' => 'Ponte en contacto con nosotros',
                'contact_info' => [
                    'email' => 'info@example.com',
                    'phone' => '+34 123 456 789',
                    'address' => 'Calle Ejemplo 123, Madrid',
                ],
            ],
        ]);
    }

    /**
     * Create an about page.
     */
    public function aboutPage(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Acerca de Nosotros',
            'slug' => 'about',
            'template' => 'about',
            'meta_data' => [
                'title' => 'Acerca de Nosotros',
                'description' => 'Conoce más sobre nuestra misión y valores',
                'keywords' => 'empresa, misión, valores, equipo',
            ],
        ]);
    }

    /**
     * Create an article list page.
     */
    public function articleListPage(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Blog',
            'slug' => 'blog',
            'template' => 'article_list',
            'meta_data' => [
                'title' => 'Blog - Últimas Noticias',
                'description' => 'Lee nuestras últimas noticias y artículos',
                'articles_per_page' => 10,
                'show_categories' => true,
            ],
        ]);
    }

    /**
     * Create a services page.
     */
    public function servicesPage(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Servicios',
            'slug' => 'services',
            'template' => 'services',
            'meta_data' => [
                'title' => 'Nuestros Servicios',
                'description' => 'Descubre todos los servicios que ofrecemos',
                'service_categories' => ['consultoría', 'instalación', 'mantenimiento'],
            ],
        ]);
    }

    /**
     * Create a gallery page.
     */
    public function galleryPage(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Galería',
            'slug' => 'gallery',
            'template' => 'gallery',
            'meta_data' => [
                'title' => 'Galería de Imágenes',
                'description' => 'Explora nuestra galería de proyectos',
                'images_per_row' => 3,
                'show_lightbox' => true,
            ],
        ]);
    }

    /**
     * Create a page that requires authentication.
     */
    public function requiresAuth(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_auth' => true,
            'allowed_roles' => ['member', 'premium'],
        ]);
    }

    /**
     * Create a page for specific organization.
     */
    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Create a child page.
     */
    public function childOf(Page $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'language' => $parent->language,
            'organization_id' => $parent->organization_id,
            'sort_order' => $parent->children()->count() + 1,
        ]);
    }

    /**
     * Create a page with specific route.
     */
    public function withRoute(string $route): static
    {
        return $this->state(fn (array $attributes) => [
            'route' => '/' . ltrim($route, '/'),
        ]);
    }

    /**
     * Create a page with meta data.
     */
    public function withMetaData(array $metaData): static
    {
        return $this->state(fn (array $attributes) => [
            'meta_data' => array_merge($attributes['meta_data'] ?? [], $metaData),
        ]);
    }

    /**
     * Create a page with search keywords.
     */
    public function withSearchKeywords(array $keywords): static
    {
        return $this->state(fn (array $attributes) => [
            'search_keywords' => $keywords,
        ]);
    }

    /**
     * Create a page with specific template.
     */
    public function withTemplate(string $template): static
    {
        return $this->state(fn (array $attributes) => [
            'template' => $template,
        ]);
    }

    /**
     * Create a page with custom cache duration.
     */
    public function withCacheDuration(int $minutes): static
    {
        return $this->state(fn (array $attributes) => [
            'cache_duration' => $minutes,
        ]);
    }

    /**
     * Create a page with internal notes.
     */
    public function withInternalNotes(string $notes): static
    {
        return $this->state(fn (array $attributes) => [
            'internal_notes' => $notes,
        ]);
    }

    /**
     * Create a page with accessibility notes.
     */
    public function withAccessibilityNotes(string $notes): static
    {
        return $this->state(fn (array $attributes) => [
            'accessibility_notes' => $notes,
        ]);
    }

    /**
     * Create a page with specific reading level.
     */
    public function withReadingLevel(string $level): static
    {
        return $this->state(fn (array $attributes) => [
            'reading_level' => $level,
        ]);
    }

    /**
     * Create a recently reviewed page.
     */
    public function recentlyReviewed(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_reviewed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Create an approved page.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_by_user_id' => User::factory(),
            'approved_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Create a page with sort order.
     */
    public function withSortOrder(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'sort_order' => $order,
        ]);
    }

    /**
     * Create multiple pages in hierarchy.
     */
    public function hierarchy(int $levels = 2): static
    {
        return $this->afterCreating(function (Page $page) use ($levels) {
            if ($levels > 1) {
                Page::factory()
                    ->count(rand(1, 3))
                    ->childOf($page)
                    ->hierarchy($levels - 1)
                    ->create();
            }
        });
    }

    /**
     * Create a page with complete data.
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Página Completa de Prueba',
            'slug' => 'pagina-completa-prueba',
            'route' => '/test/complete-page',
            'language' => 'es',
            'is_draft' => false,
            'template' => 'default',
            'meta_data' => [
                'title' => 'Página Completa - Meta Título',
                'description' => 'Esta es una página completa con todos los campos llenos para testing',
                'keywords' => 'test, completo, página, meta, seo',
                'canonical_url' => 'https://example.com/test/complete-page',
                'robots' => 'index,follow',
            ],
            'cache_duration' => 60,
            'requires_auth' => false,
            'allowed_roles' => null,
            'sort_order' => 10,
            'published_at' => now()->subDays(7),
            'search_keywords' => ['test', 'página', 'completa', 'ejemplo'],
            'internal_notes' => 'Esta página es para testing completo de funcionalidades',
            'last_reviewed_at' => now()->subDays(2),
            'accessibility_notes' => 'Página accesible con alt text en imágenes y navegación por teclado',
            'reading_level' => 'intermediate',
        ]);
    }

    /**
     * Create a minimal page.
     */
    public function minimal(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Página Mínima',
            'slug' => 'pagina-minima',
            'route' => null,
            'meta_data' => null,
            'search_keywords' => null,
            'internal_notes' => null,
            'accessibility_notes' => null,
            'reading_level' => null,
            'last_reviewed_at' => null,
            'allowed_roles' => null,
        ]);
    }

    /**
     * Create a page with long content for testing.
     */
    public function withLongContent(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => str_repeat('Título Muy Largo Para Testing de Límites ', 5),
            'internal_notes' => $this->faker->text(1500),
            'accessibility_notes' => $this->faker->text(800),
            'meta_data' => [
                'title' => str_repeat('Meta título muy largo ', 8),
                'description' => $this->faker->text(450),
                'keywords' => implode(', ', $this->faker->words(50)),
            ],
        ]);
    }

    /**
     * Create a page with special characters.
     */
    public function withSpecialCharacters(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Página con Ñ, Ç & Símbolos Éspeciáles',
            'slug' => 'pagina-con-n-c-simbolos-especiales',
            'meta_data' => [
                'title' => 'Títúlo cön çaractéres ëspëçiälës & símbölos',
                'description' => '¡Esta página contiene acentos, ñ, ç y otros símbolos especiales!',
                'keywords' => 'ñ, ç, é, í, ó, ú, à, è, ì, ò, ù',
            ],
            'internal_notes' => 'Nötas internas con ¢aracteres €speciales & símbolos #raros',
        ]);
    }
}
