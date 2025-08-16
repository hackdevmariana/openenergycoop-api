<?php

namespace Database\Factories;

use App\Models\NewsletterSubscription;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NewsletterSubscription>
 */
class NewsletterSubscriptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = NewsletterSubscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending', 'confirmed', 'unsubscribed', 'bounced']);
        $language = $this->faker->randomElement(['es', 'en', 'ca', 'eu', 'gl']);
        $source = $this->faker->randomElement(['website', 'api', 'import', 'manual', 'form', 'landing', 'social', 'referral']);

        return [
            'name' => $this->faker->optional(0.8)->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'status' => $status,
            'subscription_source' => $source,
            'preferences' => $this->generatePreferences(),
            'tags' => $this->generateTags(),
            'language' => $language,
            'confirmed_at' => $status === 'confirmed' ? $this->faker->dateTimeBetween('-6 months', 'now') : null,
            'unsubscribed_at' => $status === 'unsubscribed' ? $this->faker->dateTimeBetween('-3 months', 'now') : null,
            'confirmation_token' => $this->faker->sha256(),
            'unsubscribe_token' => $this->faker->sha256(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'emails_sent' => $this->faker->numberBetween(0, 50),
            'emails_opened' => $this->faker->numberBetween(0, 25),
            'links_clicked' => $this->faker->numberBetween(0, 10),
            'last_email_sent_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 month', 'now'),
            'last_email_opened_at' => $this->faker->optional(0.5)->dateTimeBetween('-2 weeks', 'now'),
            'organization_id' => null, // Can be overridden
        ];
    }

    /**
     * Indicate that the subscription is pending confirmation.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'confirmed_at' => null,
            'unsubscribed_at' => null,
            'emails_sent' => 0,
            'emails_opened' => 0,
            'links_clicked' => 0,
            'last_email_sent_at' => null,
            'last_email_opened_at' => null,
        ]);
    }

    /**
     * Indicate that the subscription is confirmed/active.
     */
    public function confirmed(): static
    {
        $confirmedAt = $this->faker->dateTimeBetween('-6 months', '-1 day');

        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'confirmed_at' => $confirmedAt,
            'unsubscribed_at' => null,
            'emails_sent' => $this->faker->numberBetween(1, 50),
            'emails_opened' => $this->faker->numberBetween(0, 25),
            'links_clicked' => $this->faker->numberBetween(0, 10),
            'last_email_sent_at' => $this->faker->dateTimeBetween($confirmedAt, 'now'),
        ]);
    }

    /**
     * Indicate that the subscription is unsubscribed.
     */
    public function unsubscribed(): static
    {
        $confirmedAt = $this->faker->dateTimeBetween('-8 months', '-2 months');
        $unsubscribedAt = $this->faker->dateTimeBetween($confirmedAt, '-1 week');

        return $this->state(fn (array $attributes) => [
            'status' => 'unsubscribed',
            'confirmed_at' => $confirmedAt,
            'unsubscribed_at' => $unsubscribedAt,
            'tags' => array_merge(
                $this->generateTags(),
                ['unsubscribe_reason:' . $this->faker->randomElement(['too_frequent', 'not_relevant', 'spam', 'other'])]
            ),
        ]);
    }

    /**
     * Indicate that the subscription email bounced.
     */
    public function bounced(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'bounced',
            'confirmed_at' => $this->faker->dateTimeBetween('-6 months', '-1 month'),
            'unsubscribed_at' => null,
            'tags' => array_merge($this->generateTags(), ['bounced']),
        ]);
    }

    /**
     * Indicate that the subscriber complained about spam.
     */
    public function complained(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'complained',
            'confirmed_at' => $this->faker->dateTimeBetween('-6 months', '-1 month'),
            'unsubscribed_at' => null,
            'tags' => array_merge($this->generateTags(), ['spam_complaint']),
        ]);
    }

    /**
     * Create a highly engaged subscriber.
     */
    public function engaged(): static
    {
        $emailsSent = $this->faker->numberBetween(10, 50);
        $emailsOpened = $this->faker->numberBetween(8, $emailsSent);
        $linksClicked = $this->faker->numberBetween(5, $emailsOpened);

        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'emails_sent' => $emailsSent,
            'emails_opened' => $emailsOpened,
            'links_clicked' => $linksClicked,
            'tags' => array_merge($this->generateTags(), ['engaged', 'high_value']),
        ]);
    }

    /**
     * Create a low engagement subscriber.
     */
    public function lowEngagement(): static
    {
        $emailsSent = $this->faker->numberBetween(10, 30);

        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'emails_sent' => $emailsSent,
            'emails_opened' => 0, // No emails opened
            'links_clicked' => 0, // No links clicked
            'tags' => array_merge($this->generateTags(), ['low_engagement']),
        ]);
    }

    /**
     * Create a subscription with specific language.
     */
    public function withLanguage(string $language): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => $language,
        ]);
    }

    /**
     * Create a subscription from specific source.
     */
    public function fromSource(string $source): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_source' => $source,
        ]);
    }

    /**
     * Create a subscription with specific organization.
     */
    public function withOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Create a subscription with specific email.
     */
    public function withEmail(string $email): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => $email,
        ]);
    }

    /**
     * Create a subscription with specific tags.
     */
    public function withTags(array $tags): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => $tags,
        ]);
    }

    /**
     * Create a recent subscription.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Create an old subscription.
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-2 years', '-6 months'),
        ]);
    }

    /**
     * Create a subscription with daily frequency.
     */
    public function dailyFrequency(): static
    {
        return $this->state(fn (array $attributes) => [
            'preferences' => array_merge(
                $this->generatePreferences(),
                ['frequency' => 'daily']
            ),
        ]);
    }

    /**
     * Create a subscription with weekly frequency.
     */
    public function weeklyFrequency(): static
    {
        return $this->state(fn (array $attributes) => [
            'preferences' => array_merge(
                $this->generatePreferences(),
                ['frequency' => 'weekly']
            ),
        ]);
    }

    /**
     * Create a subscription with monthly frequency.
     */
    public function monthlyFrequency(): static
    {
        return $this->state(fn (array $attributes) => [
            'preferences' => array_merge(
                $this->generatePreferences(),
                ['frequency' => 'monthly']
            ),
        ]);
    }

    /**
     * Create a subscription without name.
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => null,
        ]);
    }

    /**
     * Generate realistic preferences.
     */
    private function generatePreferences(): array
    {
        return [
            'frequency' => $this->faker->randomElement(['daily', 'weekly', 'monthly']),
            'format' => $this->faker->randomElement(['html', 'text']),
            'topics' => $this->faker->randomElements([
                'general', 'news', 'updates', 'events', 'promotions', 
                'technology', 'environment', 'community', 'energy'
            ], $this->faker->numberBetween(1, 4)),
        ];
    }

    /**
     * Generate realistic tags.
     */
    private function generateTags(): array
    {
        $possibleTags = [
            'newsletter', 'subscriber', 'member', 'customer', 'lead',
            'interested_in_solar', 'interested_in_wind', 'business',
            'residential', 'premium', 'vip', 'new_subscriber'
        ];

        return $this->faker->randomElements(
            $possibleTags,
            $this->faker->numberBetween(0, 3)
        );
    }
}
