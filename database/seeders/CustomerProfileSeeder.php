<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomerProfile;
use App\Models\CustomerProfileContactInfo;
use App\Models\LegalDocument;
use App\Models\User;
use App\Models\Organization;
use App\Enums\AppEnums;

class CustomerProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener organizaciones existentes o crear algunas si no existen
        $organizations = Organization::all();
        if ($organizations->isEmpty()) {
            $organizations = Organization::factory(3)->create();
        }

        // Obtener usuarios existentes
        $users = User::all();
        
        // Si no hay suficientes usuarios, mostrar mensaje y salir
        if ($users->count() < 5) {
            $this->command->warn('Se necesitan al menos 5 usuarios para crear perfiles de cliente. Solo hay ' . $users->count() . ' usuarios.');
            return;
        }

        $this->command->info('🇪🇸 Creando perfiles de cliente españoles de Aragón...');

        // Crear perfiles de cliente individuales
        $this->createIndividualProfiles($users, $organizations);

        // Crear perfiles de cliente empresariales
        $this->createCompanyProfiles($users, $organizations);

        // Crear perfiles de inquilinos
        $this->createTenantProfiles($users, $organizations);

        // Crear perfiles de cambio de titularidad
        $this->createOwnershipChangeProfiles($users, $organizations);

        // Crear perfiles de cliente con información de contacto completa
        $this->createProfilesWithContactInfo($users, $organizations);

        // Crear perfiles de cliente con documentos legales
        $this->createProfilesWithLegalDocuments($users, $organizations);

        $this->command->info('✅ CustomerProfileSeeder completado. Se crearon ' . CustomerProfile::count() . ' perfiles de cliente españoles.');
    }

    /**
     * Crear perfiles de cliente individuales
     */
    private function createIndividualProfiles($users, $organizations): void
    {
        $this->command->info('👤 Creando perfiles individuales...');
        $individualUsers = $users->take(5);
        
        foreach ($individualUsers as $user) {
            $organization = $organizations->random();
            
            $profile = CustomerProfile::create([
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'profile_type' => 'individual',
                'legal_id_type' => fake()->randomElement(['dni', 'nie', 'passport']),
                'legal_id_number' => fake()->unique()->regexify('[A-Z0-9]{8,10}'),
                'legal_name' => $user->name,
                'contract_type' => 'own',
            ]);

            // Crear información de contacto para perfiles individuales
            CustomerProfileContactInfo::factory()
                ->valid()
                ->create([
                    'customer_profile_id' => $profile->id,
                    'organization_id' => $organization->id,
                    'billing_email' => $user->email,
                    'technical_email' => $user->email,
                ]);

            // Crear documentos legales para perfiles individuales
            $this->createLegalDocumentsForProfile($profile, $organization);
        }
    }

    /**
     * Crear perfiles de cliente empresariales
     */
    private function createCompanyProfiles($users, $organizations): void
    {
        $this->command->info('🏢 Creando perfiles empresariales...');
        $companyUsers = $users->slice(5, 3);
        
        foreach ($companyUsers as $user) {
            $organization = $organizations->random();
            
            $profile = CustomerProfile::create([
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'profile_type' => 'company',
                'legal_id_type' => 'cif',
                'legal_id_number' => fake()->unique()->regexify('[A-Z0-9]{8,10}'),
                'legal_name' => fake()->company(),
                'contract_type' => 'company',
            ]);

            // Crear información de contacto para empresas
            CustomerProfileContactInfo::factory()
                ->complete()
                ->create([
                    'customer_profile_id' => $profile->id,
                    'organization_id' => $organization->id,
                    'billing_email' => 'billing@' . strtolower(str_replace(' ', '', $profile->legal_name)) . '.es',
                    'technical_email' => 'tech@' . strtolower(str_replace(' ', '', $profile->legal_name)) . '.es',
                ]);

            // Crear documentos legales para empresas
            $this->createLegalDocumentsForProfile($profile, $organization, true);
        }
    }

    /**
     * Crear perfiles de inquilinos
     */
    private function createTenantProfiles($users, $organizations): void
    {
        $this->command->info('🏠 Creando perfiles de inquilinos...');
        $tenantUsers = $users->slice(8, 2);
        
        foreach ($tenantUsers as $user) {
            $organization = $organizations->random();
            
            $profile = CustomerProfile::create([
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'profile_type' => 'tenant',
                'legal_id_type' => fake()->randomElement(['dni', 'nie', 'passport']),
                'legal_id_number' => fake()->unique()->regexify('[A-Z0-9]{8,10}'),
                'legal_name' => $user->name,
                'contract_type' => 'tenant',
            ]);

            // Crear información de contacto para inquilinos
            CustomerProfileContactInfo::factory()
                ->valid()
                ->create([
                    'customer_profile_id' => $profile->id,
                    'organization_id' => $organization->id,
                    'billing_email' => $user->email,
                    'technical_email' => $user->email,
                ]);

            // Crear documentos legales para inquilinos
            $this->createLegalDocumentsForProfile($profile, $organization);
        }
    }

    /**
     * Crear perfiles de cambio de titularidad
     */
    private function createOwnershipChangeProfiles($users, $organizations): void
    {
        $this->command->info('🔄 Creando perfiles de cambio de titularidad...');
        $ownershipChangeUsers = $users->slice(10, 2);
        
        foreach ($ownershipChangeUsers as $user) {
            $organization = $organizations->random();
            
            $profile = CustomerProfile::create([
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'profile_type' => 'ownership_change',
                'legal_id_type' => fake()->randomElement(['dni', 'nie', 'passport']),
                'legal_id_number' => fake()->unique()->regexify('[A-Z0-9]{8,10}'),
                'legal_name' => $user->name,
                'contract_type' => 'ownership_change',
            ]);

            // Crear información de contacto para cambios de titularidad
            CustomerProfileContactInfo::factory()
                ->valid()
                ->create([
                    'customer_profile_id' => $profile->id,
                    'organization_id' => $organization->id,
                    'billing_email' => $user->email,
                    'technical_email' => $user->email,
                ]);

            // Crear documentos legales para cambios de titularidad
            $this->createLegalDocumentsForProfile($profile, $organization);
        }
    }

    /**
     * Crear perfiles con información de contacto completa
     */
    private function createProfilesWithContactInfo($users, $organizations): void
    {
        $this->command->info('📍 Creando perfiles con información de contacto completa...');
        $contactUsers = $users->take(3);
        
        foreach ($contactUsers as $user) {
            $organization = $organizations->random();
            
            $profile = CustomerProfile::create([
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'profile_type' => 'individual',
                'legal_id_type' => fake()->randomElement(['dni', 'nie', 'passport']),
                'legal_id_number' => fake()->unique()->regexify('[A-Z0-9]{8,10}'),
                'legal_name' => $user->name,
                'contract_type' => 'own',
            ]);

            // Crear información de contacto completa
            CustomerProfileContactInfo::factory()
                ->complete()
                ->zaragoza() // Usar Zaragoza como principal
                ->create([
                    'customer_profile_id' => $profile->id,
                    'organization_id' => $organization->id,
                    'billing_email' => $user->email,
                    'technical_email' => 'tech.' . $user->email,
                ]);
        }
    }

    /**
     * Crear perfiles con documentos legales
     */
    private function createProfilesWithLegalDocuments($users, $organizations): void
    {
        $this->command->info('📄 Creando perfiles con documentos legales...');
        $documentUsers = $users->slice(3, 4);
        
        foreach ($documentUsers as $user) {
            $organization = $organizations->random();
            
            $profile = CustomerProfile::create([
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'profile_type' => 'individual',
                'legal_id_type' => fake()->randomElement(['dni', 'nie', 'passport']),
                'legal_id_number' => fake()->unique()->regexify('[A-Z0-9]{8,10}'),
                'legal_name' => $user->name,
                'contract_type' => 'own',
            ]);

            // Crear información de contacto
            CustomerProfileContactInfo::factory()
                ->valid()
                ->create([
                    'customer_profile_id' => $profile->id,
                    'organization_id' => $organization->id,
                    'billing_email' => $user->email,
                ]);

            // Crear documentos legales completos
            $this->createLegalDocumentsForProfile($profile, $organization, false, true);
        }
    }

    /**
     * Crear documentos legales para un perfil
     */
    private function createLegalDocumentsForProfile($profile, $organization, $isCompany = false, $complete = false): void
    {
        // Documento de identificación - usar 'dni' para todos ya que 'cif' no está permitido
        LegalDocument::create([
            'customer_profile_id' => $profile->id,
            'organization_id' => $organization->id,
            'type' => 'dni',
            'version' => '1.0',
            'uploaded_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'verified_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'verifier_user_id' => $profile->user_id,
            'notes' => fake()->paragraph(),
            'expires_at' => fake()->optional(0.6)->dateTimeBetween('now', '+2 years'),
        ]);

        // Justificante IBAN
        if ($complete || $isCompany) {
            LegalDocument::create([
                'customer_profile_id' => $profile->id,
                'organization_id' => $organization->id,
                'type' => 'iban_receipt',
                'version' => '1.0',
                'uploaded_at' => fake()->dateTimeBetween('-6 months', 'now'),
                'verified_at' => fake()->dateTimeBetween('-3 months', 'now'),
                'verifier_user_id' => $profile->user_id,
                'notes' => fake()->paragraph(),
                'expires_at' => fake()->optional(0.6)->dateTimeBetween('now', '+2 years'),
            ]);
        }

        // Contrato
        LegalDocument::create([
            'customer_profile_id' => $profile->id,
            'organization_id' => $organization->id,
            'type' => 'contract',
            'version' => '1.0',
            'uploaded_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'verified_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'verifier_user_id' => $profile->user_id,
            'notes' => fake()->paragraph(),
            'expires_at' => fake()->optional(0.6)->dateTimeBetween('now', '+2 years'),
        ]);

        // Factura (opcional)
        if ($complete) {
            LegalDocument::create([
                'customer_profile_id' => $profile->id,
                'organization_id' => $organization->id,
                'type' => 'invoice',
                'version' => '1.0',
                'uploaded_at' => fake()->dateTimeBetween('-6 months', 'now'),
                'verified_at' => fake()->dateTimeBetween('-3 months', 'now'),
                'verifier_user_id' => $profile->user_id,
                'notes' => fake()->paragraph(),
                'expires_at' => fake()->optional(0.6)->dateTimeBetween('now', '+2 years'),
            ]);
        }
    }
}
