<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConsentLog;
use App\Models\User;
use Carbon\Carbon;

class ConsentLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📋 Creando logs de consentimiento para cumplimiento GDPR...');

        // Obtener usuarios disponibles
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles. Ejecuta RolesAndAdminSeeder primero.');
            return;
        }

        // 1. Consentimientos Activos
        $this->createActiveConsents($users);

        // 2. Consentimientos Revocados
        $this->createRevokedConsents($users);

        // 3. Consentimientos de Términos y Condiciones
        $this->createTermsAndConditionsConsents($users);

        // 4. Consentimientos de Política de Privacidad
        $this->createPrivacyPolicyConsents($users);

        // 5. Consentimientos de Cookies
        $this->createCookiesPolicyConsents($users);

        // 6. Consentimientos de Marketing
        $this->createMarketingConsents($users);

        // 7. Consentimientos de Newsletter
        $this->createNewsletterConsents($users);

        // 8. Consentimientos de Analytics
        $this->createAnalyticsConsents($users);

        // 9. Consentimientos de Compartir con Terceros
        $this->createThirdPartyConsents($users);

        // 10. Consentimientos Recientes
        $this->createRecentConsents($users);

        // 11. Consentimientos Denegados
        $this->createDeniedConsents($users);

        // 12. Consentimientos con Diferentes Versiones
        $this->createVersionedConsents($users);

        $this->command->info('✅ ConsentLogSeeder completado. Se crearon ' . ConsentLog::count() . ' logs de consentimiento.');
    }

    /**
     * Crear consentimientos activos
     */
    private function createActiveConsents($users): void
    {
        $this->command->info('✅ Creando consentimientos activos...');

        $activeConsents = [
            [
                'user_id' => $users->random()->id,
                'consent_type' => 'terms_and_conditions',
                'consent_given' => true,
                'consented_at' => Carbon::now()->subDays(rand(30, 180)),
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'version' => '2.1',
                'purpose' => 'Aceptación de términos y condiciones del servicio',
                'legal_basis' => 'Artículo 6.1.b GDPR - Ejecución de contrato',
                'data_categories' => ['personal_data', 'contact_info'],
                'retention_period' => '5 años',
                'third_parties' => [],
                'withdrawal_method' => 'Contactar a legal@cooperativa.com',
                'consent_document_url' => 'https://cooperativa.com/terms/v2.1',
                'revoked_at' => null,
                'revocation_reason' => null,
                'consent_context' => [
                    'source' => 'registration',
                    'campaign' => 'new_user_onboarding',
                    'referrer' => 'https://cooperativa.com/register',
                ],
                'metadata' => [
                    'browser' => 'Chrome',
                    'device' => 'desktop',
                    'locale' => 'es',
                ],
            ],
            [
                'user_id' => $users->random()->id,
                'consent_type' => 'privacy_policy',
                'consent_given' => true,
                'consented_at' => Carbon::now()->subDays(rand(20, 120)),
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'version' => '1.2',
                'purpose' => 'Procesamiento de datos personales para servicios energéticos',
                'legal_basis' => 'Artículo 6.1.a GDPR - Consentimiento',
                'data_categories' => ['personal_data', 'contact_info', 'usage_data'],
                'retention_period' => 'Hasta revocación',
                'third_parties' => ['Google Analytics'],
                'withdrawal_method' => 'Configuración de cuenta',
                'consent_document_url' => 'https://cooperativa.com/privacy/v1.2',
                'revoked_at' => null,
                'revocation_reason' => null,
                'consent_context' => [
                    'source' => 'login',
                    'campaign' => 'privacy_update_2024',
                    'referrer' => 'https://cooperativa.com/dashboard',
                ],
                'metadata' => [
                    'browser' => 'Firefox',
                    'device' => 'mobile',
                    'locale' => 'es',
                ],
            ],
        ];

        foreach ($activeConsents as $consent) {
            $this->createConsent($consent);
        }

        // Crear consentimientos adicionales
        for ($i = 0; $i < 25; $i++) {
            $consent = ConsentLog::create([
                'user_id' => $users->random()->id,
                'consent_type' => fake()->randomElement(array_keys(ConsentLog::CONSENT_TYPES)),
                'consent_given' => true,
                'consented_at' => Carbon::now()->subDays(rand(1, 365)),
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'version' => fake()->optional(0.8)->randomElement(['1.0', '1.1', '2.0', '2.1']),
                'purpose' => fake()->optional(0.7)->sentence(),
                'legal_basis' => fake()->optional(0.6)->randomElement([
                    'Artículo 6.1.a GDPR - Consentimiento',
                    'Artículo 6.1.b GDPR - Ejecución de contrato',
                    'Artículo 6.1.f GDPR - Interés legítimo'
                ]),
                'data_categories' => fake()->optional(0.5)->randomElements([
                    'personal_data', 'contact_info', 'usage_data', 'preferences', 'location_data'
                ], fake()->numberBetween(1, 3)),
                'retention_period' => fake()->optional(0.5)->randomElement([
                    '5 años', '2 años', '1 año', 'Hasta revocación'
                ]),
                'third_parties' => fake()->optional(0.3)->randomElements([
                    'Google Analytics', 'Mailchimp', 'Stripe', 'Zendesk'
                ], fake()->numberBetween(1, 2)),
                'withdrawal_method' => fake()->optional(0.4)->randomElement([
                    'Contactar a privacy@cooperativa.com',
                    'Configuración de cuenta',
                    'Formulario de contacto'
                ]),
                'consent_document_url' => fake()->optional(0.8)->url(),
                'revoked_at' => null,
                'revocation_reason' => null,
                'consent_context' => [
                    'source' => fake()->randomElement(['registration', 'login', 'settings', 'onboarding']),
                    'campaign' => fake()->optional()->word(),
                    'referrer' => fake()->optional()->url(),
                ],
                'metadata' => [
                    'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari']),
                    'device' => fake()->randomElement(['mobile', 'desktop', 'tablet']),
                    'locale' => fake()->randomElement(['es', 'en', 'ca']),
                ],
            ]);
            $this->command->line("   ✅ Creado consentimiento activo: {$consent->consent_type} - Usuario: {$consent->user_id}");
        }
    }

    /**
     * Crear consentimientos revocados
     */
    private function createRevokedConsents($users): void
    {
        $this->command->info('❌ Creando consentimientos revocados...');

        $revokedConsents = [
            [
                'user_id' => $users->random()->id,
                'consent_type' => 'marketing_communications',
                'consent_given' => true,
                'consented_at' => Carbon::now()->subDays(rand(60, 200)),
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'version' => '1.0',
                'purpose' => 'Envío de comunicaciones comerciales y ofertas',
                'legal_basis' => 'Artículo 6.1.a GDPR - Consentimiento',
                'data_categories' => ['personal_data', 'contact_info', 'preferences'],
                'retention_period' => 'Hasta revocación',
                'third_parties' => ['Mailchimp'],
                'withdrawal_method' => 'Configuración de cuenta',
                'consent_document_url' => 'https://cooperativa.com/marketing/v1.0',
                'revoked_at' => Carbon::now()->subDays(rand(1, 30)),
                'revocation_reason' => 'Usuario canceló suscripción a comunicaciones comerciales',
                'consent_context' => [
                    'source' => 'settings',
                    'campaign' => 'marketing_opt_out',
                    'referrer' => 'https://cooperativa.com/account/preferences',
                ],
                'metadata' => [
                    'browser' => 'Safari',
                    'device' => 'desktop',
                    'locale' => 'es',
                ],
            ],
            [
                'user_id' => $users->random()->id,
                'consent_type' => 'analytics',
                'consent_given' => true,
                'consented_at' => Carbon::now()->subDays(rand(40, 150)),
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'version' => '1.1',
                'purpose' => 'Análisis de uso y mejora de servicios',
                'legal_basis' => 'Artículo 6.1.f GDPR - Interés legítimo',
                'data_categories' => ['usage_data', 'preferences'],
                'retention_period' => '2 años',
                'third_parties' => ['Google Analytics'],
                'withdrawal_method' => 'Contactar a privacy@cooperativa.com',
                'consent_document_url' => 'https://cooperativa.com/analytics/v1.1',
                'revoked_at' => Carbon::now()->subDays(rand(5, 45)),
                'revocation_reason' => 'Usuario solicitó desactivar tracking',
                'consent_context' => [
                    'source' => 'settings',
                    'campaign' => 'privacy_preferences',
                    'referrer' => 'https://cooperativa.com/account/privacy',
                ],
                'metadata' => [
                    'browser' => 'Chrome',
                    'device' => 'mobile',
                    'locale' => 'en',
                ],
            ],
        ];

        foreach ($revokedConsents as $consent) {
            $this->createConsent($consent);
        }

        // Crear consentimientos adicionales revocados
        for ($i = 0; $i < 15; $i++) {
            $consentedAt = Carbon::now()->subDays(rand(30, 200));
            $consent = ConsentLog::create([
                'user_id' => $users->random()->id,
                'consent_type' => fake()->randomElement(array_keys(ConsentLog::CONSENT_TYPES)),
                'consent_given' => true,
                'consented_at' => $consentedAt,
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'version' => fake()->optional(0.7)->randomElement(['1.0', '1.1', '2.0']),
                'purpose' => fake()->optional(0.6)->sentence(),
                'legal_basis' => fake()->optional(0.5)->randomElement([
                    'Artículo 6.1.a GDPR - Consentimiento',
                    'Artículo 6.1.b GDPR - Ejecución de contrato',
                    'Artículo 6.1.f GDPR - Interés legítimo'
                ]),
                'data_categories' => fake()->optional(0.4)->randomElements([
                    'personal_data', 'contact_info', 'usage_data', 'preferences'
                ], fake()->numberBetween(1, 3)),
                'retention_period' => fake()->optional(0.5)->randomElement([
                    '5 años', '2 años', '1 año', 'Hasta revocación'
                ]),
                'third_parties' => fake()->optional(0.3)->randomElements([
                    'Google Analytics', 'Mailchimp', 'Stripe'
                ], fake()->numberBetween(1, 2)),
                'withdrawal_method' => fake()->optional(0.4)->randomElement([
                    'Contactar a privacy@cooperativa.com',
                    'Configuración de cuenta',
                    'Formulario de contacto'
                ]),
                'consent_document_url' => fake()->optional(0.8)->url(),
                'revoked_at' => Carbon::now()->subDays(rand(1, 60)),
                'revocation_reason' => fake()->optional(0.8)->sentence(),
                'consent_context' => [
                    'source' => fake()->randomElement(['settings', 'account', 'privacy']),
                    'campaign' => fake()->optional()->word(),
                    'referrer' => fake()->optional()->url(),
                ],
                'metadata' => [
                    'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari']),
                    'device' => fake()->randomElement(['mobile', 'desktop', 'tablet']),
                    'locale' => fake()->randomElement(['es', 'en', 'ca']),
                ],
            ]);
            $this->command->line("   ✅ Creado consentimiento revocado: {$consent->consent_type} - Usuario: {$consent->user_id}");
        }
    }

    /**
     * Crear consentimientos de términos y condiciones
     */
    private function createTermsAndConditionsConsents($users): void
    {
        $this->command->info('📜 Creando consentimientos de términos y condiciones...');

        for ($i = 0; $i < 20; $i++) {
            $consent = ConsentLog::create([
                'user_id' => $users->random()->id,
                'consent_type' => 'terms_and_conditions',
                'consent_given' => fake()->boolean(95), // 95% aceptan términos
                'consented_at' => Carbon::now()->subDays(rand(1, 365)),
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'version' => fake()->randomElement(['1.0', '1.1', '2.0', '2.1']),
                'purpose' => 'Aceptación de términos y condiciones del servicio energético',
                'legal_basis' => 'Artículo 6.1.b GDPR - Ejecución de contrato',
                'data_categories' => ['personal_data', 'contact_info'],
                'retention_period' => '5 años',
                'third_parties' => [],
                'withdrawal_method' => 'Contactar a legal@cooperativa.com',
                'consent_document_url' => 'https://cooperativa.com/terms/v' . fake()->randomElement(['1.0', '1.1', '2.0', '2.1']),
                'revoked_at' => fake()->optional(0.05)->dateTimeBetween('-6 months', 'now'),
                'revocation_reason' => fake()->optional(0.05)->sentence(),
                'consent_context' => [
                    'source' => fake()->randomElement(['registration', 'login', 'contract_renewal']),
                    'campaign' => fake()->optional()->word(),
                    'referrer' => fake()->optional()->url(),
                ],
                'metadata' => [
                    'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari']),
                    'device' => fake()->randomElement(['mobile', 'desktop', 'tablet']),
                    'locale' => fake()->randomElement(['es', 'en', 'ca']),
                ],
            ]);
            $this->command->line("   ✅ Creado consentimiento términos: {$consent->consent_type} - Usuario: {$consent->user_id}");
        }
    }

    /**
     * Crear consentimientos de política de privacidad
     */
    private function createPrivacyPolicyConsents($users): void
    {
        $this->command->info('🔒 Creando consentimientos de política de privacidad...');

        for ($i = 0; $i < 18; $i++) {
            $consent = ConsentLog::create([
                'user_id' => $users->random()->id,
                'consent_type' => 'privacy_policy',
                'consent_given' => fake()->boolean(90), // 90% aceptan privacidad
                'consented_at' => Carbon::now()->subDays(rand(1, 365)),
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'version' => fake()->randomElement(['1.0', '1.1', '1.2', '2.0']),
                'purpose' => 'Procesamiento de datos personales para servicios energéticos',
                'legal_basis' => 'Artículo 6.1.a GDPR - Consentimiento',
                'data_categories' => ['personal_data', 'contact_info', 'usage_data'],
                'retention_period' => 'Hasta revocación',
                'third_parties' => fake()->optional(0.3)->randomElements(['Google Analytics'], 1),
                'withdrawal_method' => 'Configuración de cuenta',
                'consent_document_url' => 'https://cooperativa.com/privacy/v' . fake()->randomElement(['1.0', '1.1', '1.2', '2.0']),
                'revoked_at' => fake()->optional(0.1)->dateTimeBetween('-6 months', 'now'),
                'revocation_reason' => fake()->optional(0.1)->sentence(),
                'consent_context' => [
                    'source' => fake()->randomElement(['registration', 'login', 'privacy_update']),
                    'campaign' => fake()->optional()->word(),
                    'referrer' => fake()->optional()->url(),
                ],
                'metadata' => [
                    'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari']),
                    'device' => fake()->randomElement(['mobile', 'desktop', 'tablet']),
                    'locale' => fake()->randomElement(['es', 'en', 'ca']),
                ],
            ]);
            $this->command->line("   ✅ Creado consentimiento privacidad: {$consent->consent_type} - Usuario: {$consent->user_id}");
        }
    }

    /**
     * Crear consentimientos de política de cookies
     */
    private function createCookiesPolicyConsents($users): void
    {
        $this->command->info('🍪 Creando consentimientos de política de cookies...');

        for ($i = 0; $i < 15; $i++) {
            $consent = ConsentLog::create([
                'user_id' => $users->random()->id,
                'consent_type' => 'cookies_policy',
                'consent_given' => fake()->boolean(85), // 85% aceptan cookies
                'consented_at' => Carbon::now()->subDays(rand(1, 365)),
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'version' => fake()->randomElement(['1.0', '1.1', '2.0']),
                'purpose' => 'Uso de cookies para mejorar la experiencia del usuario',
                'legal_basis' => 'Artículo 6.1.a GDPR - Consentimiento',
                'data_categories' => ['usage_data', 'preferences'],
                'retention_period' => '2 años',
                'third_parties' => fake()->optional(0.4)->randomElements(['Google Analytics'], 1),
                'withdrawal_method' => 'Configuración de cookies',
                'consent_document_url' => 'https://cooperativa.com/cookies/v' . fake()->randomElement(['1.0', '1.1', '2.0']),
                'revoked_at' => fake()->optional(0.15)->dateTimeBetween('-6 months', 'now'),
                'revocation_reason' => fake()->optional(0.15)->sentence(),
                'consent_context' => [
                    'source' => fake()->randomElement(['banner', 'settings', 'first_visit']),
                    'campaign' => fake()->optional()->word(),
                    'referrer' => fake()->optional()->url(),
                ],
                'metadata' => [
                    'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari']),
                    'device' => fake()->randomElement(['mobile', 'desktop', 'tablet']),
                    'locale' => fake()->randomElement(['es', 'en', 'ca']),
                ],
            ]);
            $this->command->line("   ✅ Creado consentimiento cookies: {$consent->consent_type} - Usuario: {$consent->user_id}");
        }
    }

    /**
     * Crear consentimientos de marketing
     */
    private function createMarketingConsents($users): void
    {
        $this->command->info('📧 Creando consentimientos de marketing...');

        for ($i = 0; $i < 12; $i++) {
            $consent = ConsentLog::create([
                'user_id' => $users->random()->id,
                'consent_type' => 'marketing_communications',
                'consent_given' => fake()->boolean(70), // 70% aceptan marketing
                'consented_at' => Carbon::now()->subDays(rand(1, 365)),
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'version' => fake()->randomElement(['1.0', '1.1']),
                'purpose' => 'Envío de comunicaciones comerciales y ofertas',
                'legal_basis' => 'Artículo 6.1.a GDPR - Consentimiento',
                'data_categories' => ['personal_data', 'contact_info', 'preferences'],
                'retention_period' => 'Hasta revocación',
                'third_parties' => fake()->optional(0.5)->randomElements(['Mailchimp'], 1),
                'withdrawal_method' => 'Configuración de cuenta',
                'consent_document_url' => 'https://cooperativa.com/marketing/v' . fake()->randomElement(['1.0', '1.1']),
                'revoked_at' => fake()->optional(0.2)->dateTimeBetween('-6 months', 'now'),
                'revocation_reason' => fake()->optional(0.2)->sentence(),
                'consent_context' => [
                    'source' => fake()->randomElement(['registration', 'settings', 'newsletter_signup']),
                    'campaign' => fake()->optional()->word(),
                    'referrer' => fake()->optional()->url(),
                ],
                'metadata' => [
                    'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari']),
                    'device' => fake()->randomElement(['mobile', 'desktop', 'tablet']),
                    'locale' => fake()->randomElement(['es', 'en', 'ca']),
                ],
            ]);
            $this->command->line("   ✅ Creado consentimiento marketing: {$consent->consent_type} - Usuario: {$consent->user_id}");
        }
    }

    /**
     * Crear consentimientos de newsletter
     */
    private function createNewsletterConsents($users): void
    {
        $this->command->info('📰 Creando consentimientos de newsletter...');

        for ($i = 0; $i < 10; $i++) {
            $consent = ConsentLog::create([
                'user_id' => $users->random()->id,
                'consent_type' => 'newsletter',
                'consent_given' => fake()->boolean(75), // 75% aceptan newsletter
                'consented_at' => Carbon::now()->subDays(rand(1, 365)),
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'version' => fake()->randomElement(['1.0', '1.1']),
                'purpose' => 'Suscripción a newsletter con información energética',
                'legal_basis' => 'Artículo 6.1.a GDPR - Consentimiento',
                'data_categories' => ['personal_data', 'contact_info', 'preferences'],
                'retention_period' => 'Hasta revocación',
                'third_parties' => fake()->optional(0.6)->randomElements(['Mailchimp'], 1),
                'withdrawal_method' => 'Link de desuscripción en emails',
                'consent_document_url' => 'https://cooperativa.com/newsletter/v' . fake()->randomElement(['1.0', '1.1']),
                'revoked_at' => fake()->optional(0.25)->dateTimeBetween('-6 months', 'now'),
                'revocation_reason' => fake()->optional(0.25)->sentence(),
                'consent_context' => [
                    'source' => fake()->randomElement(['registration', 'newsletter_signup', 'footer']),
                    'campaign' => fake()->optional()->word(),
                    'referrer' => fake()->optional()->url(),
                ],
                'metadata' => [
                    'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari']),
                    'device' => fake()->randomElement(['mobile', 'desktop', 'tablet']),
                    'locale' => fake()->randomElement(['es', 'en', 'ca']),
                ],
            ]);
            $this->command->line("   ✅ Creado consentimiento newsletter: {$consent->consent_type} - Usuario: {$consent->user_id}");
        }
    }

    /**
     * Crear consentimientos de analytics
     */
    private function createAnalyticsConsents($users): void
    {
        $this->command->info('📊 Creando consentimientos de analytics...');

        for ($i = 0; $i < 8; $i++) {
            $consent = ConsentLog::create([
                'user_id' => $users->random()->id,
                'consent_type' => 'analytics',
                'consent_given' => fake()->boolean(80), // 80% aceptan analytics
                'consented_at' => Carbon::now()->subDays(rand(1, 365)),
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'version' => fake()->randomElement(['1.0', '1.1']),
                'purpose' => 'Análisis de uso y mejora de servicios',
                'legal_basis' => 'Artículo 6.1.f GDPR - Interés legítimo',
                'data_categories' => ['usage_data', 'preferences'],
                'retention_period' => '2 años',
                'third_parties' => fake()->optional(0.7)->randomElements(['Google Analytics'], 1),
                'withdrawal_method' => 'Contactar a privacy@cooperativa.com',
                'consent_document_url' => 'https://cooperativa.com/analytics/v' . fake()->randomElement(['1.0', '1.1']),
                'revoked_at' => fake()->optional(0.15)->dateTimeBetween('-6 months', 'now'),
                'revocation_reason' => fake()->optional(0.15)->sentence(),
                'consent_context' => [
                    'source' => fake()->randomElement(['banner', 'settings', 'privacy_preferences']),
                    'campaign' => fake()->optional()->word(),
                    'referrer' => fake()->optional()->url(),
                ],
                'metadata' => [
                    'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari']),
                    'device' => fake()->randomElement(['mobile', 'desktop', 'tablet']),
                    'locale' => fake()->randomElement(['es', 'en', 'ca']),
                ],
            ]);
            $this->command->line("   ✅ Creado consentimiento analytics: {$consent->consent_type} - Usuario: {$consent->user_id}");
        }
    }

    /**
     * Crear consentimientos de compartir con terceros
     */
    private function createThirdPartyConsents($users): void
    {
        $this->command->info('🤝 Creando consentimientos de compartir con terceros...');

        for ($i = 0; $i < 6; $i++) {
            $consent = ConsentLog::create([
                'user_id' => $users->random()->id,
                'consent_type' => 'third_party_sharing',
                'consent_given' => fake()->boolean(60), // 60% aceptan compartir con terceros
                'consented_at' => Carbon::now()->subDays(rand(1, 365)),
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'version' => fake()->randomElement(['1.0', '1.1']),
                'purpose' => 'Compartir datos con proveedores de servicios',
                'legal_basis' => 'Artículo 6.1.a GDPR - Consentimiento',
                'data_categories' => ['personal_data', 'contact_info'],
                'retention_period' => 'Hasta revocación',
                'third_parties' => fake()->optional(0.8)->randomElements(['Stripe', 'Zendesk', 'Mailchimp'], fake()->numberBetween(1, 2)),
                'withdrawal_method' => 'Contactar a privacy@cooperativa.com',
                'consent_document_url' => 'https://cooperativa.com/third-party/v' . fake()->randomElement(['1.0', '1.1']),
                'revoked_at' => fake()->optional(0.3)->dateTimeBetween('-6 months', 'now'),
                'revocation_reason' => fake()->optional(0.3)->sentence(),
                'consent_context' => [
                    'source' => fake()->randomElement(['registration', 'settings', 'service_activation']),
                    'campaign' => fake()->optional()->word(),
                    'referrer' => fake()->optional()->url(),
                ],
                'metadata' => [
                    'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari']),
                    'device' => fake()->randomElement(['mobile', 'desktop', 'tablet']),
                    'locale' => fake()->randomElement(['es', 'en', 'ca']),
                ],
            ]);
            $this->command->line("   ✅ Creado consentimiento terceros: {$consent->consent_type} - Usuario: {$consent->user_id}");
        }
    }

    /**
     * Crear consentimientos recientes
     */
    private function createRecentConsents($users): void
    {
        $this->command->info('🕒 Creando consentimientos recientes...');

        for ($i = 0; $i < 12; $i++) {
            $consent = ConsentLog::create([
                'user_id' => $users->random()->id,
                'consent_type' => fake()->randomElement(array_keys(ConsentLog::CONSENT_TYPES)),
                'consent_given' => fake()->boolean(85),
                'consented_at' => Carbon::now()->subDays(rand(1, 7)),
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'version' => fake()->optional(0.8)->randomElement(['2.0', '2.1']),
                'purpose' => fake()->optional(0.7)->sentence(),
                'legal_basis' => fake()->optional(0.6)->randomElement([
                    'Artículo 6.1.a GDPR - Consentimiento',
                    'Artículo 6.1.b GDPR - Ejecución de contrato',
                    'Artículo 6.1.f GDPR - Interés legítimo'
                ]),
                'data_categories' => fake()->optional(0.5)->randomElements([
                    'personal_data', 'contact_info', 'usage_data', 'preferences'
                ], fake()->numberBetween(1, 3)),
                'retention_period' => fake()->optional(0.5)->randomElement([
                    '5 años', '2 años', '1 año', 'Hasta revocación'
                ]),
                'third_parties' => fake()->optional(0.3)->randomElements([
                    'Google Analytics', 'Mailchimp', 'Stripe'
                ], fake()->numberBetween(1, 2)),
                'withdrawal_method' => fake()->optional(0.4)->randomElement([
                    'Contactar a privacy@cooperativa.com',
                    'Configuración de cuenta',
                    'Formulario de contacto'
                ]),
                'consent_document_url' => fake()->optional(0.8)->url(),
                'revoked_at' => null,
                'revocation_reason' => null,
                'consent_context' => [
                    'source' => fake()->randomElement(['registration', 'login', 'settings', 'onboarding']),
                    'campaign' => fake()->optional()->word(),
                    'referrer' => fake()->optional()->url(),
                ],
                'metadata' => [
                    'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari']),
                    'device' => fake()->randomElement(['mobile', 'desktop', 'tablet']),
                    'locale' => fake()->randomElement(['es', 'en', 'ca']),
                ],
            ]);
            $this->command->line("   ✅ Creado consentimiento reciente: {$consent->consent_type} - Usuario: {$consent->user_id}");
        }
    }

    /**
     * Crear consentimientos denegados
     */
    private function createDeniedConsents($users): void
    {
        $this->command->info('🚫 Creando consentimientos denegados...');

        for ($i = 0; $i < 8; $i++) {
            $consent = ConsentLog::create([
                'user_id' => $users->random()->id,
                'consent_type' => fake()->randomElement(array_keys(ConsentLog::CONSENT_TYPES)),
                'consent_given' => false,
                'consented_at' => Carbon::now()->subDays(rand(1, 365)),
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'version' => fake()->optional(0.7)->randomElement(['1.0', '1.1', '2.0']),
                'purpose' => fake()->optional(0.6)->sentence(),
                'legal_basis' => fake()->optional(0.5)->randomElement([
                    'Artículo 6.1.a GDPR - Consentimiento',
                    'Artículo 6.1.b GDPR - Ejecución de contrato',
                    'Artículo 6.1.f GDPR - Interés legítimo'
                ]),
                'data_categories' => fake()->optional(0.4)->randomElements([
                    'personal_data', 'contact_info', 'usage_data', 'preferences'
                ], fake()->numberBetween(1, 3)),
                'retention_period' => fake()->optional(0.5)->randomElement([
                    '5 años', '2 años', '1 año', 'Hasta revocación'
                ]),
                'third_parties' => fake()->optional(0.3)->randomElements([
                    'Google Analytics', 'Mailchimp', 'Stripe'
                ], fake()->numberBetween(1, 2)),
                'withdrawal_method' => fake()->optional(0.4)->randomElement([
                    'Contactar a privacy@cooperativa.com',
                    'Configuración de cuenta',
                    'Formulario de contacto'
                ]),
                'consent_document_url' => fake()->optional(0.8)->url(),
                'revoked_at' => null,
                'revocation_reason' => null,
                'consent_context' => [
                    'source' => fake()->randomElement(['registration', 'login', 'settings', 'banner']),
                    'campaign' => fake()->optional()->word(),
                    'referrer' => fake()->optional()->url(),
                ],
                'metadata' => [
                    'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari']),
                    'device' => fake()->randomElement(['mobile', 'desktop', 'tablet']),
                    'locale' => fake()->randomElement(['es', 'en', 'ca']),
                ],
            ]);
            $this->command->line("   ✅ Creado consentimiento denegado: {$consent->consent_type} - Usuario: {$consent->user_id}");
        }
    }

    /**
     * Crear consentimientos con diferentes versiones
     */
    private function createVersionedConsents($users): void
    {
        $this->command->info('📋 Creando consentimientos con diferentes versiones...');

        for ($i = 0; $i < 10; $i++) {
            $consent = ConsentLog::create([
                'user_id' => $users->random()->id,
                'consent_type' => fake()->randomElement(['terms_and_conditions', 'privacy_policy']),
                'consent_given' => fake()->boolean(90),
                'consented_at' => Carbon::now()->subDays(rand(1, 365)),
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'version' => fake()->randomElement(['1.0', '1.1', '1.2', '2.0', '2.1']),
                'purpose' => fake()->optional(0.7)->sentence(),
                'legal_basis' => fake()->optional(0.6)->randomElement([
                    'Artículo 6.1.a GDPR - Consentimiento',
                    'Artículo 6.1.b GDPR - Ejecución de contrato',
                    'Artículo 6.1.f GDPR - Interés legítimo'
                ]),
                'data_categories' => fake()->optional(0.5)->randomElements([
                    'personal_data', 'contact_info', 'usage_data', 'preferences'
                ], fake()->numberBetween(1, 3)),
                'retention_period' => fake()->optional(0.5)->randomElement([
                    '5 años', '2 años', '1 año', 'Hasta revocación'
                ]),
                'third_parties' => fake()->optional(0.3)->randomElements([
                    'Google Analytics', 'Mailchimp', 'Stripe'
                ], fake()->numberBetween(1, 2)),
                'withdrawal_method' => fake()->optional(0.4)->randomElement([
                    'Contactar a privacy@cooperativa.com',
                    'Configuración de cuenta',
                    'Formulario de contacto'
                ]),
                'consent_document_url' => fake()->optional(0.8)->url(),
                'revoked_at' => fake()->optional(0.1)->dateTimeBetween('-6 months', 'now'),
                'revocation_reason' => fake()->optional(0.1)->sentence(),
                'consent_context' => [
                    'source' => fake()->randomElement(['registration', 'login', 'version_update']),
                    'campaign' => fake()->optional()->word(),
                    'referrer' => fake()->optional()->url(),
                ],
                'metadata' => [
                    'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari']),
                    'device' => fake()->randomElement(['mobile', 'desktop', 'tablet']),
                    'locale' => fake()->randomElement(['es', 'en', 'ca']),
                ],
            ]);
            $this->command->line("   ✅ Creado consentimiento versionado: {$consent->consent_type} v{$consent->version} - Usuario: {$consent->user_id}");
        }
    }

    /**
     * Crear un consentimiento individual
     */
    private function createConsent(array $data): void
    {
        $consent = ConsentLog::create($data);
        $this->command->line("   ✅ Creado consentimiento: {$consent->consent_type} - Usuario: {$consent->user_id} - Estado: " . ($consent->isActive() ? 'Activo' : 'Revocado'));
    }
}
