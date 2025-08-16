<?php

namespace Database\Factories;

use App\Models\LegalDocument;
use App\Models\CustomerProfile;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LegalDocument>
 */
class LegalDocumentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LegalDocument::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $customerProfile = CustomerProfile::factory()->create();
        
        return [
            'customer_profile_id' => $customerProfile->id,
            'organization_id' => $customerProfile->organization_id,
            'type' => $this->faker->randomElement(['dni', 'iban_receipt', 'contract', 'invoice', 'other']),
            'version' => '1.0',
            'uploaded_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'verified_at' => null,
            'verifier_user_id' => null,
            'notes' => $this->faker->optional(0.3)->paragraph(),
            'expires_at' => $this->faker->optional(0.6)->dateTimeBetween('now', '+2 years'),
        ];
    }

    /**
     * Create a verified legal document.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_at' => $this->faker->dateTimeBetween($attributes['uploaded_at'] ?? '-3 months', 'now'),
            'verifier_user_id' => User::factory(),
            'notes' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Create an unverified legal document.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_at' => null,
            'verifier_user_id' => null,
        ]);
    }

    /**
     * Create a legal document of specific type.
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }

    /**
     * Create a DNI document.
     */
    public function dni(): static
    {
        return $this->ofType('dni');
    }

    /**
     * Create an IBAN receipt document.
     */
    public function ibanReceipt(): static
    {
        return $this->ofType('iban_receipt');
    }

    /**
     * Create a contract document.
     */
    public function contract(): static
    {
        return $this->ofType('contract');
    }

    /**
     * Create an invoice document.
     */
    public function invoice(): static
    {
        return $this->ofType('invoice');
    }

    /**
     * Create another type of document.
     */
    public function other(): static
    {
        return $this->ofType('other');
    }

    /**
     * Create a legal document for specific customer profile.
     */
    public function forCustomerProfile(CustomerProfile $customerProfile): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_profile_id' => $customerProfile->id,
            'organization_id' => $customerProfile->organization_id,
        ]);
    }

    /**
     * Create a legal document for specific organization.
     */
    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Create a legal document with specific version.
     */
    public function version(string $version): static
    {
        return $this->state(fn (array $attributes) => [
            'version' => $version,
        ]);
    }

    /**
     * Create an expired legal document.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    /**
     * Create a legal document expiring soon.
     */
    public function expiringSoon(int $days = 30): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('now', "+{$days} days"),
        ]);
    }

    /**
     * Create a legal document without expiry.
     */
    public function withoutExpiry(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => null,
        ]);
    }

    /**
     * Create a legal document with notes.
     */
    public function withNotes(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Create a legal document without notes.
     */
    public function withoutNotes(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => null,
        ]);
    }

    /**
     * Create a recently uploaded legal document.
     */
    public function recentlyUploaded(): static
    {
        return $this->state(fn (array $attributes) => [
            'uploaded_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Create a verified document with verifier.
     */
    public function verifiedBy(User $verifier): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_at' => $this->faker->dateTimeBetween($attributes['uploaded_at'] ?? '-3 months', 'now'),
            'verifier_user_id' => $verifier->id,
            'notes' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Create a complete legal document with all fields.
     */
    public function complete(): static
    {
        return $this->verified()
                    ->withNotes()
                    ->expiringSoon();
    }

    /**
     * Create a minimal legal document.
     */
    public function minimal(): static
    {
        return $this->unverified()
                    ->withoutNotes()
                    ->withoutExpiry();
    }
}