<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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
                'password' => bcrypt('password'), // cámbiala en producción
            ]
        );

        // Asignar rol admin si aún no lo tiene
        if (! $adminUser->hasRole('admin')) {
            $adminUser->assignRole('admin');
        }

        $this->command->info('Roles base creados: ' . implode(', ', $roles));
        $this->command->info('Permiso access filament creado y asignado al rol admin');
        $this->command->info('Usuario admin creado: admin@demo.com / password');
    }
}
