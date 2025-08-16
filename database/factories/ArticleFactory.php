<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Article::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(4);
        $wordCount = $this->faker->numberBetween(200, 1000);
        $content = $this->faker->paragraphs($this->faker->numberBetween(3, 8), true);

        return [
            'title' => $title,
            'subtitle' => $this->faker->optional()->sentence(3),
            'text' => $content,
            'excerpt' => $this->faker->text(160),
            'slug' => \Str::slug($title) . '-' . $this->faker->unique()->randomNumber(5) . '-' . time(),
            'featured_image' => $this->faker->optional()->imageUrl(800, 400, 'nature'),
            'author_id' => User::factory(),
            'category_id' => Category::factory(),
            'organization_id' => Organization::factory(),
            'published_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 year', 'now'),
            'scheduled_at' => null,
            'status' => $this->faker->randomElement(['draft', 'published', 'review']),
            'is_draft' => false,
            'featured' => $this->faker->boolean(20), // 20% featured
            'comment_enabled' => $this->faker->boolean(80),
            'language' => $this->faker->randomElement(['es', 'en', 'ca']),
            'reading_time' => max(1, ceil($wordCount / 200)), // Approximate reading time
            'number_of_views' => $this->faker->numberBetween(0, 1000),
            'social_shares_count' => $this->faker->numberBetween(0, 100),
            'seo_focus_keyword' => $this->faker->optional()->words(2, true),
            'search_keywords' => $this->faker->optional()->words(5),
            'related_articles' => null,
            'internal_notes' => $this->faker->optional()->sentence(),
            'accessibility_notes' => $this->faker->optional()->sentence(),
            'reading_level' => $this->faker->randomElement(['basic', 'intermediate', 'advanced']),
            'last_reviewed_at' => $this->faker->optional()->dateTimeBetween('-6 months', 'now'),
            'created_by_user_id' => User::factory(),
            'updated_by_user_id' => null,
            'approved_by_user_id' => null,
            'approved_at' => null,
        ];
    }

    /**
     * Indicate that the article is published
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'is_draft' => false,
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Indicate that the article is a draft
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'is_draft' => true,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the article is featured
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured' => true,
        ]);
    }

    /**
     * Indicate that the article is not featured
     */
    public function notFeatured(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured' => false,
        ]);
    }

    /**
     * Indicate that the article is in review
     */
    public function inReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'review',
            'is_draft' => false,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the article is archived
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
            'is_draft' => true,
            'published_at' => $this->faker->optional()->dateTimeBetween('-1 year', '-1 month'),
        ]);
    }

    /**
     * Indicate that the article is scheduled for future publication
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'is_draft' => false,
            'scheduled_at' => $this->faker->dateTimeBetween('now', '+1 month'),
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the article is popular (high views)
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'number_of_views' => $this->faker->numberBetween(1000, 10000),
            'social_shares_count' => $this->faker->numberBetween(50, 500),
        ]);
    }

    /**
     * Indicate that the article is recent
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the article is old
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => $this->faker->dateTimeBetween('-2 years', '-6 months'),
        ]);
    }

    /**
     * Indicate that the article has high engagement
     */
    public function highEngagement(): static
    {
        return $this->state(fn (array $attributes) => [
            'number_of_views' => $this->faker->numberBetween(2000, 15000),
            'social_shares_count' => $this->faker->numberBetween(100, 800),
            'comment_enabled' => true,
        ]);
    }

    /**
     * Indicate that the article is in a specific language
     */
    public function inLanguage(string $language): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => $language,
        ]);
    }

    /**
     * Indicate that the article belongs to a specific category
     */
    public function inCategory(int $categoryId): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $categoryId,
        ]);
    }

    /**
     * Indicate that the article is written by a specific author
     */
    public function byAuthor(int $authorId): static
    {
        return $this->state(fn (array $attributes) => [
            'author_id' => $authorId,
        ]);
    }

    /**
     * Indicate that the article belongs to a specific organization
     */
    public function forOrganization(int $organizationId): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organizationId,
        ]);
    }

    /**
     * Indicate that the article has comments enabled
     */
    public function withComments(): static
    {
        return $this->state(fn (array $attributes) => [
            'comment_enabled' => true,
        ]);
    }

    /**
     * Indicate that the article has comments disabled
     */
    public function withoutComments(): static
    {
        return $this->state(fn (array $attributes) => [
            'comment_enabled' => false,
        ]);
    }

    /**
     * Indicate that the article has been approved
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_by_user_id' => User::factory(),
            'approved_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the article has related articles
     */
    public function withRelatedArticles(array $articleIds): static
    {
        return $this->state(fn (array $attributes) => [
            'related_articles' => $articleIds,
        ]);
    }

    /**
     * Create an article with specific content for testing search
     */
    public function withContent(string $title, string $content, ?string $excerpt = null): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => $title,
            'text' => $content,
            'excerpt' => $excerpt ?: \Str::limit(strip_tags($content), 160),
            'slug' => \Str::slug($title) . '-' . $this->faker->unique()->randomNumber(5) . '-' . time(),
        ]);
    }

    /**
     * Create multiple articles for testing purposes
     */
    public function testingSuite(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Test Article ' . $this->faker->unique()->numberBetween(1, 1000),
            'text' => 'This is test content for automated testing purposes.',
            'excerpt' => 'Test excerpt for automated testing.',
            'slug' => 'test-article-' . $this->faker->unique()->randomNumber(4),
        ]);
    }
}
