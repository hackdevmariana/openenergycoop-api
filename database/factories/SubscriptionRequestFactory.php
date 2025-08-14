<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\SubscriptionRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubscriptionRequest>
 */
class SubscriptionRequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SubscriptionRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'cooperative_id' => Organization::factory(),
            'status' => $this->faker->randomElement([
                SubscriptionRequest::STATUS_PENDING,
                SubscriptionRequest::STATUS_APPROVED,
                SubscriptionRequest::STATUS_REJECTED,
                SubscriptionRequest::STATUS_IN_REVIEW,
            ]),
            'type' => $this->faker->randomElement([
                SubscriptionRequest::TYPE_NEW_SUBSCRIPTION,
                SubscriptionRequest::TYPE_OWNERSHIP_CHANGE,
                SubscriptionRequest::TYPE_TENANT_REQUEST,
            ]),
            'submitted_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'processed_at' => $this->faker->optional(0.3)->dateTimeBetween('-30 days', 'now'),
            'notes' => $this->faker->optional(0.7)->paragraph(),
        ];
    }

    /**
     * Indicate that the subscription request is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionRequest::STATUS_PENDING,
            'processed_at' => null,
        ]);
    }

    /**
     * Indicate that the subscription request is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionRequest::STATUS_APPROVED,
            'processed_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the subscription request is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionRequest::STATUS_REJECTED,
            'processed_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'notes' => $this->faker->sentence() . ' - Solicitud rechazada por documentación incompleta.',
        ]);
    }

    /**
     * Indicate that the subscription request is in review.
     */
    public function inReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionRequest::STATUS_IN_REVIEW,
            'processed_at' => null,
            'notes' => $this->faker->sentence() . ' - Solicitud en revisión técnica.',
        ]);
    }

    /**
     * Indicate that the subscription request is for a new subscription.
     */
    public function newSubscription(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => SubscriptionRequest::TYPE_NEW_SUBSCRIPTION,
            'notes' => $this->faker->optional(0.8)->sentence() . ' - Solicitud de alta para nueva instalación.',
        ]);
    }

    /**
     * Indicate that the subscription request is for ownership change.
     */
    public function ownershipChange(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => SubscriptionRequest::TYPE_OWNERSHIP_CHANGE,
            'notes' => $this->faker->optional(0.8)->sentence() . ' - Cambio de titularidad de la instalación.',
        ]);
    }

    /**
     * Indicate that the subscription request is for tenant request.
     */
    public function tenantRequest(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => SubscriptionRequest::TYPE_TENANT_REQUEST,
            'notes' => $this->faker->optional(0.8)->sentence() . ' - Solicitud de arrendatario.',
        ]);
    }

    /**
     * Indicate that the subscription request is for solar installation.
     */
    public function solarInstallation(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => $this->faker->optional(0.8)->sentence() . ' - Instalación solar fotovoltaica.',
        ]);
    }

    /**
     * Indicate that the subscription request is for commercial use.
     */
    public function commercialUse(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => $this->faker->optional(0.8)->sentence() . ' - Uso comercial/empresarial.',
        ]);
    }

    /**
     * Indicate that the subscription request is for residential use.
     */
    public function residentialUse(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => $this->faker->optional(0.8)->sentence() . ' - Uso residencial.',
        ]);
    }
}
