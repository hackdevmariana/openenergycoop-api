<?php

namespace Database\Factories;

use App\Models\SocialLink;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SocialLink>
 */
class SocialLinkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SocialLink::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $platform = $this->faker->randomElement([
            'facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 
            'tiktok', 'telegram', 'whatsapp', 'github', 'discord'
        ]);

        return [
            'platform' => $platform,
            'url' => $this->generateUrlForPlatform($platform),
            'icon' => $this->getIconForPlatform($platform),
            'css_class' => "social-link-{$platform}",
            'color' => SocialLink::PLATFORM_COLORS[$platform] ?? $this->faker->hexColor(),
            'order' => $this->faker->numberBetween(0, 100),
            'is_active' => true,
            'followers_count' => $this->faker->optional(0.7)->numberBetween(100, 50000),
            'organization_id' => null, // Can be overridden
            'is_draft' => false,
            'created_by_user_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the social link is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => true,
        ]);
    }

    /**
     * Indicate that the social link is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => false,
        ]);
    }

    /**
     * Indicate that the social link is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a Facebook social link.
     */
    public function facebook(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => 'facebook',
            'url' => 'https://facebook.com/' . $this->faker->userName(),
            'icon' => 'fab fa-facebook-f',
            'css_class' => 'social-link-facebook',
            'color' => '#1877F2',
        ]);
    }

    /**
     * Create a Twitter social link.
     */
    public function twitter(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => 'twitter',
            'url' => 'https://twitter.com/' . $this->faker->userName(),
            'icon' => 'fab fa-twitter',
            'css_class' => 'social-link-twitter',
            'color' => '#1DA1F2',
        ]);
    }

    /**
     * Create an Instagram social link.
     */
    public function instagram(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => 'instagram',
            'url' => 'https://instagram.com/' . $this->faker->userName(),
            'icon' => 'fab fa-instagram',
            'css_class' => 'social-link-instagram',
            'color' => '#E4405F',
        ]);
    }

    /**
     * Create a LinkedIn social link.
     */
    public function linkedin(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => 'linkedin',
            'url' => 'https://linkedin.com/company/' . $this->faker->slug(),
            'icon' => 'fab fa-linkedin-in',
            'css_class' => 'social-link-linkedin',
            'color' => '#0A66C2',
        ]);
    }

    /**
     * Create a YouTube social link.
     */
    public function youtube(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => 'youtube',
            'url' => 'https://youtube.com/@' . $this->faker->userName(),
            'icon' => 'fab fa-youtube',
            'css_class' => 'social-link-youtube',
            'color' => '#FF0000',
        ]);
    }

    /**
     * Create a GitHub social link.
     */
    public function github(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => 'github',
            'url' => 'https://github.com/' . $this->faker->userName(),
            'icon' => 'fab fa-github',
            'css_class' => 'social-link-github',
            'color' => '#181717',
        ]);
    }

    /**
     * Create a social link with high followers count.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'followers_count' => $this->faker->numberBetween(10000, 1000000),
        ]);
    }

    /**
     * Create a verified social link (high followers).
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'followers_count' => $this->faker->numberBetween(10000, 5000000),
        ]);
    }

    /**
     * Create a social link with no followers data.
     */
    public function noFollowers(): static
    {
        return $this->state(fn (array $attributes) => [
            'followers_count' => null,
        ]);
    }

    /**
     * Create a social link with custom order.
     */
    public function withOrder(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order' => $order,
        ]);
    }

    /**
     * Create a social link with specific organization.
     */
    public function withOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Generate URL for specific platform.
     */
    private function generateUrlForPlatform(string $platform): string
    {
        $username = $this->faker->userName();
        
        return match ($platform) {
            'facebook' => "https://facebook.com/{$username}",
            'twitter' => "https://twitter.com/{$username}",
            'instagram' => "https://instagram.com/{$username}",
            'linkedin' => "https://linkedin.com/company/{$this->faker->slug()}",
            'youtube' => "https://youtube.com/@{$username}",
            'tiktok' => "https://tiktok.com/@{$username}",
            'telegram' => "https://t.me/{$username}",
            'whatsapp' => "https://wa.me/{$this->faker->phoneNumber()}",
            'github' => "https://github.com/{$username}",
            'discord' => "https://discord.gg/{$this->faker->lexify('??????')}",
            default => "https://example.com/{$username}",
        };
    }

    /**
     * Get icon for specific platform.
     */
    private function getIconForPlatform(string $platform): string
    {
        return match ($platform) {
            'facebook' => 'fab fa-facebook-f',
            'twitter' => 'fab fa-twitter',
            'instagram' => 'fab fa-instagram',
            'linkedin' => 'fab fa-linkedin-in',
            'youtube' => 'fab fa-youtube',
            'tiktok' => 'fab fa-tiktok',
            'telegram' => 'fab fa-telegram-plane',
            'whatsapp' => 'fab fa-whatsapp',
            'github' => 'fab fa-github',
            'discord' => 'fab fa-discord',
            default => 'fas fa-link',
        };
    }
}
