<?php

namespace Database\Factories;

use App\Models\UserDevice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserDevice>
 */
class UserDeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $deviceTypes = array_keys(UserDevice::DEVICE_TYPES);
        $platforms = array_keys(UserDevice::PLATFORMS);
        
        return [
            'user_id' => User::factory(),
            'device_name' => $this->faker->optional(0.6)->randomElement([
                'iPhone de Juan',
                'MacBook Pro',
                'Samsung Galaxy',
                'iPad',
                'Laptop Personal',
                'PC Oficina',
                'Chrome Browser',
            ]),
            'device_type' => $this->faker->randomElement($deviceTypes),
            'platform' => $this->faker->randomElement($platforms),
            'last_seen_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'push_token' => $this->faker->optional(0.4)->regexify('[a-zA-Z0-9]{64}'),
            'user_agent' => $this->faker->userAgent(),
            'ip_address' => $this->faker->ipv4(),
            'is_current' => $this->faker->boolean(20), // 20% probabilidad de ser el dispositivo actual
            'revoked_at' => $this->faker->optional(0.05)->dateTimeBetween('-3 months', 'now'),
        ];
    }

    /**
     * Estado para dispositivo activo (no revocado)
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'revoked_at' => null,
        ]);
    }

    /**
     * Estado para dispositivo revocado
     */
    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'revoked_at' => $this->faker->dateTimeBetween($attributes['last_seen_at'] ?? '-1 month', 'now'),
            'is_current' => false,
        ]);
    }

    /**
     * Estado para dispositivo actual
     */
    public function current(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_current' => true,
            'revoked_at' => null,
            'last_seen_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
        ]);
    }

    /**
     * Estado para dispositivo móvil
     */
    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => 'mobile',
            'platform' => $this->faker->randomElement(['iOS', 'Android']),
            'push_token' => $this->faker->regexify('[a-zA-Z0-9]{64}'),
        ]);
    }

    /**
     * Estado para dispositivo web
     */
    public function web(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => 'web',
            'platform' => 'Web',
            'push_token' => null,
        ]);
    }

    /**
     * Estado para dispositivo con push token
     */
    public function withPushToken(): static
    {
        return $this->state(fn (array $attributes) => [
            'push_token' => $this->faker->regexify('[a-zA-Z0-9]{64}'),
        ]);
    }

    /**
     * Estado para dispositivo recientemente activo
     */
    public function recentlyActive(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_seen_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
        ]);
    }
}
