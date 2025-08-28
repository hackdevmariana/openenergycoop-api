<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FormSubmission;
use App\Models\User;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FormSubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📝 Creando envíos de formularios para el sistema...');
        
        // Limpiar envíos existentes
        FormSubmission::query()->delete();
        
        // Obtener usuarios y organizaciones disponibles
        $users = User::all();
        $organizations = Organization::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. No se pueden crear envíos de formularios.');
            return;
        }
        
        $this->command->info("👥 Usuarios disponibles: {$users->count()}");
        $this->command->info("🏢 Organizaciones disponibles: {$organizations->count()}");
        
        // Crear envíos de formularios para diferentes tipos
        $this->createContactFormSubmissions($users, $organizations);
        $this->createNewsletterSubscriptions($users, $organizations);
        $this->createSurveySubmissions($users, $organizations);
        $this->createFeedbackSubmissions($users, $organizations);
        $this->createSupportRequests($users, $organizations);
        $this->createRegistrationForms($users, $organizations);
        $this->createQuoteRequests($users, $organizations);
        $this->createPartnershipRequests($users, $organizations);
        $this->createJobApplications($users, $organizations);
        $this->createVolunteerApplications($users, $organizations);
        $this->createSpamSubmissions($users, $organizations);
        
        $this->command->info('✅ FormSubmissionSeeder completado. Se crearon ' . FormSubmission::count() . ' envíos de formularios.');
    }
    
    private function createContactFormSubmissions($users, $organizations): void
    {
        $this->command->info('📞 Creando envíos de formularios de contacto...');
        
        $contactTypes = [
            'general' => 'Consulta General',
            'technical' => 'Soporte Técnico',
            'commercial' => 'Consulta Comercial',
            'partnership' => 'Propuesta de Colaboración',
            'complaint' => 'Reclamo',
            'suggestion' => 'Sugerencia'
        ];
        
        foreach ($contactTypes as $type => $description) {
            for ($i = 0; $i < rand(3, 8); $i++) {
                $organization = $organizations->random();
                $user = $users->random();
                
                $fields = [
                    'name' => fake()->name(),
                    'email' => fake()->safeEmail(),
                    'phone' => fake()->optional(0.7)->phoneNumber(),
                    'subject' => $description,
                    'message' => fake()->paragraph(rand(2, 4)),
                    'contact_type' => $type,
                    'company' => fake()->optional(0.6)->company(),
                    'preferred_contact' => fake()->randomElement(['email', 'phone', 'both']),
                    'urgency' => fake()->randomElement(['low', 'medium', 'high']),
                    'source' => fake()->randomElement(['website', 'google', 'social_media', 'referral'])
                ];
                
                $status = fake()->randomElement(['pending', 'processed', 'archived']);
                $processedBy = $status === 'processed' ? $users->random() : null;
                
                FormSubmission::create([
                    'form_name' => 'contact',
                    'fields' => $fields,
                    'status' => $status,
                    'source_url' => fake()->url(),
                    'referrer' => fake()->optional(0.6)->url(),
                    'ip_address' => $this->generateRandomIP(),
                    'user_agent' => $this->generateRandomUserAgent(),
                    'processed_at' => $status === 'processed' ? Carbon::now()->subDays(rand(1, 30)) : null,
                    'processed_by_user_id' => $processedBy ? $processedBy->id : null,
                    'processing_notes' => $status === 'processed' ? fake()->sentence() : null,
                    'organization_id' => $organization->id,
                    'created_at' => Carbon::now()->subDays(rand(0, 90)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 90))
                ]);
            }
        }
        
        $this->command->info("📞 Envíos de formularios de contacto creados");
    }
    
    private function createNewsletterSubscriptions($users, $organizations): void
    {
        $this->command->info('📧 Creando suscripciones a newsletters...');
        
        $interests = [
            'energy' => 'Energías Renovables',
            'environment' => 'Medio Ambiente',
            'sustainability' => 'Sostenibilidad',
            'technology' => 'Tecnología',
            'cooperatives' => 'Cooperativas',
            'renewable_energy' => 'Energía Renovable',
            'green_tech' => 'Tecnología Verde',
            'climate_change' => 'Cambio Climático'
        ];
        
        foreach ($organizations as $organization) {
            for ($i = 0; $i < rand(5, 15); $i++) {
                $user = $users->random();
                
                $fields = [
                    'email' => fake()->safeEmail(),
                    'name' => fake()->optional(0.8)->name(),
                    'interests' => fake()->randomElements(array_keys($interests), rand(2, 4)),
                    'frequency' => fake()->randomElement(['weekly', 'biweekly', 'monthly', 'quarterly']),
                    'language' => fake()->randomElement(['es', 'en', 'ca', 'eu']),
                    'newsletter_type' => fake()->randomElement(['general', 'technical', 'commercial', 'updates']),
                    'consent_marketing' => fake()->boolean(80),
                    'consent_analytics' => fake()->boolean(70),
                    'source' => fake()->randomElement(['website', 'social_media', 'email_campaign', 'referral'])
                ];
                
                $status = fake()->randomElement(['pending', 'processed']);
                $processedBy = $status === 'processed' ? $users->random() : null;
                
                FormSubmission::create([
                    'form_name' => 'newsletter',
                    'fields' => $fields,
                    'status' => $status,
                    'source_url' => fake()->url(),
                    'referrer' => fake()->optional(0.5)->url(),
                    'ip_address' => $this->generateRandomIP(),
                    'user_agent' => $this->generateRandomUserAgent(),
                    'processed_at' => $status === 'processed' ? Carbon::now()->subDays(rand(1, 15)) : null,
                    'processed_by_user_id' => $processedBy ? $processedBy->id : null,
                    'processing_notes' => $status === 'processed' ? 'Suscripción confirmada' : null,
                    'organization_id' => $organization->id,
                    'created_at' => Carbon::now()->subDays(rand(0, 60)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 60))
                ]);
            }
        }
        
        $this->command->info("📧 Suscripciones a newsletters creadas");
    }
    
    private function createSurveySubmissions($users, $organizations): void
    {
        $this->command->info('📊 Creando envíos de encuestas...');
        
        $surveyTypes = [
            'customer_satisfaction' => 'Satisfacción del Cliente',
            'service_quality' => 'Calidad del Servicio',
            'energy_usage' => 'Uso de Energía',
            'sustainability_awareness' => 'Conciencia Sostenible',
            'cooperative_governance' => 'Gobernanza Cooperativa',
            'technology_adoption' => 'Adopción de Tecnología'
        ];
        
        foreach ($surveyTypes as $type => $title) {
            for ($i = 0; $i < rand(10, 25); $i++) {
                $organization = $organizations->random();
                $user = $users->random();
                
                $fields = [
                    'survey_type' => $type,
                    'email' => fake()->optional(0.6)->safeEmail(),
                    'age_group' => fake()->randomElement(['18-25', '26-35', '36-45', '46-55', '55+']),
                    'gender' => fake()->optional(0.7)->randomElement(['male', 'female', 'other', 'prefer_not_to_say']),
                    'location' => fake()->city() . ', ' . fake()->state(),
                    'satisfaction_overall' => fake()->numberBetween(1, 10),
                    'satisfaction_service' => fake()->numberBetween(1, 10),
                    'satisfaction_communication' => fake()->numberBetween(1, 10),
                    'satisfaction_technology' => fake()->numberBetween(1, 10),
                    'recommendation_likelihood' => fake()->randomElement(['very_likely', 'likely', 'neutral', 'unlikely', 'very_unlikely']),
                    'energy_awareness' => fake()->randomElement(['very_high', 'high', 'medium', 'low', 'very_low']),
                    'sustainability_importance' => fake()->randomElement(['critical', 'very_important', 'important', 'somewhat_important', 'not_important']),
                    'comments' => fake()->optional(0.8)->paragraph(rand(1, 3)),
                    'improvement_suggestions' => fake()->optional(0.6)->paragraph(rand(1, 2)),
                    'contact_permission' => fake()->boolean(40)
                ];
                
                $status = fake()->randomElement(['pending', 'processed', 'archived']);
                $processedBy = $status === 'processed' ? $users->random() : null;
                
                FormSubmission::create([
                    'form_name' => 'survey',
                    'fields' => $fields,
                    'status' => $status,
                    'source_url' => fake()->url(),
                    'referrer' => fake()->optional(0.4)->url(),
                    'ip_address' => $this->generateRandomIP(),
                    'user_agent' => $this->generateRandomUserAgent(),
                    'processed_at' => $status === 'processed' ? Carbon::now()->subDays(rand(1, 45)) : null,
                    'processed_by_user_id' => $processedBy ? $processedBy->id : null,
                    'processing_notes' => $status === 'processed' ? 'Encuesta procesada y analizada' : null,
                    'organization_id' => $organization->id,
                    'created_at' => Carbon::now()->subDays(rand(0, 120)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 120))
                ]);
            }
        }
        
        $this->command->info("📊 Envíos de encuestas creados");
    }
    
    private function createFeedbackSubmissions($users, $organizations): void
    {
        $this->command->info('💬 Creando envíos de retroalimentación...');
        
        $feedbackCategories = [
            'bug' => 'Error o Bug',
            'feature' => 'Nueva Funcionalidad',
            'improvement' => 'Mejora',
            'complaint' => 'Queja',
            'praise' => 'Elogio',
            'suggestion' => 'Sugerencia',
            'question' => 'Pregunta'
        ];
        
        foreach ($feedbackCategories as $category => $label) {
            for ($i = 0; $i < rand(2, 6); $i++) {
                $organization = $organizations->random();
                $user = $users->random();
                
                $fields = [
                    'name' => fake()->optional(0.7)->name(),
                    'email' => fake()->optional(0.8)->safeEmail(),
                    'category' => $category,
                    'rating' => fake()->numberBetween(1, 5),
                    'feedback' => fake()->paragraph(rand(2, 4)),
                    'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
                    'user_type' => fake()->randomElement(['customer', 'member', 'employee', 'partner', 'visitor']),
                    'affected_feature' => fake()->optional(0.6)->randomElement(['website', 'mobile_app', 'billing_system', 'energy_monitoring', 'customer_portal']),
                    'browser' => fake()->optional(0.5)->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
                    'device' => fake()->optional(0.5)->randomElement(['desktop', 'mobile', 'tablet']),
                    'reproducible' => fake()->optional(0.7)->boolean(),
                    'steps_to_reproduce' => fake()->optional(0.6)->paragraph(rand(1, 2))
                ];
                
                $status = fake()->randomElement(['pending', 'processed', 'archived']);
                $processedBy = $status === 'processed' ? $users->random() : null;
                
                FormSubmission::create([
                    'form_name' => 'feedback',
                    'fields' => $fields,
                    'status' => $status,
                    'source_url' => fake()->url(),
                    'referrer' => fake()->optional(0.5)->url(),
                    'ip_address' => $this->generateRandomIP(),
                    'user_agent' => $this->generateRandomUserAgent(),
                    'processed_at' => $status === 'processed' ? Carbon::now()->subDays(rand(1, 30)) : null,
                    'processed_by_user_id' => $processedBy ? $processedBy->id : null,
                    'processing_notes' => $status === 'processed' ? 'Retroalimentación procesada y categorizada' : null,
                    'organization_id' => $organization->id,
                    'created_at' => Carbon::now()->subDays(rand(0, 90)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 90))
                ]);
            }
        }
        
        $this->command->info("💬 Envíos de retroalimentación creados");
    }
    
    private function createSupportRequests($users, $organizations): void
    {
        $this->command->info('🆘 Creando solicitudes de soporte...');
        
        $issueTypes = [
            'technical' => 'Problema Técnico',
            'billing' => 'Facturación',
            'account' => 'Cuenta de Usuario',
            'energy_service' => 'Servicio de Energía',
            'website' => 'Problema del Sitio Web',
            'mobile_app' => 'Problema de la App Móvil',
            'billing_system' => 'Sistema de Facturación',
            'energy_monitoring' => 'Monitoreo de Energía'
        ];
        
        foreach ($issueTypes as $type => $description) {
            for ($i = 0; $i < rand(3, 8); $i++) {
                $organization = $organizations->random();
                $user = $users->random();
                
                $fields = [
                    'name' => fake()->name(),
                    'email' => fake()->safeEmail(),
                    'phone' => fake()->optional(0.5)->phoneNumber(),
                    'issue_type' => $type,
                    'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
                    'description' => fake()->paragraph(rand(2, 4)),
                    'customer_id' => fake()->optional(0.6)->regexify('[A-Z]{2}[0-9]{6}'),
                    'account_number' => fake()->optional(0.7)->regexify('[0-9]{8}'),
                    'service_address' => fake()->optional(0.5)->address(),
                    'error_message' => fake()->optional(0.4)->sentence(),
                    'steps_to_reproduce' => fake()->optional(0.6)->paragraph(rand(1, 2)),
                    'expected_behavior' => fake()->optional(0.5)->sentence(),
                    'actual_behavior' => fake()->optional(0.5)->sentence(),
                    'contact_preference' => fake()->randomElement(['email', 'phone', 'both']),
                    'best_time_to_contact' => fake()->randomElement(['morning', 'afternoon', 'evening', 'anytime'])
                ];
                
                $status = fake()->randomElement(['pending', 'processed']);
                $processedBy = $status === 'processed' ? $users->random() : null;
                
                FormSubmission::create([
                    'form_name' => 'support',
                    'fields' => $fields,
                    'status' => $status,
                    'source_url' => fake()->url(),
                    'referrer' => fake()->optional(0.6)->url(),
                    'ip_address' => $this->generateRandomIP(),
                    'user_agent' => $this->generateRandomUserAgent(),
                    'processed_at' => $status === 'processed' ? Carbon::now()->subDays(rand(1, 7)) : null,
                    'processed_by_user_id' => $processedBy ? $processedBy->id : null,
                    'processing_notes' => $status === 'processed' ? 'Solicitud de soporte procesada y asignada' : null,
                    'organization_id' => $organization->id,
                    'created_at' => Carbon::now()->subDays(rand(0, 30)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 30))
                ]);
            }
        }
        
        $this->command->info("🆘 Solicitudes de soporte creadas");
    }
    
    private function createRegistrationForms($users, $organizations): void
    {
        $this->command->info('📋 Creando formularios de registro...');
        
        $registrationTypes = [
            'cooperative_member' => 'Miembro de Cooperativa',
            'energy_service' => 'Servicio de Energía',
            'newsletter' => 'Newsletter',
            'event' => 'Evento',
            'workshop' => 'Taller',
            'webinar' => 'Webinar'
        ];
        
        foreach ($registrationTypes as $type => $title) {
            for ($i = 0; $i < rand(2, 5); $i++) {
                $organization = $organizations->random();
                $user = $users->random();
                
                $fields = [
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->safeEmail(),
                    'phone' => fake()->optional(0.7)->phoneNumber(),
                    'registration_type' => $type,
                    'company' => fake()->optional(0.5)->company(),
                    'position' => fake()->optional(0.4)->jobTitle(),
                    'address' => fake()->optional(0.6)->address(),
                    'city' => fake()->optional(0.6)->city(),
                    'postal_code' => fake()->optional(0.6)->postcode(),
                    'country' => fake()->optional(0.6)->country(),
                    'interests' => fake()->randomElements(['renewable_energy', 'sustainability', 'cooperatives', 'technology', 'environment'], rand(1, 3)),
                    'experience_level' => fake()->randomElement(['beginner', 'intermediate', 'advanced', 'expert']),
                    'hear_about_us' => fake()->randomElement(['google', 'social_media', 'friend', 'advertisement', 'event', 'other']),
                    'consent_terms' => true,
                    'consent_marketing' => fake()->boolean(70),
                    'consent_analytics' => fake()->boolean(60)
                ];
                
                $status = fake()->randomElement(['pending', 'processed']);
                $processedBy = $status === 'processed' ? $users->random() : null;
                
                FormSubmission::create([
                    'form_name' => 'registration',
                    'fields' => $fields,
                    'status' => $status,
                    'source_url' => fake()->url(),
                    'referrer' => fake()->optional(0.5)->url(),
                    'ip_address' => $this->generateRandomIP(),
                    'user_agent' => $this->generateRandomUserAgent(),
                    'processed_at' => $status === 'processed' ? Carbon::now()->subDays(rand(1, 14)) : null,
                    'processed_by_user_id' => $processedBy ? $processedBy->id : null,
                    'processing_notes' => $status === 'processed' ? 'Registro procesado y confirmado' : null,
                    'organization_id' => $organization->id,
                    'created_at' => Carbon::now()->subDays(rand(0, 60)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 60))
                ]);
            }
        }
        
        $this->command->info("📋 Formularios de registro creados");
    }
    
    private function createQuoteRequests($users, $organizations): void
    {
        $this->command->info('💰 Creando solicitudes de cotización...');
        
        $serviceTypes = [
            'solar_installation' => 'Instalación Solar',
            'energy_audit' => 'Auditoría Energética',
            'energy_efficiency' => 'Eficiencia Energética',
            'battery_storage' => 'Almacenamiento con Baterías',
            'smart_home' => 'Hogar Inteligente',
            'energy_monitoring' => 'Monitoreo de Energía'
        ];
        
        foreach ($serviceTypes as $service => $title) {
            for ($i = 0; $i < rand(1, 4); $i++) {
                $organization = $organizations->random();
                $user = $users->random();
                
                $fields = [
                    'name' => fake()->name(),
                    'email' => fake()->safeEmail(),
                    'phone' => fake()->phoneNumber(),
                    'company' => fake()->optional(0.6)->company(),
                    'service_requested' => $service,
                    'project_description' => fake()->paragraph(rand(2, 4)),
                    'property_type' => fake()->randomElement(['residential', 'commercial', 'industrial', 'agricultural']),
                    'property_size' => fake()->randomElement(['<100m2', '100-500m2', '500-1000m2', '1000m2+']),
                    'current_energy_consumption' => fake()->randomElement(['<1000kWh/month', '1000-5000kWh/month', '5000-10000kWh/month', '10000kWh/month+']),
                    'budget_range' => fake()->randomElement(['<5k', '5k-15k', '15k-50k', '50k+']),
                    'timeline' => fake()->randomElement(['immediate', '1-3 months', '3-6 months', '6+ months']),
                    'location' => fake()->city() . ', ' . fake()->state(),
                    'additional_requirements' => fake()->optional(0.7)->paragraph(rand(1, 2)),
                    'preferred_contact_method' => fake()->randomElement(['email', 'phone', 'both']),
                    'hear_about_us' => fake()->randomElement(['google', 'referral', 'social_media', 'advertisement', 'event'])
                ];
                
                $status = fake()->randomElement(['pending', 'processed']);
                $processedBy = $status === 'processed' ? $users->random() : null;
                
                FormSubmission::create([
                    'form_name' => 'quote',
                    'fields' => $fields,
                    'status' => $status,
                    'source_url' => fake()->url(),
                    'referrer' => fake()->optional(0.6)->url(),
                    'ip_address' => $this->generateRandomIP(),
                    'user_agent' => $this->generateRandomUserAgent(),
                    'processed_at' => $status === 'processed' ? Carbon::now()->subDays(rand(1, 21)) : null,
                    'processed_by_user_id' => $processedBy ? $processedBy->id : null,
                    'processing_notes' => $status === 'processed' ? 'Cotización procesada y enviada' : null,
                    'organization_id' => $organization->id,
                    'created_at' => Carbon::now()->subDays(rand(0, 45)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 45))
                ]);
            }
        }
        
        $this->command->info("💰 Solicitudes de cotización creadas");
    }
    
    private function createPartnershipRequests($users, $organizations): void
    {
        $this->command->info('🤝 Creando solicitudes de colaboración...');
        
        $partnershipTypes = [
            'business_partnership' => 'Colaboración Comercial',
            'technology_partnership' => 'Colaboración Tecnológica',
            'research_partnership' => 'Colaboración de Investigación',
            'marketing_partnership' => 'Colaboración de Marketing',
            'supply_partnership' => 'Colaboración de Suministro',
            'distribution_partnership' => 'Colaboración de Distribución'
        ];
        
        foreach ($partnershipTypes as $type => $title) {
            for ($i = 0; $i < rand(1, 3); $i++) {
                $organization = $organizations->random();
                $user = $users->random();
                
                $fields = [
                    'company_name' => fake()->company(),
                    'contact_name' => fake()->name(),
                    'contact_title' => fake()->jobTitle(),
                    'email' => fake()->safeEmail(),
                    'phone' => fake()->phoneNumber(),
                    'website' => fake()->optional(0.8)->url(),
                    'partnership_type' => $type,
                    'company_description' => fake()->paragraph(rand(2, 3)),
                    'partnership_proposal' => fake()->paragraph(rand(3, 5)),
                    'company_size' => fake()->randomElement(['startup', 'small', 'medium', 'large', 'enterprise']),
                    'industry' => fake()->randomElement(['energy', 'technology', 'manufacturing', 'services', 'retail', 'other']),
                    'annual_revenue' => fake()->randomElement(['<100k', '100k-1M', '1M-10M', '10M-100M', '100M+']),
                    'employees_count' => fake()->randomElement(['1-10', '11-50', '51-200', '201-1000', '1000+']),
                    'geographic_reach' => fake()->randomElement(['local', 'regional', 'national', 'international', 'global']),
                    'key_benefits' => fake()->paragraph(rand(1, 2)),
                    'timeline' => fake()->randomElement(['immediate', '3-6 months', '6-12 months', '1+ years']),
                    'investment_capacity' => fake()->randomElement(['<10k', '10k-50k', '50k-200k', '200k+']),
                    'additional_notes' => fake()->optional(0.6)->paragraph(rand(1, 2))
                ];
                
                $status = fake()->randomElement(['pending', 'processed']);
                $processedBy = $status === 'processed' ? $users->random() : null;
                
                FormSubmission::create([
                    'form_name' => 'partnership',
                    'fields' => $fields,
                    'status' => $status,
                    'source_url' => fake()->url(),
                    'referrer' => fake()->optional(0.5)->url(),
                    'ip_address' => $this->generateRandomIP(),
                    'user_agent' => $this->generateRandomUserAgent(),
                    'processed_at' => $status === 'processed' ? Carbon::now()->subDays(rand(1, 30)) : null,
                    'processed_by_user_id' => $processedBy ? $processedBy->id : null,
                    'processing_notes' => $status === 'processed' ? 'Solicitud de colaboración procesada y evaluada' : null,
                    'organization_id' => $organization->id,
                    'created_at' => Carbon::now()->subDays(rand(0, 90)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 90))
                ]);
            }
        }
        
        $this->command->info("🤝 Solicitudes de colaboración creadas");
    }
    
    private function createJobApplications($users, $organizations): void
    {
        $this->command->info('💼 Creando aplicaciones de trabajo...');
        
        $jobPositions = [
            'energy_engineer' => 'Ingeniero de Energía',
            'sustainability_specialist' => 'Especialista en Sostenibilidad',
            'software_developer' => 'Desarrollador de Software',
            'project_manager' => 'Gerente de Proyecto',
            'marketing_specialist' => 'Especialista en Marketing',
            'customer_service' => 'Servicio al Cliente',
            'data_analyst' => 'Analista de Datos',
            'sales_representative' => 'Representante de Ventas'
        ];
        
        foreach ($jobPositions as $position => $title) {
            for ($i = 0; $i < rand(2, 6); $i++) {
                $organization = $organizations->random();
                $user = $users->random();
                
                $fields = [
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->safeEmail(),
                    'phone' => fake()->phoneNumber(),
                    'position_applied' => $position,
                    'cover_letter' => fake()->paragraph(rand(3, 6)),
                    'experience_years' => fake()->numberBetween(0, 20),
                    'education_level' => fake()->randomElement(['high_school', 'bachelor', 'master', 'phd', 'other']),
                    'current_company' => fake()->optional(0.7)->company(),
                    'current_position' => fake()->optional(0.6)->jobTitle(),
                    'salary_expectation' => fake()->randomElement(['<30k', '30k-50k', '50k-80k', '80k-120k', '120k+']),
                    'start_date' => fake()->randomElement(['immediate', '1 month', '3 months', '6 months', 'flexible']),
                    'work_type' => fake()->randomElement(['full_time', 'part_time', 'contract', 'remote', 'hybrid']),
                    'location_preference' => fake()->city() . ', ' . fake()->state(),
                    'skills' => fake()->randomElements(['renewable_energy', 'project_management', 'data_analysis', 'marketing', 'sales', 'customer_service', 'software_development'], rand(2, 5)),
                    'languages' => fake()->randomElements(['Spanish', 'English', 'Catalan', 'Basque', 'French', 'German'], rand(1, 3)),
                    'portfolio_url' => fake()->optional(0.4)->url(),
                    'linkedin_url' => fake()->optional(0.6)->url(),
                    'references' => fake()->optional(0.5)->paragraph(rand(1, 2))
                ];
                
                $status = fake()->randomElement(['pending', 'processed', 'archived']);
                $processedBy = $status === 'processed' ? $users->random() : null;
                
                FormSubmission::create([
                    'form_name' => 'job_application',
                    'fields' => $fields,
                    'status' => $status,
                    'source_url' => fake()->url(),
                    'referrer' => fake()->optional(0.5)->url(),
                    'ip_address' => $this->generateRandomIP(),
                    'user_agent' => $this->generateRandomUserAgent(),
                    'processed_at' => $status === 'processed' ? Carbon::now()->subDays(rand(1, 45)) : null,
                    'processed_by_user_id' => $processedBy ? $processedBy->id : null,
                    'processing_notes' => $status === 'processed' ? 'Aplicación procesada y evaluada' : null,
                    'organization_id' => $organization->id,
                    'created_at' => Carbon::now()->subDays(rand(0, 120)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 120))
                ]);
            }
        }
        
        $this->command->info("💼 Aplicaciones de trabajo creadas");
    }
    
    private function createVolunteerApplications($users, $organizations): void
    {
        $this->command->info('🤲 Creando aplicaciones de voluntariado...');
        
        $volunteerAreas = [
            'community_outreach' => 'Alcance Comunitario',
            'environmental_education' => 'Educación Ambiental',
            'event_organization' => 'Organización de Eventos',
            'social_media' => 'Redes Sociales',
            'translation' => 'Traducción',
            'technical_support' => 'Soporte Técnico',
            'fundraising' => 'Recaudación de Fondos',
            'research' => 'Investigación'
        ];
        
        foreach ($volunteerAreas as $area => $title) {
            for ($i = 0; $i < rand(1, 4); $i++) {
                $organization = $organizations->random();
                $user = $users->random();
                
                $fields = [
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->safeEmail(),
                    'phone' => fake()->optional(0.8)->phoneNumber(),
                    'volunteer_area' => $area,
                    'motivation' => fake()->paragraph(rand(2, 4)),
                    'experience' => fake()->paragraph(rand(1, 3)),
                    'availability' => fake()->randomElements(['weekdays', 'weekends', 'evenings', 'mornings', 'flexible'], rand(1, 3)),
                    'hours_per_week' => fake()->randomElement(['1-5', '5-10', '10-20', '20+']),
                    'start_date' => fake()->randomElement(['immediate', 'next_month', 'next_quarter', 'flexible']),
                    'commitment_duration' => fake()->randomElement(['short_term', 'long_term', 'ongoing', 'project_based']),
                    'skills' => fake()->randomElements(['communication', 'organization', 'teaching', 'technical', 'creative', 'leadership'], rand(2, 4)),
                    'languages' => fake()->randomElements(['Spanish', 'English', 'Catalan', 'Basque', 'French'], rand(1, 3)),
                    'previous_volunteer' => fake()->boolean(60),
                    'previous_organization' => fake()->optional(0.4)->company(),
                    'emergency_contact' => fake()->optional(0.7)->name(),
                    'emergency_phone' => fake()->optional(0.7)->phoneNumber(),
                    'health_conditions' => fake()->optional(0.2)->sentence(),
                    'special_accommodations' => fake()->optional(0.1)->sentence(),
                    'additional_notes' => fake()->optional(0.5)->paragraph(rand(1, 2))
                ];
                
                $status = fake()->randomElement(['pending', 'processed']);
                $processedBy = $status === 'processed' ? $users->random() : null;
                
                FormSubmission::create([
                    'form_name' => 'volunteer',
                    'fields' => $fields,
                    'status' => $status,
                    'source_url' => fake()->url(),
                    'referrer' => fake()->optional(0.5)->url(),
                    'ip_address' => $this->generateRandomIP(),
                    'user_agent' => $this->generateRandomUserAgent(),
                    'processed_at' => $status === 'processed' ? Carbon::now()->subDays(rand(1, 30)) : null,
                    'processed_by_user_id' => $processedBy ? $processedBy->id : null,
                    'processing_notes' => $status === 'processed' ? 'Aplicación de voluntariado procesada' : null,
                    'organization_id' => $organization->id,
                    'created_at' => Carbon::now()->subDays(rand(0, 90)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 90))
                ]);
            }
        }
        
        $this->command->info("🤲 Aplicaciones de voluntariado creadas");
    }
    
    private function createSpamSubmissions($users, $organizations): void
    {
        $this->command->info('🚫 Creando envíos de spam...');
        
        $spamTypes = [
            'generic_spam' => 'Spam Genérico',
            'phishing' => 'Phishing',
            'scam' => 'Estafa',
            'bot_submission' => 'Envío de Bot',
            'malicious_content' => 'Contenido Malicioso'
        ];
        
        foreach ($spamTypes as $type => $description) {
            for ($i = 0; $i < rand(1, 3); $i++) {
                $organization = $organizations->random();
                $user = $users->random();
                
                $fields = [
                    'name' => fake()->randomElement(['Spam User', 'Fake Name', 'Bot User', 'Test User', 'John Doe']),
                    'email' => fake()->randomElement(['spam@fake.com', 'bot@test.com', 'fake@spam.com', 'test@bot.com', 'user@fake.org']),
                    'message' => fake()->randomElement([
                        'Buy cheap viagra now! Click here: http://spam.com',
                        'Make money fast! Visit: http://scam.com',
                        'Free gift card! Click: http://fake.com',
                        'You won a prize! Claim at: http://malicious.com',
                        'Investment opportunity! Send money to: fake@scam.com'
                    ]),
                    'spam_type' => $type,
                    'suspicious_links' => fake()->randomElements(['http://spam.com', 'http://fake.com', 'http://scam.com'], rand(1, 3)),
                    'suspicious_keywords' => fake()->randomElements(['viagra', 'casino', 'lottery', 'investment', 'free money'], rand(2, 4))
                ];
                
                $status = 'spam';
                $processedBy = $users->random();
                
                FormSubmission::create([
                    'form_name' => 'contact',
                    'fields' => $fields,
                    'status' => $status,
                    'source_url' => fake()->url(),
                    'referrer' => fake()->optional(0.3)->url(),
                    'ip_address' => $this->generateRandomIP(),
                    'user_agent' => fake()->randomElement([
                        'Mozilla/5.0 (compatible; SpamBot/1.0)',
                        'Bot/1.0 (Spam)',
                        'Fake User Agent',
                        'Spam Crawler'
                    ]),
                    'processed_at' => Carbon::now()->subDays(rand(1, 7)),
                    'processed_by_user_id' => $processedBy->id,
                    'processing_notes' => 'Marcado como spam debido a contenido sospechoso',
                    'organization_id' => $organization->id,
                    'created_at' => Carbon::now()->subDays(rand(0, 14)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 14))
                ]);
            }
        }
        
        $this->command->info("🚫 Envíos de spam creados");
    }
    
    private function generateRandomIP(): string
    {
        $ips = [
            '192.168.1.' . rand(1, 254),
            '10.0.0.' . rand(1, 254),
            '172.16.' . rand(0, 31) . '.' . rand(1, 254),
            '203.0.113.' . rand(1, 254),
            '198.51.100.' . rand(1, 254),
            '127.0.0.' . rand(1, 254)
        ];
        
        return $ips[array_rand($ips)];
    }
    
    private function generateRandomUserAgent(): string
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Android 14; Mobile; rv:120.0) Gecko/120.0 Firefox/120.0'
        ];
        
        return $userAgents[array_rand($userAgents)];
    }
}
