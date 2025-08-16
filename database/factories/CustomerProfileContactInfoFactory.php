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
            'billing_email' => $this->faker->optional(0.8)->email(),
            'technical_email' => $this->faker->optional(0.6)->email(),
            'address' => $this->faker->address(),
            'postal_code' => $this->faker->postcode(),
            'city' => $this->faker->city(),
            'province' => $this->faker->randomElement([
                'Madrid', 'Barcelona', 'Valencia', 'Sevilla', 'Zaragoza', 
                'Málaga', 'Murcia', 'Palma', 'Las Palmas', 'Bilbao'
            ]),
            'iban' => $this->faker->optional(0.7)->iban('ES'),
            'cups' => $this->faker->optional(0.9)->regexify('ES[0-9]{18}[A-Z]{2}'),
            'valid_from' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'valid_to' => $this->faker->optional(0.3)->dateTimeBetween('now', '+2 years'),
        ];
    }

    /**
     * State for valid contact info (currently active)
     */
    public function valid(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_from' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'valid_to' => null, // No end date = currently valid
        ]);
    }

    /**
     * State for expired contact info
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_from' => $this->faker->dateTimeBetween('-2 years', '-6 months'),
            'valid_to' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * State for future contact info (not yet valid)
     */
    public function future(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_from' => $this->faker->dateTimeBetween('now', '+3 months'),
            'valid_to' => $this->faker->optional(0.5)->dateTimeBetween('+3 months', '+2 years'),
        ]);
    }

    /**
     * State for specific province
     */
    public function inProvince(string $province): static
    {
        return $this->state(fn (array $attributes) => [
            'province' => $province,
        ]);
    }

    /**
     * State for Madrid province with specific postal codes
     */
    public function madrid(): static
    {
        return $this->state(fn (array $attributes) => [
            'province' => 'Madrid',
            'city' => 'Madrid',
            'postal_code' => $this->faker->randomElement(['28001', '28002', '28003', '28004', '28005']),
        ]);
    }

    /**
     * State for complete contact info (all fields filled)
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_email' => $this->faker->email(),
            'technical_email' => $this->faker->email(),
            'iban' => $this->faker->iban('ES'),
            'cups' => $this->faker->regexify('ES[0-9]{18}[A-Z]{2}'),
        ]);
    }

    /**
     * State for minimal contact info (only required fields)
     */
    public function minimal(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_email' => null,
            'technical_email' => null,
            'iban' => null,
            'cups' => null,
        ]);
    }

    /**
     * State for specific customer profile
     */
    public function forCustomerProfile(CustomerProfile $customerProfile): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_profile_id' => $customerProfile->id,
            'organization_id' => $customerProfile->organization_id,
        ]);
    }
}


