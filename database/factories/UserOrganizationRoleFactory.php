<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\OrganizationRole;
use App\Models\User;
use App\Models\UserOrganizationRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserOrganizationRole>
 */
class UserOrganizationRoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserOrganizationRole::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'organization_id' => Organization::factory(),
            'organization_role_id' => OrganizationRole::factory(),
            'assigned_at' => $this->faker->dateTimeBetween('-90 days', 'now'),
        ];
    }

    /**
     * Indicate that the role assignment is recent (last 30 days).
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the role assignment is old (more than 90 days).
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_at' => $this->faker->dateTimeBetween('-365 days', '-90 days'),
        ]);
    }

    /**
     * Indicate that the role assignment is for today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_at' => now(),
        ]);
    }

    /**
     * Indicate that the role assignment is for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the role assignment is for a specific organization.
     */
    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Indicate that the role assignment is for a specific organization role.
     */
    public function forOrganizationRole(OrganizationRole $organizationRole): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_role_id' => $organizationRole->id,
        ]);
    }

    /**
     * Indicate that the role assignment is for a project manager role.
     */
    public function projectManager(): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_role_id' => OrganizationRole::factory()->projectManager(),
        ]);
    }

    /**
     * Indicate that the role assignment is for a technical installer role.
     */
    public function technicalInstaller(): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_role_id' => OrganizationRole::factory()->technicalInstaller(),
        ]);
    }

    /**
     * Indicate that the role assignment is for a customer service role.
     */
    public function customerService(): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_role_id' => OrganizationRole::factory()->customerService(),
        ]);
    }

    /**
     * Indicate that the role assignment is for a sales role.
     */
    public function sales(): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_role_id' => OrganizationRole::factory()->sales(),
        ]);
    }

    /**
     * Indicate that the role assignment is for an administrator role.
     */
    public function administrator(): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_role_id' => OrganizationRole::factory()->administrator(),
        ]);
    }

    /**
     * Indicate that the role assignment is for a billing role.
     */
    public function billing(): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_role_id' => OrganizationRole::factory()->billing(),
        ]);
    }

    /**
     * Indicate that the role assignment is for a reporting role.
     */
    public function reporting(): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_role_id' => OrganizationRole::factory()->reporting(),
        ]);
    }

    /**
     * Indicate that the role assignment has minimal permissions.
     */
    public function minimalPermissions(): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_role_id' => OrganizationRole::factory()->minimalPermissions(),
        ]);
    }

    /**
     * Indicate that the role assignment has read-only permissions.
     */
    public function readOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_role_id' => OrganizationRole::factory()->readOnly(),
        ]);
    }
}
