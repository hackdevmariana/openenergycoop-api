<?php

namespace Database\Factories;

use App\Models\SeoMetaData;
use App\Models\Article;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeoMetaDataFactory extends Factory
{
    protected $model = SeoMetaData::class;

    public function definition(): array
    {
        $seoableModels = [Article::class, Page::class];
        $seoableType = $this->faker->randomElement($seoableModels);
        
        return [
            'seoable_type' => $seoableType,
            'seoable_id' => $seoableType::factory(),
            'meta_title' => $this->faker->optional(0.8)->sentence(6),
            'meta_description' => $this->faker->optional(0.7)->text(150),
            'canonical_url' => $this->faker->optional(0.5)->url(),
            'robots' => $this->faker->randomElement(['index,follow', 'index,nofollow', 'noindex,follow', 'noindex,nofollow']),
            'og_title' => $this->faker->optional(0.6)->sentence(5),
            'og_description' => $this->faker->optional(0.6)->text(200),
            'og_image_path' => $this->faker->optional(0.4)->imageUrl(1200, 630),
            'og_type' => $this->faker->randomElement(['website', 'article', 'book', 'profile']),
            'twitter_title' => $this->faker->optional(0.5)->sentence(5),
            'twitter_description' => $this->faker->optional(0.5)->text(160),
            'twitter_image_path' => $this->faker->optional(0.3)->imageUrl(1200, 630),
            'twitter_card' => $this->faker->randomElement(['summary', 'summary_large_image', 'app', 'player']),
            'structured_data' => $this->faker->optional(0.3)->randomElements([
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $this->faker->sentence(),
                'author' => ['@type' => 'Person', 'name' => $this->faker->name()],
            ]),
            'focus_keyword' => $this->faker->optional(0.6)->words(2, true),
            'additional_meta' => $this->faker->optional(0.2)->randomElements([
                ['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'],
                ['name' => 'theme-color', 'content' => '#007bff'],
            ]),
            'language' => $this->faker->randomElement(['es', 'en', 'ca', 'eu']),
        ];
    }

    /**
     * State for specific seoable model
     */
    public function forModel($model): static
    {
        return $this->state(fn (array $attributes) => [
            'seoable_type' => get_class($model),
            'seoable_id' => $model->getKey(),
        ]);
    }

    /**
     * State for article SEO
     */
    public function forArticle(): static
    {
        return $this->state(fn (array $attributes) => [
            'seoable_type' => Article::class,
            'seoable_id' => Article::factory(),
            'og_type' => 'article',
            'structured_data' => [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $this->faker->sentence(),
                'author' => ['@type' => 'Person', 'name' => $this->faker->name()],
                'datePublished' => $this->faker->iso8601(),
            ],
        ]);
    }

    /**
     * State for page SEO
     */
    public function forPage(): static
    {
        return $this->state(fn (array $attributes) => [
            'seoable_type' => Page::class,
            'seoable_id' => Page::factory(),
            'og_type' => 'website',
            'structured_data' => [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => $this->faker->sentence(3),
                'description' => $this->faker->paragraph(),
            ],
        ]);
    }

    /**
     * State for complete SEO data
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'meta_title' => $this->faker->sentence(6),
            'meta_description' => $this->faker->text(150),
            'canonical_url' => $this->faker->url(),
            'og_title' => $this->faker->sentence(5),
            'og_description' => $this->faker->text(200),
            'og_image_path' => $this->faker->imageUrl(1200, 630),
            'twitter_title' => $this->faker->sentence(5),
            'twitter_description' => $this->faker->text(160),
            'twitter_image_path' => $this->faker->imageUrl(1200, 630),
            'focus_keyword' => $this->faker->words(2, true),
            'additional_meta' => [
                ['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'],
                ['name' => 'theme-color', 'content' => '#007bff'],
            ],
            'structured_data' => [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => $this->faker->sentence(3),
                'description' => $this->faker->paragraph(),
            ],
        ]);
    }

    /**
     * State for minimal SEO data
     */
    public function minimal(): static
    {
        return $this->state(fn (array $attributes) => [
            'meta_title' => $this->faker->sentence(4),
            'meta_description' => $this->faker->text(100),
            'og_title' => null,
            'og_description' => null,
            'og_image_path' => null,
            'twitter_title' => null,
            'twitter_description' => null,
            'twitter_image_path' => null,
            'focus_keyword' => null,
            'additional_meta' => null,
            'structured_data' => null,
        ]);
    }

    /**
     * State for Spanish language
     */
    public function spanish(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'es',
        ]);
    }

    /**
     * State for English language
     */
    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'en',
        ]);
    }

    /**
     * State for indexable content
     */
    public function indexable(): static
    {
        return $this->state(fn (array $attributes) => [
            'robots' => 'index,follow',
        ]);
    }

    /**
     * State for non-indexable content
     */
    public function nonIndexable(): static
    {
        return $this->state(fn (array $attributes) => [
            'robots' => 'noindex,nofollow',
        ]);
    }

    /**
     * State with specific focus keyword
     */
    public function withKeyword(string $keyword): static
    {
        return $this->state(fn (array $attributes) => [
            'focus_keyword' => $keyword,
        ]);
    }

    /**
     * State with images
     */
    public function withImages(): static
    {
        return $this->state(fn (array $attributes) => [
            'og_image_path' => $this->faker->imageUrl(1200, 630),
            'twitter_image_path' => $this->faker->imageUrl(1200, 630),
        ]);
    }
}