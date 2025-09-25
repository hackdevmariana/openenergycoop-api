<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SocialLink;
use App\Models\User;
use App\Models\Organization;
use Carbon\Carbon;

class SocialLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔗 Creando enlaces de redes sociales para la cooperativa energética...');

        // Limpiar enlaces existentes para evitar duplicados
        SocialLink::query()->delete();

        // Obtener usuarios y organizaciones disponibles
        $users = User::all();
        $organizations = Organization::all();

        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. No se pueden crear enlaces sociales.');
            return;
        }

        $this->command->info("👥 Usuarios disponibles: {$users->count()}");
        $this->command->info("🏢 Organizaciones disponibles: {$organizations->count()}");

        // Crear enlaces sociales para diferentes escenarios
        $this->createMainSocialLinks($users, $organizations);
        $this->createSecondarySocialLinks($users, $organizations);
        $this->createProfessionalSocialLinks($users, $organizations);
        $this->createCommunitySocialLinks($users, $organizations);
        $this->createInactiveSocialLinks($users, $organizations);
        $this->createDraftSocialLinks($users, $organizations);

        $this->command->info('✅ SocialLinkSeeder completado. Se crearon ' . SocialLink::count() . ' enlaces sociales.');
    }

    /**
     * Crear enlaces sociales principales
     */
    private function createMainSocialLinks($users, $organizations): void
    {
        $this->command->info('🌟 Creando enlaces sociales principales...');

        $mainSocialLinks = [
            [
                'platform' => 'facebook',
                'url' => 'https://facebook.com/openenergycoop',
                'icon' => 'fab fa-facebook-f',
                'css_class' => 'social-link-facebook',
                'color' => '#1877F2',
                'order' => 1,
                'is_active' => true,
                'followers_count' => 12500,
                'is_draft' => false,
            ],
            [
                'platform' => 'twitter',
                'url' => 'https://twitter.com/openenergycoop',
                'icon' => 'fab fa-twitter',
                'css_class' => 'social-link-twitter',
                'color' => '#1DA1F2',
                'order' => 2,
                'is_active' => true,
                'followers_count' => 8900,
                'is_draft' => false,
            ],
            [
                'platform' => 'instagram',
                'url' => 'https://instagram.com/openenergycoop',
                'icon' => 'fab fa-instagram',
                'css_class' => 'social-link-instagram',
                'color' => '#E4405F',
                'order' => 3,
                'is_active' => true,
                'followers_count' => 15600,
                'is_draft' => false,
            ],
            [
                'platform' => 'linkedin',
                'url' => 'https://linkedin.com/company/openenergycoop',
                'icon' => 'fab fa-linkedin-in',
                'css_class' => 'social-link-linkedin',
                'color' => '#0A66C2',
                'order' => 4,
                'is_active' => true,
                'followers_count' => 3200,
                'is_draft' => false,
            ],
        ];

        foreach ($mainSocialLinks as $socialLink) {
            $this->createSocialLink($socialLink, $users, $organizations);
        }
    }

    /**
     * Crear enlaces sociales secundarios
     */
    private function createSecondarySocialLinks($users, $organizations): void
    {
        $this->command->info('📱 Creando enlaces sociales secundarios...');

        $secondarySocialLinks = [
            [
                'platform' => 'youtube',
                'url' => 'https://youtube.com/c/openenergycoop',
                'icon' => 'fab fa-youtube',
                'css_class' => 'social-link-youtube',
                'color' => '#FF0000',
                'order' => 5,
                'is_active' => true,
                'followers_count' => 4200,
                'is_draft' => false,
            ],
            [
                'platform' => 'tiktok',
                'url' => 'https://tiktok.com/@openenergycoop',
                'icon' => 'fab fa-tiktok',
                'css_class' => 'social-link-tiktok',
                'color' => '#000000',
                'order' => 6,
                'is_active' => true,
                'followers_count' => 1800,
                'is_draft' => false,
            ],
            [
                'platform' => 'telegram',
                'url' => 'https://t.me/openenergycoop',
                'icon' => 'fab fa-telegram-plane',
                'css_class' => 'social-link-telegram',
                'color' => '#0088CC',
                'order' => 7,
                'is_active' => true,
                'followers_count' => 950,
                'is_draft' => false,
            ],
        ];

        foreach ($secondarySocialLinks as $socialLink) {
            $this->createSocialLink($socialLink, $users, $organizations);
        }
    }

    /**
     * Crear enlaces sociales profesionales
     */
    private function createProfessionalSocialLinks($users, $organizations): void
    {
        $this->command->info('💼 Creando enlaces sociales profesionales...');

        $professionalSocialLinks = [
            [
                'platform' => 'github',
                'url' => 'https://github.com/openenergycoop',
                'icon' => 'fab fa-github',
                'css_class' => 'social-link-github',
                'color' => '#181717',
                'order' => 8,
                'is_active' => true,
                'followers_count' => 1200,
                'is_draft' => false,
            ],
            [
                'platform' => 'discord',
                'url' => 'https://discord.gg/openenergycoop',
                'icon' => 'fab fa-discord',
                'css_class' => 'social-link-discord',
                'color' => '#5865F2',
                'order' => 9,
                'is_active' => true,
                'followers_count' => 750,
                'is_draft' => false,
            ],
        ];

        foreach ($professionalSocialLinks as $socialLink) {
            $this->createSocialLink($socialLink, $users, $organizations);
        }
    }

    /**
     * Crear enlaces sociales comunitarios
     */
    private function createCommunitySocialLinks($users, $organizations): void
    {
        $this->command->info('🤝 Creando enlaces sociales comunitarios...');

        $communitySocialLinks = [
            [
                'platform' => 'whatsapp',
                'url' => 'https://wa.me/34912345678',
                'icon' => 'fab fa-whatsapp',
                'css_class' => 'social-link-whatsapp',
                'color' => '#25D366',
                'order' => 10,
                'is_active' => true,
                'followers_count' => null, // WhatsApp no tiene seguidores públicos
                'is_draft' => false,
            ],
            [
                'platform' => 'other',
                'url' => 'https://mastodon.social/@openenergycoop',
                'icon' => 'fab fa-mastodon',
                'css_class' => 'social-link-mastodon',
                'color' => '#6364FF',
                'order' => 11,
                'is_active' => true,
                'followers_count' => 450,
                'is_draft' => false,
            ],
        ];

        foreach ($communitySocialLinks as $socialLink) {
            $this->createSocialLink($socialLink, $users, $organizations);
        }
    }

    /**
     * Crear enlaces sociales inactivos
     */
    private function createInactiveSocialLinks($users, $organizations): void
    {
        $this->command->info('⏸️ Creando enlaces sociales inactivos...');

        $inactiveSocialLinks = [
            [
                'platform' => 'other',
                'url' => 'https://snapchat.com/add/openenergycoop',
                'icon' => 'fab fa-snapchat-ghost',
                'css_class' => 'social-link-snapchat',
                'color' => '#FFFC00',
                'order' => 12,
                'is_active' => false,
                'followers_count' => 320,
                'is_draft' => false,
            ],
            [
                'platform' => 'other',
                'url' => 'https://pinterest.com/openenergycoop',
                'icon' => 'fab fa-pinterest',
                'css_class' => 'social-link-pinterest',
                'color' => '#BD081C',
                'order' => 13,
                'is_active' => false,
                'followers_count' => 180,
                'is_draft' => false,
            ],
        ];

        foreach ($inactiveSocialLinks as $socialLink) {
            $this->createSocialLink($socialLink, $users, $organizations);
        }
    }

    /**
     * Crear enlaces sociales en borrador
     */
    private function createDraftSocialLinks($users, $organizations): void
    {
        $this->command->info('📝 Creando enlaces sociales en borrador...');

        $draftSocialLinks = [
            [
                'platform' => 'other',
                'url' => 'https://reddit.com/r/openenergycoop',
                'icon' => 'fab fa-reddit-alien',
                'css_class' => 'social-link-reddit',
                'color' => '#FF4500',
                'order' => 14,
                'is_active' => true,
                'followers_count' => null,
                'is_draft' => true,
            ],
            [
                'platform' => 'other',
                'url' => 'https://twitch.tv/openenergycoop',
                'icon' => 'fab fa-twitch',
                'css_class' => 'social-link-twitch',
                'color' => '#9146FF',
                'order' => 15,
                'is_active' => true,
                'followers_count' => null,
                'is_draft' => true,
            ],
        ];

        foreach ($draftSocialLinks as $socialLink) {
            $this->createSocialLink($socialLink, $users, $organizations);
        }
    }

    /**
     * Crear un enlace social individual
     */
    private function createSocialLink(array $data, $users, $organizations): void
    {
        $organizationId = $organizations->isEmpty() ? null : $organizations->random()->id;
        $createdByUserId = $users->random()->id;

        $socialLink = SocialLink::create(array_merge($data, [
            'organization_id' => $organizationId,
            'created_by_user_id' => $createdByUserId,
        ]));

        $status = $socialLink->is_draft ? 'borrador' : ($socialLink->is_active ? 'activo' : 'inactivo');
        $followers = $socialLink->followers_count ? " ({$socialLink->getFormattedFollowersCount()} seguidores)" : '';
        
        $this->command->line("   ✅ Creado enlace: {$socialLink->getPlatformLabel()} - {$status}{$followers}");
    }
}
