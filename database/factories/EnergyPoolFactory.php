<?php

namespace Database\Factories;

use App\Models\EnergyPool;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EnergyPool>
 */
class EnergyPoolFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $poolType = $this->faker->randomElement(array_keys(EnergyPool::getPoolTypes()));
        $status = $this->faker->randomElement(array_keys(EnergyPool::getStatuses()));
        $energyCategory = $this->faker->randomElement(array_keys(EnergyPool::getEnergyCategories()));
        
        $totalCapacity = $this->faker->randomFloat(2, 10, 1000);
        $utilizedCapacity = $this->faker->randomFloat(2, 0, $totalCapacity);
        $reservedCapacity = $this->faker->randomFloat(2, 0, $totalCapacity - $utilizedCapacity);
        $availableCapacity = $totalCapacity - $utilizedCapacity - $reservedCapacity;
        
        $efficiencyRating = $this->faker->randomFloat(2, 60, 100);
        $availabilityFactor = $this->faker->randomFloat(2, 70, 100);
        $capacityFactor = $this->faker->randomFloat(2, 50, 95);
        
        $annualProduction = $totalCapacity * $capacityFactor * 8760 / 100; // MWh
        $monthlyProduction = $annualProduction / 12;
        $dailyProduction = $annualProduction / 365;
        $hourlyProduction = $dailyProduction / 24;
        
        $constructionCost = $this->faker->randomFloat(2, 100000, 10000000);
        $operationalCost = $this->faker->randomFloat(2, 20, 200);
        $maintenanceCost = $this->faker->randomFloat(2, 10, 100);
        
        $commissioningDate = $this->faker->dateTimeBetween('-10 years', '-1 year');
        $expectedLifespan = $this->faker->numberBetween(20, 50);
        
        return [
            'pool_number' => 'POOL-' . $this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->faker->company() . ' Energy Pool',
            'description' => $this->faker->paragraph(),
            'pool_type' => $poolType,
            'status' => $status,
            'energy_category' => $energyCategory,
            'total_capacity_mw' => $totalCapacity,
            'available_capacity_mw' => $availableCapacity,
            'reserved_capacity_mw' => $reservedCapacity,
            'utilized_capacity_mw' => $utilizedCapacity,
            'efficiency_rating' => $efficiencyRating,
            'availability_factor' => $availabilityFactor,
            'capacity_factor' => $capacityFactor,
            'annual_production_mwh' => $annualProduction,
            'monthly_production_mwh' => $monthlyProduction,
            'daily_production_mwh' => $dailyProduction,
            'hourly_production_mwh' => $hourlyProduction,
            'location_address' => $this->faker->address(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'region' => $this->faker->randomElement(['North', 'South', 'East', 'West', 'Central']),
            'country' => $this->faker->randomElement(['Spain', 'France', 'Germany', 'Italy', 'Portugal']),
            'commissioning_date' => $commissioningDate,
            'decommissioning_date' => $this->faker->optional(0.1)->dateTimeBetween($commissioningDate, '+50 years'),
            'expected_lifespan_years' => $expectedLifespan,
            'construction_cost' => $constructionCost,
            'operational_cost_per_mwh' => $operationalCost,
            'maintenance_cost_per_mwh' => $maintenanceCost,
            'technical_specifications' => [
                'technology' => $this->faker->randomElement(['Solar PV', 'Wind Turbine', 'Hydroelectric', 'Battery Storage']),
                'manufacturer' => $this->faker->company(),
                'model' => $this->faker->bothify('Model-####'),
                'year_manufactured' => $this->faker->numberBetween(2010, 2023),
                'certifications' => $this->faker->randomElements(['ISO 9001', 'ISO 14001', 'OHSAS 18001'], $this->faker->numberBetween(1, 3))
            ],
            'environmental_impact' => [
                'co2_reduction_tonnes' => $this->faker->randomFloat(2, 100, 10000),
                'renewable_energy_percentage' => $this->faker->randomFloat(2, 80, 100),
                'water_consumption' => $this->faker->randomFloat(2, 0, 1000),
                'waste_generation' => $this->faker->randomFloat(2, 0, 100)
            ],
            'regulatory_compliance' => [
                'energy_authority_approval' => $this->faker->boolean(),
                'environmental_permits' => $this->faker->boolean(),
                'safety_certifications' => $this->faker->boolean(),
                'grid_connection_approval' => $this->faker->boolean()
            ],
            'safety_features' => [
                'emergency_shutdown' => $this->faker->boolean(),
                'fire_suppression' => $this->faker->boolean(),
                'security_systems' => $this->faker->boolean(),
                'monitoring_alarms' => $this->faker->boolean()
            ],
            'pool_members' => [
                'total_members' => $this->faker->numberBetween(10, 1000),
                'member_types' => $this->faker->randomElements(['Individual', 'Commercial', 'Industrial', 'Municipal'], $this->faker->numberBetween(1, 4)),
                'governance_structure' => $this->faker->randomElement(['Democratic', 'Representative', 'Hybrid'])
            ],
            'pool_operators' => [
                'primary_operator' => $this->faker->company(),
                'backup_operators' => $this->faker->randomElements([$this->faker->company(), $this->faker->company()], $this->faker->numberBetween(0, 2)),
                'operation_hours' => '24/7',
                'maintenance_team_size' => $this->faker->numberBetween(5, 50)
            ],
            'pool_governance' => [
                'board_size' => $this->faker->numberBetween(5, 15),
                'election_frequency' => $this->faker->randomElement(['Annual', 'Biennial', 'Triennial']),
                'voting_rights' => $this->faker->randomElement(['One member, one vote', 'Proportional to investment', 'Hybrid'])
            ],
            'trading_rules' => [
                'trading_hours' => '24/7',
                'minimum_trade_size' => $this->faker->randomFloat(2, 0.1, 10),
                'price_volatility_limit' => $this->faker->randomFloat(2, 5, 50),
                'settlement_period' => $this->faker->randomElement(['T+1', 'T+2', 'T+3'])
            ],
            'settlement_procedures' => [
                'payment_methods' => $this->faker->randomElements(['Bank Transfer', 'Credit Card', 'Digital Wallet'], $this->faker->numberBetween(1, 3)),
                'settlement_currency' => $this->faker->randomElement(['EUR', 'USD', 'GBP']),
                'dispute_resolution' => $this->faker->randomElement(['Arbitration', 'Mediation', 'Legal Action'])
            ],
            'risk_management' => [
                'risk_assessment_frequency' => $this->faker->randomElement(['Monthly', 'Quarterly', 'Annually']),
                'insurance_coverage' => $this->faker->boolean(),
                'hedging_strategies' => $this->faker->boolean(),
                'emergency_fund' => $this->faker->boolean()
            ],
            'performance_metrics' => [
                'uptime_percentage' => $this->faker->randomFloat(2, 90, 99.9),
                'response_time_minutes' => $this->faker->numberBetween(1, 60),
                'customer_satisfaction' => $this->faker->randomFloat(2, 3.5, 5.0),
                'efficiency_trend' => $this->faker->randomElement(['Improving', 'Stable', 'Declining'])
            ],
            'environmental_data' => [
                'air_quality_impact' => $this->faker->randomFloat(2, 0, 100),
                'noise_level_db' => $this->faker->randomFloat(2, 30, 80),
                'visual_impact_assessment' => $this->faker->randomElement(['Low', 'Medium', 'High']),
                'biodiversity_impact' => $this->faker->randomElement(['Positive', 'Neutral', 'Negative'])
            ],
            'regulatory_documents' => [
                'environmental_impact_assessment' => $this->faker->boolean(),
                'energy_license' => $this->faker->boolean(),
                'grid_connection_agreement' => $this->faker->boolean(),
                'operating_permit' => $this->faker->boolean()
            ],
            'tags' => $this->faker->randomElements([
                'Renewable', 'High Efficiency', 'Smart Grid', 'Energy Storage',
                'Peak Shaving', 'Load Balancing', 'Grid Stability', 'Carbon Neutral'
            ], $this->faker->numberBetween(2, 6)),
            'managed_by' => User::factory(),
            'created_by' => User::factory(),
            'approved_by' => $this->faker->optional(0.8, null)->randomElement([User::factory(), null]),
            'approved_at' => $this->faker->optional(0.8, null)->dateTimeBetween('-1 year', 'now'),
            'notes' => $this->faker->optional(0.7, null)->paragraph(),
        ];
    }

    /**
     * Indicate that the energy pool is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the energy pool is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the energy pool is in maintenance.
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'maintenance',
        ]);
    }

    /**
     * Indicate that the energy pool is decommissioned.
     */
    public function decommissioned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'decommissioned',
        ]);
    }

    /**
     * Indicate that the energy pool is a trading pool.
     */
    public function trading(): static
    {
        return $this->state(fn (array $attributes) => [
            'pool_type' => 'trading',
        ]);
    }

    /**
     * Indicate that the energy pool is a storage pool.
     */
    public function storage(): static
    {
        return $this->state(fn (array $attributes) => [
            'pool_type' => 'storage',
        ]);
    }

    /**
     * Indicate that the energy pool is a distribution pool.
     */
    public function distribution(): static
    {
        return $this->state(fn (array $attributes) => [
            'pool_type' => 'distribution',
        ]);
    }

    /**
     * Indicate that the energy pool is renewable.
     */
    public function renewable(): static
    {
        return $this->state(fn (array $attributes) => [
            'energy_category' => 'renewable',
        ]);
    }

    /**
     * Indicate that the energy pool is fossil fuel based.
     */
    public function fossil(): static
    {
        return $this->state(fn (array $attributes) => [
            'energy_category' => 'fossil',
        ]);
    }

    /**
     * Indicate that the energy pool is nuclear.
     */
    public function nuclear(): static
    {
        return $this->state(fn (array $attributes) => [
            'energy_category' => 'nuclear',
        ]);
    }

    /**
     * Indicate that the energy pool is hybrid.
     */
    public function hybrid(): static
    {
        return $this->state(fn (array $attributes) => [
            'energy_category' => 'hybrid',
        ]);
    }

    /**
     * Indicate that the energy pool has high efficiency.
     */
    public function highEfficiency(): static
    {
        return $this->state(fn (array $attributes) => [
            'efficiency_rating' => $this->faker->randomFloat(2, 90, 100),
        ]);
    }

    /**
     * Indicate that the energy pool has high availability.
     */
    public function highAvailability(): static
    {
        return $this->state(fn (array $attributes) => [
            'availability_factor' => $this->faker->randomFloat(2, 95, 100),
        ]);
    }

    /**
     * Indicate that the energy pool has high capacity factor.
     */
    public function highCapacityFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'capacity_factor' => $this->faker->randomFloat(2, 80, 95),
        ]);
    }

    /**
     * Indicate that the energy pool is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_by' => User::factory(),
            'approved_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Indicate that the energy pool is pending approval.
     */
    public function pendingApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    /**
     * Indicate that the energy pool is commissioned.
     */
    public function commissioned(): static
    {
        return $this->state(fn (array $attributes) => [
            'commissioning_date' => $this->faker->dateTimeBetween('-10 years', '-1 month'),
        ]);
    }

    /**
     * Indicate that the energy pool is newly built.
     */
    public function newlyBuilt(): static
    {
        return $this->state(fn (array $attributes) => [
            'commissioning_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * Indicate that the energy pool has low capacity.
     */
    public function lowCapacity(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_capacity_mw' => $this->faker->randomFloat(2, 1, 50),
        ]);
    }

    /**
     * Indicate that the energy pool has medium capacity.
     */
    public function mediumCapacity(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_capacity_mw' => $this->faker->randomFloat(2, 50, 200),
        ]);
    }

    /**
     * Indicate that the energy pool has high capacity.
     */
    public function highCapacity(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_capacity_mw' => $this->faker->randomFloat(2, 200, 1000),
        ]);
    }

    /**
     * Indicate that the energy pool is fully utilized.
     */
    public function fullyUtilized(): static
    {
        return $this->state(function (array $attributes) {
            $totalCapacity = $attributes['total_capacity_mw'] ?? 100;
            return [
                'utilized_capacity_mw' => $totalCapacity,
                'available_capacity_mw' => 0,
                'reserved_capacity_mw' => 0,
            ];
        });
    }

    /**
     * Indicate that the energy pool has available capacity.
     */
    public function hasAvailableCapacity(): static
    {
        return $this->state(function (array $attributes) {
            $totalCapacity = $attributes['total_capacity_mw'] ?? 100;
            $utilized = $totalCapacity * 0.6;
            $reserved = $totalCapacity * 0.2;
            $available = $totalCapacity - $utilized - $reserved;
            
            return [
                'utilized_capacity_mw' => $utilized,
                'reserved_capacity_mw' => $reserved,
                'available_capacity_mw' => $available,
            ];
        });
    }

    /**
     * Indicate that the energy pool is in a specific region.
     */
    public function inRegion(string $region): static
    {
        return $this->state(fn (array $attributes) => [
            'region' => $region,
        ]);
    }

    /**
     * Indicate that the energy pool is in a specific country.
     */
    public function inCountry(string $country): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => $country,
        ]);
    }

    /**
     * Indicate that the energy pool has specific tags.
     */
    public function withTags(array $tags): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => $tags,
        ]);
    }

    /**
     * Indicate that the energy pool has no location data.
     */
    public function noLocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'location_address' => null,
            'latitude' => null,
            'longitude' => null,
            'region' => null,
            'country' => null,
        ]);
    }

    /**
     * Indicate that the energy pool has no cost data.
     */
    public function noCostData(): static
    {
        return $this->state(fn (array $attributes) => [
            'construction_cost' => null,
            'operational_cost_per_mwh' => null,
            'maintenance_cost_per_mwh' => null,
        ]);
    }

    /**
     * Indicate that the energy pool has no production data.
     */
    public function noProductionData(): static
    {
        return $this->state(fn (array $attributes) => [
            'annual_production_mwh' => null,
            'monthly_production_mwh' => null,
            'daily_production_mwh' => null,
            'hourly_production_mwh' => null,
        ]);
    }
}
