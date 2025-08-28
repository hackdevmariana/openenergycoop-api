<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Collaborator;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;

class CollaboratorSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🤝 Creando colaboradores para la cooperativa energética...');
        
        // Limpiar colaboradores existentes
        Collaborator::query()->delete();
        
        // Obtener organizaciones y usuarios disponibles
        $organizations = Organization::all();
        $users = User::all();
        
        if ($organizations->isEmpty()) {
            $this->command->warn('⚠️ No hay organizaciones disponibles. No se pueden crear colaboradores.');
            return;
        }
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. No se pueden crear colaboradores.');
            return;
        }
        
        $this->command->info("🏢 Organizaciones disponibles: {$organizations->count()}");
        $this->command->info("👥 Usuarios disponibles: {$users->count()}");
        
        // Crear colaboradores para cada organización
        foreach ($organizations as $organization) {
            $this->createCollaboratorsForOrganization($organization, $users);
        }
        
        // Crear algunos colaboradores globales (sin organización específica)
        $this->createGlobalCollaborators($users);
        
        $this->command->info('✅ CollaboratorSeeder completado. Se crearon ' . Collaborator::count() . ' colaboradores.');
    }
    
    private function createCollaboratorsForOrganization(Organization $organization, $users): void
    {
        $this->command->info("🏢 Creando colaboradores para la organización: {$organization->name}");
        
        // Socio principal de la organización
        $this->createPartnerCollaborator($organization, $users, 1);
        
        // Patrocinador de la organización
        $this->createSponsorCollaborator($organization, $users, 2);
        
        // Miembro activo
        $this->createMemberCollaborator($organization, $users, 3);
        
        // Proveedor de servicios
        $this->createSupporterCollaborator($organization, $users, 4);
        
        // Colaborador institucional
        $this->createInstitutionalCollaborator($organization, $users, 5);
    }
    
    private function createPartnerCollaborator(Organization $organization, $users, int $order): void
    {
        $partnerNames = [
            'Energía Verde S.L.',
            'Renovables del Sur',
            'EcoPower Solutions',
            'SolarTech Pro',
            'WindEnergy Corp',
            'GreenGrid Systems',
            'CleanPower Partners',
            'Sustainable Energy Co.',
            'EcoFriendly Power',
            'GreenTech Solutions'
        ];
        
        $name = $partnerNames[array_rand($partnerNames)];
        
        Collaborator::create([
            'name' => $name,
            'logo' => 'collaborators/' . strtolower(str_replace(' ', '_', $name)) . '_logo.png',
            'url' => 'https://' . strtolower(str_replace(' ', '', $name)) . '.com',
            'description' => "Socio estratégico de {$organization->name} especializado en soluciones de energía renovable y sostenibilidad.",
            'order' => $order,
            'is_active' => true,
            'collaborator_type' => 'partner',
            'organization_id' => $organization->id,
            'is_draft' => false,
            'published_at' => Carbon::now()->subMonths(rand(1, 6)),
            'created_by_user_id' => $users->random()->id,
        ]);
        
        $this->command->info("🤝 Socio creado: {$name}");
    }
    
    private function createSponsorCollaborator(Organization $organization, $users, int $order): void
    {
        $sponsorNames = [
            'Fundación Energía Sostenible',
            'Instituto de Tecnología Verde',
            'Asociación de Energías Renovables',
            'Consorcio Solar Español',
            'Fundación Eólica Europea',
            'Green Investment Fund',
            'Sustainable Development Trust',
            'Renewable Energy Foundation',
            'Eco-Innovation Center',
            'Clean Energy Alliance'
        ];
        
        $name = $sponsorNames[array_rand($sponsorNames)];
        
        Collaborator::create([
            'name' => $name,
            'logo' => 'collaborators/' . strtolower(str_replace(' ', '_', $name)) . '_logo.png',
            'url' => 'https://' . strtolower(str_replace(' ', '', $name)) . '.org',
            'description' => "Patrocinador institucional que apoya los proyectos de {$organization->name} en el desarrollo de energías renovables.",
            'order' => $order,
            'is_active' => true,
            'collaborator_type' => 'sponsor',
            'organization_id' => $organization->id,
            'is_draft' => false,
            'published_at' => Carbon::now()->subMonths(rand(1, 8)),
            'created_by_user_id' => $users->random()->id,
        ]);
        
        $this->command->info("🏆 Patrocinador creado: {$name}");
    }
    
    private function createMemberCollaborator(Organization $organization, $users, int $order): void
    {
        $memberNames = [
            'Asociación de Consumidores Verdes',
            'Colectivo de Energía Comunitaria',
            'Grupo de Inversores Sostenibles',
            'Red de Cooperativas Energéticas',
            'Alianza por la Transición Energética',
            'Movimiento Ciudadano Verde',
            'Colectivo de Autoconsumo',
            'Asociación de Pequeños Productores',
            'Red de Comunidades Sostenibles',
            'Grupo de Acción Climática'
        ];
        
        $name = $memberNames[array_rand($memberNames)];
        
        Collaborator::create([
            'name' => $name,
            'logo' => 'collaborators/' . strtolower(str_replace(' ', '_', $name)) . '_logo.png',
            'url' => 'https://' . strtolower(str_replace(' ', '', $name)) . '.es',
            'description' => "Miembro activo de {$organization->name} que participa en la gobernanza y toma de decisiones de la cooperativa.",
            'order' => $order,
            'is_active' => true,
            'collaborator_type' => 'member',
            'organization_id' => $organization->id,
            'is_draft' => false,
            'published_at' => Carbon::now()->subMonths(rand(1, 12)),
            'created_by_user_id' => $users->random()->id,
        ]);
        
        $this->command->info("👥 Miembro creado: {$name}");
    }
    
    private function createSupporterCollaborator(Organization $organization, $users, int $order): void
    {
        $supporterNames = [
            'Tecnología Solar Avanzada',
            'Sistemas de Almacenamiento Verde',
            'Smart Grid Solutions',
            'Eficiencia Energética Pro',
            'Monitoreo Inteligente',
            'Software de Gestión Energética',
            'Equipos de Medición Verde',
            'Servicios de Mantenimiento Eco',
            'Consultoría Sostenible',
            'Formación en Energías Verdes'
        ];
        
        $name = $supporterNames[array_rand($supporterNames)];
        
        Collaborator::create([
            'name' => $name,
            'logo' => 'collaborators/' . strtolower(str_replace(' ', '_', $name)) . '_logo.png',
            'url' => 'https://' . strtolower(str_replace(' ', '', $name)) . '.com',
            'description' => "Proveedor de servicios que apoya a {$organization->name} con soluciones técnicas y tecnológicas especializadas.",
            'order' => $order,
            'is_active' => true,
            'collaborator_type' => 'supporter',
            'organization_id' => $organization->id,
            'is_draft' => false,
            'published_at' => Carbon::now()->subMonths(rand(1, 4)),
            'created_by_user_id' => $users->random()->id,
        ]);
        
        $this->command->info("🛠️ Proveedor creado: {$name}");
    }
    
    private function createInstitutionalCollaborator(Organization $organization, $users, int $order): void
    {
        $institutionalNames = [
            'Ministerio de Transición Ecológica',
            'Agencia de la Energía',
            'Instituto para la Diversificación',
            'Comisión Nacional de Energía',
            'Ente Regional de la Energía',
            'Agencia Local de Sostenibilidad',
            'Consejo de Energía Regional',
            'Oficina de Cambio Climático',
            'Departamento de Medio Ambiente',
            'Secretaría de Energía Verde'
        ];
        
        $name = $institutionalNames[array_rand($institutionalNames)];
        
        Collaborator::create([
            'name' => $name,
            'logo' => 'collaborators/' . strtolower(str_replace(' ', '_', $name)) . '_logo.png',
            'url' => 'https://' . strtolower(str_replace(' ', '', $name)) . '.gob.es',
            'description' => "Entidad institucional que colabora con {$organization->name} en el desarrollo de políticas y regulaciones energéticas.",
            'order' => $order,
            'is_active' => true,
            'collaborator_type' => 'partner',
            'organization_id' => $organization->id,
            'is_draft' => false,
            'published_at' => Carbon::now()->subMonths(rand(1, 10)),
            'created_by_user_id' => $users->random()->id,
        ]);
        
        $this->command->info("🏛️ Institucional creado: {$name}");
    }
    
    private function createGlobalCollaborators($users): void
    {
        $this->command->info("🌍 Creando colaboradores globales...");
        
        // Colaborador global de investigación
        Collaborator::create([
            'name' => 'Centro Nacional de Investigación en Energías Renovables',
            'logo' => 'collaborators/cnid_energia_renovable_logo.png',
            'url' => 'https://cnid-energia-renovable.es',
            'description' => 'Centro de investigación nacional que colabora con todas las cooperativas energéticas en el desarrollo de nuevas tecnologías y estudios de viabilidad.',
            'order' => 100,
            'is_active' => true,
            'collaborator_type' => 'partner',
            'organization_id' => null,
            'is_draft' => false,
            'published_at' => Carbon::now()->subMonths(3),
            'created_by_user_id' => $users->random()->id,
        ]);
        
        // Colaborador global de formación
        Collaborator::create([
            'name' => 'Academia de Energías Sostenibles',
            'logo' => 'collaborators/academia_energias_sostenibles_logo.png',
            'url' => 'https://academia-energias-sostenibles.org',
            'description' => 'Institución educativa que proporciona formación especializada en energías renovables y gestión cooperativa a todas las organizaciones del sector.',
            'order' => 101,
            'is_active' => true,
            'collaborator_type' => 'sponsor',
            'organization_id' => null,
            'is_draft' => false,
            'published_at' => Carbon::now()->subMonths(2),
            'created_by_user_id' => $users->random()->id,
        ]);
        
        // Colaborador global de certificación
        Collaborator::create([
            'name' => 'Organismo de Certificación Energética Verde',
            'logo' => 'collaborators/oc_energia_verde_logo.png',
            'url' => 'https://oc-energia-verde.es',
            'description' => 'Entidad certificadora que garantiza la calidad y sostenibilidad de los proyectos energéticos de las cooperativas.',
            'order' => 102,
            'is_active' => true,
            'collaborator_type' => 'supporter',
            'organization_id' => null,
            'is_draft' => false,
            'published_at' => Carbon::now()->subMonths(1),
            'created_by_user_id' => $users->random()->id,
        ]);
        
        $this->command->info("🌍 Colaboradores globales creados");
    }
}
