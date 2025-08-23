<?php

namespace Database\Factories;

use App\Models\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'legal_name' => $this->faker->company() . ' S.L.',
            'tax_id' => $this->faker->unique()->regexify('[A-Z]\d{8}'),
            'registration_number' => $this->faker->unique()->regexify('[A-Z]\d{7}'),
            'vendor_type' => $this->faker->randomElement(array_keys(Vendor::getVendorTypes())),
            'industry' => $this->faker->randomElement(['energy', 'technology', 'manufacturing', 'services', 'construction', 'healthcare', 'finance', 'retail', 'transportation', 'agriculture']),
            'description' => $this->faker->paragraph(),
            'contact_person' => $this->faker->name(),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'website' => $this->faker->url(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'postal_code' => $this->faker->postcode(),
            'country' => $this->faker->country(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'payment_terms' => $this->faker->randomElement(['30 days', '60 days', '90 days', 'Net 30', 'Net 60', 'Net 90']),
            'credit_limit' => $this->faker->randomFloat(2, 1000, 100000),
            'current_balance' => $this->faker->randomFloat(2, 0, 50000),
            'currency' => $this->faker->randomElement(['EUR', 'USD', 'GBP']),
            'tax_rate' => $this->faker->randomFloat(2, 0, 25),
            'discount_rate' => $this->faker->randomFloat(2, 0, 15),
            'rating' => $this->faker->randomFloat(1, 1, 5),
            'is_active' => $this->faker->boolean(80),
            'is_verified' => $this->faker->boolean(70),
            'is_preferred' => $this->faker->boolean(20),
            'is_blacklisted' => $this->faker->boolean(5),
            'contract_start_date' => $this->faker->optional()->dateTimeBetween('-2 years', 'now'),
            'contract_end_date' => $this->faker->optional()->dateTimeBetween('now', '+2 years'),
            'contract_terms' => [
                'payment_terms' => $this->faker->randomElement(['30 days', '60 days', '90 days']),
                'delivery_terms' => $this->faker->randomElement(['FOB', 'CIF', 'EXW']),
                'warranty_period' => $this->faker->randomElement(['1 year', '2 years', '3 years']),
            ],
            'insurance_coverage' => [
                'general_liability' => $this->faker->randomFloat(2, 1000000, 5000000),
                'professional_liability' => $this->faker->randomFloat(2, 500000, 2000000),
                'workers_compensation' => $this->faker->randomFloat(2, 100000, 1000000),
            ],
            'certifications' => $this->faker->randomElements([
                'ISO 9001', 'ISO 14001', 'OHSAS 18001', 'ISO 27001', 'CE Marking',
                'FDA Approval', 'UL Certification', 'RoHS Compliance'
            ], $this->faker->numberBetween(0, 4)),
            'licenses' => $this->faker->randomElements([
                'Business License', 'Professional License', 'Trade License',
                'Import/Export License', 'Specialized Industry License'
            ], $this->faker->numberBetween(0, 3)),
            'performance_metrics' => [
                'on_time_delivery' => $this->faker->randomFloat(2, 85, 100),
                'quality_rating' => $this->faker->randomFloat(2, 90, 100),
                'response_time' => $this->faker->randomFloat(2, 1, 48),
                'cost_effectiveness' => $this->faker->randomFloat(2, 80, 100),
            ],
            'quality_standards' => [
                'quality_management' => $this->faker->randomElement(['ISO 9001', 'Six Sigma', 'Lean Manufacturing']),
                'environmental_management' => $this->faker->randomElement(['ISO 14001', 'Green Business', 'Carbon Neutral']),
                'safety_standards' => $this->faker->randomElement(['OHSAS 18001', 'OSHA Compliance', 'Safety First']),
            ],
            'delivery_terms' => [
                'delivery_method' => $this->faker->randomElement(['Standard Shipping', 'Express Delivery', 'Local Pickup']),
                'delivery_time' => $this->faker->randomElement(['1-3 days', '3-5 days', '5-7 days', '1-2 weeks']),
                'delivery_cost' => $this->faker->randomFloat(2, 0, 100),
            ],
            'warranty_terms' => [
                'warranty_period' => $this->faker->randomElement(['1 year', '2 years', '3 years', '5 years']),
                'warranty_coverage' => $this->faker->randomElement(['Parts and Labor', 'Parts Only', 'Limited Warranty']),
                'warranty_exclusions' => $this->faker->randomElements([
                    'Normal wear and tear', 'Improper installation', 'Unauthorized modifications'
                ], $this->faker->numberBetween(1, 3)),
            ],
            'return_policy' => [
                'return_period' => $this->faker->randomElement(['30 days', '60 days', '90 days']),
                'return_conditions' => $this->faker->randomElement(['Original packaging', 'Unused condition', 'With receipt']),
                'restocking_fee' => $this->faker->randomFloat(2, 0, 25),
            ],
            'notes' => $this->faker->optional()->paragraph(),
            'tags' => $this->faker->randomElements([
                'reliable', 'high-quality', 'fast-delivery', 'cost-effective', 'innovative',
                'sustainable', 'local', 'certified', 'premium', 'budget-friendly'
            ], $this->faker->numberBetween(0, 5)),
            'logo' => $this->faker->optional()->imageUrl(200, 200, 'business'),
            'documents' => $this->faker->optional()->randomElements([
                'contract.pdf', 'certificate.pdf', 'license.pdf', 'insurance.pdf'
            ], $this->faker->numberBetween(0, 3)),
            'bank_account' => [
                'bank_name' => $this->faker->company(),
                'account_number' => $this->faker->bankAccountNumber(),
                'swift_code' => $this->faker->regexify('[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?'),
                'iban' => $this->faker->regexify('[A-Z]{2}[0-9]{2}[A-Z0-9]{4}[0-9]{7}([A-Z0-9]?){0,16}'),
            ],
            'payment_methods' => $this->faker->randomElements([
                'Bank Transfer', 'Credit Card', 'PayPal', 'Check', 'Cash on Delivery'
            ], $this->faker->numberBetween(1, 3)),
            'contact_history' => [
                'last_contact' => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d H:i:s'),
                'contact_method' => $this->faker->randomElement(['Email', 'Phone', 'Meeting', 'Video Call']),
                'contact_notes' => $this->faker->sentence(),
            ],
            'created_by' => User::factory(),
            'approved_by' => $this->faker->optional()->randomElement([User::factory(), null]),
            'approved_at' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
            'status' => $this->faker->randomElement(array_keys(Vendor::getStatuses())),
            'risk_level' => $this->faker->randomElement(array_keys(Vendor::getRiskLevels())),
            'compliance_status' => $this->faker->randomElement(array_keys(Vendor::getComplianceStatuses())),
            'audit_frequency' => $this->faker->randomElement([30, 60, 90, 180, 365]),
            'last_audit_date' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
            'next_audit_date' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
            'financial_stability' => [
                'credit_score' => $this->faker->numberBetween(300, 850),
                'financial_health' => $this->faker->randomElement(['Excellent', 'Good', 'Fair', 'Poor']),
                'payment_history' => $this->faker->randomElement(['Always on time', 'Usually on time', 'Sometimes late', 'Often late']),
            ],
            'market_reputation' => [
                'market_position' => $this->faker->randomElement(['Market Leader', 'Strong Competitor', 'Established Player', 'Emerging Company']),
                'customer_satisfaction' => $this->faker->randomFloat(2, 70, 100),
                'industry_recognition' => $this->faker->randomElements([
                    'Best Supplier Award', 'Quality Excellence', 'Innovation Award', 'Customer Choice'
                ], $this->faker->numberBetween(0, 2)),
            ],
            'competitor_analysis' => [
                'main_competitors' => $this->faker->randomElements([
                    'Competitor A', 'Competitor B', 'Competitor C', 'Competitor D'
                ], $this->faker->numberBetween(1, 3)),
                'competitive_advantages' => $this->faker->randomElements([
                    'Lower prices', 'Better quality', 'Faster delivery', 'Better service', 'Innovation'
                ], $this->faker->numberBetween(1, 3)),
                'market_share' => $this->faker->randomFloat(2, 5, 30),
            ],
            'strategic_importance' => [
                'business_critical' => $this->faker->boolean(30),
                'supply_chain_position' => $this->faker->randomElement(['Primary', 'Secondary', 'Backup', 'Alternative']),
                'strategic_value' => $this->faker->randomElement(['High', 'Medium', 'Low']),
            ],
            'dependencies' => [
                'supplier_dependencies' => $this->faker->randomElements([
                    'Raw materials', 'Components', 'Packaging', 'Transportation'
                ], $this->faker->numberBetween(0, 3)),
                'technology_dependencies' => $this->faker->randomElements([
                    'Software platforms', 'Hardware systems', 'Cloud services', 'Communication tools'
                ], $this->faker->numberBetween(0, 2)),
            ],
            'alternatives' => [
                'alternative_suppliers' => $this->faker->randomElements([
                    'Alternative A', 'Alternative B', 'Alternative C'
                ], $this->faker->numberBetween(0, 2)),
                'switching_costs' => $this->faker->randomElement(['Low', 'Medium', 'High']),
                'evaluation_status' => $this->faker->randomElement(['Not evaluated', 'Under evaluation', 'Evaluated', 'Approved']),
            ],
            'cost_benefit_analysis' => [
                'total_cost' => $this->faker->randomFloat(2, 1000, 100000),
                'benefits' => $this->faker->randomElements([
                    'Cost savings', 'Quality improvement', 'Faster delivery', 'Better service'
                ], $this->faker->numberBetween(1, 3)),
                'roi' => $this->faker->randomFloat(2, 10, 200),
            ],
            'performance_reviews' => [
                'last_review_date' => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
                'review_score' => $this->faker->randomFloat(2, 1, 5),
                'strengths' => $this->faker->randomElements([
                    'Reliability', 'Quality', 'Communication', 'Innovation', 'Cost-effectiveness'
                ], $this->faker->numberBetween(1, 3)),
                'areas_for_improvement' => $this->faker->randomElements([
                    'Response time', 'Documentation', 'Training', 'Technology adoption'
                ], $this->faker->numberBetween(0, 2)),
            ],
            'improvement_plans' => [
                'action_items' => $this->faker->randomElements([
                    'Improve response time', 'Enhance quality control', 'Better communication',
                    'Technology upgrade', 'Staff training'
                ], $this->faker->numberBetween(1, 3)),
                'timeline' => $this->faker->randomElement(['30 days', '60 days', '90 days', '6 months']),
                'responsible_party' => $this->faker->name(),
            ],
            'escalation_procedures' => [
                'escalation_levels' => $this->faker->randomElements([
                    'Level 1: Vendor Manager', 'Level 2: Procurement Director',
                    'Level 3: C-Level Executive'
                ], $this->faker->numberBetween(1, 3)),
                'response_times' => [
                    'Level 1' => '24 hours',
                    'Level 2' => '48 hours',
                    'Level 3' => '72 hours'
                ],
                'contact_information' => [
                    'emergency_phone' => $this->faker->phoneNumber(),
                    'emergency_email' => $this->faker->email(),
                ],
            ],
        ];
    }

    public function supplier(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'supplier',
        ]);
    }

    public function serviceProvider(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'service_provider',
        ]);
    }

    public function contractor(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'contractor',
        ]);
    }

    public function consultant(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'consultant',
        ]);
    }

    public function manufacturer(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'manufacturer',
        ]);
    }

    public function distributor(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'distributor',
        ]);
    }

    public function wholesaler(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'wholesaler',
        ]);
    }

    public function retailer(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'retailer',
        ]);
    }

    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'maintenance',
        ]);
    }

    public function itServices(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'it_services',
        ]);
    }

    public function financial(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'financial',
        ]);
    }

    public function insurance(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'insurance',
        ]);
    }

    public function legal(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'legal',
        ]);
    }

    public function marketing(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'marketing',
        ]);
    }

    public function transportation(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'transportation',
        ]);
    }

    public function wasteManagement(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'waste_management',
        ]);
    }

    public function security(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'security',
        ]);
    }

    public function cleaning(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'cleaning',
        ]);
    }

    public function catering(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'catering',
        ]);
    }

    public function other(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_type' => 'other',
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'status' => 'active',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'status' => 'inactive',
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => false,
        ]);
    }

    public function preferred(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_preferred' => true,
            'rating' => $this->faker->randomFloat(1, 4.0, 5.0),
        ]);
    }

    public function blacklisted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_blacklisted' => true,
            'is_active' => false,
            'status' => 'suspended',
            'rating' => $this->faker->randomFloat(1, 1.0, 2.0),
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_at' => now(),
            'approved_by' => User::factory(),
            'status' => 'approved',
        ]);
    }

    public function pendingApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_at' => null,
            'approved_by' => null,
            'status' => 'pending',
        ]);
    }

    public function lowRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_level' => 'low',
            'rating' => $this->faker->randomFloat(1, 4.0, 5.0),
        ]);
    }

    public function mediumRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_level' => 'medium',
            'rating' => $this->faker->randomFloat(1, 3.0, 4.5),
        ]);
    }

    public function highRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_level' => 'high',
            'rating' => $this->faker->randomFloat(1, 2.0, 3.5),
        ]);
    }

    public function extremeRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_level' => 'extreme',
            'rating' => $this->faker->randomFloat(1, 1.0, 2.5),
        ]);
    }

    public function compliant(): static
    {
        return $this->state(fn (array $attributes) => [
            'compliance_status' => 'compliant',
        ]);
    }

    public function nonCompliant(): static
    {
        return $this->state(fn (array $attributes) => [
            'compliance_status' => 'non_compliant',
        ]);
    }

    public function pendingReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'compliance_status' => 'pending_review',
        ]);
    }

    public function underInvestigation(): static
    {
        return $this->state(fn (array $attributes) => [
            'compliance_status' => 'under_investigation',
        ]);
    }

    public function complianceApproved(): static
    {
        return $this->state(fn (array $attributes) => [
            'compliance_status' => 'approved',
        ]);
    }

    public function complianceRejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'compliance_status' => 'rejected',
        ]);
    }

    public function highRating(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => $this->faker->randomFloat(1, 4.0, 5.0),
        ]);
    }

    public function lowRating(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => $this->faker->randomFloat(1, 1.0, 3.0),
        ]);
    }

    public function withActiveContract(): static
    {
        return $this->state(fn (array $attributes) => [
            'contract_start_date' => now()->subMonth(),
            'contract_end_date' => now()->addMonth(),
        ]);
    }

    public function withExpiringContract(): static
    {
        return $this->state(fn (array $attributes) => [
            'contract_start_date' => now()->subYear(),
            'contract_end_date' => now()->addDays(15),
        ]);
    }

    public function withExpiredContract(): static
    {
        return $this->state(fn (array $attributes) => [
            'contract_start_date' => now()->subYear(),
            'contract_end_date' => now()->subMonth(),
        ]);
    }

    public function needsAudit(): static
    {
        return $this->state(fn (array $attributes) => [
            'next_audit_date' => now()->subDays(5),
        ]);
    }

    public function auditDueSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'next_audit_date' => now()->addDays(15),
        ]);
    }

    public function withHighCredit(): static
    {
        return $this->state(fn (array $attributes) => [
            'credit_limit' => $this->faker->randomFloat(2, 50000, 200000),
            'current_balance' => $this->faker->randomFloat(2, 0, 10000),
        ]);
    }

    public function withLowCredit(): static
    {
        return $this->state(fn (array $attributes) => [
            'credit_limit' => $this->faker->randomFloat(2, 1000, 10000),
            'current_balance' => $this->faker->randomFloat(2, 0, 5000),
        ]);
    }

    public function withHighUtilization(): static
    {
        return $this->state(fn (array $attributes) => [
            'credit_limit' => 10000,
            'current_balance' => 9500,
        ]);
    }

    public function energyIndustry(): static
    {
        return $this->state(fn (array $attributes) => [
            'industry' => 'energy',
        ]);
    }

    public function technologyIndustry(): static
    {
        return $this->state(fn (array $attributes) => [
            'industry' => 'technology',
        ]);
    }

    public function manufacturingIndustry(): static
    {
        return $this->state(fn (array $attributes) => [
            'industry' => 'manufacturing',
        ]);
    }

    public function servicesIndustry(): static
    {
        return $this->state(fn (array $attributes) => [
            'industry' => 'services',
        ]);
    }

    public function constructionIndustry(): static
    {
        return $this->state(fn (array $attributes) => [
            'industry' => 'construction',
        ]);
    }

    public function healthcareIndustry(): static
    {
        return $this->state(fn (array $attributes) => [
            'industry' => 'healthcare',
        ]);
    }

    public function financeIndustry(): static
    {
        return $this->state(fn (array $attributes) => [
            'industry' => 'finance',
        ]);
    }

    public function retailIndustry(): static
    {
        return $this->state(fn (array $attributes) => [
            'industry' => 'retail',
        ]);
    }

    public function transportationIndustry(): static
    {
        return $this->state(fn (array $attributes) => [
            'industry' => 'transportation',
        ]);
    }

    public function agricultureIndustry(): static
    {
        return $this->state(fn (array $attributes) => [
            'industry' => 'agriculture',
        ]);
    }

    public function spainLocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'Spain',
            'state' => 'Madrid',
            'city' => 'Madrid',
        ]);
    }

    public function usaLocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'United States',
            'state' => 'California',
            'city' => 'Los Angeles',
        ]);
    }

    public function germanyLocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'Germany',
            'state' => 'Bavaria',
            'city' => 'Munich',
        ]);
    }

    public function ukLocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'United Kingdom',
            'state' => 'England',
            'city' => 'London',
        ]);
    }

    public function franceLocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'France',
            'state' => 'Île-de-France',
            'city' => 'Paris',
        ]);
    }

    public function italyLocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'Italy',
            'state' => 'Lazio',
            'city' => 'Rome',
        ]);
    }

    public function netherlandsLocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'Netherlands',
            'state' => 'North Holland',
            'city' => 'Amsterdam',
        ]);
    }

    public function belgiumLocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'Belgium',
            'state' => 'Brussels',
            'city' => 'Brussels',
        ]);
    }

    public function switzerlandLocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'Switzerland',
            'state' => 'Zurich',
            'city' => 'Zurich',
        ]);
    }

    public function austriaLocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'Austria',
            'state' => 'Vienna',
            'city' => 'Vienna',
        ]);
    }

    public function withCertifications(): static
    {
        return $this->state(fn (array $attributes) => [
            'certifications' => ['ISO 9001', 'ISO 14001', 'OHSAS 18001'],
        ]);
    }

    public function withInsurance(): static
    {
        return $this->state(fn (array $attributes) => [
            'insurance_coverage' => [
                'general_liability' => 5000000,
                'professional_liability' => 2000000,
                'workers_compensation' => 1000000,
            ],
        ]);
    }

    public function withPerformanceMetrics(): static
    {
        return $this->state(fn (array $attributes) => [
            'performance_metrics' => [
                'on_time_delivery' => 98.5,
                'quality_rating' => 96.2,
                'response_time' => 2.5,
                'cost_effectiveness' => 92.8,
            ],
        ]);
    }

    public function withQualityStandards(): static
    {
        return $this->state(fn (array $attributes) => [
            'quality_standards' => [
                'quality_management' => 'ISO 9001',
                'environmental_management' => 'ISO 14001',
                'safety_standards' => 'OHSAS 18001',
            ],
        ]);
    }

    public function withFinancialStability(): static
    {
        return $this->state(fn (array $attributes) => [
            'financial_stability' => [
                'credit_score' => 800,
                'financial_health' => 'Excellent',
                'payment_history' => 'Always on time',
            ],
        ]);
    }

    public function withMarketReputation(): static
    {
        return $this->state(fn (array $attributes) => [
            'market_reputation' => [
                'market_position' => 'Market Leader',
                'customer_satisfaction' => 95.5,
                'industry_recognition' => ['Best Supplier Award', 'Quality Excellence'],
            ],
        ]);
    }

    public function withCompetitiveAdvantages(): static
    {
        return $this->state(fn (array $attributes) => [
            'competitor_analysis' => [
                'main_competitors' => ['Competitor A', 'Competitor B'],
                'competitive_advantages' => ['Lower prices', 'Better quality', 'Faster delivery'],
                'market_share' => 25.5,
            ],
        ]);
    }

    public function withStrategicValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'strategic_importance' => [
                'business_critical' => true,
                'supply_chain_position' => 'Primary',
                'strategic_value' => 'High',
            ],
        ]);
    }

    public function withCostBenefit(): static
    {
        return $this->state(fn (array $attributes) => [
            'cost_benefit_analysis' => [
                'total_cost' => 50000,
                'benefits' => ['Cost savings', 'Quality improvement', 'Faster delivery'],
                'roi' => 150.0,
            ],
        ]);
    }

    public function withPerformanceReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'performance_reviews' => [
                'last_review_date' => now()->subMonth()->format('Y-m-d'),
                'review_score' => 4.5,
                'strengths' => ['Reliability', 'Quality', 'Communication'],
                'areas_for_improvement' => ['Response time'],
            ],
        ]);
    }

    public function withImprovementPlan(): static
    {
        return $this->state(fn (array $attributes) => [
            'improvement_plans' => [
                'action_items' => ['Improve response time', 'Enhance quality control'],
                'timeline' => '60 days',
                'responsible_party' => 'Vendor Manager',
            ],
        ]);
    }

    public function withEscalationProcedures(): static
    {
        return $this->state(fn (array $attributes) => [
            'escalation_procedures' => [
                'escalation_levels' => ['Level 1: Vendor Manager', 'Level 2: Procurement Director'],
                'response_times' => [
                    'Level 1' => '24 hours',
                    'Level 2' => '48 hours',
                ],
                'contact_information' => [
                    'emergency_phone' => '+1234567890',
                    'emergency_email' => 'emergency@vendor.com',
                ],
            ],
        ]);
    }
}
