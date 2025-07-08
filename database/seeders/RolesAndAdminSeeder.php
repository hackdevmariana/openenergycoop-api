<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str; // <-- Import necesario

class RolesAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['admin', 'gestor', 'tecnico', 'usuario'];

        // Crear roles base
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Crear permiso "access filament" si no existe
        $accessFilament = Permission::firstOrCreate([
            'name' => 'access filament',
            'guard_name' => 'web',
        ]);

        // Asegurar que el rol admin tenga el permiso
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && !$adminRole->hasPermissionTo('access filament')) {
            $adminRole->givePermissionTo($accessFilament);
        }

        // Crear usuario admin si no existe
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Administrador',
                'password' => bcrypt('password'), // Cambiar en producción
            ]
        );

        // Asignar rol admin si aún no lo tiene
        if (! $adminUser->hasRole('admin')) {
            $adminUser->assignRole('admin');
        }

        // Crear usuario test si no existe
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'remember_token' => Str::random(10),
            ]
        );

        $this->command->info('Roles base creados: ' . implode(', ', $roles));
        $this->command->info('Permiso access filament creado y asignado al rol admin');
        $this->command->info('Usuario admin creado: admin@demo.com / password');
        $this->command->info('Usuario test creado: test@example.com / password');
    }
}
