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
        $spanishData = config('spanish_data');
        
        // Seleccionar provincia aleatoria de Aragón
        $province = $this->faker->randomElement(array_keys($spanishData['aragon']['provinces']));
        
        // Seleccionar municipio aleatorio de la provincia
        $municipality = $this->faker->randomElement($spanishData['aragon']['provinces'][$province]['municipalities']);
        
        // Seleccionar dirección aleatoria de la provincia
        $address = $this->faker->randomElement($spanishData['aragon']['addresses'][$province]);
        
        // Seleccionar código postal de la provincia
        $postalCode = $this->faker->randomElement($spanishData['aragon']['provinces'][$province]['postal_codes']);
        
        return [
            'customer_profile_id' => CustomerProfile::factory(),
            'organization_id' => Organization::factory(),
            'billing_email' => $this->faker->optional(0.8)->email(),
            'technical_email' => $this->faker->optional(0.6)->email(),
            'address' => $address,
            'postal_code' => $postalCode,
            'city' => $municipality,
            'province' => $province,
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
        return $this->state(function (array $attributes) use ($province) {
            $spanishData = config('spanish_data');
            
            if (!isset($spanishData['aragon']['provinces'][$province])) {
                return [];
            }
            
            $municipality = $this->faker->randomElement($spanishData['aragon']['provinces'][$province]['municipalities']);
            $address = $this->faker->randomElement($spanishData['aragon']['addresses'][$province]);
            $postalCode = $this->faker->randomElement($spanishData['aragon']['provinces'][$province]['postal_codes']);
            
            return [
                'province' => $province,
                'city' => $municipality,
                'address' => $address,
                'postal_code' => $postalCode,
            ];
        });
    }

    /**
     * State for Madrid province with specific postal codes
     */
    public function madrid(): static
    {
        return $this->state(function (array $attributes) {
            $spanishData = config('spanish_data');
            $province = 'Zaragoza'; // Usar Zaragoza como principal
            
            $municipality = $this->faker->randomElement($spanishData['aragon']['provinces'][$province]['municipalities']);
            $address = $this->faker->randomElement($spanishData['aragon']['addresses'][$province]);
            $postalCode = $this->faker->randomElement(['50001', '50002', '50003', '50004', '50005']);
            
            return [
                'province' => $province,
                'city' => $municipality,
                'address' => $address,
                'postal_code' => $postalCode,
            ];
        });
    }

    /**
     * State for Zaragoza province
     */
    public function zaragoza(): static
    {
        return $this->state(function (array $attributes) {
            $spanishData = config('spanish_data');
            $province = 'Zaragoza';
            
            $municipality = $this->faker->randomElement($spanishData['aragon']['provinces'][$province]['municipalities']);
            $address = $this->faker->randomElement($spanishData['aragon']['addresses'][$province]);
            $postalCode = $this->faker->randomElement($spanishData['aragon']['provinces'][$province]['postal_codes']);
            
            return [
                'province' => $province,
                'city' => $municipality,
                'address' => $address,
                'postal_code' => $postalCode,
            ];
        });
    }

    /**
     * State for Huesca province
     */
    public function huesca(): static
    {
        return $this->state(function (array $attributes) {
            $spanishData = config('spanish_data');
            $province = 'Huesca';
            
            $municipality = $this->faker->randomElement($spanishData['aragon']['provinces'][$province]['municipalities']);
            $address = $this->faker->randomElement($spanishData['aragon']['addresses'][$province]);
            $postalCode = $this->faker->randomElement($spanishData['aragon']['provinces'][$province]['postal_codes']);
            
            return [
                'province' => $province,
                'city' => $municipality,
                'address' => $address,
                'postal_code' => $postalCode,
            ];
        });
    }

    /**
     * State for Teruel province
     */
    public function teruel(): static
    {
        return $this->state(function (array $attributes) {
            $spanishData = config('spanish_data');
            $province = 'Teruel';
            
            $municipality = $this->faker->randomElement($spanishData['aragon']['provinces'][$province]['municipalities']);
            $address = $this->faker->randomElement($spanishData['aragon']['addresses'][$province]);
            $postalCode = $this->faker->randomElement($spanishData['aragon']['provinces'][$province]['postal_codes']);
            
            return [
                'province' => $province,
                'city' => $municipality,
                'address' => $address,
                'postal_code' => $postalCode,
            ];
        });
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
}


