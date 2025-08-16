<?php

namespace Database\Factories;

use App\Models\FormSubmission;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FormSubmission>
 */
class FormSubmissionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FormSubmission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $formName = $this->faker->randomElement(['contact', 'newsletter', 'survey', 'feedback', 'support']);
        $status = $this->faker->randomElement(['pending', 'processed', 'archived', 'spam']);

        return [
            'form_name' => $formName,
            'fields' => $this->generateFields($formName),
            'status' => $status,
            'source_url' => $this->faker->optional(0.8)->url(),
            'referrer' => $this->faker->optional(0.6)->url(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'processed_at' => $status === 'processed' ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
            'processed_by_user_id' => $status === 'processed' ? User::factory() : null,
            'processing_notes' => $status === 'processed' ? $this->faker->optional(0.7)->sentence() : null,
            'organization_id' => null, // Can be overridden
        ];
    }

    /**
     * Indicate that the submission is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'processed_at' => null,
            'processed_by_user_id' => null,
            'processing_notes' => null,
        ]);
    }

    /**
     * Indicate that the submission is processed.
     */
    public function processed(): static
    {
        $processedAt = $this->faker->dateTimeBetween('-1 month', 'now');

        return $this->state(fn (array $attributes) => [
            'status' => 'processed',
            'processed_at' => $processedAt,
            'processed_by_user_id' => User::factory(),
            'processing_notes' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the submission is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
            'processed_at' => $this->faker->dateTimeBetween('-2 months', '-1 week'),
            'processed_by_user_id' => User::factory(),
            'processing_notes' => 'Archived after processing',
        ]);
    }

    /**
     * Indicate that the submission is spam.
     */
    public function spam(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'spam',
            'fields' => [
                'name' => 'Spam User',
                'email' => 'spam@fake.com',
                'message' => 'Buy cheap viagra now! Click here: http://spam.com and http://fake.com for amazing deals!',
            ],
            'processed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'processed_by_user_id' => User::factory(),
            'processing_notes' => 'Marked as spam due to suspicious content',
        ]);
    }

    /**
     * Create a contact form submission.
     */
    public function contact(): static
    {
        return $this->state(fn (array $attributes) => [
            'form_name' => 'contact',
            'fields' => [
                'name' => $this->faker->name(),
                'email' => $this->faker->safeEmail(),
                'phone' => $this->faker->optional(0.7)->phoneNumber(),
                'subject' => $this->faker->sentence(4),
                'message' => $this->faker->paragraph(3),
                'company' => $this->faker->optional(0.5)->company(),
            ],
        ]);
    }

    /**
     * Create a newsletter form submission.
     */
    public function newsletter(): static
    {
        return $this->state(fn (array $attributes) => [
            'form_name' => 'newsletter',
            'fields' => [
                'email' => $this->faker->safeEmail(),
                'name' => $this->faker->optional(0.8)->name(),
                'interests' => $this->faker->randomElements(['energy', 'environment', 'sustainability', 'technology'], 2),
                'frequency' => $this->faker->randomElement(['weekly', 'monthly']),
            ],
        ]);
    }

    /**
     * Create a survey form submission.
     */
    public function survey(): static
    {
        return $this->state(fn (array $attributes) => [
            'form_name' => 'survey',
            'fields' => [
                'email' => $this->faker->optional(0.6)->safeEmail(),
                'age_group' => $this->faker->randomElement(['18-25', '26-35', '36-45', '46-55', '55+']),
                'satisfaction' => $this->faker->numberBetween(1, 10),
                'recommendation' => $this->faker->randomElement(['very_likely', 'likely', 'neutral', 'unlikely', 'very_unlikely']),
                'comments' => $this->faker->optional(0.8)->paragraph(2),
            ],
        ]);
    }

    /**
     * Create a feedback form submission.
     */
    public function feedback(): static
    {
        return $this->state(fn (array $attributes) => [
            'form_name' => 'feedback',
            'fields' => [
                'name' => $this->faker->optional(0.7)->name(),
                'email' => $this->faker->optional(0.8)->safeEmail(),
                'rating' => $this->faker->numberBetween(1, 5),
                'category' => $this->faker->randomElement(['bug', 'feature', 'improvement', 'complaint']),
                'feedback' => $this->faker->paragraph(2),
            ],
        ]);
    }

    /**
     * Create a support form submission.
     */
    public function support(): static
    {
        return $this->state(fn (array $attributes) => [
            'form_name' => 'support',
            'fields' => [
                'name' => $this->faker->name(),
                'email' => $this->faker->safeEmail(),
                'phone' => $this->faker->optional(0.5)->phoneNumber(),
                'issue_type' => $this->faker->randomElement(['technical', 'billing', 'account', 'general']),
                'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
                'description' => $this->faker->paragraph(3),
            ],
        ]);
    }

    /**
     * Create submission with specific organization.
     */
    public function withOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Create submission with specific form name.
     */
    public function withFormName(string $formName): static
    {
        return $this->state(fn (array $attributes) => [
            'form_name' => $formName,
            'fields' => $this->generateFields($formName),
        ]);
    }

    /**
     * Create submission from specific IP.
     */
    public function fromIp(string $ip): static
    {
        return $this->state(fn (array $attributes) => [
            'ip_address' => $ip,
        ]);
    }

    /**
     * Create recent submission.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Create old submission.
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 year', '-2 months'),
        ]);
    }

    /**
     * Create submission with minimal fields.
     */
    public function minimal(): static
    {
        return $this->state(fn (array $attributes) => [
            'fields' => [
                'email' => $this->faker->safeEmail(),
            ],
        ]);
    }

    /**
     * Create submission with extensive fields.
     */
    public function extensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'fields' => [
                'name' => $this->faker->name(),
                'email' => $this->faker->safeEmail(),
                'phone' => $this->faker->phoneNumber(),
                'company' => $this->faker->company(),
                'address' => $this->faker->address(),
                'website' => $this->faker->url(),
                'subject' => $this->faker->sentence(),
                'message' => $this->faker->paragraphs(3, true),
                'source' => $this->faker->randomElement(['google', 'facebook', 'referral', 'direct']),
                'budget' => $this->faker->randomElement(['<5k', '5k-10k', '10k-25k', '25k+']),
                'timeline' => $this->faker->randomElement(['immediate', '1-3 months', '3-6 months', '6+ months']),
            ],
        ]);
    }

    /**
     * Create mobile submission.
     */
    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
        ]);
    }

    /**
     * Generate fields based on form type.
     */
    private function generateFields(string $formName): array
    {
        switch ($formName) {
            case 'contact':
                return [
                    'name' => $this->faker->name(),
                    'email' => $this->faker->safeEmail(),
                    'message' => $this->faker->paragraph(),
                ];

            case 'newsletter':
                return [
                    'email' => $this->faker->safeEmail(),
                    'name' => $this->faker->optional(0.7)->name(),
                ];

            case 'survey':
                return [
                    'rating' => $this->faker->numberBetween(1, 5),
                    'comments' => $this->faker->optional(0.8)->paragraph(),
                ];

            case 'feedback':
                return [
                    'feedback' => $this->faker->paragraph(),
                    'rating' => $this->faker->numberBetween(1, 5),
                ];

            case 'support':
                return [
                    'name' => $this->faker->name(),
                    'email' => $this->faker->safeEmail(),
                    'issue' => $this->faker->paragraph(),
                ];

            default:
                return [
                    'message' => $this->faker->paragraph(),
                ];
        }
    }
}
