<?php

namespace Database\Factories;

use App\Models\LegalDocument;
use App\Models\CustomerProfile;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LegalDocumentFactory extends Factory
{
    protected $model = LegalDocument::class;

    public function definition(): array
    {
        return [
            'customer_profile_id' => CustomerProfile::factory(),
            'organization_id' => Organization::factory(),
            'document_type' => $this->faker->randomElement(['id_card', 'passport', 'drivers_license', 'contract', 'invoice', 'certificate', 'other']),
            'document_number' => $this->faker->unique()->regexify('[A-Z0-9]{8,12}'),
            'issue_date' => $this->faker->dateTimeBetween('-5 years', '-1 year'),
            'expiry_date' => $this->faker->dateTimeBetween('+1 year', '+10 years'),
            'issuing_authority' => $this->faker->company(),
            'verification_status' => $this->faker->randomElement(['pending', 'verified', 'rejected', 'expired']),
            'verification_notes' => $this->faker->optional()->sentence(),
            'verifier_user_id' => $this->faker->optional()->randomElement([User::factory()]),
            'verified_at' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
            'is_active' => $this->faker->boolean(80),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
```



