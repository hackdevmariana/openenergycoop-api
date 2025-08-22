<?php

namespace Database\Factories;

use App\Models\EnergyBond;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EnergyBond>
 */
class EnergyBondFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EnergyBond::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bondTypes = ['solar', 'wind', 'hydro', 'biomass', 'geothermal', 'nuclear', 'hybrid', 'other'];
        $statuses = ['draft', 'pending', 'approved', 'active', 'inactive', 'expired', 'cancelled', 'rejected'];
        $priorities = ['low', 'medium', 'high', 'urgent', 'critical'];
        $couponFrequencies = ['monthly', 'quarterly', 'semi_annually', 'annually', 'at_maturity'];
        $paymentMethods = ['bank_transfer', 'credit_card', 'crypto', 'check', 'cash', 'other'];
        $currencies = ['EUR', 'USD', 'GBP', 'JPY', 'CHF'];
        $riskRatings = ['aaa', 'aa', 'a', 'bbb', 'bb', 'b', 'ccc', 'cc', 'c', 'd'];

        $faceValue = $this->faker->randomFloat(2, 1000, 1000000);
        $totalUnits = $this->faker->numberBetween(100, 100000);
        $availableUnits = $this->faker->numberBetween(0, $totalUnits);

        return [
            'name' => $this->faker->unique()->sentence(3, false),
            'description' => $this->faker->paragraph(3),
            'bond_type' => $this->faker->randomElement($bondTypes),
            'status' => $this->faker->randomElement($statuses),
            'priority' => $this->faker->randomElement($priorities),
            'face_value' => $faceValue,
            'interest_rate' => $this->faker->randomFloat(2, 0.5, 15.0),
            'maturity_date' => $this->faker->dateTimeBetween('+1 year', '+10 years'),
            'issue_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'coupon_frequency' => $this->faker->randomElement($couponFrequencies),
            'payment_method' => $this->faker->randomElement($paymentMethods),
            'currency' => $this->faker->randomElement($currencies),
            'total_units' => $totalUnits,
            'available_units' => $availableUnits,
            'minimum_investment' => $this->faker->randomFloat(2, 10, $faceValue * 0.1),
            'maximum_investment' => $this->faker->optional(0.7)->randomFloat(2, $faceValue * 0.1, $faceValue),
            'early_redemption_allowed' => $this->faker->boolean(80),
            'early_redemption_fee' => $this->faker->optional(0.6)->randomFloat(2, 0, 10),
            'transferable' => $this->faker->boolean(70),
            'transfer_fee' => $this->faker->optional(0.5)->randomFloat(2, 0, 5),
            'collateral_required' => $this->faker->boolean(30),
            'collateral_type' => $this->faker->optional(0.3)->sentence(2),
            'collateral_value' => $this->faker->optional(0.3)->randomFloat(2, 0, $faceValue * 0.5),
            'guarantee_provided' => $this->faker->boolean(40),
            'guarantor_name' => $this->faker->optional(0.4)->company(),
            'guarantee_amount' => $this->faker->optional(0.4)->randomFloat(2, 0, $faceValue * 0.3),
            'risk_rating' => $this->faker->randomElement($riskRatings),
            'credit_score_required' => $this->faker->optional(0.6)->numberBetween(300, 850),
            'income_requirement' => $this->faker->optional(0.5)->randomFloat(2, 0, 1000000),
            'employment_verification' => $this->faker->boolean(60),
            'bank_statement_required' => $this->faker->boolean(70),
            'tax_documentation_required' => $this->faker->boolean(80),
            'kyc_required' => $this->faker->boolean(90),
            'aml_check_required' => $this->faker->boolean(85),
            'is_public' => $this->faker->boolean(70),
            'is_featured' => $this->faker->boolean(20),
            'requires_approval' => $this->faker->boolean(80),
            'is_template' => $this->faker->boolean(15),
            'version' => $this->faker->optional(0.3)->semver(),
            'sort_order' => $this->faker->optional(0.4)->numberBetween(0, 1000),
            'organization_id' => Organization::factory(),
            'managed_by' => User::factory(),
            'approved_by' => $this->faker->optional(0.6)->randomElement([User::factory()]),
            'tags' => $this->faker->optional(0.7)->words($this->faker->numberBetween(1, 5)),
            'notes' => $this->faker->optional(0.5)->paragraph(2),
            'documents' => $this->faker->optional(0.3)->words($this->faker->numberBetween(1, 3)),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the bond is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'is_public' => true,
        ]);
    }

    /**
     * Indicate that the bond is pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'is_public' => false,
        ]);
    }

    /**
     * Indicate that the bond is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the bond is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Indicate that the bond is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    /**
     * Indicate that the bond is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'is_public' => true,
        ]);
    }

    /**
     * Indicate that the bond is solar type.
     */
    public function solar(): static
    {
        return $this->state(fn (array $attributes) => [
            'bond_type' => 'solar',
        ]);
    }

    /**
     * Indicate that the bond is wind type.
     */
    public function wind(): static
    {
        return $this->state(fn (array $attributes) => [
            'bond_type' => 'wind',
        ]);
    }

    /**
     * Indicate that the bond is hydro type.
     */
    public function hydro(): static
    {
        return $this->state(fn (array $attributes) => [
            'bond_type' => 'hydro',
        ]);
    }

    /**
     * Indicate that the bond has high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    /**
     * Indicate that the bond has urgent priority.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
        ]);
    }

    /**
     * Indicate that the bond has low risk rating.
     */
    public function lowRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_rating' => $this->faker->randomElement(['aaa', 'aa', 'a']),
        ]);
    }

    /**
     * Indicate that the bond has high risk rating.
     */
    public function highRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_rating' => $this->faker->randomElement(['ccc', 'cc', 'c', 'd']),
        ]);
    }

    /**
     * Indicate that the bond is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'maturity_date' => $this->faker->dateTimeBetween('-2 years', '-1 day'),
        ]);
    }

    /**
     * Indicate that the bond is a template.
     */
    public function template(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_template' => true,
            'status' => 'draft',
        ]);
    }

    /**
     * Indicate that the bond requires approval.
     */
    public function requiresApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_approval' => true,
        ]);
    }

    /**
     * Indicate that the bond has collateral.
     */
    public function withCollateral(): static
    {
        return $this->state(fn (array $attributes) => [
            'collateral_required' => true,
            'collateral_type' => $this->faker->randomElement(['real_estate', 'equipment', 'inventory', 'securities']),
            'collateral_value' => $this->faker->randomFloat(2, 1000, 500000),
        ]);
    }

    /**
     * Indicate that the bond has guarantee.
     */
    public function withGuarantee(): static
    {
        return $this->state(fn (array $attributes) => [
            'guarantee_provided' => true,
            'guarantor_name' => $this->faker->company(),
            'guarantee_amount' => $this->faker->randomFloat(2, 1000, 300000),
        ]);
    }

    /**
     * Indicate that the bond has high credit requirements.
     */
    public function highCreditRequirements(): static
    {
        return $this->state(fn (array $attributes) => [
            'credit_score_required' => $this->faker->numberBetween(700, 850),
            'income_requirement' => $this->faker->randomFloat(2, 50000, 500000),
            'employment_verification' => true,
            'bank_statement_required' => true,
            'tax_documentation_required' => true,
        ]);
    }

    /**
     * Indicate that the bond has low credit requirements.
     */
    public function lowCreditRequirements(): static
    {
        return $this->state(fn (array $attributes) => [
            'credit_score_required' => $this->faker->numberBetween(300, 600),
            'income_requirement' => $this->faker->randomFloat(2, 10000, 50000),
            'employment_verification' => false,
            'bank_statement_required' => false,
            'tax_documentation_required' => false,
        ]);
    }
}
