<?php

namespace Database\Factories;

use App\Models\Hero;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Hero>
 */
class HeroFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Hero::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $textAlign = $this->faker->randomElement(['left', 'center', 'right']);
        $ctaStyle = $this->faker->randomElement(['primary', 'secondary', 'outline', 'ghost', 'link']);
        $animationType = $this->faker->randomElement(['fade', 'slide_left', 'slide_right', 'slide_up', 'slide_down', 'zoom', 'bounce', 'none']);

        return [
            'image' => $this->faker->imageUrl(1920, 1080, 'business'),
            'mobile_image' => $this->faker->imageUrl(768, 512, 'business'),
            'text' => $this->faker->sentence(6),
            'subtext' => $this->faker->optional(0.8)->sentence(12),
            'text_button' => $this->faker->optional(0.7)->words(2, true),
            'internal_link' => $this->faker->optional(0.3)->url(),
            'cta_link_external' => $this->faker->optional(0.4)->url(),
            'position' => $this->faker->numberBetween(1, 10),
            'exhibition_beginning' => $this->faker->optional(0.3)->dateTimeBetween('now', '+1 month'),
            'exhibition_end' => $this->faker->optional(0.3)->dateTimeBetween('+1 month', '+6 months'),
            'active' => true,
            'video_url' => $this->faker->optional(0.2)->url(),
            'video_background' => null,
            'text_align' => $textAlign,
            'overlay_opacity' => $this->faker->numberBetween(0, 80),
            'animation_type' => $animationType,
            'cta_style' => $ctaStyle,
            'priority' => $this->faker->numberBetween(0, 100),
            'language' => $this->faker->randomElement(['es', 'en', 'ca']),
            'organization_id' => null, // Can be overridden
            'is_draft' => false,
            'published_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'created_by_user_id' => User::factory(),
            'updated_by_user_id' => null,
        ];
    }

    /**
     * Indicate that the hero is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => true,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the hero is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => false,
            'published_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the hero is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    /**
     * Create a hero with video content.
     */
    public function withVideo(): static
    {
        return $this->state(fn (array $attributes) => [
            'video_url' => $this->faker->randomElement([
                'https://www.youtube.com/watch?v=' . $this->faker->lexify('???????????'),
                'https://vimeo.com/' . $this->faker->numberBetween(100000000, 999999999),
                'https://www.dailymotion.com/video/' . $this->faker->lexify('???????'),
            ]),
            'video_background' => $this->faker->optional(0.5)->url(),
        ]);
    }

    /**
     * Create a hero without images (text-only).
     */
    public function textOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'image' => null,
            'mobile_image' => null,
            'video_url' => null,
            'video_background' => null,
            'text' => $this->faker->sentence(8),
            'subtext' => $this->faker->sentence(15),
        ]);
    }

    /**
     * Create a hero with CTA button.
     */
    public function withCta(): static
    {
        $hasExternal = $this->faker->boolean();
        
        return $this->state(fn (array $attributes) => [
            'text_button' => $this->faker->words(2, true),
            'cta_link_external' => $hasExternal ? $this->faker->url() : null,
            'internal_link' => !$hasExternal ? '/pages/about' : null,
        ]);
    }

    /**
     * Create a hero without CTA.
     */
    public function withoutCta(): static
    {
        return $this->state(fn (array $attributes) => [
            'text_button' => null,
            'cta_link_external' => null,
            'internal_link' => null,
        ]);
    }

    /**
     * Create a hero with high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $this->faker->numberBetween(80, 100),
            'position' => $this->faker->numberBetween(1, 3),
        ]);
    }

    /**
     * Create a hero with low priority.
     */
    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $this->faker->numberBetween(0, 20),
            'position' => $this->faker->numberBetween(8, 15),
        ]);
    }

    /**
     * Create a hero with exhibition period.
     */
    public function withExhibitionPeriod(): static
    {
        $start = $this->faker->dateTimeBetween('-1 week', '+1 week');
        $end = $this->faker->dateTimeBetween($start, '+2 months');
        
        return $this->state(fn (array $attributes) => [
            'exhibition_beginning' => $start,
            'exhibition_end' => $end,
        ]);
    }

    /**
     * Create a hero currently in exhibition period.
     */
    public function inExhibitionPeriod(): static
    {
        return $this->state(fn (array $attributes) => [
            'exhibition_beginning' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
            'exhibition_end' => $this->faker->dateTimeBetween('+1 day', '+2 months'),
        ]);
    }

    /**
     * Create a hero outside exhibition period.
     */
    public function outsideExhibitionPeriod(): static
    {
        return $this->state(fn (array $attributes) => [
            'exhibition_beginning' => $this->faker->dateTimeBetween('-6 months', '-3 months'),
            'exhibition_end' => $this->faker->dateTimeBetween('-2 months', '-1 day'),
        ]);
    }

    /**
     * Create a hero with specific language.
     */
    public function inLanguage(string $language): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => $language,
        ]);
    }

    /**
     * Create a hero in Spanish.
     */
    public function spanish(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'es',
            'text' => 'Bienvenido a OpenEnergyCoop',
            'subtext' => 'Tu cooperativa energética sostenible y comprometida con el medio ambiente',
            'text_button' => 'Únete ahora',
        ]);
    }

    /**
     * Create a hero in English.
     */
    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'en',
            'text' => 'Welcome to OpenEnergyCoop',
            'subtext' => 'Your sustainable energy cooperative committed to the environment',
            'text_button' => 'Join now',
        ]);
    }

    /**
     * Create a hero in Catalan.
     */
    public function catalan(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'ca',
            'text' => 'Benvingut a OpenEnergyCoop',
            'subtext' => 'La teva cooperativa energètica sostenible i compromesa amb el medi ambient',
            'text_button' => 'Uneix-te ara',
        ]);
    }

    /**
     * Create a hero with specific position.
     */
    public function withPosition(int $position): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => $position,
        ]);
    }

    /**
     * Create a hero with specific organization.
     */
    public function withOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Create a hero with specific text alignment.
     */
    public function withTextAlign(string $align): static
    {
        return $this->state(fn (array $attributes) => [
            'text_align' => $align,
        ]);
    }

    /**
     * Create a hero with specific animation.
     */
    public function withAnimation(string $animationType): static
    {
        return $this->state(fn (array $attributes) => [
            'animation_type' => $animationType,
        ]);
    }

    /**
     * Create a hero without animation.
     */
    public function withoutAnimation(): static
    {
        return $this->state(fn (array $attributes) => [
            'animation_type' => 'none',
        ]);
    }

    /**
     * Create a hero with specific overlay opacity.
     */
    public function withOverlay(int $opacity): static
    {
        return $this->state(fn (array $attributes) => [
            'overlay_opacity' => $opacity,
        ]);
    }

    /**
     * Create a slideshow-ready hero.
     */
    public function slideshowReady(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
            'is_draft' => false,
            'published_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'exhibition_beginning' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'exhibition_end' => $this->faker->dateTimeBetween('+1 week', '+2 months'),
            'priority' => $this->faker->numberBetween(50, 100),
        ]);
    }
}
