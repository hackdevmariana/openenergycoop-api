<?php

namespace Database\Factories;

use App\Models\CustomerProfile;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerProfileFactory extends Factory
{
    protected $model = CustomerProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'organization_id' => Organization::factory(),
            'profile_type' => $this->faker->randomElement(['individual', 'tenant', 'company', 'ownership_change']),
            'legal_id_type' => $this->faker->randomElement(['dni', 'nie', 'passport', 'cif']),
            'legal_id_number' => $this->faker->unique()->regexify('[A-Z0-9]{8,10}'),
            'legal_name' => $this->faker->company(),
            'contract_type' => $this->faker->randomElement(['own', 'tenant', 'company', 'ownership_change']),
        ];
    }

    /**
     * Create a customer profile for specific organization.
     */
    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Create a customer profile for specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create an individual customer profile.
     */
    public function individual(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_type' => 'individual',
            'legal_id_type' => $this->faker->randomElement(['dni', 'nie', 'passport']),
        ]);
    }

    /**
     * Create a company customer profile.
     */
    public function company(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_type' => 'company',
            'legal_id_type' => 'cif',
            'legal_name' => $this->faker->company(),
        ]);
    }
}
