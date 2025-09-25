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
                    'topics' => ['general', 'news', 'energy', 'community']
                ],
                'tags' => ['newsletter', 'member', 'interested_in_solar'],
                'language' => 'es',
                'confirmed_at' => Carbon::now()->subMonths(6),
                'emails_sent' => 24,
                'emails_opened' => 18,
                'links_clicked' => 8,
                'last_email_sent_at' => Carbon::now()->subDays(3),
                'last_email_opened_at' => Carbon::now()->subDays(2),
            ],
            [
                'name' => 'Carlos Rodríguez Martín',
                'email' => 'carlos.rodriguez@email.com',
                'status' => 'confirmed',
                'subscription_source' => 'form',
                'preferences' => [
                    'frequency' => 'monthly',
                    'format' => 'html',
                    'topics' => ['technology', 'environment', 'updates']
                ],
                'tags' => ['newsletter', 'customer', 'business'],
                'language' => 'es',
                'confirmed_at' => Carbon::now()->subMonths(4),
                'emails_sent' => 16,
                'emails_opened' => 12,
                'links_clicked' => 5,
                'last_email_sent_at' => Carbon::now()->subDays(7),
                'last_email_opened_at' => Carbon::now()->subDays(6),
            ],
            [
                'name' => 'Ana Martínez Fernández',
                'email' => 'ana.martinez@email.com',
                'status' => 'confirmed',
                'subscription_source' => 'landing',
                'preferences' => [
                    'frequency' => 'daily',
                    'format' => 'text',
                    'topics' => ['news', 'events', 'promotions']
                ],
                'tags' => ['newsletter', 'subscriber', 'premium'],
                'language' => 'es',
                'confirmed_at' => Carbon::now()->subMonths(2),
                'emails_sent' => 60,
                'emails_opened' => 45,
                'links_clicked' => 15,
                'last_email_sent_at' => Carbon::now()->subDay(),
                'last_email_opened_at' => Carbon::now()->subHours(12),
            ],
        ];

        foreach ($confirmedSubscriptions as $subscription) {
            $this->createSubscription($subscription, $organizations);
        }

        // Crear suscripciones adicionales usando el factory
        NewsletterSubscription::factory()
            ->confirmed()
            ->count(15)
            ->create()
            ->each(function ($subscription) use ($organizations) {
                if (!$organizations->isEmpty()) {
                    $subscription->update(['organization_id' => $organizations->random()->id]);
                }
            });
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

        // Crear suscripciones adicionales usando el factory
        NewsletterSubscription::factory()
            ->pending()
            ->count(8)
            ->create()
            ->each(function ($subscription) use ($organizations) {
                if (!$organizations->isEmpty()) {
                    $subscription->update(['organization_id' => $organizations->random()->id]);
                }
            });
    }

    /**
     * Crear suscripciones desuscritas
     */
    private function createUnsubscribedSubscriptions($organizations): void
    {
        $this->command->info('🚫 Creando suscripciones desuscritas...');

        $unsubscribedSubscriptions = [
            [
                'name' => 'Pedro Sánchez Moreno',
                'email' => 'pedro.sanchez@email.com',
                'status' => 'unsubscribed',
                'subscription_source' => 'website',
                'preferences' => [
                    'frequency' => 'weekly',
                    'format' => 'html',
                    'topics' => ['general', 'news']
                ],
                'tags' => ['newsletter', 'unsubscribe_reason:too_frequent'],
                'language' => 'es',
                'confirmed_at' => Carbon::now()->subMonths(8),
                'unsubscribed_at' => Carbon::now()->subMonths(1),
                'emails_sent' => 32,
                'emails_opened' => 20,
                'links_clicked' => 8,
            ],
            [
                'name' => 'Carmen López Vega',
                'email' => 'carmen.lopez@email.com',
                'status' => 'unsubscribed',
                'subscription_source' => 'form',
                'preferences' => [
                    'frequency' => 'monthly',
                    'format' => 'html',
                    'topics' => ['technology', 'environment']
                ],
                'tags' => ['newsletter', 'unsubscribe_reason:not_relevant'],
                'language' => 'es',
                'confirmed_at' => Carbon::now()->subMonths(10),
                'unsubscribed_at' => Carbon::now()->subWeeks(2),
                'emails_sent' => 40,
                'emails_opened' => 15,
                'links_clicked' => 3,
            ],
        ];

        foreach ($unsubscribedSubscriptions as $subscription) {
            $this->createSubscription($subscription, $organizations);
        }

        // Crear suscripciones adicionales usando el factory
        NewsletterSubscription::factory()
            ->unsubscribed()
            ->count(6)
            ->create()
            ->each(function ($subscription) use ($organizations) {
                if (!$organizations->isEmpty()) {
                    $subscription->update(['organization_id' => $organizations->random()->id]);
                }
            });
    }

    /**
     * Crear suscripciones con emails rebotados
     */
    private function createBouncedSubscriptions($organizations): void
    {
        $this->command->info('📧 Creando suscripciones con emails rebotados...');

        $bouncedSubscriptions = [
            [
                'name' => 'Roberto Fernández Castro',
                'email' => 'roberto.fernandez@email.com',
                'status' => 'bounced',
                'subscription_source' => 'website',
                'preferences' => [
                    'frequency' => 'weekly',
                    'format' => 'html',
                    'topics' => ['general', 'energy']
                ],
                'tags' => ['newsletter', 'bounced'],
                'language' => 'es',
                'confirmed_at' => Carbon::now()->subMonths(5),
                'emails_sent' => 20,
                'emails_opened' => 0,
                'links_clicked' => 0,
            ],
        ];

        foreach ($bouncedSubscriptions as $subscription) {
            $this->createSubscription($subscription, $organizations);
        }

        // Crear suscripciones adicionales usando el factory
        NewsletterSubscription::factory()
            ->bounced()
            ->count(4)
            ->create()
            ->each(function ($subscription) use ($organizations) {
                if (!$organizations->isEmpty()) {
                    $subscription->update(['organization_id' => $organizations->random()->id]);
                }
            });
    }

    /**
     * Crear suscripciones marcadas como spam
     */
    private function createComplainedSubscriptions($organizations): void
    {
        $this->command->info('🚨 Creando suscripciones marcadas como spam...');

        $complainedSubscriptions = [
            [
                'name' => 'Isabel Torres Ruiz',
                'email' => 'isabel.torres@email.com',
                'status' => 'complained',
                'subscription_source' => 'import',
                'preferences' => [
                    'frequency' => 'monthly',
                    'format' => 'html',
                    'topics' => ['promotions', 'events']
                ],
                'tags' => ['newsletter', 'spam_complaint'],
                'language' => 'es',
                'confirmed_at' => Carbon::now()->subMonths(6),
                'emails_sent' => 24,
                'emails_opened' => 5,
                'links_clicked' => 1,
            ],
        ];

        foreach ($complainedSubscriptions as $subscription) {
            $this->createSubscription($subscription, $organizations);
        }

        // Crear suscripciones adicionales usando el factory
        NewsletterSubscription::factory()
            ->complained()
            ->count(3)
            ->create()
            ->each(function ($subscription) use ($organizations) {
                if (!$organizations->isEmpty()) {
                    $subscription->update(['organization_id' => $organizations->random()->id]);
                }
            });
    }

    /**
     * Crear suscriptores altamente comprometidos
     */
    private function createEngagedSubscriptions($organizations): void
    {
        $this->command->info('⭐ Creando suscriptores altamente comprometidos...');

        $engagedSubscriptions = [
            [
                'name' => 'David Moreno Jiménez',
                'email' => 'david.moreno@email.com',
                'status' => 'confirmed',
                'subscription_source' => 'website',
                'preferences' => [
                    'frequency' => 'daily',
                    'format' => 'html',
                    'topics' => ['general', 'news', 'technology', 'energy', 'community']
                ],
                'tags' => ['newsletter', 'engaged', 'high_value', 'vip'],
                'language' => 'es',
                'confirmed_at' => Carbon::now()->subMonths(12),
                'emails_sent' => 360,
                'emails_opened' => 320,
                'links_clicked' => 150,
                'last_email_sent_at' => Carbon::now()->subDay(),
                'last_email_opened_at' => Carbon::now()->subHours(6),
            ],
            [
                'name' => 'Laura Vega Castro',
                'email' => 'laura.vega@email.com',
                'status' => 'confirmed',
                'subscription_source' => 'form',
                'preferences' => [
                    'frequency' => 'weekly',
                    'format' => 'html',
                    'topics' => ['news', 'events', 'environment', 'community']
                ],
                'tags' => ['newsletter', 'engaged', 'high_value'],
                'language' => 'es',
                'confirmed_at' => Carbon::now()->subMonths(8),
                'emails_sent' => 32,
                'emails_opened' => 30,
                'links_clicked' => 18,
                'last_email_sent_at' => Carbon::now()->subDays(3),
                'last_email_opened_at' => Carbon::now()->subDays(2),
            ],
        ];

        foreach ($engagedSubscriptions as $subscription) {
            $this->createSubscription($subscription, $organizations);
        }

        // Crear suscripciones adicionales usando el factory
        NewsletterSubscription::factory()
            ->engaged()
            ->count(10)
            ->create()
            ->each(function ($subscription) use ($organizations) {
                if (!$organizations->isEmpty()) {
                    $subscription->update(['organization_id' => $organizations->random()->id]);
                }
            });
    }

    /**
     * Crear suscriptores de bajo engagement
     */
    private function createLowEngagementSubscriptions($organizations): void
    {
        $this->command->info('📉 Creando suscriptores de bajo engagement...');

        // Crear suscripciones usando el factory
        NewsletterSubscription::factory()
            ->lowEngagement()
            ->count(12)
            ->create()
            ->each(function ($subscription) use ($organizations) {
                if (!$organizations->isEmpty()) {
                    $subscription->update(['organization_id' => $organizations->random()->id]);
                }
            });
    }

    /**
     * Crear suscripciones recientes
     */
    private function createRecentSubscriptions($organizations): void
    {
        $this->command->info('🆕 Creando suscripciones recientes...');

        // Crear suscripciones usando el factory
        NewsletterSubscription::factory()
            ->recent()
            ->count(20)
            ->create()
            ->each(function ($subscription) use ($organizations) {
                if (!$organizations->isEmpty()) {
                    $subscription->update(['organization_id' => $organizations->random()->id]);
                }
            });
    }

    /**
     * Crear suscripciones específicas por idioma
     */
    private function createLanguageSpecificSubscriptions($organizations): void
    {
        $this->command->info('🌍 Creando suscripciones por idiomas específicos...');

        // Suscripciones en inglés
        NewsletterSubscription::factory()
            ->withLanguage('en')
            ->confirmed()
            ->count(8)
            ->create()
            ->each(function ($subscription) use ($organizations) {
                if (!$organizations->isEmpty()) {
                    $subscription->update(['organization_id' => $organizations->random()->id]);
                }
            });

        // Suscripciones en catalán
        NewsletterSubscription::factory()
            ->withLanguage('ca')
            ->confirmed()
            ->count(6)
            ->create()
            ->each(function ($subscription) use ($organizations) {
                if (!$organizations->isEmpty()) {
                    $subscription->update(['organization_id' => $organizations->random()->id]);
                }
            });

        // Suscripciones en euskera
        NewsletterSubscription::factory()
            ->withLanguage('eu')
            ->confirmed()
            ->count(4)
            ->create()
            ->each(function ($subscription) use ($organizations) {
                if (!$organizations->isEmpty()) {
                    $subscription->update(['organization_id' => $organizations->random()->id]);
                }
            });

        // Suscripciones en gallego
        NewsletterSubscription::factory()
            ->withLanguage('gl')
            ->confirmed()
            ->count(4)
            ->create()
            ->each(function ($subscription) use ($organizations) {
                if (!$organizations->isEmpty()) {
                    $subscription->update(['organization_id' => $organizations->random()->id]);
                }
            });
    }

    /**
     * Crear suscripciones específicas por fuente
     */
    private function createSourceSpecificSubscriptions($organizations): void
    {
        $this->command->info('📱 Creando suscripciones por fuentes específicas...');

        // Suscripciones desde redes sociales
        NewsletterSubscription::factory()
            ->fromSource('social')
            ->confirmed()
            ->count(10)
            ->create()
            ->each(function ($subscription) use ($organizations) {
                if (!$organizations->isEmpty()) {
                    $subscription->update(['organization_id' => $organizations->random()->id]);
                }
            });

        // Suscripciones desde landing pages
        NewsletterSubscription::factory()
            ->fromSource('landing')
            ->confirmed()
            ->count(8)
            ->create()
            ->each(function ($subscription) use ($organizations) {
                if (!$organizations->isEmpty()) {
                    $subscription->update(['organization_id' => $organizations->random()->id]);
                }
            });

        // Suscripciones desde referidos
        NewsletterSubscription::factory()
            ->fromSource('referral')
            ->confirmed()
            ->count(6)
            ->create()
            ->each(function ($subscription) use ($organizations) {
                if (!$organizations->isEmpty()) {
                    $subscription->update(['organization_id' => $organizations->random()->id]);
                }
            });

        // Suscripciones desde API
        NewsletterSubscription::factory()
            ->fromSource('api')
            ->confirmed()
            ->count(5)
            ->create()
            ->each(function ($subscription) use ($organizations) {
                if (!$organizations->isEmpty()) {
                    $subscription->update(['organization_id' => $organizations->random()->id]);
                }
            });
    }

    /**
     * Crear una suscripción individual
     */
    private function createSubscription(array $data, $organizations): void
    {
        $organizationId = $organizations->isEmpty() ? null : $organizations->random()->id;
        
        // Verificar si ya existe una suscripción con este email y organización
        $existingSubscription = NewsletterSubscription::where('email', $data['email'])
            ->where('organization_id', $organizationId)
            ->exists();
            
        if ($existingSubscription) {
            $this->command->line("   ⚠️ Suscripción ya existe: {$data['email']} (organización: {$organizationId})");
            return;
        }
        
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
