<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Menu>
 */
class MenuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $menuGroups = array_keys(Menu::MENU_GROUPS);
        $isExternal = $this->faker->boolean(30); // 30% chance of external link

        return [
            'icon' => $this->faker->randomElement(['home', 'info', 'contact', 'about', 'services']),
            'text' => $this->faker->words(2, true),
            'internal_link' => $isExternal ? null : '/' . $this->faker->slug(),
            'external_link' => $isExternal ? $this->faker->url() : null,
            'target_blank' => $isExternal ? $this->faker->boolean() : false,
            'parent_id' => null, // Default to root items
            'order' => $this->faker->numberBetween(1, 100),
            'permission' => $this->faker->optional()->word(),
            'menu_group' => $this->faker->randomElement($menuGroups),
            'css_classes' => $this->faker->optional()->words(2, true),
            'visibility_rules' => $this->faker->optional()->randomElements(['auth', 'guest'], 1),
            'badge_text' => $this->faker->optional()->word(),
            'badge_color' => $this->faker->optional()->randomElement(['primary', 'secondary', 'success', 'warning', 'danger']),
            'language' => $this->faker->randomElement(['es', 'en', 'ca']),
            'organization_id' => Organization::factory(),
            'is_draft' => $this->faker->boolean(20), // 20% chance of being draft
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
            'published_at' => $this->faker->boolean(80) ? $this->faker->dateTimeBetween('-1 month') : null,
            'created_by_user_id' => User::factory(),
            'updated_by_user_id' => null,
        ];
    }

    /**
     * Indicate that the menu is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => false,
            'published_at' => $this->faker->dateTimeBetween('-1 month'),
        ]);
    }

    /**
     * Indicate that the menu is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => true,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the menu is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the menu is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set a specific menu group.
     */
    public function group(string $group): static
    {
        return $this->state(fn (array $attributes) => [
            'menu_group' => $group,
        ]);
    }

    /**
     * Set a specific language.
     */
    public function language(string $language): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => $language,
        ]);
    }

    /**
     * Create a child menu item.
     */
    public function child(Menu $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'menu_group' => $parent->menu_group,
            'language' => $parent->language,
            'organization_id' => $parent->organization_id,
        ]);
    }

    /**
     * Create a root menu item.
     */
    public function root(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => null,
        ]);
    }

    /**
     * Create an external link menu.
     */
    public function external(): static
    {
        return $this->state(fn (array $attributes) => [
            'internal_link' => null,
            'external_link' => $this->faker->url(),
            'target_blank' => true,
        ]);
    }

    /**
     * Create an internal link menu.
     */
    public function internal(): static
    {
        return $this->state(fn (array $attributes) => [
            'internal_link' => '/' . $this->faker->slug(),
            'external_link' => null,
            'target_blank' => false,
        ]);
    }
}