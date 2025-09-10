<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PageComponent;
use App\Models\Page;
use App\Models\Hero;
use App\Models\TextContent;
use App\Models\Banner;
use App\Models\User;
use App\Models\Organization;

class PageComponentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener o crear un usuario
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

        // Obtener o crear una organización
        $organization = Organization::first();
        if (!$organization) {
            $organization = Organization::create([
                'name' => 'Open Energy Coop',
                'slug' => 'open-energy-coop',
                'description' => 'Organización principal',
                'is_active' => true,
            ]);
        }

        // Obtener o crear páginas
        $homePage = Page::firstOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Página Principal',
                'route' => '/',
                'language' => 'es',
                'organization_id' => $organization->id,
                'is_draft' => false,
                'template' => 'home',
                'published_at' => now(),
                'created_by_user_id' => $user->id,
            ]
        );

        $aboutPage = Page::firstOrCreate(
            ['slug' => 'about'],
            [
                'title' => 'Acerca de Nosotros',
                'route' => '/about',
                'language' => 'es',
                'organization_id' => $organization->id,
                'is_draft' => false,
                'template' => 'about',
                'published_at' => now(),
                'created_by_user_id' => $user->id,
            ]
        );

        $servicesPage = Page::firstOrCreate(
            ['slug' => 'services'],
            [
                'title' => 'Servicios',
                'route' => '/services',
                'language' => 'es',
                'organization_id' => $organization->id,
                'is_draft' => false,
                'template' => 'services',
                'published_at' => now(),
                'created_by_user_id' => $user->id,
            ]
        );

        // Obtener componentes existentes para asociar
        $heroes = Hero::where('active', true)->take(3)->get();
        $textContents = TextContent::where('is_draft', false)->take(4)->get();
        $banners = Banner::where('active', true)->take(2)->get();

        $pageComponents = [
            // Página Principal
            [
                'page_id' => $homePage->id,
                'componentable_type' => 'App\\Models\\Hero',
                'componentable_id' => $heroes->first()?->id ?? 1,
                'position' => 1,
                'language' => 'es',
                'organization_id' => $organization->id,
                'is_draft' => false,
                'version' => '1.0',
                'published_at' => now(),
                'cache_enabled' => true,
                'settings' => [
                    'show_title' => true,
                    'show_subtitle' => true,
                    'animation' => 'fadeIn',
                ],
            ],
            [
                'page_id' => $homePage->id,
                'componentable_type' => 'App\\Models\\TextContent',
                'componentable_id' => $textContents->first()?->id ?? 1,
                'position' => 2,
                'language' => 'es',
                'organization_id' => $organization->id,
                'is_draft' => false,
                'version' => '1.0',
                'published_at' => now(),
                'cache_enabled' => true,
                'settings' => [
                    'show_author' => false,
                    'show_date' => true,
                    'max_width' => '800px',
                ],
            ],
            [
                'page_id' => $homePage->id,
                'componentable_type' => 'App\\Models\\Banner',
                'componentable_id' => $banners->first()?->id ?? 1,
                'position' => 3,
                'language' => 'es',
                'organization_id' => $organization->id,
                'is_draft' => false,
                'version' => '1.0',
                'published_at' => now(),
                'cache_enabled' => true,
                'settings' => [
                    'show_close_button' => true,
                    'auto_close' => 5000,
                ],
            ],

            // Página Acerca de
            [
                'page_id' => $aboutPage->id,
                'componentable_type' => 'App\\Models\\TextContent',
                'componentable_id' => $textContents->skip(1)->first()?->id ?? 2,
                'position' => 1,
                'language' => 'es',
                'organization_id' => $organization->id,
                'is_draft' => false,
                'version' => '1.0',
                'published_at' => now(),
                'cache_enabled' => true,
                'settings' => [
                    'show_author' => true,
                    'show_date' => false,
                    'max_width' => '1000px',
                ],
            ],
            [
                'page_id' => $aboutPage->id,
                'componentable_type' => 'App\\Models\\Hero',
                'componentable_id' => $heroes->skip(1)->first()?->id ?? 2,
                'position' => 2,
                'language' => 'es',
                'organization_id' => $organization->id,
                'is_draft' => false,
                'version' => '1.0',
                'published_at' => now(),
                'cache_enabled' => true,
                'settings' => [
                    'show_title' => false,
                    'show_subtitle' => true,
                    'animation' => 'slideIn',
                ],
            ],

            // Página Servicios
            [
                'page_id' => $servicesPage->id,
                'componentable_type' => 'App\\Models\\Hero',
                'componentable_id' => $heroes->skip(2)->first()?->id ?? 3,
                'position' => 1,
                'language' => 'es',
                'organization_id' => $organization->id,
                'is_draft' => false,
                'version' => '1.0',
                'published_at' => now(),
                'cache_enabled' => true,
                'settings' => [
                    'show_title' => true,
                    'show_subtitle' => true,
                    'animation' => 'zoomIn',
                ],
            ],
            [
                'page_id' => $servicesPage->id,
                'componentable_type' => 'App\\Models\\TextContent',
                'componentable_id' => $textContents->skip(2)->first()?->id ?? 3,
                'position' => 2,
                'language' => 'es',
                'organization_id' => $organization->id,
                'is_draft' => false,
                'version' => '1.0',
                'published_at' => now(),
                'cache_enabled' => true,
                'settings' => [
                    'show_author' => false,
                    'show_date' => true,
                    'max_width' => '900px',
                ],
            ],
            [
                'page_id' => $servicesPage->id,
                'componentable_type' => 'App\\Models\\Banner',
                'componentable_id' => $banners->skip(1)->first()?->id ?? 2,
                'position' => 3,
                'language' => 'es',
                'organization_id' => $organization->id,
                'is_draft' => false,
                'version' => '1.0',
                'published_at' => now(),
                'cache_enabled' => true,
                'settings' => [
                    'show_close_button' => false,
                    'auto_close' => 0,
                ],
            ],

            // Componentes en borrador
            [
                'page_id' => $homePage->id,
                'componentable_type' => 'App\\Models\\TextContent',
                'componentable_id' => $textContents->skip(3)->first()?->id ?? 4,
                'position' => 4,
                'language' => 'es',
                'organization_id' => $organization->id,
                'is_draft' => true,
                'version' => '1.0',
                'published_at' => null,
                'cache_enabled' => false,
                'settings' => [
                    'show_author' => true,
                    'show_date' => true,
                    'max_width' => '600px',
                ],
            ],

            // Componente con reglas de visibilidad
            [
                'page_id' => $aboutPage->id,
                'componentable_type' => 'App\\Models\\TextContent',
                'componentable_id' => $textContents->first()?->id ?? 1,
                'position' => 3,
                'language' => 'es',
                'organization_id' => $organization->id,
                'is_draft' => false,
                'version' => '1.0',
                'published_at' => now(),
                'cache_enabled' => true,
                'visibility_rules' => [
                    [
                        'type' => 'auth_required',
                        'value' => true,
                    ],
                ],
                'settings' => [
                    'show_author' => false,
                    'show_date' => false,
                    'max_width' => '800px',
                ],
            ],

            // Componente con test A/B
            [
                'page_id' => $servicesPage->id,
                'componentable_type' => 'App\\Models\\Banner',
                'componentable_id' => $banners->first()?->id ?? 1,
                'position' => 4,
                'language' => 'es',
                'organization_id' => $organization->id,
                'is_draft' => false,
                'version' => '1.0',
                'published_at' => now(),
                'cache_enabled' => true,
                'ab_test_group' => 'variant_a',
                'settings' => [
                    'show_close_button' => true,
                    'auto_close' => 3000,
                ],
            ],
        ];

        foreach ($pageComponents as $component) {
            PageComponent::create($component);
        }

        $this->command->info('✅ PageComponentSeeder ejecutado correctamente');
        $this->command->info('📊 Componentes creados: ' . count($pageComponents));
        $this->command->info('📄 Páginas utilizadas: 3 (home, about, services)');
        $this->command->info('🎯 Tipos de componentes: Hero, TextContent, Banner');
        $this->command->info('📝 Estados: Publicados y borradores');
        $this->command->info('🔧 Configuraciones: Diferentes settings para cada componente');
        $this->command->info('👁️ Reglas de visibilidad: Componente con autenticación requerida');
        $this->command->info('🧪 Test A/B: Componente con grupo variant_a');
    }
}