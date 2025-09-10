<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Hero;
use App\Models\User;

class HeroSeeder extends Seeder
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

        $heroes = [
            [
                'text' => 'Energía Sostenible para Todos',
                'subtext' => 'Únete a nuestra cooperativa y forma parte del cambio hacia un futuro más limpio y justo',
                'text_button' => 'Únete Ahora',
                'internal_link' => '/unirse',
                'text_align' => 'center',
                'overlay_opacity' => 40,
                'animation_type' => 'fade',
                'cta_style' => 'primary',
                'position' => 1,
                'priority' => 10,
                'active' => true,
                'exhibition_beginning' => now()->subDays(30),
                'exhibition_end' => now()->addDays(30),
                'created_by_user_id' => $user->id,
                'language' => 'es',
                'is_draft' => false,
                'published_at' => now()->subDays(30),
            ],
            [
                'text' => 'Tecnología Inteligente',
                'subtext' => 'Descubre cómo nuestra plataforma revoluciona la gestión energética comunitaria',
                'text_button' => 'Conoce Más',
                'internal_link' => '/tecnologia',
                'text_align' => 'left',
                'overlay_opacity' => 50,
                'animation_type' => 'slide',
                'cta_style' => 'secondary',
                'position' => 2,
                'priority' => 8,
                'active' => true,
                'exhibition_beginning' => now()->subDays(15),
                'exhibition_end' => now()->addDays(45),
                'created_by_user_id' => $user->id,
                'language' => 'es',
                'is_draft' => false,
                'published_at' => now()->subDays(15),
            ],
            [
                'text' => 'Ahorra en tu Factura',
                'subtext' => 'Reduce hasta un 30% en tus costes energéticos con nuestras tarifas cooperativas',
                'text_button' => 'Calcula tu Ahorro',
                'internal_link' => '/calculadora',
                'text_align' => 'right',
                'overlay_opacity' => 35,
                'animation_type' => 'zoom',
                'cta_style' => 'outline',
                'position' => 3,
                'priority' => 7,
                'active' => true,
                'exhibition_beginning' => now()->subDays(7),
                'exhibition_end' => now()->addDays(60),
                'created_by_user_id' => $user->id,
                'language' => 'es',
                'is_draft' => false,
                'published_at' => now()->subDays(7),
            ],
            [
                'text' => 'Impacto Ambiental Positivo',
                'subtext' => 'Cada kWh que consumes contribuye a un planeta más sostenible',
                'text_button' => 'Ver Impacto',
                'internal_link' => '/impacto',
                'text_align' => 'center',
                'overlay_opacity' => 45,
                'animation_type' => 'bounce',
                'cta_style' => 'primary',
                'position' => 4,
                'priority' => 6,
                'active' => true,
                'exhibition_beginning' => now()->subDays(3),
                'exhibition_end' => now()->addDays(90),
                'created_by_user_id' => $user->id,
                'language' => 'es',
                'is_draft' => false,
                'published_at' => now()->subDays(3),
            ],
            [
                'text' => 'Comunidad Activa',
                'subtext' => 'Más de 1000 familias ya forman parte de nuestra red energética',
                'text_button' => 'Conoce la Comunidad',
                'internal_link' => '/comunidad',
                'text_align' => 'left',
                'overlay_opacity' => 55,
                'animation_type' => 'fade',
                'cta_style' => 'ghost',
                'position' => 5,
                'priority' => 5,
                'active' => true,
                'exhibition_beginning' => now()->subDays(1),
                'exhibition_end' => now()->addDays(120),
                'created_by_user_id' => $user->id,
                'language' => 'es',
                'is_draft' => false,
                'published_at' => now()->subDays(1),
            ],
            [
                'text' => 'Innovación Constante',
                'subtext' => 'Estamos desarrollando nuevas tecnologías para el futuro energético',
                'text_button' => 'Proyectos Futuros',
                'internal_link' => '/proyectos',
                'text_align' => 'center',
                'overlay_opacity' => 60,
                'animation_type' => 'slide',
                'cta_style' => 'link',
                'position' => 6,
                'priority' => 4,
                'active' => false, // Este está inactivo
                'exhibition_beginning' => now()->addDays(30),
                'exhibition_end' => now()->addDays(150),
                'created_by_user_id' => $user->id,
                'language' => 'es',
                'is_draft' => true,
                'published_at' => null,
            ],
            [
                'text' => 'Transparencia Total',
                'subtext' => 'Todas nuestras decisiones se toman de forma democrática y transparente',
                'text_button' => 'Asambleas',
                'internal_link' => '/asambleas',
                'text_align' => 'right',
                'overlay_opacity' => 30,
                'animation_type' => 'zoom',
                'cta_style' => 'secondary',
                'position' => 7,
                'priority' => 3,
                'active' => true,
                'exhibition_beginning' => now(),
                'exhibition_end' => now()->addDays(180),
                'created_by_user_id' => $user->id,
                'language' => 'es',
                'is_draft' => false,
                'published_at' => now(),
            ],
            [
                'text' => 'Sostenibilidad Local',
                'subtext' => 'Apoyamos el desarrollo económico y ambiental de nuestra región',
                'text_button' => 'Proyectos Locales',
                'internal_link' => '/local',
                'text_align' => 'left',
                'overlay_opacity' => 40,
                'animation_type' => 'fade',
                'cta_style' => 'primary',
                'position' => 8,
                'priority' => 2,
                'active' => true,
                'exhibition_beginning' => now()->addDays(7),
                'exhibition_end' => now()->addDays(200),
                'created_by_user_id' => $user->id,
                'language' => 'es',
                'is_draft' => false,
                'published_at' => now()->addDays(7),
            ],
        ];

        foreach ($heroes as $hero) {
            Hero::create($hero);
        }

        $activeCount = collect($heroes)->where('active', true)->count();
        $inactiveCount = collect($heroes)->where('active', false)->count();

        $this->command->info("HeroSeeder ejecutado exitosamente.");
        $this->command->info("Se crearon " . count($heroes) . " heroes:");
        $this->command->info("- Activos: {$activeCount}");
        $this->command->info("- Inactivos: {$inactiveCount}");
        $this->command->info("El badge mostrará: {$activeCount}");
    }
}
