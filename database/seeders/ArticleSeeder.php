<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\User;
use App\Models\Category;
use Carbon\Carbon;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener usuarios y categorías existentes
        $users = User::all();
        $categories = Category::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('No hay usuarios en la base de datos. Creando artículos sin autor asignado.');
            $users = collect([null]);
        }

        if ($categories->isEmpty()) {
            $this->command->warn('No hay categorías en la base de datos. Creando artículos sin categoría asignada.');
            $categories = collect([null]);
        }

        // Datos de artículos variados
        $articles = [
            // Artículos publicados
            [
                'title' => 'El Futuro de la Energía Renovable en España',
                'subtitle' => 'Análisis completo del panorama energético español y las oportunidades de crecimiento',
                'text' => 'España se encuentra en una posición privilegiada para liderar la transición energética en Europa. Con más de 2.500 horas de sol al año en muchas regiones y un potencial eólico considerable, nuestro país tiene todas las condiciones para convertirse en un referente mundial en energías renovables.',
                'excerpt' => 'España tiene un potencial excepcional para liderar la transición energética en Europa, con oportunidades en solar, eólica y otras tecnologías renovables.',
                'slug' => 'futuro-energia-renovable-espana',
                'author_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'category_id' => $categories->isNotEmpty() ? $categories->random()->id : null,
                'published_at' => Carbon::now()->subDays(5),
                'scheduled_at' => null,
                'comment_enabled' => true,
                'featured' => true,
                'status' => 'published',
                'reading_time' => 8,
                'seo_focus_keyword' => 'energía renovable España',
                'related_articles' => null,
                'social_shares_count' => 45,
                'number_of_views' => 1250,
                'language' => 'es',
                'is_draft' => false,
            ],
            [
                'title' => 'Cómo Reducir tu Factura Eléctrica con Paneles Solares',
                'subtitle' => 'Guía práctica para instalar energía solar en tu hogar y ahorrar dinero',
                'text' => 'La instalación de paneles solares en el hogar se ha convertido en una de las inversiones más rentables para las familias españolas. Con los precios actuales de la electricidad y las ayudas disponibles, el retorno de la inversión se puede conseguir en menos de 8 años.',
                'excerpt' => 'Descubre cómo los paneles solares pueden reducir tu factura eléctrica hasta un 70% y generar ingresos adicionales.',
                'slug' => 'reducir-factura-electrica-paneles-solares',
                'author_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'category_id' => $categories->isNotEmpty() ? $categories->random()->id : null,
                'published_at' => Carbon::now()->subDays(3),
                'scheduled_at' => null,
                'comment_enabled' => true,
                'featured' => false,
                'status' => 'published',
                'reading_time' => 6,
                'seo_focus_keyword' => 'paneles solares hogar',
                'related_articles' => null,
                'social_shares_count' => 23,
                'number_of_views' => 890,
                'language' => 'es',
                'is_draft' => false,
            ],
            [
                'title' => 'La Revolución de las Comunidades Energéticas',
                'subtitle' => 'Cómo las cooperativas energéticas están transformando el sector',
                'text' => 'Las comunidades energéticas representan una revolución silenciosa en el sector energético español. Estas iniciativas ciudadanas permiten a los vecinos unirse para generar, gestionar y consumir su propia energía renovable de forma colectiva.',
                'excerpt' => 'Las comunidades energéticas permiten a los ciudadanos participar activamente en la transición energética y beneficiarse de energías renovables.',
                'slug' => 'revolucion-comunidades-energeticas',
                'author_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'category_id' => $categories->isNotEmpty() ? $categories->random()->id : null,
                'published_at' => Carbon::now()->subDays(1),
                'scheduled_at' => null,
                'comment_enabled' => true,
                'featured' => true,
                'status' => 'published',
                'reading_time' => 7,
                'seo_focus_keyword' => 'comunidades energéticas',
                'related_articles' => null,
                'social_shares_count' => 67,
                'number_of_views' => 2100,
                'language' => 'es',
                'is_draft' => false,
            ],

            // Artículos en borrador
            [
                'title' => 'Tecnologías Emergentes en Almacenamiento de Energía',
                'subtitle' => 'Baterías de nueva generación y sistemas de almacenamiento innovadores',
                'text' => 'El almacenamiento de energía es uno de los desafíos más importantes de la transición energética. Las tecnologías emergentes en este campo están revolucionando la forma en que almacenamos y utilizamos la energía renovable.',
                'excerpt' => 'Las nuevas tecnologías de almacenamiento están revolucionando el sector energético con soluciones más eficientes y duraderas.',
                'slug' => 'tecnologias-emergentes-almacenamiento-energia',
                'author_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'category_id' => $categories->isNotEmpty() ? $categories->random()->id : null,
                'published_at' => null,
                'scheduled_at' => Carbon::now()->addDays(7),
                'comment_enabled' => true,
                'featured' => false,
                'status' => 'draft',
                'reading_time' => 9,
                'seo_focus_keyword' => 'almacenamiento energía',
                'related_articles' => null,
                'social_shares_count' => 0,
                'number_of_views' => 0,
                'language' => 'es',
                'is_draft' => true,
            ],
            [
                'title' => 'Impacto Ambiental de las Energías Renovables',
                'subtitle' => 'Análisis del ciclo de vida y sostenibilidad de las tecnologías verdes',
                'text' => 'Aunque las energías renovables son consideradas limpias, es importante analizar su impacto ambiental completo, incluyendo la fabricación, instalación y desmantelamiento de los equipos.',
                'excerpt' => 'Un análisis completo del impacto ambiental de las energías renovables, desde la fabricación hasta el reciclaje.',
                'slug' => 'impacto-ambiental-energias-renovables',
                'author_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'category_id' => $categories->isNotEmpty() ? $categories->random()->id : null,
                'published_at' => null,
                'scheduled_at' => null,
                'comment_enabled' => true,
                'featured' => false,
                'status' => 'draft',
                'reading_time' => 10,
                'seo_focus_keyword' => 'impacto ambiental renovables',
                'related_articles' => null,
                'social_shares_count' => 0,
                'number_of_views' => 0,
                'language' => 'es',
                'is_draft' => true,
            ],

            // Artículos programados
            [
                'title' => 'Tendencias en Energía Eólica Marina para 2024',
                'subtitle' => 'Los avances más importantes en tecnología eólica offshore',
                'text' => 'La energía eólica marina está experimentando un crecimiento sin precedentes en 2024, con avances tecnológicos que están reduciendo los costes y aumentando la eficiencia de los aerogeneradores offshore.',
                'excerpt' => 'Descubre las últimas tendencias en energía eólica marina y cómo están transformando el sector energético.',
                'slug' => 'tendencias-energia-eolica-marina-2024',
                'author_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'category_id' => $categories->isNotEmpty() ? $categories->random()->id : null,
                'published_at' => null,
                'scheduled_at' => Carbon::now()->addDays(3),
                'comment_enabled' => true,
                'featured' => true,
                'status' => 'review',
                'reading_time' => 8,
                'seo_focus_keyword' => 'energía eólica marina',
                'related_articles' => null,
                'social_shares_count' => 0,
                'number_of_views' => 0,
                'language' => 'es',
                'is_draft' => false,
            ],
            [
                'title' => 'Guía Completa de Subvenciones para Energías Renovables',
                'subtitle' => 'Todas las ayudas disponibles para instalar energías limpias en tu hogar',
                'text' => 'Las subvenciones y ayudas para energías renovables han experimentado un aumento significativo en los últimos años, facilitando el acceso a estas tecnologías para los ciudadanos.',
                'excerpt' => 'Una guía completa de todas las subvenciones y ayudas disponibles para instalar energías renovables en tu hogar.',
                'slug' => 'guia-subvenciones-energias-renovables',
                'author_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'category_id' => $categories->isNotEmpty() ? $categories->random()->id : null,
                'published_at' => null,
                'scheduled_at' => Carbon::now()->addDays(5),
                'comment_enabled' => true,
                'featured' => false,
                'status' => 'review',
                'reading_time' => 12,
                'seo_focus_keyword' => 'subvenciones energías renovables',
                'related_articles' => null,
                'social_shares_count' => 0,
                'number_of_views' => 0,
                'language' => 'es',
                'is_draft' => false,
            ],
        ];

        $this->command->info('Creando artículos...');

        $createdCount = 0;
        $totalArticles = count($articles);

        foreach ($articles as $index => $articleData) {
            // Crear el artículo
            Article::create($articleData);
            $createdCount++;

            // Mostrar progreso cada 2 artículos
            if (($index + 1) % 2 === 0) {
                $this->command->info("Progreso: {$createdCount}/{$totalArticles} artículos creados");
            }
        }

        $this->command->info("✅ Se han creado {$createdCount} artículos exitosamente");

        // Mostrar estadísticas
        $this->showStatistics();
    }

    /**
     * Mostrar estadísticas de los artículos creados
     */
    private function showStatistics(): void
    {
        $this->command->info("\n📊 Estadísticas de Artículos:");
        
        $total = Article::count();
        $published = Article::published()->count();
        $draft = Article::where('is_draft', true)->count();
        $review = Article::where('status', 'review')->count();
        $featured = Article::where('featured', true)->count();

        $this->command->info("• Total: {$total}");
        $this->command->info("• Publicados: {$published}");
        $this->command->info("• Borradores: {$draft}");
        $this->command->info("• En revisión: {$review}");
        $this->command->info("• Destacados: {$featured}");

        // Estadísticas por estado
        $this->command->info("\n📈 Por estado:");
        $statuses = Article::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        foreach ($statuses as $status => $count) {
            $this->command->info("• {$status}: {$count}");
        }

        // Estadísticas por autor
        $authorsWithArticles = Article::selectRaw('author_id, COUNT(*) as count')
            ->whereNotNull('author_id')
            ->groupBy('author_id')
            ->get();

        if ($authorsWithArticles->isNotEmpty()) {
            $this->command->info("\n👥 Artículos por autor:");
            foreach ($authorsWithArticles as $authorArticle) {
                $user = User::find($authorArticle->author_id);
                $userName = $user ? $user->name : "Usuario ID {$authorArticle->author_id}";
                $this->command->info("• {$userName}: {$authorArticle->count} artículos");
            }
        }
    }
}
