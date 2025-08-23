<?php

namespace Database\Factories;

use App\Models\ProductionProject;
use App\Models\Organization;
use App\Models\User;
use App\Models\EnergySource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductionProject>
 */
class ProductionProjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductionProject::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $projectTypes = array_keys(ProductionProject::getProjectTypes());
        $technologyTypes = [
            'photovoltaic',
            'concentrated_solar',
            'wind_turbine',
            'hydroelectric',
            'biomass_plant',
            'geothermal_plant',
            'other'
        ];
        $statuses = array_keys(ProductionProject::getStatuses());
        $priorities = array_keys(ProductionProject::getPriorities());
        
        $capacityKw = $this->faker->randomFloat(2, 10, 10000);
        $totalInvestment = $capacityKw * $this->faker->randomFloat(2, 800, 2000);
        
        return [
            'project_number' => 'PP-' . $this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->faker->unique()->sentence(3, false),
            'description' => $this->faker->paragraph(3),
            'project_type' => $this->faker->randomElement($projectTypes),
            'status' => $this->faker->randomElement($statuses),
            'priority' => $this->faker->randomElement($priorities),
            'start_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'expected_completion_date' => $this->faker->dateTimeBetween('now', '+2 years'),
            'actual_completion_date' => null,
            'budget' => $totalInvestment,
            'spent_amount' => $this->faker->randomFloat(2, 0, $totalInvestment * 0.8),
            'remaining_budget' => function (array $attributes) {
                return $attributes['budget'] - $attributes['spent_amount'];
            },
            'planned_capacity_mw' => $capacityKw / 1000,
            'actual_capacity_mw' => function (array $attributes) {
                return $attributes['planned_capacity_mw'] * $this->faker->randomFloat(2, 0, 1);
            },
            'efficiency_rating' => $this->faker->randomFloat(2, 70, 95),
            'location_address' => $this->faker->streetAddress(),
            'latitude' => $this->faker->latitude(35, 45), // España
            'longitude' => $this->faker->longitude(-10, 5), // España
            'technical_specifications' => $this->faker->paragraphs(3, true),
            'environmental_impact' => $this->faker->paragraphs(2, true),
            'regulatory_compliance' => $this->faker->paragraphs(2, true),
            'safety_measures' => $this->faker->paragraphs(2, true),
            'project_team' => [
                'project_manager' => $this->faker->name(),
                'engineers' => [$this->faker->name(), $this->faker->name()],
                'technicians' => [$this->faker->name(), $this->faker->name(), $this->faker->name()],
            ],
            'stakeholders' => [
                'investors' => [$this->faker->company(), $this->faker->company()],
                'local_authorities' => [$this->faker->company()],
                'environmental_groups' => [$this->faker->company()],
            ],
            'contractors' => [
                'construction' => $this->faker->company(),
                'electrical' => $this->faker->company(),
                'civil_works' => $this->faker->company(),
            ],
            'suppliers' => [
                'equipment' => $this->faker->company(),
                'materials' => $this->faker->company(),
                'services' => $this->faker->company(),
            ],
            'milestones' => [
                [
                    'title' => 'Planificación Completada',
                    'due_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
                    'completed' => true,
                ],
                [
                    'title' => 'Aprobación Regulatoria',
                    'due_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
                    'completed' => $this->faker->boolean(80),
                ],
                [
                    'title' => 'Inicio de Construcción',
                    'due_date' => $this->faker->dateTimeBetween('-3 months', '+3 months'),
                    'completed' => $this->faker->boolean(60),
                ],
            ],
            'risks' => [
                'technical' => ['Retrasos en la entrega de equipos', 'Problemas de calidad'],
                'environmental' => ['Impacto en hábitats locales', 'Cambios climáticos'],
                'financial' => ['Aumento de costos', 'Fluctuaciones de mercado'],
            ],
            'mitigation_strategies' => [
                'technical' => ['Contratos con penalizaciones', 'Inspecciones de calidad'],
                'environmental' => ['Estudios de impacto', 'Monitoreo continuo'],
                'financial' => ['Presupuesto de contingencia', 'Hedging de precios'],
            ],
            'quality_standards' => [
                'ISO_9001' => true,
                'ISO_14001' => true,
                'OHSAS_18001' => $this->faker->boolean(80),
            ],
            'documentation' => [
                'technical_drawings' => $this->faker->boolean(90),
                'environmental_assessments' => $this->faker->boolean(85),
                'safety_plans' => $this->faker->boolean(90),
                'operation_manuals' => $this->faker->boolean(70),
            ],
            'tags' => $this->faker->words(3, false),
            'project_manager' => User::factory(),
            'created_by' => User::factory(),
            'approved_by' => null,
            'approved_at' => null,
            'notes' => $this->faker->paragraphs(2, true),
            
            // Campos adicionales del modelo actual
            'slug' => function (array $attributes) {
                return \Str::slug($attributes['name']);
            },
            'technology_type' => $this->faker->randomElement($technologyTypes),
            'organization_id' => Organization::factory(),
            'owner_user_id' => User::factory(),
            'energy_source_id' => EnergySource::factory(),
            'capacity_kw' => $capacityKw,
            'estimated_annual_production' => $capacityKw * 8760 * 0.25, // 25% factor de capacidad
            'peak_power_kw' => $capacityKw * $this->faker->randomFloat(2, 0.8, 1.2),
            'capacity_factor' => $this->faker->randomFloat(2, 15, 35),
            'technical_specifications' => $this->faker->paragraphs(3, true),
            'equipment_details' => $this->faker->paragraphs(2, true),
            'manufacturer' => $this->faker->company(),
            'model' => $this->faker->bothify('MOD-####'),
            'location_city' => $this->faker->city(),
            'location_region' => $this->faker->state(),
            'location_country' => 'ES',
            'location_postal_code' => $this->faker->postcode(),
            'location_metadata' => $this->faker->paragraphs(2, true),
            'planning_start_date' => $this->faker->dateTimeBetween('-2 years', '-1 year'),
            'construction_start_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'construction_end_date' => null,
            'operational_start_date' => null,
            'expected_end_date' => $this->faker->dateTimeBetween('now', '+2 years'),
            'completion_percentage' => $this->faker->randomFloat(2, 0, 100),
            'estimated_duration_months' => $this->faker->numberBetween(6, 36),
            'cost_per_kw' => $totalInvestment / $capacityKw,
            'estimated_roi_percentage' => $this->faker->randomFloat(2, 5, 25),
            'payback_period_years' => $this->faker->randomFloat(1, 3, 15),
            'annual_operating_cost' => $totalInvestment * $this->faker->randomFloat(2, 0.01, 0.05),
            'accepts_crowdfunding' => $this->faker->boolean(30),
            'is_investment_ready' => $this->faker->boolean(60),
            'crowdfunding_target' => function (array $attributes) {
                return $attributes['accepts_crowdfunding'] ? $attributes['total_investment'] * $this->faker->randomFloat(2, 0.1, 0.3) : 0;
            },
            'crowdfunding_raised' => function (array $attributes) {
                return $attributes['accepts_crowdfunding'] ? $attributes['crowdfunding_target'] * $this->faker->randomFloat(2, 0, 0.8) : 0;
            },
            'min_investment' => function (array $attributes) {
                return $attributes['accepts_crowdfunding'] ? $this->faker->randomFloat(2, 100, 1000) : 0;
            },
            'max_investment' => function (array $attributes) {
                return $attributes['accepts_crowdfunding'] ? $attributes['total_investment'] * $this->faker->randomFloat(2, 0.05, 0.15) : 0;
            },
            'investment_terms' => $this->faker->sentence(),
            'co2_avoided_tons_year' => $capacityKw * $this->faker->randomFloat(2, 0.3, 0.8),
            'renewable_percentage' => $this->faker->randomFloat(2, 80, 100),
            'environmental_score' => $this->faker->randomFloat(2, 70, 95),
            'environmental_certifications' => $this->faker->paragraphs(2, true),
            'sustainability_metrics' => $this->faker->paragraphs(2, true),
            'regulatory_approved' => $this->faker->boolean(70),
            'permits_complete' => $this->faker->boolean(60),
            'regulatory_approval_date' => function (array $attributes) {
                return $attributes['regulatory_approved'] ? $this->faker->dateTimeBetween('-1 year', 'now') : null;
            },
            'regulatory_authority' => $this->faker->company(),
            'permits_required' => $this->faker->paragraphs(2, true),
            'permits_obtained' => $this->faker->paragraphs(2, true),
            'annual_maintenance_cost' => $totalInvestment * $this->faker->randomFloat(2, 0.01, 0.03),
            'maintenance_interval_months' => $this->faker->numberBetween(6, 24),
            'maintenance_provider' => $this->faker->company(),
            'last_maintenance_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'next_maintenance_date' => function (array $attributes) {
                return $attributes['last_maintenance_date'] ? 
                    (new \DateTime($attributes['last_maintenance_date']))->add(new \DateInterval('P' . $attributes['maintenance_interval_months'] . 'M')) :
                    null;
            },
            'maintenance_requirements' => $this->faker->paragraphs(2, true),
            'is_public' => $this->faker->boolean(80),
            'is_active' => $this->faker->boolean(90),
            'is_featured' => $this->faker->boolean(20),
            'requires_approval' => $this->faker->boolean(40),
            'notes' => $this->faker->paragraphs(2, true),
            'images' => [],
            'documents' => [],
        ];
    }

    /**
     * Indicate that the project is in planning stage.
     */
    public function planning(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'planning',
            'completion_percentage' => 0,
            'construction_start_date' => null,
            'construction_end_date' => null,
            'operational_start_date' => null,
        ]);
    }

    /**
     * Indicate that the project is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'completion_percentage' => 0,
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the project is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'completion_percentage' => $this->faker->randomFloat(2, 10, 80),
            'construction_start_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * Indicate that the project is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completion_percentage' => 100,
            'actual_completion_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'operational_start_date' => $this->faker->dateTimeBetween('-1 year', '-6 months'),
        ]);
    }

    /**
     * Indicate that the project is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'completion_percentage' => $this->faker->randomFloat(2, 0, 50),
        ]);
    }

    /**
     * Indicate that the project is on hold.
     */
    public function onHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'on_hold',
            'completion_percentage' => $this->faker->randomFloat(2, 0, 80),
        ]);
    }

    /**
     * Indicate that the project is in maintenance.
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'maintenance',
            'completion_percentage' => 100,
        ]);
    }

    /**
     * Indicate that the project is high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $this->faker->randomElement(['high', 'urgent', 'critical']),
        ]);
    }

    /**
     * Indicate that the project is solar type.
     */
    public function solar(): static
    {
        return $this->state(fn (array $attributes) => [
            'project_type' => 'solar_farm',
            'technology_type' => $this->faker->randomElement(['photovoltaic', 'concentrated_solar']),
        ]);
    }

    /**
     * Indicate that the project is wind type.
     */
    public function wind(): static
    {
        return $this->state(fn (array $attributes) => [
            'project_type' => 'wind_farm',
            'technology_type' => 'wind_turbine',
        ]);
    }

    /**
     * Indicate that the project is hydroelectric type.
     */
    public function hydroelectric(): static
    {
        return $this->state(fn (array $attributes) => [
            'project_type' => 'hydroelectric',
            'technology_type' => 'hydroelectric',
        ]);
    }

    /**
     * Indicate that the project accepts crowdfunding.
     */
    public function crowdfunding(): static
    {
        return $this->state(fn (array $attributes) => [
            'accepts_crowdfunding' => true,
            'is_investment_ready' => true,
            'crowdfunding_target' => $attributes['total_investment'] * $this->faker->randomFloat(2, 0.1, 0.3),
            'crowdfunding_raised' => function (array $attributes) {
                return $attributes['crowdfunding_target'] * $this->faker->randomFloat(2, 0, 0.8);
            },
            'min_investment' => $this->faker->randomFloat(2, 100, 1000),
            'max_investment' => function (array $attributes) {
                return $attributes['total_investment'] * $this->faker->randomFloat(2, 0.05, 0.15);
            },
        ]);
    }

    /**
     * Indicate that the project is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Indicate that the project is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the project is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the project is regulatory approved.
     */
    public function regulatoryApproved(): static
    {
        return $this->state(fn (array $attributes) => [
            'regulatory_approved' => true,
            'permits_complete' => true,
            'regulatory_approval_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Indicate that the project is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'expected_completion_date' => $this->faker->dateTimeBetween('-6 months', '-1 month'),
            'status' => $this->faker->randomElement(['planning', 'approved', 'in_progress']),
        ]);
    }

    /**
     * Indicate that the project has high capacity.
     */
    public function highCapacity(): static
    {
        return $this->state(fn (array $attributes) => [
            'capacity_kw' => $this->faker->randomFloat(2, 5000, 10000),
            'planned_capacity_mw' => $this->faker->randomFloat(2, 5, 10),
        ]);
    }

    /**
     * Indicate that the project has low capacity.
     */
    public function lowCapacity(): static
    {
        return $this->state(fn (array $attributes) => [
            'capacity_kw' => $this->faker->randomFloat(2, 10, 100),
            'planned_capacity_mw' => $this->faker->randomFloat(2, 0.01, 0.1),
        ]);
    }

    /**
     * Indicate that the project has high investment.
     */
    public function highInvestment(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_investment' => $this->faker->randomFloat(2, 10000000, 100000000),
            'budget' => $this->faker->randomFloat(2, 10000000, 100000000),
        ]);
    }

    /**
     * Indicate that the project has low investment.
     */
    public function lowInvestment(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_investment' => $this->faker->randomFloat(2, 10000, 100000),
            'budget' => $this->faker->randomFloat(2, 10000, 100000),
        ]);
    }
}
