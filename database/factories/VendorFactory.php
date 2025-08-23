<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vendor>
 */
class VendorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Vendor::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $vendorTypes = array_keys(Vendor::getVendorTypes());
        $statuses = array_keys(Vendor::getStatuses());
        $riskLevels = array_keys(Vendor::getRiskLevels());
        $complianceStatuses = array_keys(Vendor::getComplianceStatuses());
        $industries = [
            'Technology', 'Healthcare', 'Energy', 'Manufacturing', 'Construction',
            'Transportation', 'Finance', 'Education', 'Retail', 'Food & Beverage',
            'Automotive', 'Aerospace', 'Pharmaceuticals', 'Telecommunications',
            'Real Estate', 'Entertainment', 'Sports', 'Fashion', 'Beauty',
            'Home & Garden', 'Pet Care', 'Legal Services', 'Consulting'
        ];
        $countries = [
            'Spain', 'France', 'Germany', 'Italy', 'United Kingdom',
            'Netherlands', 'Belgium', 'Switzerland', 'Austria', 'Sweden',
            'Norway', 'Denmark', 'Finland', 'Poland', 'Czech Republic',
            'Hungary', 'Slovakia', 'Slovenia', 'Croatia', 'Romania',
            'Bulgaria', 'Greece', 'Portugal', 'Ireland', 'Luxembourg'
        ];

        return [
            'name' => fake()->company(),
            'legal_name' => fake()->company() . ' S.L.',
            'tax_id' => 'B' . fake()->numberBetween(10000000, 99999999),
            'registration_number' => fake()->regexify('[A-Z]{2}[0-9]{8}'),
            'vendor_type' => fake()->randomElement($vendorTypes),
            'industry' => fake()->randomElement($industries),
            'description' => fake()->paragraph(3),
            'contact_person' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+' . fake()->numberBetween(1, 999) . ' ' . fake()->numberBetween(100000000, 999999999),
            'website' => fake()->url(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country' => fake()->randomElement($countries),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'payment_terms' => fake()->randomElement(['30 days', '60 days', '90 days', 'Net 30', 'Net 60', 'Net 90']),
            'credit_limit' => fake()->randomFloat(2, 1000, 1000000),
            'current_balance' => fake()->randomFloat(2, 0, 500000),
            'currency' => fake()->randomElement(['EUR', 'USD', 'GBP']),
            'tax_rate' => fake()->randomFloat(2, 0, 25),
            'discount_rate' => fake()->randomFloat(2, 0, 20),
            'rating' => fake()->randomFloat(1, 1, 5),
            'is_active' => fake()->boolean(80),
            'is_verified' => fake()->boolean(70),
            'is_preferred' => fake()->boolean(20),
            'is_blacklisted' => fake()->boolean(5),
            'contract_start_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'contract_end_date' => fake()->dateTimeBetween('now', '+2 years'),
            'contract_terms' => [
                'payment_terms' => fake()->randomElement(['30 days', '60 days', '90 days']),
                'delivery_terms' => fake()->randomElement(['FOB', 'CIF', 'EXW', 'DDP']),
                'warranty_period' => fake()->numberBetween(12, 60) . ' months',
                'penalty_clause' => fake()->boolean(30),
                'renewal_terms' => fake()->randomElement(['Automatic', 'Manual', 'Negotiable'])
            ],
            'insurance_coverage' => [
                'general_liability' => fake()->numberBetween(1000000, 10000000),
                'professional_liability' => fake()->numberBetween(500000, 5000000),
                'workers_compensation' => fake()->numberBetween(100000, 1000000),
                'property_damage' => fake()->numberBetween(500000, 5000000),
                'cyber_liability' => fake()->numberBetween(100000, 2000000)
            ],
            'certifications' => [
                'iso_9001' => fake()->boolean(60),
                'iso_14001' => fake()->boolean(40),
                'iso_45001' => fake()->boolean(30),
                'ce_marking' => fake()->boolean(50),
                'fda_approval' => fake()->boolean(20),
                'other_certifications' => fake()->sentences(2, true)
            ],
            'licenses' => [
                'business_license' => fake()->regexify('[A-Z]{2}[0-9]{6}'),
                'professional_license' => fake()->regexify('[A-Z]{3}[0-9]{5}'),
                'export_license' => fake()->boolean(30),
                'import_license' => fake()->boolean(30),
                'specialized_permits' => fake()->sentences(1, true)
            ],
            'performance_metrics' => [
                'on_time_delivery' => fake()->randomFloat(2, 85, 99),
                'quality_score' => fake()->randomFloat(2, 80, 100),
                'response_time_hours' => fake()->numberBetween(1, 48),
                'customer_satisfaction' => fake()->randomFloat(2, 3, 5),
                'defect_rate' => fake()->randomFloat(2, 0, 5)
            ],
            'quality_standards' => [
                'quality_management_system' => fake()->boolean(80),
                'quality_control_procedures' => fake()->boolean(90),
                'testing_capabilities' => fake()->boolean(70),
                'documentation_standards' => fake()->boolean(85),
                'continuous_improvement' => fake()->boolean(75)
            ],
            'delivery_terms' => [
                'delivery_method' => fake()->randomElement(['Ground', 'Air', 'Sea', 'Express']),
                'delivery_time' => fake()->numberBetween(1, 30) . ' days',
                'packaging_requirements' => fake()->sentences(2, true),
                'handling_instructions' => fake()->sentences(1, true),
                'delivery_areas' => fake()->sentences(1, true)
            ],
            'warranty_terms' => [
                'warranty_period' => fake()->numberBetween(12, 60) . ' months',
                'warranty_coverage' => fake()->sentences(2, true),
                'exclusions' => fake()->sentences(1, true),
                'claim_process' => fake()->sentences(2, true),
                'extended_warranty' => fake()->boolean(40)
            ],
            'return_policy' => [
                'return_period' => fake()->numberBetween(7, 90) . ' days',
                'return_conditions' => fake()->sentences(2, true),
                'restocking_fee' => fake()->randomFloat(2, 0, 25),
                'return_shipping' => fake()->randomElement(['Buyer pays', 'Seller pays', 'Split cost']),
                'refund_method' => fake()->randomElement(['Full refund', 'Partial refund', 'Credit note'])
            ],
            'notes' => fake()->paragraph(2),
            'tags' => fake()->words(3, true),
            'logo' => fake()->imageUrl(200, 200, 'business'),
            'documents' => [
                'contract_template' => fake()->filePath(),
                'pricing_sheet' => fake()->filePath(),
                'catalog' => fake()->filePath(),
                'terms_conditions' => fake()->filePath(),
                'privacy_policy' => fake()->filePath()
            ],
            'bank_account' => [
                'bank_name' => fake()->company(),
                'account_number' => fake()->bankAccountNumber(),
                'swift_code' => fake()->regexify('[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?'),
                'iban' => fake()->regexify('[A-Z]{2}[0-9]{2}[A-Z0-9]{4}[0-9]{7}([A-Z0-9]?){0,16}'),
                'account_holder' => fake()->company()
            ],
            'payment_methods' => [
                'bank_transfer' => true,
                'credit_card' => fake()->boolean(70),
                'paypal' => fake()->boolean(50),
                'check' => fake()->boolean(30),
                'cash' => fake()->boolean(20),
                'cryptocurrency' => fake()->boolean(10)
            ],
            'contact_history' => [
                [
                    'date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
                    'type' => fake()->randomElement(['email', 'phone', 'meeting', 'site_visit']),
                    'contact_person' => fake()->name(),
                    'summary' => fake()->sentence(),
                    'follow_up_required' => fake()->boolean(30)
                ],
                [
                    'date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
                    'type' => fake()->randomElement(['email', 'phone', 'meeting', 'site_visit']),
                    'contact_person' => fake()->name(),
                    'summary' => fake()->sentence(),
                    'follow_up_required' => fake()->boolean(30)
                ]
            ],
            'status' => fake()->randomElement($statuses),
            'risk_level' => fake()->randomElement($riskLevels),
            'compliance_status' => fake()->randomElement($complianceStatuses),
            'audit_frequency' => fake()->numberBetween(30, 365),
            'last_audit_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'next_audit_date' => fake()->dateTimeBetween('now', '+1 year'),
            'financial_stability' => [
                'credit_rating' => fake()->randomElement(['AAA', 'AA', 'A', 'BBB', 'BB', 'B', 'CCC']),
                'financial_strength' => fake()->randomElement(['Excellent', 'Good', 'Fair', 'Poor']),
                'payment_history' => fake()->randomElement(['Excellent', 'Good', 'Fair', 'Poor']),
                'debt_ratio' => fake()->randomFloat(2, 0, 2),
                'profit_margin' => fake()->randomFloat(2, 5, 25)
            ],
            'market_reputation' => [
                'market_position' => fake()->randomElement(['Leader', 'Challenger', 'Follower', 'Niche']),
                'brand_recognition' => fake()->randomElement(['High', 'Medium', 'Low']),
                'customer_loyalty' => fake()->randomElement(['High', 'Medium', 'Low']),
                'industry_respect' => fake()->randomElement(['High', 'Medium', 'Low']),
                'awards_recognition' => fake()->sentences(1, true)
            ],
            'competitor_analysis' => [
                'main_competitors' => fake()->sentences(2, true),
                'competitive_advantages' => fake()->sentences(2, true),
                'market_share' => fake()->randomFloat(2, 1, 50) . '%',
                'price_positioning' => fake()->randomElement(['Premium', 'Mid-market', 'Budget']),
                'differentiation_factors' => fake()->sentences(2, true)
            ],
            'strategic_importance' => [
                'business_critical' => fake()->boolean(30),
                'revenue_contribution' => fake()->randomFloat(2, 1, 50) . '%',
                'cost_savings_potential' => fake()->randomFloat(2, 5, 30) . '%',
                'innovation_potential' => fake()->randomElement(['High', 'Medium', 'Low']),
                'partnership_potential' => fake()->randomElement(['High', 'Medium', 'Low'])
            ],
            'dependencies' => [
                'single_source' => fake()->boolean(20),
                'alternative_sources' => fake()->sentences(1, true),
                'switching_costs' => fake()->randomElement(['High', 'Medium', 'Low']),
                'lead_time_impact' => fake()->randomElement(['Critical', 'Important', 'Minor']),
                'quality_impact' => fake()->randomElement(['Critical', 'Important', 'Minor'])
            ],
            'alternatives' => [
                'competitor_products' => fake()->sentences(2, true),
                'substitute_technologies' => fake()->sentences(1, true),
                'in_house_capabilities' => fake()->boolean(40),
                'other_vendors' => fake()->sentences(2, true),
                'evaluation_status' => fake()->randomElement(['Evaluated', 'Under Review', 'Not Considered'])
            ],
            'cost_benefit_analysis' => [
                'total_cost_ownership' => fake()->randomFloat(2, 10000, 1000000),
                'cost_savings' => fake()->randomFloat(2, 5000, 500000),
                'roi_estimate' => fake()->randomFloat(2, 10, 200) . '%',
                'payback_period' => fake()->numberBetween(6, 60) . ' months',
                'risk_adjusted_return' => fake()->randomFloat(2, 5, 150) . '%'
            ],
            'performance_reviews' => [
                'last_review_date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
                'overall_rating' => fake()->randomElement(['Excellent', 'Good', 'Satisfactory', 'Needs Improvement', 'Unsatisfactory']),
                'strengths' => fake()->sentences(2, true),
                'areas_for_improvement' => fake()->sentences(2, true),
                'action_items' => fake()->sentences(2, true),
                'next_review_date' => fake()->dateTimeBetween('now', '+1 year')->format('Y-m-d')
            ],
            'improvement_plans' => [
                'current_phase' => fake()->randomElement(['Planning', 'Implementation', 'Monitoring', 'Completed']),
                'target_areas' => fake()->sentences(2, true),
                'timeline' => fake()->numberBetween(3, 24) . ' months',
                'resources_required' => fake()->sentences(1, true),
                'success_metrics' => fake()->sentences(2, true),
                'progress_percentage' => fake()->numberBetween(0, 100)
            ],
            'escalation_procedures' => [
                'escalation_levels' => fake()->sentences(2, true),
                'contact_hierarchy' => fake()->sentences(2, true),
                'response_timeframes' => fake()->sentences(1, true),
                'resolution_protocols' => fake()->sentences(2, true),
                'emergency_contacts' => fake()->sentences(1, true)
            ],
            'created_by' => User::factory(),
            'approved_by' => fake()->optional(0.7)->passthrough(User::factory()),
            'approved_at' => fake()->optional(0.7)->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the vendor is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the vendor is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the vendor is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
        ]);
    }

    /**
     * Indicate that the vendor is unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => false,
        ]);
    }

    /**
     * Indicate that the vendor is preferred.
     */
    public function preferred(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_preferred' => true,
            'is_verified' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the vendor is blacklisted.
     */
    public function blacklisted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_blacklisted' => true,
            'is_active' => false,
            'is_preferred' => false,
        ]);
    }

    /**
     * Indicate that the vendor has high risk.
     */
    public function highRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_level' => 'high',
        ]);
    }

    /**
     * Indicate that the vendor has extreme risk.
     */
    public function extremeRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_level' => 'extreme',
        ]);
    }

    /**
     * Indicate that the vendor is compliant.
     */
    public function compliant(): static
    {
        return $this->state(fn (array $attributes) => [
            'compliance_status' => 'compliant',
        ]);
    }

    /**
     * Indicate that the vendor is non-compliant.
     */
    public function nonCompliant(): static
    {
        return $this->state(fn (array $attributes) => [
            'compliance_status' => 'non_compliant',
        ]);
    }

    /**
     * Indicate that the vendor needs audit.
     */
    public function needsAudit(): static
    {
        return $this->state(fn (array $attributes) => [
            'compliance_status' => 'needs_audit',
        ]);
    }

    /**
     * Indicate that the vendor has high rating.
     */
    public function highRating(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => fake()->randomFloat(1, 4.0, 5.0),
        ]);
    }

    /**
     * Indicate that the vendor has low rating.
     */
    public function lowRating(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => fake()->randomFloat(1, 1.0, 3.0),
        ]);
    }

    /**
     * Indicate that the vendor has approved status.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_at' => now(),
            'approved_by' => User::factory(),
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the vendor has pending approval.
     */
    public function pendingApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_at' => null,
            'approved_by' => null,
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the vendor has active contract.
     */
    public function activeContract(): static
    {
        return $this->state(fn (array $attributes) => [
            'contract_start_date' => now()->subMonths(6),
            'contract_end_date' => now()->addMonths(6),
        ]);
    }

    /**
     * Indicate that the vendor has expiring contract.
     */
    public function expiringContract(): static
    {
        return $this->state(fn (array $attributes) => [
            'contract_start_date' => now()->subMonths(11),
            'contract_end_date' => now()->addDays(30),
        ]);
    }

    /**
     * Indicate that the vendor has expired contract.
     */
    public function expiredContract(): static
    {
        return $this->state(fn (array $attributes) => [
            'contract_start_date' => now()->subMonths(12),
            'contract_end_date' => now()->subDays(30),
        ]);
    }

    /**
     * Indicate that the vendor needs audit soon.
     */
    public function needsAuditSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'next_audit_date' => now()->addDays(30),
        ]);
    }

    /**
     * Indicate that the vendor has overdue audit.
     */
    public function overdueAudit(): static
    {
        return $this->state(fn (array $attributes) => [
            'next_audit_date' => now()->subDays(30),
        ]);
    }

    /**
     * Indicate that the vendor has high credit limit.
     */
    public function highCreditLimit(): static
    {
        return $this->state(fn (array $attributes) => [
            'credit_limit' => fake()->randomFloat(2, 500000, 2000000),
        ]);
    }

    /**
     * Indicate that the vendor has low credit limit.
     */
    public function lowCreditLimit(): static
    {
        return $this->state(fn (array $attributes) => [
            'credit_limit' => fake()->randomFloat(2, 1000, 50000),
        ]);
    }

    /**
     * Indicate that the vendor has high credit utilization.
     */
    public function highCreditUtilization(): static
    {
        return $this->state(fn (array $attributes) => [
            'credit_limit' => 100000,
            'current_balance' => 90000,
        ]);
    }

    /**
     * Indicate that the vendor has low credit utilization.
     */
    public function lowCreditUtilization(): static
    {
        return $this->state(fn (array $attributes) => [
            'credit_limit' => 100000,
            'current_balance' => 10000,
        ]);
    }

    /**
     * Indicate that the vendor is equipment supplier.
     */
    public function equipmentSupplier(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'equipment_supplier',
        ]);
    }

    /**
     * Indicate that the vendor is service provider.
     */
    public function serviceProvider(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'service_provider',
        ]);
    }

    /**
     * Indicate that the vendor is material supplier.
     */
    public function materialSupplier(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'material_supplier',
        ]);
    }

    /**
     * Indicate that the vendor is consultant.
     */
    public function consultant(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'consultant',
        ]);
    }

    /**
     * Indicate that the vendor is contractor.
     */
    public function contractor(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'contractor',
        ]);
    }

    /**
     * Indicate that the vendor is in technology industry.
     */
    public function technologyIndustry(): static
    {
        return $this->state(fn (array $attributes) => [
            'industry' => 'Technology',
        ]);
    }

    /**
     * Indicate that the vendor is in healthcare industry.
     */
    public function healthcareIndustry(): static
    {
        return $this->state(fn (array $attributes) => [
            'industry' => 'Healthcare',
        ]);
    }

    /**
     * Indicate that the vendor is in energy industry.
     */
    public function energyIndustry(): static
    {
        return $this->state(fn (array $attributes) => [
            'industry' => 'Energy',
        ]);
    }

    /**
     * Indicate that the vendor is in manufacturing industry.
     */
    public function manufacturingIndustry(): static
    {
        return $this->state(fn (array $attributes) => [
            'industry' => 'Manufacturing',
        ]);
    }

    /**
     * Indicate that the vendor is in Spain.
     */
    public function inSpain(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'Spain',
        ]);
    }

    /**
     * Indicate that the vendor is in France.
     */
    public function inFrance(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'France',
        ]);
    }

    /**
     * Indicate that the vendor is in Germany.
     */
    public function inGermany(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'Germany',
        ]);
    }

    /**
     * Indicate that the vendor has premium pricing.
     */
    public function premiumPricing(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => fake()->randomFloat(1, 4.5, 5.0),
            'is_preferred' => true,
            'risk_level' => 'low',
        ]);
    }

    /**
     * Indicate that the vendor has budget pricing.
     */
    public function budgetPricing(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => fake()->randomFloat(1, 2.5, 4.0),
            'is_preferred' => false,
            'risk_level' => 'medium',
        ]);
    }

    /**
     * Indicate that the vendor is newly created.
     */
    public function newlyCreated(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => now()->subDays(7),
            'status' => 'pending',
            'compliance_status' => 'pending_review',
            'is_verified' => false,
            'is_preferred' => false,
        ]);
    }

    /**
     * Indicate that the vendor is established.
     */
    public function established(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => now()->subYears(3),
            'status' => 'active',
            'compliance_status' => 'compliant',
            'is_verified' => true,
            'approved_at' => now()->subYears(2),
        ]);
    }

    /**
     * Indicate that the vendor is struggling.
     */
    public function struggling(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => fake()->randomFloat(1, 2.0, 3.5),
            'risk_level' => 'high',
            'compliance_status' => 'needs_audit',
            'is_active' => true,
            'is_verified' => false,
        ]);
    }

    /**
     * Indicate that the vendor is excellent performer.
     */
    public function excellentPerformer(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => fake()->randomFloat(1, 4.5, 5.0),
            'risk_level' => 'low',
            'compliance_status' => 'compliant',
            'is_active' => true,
            'is_verified' => true,
            'is_preferred' => true,
        ]);
    }
}
