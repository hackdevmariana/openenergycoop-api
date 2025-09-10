<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TextContent;
use App\Models\User;

class TextContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener un usuario para asignar como autor
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

        $textContents = [
            [
                'slug' => 'bienvenida-cooperativa',
                'title' => 'Bienvenidos a Nuestra Cooperativa Energética',
                'subtitle' => 'Un futuro sostenible comienza aquí',
                'text' => '<h2>¿Qué es una cooperativa energética?</h2><p>Una cooperativa energética es una organización democrática donde los miembros se unen para producir, gestionar y distribuir energía de manera sostenible y justa.</p><h3>Nuestros valores:</h3><ul><li>Sostenibilidad ambiental</li><li>Democracia participativa</li><li>Justicia social</li><li>Transparencia</li></ul>',
                'excerpt' => 'Descubre cómo funciona nuestra cooperativa energética y los beneficios de formar parte de una comunidad sostenible.',
                'is_draft' => false,
                'published_at' => now(),
                'author_id' => $user->id,
                'language' => 'es',
                'version' => '1.0',
                'reading_time' => 3,
                'seo_focus_keyword' => 'cooperativa energética',
                'search_keywords' => ['energía', 'sostenible', 'cooperativa', 'renovable'],
                'reading_level' => 'intermediate',
            ],
            [
                'slug' => 'como-unirse',
                'title' => 'Cómo Unirse a la Cooperativa',
                'subtitle' => 'Pasos para formar parte de nuestra comunidad',
                'text' => '<h2>Proceso de adhesión</h2><p>Unirse a nuestra cooperativa es sencillo y transparente. Te guiamos paso a paso:</p><ol><li><strong>Información inicial:</strong> Consulta nuestros estatutos y reglamentos</li><li><strong>Solicitud:</strong> Completa el formulario de adhesión</li><li><strong>Revisión:</strong> Nuestro equipo revisará tu solicitud</li><li><strong>Aprobación:</strong> Votación en asamblea general</li><li><strong>Integración:</strong> Bienvenido a la familia cooperativa</li></ol>',
                'excerpt' => 'Guía completa para unirse a nuestra cooperativa energética. Proceso transparente y democrático.',
                'is_draft' => false,
                'published_at' => now()->subDays(2),
                'author_id' => $user->id,
                'language' => 'es',
                'version' => '1.1',
                'reading_time' => 5,
                'seo_focus_keyword' => 'unirse cooperativa',
                'search_keywords' => ['adhesión', 'socio', 'cooperativa', 'proceso'],
                'reading_level' => 'basic',
            ],
            [
                'slug' => 'beneficios-socio',
                'title' => 'Beneficios de Ser Socio',
                'subtitle' => 'Ventajas económicas y ambientales',
                'text' => '<h2>Ventajas económicas</h2><p>Como socio de nuestra cooperativa, disfrutas de:</p><ul><li>Tarifas preferenciales en energía</li><li>Participación en beneficios</li><li>Inversión en proyectos sostenibles</li><li>Reducción de costes energéticos</li></ul><h2>Impacto ambiental</h2><p>Contribuyes directamente a:</p><ul><li>Reducción de emisiones CO₂</li><li>Promoción de energías renovables</li><li>Transición energética justa</li><li>Sostenibilidad local</li></ul>',
                'excerpt' => 'Descubre todos los beneficios económicos y ambientales de ser socio de nuestra cooperativa energética.',
                'is_draft' => false,
                'published_at' => now()->subDays(5),
                'author_id' => $user->id,
                'language' => 'es',
                'version' => '1.0',
                'reading_time' => 4,
                'seo_focus_keyword' => 'beneficios socio cooperativa',
                'search_keywords' => ['beneficios', 'socio', 'ahorro', 'sostenible'],
                'reading_level' => 'intermediate',
            ],
            [
                'slug' => 'tecnologia-plataforma',
                'title' => 'Tecnología de Nuestra Plataforma',
                'subtitle' => 'Innovación al servicio de la comunidad',
                'text' => '<h2>Sistema de gestión inteligente</h2><p>Nuestra plataforma utiliza tecnología de vanguardia para:</p><ul><li>Monitoreo en tiempo real del consumo</li><li>Optimización automática de la distribución</li><li>Predicción de demanda energética</li><li>Gestión de excedentes</li></ul><h2>Seguridad y privacidad</h2><p>Garantizamos la protección de tus datos con:</p><ul><li>Encriptación de extremo a extremo</li><li>Cumplimiento RGPD</li><li>Auditorías de seguridad regulares</li><li>Transparencia en el uso de datos</li></ul>',
                'excerpt' => 'Conoce la tecnología avanzada que hace posible nuestra plataforma de gestión energética cooperativa.',
                'is_draft' => false,
                'published_at' => now()->subDays(7),
                'author_id' => $user->id,
                'language' => 'es',
                'version' => '2.0',
                'reading_time' => 6,
                'seo_focus_keyword' => 'tecnología cooperativa energética',
                'search_keywords' => ['tecnología', 'plataforma', 'smart grid', 'IoT'],
                'reading_level' => 'advanced',
            ],
            [
                'slug' => 'preguntas-frecuentes',
                'title' => 'Preguntas Frecuentes',
                'subtitle' => 'Respuestas a las dudas más comunes',
                'text' => '<h2>Preguntas sobre la cooperativa</h2><h3>¿Cuánto cuesta unirse?</h3><p>La cuota de socio es de 100€ anuales, que se destinan a la gestión y desarrollo de proyectos.</p><h3>¿Puedo salirme cuando quiera?</h3><p>Sí, puedes solicitar la baja en cualquier momento siguiendo el procedimiento establecido en nuestros estatutos.</p><h3>¿Cómo se toman las decisiones?</h3><p>Todas las decisiones importantes se toman en asamblea general, donde cada socio tiene un voto.</p>',
                'excerpt' => 'Encuentra respuestas a las preguntas más frecuentes sobre nuestra cooperativa energética.',
                'is_draft' => false,
                'published_at' => now()->subDays(10),
                'author_id' => $user->id,
                'language' => 'es',
                'version' => '1.2',
                'reading_time' => 8,
                'seo_focus_keyword' => 'preguntas frecuentes cooperativa',
                'search_keywords' => ['FAQ', 'preguntas', 'dudas', 'ayuda'],
                'reading_level' => 'basic',
            ],
            [
                'slug' => 'proyectos-futuros',
                'title' => 'Proyectos Futuros',
                'subtitle' => 'Nuestra visión para el mañana',
                'text' => '<h2>Expansión de la red</h2><p>Planeamos expandir nuestra red de generación con:</p><ul><li>Nuevas instalaciones solares comunitarias</li><li>Parques eólicos cooperativos</li><li>Sistemas de almacenamiento inteligente</li><li>Redes de distribución local</li></ul><h2>Innovación tecnológica</h2><p>Estamos trabajando en:</p><ul><li>Blockchain para transacciones energéticas</li><li>IA para optimización de la red</li><li>Vehículos eléctricos compartidos</li><li>Edificios de energía positiva</li></ul>',
                'excerpt' => 'Descubre los emocionantes proyectos que tenemos planeados para el futuro de nuestra cooperativa.',
                'is_draft' => true, // Este está en borrador
                'published_at' => null,
                'author_id' => $user->id,
                'language' => 'es',
                'version' => '0.9',
                'reading_time' => 7,
                'seo_focus_keyword' => 'proyectos futuros cooperativa',
                'search_keywords' => ['futuro', 'proyectos', 'innovación', 'expansión'],
                'reading_level' => 'intermediate',
                'internal_notes' => 'Revisar con el equipo técnico antes de publicar',
            ],
            [
                'slug' => 'sostenibilidad-ambiental',
                'title' => 'Compromiso con la Sostenibilidad',
                'subtitle' => 'Cuidando el planeta para las futuras generaciones',
                'text' => '<h2>Nuestro impacto ambiental</h2><p>Desde nuestra fundación, hemos logrado:</p><ul><li>Reducción del 40% en emisiones CO₂</li><li>Generación de 2.5 GWh de energía limpia</li><li>Evitado 1.2 toneladas de emisiones</li><li>Plantado más de 500 árboles</li></ul><h2>Objetivos 2030</h2><p>Nos comprometemos a:</p><ul><li>Ser 100% renovables para 2030</li><li>Reducir emisiones en un 60%</li><li>Crear 50 nuevos empleos verdes</li><li>Educar a 1000 familias en eficiencia</li></ul>',
                'excerpt' => 'Conoce nuestro compromiso con la sostenibilidad ambiental y los logros alcanzados.',
                'is_draft' => false,
                'published_at' => now()->subDays(15),
                'author_id' => $user->id,
                'language' => 'es',
                'version' => '1.0',
                'reading_time' => 5,
                'seo_focus_keyword' => 'sostenibilidad ambiental cooperativa',
                'search_keywords' => ['sostenibilidad', 'medio ambiente', 'CO2', 'renovable'],
                'reading_level' => 'intermediate',
            ],
        ];

        foreach ($textContents as $content) {
            TextContent::create($content);
        }

        $this->command->info('TextContentSeeder ejecutado exitosamente. Se crearon ' . count($textContents) . ' contenidos de texto.');
    }
}
