<?php

namespace Database\Factories;

use App\Models\CustomerProfileContactInfo;
use App\Models\CustomerProfile;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerProfileContactInfoFactory extends Factory
{
    protected $model = CustomerProfileContactInfo::class;

    public function definition(): array
    {
        return [
            'customer_profile_id' => CustomerProfile::factory(),
            'organization_id' => Organization::factory(),
            'contact_type' => $this->faker->randomElement(['email', 'phone', 'address', 'social_media']),
            'contact_value' => $this->faker->email(),
            'is_primary' => $this->faker->boolean(20),
            'is_active' => $this->faker->boolean(90),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}


