<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Banner;
use App\Models\User;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener un usuario para asignar como creador
        $user = User::first();
        
        if (!$user) {
            $this->command->warn('No hay usuarios en la base de datos. Creando un usuario de prueba...');
            $user = User::create([
                'name' => 'Admin Test',
                'email' => 'admin@test.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

        $banners = [
            [
                'title' => 'Banner Principal - Únete a la Cooperativa',
                'description' => 'Banner principal promocionando la adhesión a la cooperativa energética',
                'alt_text' => 'Únete a nuestra cooperativa energética sostenible',
                'image' => 'banners/principal-cooperativa.jpg',
                'banner_type' => 'header',
                'position' => 1,
                'active' => true,
                'internal_link' => '/unirse',
                'exhibition_beginning' => now()->subDays(30),
                'exhibition_end' => now()->addDays(60),
                'click_count' => 45,
                'impression_count' => 1250,
                'display_rules' => [
                    'page' => '/',
                    'user_type' => 'guest'
                ],
                'created_by_user_id' => $user->id,
                'is_draft' => false,
                'published_at' => now()->subDays(30),
            ],
            [
                'title' => 'Banner Lateral - Calculadora de Ahorro',
                'description' => 'Banner lateral promocionando la calculadora de ahorro energético',
                'alt_text' => 'Calcula cuánto puedes ahorrar con nuestra cooperativa',
                'image' => 'banners/calculadora-ahorro.jpg',
                'banner_type' => 'sidebar',
                'position' => 1,
                'active' => true,
                'internal_link' => '/calculadora',
                'exhibition_beginning' => now()->subDays(15),
                'exhibition_end' => now()->addDays(45),
                'click_count' => 23,
                'impression_count' => 890,
                'display_rules' => [
                    'page' => '/servicios',
                    'user_type' => 'member'
                ],
                'created_by_user_id' => $user->id,
                'is_draft' => false,
                'published_at' => now()->subDays(15),
            ],
            [
                'title' => 'Banner de Pie - Newsletter',
                'description' => 'Banner en el pie de página para suscripción al newsletter',
                'alt_text' => 'Suscríbete a nuestro newsletter y mantente informado',
                'image' => 'banners/newsletter.jpg',
                'banner_type' => 'footer',
                'position' => 1,
                'active' => true,
                'internal_link' => '/newsletter',
                'exhibition_beginning' => now()->subDays(7),
                'exhibition_end' => now()->addDays(90),
                'click_count' => 67,
                'impression_count' => 2100,
                'display_rules' => [
                    'page' => '*',
                    'user_type' => '*'
                ],
                'created_by_user_id' => $user->id,
                'is_draft' => false,
                'published_at' => now()->subDays(7),
            ],
            [
                'title' => 'Banner de Contenido - Tecnología',
                'description' => 'Banner promocionando la tecnología de la plataforma',
                'alt_text' => 'Descubre nuestra tecnología inteligente de gestión energética',
                'image' => 'banners/tecnologia.jpg',
                'banner_type' => 'inline',
                'position' => 1,
                'active' => true,
                'internal_link' => '/tecnologia',
                'exhibition_beginning' => now()->subDays(5),
                'exhibition_end' => now()->addDays(30),
                'click_count' => 34,
                'impression_count' => 756,
                'display_rules' => [
                    'page' => '/sobre-nosotros',
                    'user_type' => '*'
                ],
                'created_by_user_id' => $user->id,
                'is_draft' => false,
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Banner Popup - Oferta Especial',
                'description' => 'Banner popup con oferta especial para nuevos miembros',
                'alt_text' => 'Oferta especial: 50% de descuento en la cuota de socio',
                'image' => 'banners/oferta-especial.jpg',
                'banner_type' => 'popup',
                'position' => 1,
                'active' => true,
                'internal_link' => '/oferta-especial',
                'exhibition_beginning' => now()->subDays(2),
                'exhibition_end' => now()->addDays(15),
                'click_count' => 89,
                'impression_count' => 450,
                'display_rules' => [
                    'page' => '/',
                    'user_type' => 'guest',
                    'visit_count' => '1'
                ],
                'created_by_user_id' => $user->id,
                'is_draft' => false,
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Banner Flotante - Soporte',
                'description' => 'Banner flotante para acceso rápido al soporte',
                'alt_text' => '¿Necesitas ayuda? Contacta con nuestro soporte',
                'image' => 'banners/soporte.jpg',
                'banner_type' => 'popup',
                'position' => 1,
                'active' => true,
                'internal_link' => '/soporte',
                'exhibition_beginning' => now()->subDays(1),
                'exhibition_end' => now()->addDays(120),
                'click_count' => 156,
                'impression_count' => 3200,
                'display_rules' => [
                    'page' => '*',
                    'user_type' => '*'
                ],
                'created_by_user_id' => $user->id,
                'is_draft' => false,
                'published_at' => now()->subDays(1),
            ],
            [
                'title' => 'Banner de Cabecera - Eventos',
                'description' => 'Banner promocionando próximos eventos de la cooperativa',
                'alt_text' => 'Próximos eventos y asambleas de la cooperativa',
                'image' => 'banners/eventos.jpg',
                'banner_type' => 'header',
                'position' => 2,
                'active' => true,
                'internal_link' => '/eventos',
                'exhibition_beginning' => now(),
                'exhibition_end' => now()->addDays(20),
                'click_count' => 12,
                'impression_count' => 680,
                'display_rules' => [
                    'page' => '/',
                    'user_type' => 'member'
                ],
                'created_by_user_id' => $user->id,
                'is_draft' => false,
                'published_at' => now(),
            ],
            [
                'title' => 'Banner de Contenido - Sostenibilidad',
                'description' => 'Banner promocionando el compromiso ambiental',
                'alt_text' => 'Nuestro compromiso con la sostenibilidad ambiental',
                'image' => 'banners/sostenibilidad.jpg',
                'banner_type' => 'inline',
                'position' => 2,
                'active' => false, // Este está inactivo
                'internal_link' => '/sostenibilidad',
                'exhibition_beginning' => now()->addDays(10),
                'exhibition_end' => now()->addDays(50),
                'click_count' => 0,
                'impression_count' => 0,
                'display_rules' => [
                    'page' => '/impacto',
                    'user_type' => '*'
                ],
                'created_by_user_id' => $user->id,
                'is_draft' => true,
                'published_at' => null,
            ],
            [
                'title' => 'Banner Lateral - Recursos',
                'description' => 'Banner lateral promocionando recursos educativos',
                'alt_text' => 'Recursos educativos sobre energía sostenible',
                'image' => 'banners/recursos.jpg',
                'banner_type' => 'sidebar',
                'position' => 2,
                'active' => true,
                'internal_link' => '/recursos',
                'exhibition_beginning' => now()->addDays(5),
                'exhibition_end' => now()->addDays(75),
                'click_count' => 8,
                'impression_count' => 234,
                'display_rules' => [
                    'page' => '/educacion',
                    'user_type' => '*'
                ],
                'created_by_user_id' => $user->id,
                'is_draft' => false,
                'published_at' => now()->addDays(5),
            ],
        ];

        foreach ($banners as $banner) {
            Banner::create($banner);
        }

        $activeCount = collect($banners)->where('active', true)->count();
        $inactiveCount = collect($banners)->where('active', false)->count();

        $this->command->info("BannerSeeder ejecutado exitosamente.");
        $this->command->info("Se crearon " . count($banners) . " banners:");
        $this->command->info("- Activos: {$activeCount}");
        $this->command->info("- Inactivos: {$inactiveCount}");
        $this->command->info("El badge mostrará: {$activeCount}");
    }
}
