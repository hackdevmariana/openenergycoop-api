<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\User;
use Carbon\Carbon;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener usuarios existentes
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('No hay usuarios en la base de datos. Creando menús sin usuario asignado.');
            $users = collect([null]);
        }

        // Datos de elementos de menú variados
        $menuItems = [
            // Menús activos principales
            [
                'icon' => 'heroicon-o-home',
                'text' => 'Inicio',
                'internal_link' => '/',
                'external_link' => null,
                'target_blank' => false,
                'parent_id' => null,
                'order' => 1,
                'permission' => null,
                'menu_group' => 'main',
                'css_classes' => 'nav-link',
                'visibility_rules' => null,
                'badge_text' => null,
                'badge_color' => null,
                'language' => 'es',
                'is_draft' => false,
                'is_active' => true,
                'published_at' => Carbon::now()->subDays(30),
                'created_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'updated_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
            ],
            [
                'icon' => 'heroicon-o-lightning-bolt',
                'text' => 'Energía',
                'internal_link' => '/energia',
                'external_link' => null,
                'target_blank' => false,
                'parent_id' => null,
                'order' => 2,
                'permission' => null,
                'menu_group' => 'main',
                'css_classes' => 'nav-link',
                'visibility_rules' => null,
                'badge_text' => null,
                'badge_color' => null,
                'language' => 'es',
                'is_draft' => false,
                'is_active' => true,
                'published_at' => Carbon::now()->subDays(25),
                'created_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'updated_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
            ],
            [
                'icon' => 'heroicon-o-users',
                'text' => 'Comunidad',
                'internal_link' => '/comunidad',
                'external_link' => null,
                'target_blank' => false,
                'parent_id' => null,
                'order' => 3,
                'permission' => null,
                'menu_group' => 'main',
                'css_classes' => 'nav-link',
                'visibility_rules' => null,
                'badge_text' => null,
                'badge_color' => null,
                'language' => 'es',
                'is_draft' => false,
                'is_active' => true,
                'published_at' => Carbon::now()->subDays(20),
                'created_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'updated_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
            ],
            [
                'icon' => 'heroicon-o-document-text',
                'text' => 'Documentos',
                'internal_link' => '/documentos',
                'external_link' => null,
                'target_blank' => false,
                'parent_id' => null,
                'order' => 4,
                'permission' => null,
                'menu_group' => 'main',
                'css_classes' => 'nav-link',
                'visibility_rules' => null,
                'badge_text' => null,
                'badge_color' => null,
                'language' => 'es',
                'is_draft' => false,
                'is_active' => true,
                'published_at' => Carbon::now()->subDays(15),
                'created_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'updated_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
            ],
            [
                'icon' => 'heroicon-o-question-mark-circle',
                'text' => 'FAQ',
                'internal_link' => '/faq',
                'external_link' => null,
                'target_blank' => false,
                'parent_id' => null,
                'order' => 5,
                'permission' => null,
                'menu_group' => 'main',
                'css_classes' => 'nav-link',
                'visibility_rules' => null,
                'badge_text' => null,
                'badge_color' => null,
                'language' => 'es',
                'is_draft' => false,
                'is_active' => true,
                'published_at' => Carbon::now()->subDays(10),
                'created_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'updated_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
            ],

            // Submenús activos
            [
                'icon' => 'heroicon-o-sun',
                'text' => 'Energía Solar',
                'internal_link' => '/energia/solar',
                'external_link' => null,
                'target_blank' => false,
                'parent_id' => null, // Se establecerá después
                'order' => 1,
                'permission' => null,
                'menu_group' => 'main',
                'css_classes' => 'nav-link submenu',
                'visibility_rules' => null,
                'badge_text' => null,
                'badge_color' => null,
                'language' => 'es',
                'is_draft' => false,
                'is_active' => true,
                'published_at' => Carbon::now()->subDays(22),
                'created_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'updated_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
            ],
            [
                'icon' => 'heroicon-o-wind',
                'text' => 'Energía Eólica',
                'internal_link' => '/energia/eolica',
                'external_link' => null,
                'target_blank' => false,
                'parent_id' => null, // Se establecerá después
                'order' => 2,
                'permission' => null,
                'menu_group' => 'main',
                'css_classes' => 'nav-link submenu',
                'visibility_rules' => null,
                'badge_text' => null,
                'badge_color' => null,
                'language' => 'es',
                'is_draft' => false,
                'is_active' => true,
                'published_at' => Carbon::now()->subDays(18),
                'created_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'updated_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
            ],

            // Menús inactivos
            [
                'icon' => 'heroicon-o-cog-6-tooth',
                'text' => 'Configuración',
                'internal_link' => '/configuracion',
                'external_link' => null,
                'target_blank' => false,
                'parent_id' => null,
                'order' => 6,
                'permission' => 'admin',
                'menu_group' => 'admin',
                'css_classes' => 'nav-link admin',
                'visibility_rules' => null,
                'badge_text' => null,
                'badge_color' => null,
                'language' => 'es',
                'is_draft' => false,
                'is_active' => false,
                'published_at' => Carbon::now()->subDays(5),
                'created_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'updated_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
            ],
            [
                'icon' => 'heroicon-o-chart-bar',
                'text' => 'Estadísticas',
                'internal_link' => '/estadisticas',
                'external_link' => null,
                'target_blank' => false,
                'parent_id' => null,
                'order' => 7,
                'permission' => 'member',
                'menu_group' => 'member',
                'css_classes' => 'nav-link member',
                'visibility_rules' => null,
                'badge_text' => null,
                'badge_color' => null,
                'language' => 'es',
                'is_draft' => true,
                'is_active' => false,
                'published_at' => null,
                'created_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'updated_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
            ],

            // Menús con badges
            [
                'icon' => 'heroicon-o-bell',
                'text' => 'Notificaciones',
                'internal_link' => '/notificaciones',
                'external_link' => null,
                'target_blank' => false,
                'parent_id' => null,
                'order' => 8,
                'permission' => 'member',
                'menu_group' => 'member',
                'css_classes' => 'nav-link notifications',
                'visibility_rules' => null,
                'badge_text' => '3',
                'badge_color' => 'red',
                'language' => 'es',
                'is_draft' => false,
                'is_active' => true,
                'published_at' => Carbon::now()->subDays(3),
                'created_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'updated_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
            ],
            [
                'icon' => 'heroicon-o-exclamation-triangle',
                'text' => 'Alertas',
                'internal_link' => '/alertas',
                'external_link' => null,
                'target_blank' => false,
                'parent_id' => null,
                'order' => 9,
                'permission' => 'member',
                'menu_group' => 'member',
                'css_classes' => 'nav-link alerts',
                'visibility_rules' => null,
                'badge_text' => 'Nuevo',
                'badge_color' => 'green',
                'language' => 'es',
                'is_draft' => false,
                'is_active' => true,
                'published_at' => Carbon::now()->subDays(1),
                'created_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'updated_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
            ],

            // Enlaces externos
            [
                'icon' => 'heroicon-o-globe-alt',
                'text' => 'Sitio Web',
                'internal_link' => null,
                'external_link' => 'https://www.openenergycoop.com',
                'target_blank' => true,
                'parent_id' => null,
                'order' => 10,
                'permission' => null,
                'menu_group' => 'footer',
                'css_classes' => 'nav-link external',
                'visibility_rules' => null,
                'badge_text' => null,
                'badge_color' => null,
                'language' => 'es',
                'is_draft' => false,
                'is_active' => true,
                'published_at' => Carbon::now()->subDays(12),
                'created_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'updated_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
            ],
        ];

        $this->command->info('Creando elementos de menú...');

        $createdCount = 0;
        $totalMenuItems = count($menuItems);

        foreach ($menuItems as $index => $menuData) {
            // Crear el elemento de menú
            $menu = Menu::create($menuData);
            $createdCount++;

            // Mostrar progreso cada 3 elementos
            if (($index + 1) % 3 === 0) {
                $this->command->info("Progreso: {$createdCount}/{$totalMenuItems} elementos de menú creados");
            }
        }

        // Establecer relaciones padre-hijo para submenús
        $this->establishParentChildRelationships();

        $this->command->info("✅ Se han creado {$createdCount} elementos de menú exitosamente");

        // Mostrar estadísticas
        $this->showStatistics();
    }

    /**
     * Establecer relaciones padre-hijo para submenús
     */
    private function establishParentChildRelationships(): void
    {
        // Buscar el menú "Energía" para establecerlo como padre
        $energiaMenu = Menu::where('text', 'Energía')->first();
        
        if ($energiaMenu) {
            // Establecer "Energía Solar" como hijo de "Energía"
            $solarMenu = Menu::where('text', 'Energía Solar')->first();
            if ($solarMenu) {
                $solarMenu->update(['parent_id' => $energiaMenu->id]);
            }

            // Establecer "Energía Eólica" como hijo de "Energía"
            $eolicaMenu = Menu::where('text', 'Energía Eólica')->first();
            if ($eolicaMenu) {
                $eolicaMenu->update(['parent_id' => $energiaMenu->id]);
            }
        }
    }

    /**
     * Mostrar estadísticas de los elementos de menú creados
     */
    private function showStatistics(): void
    {
        $this->command->info("\n📊 Estadísticas de Elementos de Menú:");
        
        $total = Menu::count();
        $active = Menu::where('is_active', true)->count();
        $inactive = Menu::where('is_active', false)->count();
        $published = Menu::published()->count();
        $draft = Menu::where('is_draft', true)->count();
        $withBadges = Menu::whereNotNull('badge_text')->count();
        $externalLinks = Menu::whereNotNull('external_link')->count();

        $this->command->info("• Total: {$total}");
        $this->command->info("• Activos: {$active}");
        $this->command->info("• Inactivos: {$inactive}");
        $this->command->info("• Publicados: {$published}");
        $this->command->info("• Borradores: {$draft}");
        $this->command->info("• Con badges: {$withBadges}");
        $this->command->info("• Enlaces externos: {$externalLinks}");

        // Estadísticas por grupo de menú
        $this->command->info("\n📈 Por grupo de menú:");
        $menuGroups = Menu::selectRaw('menu_group, COUNT(*) as count')
            ->groupBy('menu_group')
            ->pluck('count', 'menu_group')
            ->toArray();

        foreach ($menuGroups as $group => $count) {
            $this->command->info("• {$group}: {$count}");
        }

        // Estadísticas por usuario
        $usersWithMenus = Menu::selectRaw('created_by_user_id, COUNT(*) as count')
            ->whereNotNull('created_by_user_id')
            ->groupBy('created_by_user_id')
            ->get();

        if ($usersWithMenus->isNotEmpty()) {
            $this->command->info("\n👥 Elementos de menú por usuario:");
            foreach ($usersWithMenus as $userMenu) {
                $user = User::find($userMenu->created_by_user_id);
                $userName = $user ? $user->name : "Usuario ID {$userMenu->created_by_user_id}";
                $this->command->info("• {$userName}: {$userMenu->count} elementos");
            }
        }
    }
}
