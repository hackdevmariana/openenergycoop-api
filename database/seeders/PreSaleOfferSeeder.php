<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PreSaleOffer;
use App\Models\User;
use Carbon\Carbon;

class PreSaleOfferSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Creando ofertas de preventa españolas para la cooperativa energética...');
        
        // Limpiar ofertas existentes
        PreSaleOffer::query()->delete();
        
        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. Creando ofertas sin usuario creador.');
            $users = collect([null]);
        }
        
        $this->createEarlyBirdOffers($users);
        $this->createFounderOffers($users);
        $this->createLimitedTimeOffers($users);
        $this->createExclusiveOffers($users);
        $this->createBetaOffers($users);
        $this->createPilotOffers($users);
        
        $this->command->info('✅ PreSaleOfferSeeder completado. Se crearon ' . PreSaleOffer::count() . ' ofertas de preventa españolas.');
    }
    
    private function createEarlyBirdOffers($users): void
    {
        $this->command->info('🐦 Creando ofertas Early Bird...');
        
        $earlyBirdOffers = [
            [
                'title' => 'Panel Solar de Perovskita 500W - Early Bird',
                'description' => '¡Sé de los primeros en obtener la nueva generación de paneles solares! Tecnología de perovskita que promete revolucionar la eficiencia solar con un 30% más de rendimiento que los paneles tradicionales.',
                'offer_type' => 'early_bird',
                'status' => 'active',
                'start_date' => now()->subDays(30),
                'end_date' => now()->addMonths(3),
                'early_bird_end_date' => now()->addDays(15),
                'total_units_available' => 100,
                'units_reserved' => 25,
                'units_sold' => 15,
                'early_bird_price' => 349.99,
                'founder_price' => 399.99,
                'regular_price' => 499.99,
                'final_price' => 599.99,
                'savings_percentage' => 30.00,
                'savings_amount' => 150.00,
                'max_units_per_customer' => 2,
                'is_featured' => true,
                'is_public' => true,
                'terms_conditions' => 'Oferta Early Bird válida hasta el 15 de enero. Precio especial para los primeros 100 clientes. Entrega estimada: Q2 2025.',
                'delivery_timeline' => 'Entrega estimada: Abril-Junio 2025. Los primeros 50 pedidos se enviarán en abril.',
                'risk_disclosure' => 'Producto en fase de desarrollo final. Riesgo mínimo de retraso en entrega.',
                'included_features' => [
                    'Panel de 500W con tecnología perovskita',
                    'Garantía extendida de 25 años',
                    'Certificación CE y RoHS',
                    'Manual de instalación digital',
                    'Soporte técnico premium'
                ],
                'excluded_features' => [
                    'Sistema de montaje',
                    'Inversor',
                    'Instalación profesional'
                ],
                'bonus_items' => [
                    'Monitor de rendimiento gratuito',
                    'App móvil de seguimiento',
                    'Mantenimiento preventivo 1 año'
                ],
                'early_access_benefits' => [
                    'Acceso beta a la app de monitoreo',
                    'Webinar exclusivo con ingenieros',
                    'Actualizaciones prioritarias de firmware'
                ],
                'founder_benefits' => [
                    'Nombre en la lista de fundadores',
                    'Certificado de fundador',
                    'Acceso a eventos exclusivos'
                ],
                'marketing_materials' => [
                    'Video promocional',
                    'Folleto técnico',
                    'Presentación ejecutiva',
                    'Imágenes de alta resolución'
                ],
                'tags' => ['early-bird', 'perovskita', 'nueva-tecnologia', 'energia-solar', 'aragon']
            ],
            [
                'title' => 'Batería de Grafeno 10kWh - Early Bird',
                'description' => 'La batería del futuro está aquí. Tecnología de grafeno que ofrece 5 veces más durabilidad y 3 veces más ciclos de carga que las baterías de litio tradicionales.',
                'offer_type' => 'early_bird',
                'status' => 'active',
                'start_date' => now()->subDays(45),
                'end_date' => now()->addMonths(4),
                'early_bird_end_date' => now()->addDays(30),
                'total_units_available' => 50,
                'units_reserved' => 15,
                'units_sold' => 8,
                'early_bird_price' => 2999.99,
                'founder_price' => 3499.99,
                'regular_price' => 4499.99,
                'final_price' => 5499.99,
                'savings_percentage' => 33.33,
                'savings_amount' => 1500.00,
                'max_units_per_customer' => 1,
                'is_featured' => true,
                'is_public' => true,
                'terms_conditions' => 'Oferta Early Bird válida hasta el 30 de enero. Solo 50 unidades disponibles. Entrega estimada: Q3 2025.',
                'delivery_timeline' => 'Entrega estimada: Julio-Septiembre 2025. Los primeros 25 pedidos se enviarán en julio.',
                'risk_disclosure' => 'Producto en fase de certificación final. Posible retraso de 1-2 meses.',
                'included_features' => [
                    'Batería de 10kWh con tecnología grafeno',
                    'Sistema de gestión BMS avanzado',
                    'Garantía de 15 años',
                    'Monitor de estado integrado',
                    'Soporte técnico 24/7'
                ],
                'excluded_features' => [
                    'Inversor híbrido',
                    'Sistema de montaje',
                    'Instalación profesional'
                ],
                'bonus_items' => [
                    'Monitor de batería inteligente',
                    'App de gestión avanzada',
                    'Mantenimiento preventivo 2 años'
                ],
                'early_access_benefits' => [
                    'Acceso beta al software de gestión',
                    'Sesión de configuración personalizada',
                    'Soporte técnico directo con ingenieros'
                ],
                'founder_benefits' => [
                    'Placa conmemorativa de fundador',
                    'Acceso a laboratorio de pruebas',
                    'Participación en desarrollo de futuras versiones'
                ],
                'marketing_materials' => [
                    'Video de demostración',
                    'Especificaciones técnicas',
                    'Comparativa con baterías tradicionales',
                    'Testimonios de expertos'
                ],
                'tags' => ['early-bird', 'grafeno', 'bateria', 'almacenamiento', 'energia-verde', 'aragon']
            ]
        ];
        
        foreach ($earlyBirdOffers as $offerData) {
            $user = $users->random();
            PreSaleOffer::create(array_merge($offerData, [
                'offer_number' => $this->generateOfferNumber('EB'),
                'created_by' => $user ? $user->id : 1,
                'approved_by' => $user ? $user->id : 1,
                'approved_at' => now()->subDays(rand(1, 30)),
            ]));
        }
    }
    
    private function createFounderOffers($users): void
    {
        $this->command->info('👑 Creando ofertas de Fundador...');
        
        $founderOffers = [
            [
                'title' => 'Sistema Híbrido Solar-Eólico - Fundador',
                'description' => 'Únete al selecto grupo de fundadores que cambiarán el futuro de la energía. Sistema híbrido que combina lo mejor de la energía solar y eólica con tecnología de vanguardia.',
                'offer_type' => 'founder',
                'status' => 'active',
                'start_date' => now()->subDays(60),
                'end_date' => now()->addMonths(6),
                'founder_end_date' => now()->addDays(45),
                'total_units_available' => 25,
                'units_reserved' => 8,
                'units_sold' => 5,
                'early_bird_price' => 8999.99,
                'founder_price' => 12999.99,
                'regular_price' => 18999.99,
                'final_price' => 24999.99,
                'savings_percentage' => 31.58,
                'savings_amount' => 6000.00,
                'max_units_per_customer' => 1,
                'is_featured' => true,
                'is_public' => false,
                'terms_conditions' => 'Oferta exclusiva para fundadores. Solo 25 unidades disponibles. Entrega estimada: Q4 2025.',
                'delivery_timeline' => 'Entrega estimada: Octubre-Diciembre 2025. Los primeros 10 sistemas se instalarán en octubre.',
                'risk_disclosure' => 'Sistema en fase de desarrollo final. Posible retraso de 2-3 meses.',
                'included_features' => [
                    'Sistema híbrido completo 5kW',
                    'Paneles solares de alta eficiencia',
                    'Aerogenerador vertical silencioso',
                    'Batería de almacenamiento 15kWh',
                    'Sistema de gestión inteligente',
                    'Instalación profesional incluida',
                    'Garantía extendida de 20 años'
                ],
                'excluded_features' => [
                    'Permisos municipales',
                    'Conexión a red eléctrica',
                    'Mantenimiento posterior'
                ],
                'bonus_items' => [
                    'Monitor de rendimiento premium',
                    'App de gestión avanzada',
                    'Mantenimiento preventivo 5 años',
                    'Soporte técnico VIP'
                ],
                'early_access_benefits' => [
                    'Acceso beta al sistema de gestión',
                    'Configuración personalizada',
                    'Soporte técnico directo con CEO'
                ],
                'founder_benefits' => [
                    'Placa conmemorativa de fundador',
                    'Certificado de fundador numerado',
                    'Acceso a eventos exclusivos',
                    'Participación en decisiones de producto',
                    'Descuento del 20% en futuros productos'
                ],
                'marketing_materials' => [
                    'Video promocional exclusivo',
                    'Documentación técnica completa',
                    'Presentación ejecutiva',
                    'Testimonios de fundadores'
                ],
                'tags' => ['founder', 'hibrido', 'solar-eolico', 'exclusivo', 'energia-renovable', 'aragon']
            ],
            [
                'title' => 'Microgrid Comunitario - Fundador',
                'description' => 'Sé parte de la revolución energética comunitaria. Sistema de microgrid que permite a comunidades enteras ser autosuficientes energéticamente.',
                'offer_type' => 'founder',
                'status' => 'active',
                'start_date' => now()->subDays(90),
                'end_date' => now()->addMonths(12),
                'founder_end_date' => now()->addDays(60),
                'total_units_available' => 10,
                'units_reserved' => 3,
                'units_sold' => 2,
                'early_bird_price' => 29999.99,
                'founder_price' => 44999.99,
                'regular_price' => 69999.99,
                'final_price' => 89999.99,
                'savings_percentage' => 35.71,
                'savings_amount' => 25000.00,
                'max_units_per_customer' => 1,
                'is_featured' => true,
                'is_public' => false,
                'terms_conditions' => 'Oferta exclusiva para fundadores comunitarios. Solo 10 sistemas disponibles. Entrega estimada: Q2 2026.',
                'delivery_timeline' => 'Entrega estimada: Abril-Junio 2026. Los primeros 5 sistemas se instalarán en abril.',
                'risk_disclosure' => 'Sistema complejo en desarrollo. Posible retraso de 3-6 meses.',
                'included_features' => [
                    'Sistema de microgrid completo 50kW',
                    'Paneles solares comunitarios',
                    'Baterías de almacenamiento 100kWh',
                    'Sistema de gestión comunitario',
                    'App de participación ciudadana',
                    'Instalación y configuración completa',
                    'Garantía extendida de 25 años'
                ],
                'excluded_features' => [
                    'Permisos municipales y ambientales',
                    'Conexión a red eléctrica',
                    'Mantenimiento comunitario'
                ],
                'bonus_items' => [
                    'Sistema de monitoreo comunitario',
                    'App de gestión participativa',
                    'Mantenimiento preventivo 10 años',
                    'Soporte técnico comunitario',
                    'Formación para gestores comunitarios'
                ],
                'early_access_benefits' => [
                    'Acceso beta al sistema de gestión',
                    'Configuración personalizada',
                    'Soporte técnico directo con equipo ejecutivo'
                ],
                'founder_benefits' => [
                    'Placa conmemorativa de fundador comunitario',
                    'Certificado de fundador numerado',
                    'Acceso a eventos exclusivos comunitarios',
                    'Participación en decisiones de producto',
                    'Descuento del 25% en futuros productos',
                    'Reconocimiento público como fundador'
                ],
                'marketing_materials' => [
                    'Video promocional comunitario',
                    'Documentación técnica completa',
                    'Presentación ejecutiva',
                    'Testimonios de fundadores comunitarios',
                    'Casos de estudio internacionales'
                ],
                'tags' => ['founder', 'microgrid', 'comunitario', 'exclusivo', 'energia-comunitaria', 'aragon']
            ]
        ];
        
        foreach ($founderOffers as $offerData) {
            $user = $users->random();
            PreSaleOffer::create(array_merge($offerData, [
                'offer_number' => $this->generateOfferNumber('FD'),
                'created_by' => $user ? $user->id : 1,
                'approved_by' => $user ? $user->id : 1,
                'approved_at' => now()->subDays(rand(1, 60)),
            ]));
        }
    }
    
    private function createLimitedTimeOffers($users): void
    {
        $this->command->info('⏰ Creando ofertas de tiempo limitado...');
        
        $limitedTimeOffers = [
            [
                'title' => 'Kit Solar Residencial 3kW - Oferta Flash',
                'description' => '¡Oferta por tiempo limitado! Kit solar completo para vivienda unifamiliar. Solo disponible hasta agotar existencias. Incluye instalación profesional y garantía extendida.',
                'offer_type' => 'limited_time',
                'status' => 'active',
                'start_date' => now()->subDays(15),
                'end_date' => now()->addDays(30),
                'total_units_available' => 75,
                'units_reserved' => 20,
                'units_sold' => 35,
                'early_bird_price' => 3999.99,
                'founder_price' => null,
                'regular_price' => 5999.99,
                'final_price' => 6999.99,
                'savings_percentage' => 33.33,
                'savings_amount' => 2000.00,
                'max_units_per_customer' => 1,
                'is_featured' => false,
                'is_public' => true,
                'terms_conditions' => 'Oferta válida hasta agotar existencias o hasta el 15 de febrero. Solo 75 unidades disponibles. Entrega en 4-6 semanas.',
                'delivery_timeline' => 'Entrega en 4-6 semanas desde la confirmación del pedido. Instalación programada según disponibilidad.',
                'risk_disclosure' => 'Producto en stock limitado. Posible agotamiento antes de la fecha límite.',
                'included_features' => [
                    'Kit solar completo 3kW',
                    '8 paneles solares de 375W',
                    'Inversor híbrido 3kW',
                    'Sistema de montaje',
                    'Instalación profesional',
                    'Garantía de 10 años'
                ],
                'excluded_features' => [
                    'Batería de almacenamiento',
                    'Permisos municipales',
                    'Conexión a red eléctrica'
                ],
                'bonus_items' => [
                    'Monitor de rendimiento básico',
                    'App de seguimiento',
                    'Mantenimiento preventivo 1 año'
                ],
                'early_access_benefits' => null,
                'founder_benefits' => null,
                'marketing_materials' => [
                    'Video de instalación',
                    'Manual de usuario',
                    'Especificaciones técnicas'
                ],
                'tags' => ['tiempo-limitado', 'kit-solar', 'residencial', 'oferta-flash', 'energia-solar', 'aragon']
            ],
            [
                'title' => 'Batería de Litio 5kWh - Liquidación',
                'description' => '¡Liquidación por cambio de modelo! Baterías de litio de alta calidad con descuento excepcional. Stock limitado, ¡no te lo pierdas!',
                'offer_type' => 'limited_time',
                'status' => 'active',
                'start_date' => now()->subDays(7),
                'end_date' => now()->addDays(21),
                'total_units_available' => 30,
                'units_reserved' => 8,
                'units_sold' => 12,
                'early_bird_price' => 1499.99,
                'founder_price' => null,
                'regular_price' => 2499.99,
                'final_price' => 2999.99,
                'savings_percentage' => 40.00,
                'savings_amount' => 1000.00,
                'max_units_per_customer' => 2,
                'is_featured' => false,
                'is_public' => true,
                'terms_conditions' => 'Liquidación por cambio de modelo. Solo 30 unidades disponibles. Entrega inmediata. Sin devoluciones.',
                'delivery_timeline' => 'Entrega inmediata en 1-2 semanas. Stock disponible en almacén.',
                'risk_disclosure' => 'Producto en liquidación. Garantía estándar de 5 años. Sin devoluciones.',
                'included_features' => [
                    'Batería de litio 5kWh',
                    'Sistema BMS integrado',
                    'Monitor de estado',
                    'Garantía de 5 años'
                ],
                'excluded_features' => [
                    'Inversor híbrido',
                    'Sistema de montaje',
                    'Instalación profesional'
                ],
                'bonus_items' => [
                    'Monitor de batería básico',
                    'Manual de instalación'
                ],
                'early_access_benefits' => null,
                'founder_benefits' => null,
                'marketing_materials' => [
                    'Especificaciones técnicas',
                    'Manual de usuario',
                    'Guía de instalación'
                ],
                'tags' => ['tiempo-limitado', 'liquidacion', 'bateria-litio', 'stock-limitado', 'energia-verde', 'aragon']
            ]
        ];
        
        foreach ($limitedTimeOffers as $offerData) {
            $user = $users->random();
            PreSaleOffer::create(array_merge($offerData, [
                'offer_number' => $this->generateOfferNumber('LT'),
                'created_by' => $user ? $user->id : 1,
                'approved_by' => $user ? $user->id : 1,
                'approved_at' => now()->subDays(rand(1, 15)),
            ]));
        }
    }
    
    private function createExclusiveOffers($users): void
    {
        $this->command->info('💎 Creando ofertas exclusivas...');
        
        $exclusiveOffers = [
            [
                'title' => 'Sistema de Hidrógeno Verde - Exclusivo',
                'description' => 'Oferta exclusiva para clientes VIP. Sistema de hidrógeno verde que convierte energía solar en hidrógeno para almacenamiento a largo plazo. Solo 5 unidades disponibles.',
                'offer_type' => 'exclusive',
                'status' => 'active',
                'start_date' => now()->subDays(30),
                'end_date' => now()->addMonths(8),
                'total_units_available' => 5,
                'units_reserved' => 2,
                'units_sold' => 1,
                'early_bird_price' => 19999.99,
                'founder_price' => 24999.99,
                'regular_price' => 34999.99,
                'final_price' => 44999.99,
                'savings_percentage' => 42.86,
                'savings_amount' => 15000.00,
                'max_units_per_customer' => 1,
                'is_featured' => true,
                'is_public' => false,
                'terms_conditions' => 'Oferta exclusiva para clientes VIP. Solo 5 unidades disponibles. Entrega estimada: Q3 2025.',
                'delivery_timeline' => 'Entrega estimada: Julio-Septiembre 2025. Instalación personalizada incluida.',
                'risk_disclosure' => 'Tecnología emergente. Posible retraso de 2-4 meses.',
                'included_features' => [
                    'Sistema de hidrógeno verde completo',
                    'Electrolizador de alta eficiencia',
                    'Tanque de almacenamiento de hidrógeno',
                    'Célula de combustible',
                    'Sistema de gestión inteligente',
                    'Instalación personalizada',
                    'Garantía extendida de 15 años'
                ],
                'excluded_features' => [
                    'Paneles solares',
                    'Permisos especiales',
                    'Certificaciones ambientales'
                ],
                'bonus_items' => [
                    'Monitor de hidrógeno premium',
                    'App de gestión avanzada',
                    'Mantenimiento preventivo 5 años',
                    'Soporte técnico VIP'
                ],
                'early_access_benefits' => [
                    'Acceso beta al sistema de gestión',
                    'Configuración personalizada',
                    'Soporte técnico directo con CTO'
                ],
                'founder_benefits' => [
                    'Acceso exclusivo a laboratorio de hidrógeno',
                    'Participación en desarrollo de futuras versiones',
                    'Descuento del 30% en futuros productos'
                ],
                'marketing_materials' => [
                    'Video promocional exclusivo',
                    'Documentación técnica confidencial',
                    'Presentación ejecutiva',
                    'Testimonios de expertos'
                ],
                'tags' => ['exclusivo', 'hidrogeno-verde', 'vip', 'tecnologia-emergente', 'energia-verde', 'aragon']
            ]
        ];
        
        foreach ($exclusiveOffers as $offerData) {
            $user = $users->random();
            PreSaleOffer::create(array_merge($offerData, [
                'offer_number' => $this->generateOfferNumber('EX'),
                'created_by' => $user ? $user->id : 1,
                'approved_by' => $user ? $user->id : 1,
                'approved_at' => now()->subDays(rand(1, 30)),
            ]));
        }
    }
    
    private function createBetaOffers($users): void
    {
        $this->command->info('🧪 Creando ofertas Beta...');
        
        $betaOffers = [
            [
                'title' => 'Sistema de Domótica IA - Beta',
                'description' => 'Únete al programa beta de nuestro sistema de domótica con inteligencia artificial. Sé de los primeros en probar la tecnología del futuro y ayuda a mejorarla.',
                'offer_type' => 'beta',
                'status' => 'active',
                'start_date' => now()->subDays(20),
                'end_date' => now()->addMonths(2),
                'total_units_available' => 20,
                'units_reserved' => 5,
                'units_sold' => 3,
                'early_bird_price' => 799.99,
                'founder_price' => 999.99,
                'regular_price' => 1499.99,
                'final_price' => 1999.99,
                'savings_percentage' => 46.67,
                'savings_amount' => 700.00,
                'max_units_per_customer' => 1,
                'is_featured' => false,
                'is_public' => true,
                'terms_conditions' => 'Programa beta. Solo 20 unidades disponibles. Entrega en 2-3 semanas. Feedback requerido.',
                'delivery_timeline' => 'Entrega en 2-3 semanas. Instalación beta incluida.',
                'risk_disclosure' => 'Producto en fase beta. Posibles bugs o funcionalidades incompletas. Feedback requerido.',
                'included_features' => [
                    'Sistema de domótica IA completo',
                    'Hub inteligente con procesador IA',
                    '5 sensores inteligentes',
                    'App de control avanzada',
                    'Instalación beta',
                    'Garantía beta de 2 años'
                ],
                'excluded_features' => [
                    'Sensores adicionales',
                    'Integración con terceros',
                    'Funcionalidades premium'
                ],
                'bonus_items' => [
                    'Acceso beta a todas las funcionalidades',
                    'Soporte técnico beta prioritario',
                    'Actualizaciones gratuitas de por vida'
                ],
                'early_access_benefits' => [
                    'Acceso beta a nuevas funcionalidades',
                    'Participación en desarrollo de features',
                    'Soporte técnico directo con desarrolladores'
                ],
                'founder_benefits' => [
                    'Acceso a roadmap de desarrollo',
                    'Participación en decisiones de producto',
                    'Descuento del 25% en versión final'
                ],
                'marketing_materials' => [
                    'Video de demostración beta',
                    'Manual de usuario beta',
                    'Roadmap de desarrollo',
                    'FAQ beta'
                ],
                'tags' => ['beta', 'domotica-ia', 'inteligencia-artificial', 'programa-beta', 'tecnologia-futuro', 'aragon']
            ]
        ];
        
        foreach ($betaOffers as $offerData) {
            $user = $users->random();
            PreSaleOffer::create(array_merge($offerData, [
                'offer_number' => $this->generateOfferNumber('BT'),
                'created_by' => $user ? $user->id : 1,
                'approved_by' => $user ? $user->id : 1,
                'approved_at' => now()->subDays(rand(1, 20)),
            ]));
        }
    }
    
    private function createPilotOffers($users): void
    {
        $this->command->info('✈️ Creando ofertas Piloto...');
        
        $pilotOffers = [
            [
                'title' => 'Sistema de Energía Comunitaria - Piloto',
                'description' => 'Programa piloto para comunidades que quieren ser pioneras en energía sostenible. Sistema completo de energía comunitaria con monitoreo participativo.',
                'offer_type' => 'pilot',
                'status' => 'active',
                'start_date' => now()->subDays(15),
                'end_date' => now()->addMonths(3),
                'total_units_available' => 3,
                'units_reserved' => 1,
                'units_sold' => 0,
                'early_bird_price' => 14999.99,
                'founder_price' => 19999.99,
                'regular_price' => 29999.99,
                'final_price' => 39999.99,
                'savings_percentage' => 50.00,
                'savings_amount' => 15000.00,
                'max_units_per_customer' => 1,
                'is_featured' => true,
                'is_public' => true,
                'terms_conditions' => 'Programa piloto. Solo 3 comunidades seleccionadas. Evaluación de candidatos requerida. Entrega en 3-4 meses.',
                'delivery_timeline' => 'Entrega en 3-4 meses. Instalación piloto incluida. Monitoreo intensivo durante 6 meses.',
                'risk_disclosure' => 'Programa piloto. Posibles ajustes durante la implementación. Monitoreo intensivo requerido.',
                'included_features' => [
                    'Sistema de energía comunitaria 20kW',
                    'Paneles solares comunitarios',
                    'Baterías de almacenamiento 40kWh',
                    'Sistema de gestión comunitario',
                    'App de participación ciudadana',
                    'Instalación piloto completa',
                    'Monitoreo intensivo 6 meses',
                    'Garantía piloto de 10 años'
                ],
                'excluded_features' => [
                    'Permisos municipales',
                    'Conexión a red eléctrica',
                    'Mantenimiento posterior'
                ],
                'bonus_items' => [
                    'Sistema de monitoreo piloto',
                    'App de gestión participativa',
                    'Formación para gestores comunitarios',
                    'Soporte técnico piloto 24/7'
                ],
                'early_access_benefits' => [
                    'Acceso piloto a todas las funcionalidades',
                    'Configuración personalizada',
                    'Soporte técnico directo con equipo piloto'
                ],
                'founder_benefits' => [
                    'Participación en desarrollo del programa',
                    'Acceso a roadmap de expansión',
                    'Descuento del 40% en futuras implementaciones'
                ],
                'marketing_materials' => [
                    'Video promocional piloto',
                    'Documentación técnica piloto',
                    'Presentación ejecutiva',
                    'Casos de estudio piloto'
                ],
                'tags' => ['pilot', 'energia-comunitaria', 'programa-piloto', 'sostenible', 'participacion-ciudadana', 'aragon']
            ]
        ];
        
        foreach ($pilotOffers as $offerData) {
            $user = $users->random();
            PreSaleOffer::create(array_merge($offerData, [
                'offer_number' => $this->generateOfferNumber('PL'),
                'created_by' => $user ? $user->id : 1,
                'approved_by' => $user ? $user->id : 1,
                'approved_at' => now()->subDays(rand(1, 15)),
            ]));
        }
    }
    
    private function generateOfferNumber($prefix): string
    {
        return $prefix . '-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    }
}
