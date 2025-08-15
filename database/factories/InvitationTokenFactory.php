<?php

namespace Database\Factories;

use App\Models\InvitationToken;
use App\Models\Organization;
use App\Models\OrganizationRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvitationToken>
 */
class InvitationTokenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InvitationToken::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token' => Str::random(32),
            'email' => $this->faker->boolean(70) ? $this->faker->unique()->safeEmail() : null,
            'organization_role_id' => OrganizationRole::factory(),
            'organization_id' => Organization::factory(),
            'invited_by' => User::factory(),
            'expires_at' => $this->faker->dateTimeBetween('now', '+30 days'),
            'used_at' => null,
            'status' => 'pending',
        ];
    }

    /**
     * Indicate that the invitation token is pending
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'used_at' => null,
        ]);
    }

    /**
     * Indicate that the invitation token has been used
     */
    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'used',
            'used_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the invitation token is expired
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expires_at' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }

    /**
     * Indicate that the invitation token is revoked
     */
    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'revoked',
        ]);
    }

    /**
     * Indicate that the invitation token has no predefined email
     */
    public function withoutEmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => null,
        ]);
    }

    /**
     * Indicate that the invitation token has a predefined email
     */
    public function withEmail(string $email = null): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => $email ?? $this->faker->safeEmail(),
        ]);
    }

    /**
     * Indicate that the invitation token expires soon
     */
    public function expiresSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('now', '+7 days'),
        ]);
    }

    /**
     * Indicate that the invitation token expires in the far future
     */
    public function longExpiry(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('+30 days', '+90 days'),
        ]);
    }

    /**
     * Indicate that the invitation token never expires
     */
    public function neverExpires(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => null,
        ]);
    }

    /**
     * Indicate that the invitation is for a specific organization
     */
    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Indicate that the invitation is for a specific role
     */
    public function forRole(OrganizationRole $role): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_role_id' => $role->id,
        ]);
    }

    /**
     * Indicate that the invitation was created by a specific user
     */
    public function invitedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'invited_by' => $user->id,
        ]);
    }

    /**
     * Indicate that the invitation is recent (created in the last week)
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the invitation is old (created more than 30 days ago)
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-90 days', '-30 days'),
        ]);
    }
}
