<?php

namespace Database\Factories;

use App\Models\Affiliate;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Affiliate>
 */
class AffiliateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Affiliate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['partner', 'reseller', 'distributor', 'consultant', 'other'];
        $statuses = ['active', 'inactive', 'pending', 'suspended', 'terminated'];
        $paymentTerms = ['Net 30', 'Net 45', 'Net 60', 'Immediate', 'Weekly', 'Monthly'];

        return [
            'donor_id' => User::factory(),
            'name' => $this->faker->company(),
            'email' => $this->faker->unique()->companyEmail(),
            'company_name' => $this->faker->company(),
            'website' => $this->faker->url(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'country' => $this->faker->country(),
            'postal_code' => $this->faker->postcode(),
            'description' => $this->faker->paragraph(2),
            'type' => $this->faker->randomElement($types),
            'status' => $this->faker->randomElement($statuses),
            'commission_rate' => $this->faker->randomFloat(2, 5, 25),
            'payment_terms' => $this->faker->randomElement($paymentTerms),
            'contract_start_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'contract_end_date' => $this->faker->dateTimeBetween('now', '+2 years'),
            'organization_id' => Organization::factory(),
            'user_id' => User::factory(),
            'is_verified' => $this->faker->boolean(70),
            'verified_at' => $this->faker->optional(0.7)->dateTimeBetween('-6 months', 'now'),
            'verification_notes' => $this->faker->optional(0.5)->sentence(),
            'performance_rating' => $this->faker->optional(0.8)->numberBetween(1, 5),
            'rating_notes' => $this->faker->optional(0.6)->sentence(),
            'rating_updated_at' => $this->faker->optional(0.6)->dateTimeBetween('-3 months', 'now'),
            'rate_change_reason' => $this->faker->optional(0.4)->sentence(),
            'rate_updated_at' => $this->faker->optional(0.4)->dateTimeBetween('-3 months', 'now'),
            'notes' => $this->faker->optional(0.7)->paragraph(1),
            'internal_notes' => $this->faker->optional(0.5)->paragraph(1),
            'tags' => $this->faker->optional(0.6)->words($this->faker->numberBetween(2, 5)),
            'social_media' => $this->faker->optional(0.7)->words($this->faker->numberBetween(1, 3)),
            'banking_info' => $this->faker->optional(0.6)->array([
                'account_name' => $this->faker->company(),
                'account_number' => $this->faker->numerify('##########'),
                'bank_name' => $this->faker->company() . ' Bank',
                'routing_number' => $this->faker->numerify('#########'),
                'swift_code' => $this->faker->bothify('????##??'),
                'iban' => $this->faker->bothify('??##????##########'),
            ]),
            'tax_info' => $this->faker->optional(0.6)->array([
                'tax_id' => $this->faker->numerify('##-#######'),
                'tax_exempt' => $this->faker->boolean(20),
                'tax_exemption_number' => $this->faker->optional(0.3)->numerify('TE-#######'),
            ]),
            'attachments' => $this->faker->optional(0.4)->words($this->faker->numberBetween(1, 3)),
        ];
    }

    /**
     * Indicate that the affiliate is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the affiliate is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the affiliate is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the affiliate is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }

    /**
     * Indicate that the affiliate is terminated.
     */
    public function terminated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'terminated',
        ]);
    }

    /**
     * Indicate that the affiliate is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
            'verified_at' => now(),
        ]);
    }

    /**
     * Indicate that the affiliate is not verified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => false,
            'verified_at' => null,
        ]);
    }

    /**
     * Indicate that the affiliate is a partner.
     */
    public function partner(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'partner',
        ]);
    }

    /**
     * Indicate that the affiliate is a reseller.
     */
    public function reseller(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'reseller',
        ]);
    }

    /**
     * Indicate that the affiliate is a distributor.
     */
    public function distributor(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'distributor',
        ]);
    }

    /**
     * Indicate that the affiliate is a consultant.
     */
    public function consultant(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'consultant',
        ]);
    }

    /**
     * Indicate that the affiliate has high performance.
     */
    public function highPerformance(): static
    {
        return $this->state(fn (array $attributes) => [
            'performance_rating' => $this->faker->numberBetween(4, 5),
        ]);
    }

    /**
     * Indicate that the affiliate has medium performance.
     */
    public function mediumPerformance(): static
    {
        return $this->state(fn (array $attributes) => [
            'performance_rating' => $this->faker->numberBetween(3, 4),
        ]);
    }

    /**
     * Indicate that the affiliate has low performance.
     */
    public function lowPerformance(): static
    {
        return $this->state(fn (array $attributes) => [
            'performance_rating' => $this->faker->numberBetween(1, 2),
        ]);
    }

    /**
     * Indicate that the affiliate has high commission rate.
     */
    public function highCommission(): static
    {
        return $this->state(fn (array $attributes) => [
            'commission_rate' => $this->faker->randomFloat(2, 15, 25),
        ]);
    }

    /**
     * Indicate that the affiliate has medium commission rate.
     */
    public function mediumCommission(): static
    {
        return $this->state(fn (array $attributes) => [
            'commission_rate' => $this->faker->randomFloat(2, 10, 15),
        ]);
    }

    /**
     * Indicate that the affiliate has low commission rate.
     */
    public function lowCommission(): static
    {
        return $this->state(fn (array $attributes) => [
            'commission_rate' => $this->faker->randomFloat(2, 5, 10),
        ]);
    }

    /**
     * Indicate that the affiliate has banking information.
     */
    public function withBankingInfo(): static
    {
        return $this->state(fn (array $attributes) => [
            'banking_info' => [
                'account_name' => $this->faker->company(),
                'account_number' => $this->faker->numerify('##########'),
                'bank_name' => $this->faker->company() . ' Bank',
                'routing_number' => $this->faker->numerify('#########'),
                'swift_code' => $this->faker->bothify('????##??'),
                'iban' => $this->faker->bothify('??##????##########'),
            ],
        ]);
    }

    /**
     * Indicate that the affiliate has tax information.
     */
    public function withTaxInfo(): static
    {
        return $this->state(fn (array $attributes) => [
            'tax_info' => [
                'tax_id' => $this->faker->numerify('##-#######'),
                'tax_exempt' => $this->faker->boolean(20),
                'tax_exemption_number' => $this->faker->optional(0.3)->numerify('TE-#######'),
            ],
        ]);
    }

    /**
     * Indicate that the affiliate has tags.
     */
    public function withTags(): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => $this->faker->words($this->faker->numberBetween(2, 5)),
        ]);
    }

    /**
     * Indicate that the affiliate has social media.
     */
    public function withSocialMedia(): static
    {
        return $this->state(fn (array $attributes) => [
            'social_media' => $this->faker->words($this->faker->numberBetween(1, 3)),
        ]);
    }

    /**
     * Indicate that the affiliate has attachments.
     */
    public function withAttachments(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachments' => $this->faker->words($this->faker->numberBetween(1, 3)),
        ]);
    }

    /**
     * Indicate that the affiliate has a long-term contract.
     */
    public function longTermContract(): static
    {
        return $this->state(fn (array $attributes) => [
            'contract_start_date' => now()->subMonths(6),
            'contract_end_date' => now()->addYears(3),
        ]);
    }

    /**
     * Indicate that the affiliate has a short-term contract.
     */
    public function shortTermContract(): static
    {
        return $this->state(fn (array $attributes) => [
            'contract_start_date' => now()->subMonths(1),
            'contract_end_date' => now()->addMonths(6),
        ]);
    }

    /**
     * Indicate that the affiliate has an expiring contract.
     */
    public function expiringContract(): static
    {
        return $this->state(fn (array $attributes) => [
            'contract_start_date' => now()->subMonths(11),
            'contract_end_date' => now()->addDays(30),
        ]);
    }

    /**
     * Indicate that the affiliate has an expired contract.
     */
    public function expiredContract(): static
    {
        return $this->state(fn (array $attributes) => [
            'contract_start_date' => now()->subMonths(12),
            'contract_end_date' => now()->subDays(30),
        ]);
    }

    /**
     * Indicate that the affiliate is energy-focused.
     */
    public function energyFocused(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement([
                'Solar Energy Partners',
                'Wind Power Solutions',
                'Hydro Energy Corp',
                'Geothermal Systems',
                'Biomass Energy Ltd',
                'Nuclear Power Partners',
                'Fossil Fuel Alternatives',
                'Renewable Energy Co',
            ]),
            'tags' => ['energy', 'renewable', 'sustainability'],
        ]);
    }

    /**
     * Indicate that the affiliate is technology-focused.
     */
    public function technologyFocused(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement([
                'Tech Solutions Inc',
                'Digital Innovations',
                'Smart Systems Corp',
                'AI Energy Solutions',
                'IoT Power Systems',
                'Blockchain Energy',
                'Cloud Energy Platform',
                'Data Center Solutions',
            ]),
            'tags' => ['technology', 'innovation', 'digital'],
        ]);
    }

    /**
     * Indicate that the affiliate is corporate.
     */
    public function corporate(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->company() . ' Corporation',
            'company_name' => $this->faker->company() . ' Corp',
            'type' => 'partner',
            'commission_rate' => $this->faker->randomFloat(2, 8, 15),
        ]);
    }

    /**
     * Indicate that the affiliate is a startup.
     */
    public function startup(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->company() . ' Startup',
            'company_name' => $this->faker->company() . ' Inc',
            'type' => 'consultant',
            'commission_rate' => $this->faker->randomFloat(2, 15, 25),
        ]);
    }

    /**
     * Indicate that the affiliate is international.
     */
    public function international(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => $this->faker->randomElement(['Germany', 'France', 'Japan', 'Canada', 'Australia', 'Brazil', 'India', 'China']),
            'currency' => $this->faker->randomElement(['EUR', 'JPY', 'CAD', 'AUD', 'BRL', 'INR', 'CNY']),
        ]);
    }

    /**
     * Indicate that the affiliate is local.
     */
    public function local(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'United States',
            'state' => $this->faker->state(),
            'city' => $this->faker->city(),
        ]);
    }
}
