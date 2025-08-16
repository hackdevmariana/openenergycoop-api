<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Page;
use App\Models\PageComponent;
use App\Models\Hero;
use App\Models\TextContent;
use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PageComponent>
 */
class PageComponentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PageComponent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'componentable_type' => $this->faker->randomElement(['App\\Models\\Hero', 'App\\Models\\Banner', 'App\\Models\\Article']),
            'componentable_id' => 1, // Will be overridden in afterCreating
            'position' => $this->faker->numberBetween(1, 10),
            'parent_id' => null,
            'language' => $this->faker->randomElement(['es', 'en', 'ca', 'eu', 'gl']),
            'organization_id' => Organization::factory(),
            'is_draft' => $this->faker->boolean(60), // 60% chance of being draft
            'version' => $this->faker->randomElement(['1.0', '1.1', '2.0']),
            'published_at' => $this->faker->optional(0.4)->dateTimeBetween('-1 year', 'now'),
            'preview_token' => null,
            'settings' => [
                'margin' => $this->faker->randomElement(['0px', '10px', '20px', '30px']),
                'padding' => $this->faker->randomElement(['0px', '15px', '25px']),
                'background_color' => $this->faker->optional(0.5)->hexColor(),
                'text_align' => $this->faker->randomElement(['left', 'center', 'right']),
            ],
            'cache_enabled' => $this->faker->boolean(80), // 80% chance of cache enabled
            'visibility_rules' => $this->faker->optional(0.3)->randomElement([
                [['type' => 'auth_required']],
                [['type' => 'role_required', 'value' => 'admin']],
                [['type' => 'date_range', 'start' => now()->subDays(30), 'end' => now()->addDays(30)]],
            ]),
            'ab_test_group' => $this->faker->optional(0.2)->randomElement(['A', 'B', 'C']),
        ];
    }

    /**
     * Create a published component.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => false,
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Create a draft component.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => true,
            'published_at' => null,
        ]);
    }

    /**
     * Create a component in Spanish.
     */
    public function spanish(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'es',
        ]);
    }

    /**
     * Create a component in English.
     */
    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'en',
        ]);
    }

    /**
     * Create a Hero component.
     */
    public function heroComponent(): static
    {
        return $this->state(fn (array $attributes) => [
            'componentable_type' => 'App\\Models\\Hero',
            'settings' => [
                'height' => '600px',
                'background_type' => 'image',
                'overlay_opacity' => 0.3,
                'text_position' => 'center',
                'animation' => 'fade-in',
            ],
        ])->afterCreating(function (PageComponent $component) {
            $hero = Hero::factory()->create();
            $component->update(['componentable_id' => $hero->id]);
        });
    }

    /**
     * Create an Article component.
     */
    public function articleComponent(): static
    {
        return $this->state(fn (array $attributes) => [
            'componentable_type' => 'App\\Models\\Article',
            'settings' => [
                'font_size' => '16px',
                'line_height' => '1.6',
                'max_width' => '800px',
                'text_align' => 'left',
            ],
        ])->afterCreating(function (PageComponent $component) {
            $article = \App\Models\Article::factory()->create();
            $component->update(['componentable_id' => $article->id]);
        });
    }

    /**
     * Create a Banner component.
     */
    public function bannerComponent(): static
    {
        return $this->state(fn (array $attributes) => [
            'componentable_type' => 'App\\Models\\Banner',
            'settings' => [
                'display_type' => 'fixed',
                'position' => 'top',
                'auto_hide' => true,
                'hide_delay' => 5000,
            ],
        ])->afterCreating(function (PageComponent $component) {
            $banner = Banner::factory()->create();
            $component->update(['componentable_id' => $banner->id]);
        });
    }

    /**
     * Create a component for specific page.
     */
    public function forPage(Page $page): static
    {
        return $this->state(fn (array $attributes) => [
            'page_id' => $page->id,
            'language' => $page->language,
            'organization_id' => $page->organization_id,
        ]);
    }

    /**
     * Create a child component.
     */
    public function childOf(PageComponent $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'page_id' => $parent->page_id,
            'language' => $parent->language,
            'organization_id' => $parent->organization_id,
        ]);
    }

    /**
     * Create a component with specific position.
     */
    public function atPosition(int $position): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => $position,
        ]);
    }

    /**
     * Create a component with visibility rules.
     */
    public function withVisibilityRules(array $rules): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility_rules' => $rules,
        ]);
    }

    /**
     * Create a component requiring authentication.
     */
    public function requiresAuth(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility_rules' => [['type' => 'auth_required']],
        ]);
    }

    /**
     * Create a component requiring specific role.
     */
    public function requiresRole(string $role): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility_rules' => [['type' => 'role_required', 'value' => $role]],
        ]);
    }

    /**
     * Create a component with date range visibility.
     */
    public function withDateRange(string $start, string $end): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility_rules' => [['type' => 'date_range', 'start' => $start, 'end' => $end]],
        ]);
    }

    /**
     * Create a component with cache enabled.
     */
    public function withCache(): static
    {
        return $this->state(fn (array $attributes) => [
            'cache_enabled' => true,
        ]);
    }

    /**
     * Create a component with cache disabled.
     */
    public function withoutCache(): static
    {
        return $this->state(fn (array $attributes) => [
            'cache_enabled' => false,
        ]);
    }

    /**
     * Create a component with custom settings.
     */
    public function withSettings(array $settings): static
    {
        return $this->state(fn (array $attributes) => [
            'settings' => array_merge($attributes['settings'] ?? [], $settings),
        ]);
    }

    /**
     * Create a component with AB test group.
     */
    public function inAbTestGroup(string $group): static
    {
        return $this->state(fn (array $attributes) => [
            'ab_test_group' => $group,
        ]);
    }

    /**
     * Create a component with preview token.
     */
    public function withPreviewToken(): static
    {
        return $this->state(fn (array $attributes) => [
            'preview_token' => \Str::random(32),
        ]);
    }

    /**
     * Create a component for specific organization.
     */
    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Create a component with specific version.
     */
    public function version(string $version): static
    {
        return $this->state(fn (array $attributes) => [
            'version' => $version,
        ]);
    }

    /**
     * Create a visible component (no visibility rules).
     */
    public function visible(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility_rules' => null,
            'is_draft' => false,
            'published_at' => now(),
        ]);
    }

    /**
     * Create a complete component with all fields.
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'componentable_type' => 'App\\Models\\Hero',
            'position' => 1,
            'language' => 'es',
            'is_draft' => false,
            'version' => '2.0',
            'published_at' => now()->subDays(5),
            'preview_token' => \Str::random(32),
            'settings' => [
                'margin' => '20px',
                'padding' => '30px',
                'background_color' => '#f8f9fa',
                'text_align' => 'center',
                'border_radius' => '8px',
                'animation' => 'slide-in',
                'responsive' => true,
            ],
            'cache_enabled' => true,
            'visibility_rules' => [
                ['type' => 'date_range', 'start' => now()->subDays(30), 'end' => now()->addDays(30)]
            ],
            'ab_test_group' => 'A',
        ]);
    }

    /**
     * Create a minimal component.
     */
    public function minimal(): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => 1,
            'settings' => null,
            'visibility_rules' => null,
            'ab_test_group' => null,
            'preview_token' => null,
        ]);
    }

    /**
     * Create a component hierarchy.
     */
    public function withChildren(int $count = 2): static
    {
        return $this->afterCreating(function (PageComponent $component) use ($count) {
            PageComponent::factory()
                ->count($count)
                ->childOf($component)
                ->create();
        });
    }

    /**
     * Create a component with complex settings.
     */
    public function withComplexSettings(): static
    {
        return $this->state(fn (array $attributes) => [
            'settings' => [
                'layout' => [
                    'type' => 'grid',
                    'columns' => 3,
                    'gap' => '20px',
                ],
                'typography' => [
                    'font_family' => 'Inter',
                    'font_size' => '16px',
                    'line_height' => '1.6',
                    'font_weight' => '400',
                ],
                'colors' => [
                    'primary' => '#007bff',
                    'secondary' => '#6c757d',
                    'background' => '#ffffff',
                ],
                'responsive' => [
                    'mobile' => ['columns' => 1],
                    'tablet' => ['columns' => 2],
                    'desktop' => ['columns' => 3],
                ],
                'animation' => [
                    'type' => 'fade-in',
                    'duration' => '300ms',
                    'delay' => '100ms',
                ],
            ],
        ]);
    }

    /**
     * Create a component with special characters in settings.
     */
    public function withSpecialCharacters(): static
    {
        return $this->state(fn (array $attributes) => [
            'settings' => [
                'title' => 'Título con Ñ, Ç & Símbolos Éspeciáles',
                'description' => '¡Descripción con acentos, ñ, ç y otros símbolos especiales!',
                'keywords' => 'ñ, ç, é, í, ó, ú, à, è, ì, ò, ù',
                'custom_css' => '.special-chars { content: "áéíóú"; }',
            ],
        ]);
    }

    /**
     * Create multiple components for a page in sequence.
     */
    public function createSequence(Page $page, int $count = 3): static
    {
        return $this->afterCreating(function (PageComponent $component) use ($page, $count) {
            for ($i = 2; $i <= $count; $i++) {
                PageComponent::factory()
                    ->forPage($page)
                    ->atPosition($i)
                    ->create();
            }
        });
    }
}
