<?php

namespace Database\Factories;

use App\Models\OrganizationFeature;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationFeatureFactory extends Factory
{
    protected $model = OrganizationFeature::class;

    public function definition(): array
    {
        $features = [
            'energy_management', 'solar_panels', 'wind_turbines', 'battery_storage',
            'smart_meters', 'consumption_analytics', 'billing_integration', 'customer_portal',
            'mobile_app', 'api_access', 'advanced_reporting', 'multi_language',
            'team_collaboration', 'audit_logs', 'backup_restore', 'custom_integrations'
        ];

        return [
            'organization_id' => Organization::factory(),
            'feature_key' => $this->faker->randomElement($features),
            'enabled_dashboard' => $this->faker->boolean(70),
            'enabled_web' => $this->faker->boolean(80),
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    /**
     * State for enabled feature (both dashboard and web)
     */
    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enabled_dashboard' => true,
            'enabled_web' => true,
        ]);
    }

    /**
     * State for disabled feature
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enabled_dashboard' => false,
            'enabled_web' => false,
        ]);
    }

    /**
     * State for dashboard-only feature
     */
    public function dashboardOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'enabled_dashboard' => true,
            'enabled_web' => false,
        ]);
    }

    /**
     * State for web-only feature
     */
    public function webOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'enabled_dashboard' => false,
            'enabled_web' => true,
        ]);
    }

    /**
     * State for specific feature key
     */
    public function withFeature(string $featureKey): static
    {
        return $this->state(fn (array $attributes) => [
            'feature_key' => $featureKey,
        ]);
    }

    /**
     * State for specific organization
     */
    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * State with notes
     */
    public function withNotes(string $notes): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => $notes,
        ]);
    }

    /**
     * State for energy management features
     */
    public function energyFeature(): static
    {
        return $this->state(fn (array $attributes) => [
            'feature_key' => $this->faker->randomElement([
                'energy_management', 'solar_panels', 'wind_turbines', 'battery_storage', 'smart_meters'
            ]),
        ]);
    }

    /**
     * State for analytics features
     */
    public function analyticsFeature(): static
    {
        return $this->state(fn (array $attributes) => [
            'feature_key' => $this->faker->randomElement([
                'consumption_analytics', 'advanced_reporting', 'audit_logs'
            ]),
        ]);
    }

    /**
     * State for customer features
     */
    public function customerFeature(): static
    {
        return $this->state(fn (array $attributes) => [
            'feature_key' => $this->faker->randomElement([
                'customer_portal', 'mobile_app', 'billing_integration'
            ]),
        ]);
    }
}