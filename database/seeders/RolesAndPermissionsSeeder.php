<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos para CustomerProfile
        $customerProfilePermissions = [
            'customer-profile.view-any',      // Ver todos los perfiles
            'customer-profile.view-own',      // Ver perfiles propios
            'customer-profile.view-org',      // Ver perfiles de la organización
            'customer-profile.create',        // Crear perfiles
            'customer-profile.update-own',    // Actualizar perfiles propios
            'customer-profile.update-org',    // Actualizar perfiles de la organización
            'customer-profile.delete-own',    // Eliminar perfiles propios
            'customer-profile.delete-org',    // Eliminar perfiles de la organización
            'customer-profile.manage-all',    // Gestionar todos los perfiles (admin)
        ];

        foreach ($customerProfilePermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear permisos para Organization
        $organizationPermissions = [
            'organization.view',
            'organization.create',
            'organization.update',
            'organization.delete',
            'organization.manage-users',
        ];

        foreach ($organizationPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear permisos para User
        $userPermissions = [
            'user.view-own',
            'user.view-org',
            'user.view-all',
            'user.create',
            'user.update-own',
            'user.update-org',
            'user.delete-own',
            'user.delete-org',
        ];

        foreach ($userPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear roles
        $roles = [
            'super-admin' => Permission::all()->pluck('name')->toArray(),
            'admin' => [
                'customer-profile.view-any',
                'customer-profile.view-org',
                'customer-profile.create',
                'customer-profile.update-org',
                'customer-profile.delete-org',
                'organization.view',
                'organization.update',
                'organization.manage-users',
                'user.view-org',
                'user.create',
                'user.update-org',
                'user.delete-org',
            ],
            'manager' => [
                'customer-profile.view-org',
                'customer-profile.create',
                'customer-profile.update-org',
                'customer-profile.view-own',
                'customer-profile.update-own',
                'organization.view',
                'user.view-org',
                'user.view-own',
                'user.update-own',
            ],
            'agent' => [
                'customer-profile.view-org',
                'customer-profile.view-own',
                'customer-profile.create',
                'customer-profile.update-own',
                'organization.view',
                'user.view-own',
                'user.update-own',
            ],
            'customer' => [
                'customer-profile.view-own',
                'customer-profile.update-own',
                'user.view-own',
                'user.update-own',
            ],
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->givePermissionTo($permissions);
        }

        $this->command->info('Roles y permisos creados exitosamente!');
        $this->command->info('Roles disponibles: ' . implode(', ', array_keys($roles)));
    }
}
