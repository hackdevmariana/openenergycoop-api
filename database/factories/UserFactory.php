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
        $spanishData = config('spanish_data');
        
        // Generar nombre español aleatorio
        $firstName = $this->faker->randomElement([
            ...$spanishData['spanish_names']['male'],
            ...$spanishData['spanish_names']['female']
        ]);
        
        $surname1 = $this->faker->randomElement($spanishData['spanish_names']['surnames']);
        $surname2 = $this->faker->randomElement($spanishData['spanish_names']['surnames']);
        
        $fullName = $firstName . ' ' . $surname1 . ' ' . $surname2;
        
        // Generar email basado en el nombre
        $email = strtolower(
            str_replace([' ', 'á', 'é', 'í', 'ó', 'ú', 'ñ'], 
            ['', 'a', 'e', 'i', 'o', 'u', 'n'], 
            $firstName . '.' . $surname1
        ) . '@example.es'
        );
        
        return [
            'name' => $fullName,
            'email' => $email,
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

    /**
     * Crear usuario con nombre específico de Aragón
     */
    public function aragon(): static
    {
        return $this->state(function (array $attributes) {
            $spanishData = config('spanish_data');
            
            // Usar nombres más comunes en Aragón
            $aragonNames = [
                'Antonio', 'Manuel', 'José', 'Francisco', 'María', 'Carmen', 'Ana', 'Isabel'
            ];
            
            $firstName = $this->faker->randomElement($aragonNames);
            $surname1 = $this->faker->randomElement($spanishData['spanish_names']['surnames']);
            $surname2 = $this->faker->randomElement($spanishData['spanish_names']['surnames']);
            
            $fullName = $firstName . ' ' . $surname1 . ' ' . $surname2;
            
            $email = strtolower(
                str_replace([' ', 'á', 'é', 'í', 'ó', 'ú', 'ñ'], 
                ['', 'a', 'e', 'i', 'o', 'u', 'n'], 
                $firstName . '.' . $surname1
                ) . '@aragon.es'
            );
            
            return [
                'name' => $fullName,
                'email' => $email,
            ];
        });
    }
}
