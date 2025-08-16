<?php

namespace Database\Factories;

use App\Models\TextContent;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TextContent>
 */
class TextContentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TextContent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(3);
        
        return [
            'slug' => \Str::slug($title),
            'title' => $title,
            'subtitle' => $this->faker->optional(0.7)->sentence(),
            'text' => $this->faker->paragraphs(3, true),
            'version' => '1.0',
            'language' => $this->faker->randomElement(['es', 'en', 'ca', 'eu', 'gl']),
            'organization_id' => null,
            'is_draft' => false,
            'published_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'author_id' => null,
            'parent_id' => null,
            'excerpt' => $this->faker->optional(0.6)->paragraph(),
            'reading_time' => $this->faker->numberBetween(1, 15),
            'seo_focus_keyword' => $this->faker->optional(0.5)->word(),
            'number_of_views' => $this->faker->numberBetween(0, 1000),
            'search_keywords' => $this->faker->optional(0.6)->words(3),
            'internal_notes' => $this->faker->optional(0.3)->paragraph(),
            'last_reviewed_at' => $this->faker->optional(0.4)->dateTimeBetween('-3 months', 'now'),
            'accessibility_notes' => $this->faker->optional(0.2)->sentence(),
            'reading_level' => $this->faker->optional(0.5)->randomElement(['beginner', 'intermediate', 'advanced']),
            'created_by_user_id' => null,
            'updated_by_user_id' => null,
            'approved_by_user_id' => null,
            'approved_at' => null,
        ];
    }

    /**
     * Create a published text content.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => false,
            'published_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * Create a draft text content.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => true,
            'published_at' => null,
        ]);
    }

    /**
     * Create text content in Spanish.
     */
    public function spanish(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'es',
        ]);
    }

    /**
     * Create text content in English.
     */
    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'en',
        ]);
    }

    /**
     * Create text content for specific organization.
     */
    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Create text content with author.
     */
    public function withAuthor(User $author): static
    {
        return $this->state(fn (array $attributes) => [
            'author_id' => $author->id,
        ]);
    }

    /**
     * Create text content with creator.
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by_user_id' => $user->id,
        ]);
    }

    /**
     * Create child text content.
     */
    public function childOf(TextContent $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'organization_id' => $parent->organization_id,
            'language' => $parent->language,
        ]);
    }

    /**
     * Create text content with high view count.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'number_of_views' => $this->faker->numberBetween(500, 5000),
        ]);
    }

    /**
     * Create text content with specific version.
     */
    public function version(string $version): static
    {
        return $this->state(fn (array $attributes) => [
            'version' => $version,
        ]);
    }

    /**
     * Create text content with SEO optimization.
     */
    public function withSeo(): static
    {
        return $this->state(fn (array $attributes) => [
            'seo_focus_keyword' => $this->faker->word(),
            'search_keywords' => $this->faker->words(5),
            'excerpt' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Create text content without SEO.
     */
    public function withoutSeo(): static
    {
        return $this->state(fn (array $attributes) => [
            'seo_focus_keyword' => null,
            'search_keywords' => null,
            'excerpt' => null,
        ]);
    }

    /**
     * Create approved text content.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_by_user_id' => User::factory(),
            'approved_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Create text content with specific title and slug.
     */
    public function titled(string $title): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => $title,
            'slug' => \Str::slug($title),
        ]);
    }

    /**
     * Create minimal text content.
     */
    public function minimal(): static
    {
        return $this->state(fn (array $attributes) => [
            'subtitle' => null,
            'excerpt' => null,
            'seo_focus_keyword' => null,
            'search_keywords' => null,
            'internal_notes' => null,
            'accessibility_notes' => null,
            'reading_level' => null,
        ]);
    }

    /**
     * Create complete text content with all features.
     */
    public function complete(): static
    {
        return $this->published()
                    ->withSeo()
                    ->approved()
                    ->popular();
    }
}
