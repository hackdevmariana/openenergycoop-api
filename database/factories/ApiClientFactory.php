<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApiClient>
 */
class ApiClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => \App\Models\Organization::factory(),
            'name' => $this->faker->company() . ' API Client',
            'token' => \Illuminate\Support\Str::random(64),
            'scopes' => $this->faker->randomElements([
                'read', 'write', 'delete', 'admin', 'user', 'organization', 
                'device', 'metrics', 'billing', 'maintenance'
            ], $this->faker->numberBetween(2, 5)),
            'status' => $this->faker->randomElement(['active', 'suspended', 'revoked']),
            'allowed_ips' => $this->faker->randomElements([
                '192.168.1.0/24', '10.0.0.0/8', '172.16.0.0/12', '0.0.0.0/0'
            ], $this->faker->numberBetween(1, 3)),
            'callback_url' => $this->faker->optional(0.7)->url(),
            'expires_at' => $this->faker->optional(0.8)->dateTimeBetween('now', '+2 years'),
            'description' => $this->faker->sentence(),
            'rate_limits' => [
                'requests_per_minute' => $this->faker->randomElement([100, 500, 1000, 2000]),
                'requests_per_hour' => $this->faker->randomElement([5000, 10000, 50000, 100000]),
                'requests_per_day' => $this->faker->randomElement([100000, 500000, 1000000, 5000000])
            ],
            'webhook_config' => [
                'enabled' => $this->faker->boolean(70),
                'retry_attempts' => $this->faker->numberBetween(1, 10),
                'timeout' => $this->faker->numberBetween(10, 60)
            ],
            'permissions' => [
                'user_management' => $this->faker->boolean(60),
                'energy_data' => $this->faker->boolean(80),
                'billing' => $this->faker->boolean(50),
                'reports' => $this->faker->boolean(70)
            ],
            'version' => $this->faker->randomElement(['1.0', '1.5', '2.0', '2.1', '3.0']),
            'metadata' => [
                'created_by' => $this->faker->randomElement(['system', 'admin', 'developer']),
                'environment' => $this->faker->randomElement(['development', 'staging', 'production']),
                'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'critical'])
            ]
        ];
    }

    /**
     * Indicate that the API client is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the API client is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }

    /**
     * Indicate that the API client is revoked.
     */
    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'revoked',
        ]);
    }

    /**
     * Indicate that the API client has admin scopes.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'scopes' => ['admin', 'read', 'write', 'delete'],
            'permissions' => [
                'user_management' => true,
                'energy_data' => true,
                'billing' => true,
                'reports' => true,
                'system_admin' => true
            ]
        ]);
    }

    /**
     * Indicate that the API client is for IoT devices.
     */
    public function iot(): static
    {
        return $this->state(fn (array $attributes) => [
            'scopes' => ['write', 'device', 'metrics'],
            'permissions' => [
                'device_management' => true,
                'energy_data' => true,
                'metrics' => true,
                'user_management' => false
            ],
            'metadata' => [
                'created_by' => 'system',
                'environment' => 'production',
                'priority' => 'high',
                'device_type' => 'iot'
            ]
        ]);
    }

    /**
     * Indicate that the API client is for mobile applications.
     */
    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'scopes' => ['read', 'write', 'user', 'organization'],
            'permissions' => [
                'user_management' => true,
                'energy_data' => true,
                'billing' => true,
                'reports' => true,
                'notifications' => true
            ],
            'metadata' => [
                'created_by' => 'system',
                'environment' => 'production',
                'priority' => 'high',
                'platform' => 'mobile'
            ]
        ]);
    }
}
