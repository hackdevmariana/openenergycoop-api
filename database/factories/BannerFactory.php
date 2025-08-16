<?php

namespace Database\Factories;

use App\Models\Banner;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BannerFactory extends Factory
{
    protected $model = Banner::class;

    public function definition(): array
    {
        return [
            'image' => 'banners/' . $this->faker->uuid() . '.jpg',
            'mobile_image' => $this->faker->optional()->passthrough('banners/mobile_' . $this->faker->uuid() . '.jpg'),
            'internal_link' => $this->faker->optional()->slug(),
            'url' => $this->faker->optional()->url(),
            'position' => $this->faker->numberBetween(0, 10),
            'active' => $this->faker->boolean(80), // 80% probabilidad de estar activo
            'alt_text' => $this->faker->optional()->sentence(3),
            'title' => $this->faker->optional()->sentence(4),
            'description' => $this->faker->optional()->paragraph(),
            'exhibition_beginning' => $this->faker->optional()->dateTimeBetween('now', '+1 week'),
            'exhibition_end' => $this->faker->optional()->dateTimeBetween('+1 week', '+1 month'),
            'banner_type' => $this->faker->randomElement(['header', 'sidebar', 'footer', 'popup', 'inline']),
            'display_rules' => $this->faker->optional()->passthrough([
                'pages' => ['home', 'about'],
                'user_types' => ['guest', 'member']
            ]),
            'click_count' => $this->faker->numberBetween(0, 1000),
            'impression_count' => $this->faker->numberBetween(0, 5000),
            'organization_id' => Organization::factory(),
            'is_draft' => $this->faker->boolean(30), // 30% probabilidad de ser borrador
            'published_at' => function (array $attributes) {
                return $attributes['is_draft'] ? null : $this->faker->dateTimeBetween('-1 month', 'now');
            },
            'created_by_user_id' => User::factory(),
            'updated_by_user_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the banner is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => false,
            'published_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the banner is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => true,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the banner is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
        ]);
    }

    /**
     * Indicate that the banner is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    /**
     * Indicate that the banner is currently displaying.
     */
    public function currentlyDisplaying(): static
    {
        return $this->state(fn (array $attributes) => [
            'exhibition_beginning' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'exhibition_end' => $this->faker->dateTimeBetween('now', '+1 week'),
            'active' => true,
            'is_draft' => false,
            'published_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the banner is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'exhibition_beginning' => $this->faker->dateTimeBetween('-1 month', '-2 weeks'),
            'exhibition_end' => $this->faker->dateTimeBetween('-2 weeks', '-1 week'),
        ]);
    }

    /**
     * Set a specific banner type.
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'banner_type' => $type,
        ]);
    }

    /**
     * Set a specific position.
     */
    public function atPosition(int $position): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => $position,
        ]);
    }
}
