<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NewsletterSubscription;
use App\Models\Organization;
use Carbon\Carbon;

class NewsletterSubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📧 Creando suscripciones a newsletters para la cooperativa energética...');

        // Limpiar suscripciones existentes para evitar duplicados
        NewsletterSubscription::query()->delete();

        // Obtener organizaciones disponibles
        $organizations = Organization::all();

        if ($organizations->isEmpty()) {
            $this->command->warn('⚠️ No hay organizaciones disponibles. Creando suscripciones sin organización.');
        }

        // 1. Suscripciones Confirmadas y Activas
        $this->createConfirmedSubscriptions($organizations);

        // 2. Suscripciones Pendientes de Confirmación
        $this->createPendingSubscriptions($organizations);

        // 3. Suscripciones Desuscritas
        $this->createUnsubscribedSubscriptions($organizations);

        // 4. Suscripciones con Emails Rebotados
        $this->createBouncedSubscriptions($organizations);

        // 5. Suscripciones Marcadas como Spam
        $this->createComplainedSubscriptions($organizations);

        // 6. Suscriptores Altamente Comprometidos
        $this->createEngagedSubscriptions($organizations);

        // 7. Suscriptores de Bajo Engagement
        $this->createLowEngagementSubscriptions($organizations);

        // 8. Suscripciones Recientes
        $this->createRecentSubscriptions($organizations);

        // 9. Suscripciones por Idiomas
        $this->createLanguageSpecificSubscriptions($organizations);

        // 10. Suscripciones por Fuentes
        $this->createSourceSpecificSubscriptions($organizations);

        $this->command->info('✅ NewsletterSubscriptionSeeder completado. Se crearon ' . NewsletterSubscription::count() . ' suscripciones.');
    }

    /**
     * Crear suscripciones confirmadas y activas
     */
    private function createConfirmedSubscriptions($organizations): void
    {
        $this->command->info('✅ Creando suscripciones confirmadas y activas...');

        $confirmedSubscriptions = [
            [
                'name' => 'María García López',
                'email' => 'maria.garcia@email.com',
                'status' => 'confirmed',
                'subscription_source' => 'website',
                'preferences' => [
                    'frequency' => 'weekly',
                    'format' => 'html',
                    'topics' => ['general', 'energy', 'community']
                ],
                'tags' => ['newsletter', 'active_subscriber'],
                'language' => 'es',
                'emails_sent' => 12,
                'emails_opened' => 10,
                'links_clicked' => 5,
                'confirmed_at' => Carbon::now()->subDays(30),
            ],
            [
                'name' => 'Carlos Rodríguez Martín',
                'email' => 'carlos.rodriguez@email.com',
                'status' => 'confirmed',
                'subscription_source' => 'social',
                'preferences' => [
                    'frequency' => 'monthly',
                    'format' => 'text',
                    'topics' => ['technology', 'innovation']
                ],
                'tags' => ['newsletter', 'tech_enthusiast'],
                'language' => 'es',
                'emails_sent' => 8,
                'emails_opened' => 7,
                'links_clicked' => 3,
                'confirmed_at' => Carbon::now()->subDays(45),
            ],
            [
                'name' => 'Ana Martínez Fernández',
                'email' => 'ana.martinez@email.com',
                'status' => 'confirmed',
                'subscription_source' => 'referral',
                'preferences' => [
                    'frequency' => 'weekly',
                    'format' => 'html',
                    'topics' => ['sustainability', 'environment']
                ],
                'tags' => ['newsletter', 'eco_friendly'],
                'language' => 'es',
                'emails_sent' => 15,
                'emails_opened' => 14,
                'links_clicked' => 8,
                'confirmed_at' => Carbon::now()->subDays(20),
            ],
        ];

        foreach ($confirmedSubscriptions as $subscription) {
            $this->createSubscription($subscription, $organizations);
        }

        // Crear suscripciones adicionales usando el factory
        $this->createFactorySubscriptions('confirmed', 10, $organizations);
    }

    /**
     * Crear suscripciones pendientes de confirmación
     */
    private function createPendingSubscriptions($organizations): void
    {
        $this->command->info('⏳ Creando suscripciones pendientes de confirmación...');

        $pendingSubscriptions = [
            [
                'name' => 'Luis Pérez González',
                'email' => 'luis.perez@email.com',
                'status' => 'pending',
                'subscription_source' => 'website',
                'preferences' => [
                    'frequency' => 'weekly',
                    'format' => 'html',
                    'topics' => ['general', 'energy']
                ],
                'tags' => ['newsletter', 'new_subscriber'],
                'language' => 'es',
                'emails_sent' => 0,
                'emails_opened' => 0,
                'links_clicked' => 0,
            ],
            [
                'name' => 'Sofia Jiménez Ruiz',
                'email' => 'sofia.jimenez@email.com',
                'status' => 'pending',
                'subscription_source' => 'social',
                'preferences' => [
                    'frequency' => 'monthly',
                    'format' => 'html',
                    'topics' => ['community', 'events']
                ],
                'tags' => ['newsletter', 'lead'],
                'language' => 'es',
                'emails_sent' => 0,
                'emails_opened' => 0,
                'links_clicked' => 0,
            ],
        ];

        foreach ($pendingSubscriptions as $subscription) {
            $this->createSubscription($subscription, $organizations);
        }

        $this->createFactorySubscriptions('pending', 8, $organizations);
    }

    /**
     * Crear suscripciones desuscritas
     */
    private function createUnsubscribedSubscriptions($organizations): void
    {
        $this->command->info('🚫 Creando suscripciones desuscritas...');

        $unsubscribedSubscriptions = [
            [
                'name' => 'Pedro Sánchez Díaz',
                'email' => 'pedro.sanchez@email.com',
                'status' => 'unsubscribed',
                'subscription_source' => 'website',
                'preferences' => [
                    'frequency' => 'weekly',
                    'format' => 'html',
                    'topics' => ['general']
                ],
                'tags' => ['newsletter', 'unsubscribed'],
                'language' => 'es',
                'emails_sent' => 5,
                'emails_opened' => 3,
                'links_clicked' => 1,
                'unsubscribed_at' => Carbon::now()->subDays(10),
            ],
            [
                'name' => 'Carmen López Ruiz',
                'email' => 'carmen.lopez@email.com',
                'status' => 'unsubscribed',
                'subscription_source' => 'email',
                'preferences' => [
                    'frequency' => 'monthly',
                    'format' => 'text',
                    'topics' => ['technology']
                ],
                'tags' => ['newsletter', 'unsubscribed'],
                'language' => 'es',
                'emails_sent' => 8,
                'emails_opened' => 6,
                'links_clicked' => 2,
                'unsubscribed_at' => Carbon::now()->subDays(5),
            ],
        ];

        foreach ($unsubscribedSubscriptions as $subscription) {
            $this->createSubscription($subscription, $organizations);
        }

        $this->createFactorySubscriptions('unsubscribed', 6, $organizations);
    }

    /**
     * Crear suscripciones con emails rebotados
     */
    private function createBouncedSubscriptions($organizations): void
    {
        $this->command->info('📧 Creando suscripciones con emails rebotados...');

        $bouncedSubscriptions = [
            [
                'name' => 'Roberto Fernández García',
                'email' => 'roberto.fernandez@email.com',
                'status' => 'bounced',
                'subscription_source' => 'website',
                'preferences' => [
                    'frequency' => 'weekly',
                    'format' => 'html',
                    'topics' => ['general']
                ],
                'tags' => ['newsletter', 'bounced'],
                'language' => 'es',
                'emails_sent' => 3,
                'emails_opened' => 0,
                'links_clicked' => 0,
            ],
        ];

        foreach ($bouncedSubscriptions as $subscription) {
            $this->createSubscription($subscription, $organizations);
        }

        $this->createFactorySubscriptions('bounced', 4, $organizations);
    }

    /**
     * Crear suscripciones marcadas como spam
     */
    private function createComplainedSubscriptions($organizations): void
    {
        $this->command->info('🚨 Creando suscripciones marcadas como spam...');

        $complainedSubscriptions = [
            [
                'name' => 'Isabel Torres Moreno',
                'email' => 'isabel.torres@email.com',
                'status' => 'complained',
                'subscription_source' => 'website',
                'preferences' => [
                    'frequency' => 'weekly',
                    'format' => 'html',
                    'topics' => ['general']
                ],
                'tags' => ['newsletter', 'complained'],
                'language' => 'es',
                'emails_sent' => 2,
                'emails_opened' => 1,
                'links_clicked' => 0,
            ],
        ];

        foreach ($complainedSubscriptions as $subscription) {
            $this->createSubscription($subscription, $organizations);
        }

        $this->createFactorySubscriptions('complained', 3, $organizations);
    }

    /**
     * Crear suscriptores altamente comprometidos
     */
    private function createEngagedSubscriptions($organizations): void
    {
        $this->command->info('⭐ Creando suscriptores altamente comprometidos...');

        $engagedSubscriptions = [
            [
                'name' => 'David Moreno Sánchez',
                'email' => 'david.moreno@email.com',
                'status' => 'confirmed',
                'subscription_source' => 'website',
                'preferences' => [
                    'frequency' => 'daily',
                    'format' => 'html',
                    'topics' => ['general', 'energy', 'technology', 'community']
                ],
                'tags' => ['newsletter', 'highly_engaged'],
                'language' => 'es',
                'emails_sent' => 25,
                'emails_opened' => 24,
                'links_clicked' => 15,
                'confirmed_at' => Carbon::now()->subDays(60),
            ],
            [
                'name' => 'Laura Vega Castro',
                'email' => 'laura.vega@email.com',
                'status' => 'confirmed',
                'subscription_source' => 'social',
                'preferences' => [
                    'frequency' => 'weekly',
                    'format' => 'html',
                    'topics' => ['sustainability', 'innovation', 'community']
                ],
                'tags' => ['newsletter', 'highly_engaged'],
                'language' => 'es',
                'emails_sent' => 20,
                'emails_opened' => 19,
                'links_clicked' => 12,
                'confirmed_at' => Carbon::now()->subDays(45),
            ],
        ];

        foreach ($engagedSubscriptions as $subscription) {
            $this->createSubscription($subscription, $organizations);
        }

        $this->createFactorySubscriptions('confirmed', 8, $organizations, ['highly_engaged']);
    }

    /**
     * Crear suscriptores de bajo engagement
     */
    private function createLowEngagementSubscriptions($organizations): void
    {
        $this->command->info('📉 Creando suscriptores de bajo engagement...');

        $this->createFactorySubscriptions('confirmed', 5, $organizations, ['low_engagement']);
    }

    /**
     * Crear suscripciones recientes
     */
    private function createRecentSubscriptions($organizations): void
    {
        $this->command->info('🆕 Creando suscripciones recientes...');

        $this->createFactorySubscriptions('confirmed', 10, $organizations, ['recent']);
    }

    /**
     * Crear suscripciones específicas por idioma
     */
    private function createLanguageSpecificSubscriptions($organizations): void
    {
        $this->command->info('🌍 Creando suscripciones por idiomas específicos...');

        // Suscripciones en inglés
        $this->createFactorySubscriptions('confirmed', 8, $organizations, [], 'en');

        // Suscripciones en catalán
        $this->createFactorySubscriptions('confirmed', 6, $organizations, [], 'ca');

        // Suscripciones en euskera
        $this->createFactorySubscriptions('confirmed', 4, $organizations, [], 'eu');

        // Suscripciones en gallego
        $this->createFactorySubscriptions('confirmed', 4, $organizations, [], 'gl');
    }

    /**
     * Crear suscripciones específicas por fuente
     */
    private function createSourceSpecificSubscriptions($organizations): void
    {
        $this->command->info('📱 Creando suscripciones por fuentes específicas...');

        // Suscripciones desde redes sociales
        $this->createFactorySubscriptions('confirmed', 10, $organizations, [], 'es', 'social');

        // Suscripciones desde landing pages
        $this->createFactorySubscriptions('confirmed', 8, $organizations, [], 'es', 'landing');

        // Suscripciones desde referidos
        $this->createFactorySubscriptions('confirmed', 6, $organizations, [], 'es', 'referral');

        // Suscripciones desde API
        $this->createFactorySubscriptions('confirmed', 5, $organizations, [], 'es', 'api');
    }

    /**
     * Crear suscripciones usando el factory
     */
    private function createFactorySubscriptions($status, $count, $organizations, $tags = [], $language = 'es', $source = 'website'): void
    {
        $organizationId = $organizations->isEmpty() ? null : $organizations->random()->id;

        NewsletterSubscription::factory()
            ->count($count)
            ->create([
                'status' => $status,
                'organization_id' => $organizationId,
                'language' => $language,
                'subscription_source' => $source,
                'tags' => array_merge(['newsletter'], $tags),
            ]);
    }

    /**
     * Crear una suscripción individual
     */
    private function createSubscription(array $data, $organizations): void
    {
        $organizationId = $organizations->isEmpty() ? null : $organizations->random()->id;
        
        $subscription = NewsletterSubscription::create(array_merge($data, [
            'organization_id' => $organizationId,
            'confirmation_token' => \Illuminate\Support\Str::random(64),
            'unsubscribe_token' => \Illuminate\Support\Str::random(64),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ]));

        $this->command->line("   ✅ Creada suscripción: {$subscription->email} ({$subscription->status})");
    }
}