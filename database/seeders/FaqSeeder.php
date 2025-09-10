<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\FaqTopic;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener usuarios para las relaciones
        $users = User::take(3)->get();
        if ($users->isEmpty()) {
            $this->command->warn('No hay usuarios disponibles. Creando usuarios de ejemplo...');
            $users = collect([
                User::factory()->create(['name' => 'Admin FAQ', 'email' => 'admin-faq@openenergycoop.com']),
                User::factory()->create(['name' => 'Editor FAQ', 'email' => 'editor-faq@openenergycoop.com']),
            ]);
        }

        // Obtener organizaciones
        $organizations = Organization::take(2)->get();
        if ($organizations->isEmpty()) {
            $this->command->warn('No hay organizaciones disponibles. Creando organizaciones de ejemplo...');
            $organizations = collect([
                Organization::factory()->create(['name' => 'Cooperativa Energética Principal']),
                Organization::factory()->create(['name' => 'Cooperativa Solar Comunitaria']),
            ]);
        }

        // Crear temas de FAQ si no existen
        $topics = $this->createFaqTopics();

        $this->command->info('Creando FAQs de ejemplo...');

        // Crear FAQs sobre energía cooperativa
        $this->createEnergyCooperativeFaqs($users, $organizations, $topics);

        // Crear FAQs sobre transferencias de energía
        $this->createEnergyTransferFaqs($users, $organizations, $topics);

        // Crear FAQs sobre participación
        $this->createParticipationFaqs($users, $organizations, $topics);

        // Crear FAQs sobre tecnología
        $this->createTechnologyFaqs($users, $organizations, $topics);

        // Crear FAQs sobre facturación
        $this->createBillingFaqs($users, $organizations, $topics);

        $this->command->info('FAQs creadas exitosamente.');
    }

    private function createFaqTopics(): array
    {
        $topics = [
            'Energía Cooperativa' => 'Preguntas sobre el funcionamiento de las cooperativas energéticas',
            'Transferencias de Energía' => 'Cómo funcionan las transferencias entre cooperativistas',
            'Participación' => 'Cómo participar y ser miembro de la cooperativa',
            'Tecnología' => 'Aspectos técnicos y tecnológicos',
            'Facturación' => 'Preguntas sobre facturación y pagos',
            'Legal' => 'Aspectos legales y normativos',
            'Sostenibilidad' => 'Impacto ambiental y sostenibilidad',
        ];

        $createdTopics = [];
        foreach ($topics as $name => $description) {
            $topic = FaqTopic::firstOrCreate(
                ['name' => $name],
                [
                    'slug' => Str::slug($name),
                    'description' => $description,
                    'sort_order' => count($createdTopics) + 1,
                    'is_active' => true,
                    'language' => 'es',
                ]
            );
            $createdTopics[] = $topic;
        }

        return $createdTopics;
    }

    private function createEnergyCooperativeFaqs($users, $organizations, $topics): void
    {
        $topic = $topics[0]; // Energía Cooperativa
        $faqs = [
            [
                'question' => '¿Qué es una cooperativa energética?',
                'answer' => '<p>Una cooperativa energética es una organización sin ánimo de lucro formada por ciudadanos, empresas y organizaciones que se unen para producir, gestionar y consumir energía de forma colectiva y sostenible.</p><p>Las cooperativas energéticas permiten:</p><ul><li>Democratizar el acceso a la energía renovable</li><li>Reducir costes energéticos</li><li>Contribuir a la transición energética</li><li>Crear empleo local</li><li>Mantener los beneficios en la comunidad</li></ul>',
                'position' => 1,
                'is_featured' => true,
                'tags' => ['cooperativa', 'energía', 'renovable', 'comunidad'],
            ],
            [
                'question' => '¿Cómo puedo unirme a la cooperativa?',
                'answer' => '<p>Para unirte a nuestra cooperativa energética, sigue estos pasos:</p><ol><li><strong>Registro:</strong> Completa el formulario de registro en nuestra web</li><li><strong>Documentación:</strong> Envía la documentación requerida (DNI, justificante de domicilio)</li><li><strong>Cuota de entrada:</strong> Realiza el pago de la cuota de socio (desde 100€)</li><li><strong>Aprobación:</strong> La junta directiva revisa tu solicitud</li><li><strong>Bienvenida:</strong> Recibes tu certificado de socio y acceso a la plataforma</li></ol><p>El proceso completo suele tardar entre 7-15 días laborables.</p>',
                'position' => 2,
                'is_featured' => true,
                'tags' => ['socio', 'registro', 'proceso', 'cuota'],
            ],
            [
                'question' => '¿Qué beneficios obtengo como socio?',
                'answer' => '<p>Como socio de la cooperativa energética disfrutas de múltiples beneficios:</p><ul><li><strong>Ahorro económico:</strong> Hasta un 30% de descuento en tu factura energética</li><li><strong>Energía verde:</strong> Consumo 100% de energía renovable</li><li><strong>Participación:</strong> Voto en las decisiones de la cooperativa</li><li><strong>Transparencia:</strong> Acceso completo a la información financiera</li><li><strong>Servicios adicionales:</strong> Asesoramiento energético gratuito</li><li><strong>Retornos:</strong> Posibles dividendos según los resultados anuales</li></ul>',
                'position' => 3,
                'is_featured' => false,
                'tags' => ['beneficios', 'ahorro', 'energía verde', 'participación'],
            ],
        ];

        foreach ($faqs as $index => $faqData) {
            Faq::create(array_merge($faqData, [
                'topic_id' => $topic->id,
                'organization_id' => $organizations->random()->id,
                'language' => 'es',
                'is_draft' => false,
                'published_at' => now()->subDays(rand(1, 30)),
                'views_count' => rand(50, 500),
                'helpful_count' => rand(20, 100),
                'not_helpful_count' => rand(0, 10),
                'created_by_user_id' => $users->random()->id,
                'updated_by_user_id' => $users->random()->id,
            ]));
        }
    }

    private function createEnergyTransferFaqs($users, $organizations, $topics): void
    {
        $topic = $topics[1]; // Transferencias de Energía
        $faqs = [
            [
                'question' => '¿Cómo funcionan las transferencias de energía entre cooperativistas?',
                'answer' => '<p>Las transferencias de energía entre cooperativistas funcionan a través de nuestra plataforma digital que permite:</p><ul><li><strong>Generación:</strong> Los socios con instalaciones solares pueden vender su excedente</li><li><strong>Consumo:</strong> Otros socios pueden comprar energía directamente</li><li><strong>P2P:</strong> Transacciones peer-to-peer sin intermediarios</li><li><strong>Precios justos:</strong> Tarifas más competitivas que el mercado tradicional</li><li><strong>Trazabilidad:</strong> Seguimiento completo del origen de la energía</li></ul><p>El sistema funciona 24/7 y las transacciones se procesan automáticamente.</p>',
                'position' => 1,
                'is_featured' => true,
                'tags' => ['transferencia', 'P2P', 'energía', 'plataforma'],
            ],
            [
                'question' => '¿Cuánto puedo ganar vendiendo mi excedente solar?',
                'answer' => '<p>Los ingresos por venta de excedente solar dependen de varios factores:</p><ul><li><strong>Potencia instalada:</strong> Cuanto mayor sea tu instalación, más puedes vender</li><li><strong>Orientación y sombras:</strong> Afectan a la producción</li><li><strong>Consumo propio:</strong> El excedente es lo que no consumes</li><li><strong>Precio de venta:</strong> Actualmente entre 0.08-0.12€/kWh</li></ul><p><strong>Ejemplo:</strong> Una instalación de 5kWp puede generar unos 6.000 kWh/año. Si consumes 3.000 kWh, puedes vender 3.000 kWh por unos 300-360€ anuales.</p>',
                'position' => 2,
                'is_featured' => false,
                'tags' => ['excedente', 'solar', 'ingresos', 'rentabilidad'],
            ],
            [
                'question' => '¿Qué garantías tengo al comprar energía de otros socios?',
                'answer' => '<p>Al comprar energía de otros socios tienes las siguientes garantías:</p><ul><li><strong>Certificación:</strong> Todos los productores están certificados</li><li><strong>Seguro:</strong> Cobertura de hasta 10.000€ por incidencias</li><li><strong>Calidad:</strong> Energía 100% renovable verificada</li><li><strong>Disponibilidad:</strong> Sistema de respaldo en caso de fallos</li><li><strong>Transparencia:</strong> Información completa del origen</li><li><strong>Reclamaciones:</strong> Proceso ágil de resolución de conflictos</li></ul><p>La cooperativa actúa como garante de todas las transacciones.</p>',
                'position' => 3,
                'is_featured' => false,
                'tags' => ['garantías', 'seguridad', 'calidad', 'certificación'],
            ],
        ];

        foreach ($faqs as $index => $faqData) {
            Faq::create(array_merge($faqData, [
                'topic_id' => $topic->id,
                'organization_id' => $organizations->random()->id,
                'language' => 'es',
                'is_draft' => false,
                'published_at' => now()->subDays(rand(1, 25)),
                'views_count' => rand(30, 300),
                'helpful_count' => rand(15, 80),
                'not_helpful_count' => rand(0, 5),
                'created_by_user_id' => $users->random()->id,
                'updated_by_user_id' => $users->random()->id,
            ]));
        }
    }

    private function createParticipationFaqs($users, $organizations, $topics): void
    {
        $topic = $topics[2]; // Participación
        $faqs = [
            [
                'question' => '¿Cómo puedo participar en las decisiones de la cooperativa?',
                'answer' => '<p>Como socio puedes participar en las decisiones de la cooperativa de varias formas:</p><ul><li><strong>Asamblea General:</strong> Voto en decisiones importantes (presupuesto, estatutos, etc.)</li><li><strong>Consejo Rector:</strong> Candidatura para formar parte del órgano de gobierno</li><li><strong>Comisiones:</strong> Participación en comisiones temáticas</li><li><strong>Propuestas:</strong> Presentación de propuestas e iniciativas</li><li><strong>Consultas:</strong> Participación en consultas y encuestas</li></ul><p>Todas las decisiones importantes se comunican con antelación y se pueden votar online.</p>',
                'position' => 1,
                'is_featured' => false,
                'tags' => ['participación', 'voto', 'asamblea', 'democracia'],
            ],
            [
                'question' => '¿Cuándo se celebran las asambleas generales?',
                'answer' => '<p>Las asambleas generales se celebran:</p><ul><li><strong>Asamblea Ordinaria:</strong> Una vez al año (marzo-abril) para aprobar cuentas y presupuesto</li><li><strong>Asamblea Extraordinaria:</strong> Cuando sea necesario para decisiones importantes</li><li><strong>Convocatoria:</strong> Con 15 días de antelación mínimo</li><li><strong>Modalidades:</strong> Presencial y online</li><li><strong>Documentación:</strong> Disponible 7 días antes</li></ul><p>Recibirás notificación por email y SMS de todas las convocatorias.</p>',
                'position' => 2,
                'is_featured' => false,
                'tags' => ['asamblea', 'convocatoria', 'fechas', 'modalidades'],
            ],
        ];

        foreach ($faqs as $index => $faqData) {
            Faq::create(array_merge($faqData, [
                'topic_id' => $topic->id,
                'organization_id' => $organizations->random()->id,
                'language' => 'es',
                'is_draft' => false,
                'published_at' => now()->subDays(rand(1, 20)),
                'views_count' => rand(20, 150),
                'helpful_count' => rand(10, 50),
                'not_helpful_count' => rand(0, 3),
                'created_by_user_id' => $users->random()->id,
                'updated_by_user_id' => $users->random()->id,
            ]));
        }
    }

    private function createTechnologyFaqs($users, $organizations, $topics): void
    {
        $topic = $topics[3]; // Tecnología
        $faqs = [
            [
                'question' => '¿Qué tecnología utiliza la plataforma de transferencias?',
                'answer' => '<p>Nuestra plataforma utiliza tecnología de vanguardia:</p><ul><li><strong>Blockchain:</strong> Para garantizar la trazabilidad y seguridad</li><li><strong>Smart Contracts:</strong> Automatización de transacciones</li><li><strong>IoT:</strong> Sensores para medición en tiempo real</li><li><strong>IA:</strong> Optimización de precios y predicción de demanda</li><li><strong>Cloud:</strong> Infraestructura escalable y segura</li><li><strong>APIs:</strong> Integración con sistemas externos</li></ul><p>Toda la tecnología es open source y auditada por terceros.</p>',
                'position' => 1,
                'is_featured' => true,
                'tags' => ['tecnología', 'blockchain', 'IoT', 'IA'],
            ],
            [
                'question' => '¿Cómo funciona el sistema de medición inteligente?',
                'answer' => '<p>El sistema de medición inteligente incluye:</p><ul><li><strong>Contadores inteligentes:</strong> Medición en tiempo real cada 15 minutos</li><li><strong>Comunicación:</strong> Datos enviados automáticamente vía 4G/WiFi</li><li><strong>Almacenamiento:</strong> Histórico completo de consumos</li><li><strong>Análisis:</strong> Detección de patrones y anomalías</li><li><strong>Alertas:</strong> Notificaciones de incidencias</li><li><strong>Dashboard:</strong> Visualización de datos en tiempo real</li></ul><p>Los datos son tuyos y puedes exportarlos cuando quieras.</p>',
                'position' => 2,
                'is_featured' => false,
                'tags' => ['medición', 'contadores', 'tiempo real', 'datos'],
            ],
        ];

        foreach ($faqs as $index => $faqData) {
            Faq::create(array_merge($faqData, [
                'topic_id' => $topic->id,
                'organization_id' => $organizations->random()->id,
                'language' => 'es',
                'is_draft' => false,
                'published_at' => now()->subDays(rand(1, 15)),
                'views_count' => rand(40, 200),
                'helpful_count' => rand(20, 60),
                'not_helpful_count' => rand(0, 8),
                'created_by_user_id' => $users->random()->id,
                'updated_by_user_id' => $users->random()->id,
            ]));
        }
    }

    private function createBillingFaqs($users, $organizations, $topics): void
    {
        $topic = $topics[4]; // Facturación
        $faqs = [
            [
                'question' => '¿Cómo funciona la facturación en la cooperativa?',
                'answer' => '<p>La facturación en nuestra cooperativa es transparente y sencilla:</p><ul><li><strong>Facturación mensual:</strong> Recibes tu factura el día 5 de cada mes</li><li><strong>Desglose detallado:</strong> Consumo, producción, transferencias y servicios</li><li><strong>Precios justos:</strong> Sin márgenes ocultos ni comisiones</li><li><strong>Múltiples formas de pago:</strong> Domiciliación, transferencia, tarjeta</li><li><strong>Descuentos:</strong> Hasta 30% menos que las comercializadoras tradicionales</li><li><strong>Transparencia:</strong> Acceso completo a todos los datos</li></ul>',
                'position' => 1,
                'is_featured' => true,
                'tags' => ['facturación', 'precios', 'transparencia', 'descuentos'],
            ],
            [
                'question' => '¿Qué incluye la cuota de socio?',
                'answer' => '<p>La cuota de socio incluye:</p><ul><li><strong>Cuota de entrada:</strong> Desde 100€ (reembolsable al salir)</li><li><strong>Cuota anual:</strong> 25€ para gastos de gestión</li><li><strong>Servicios incluidos:</strong> Asesoramiento energético, mantenimiento, seguros</li><li><strong>Participación:</strong> Derecho a voto y beneficios</li><li><strong>Transparencia:</strong> Acceso a toda la información financiera</li><li><strong>Sin compromiso:</strong> Puedes salir cuando quieras</li></ul><p>La cuota de entrada se devuelve íntegramente al salir de la cooperativa.</p>',
                'position' => 2,
                'is_featured' => false,
                'tags' => ['cuota', 'socio', 'servicios', 'reembolso'],
            ],
            [
                'question' => '¿Cómo puedo consultar mi consumo energético?',
                'answer' => '<p>Puedes consultar tu consumo energético de múltiples formas:</p><ul><li><strong>Dashboard web:</strong> Acceso 24/7 desde cualquier dispositivo</li><li><strong>App móvil:</strong> Notificaciones y alertas en tiempo real</li><li><strong>Informes mensuales:</strong> Análisis detallado de tu consumo</li><li><strong>Comparativas:</strong> Comparación con períodos anteriores</li><li><strong>Recomendaciones:</strong> Consejos personalizados para ahorrar</li><li><strong>Exportación:</strong> Descarga de datos en Excel/CSV</li></ul>',
                'position' => 3,
                'is_featured' => false,
                'tags' => ['consumo', 'dashboard', 'app', 'informes'],
            ],
        ];

        foreach ($faqs as $index => $faqData) {
            Faq::create(array_merge($faqData, [
                'topic_id' => $topic->id,
                'organization_id' => $organizations->random()->id,
                'language' => 'es',
                'is_draft' => false,
                'published_at' => now()->subDays(rand(1, 10)),
                'views_count' => rand(60, 400),
                'helpful_count' => rand(30, 120),
                'not_helpful_count' => rand(0, 15),
                'created_by_user_id' => $users->random()->id,
                'updated_by_user_id' => $users->random()->id,
            ]));
        }
    }
}
