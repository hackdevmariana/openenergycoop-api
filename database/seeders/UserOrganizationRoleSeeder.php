<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationRole;
use App\Models\UserOrganizationRole;
use Illuminate\Support\Facades\DB;

class UserOrganizationRoleSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('👥 Creando asignaciones de roles de usuario en organizaciones...');
        
        // Limpiar asignaciones existentes
        UserOrganizationRole::query()->delete();
        
        // Obtener usuarios, organizaciones y roles disponibles
        $users = User::all();
        $organizations = Organization::all();
        $organizationRoles = OrganizationRole::all();
        
        if ($users->isEmpty() || $organizations->isEmpty() || $organizationRoles->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios, organizaciones o roles disponibles. No se pueden crear asignaciones.');
            return;
        }
        
        $this->command->info("📊 Usuarios disponibles: {$users->count()}");
        $this->command->info("🏢 Organizaciones disponibles: {$organizations->count()}");
        $this->command->info("🎭 Roles disponibles: {$organizationRoles->count()}");
        
        // Crear asignaciones de roles
        $this->createUserOrganizationRoles($users, $organizations, $organizationRoles);
        
        $this->command->info('✅ UserOrganizationRoleSeeder completado. Se crearon ' . UserOrganizationRole::count() . ' asignaciones de roles.');
    }
    
    private function createUserOrganizationRoles($users, $organizations, $organizationRoles): void
    {
        $assignments = [];
        $assignmentCount = 0;
        
        foreach ($organizations as $organization) {
            $this->command->info("🏢 Asignando roles en la organización: {$organization->name}");
            
            // Obtener roles específicos de esta organización
            $orgRoles = $organizationRoles->where('organization_id', $organization->id);
            
            if ($orgRoles->isEmpty()) {
                $this->command->warn("⚠️ No hay roles disponibles para la organización: {$organization->name}");
                continue;
            }
            
            // Asignar al menos un administrador por organización
            $adminRole = $orgRoles->firstWhere('name', 'Administrador General');
            if ($adminRole) {
                $adminUser = $users->random();
                $assignments[] = [
                    'user_id' => $adminUser->id,
                    'organization_id' => $organization->id,
                    'organization_role_id' => $adminRole->id,
                    'assigned_at' => now()->subDays(rand(30, 365)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $assignmentCount++;
                
                $this->command->info("👑 Usuario {$adminUser->name} asignado como Administrador General en {$organization->name}");
            }
            
            // Asignar otros roles según el tamaño de la organización
            $roleCount = min(rand(2, 5), $orgRoles->count()); // Entre 2 y 5 roles por organización
            
            // Seleccionar roles aleatorios (excluyendo el administrador ya asignado)
            $availableRoles = $orgRoles->where('id', '!=', $adminRole?->id)->shuffle()->take($roleCount - 1);
            
            foreach ($availableRoles as $role) {
                // Seleccionar un usuario aleatorio (puede ser el mismo que el admin)
                $user = $users->random();
                
                $assignments[] = [
                    'user_id' => $user->id,
                    'organization_id' => $organization->id,
                    'organization_role_id' => $role->id,
                    'assigned_at' => now()->subDays(rand(1, 180)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $assignmentCount++;
                
                $this->command->info("👤 Usuario {$user->name} asignado como {$role->name} en {$organization->name}");
            }
            
            // Crear algunas asignaciones múltiples (usuarios con varios roles en la misma organización)
            if (rand(1, 3) === 1) { // 33% de probabilidad
                $multiRoleUser = $users->random();
                $additionalRole = $orgRoles->where('id', '!=', $adminRole?->id)->random();
                
                if ($additionalRole) {
                    $assignments[] = [
                        'user_id' => $multiRoleUser->id,
                        'organization_id' => $organization->id,
                        'organization_role_id' => $additionalRole->id,
                        'assigned_at' => now()->subDays(rand(1, 90)),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $assignmentCount++;
                    
                    $this->command->info("🔄 Usuario {$multiRoleUser->name} tiene rol adicional: {$additionalRole->name} en {$organization->name}");
                }
            }
        }
        
        // Crear algunas asignaciones de usuarios que participan en múltiples organizaciones
        $this->createCrossOrganizationAssignments($users, $organizations, $organizationRoles, $assignments, $assignmentCount);
        
        // Insertar todas las asignaciones en la base de datos
        if (!empty($assignments)) {
            DB::table('user_organization_roles')->insert($assignments);
            $this->command->info("💾 Se insertaron {$assignmentCount} asignaciones de roles en la base de datos");
        }
    }
    
    private function createCrossOrganizationAssignments($users, $organizations, $organizationRoles, &$assignments, &$assignmentCount): void
    {
        $this->command->info("🌐 Creando asignaciones de usuarios en múltiples organizaciones...");
        
        // Seleccionar algunos usuarios para que participen en múltiples organizaciones
        $multiOrgUsers = $users->shuffle()->take(min(3, $users->count()));
        
        foreach ($multiOrgUsers as $user) {
            $userOrgs = $organizations->shuffle()->take(rand(2, min(3, $organizations->count())));
            
            foreach ($userOrgs as $org) {
                $orgRoles = $organizationRoles->where('organization_id', $org->id);
                
                if ($orgRoles->isNotEmpty()) {
                    $role = $orgRoles->random();
                    
                    $assignments[] = [
                        'user_id' => $user->id,
                        'organization_id' => $org->id,
                        'organization_role_id' => $role->id,
                        'assigned_at' => now()->subDays(rand(1, 120)),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $assignmentCount++;
                    
                    $this->command->info("🌍 Usuario {$user->name} asignado como {$role->name} en {$org->name} (organización adicional)");
                }
            }
        }
    }
}
