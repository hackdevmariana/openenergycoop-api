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
}
