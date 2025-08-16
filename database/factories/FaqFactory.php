<?php

namespace Database\Factories;

use App\Models\Faq;
use App\Models\FaqTopic;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Faq>
 */
class FaqFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Faq::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'topic_id' => FaqTopic::factory(),
            'question' => $this->faker->sentence() . '?',
            'answer' => $this->faker->paragraphs(3, true),
            'position' => $this->faker->numberBetween(0, 100),
            'views_count' => $this->faker->numberBetween(0, 1000),
            'helpful_count' => $this->faker->numberBetween(0, 50),
            'not_helpful_count' => $this->faker->numberBetween(0, 10),
            'is_featured' => $this->faker->boolean(20), // 20% chance of being featured
            'tags' => $this->faker->randomElements([
                'general', 'membership', 'billing', 'technical', 'energy', 
                'renewable', 'cooperative', 'sustainability', 'community'
            ], $this->faker->numberBetween(1, 4)),
            'organization_id' => null, // Can be overridden
            'language' => $this->faker->randomElement(['es', 'en', 'ca']),
            'is_draft' => false,
            'published_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'created_by_user_id' => User::factory(),
            'updated_by_user_id' => null,
        ];
    }

    /**
     * Indicate that the FAQ is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => true,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the FAQ is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => false,
            'published_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
        ]);
    }

    /**
     * Indicate that the FAQ is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'position' => $this->faker->numberBetween(1, 10), // Featured FAQs have lower positions
        ]);
    }

    /**
     * Create an FAQ with high engagement.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'views_count' => $this->faker->numberBetween(500, 2000),
            'helpful_count' => $this->faker->numberBetween(50, 200),
            'not_helpful_count' => $this->faker->numberBetween(0, 20),
        ]);
    }

    /**
     * Create an FAQ with specific language.
     */
    public function spanish(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'es',
        ]);
    }

    /**
     * Create an FAQ with specific language.
     */
    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'en',
        ]);
    }

    /**
     * Create an FAQ with specific topic.
     */
    public function withTopic(FaqTopic $topic): static
    {
        return $this->state(fn (array $attributes) => [
            'topic_id' => $topic->id,
        ]);
    }

    /**
     * Create an FAQ without topic.
     */
    public function withoutTopic(): static
    {
        return $this->state(fn (array $attributes) => [
            'topic_id' => null,
        ]);
    }
}
