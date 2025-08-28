<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'actor_type' => $this->faker->randomElement(['user', 'system', 'api', 'cron', 'webhook']),
            'actor_identifier' => $this->faker->uuid(),
            'action' => $this->faker->randomElement([
                'create', 'update', 'delete', 'restore', 'login', 'logout', 'login_failed',
                'password_change', 'permission_change', 'api_call', 'file_upload', 'file_delete',
                'export', 'import', 'bulk_action', 'database_backup', 'cache_clear',
                'maintenance_mode', 'system_update', 'security_scan'
            ]),
            'description' => $this->faker->sentence(),
            'auditable_type' => $this->faker->optional()->randomElement([
                'App\Models\User', 'App\Models\Organization', 'App\Models\ApiClient',
                'App\Models\Collaborator', 'App\Models\FormSubmission'
            ]),
            'auditable_id' => $this->faker->optional()->numberBetween(1, 1000),
            'old_values' => $this->faker->optional()->randomElements([
                ['name' => 'Old Name', 'email' => 'old@email.com'],
                ['status' => 'inactive', 'is_active' => false],
                ['description' => 'Old description']
            ], 1),
            'new_values' => $this->faker->optional()->randomElements([
                ['name' => 'New Name', 'email' => 'new@email.com'],
                ['status' => 'active', 'is_active' => true],
                ['description' => 'New description']
            ], 1),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'url' => $this->faker->url(),
            'method' => $this->faker->randomElement(['GET', 'POST', 'PUT', 'PATCH', 'DELETE']),
            'request_data' => $this->faker->optional()->randomElements([
                ['param1' => 'value1', 'param2' => 'value2'],
                ['search' => 'query', 'filter' => 'active'],
                ['data' => 'payload']
            ], 1),
            'response_data' => $this->faker->optional()->randomElements([
                ['status' => 'success', 'message' => 'Operation completed'],
                ['error' => 'Validation failed', 'details' => 'Field required'],
                ['result' => 'Data processed']
            ], 1),
            'response_code' => $this->faker->randomElement([200, 201, 400, 401, 403, 404, 422, 500]),
            'session_id' => $this->faker->optional()->regexify('[A-Za-z0-9]{40}'),
            'request_id' => $this->faker->uuid(),
            'metadata' => [
                'browser' => $this->faker->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
                'platform' => $this->faker->randomElement(['Windows', 'macOS', 'Linux', 'iOS', 'Android']),
                'location' => $this->faker->city() . ', ' . $this->faker->country(),
                'execution_time' => $this->faker->numberBetween(10, 5000), // ms
                'memory_usage' => $this->faker->numberBetween(10, 1000), // MB
                'security_level' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
                'risk_score' => $this->faker->numberBetween(0, 100)
            ]
        ];
    }

    /**
     * Indicate that the audit log is for a user action.
     */
    public function userAction(): static
    {
        return $this->state(fn (array $attributes) => [
            'actor_type' => 'user',
            'action' => $this->faker->randomElement(['login', 'logout', 'password_change', 'profile_update']),
        ]);
    }

    /**
     * Indicate that the audit log is for a system action.
     */
    public function systemAction(): static
    {
        return $this->state(fn (array $attributes) => [
            'actor_type' => 'system',
            'action' => $this->faker->randomElement(['database_backup', 'cache_clear', 'maintenance_mode', 'system_update']),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'System Cron Job',
        ]);
    }

    /**
     * Indicate that the audit log is for an API action.
     */
    public function apiAction(): static
    {
        return $this->state(fn (array $attributes) => [
            'actor_type' => 'api',
            'action' => $this->faker->randomElement(['user_creation', 'data_export', 'bulk_update', 'webhook_delivery']),
            'user_agent' => 'API Client v2.1',
        ]);
    }

    /**
     * Indicate that the audit log is for a security event.
     */
    public function securityEvent(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => $this->faker->randomElement(['login_failed', 'permission_denied', 'suspicious_activity', 'file_access_denied']),
            'metadata' => [
                'security_level' => $this->faker->randomElement(['high', 'critical']),
                'risk_score' => $this->faker->numberBetween(70, 100),
                'requires_review' => true
            ]
        ]);
    }

    /**
     * Indicate that the audit log is for a data modification.
     */
    public function dataModification(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => $this->faker->randomElement(['create', 'update', 'delete', 'restore']),
            'auditable_type' => $this->faker->randomElement(['App\Models\User', 'App\Models\Organization', 'App\Models\ApiClient']),
            'auditable_id' => $this->faker->numberBetween(1, 1000),
        ]);
    }
}
