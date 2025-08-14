<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 1;
        
        return [
            'name' => 'User ' . $counter++,
            'email' => 'user' . $counter . '@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Asignar rol de super admin
     */
    public function superAdmin(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::where('name', 'super-admin')->first();
            if ($role) {
                $user->assignRole($role);
            }
        });
    }

    /**
     * Asignar rol de admin
     */
    public function admin(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::where('name', 'admin')->first();
            if ($role) {
                $user->assignRole($role);
            }
        });
    }

    /**
     * Asignar rol de manager
     */
    public function manager(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::where('name', 'manager')->first();
            if ($role) {
                $user->assignRole($role);
            }
        });
    }

    /**
     * Asignar rol de agent
     */
    public function agent(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::where('name', 'agent')->first();
            if ($role) {
                $user->assignRole($role);
            }
        });
    }

    /**
     * Asignar rol de customer
     */
    public function customer(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::where('name', 'customer')->first();
            if ($role) {
                $user->assignRole($role);
            }
        });
    }
}
