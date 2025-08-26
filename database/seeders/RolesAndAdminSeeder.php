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
            ['email' => 'admin@aragon.es'],
            [
                'name' => 'Antonio García López',
                'password' => bcrypt('password'), // Cambiar en producción
            ]
        );

        // Asignar rol admin si aún no lo tiene
        if (! $adminUser->hasRole('admin')) {
            $adminUser->assignRole('admin');
        }

        // Crear usuario test si no existe
        User::firstOrCreate(
            ['email' => 'test@aragon.es'],
            [
                'name' => 'María Carmen Rodríguez Fernández',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'remember_token' => Str::random(10),
            ]
        );

        // Crear usuario gestor si no existe
        User::firstOrCreate(
            ['email' => 'gestor@aragon.es'],
            [
                'name' => 'José Manuel López Martínez',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'remember_token' => Str::random(10),
            ]
        );

        // Crear usuario técnico si no existe
        User::firstOrCreate(
            ['email' => 'tecnico@aragon.es'],
            [
                'name' => 'Francisco Javier Sánchez Pérez',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'remember_token' => Str::random(10),
            ]
        );

        $this->command->info('Roles base creados: ' . implode(', ', $roles));
        $this->command->info('Permiso access filament creado y asignado al rol admin');
        $this->command->info('Usuario admin creado: admin@aragon.es / password');
        $this->command->info('Usuario test creado: test@aragon.es / password');
        $this->command->info('Usuario gestor creado: gestor@aragon.es / password');
        $this->command->info('Usuario técnico creado: tecnico@aragon.es / password');
    }
}
