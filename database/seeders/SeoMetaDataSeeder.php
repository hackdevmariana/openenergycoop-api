<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SeoMetaData;
use App\Models\Page;
use App\Models\Article;
use App\Models\Hero;
use App\Models\TextContent;
use App\Models\Banner;
use App\Models\Image;

class SeoMetaDataSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener modelos existentes para asociar SEO
        $pages = Page::take(3)->get();
        $articles = Article::take(2)->get();
        $heroes = Hero::take(2)->get();
        $textContents = TextContent::take(2)->get();
        $banners = Banner::take(1)->get();
        $images = Image::take(1)->get();

        $seoData = [
            // SEO para páginas
            [
                'seoable_type' => 'App\\Models\\Page',
                'seoable_id' => $pages->first()?->id ?? 1,
                'meta_title' => 'Open Energy Coop - Cooperativa de Energía Renovable',
                'meta_description' => 'Únete a nuestra cooperativa de energía renovable. Generamos electricidad limpia y sostenible para un futuro mejor. Participa en la transición energética.',
                'canonical_url' => 'https://openenergycoop.com',
                'robots' => 'index,follow',
                'og_title' => 'Open Energy Coop - Energía Renovable',
                'og_description' => 'Cooperativa de energía renovable comprometida con la sostenibilidad y la transición energética.',
                'og_image_path' => 'https://picsum.photos/1200/630?random=seo1',
                'og_type' => 'website',
                'twitter_title' => 'Open Energy Coop - Energía Renovable',
                'twitter_description' => 'Cooperativa de energía renovable para un futuro sostenible.',
                'twitter_image_path' => 'https://picsum.photos/1200/630?random=seo1',
                'twitter_card' => 'summary_large_image',
                'structured_data' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'Organization',
                    'name' => 'Open Energy Coop',
                    'description' => 'Cooperativa de energía renovable',
                    'url' => 'https://openenergycoop.com',
                    'logo' => 'https://picsum.photos/300/300?random=logo',
                    'sameAs' => [
                        'https://twitter.com/openenergycoop',
                        'https://linkedin.com/company/openenergycoop',
                    ],
                ],
                'focus_keyword' => 'energía renovable, cooperativa energética, sostenibilidad',
                'additional_meta' => [
                    ['name' => 'author', 'content' => 'Open Energy Coop'],
                    ['name' => 'theme-color', 'content' => '#2E7D32'],
                ],
                'language' => 'es',
            ],
            [
                'seoable_type' => 'App\\Models\\Page',
                'seoable_id' => $pages->skip(1)->first()?->id ?? 2,
                'meta_title' => 'Acerca de Nosotros - Open Energy Coop',
                'meta_description' => 'Conoce nuestra historia, misión y valores. Somos una cooperativa comprometida con la democratización de la energía renovable.',
                'canonical_url' => 'https://openenergycoop.com/about',
                'robots' => 'index,follow',
                'og_title' => 'Acerca de Open Energy Coop',
                'og_description' => 'Descubre nuestra misión de democratizar la energía renovable.',
                'og_image_path' => 'https://picsum.photos/1200/630?random=seo2',
                'og_type' => 'website',
                'twitter_title' => 'Acerca de Open Energy Coop',
                'twitter_description' => 'Misión y valores de nuestra cooperativa energética.',
                'twitter_image_path' => 'https://picsum.photos/1200/630?random=seo2',
                'twitter_card' => 'summary_large_image',
                'structured_data' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'AboutPage',
                    'name' => 'Acerca de Open Energy Coop',
                    'description' => 'Información sobre nuestra cooperativa de energía renovable',
                    'url' => 'https://openenergycoop.com/about',
                ],
                'focus_keyword' => 'acerca de, cooperativa, misión, valores',
                'additional_meta' => [
                    ['name' => 'author', 'content' => 'Open Energy Coop'],
                ],
                'language' => 'es',
            ],

            // SEO para artículos
            [
                'seoable_type' => 'App\\Models\\Article',
                'seoable_id' => $articles->first()?->id ?? 1,
                'meta_title' => 'Cómo Funciona la Energía Solar - Guía Completa 2024',
                'meta_description' => 'Descubre cómo funciona la energía solar, sus beneficios y cómo puedes integrarla en tu hogar. Guía completa sobre paneles solares.',
                'canonical_url' => 'https://openenergycoop.com/articles/energia-solar-guia',
                'robots' => 'index,follow',
                'og_title' => 'Guía Completa: Cómo Funciona la Energía Solar',
                'og_description' => 'Aprende todo sobre la energía solar y cómo implementarla en tu hogar.',
                'og_image_path' => 'https://picsum.photos/1200/630?random=seo3',
                'og_type' => 'article',
                'twitter_title' => 'Guía: Energía Solar para Principiantes',
                'twitter_description' => 'Todo lo que necesitas saber sobre energía solar.',
                'twitter_image_path' => 'https://picsum.photos/1200/630?random=seo3',
                'twitter_card' => 'summary_large_image',
                'structured_data' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'Article',
                    'headline' => 'Cómo Funciona la Energía Solar - Guía Completa 2024',
                    'description' => 'Guía completa sobre energía solar y paneles fotovoltaicos',
                    'author' => [
                        '@type' => 'Person',
                        'name' => 'Equipo Técnico Open Energy Coop',
                    ],
                    'publisher' => [
                        '@type' => 'Organization',
                        'name' => 'Open Energy Coop',
                    ],
                    'datePublished' => now()->subDays(30)->toISOString(),
                    'dateModified' => now()->subDays(5)->toISOString(),
                ],
                'focus_keyword' => 'energía solar, paneles solares, fotovoltaica, guía',
                'additional_meta' => [
                    ['name' => 'article:author', 'content' => 'Equipo Técnico'],
                    ['name' => 'article:section', 'content' => 'Energía Renovable'],
                    ['name' => 'article:tag', 'content' => 'energía solar'],
                ],
                'language' => 'es',
            ],

            // SEO para componentes Hero
            [
                'seoable_type' => 'App\\Models\\Hero',
                'seoable_id' => $heroes->first()?->id ?? 1,
                'meta_title' => 'Únete a la Revolución Energética - Open Energy Coop',
                'meta_description' => 'Forma parte del cambio hacia un futuro energético sostenible. Únete a nuestra cooperativa y participa en la generación de energía limpia.',
                'canonical_url' => 'https://openenergycoop.com/join',
                'robots' => 'index,follow',
                'og_title' => 'Únete a la Revolución Energética',
                'og_description' => 'Participa en la transición hacia la energía renovable.',
                'og_image_path' => 'https://picsum.photos/1200/630?random=seo4',
                'og_type' => 'website',
                'twitter_title' => 'Revolución Energética - Únete Ahora',
                'twitter_description' => 'Forma parte del cambio energético sostenible.',
                'twitter_image_path' => 'https://picsum.photos/1200/630?random=seo4',
                'twitter_card' => 'summary_large_image',
                'structured_data' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebPage',
                    'name' => 'Únete a la Revolución Energética',
                    'description' => 'Página de incorporación a la cooperativa energética',
                ],
                'focus_keyword' => 'únete, cooperativa, energía renovable, revolución energética',
                'additional_meta' => [
                    ['name' => 'robots', 'content' => 'index,follow'],
                ],
                'language' => 'es',
            ],

            // SEO para contenido de texto
            [
                'seoable_type' => 'App\\Models\\TextContent',
                'seoable_id' => $textContents->first()?->id ?? 1,
                'meta_title' => 'Beneficios de la Energía Renovable - Open Energy Coop',
                'meta_description' => 'Descubre todos los beneficios de la energía renovable: sostenibilidad, ahorro económico y cuidado del medio ambiente.',
                'canonical_url' => 'https://openenergycoop.com/beneficios-energia-renovable',
                'robots' => 'index,follow',
                'og_title' => 'Beneficios de la Energía Renovable',
                'og_description' => 'Conoce las ventajas de apostar por la energía limpia.',
                'og_image_path' => 'https://picsum.photos/1200/630?random=seo5',
                'og_type' => 'article',
                'twitter_title' => 'Beneficios de la Energía Renovable',
                'twitter_description' => 'Ventajas de la energía limpia y sostenible.',
                'twitter_image_path' => 'https://picsum.photos/1200/630?random=seo5',
                'twitter_card' => 'summary_large_image',
                'structured_data' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'Article',
                    'headline' => 'Beneficios de la Energía Renovable',
                    'description' => 'Artículo sobre las ventajas de la energía renovable',
                ],
                'focus_keyword' => 'beneficios energía renovable, sostenibilidad, medio ambiente',
                'additional_meta' => [
                    ['name' => 'article:section', 'content' => 'Sostenibilidad'],
                ],
                'language' => 'es',
            ],

            // SEO para banners
            [
                'seoable_type' => 'App\\Models\\Banner',
                'seoable_id' => $banners->first()?->id ?? 1,
                'meta_title' => 'Oferta Especial - Instalación Solar - Open Energy Coop',
                'meta_description' => 'Aprovecha nuestra oferta especial en instalaciones solares. Financiación flexible y garantía extendida. ¡Consulta sin compromiso!',
                'canonical_url' => 'https://openenergycoop.com/oferta-solar',
                'robots' => 'index,follow',
                'og_title' => 'Oferta Especial Instalación Solar',
                'og_description' => 'Oferta limitada en instalaciones de paneles solares.',
                'og_image_path' => 'https://picsum.photos/1200/630?random=seo6',
                'og_type' => 'website',
                'twitter_title' => 'Oferta Solar - Open Energy Coop',
                'twitter_description' => 'Oferta especial en instalaciones solares.',
                'twitter_image_path' => 'https://picsum.photos/1200/630?random=seo6',
                'twitter_card' => 'summary_large_image',
                'structured_data' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'Offer',
                    'name' => 'Oferta Especial Instalación Solar',
                    'description' => 'Oferta limitada en instalaciones de paneles solares',
                    'price' => 'Desde 2999€',
                    'priceCurrency' => 'EUR',
                ],
                'focus_keyword' => 'oferta solar, instalación paneles, financiación',
                'additional_meta' => [
                    ['name' => 'robots', 'content' => 'index,follow'],
                ],
                'language' => 'es',
            ],

            // SEO para imágenes
            [
                'seoable_type' => 'App\\Models\\Image',
                'seoable_id' => $images->first()?->id ?? 1,
                'meta_title' => 'Paneles Solares - Imagen de Energía Renovable',
                'meta_description' => 'Imagen de paneles solares instalados en campo abierto. Fotografía de alta calidad para uso en proyectos de energía renovable.',
                'canonical_url' => 'https://openenergycoop.com/images/paneles-solares',
                'robots' => 'index,follow',
                'og_title' => 'Paneles Solares - Energía Renovable',
                'og_description' => 'Imagen de paneles solares para proyectos sostenibles.',
                'og_image_path' => 'https://picsum.photos/1200/630?random=seo7',
                'og_type' => 'website',
                'twitter_title' => 'Paneles Solares - Imagen',
                'twitter_description' => 'Fotografía de paneles solares.',
                'twitter_image_path' => 'https://picsum.photos/1200/630?random=seo7',
                'twitter_card' => 'summary_large_image',
                'structured_data' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'ImageObject',
                    'name' => 'Paneles Solares',
                    'description' => 'Imagen de paneles solares en campo abierto',
                    'url' => 'https://picsum.photos/1920/1080?random=1',
                ],
                'focus_keyword' => 'paneles solares, imagen, energía renovable, fotografía',
                'additional_meta' => [
                    ['name' => 'image:alt', 'content' => 'Paneles solares instalados en campo abierto'],
                ],
                'language' => 'es',
            ],

            // SEO en inglés
            [
                'seoable_type' => 'App\\Models\\Page',
                'seoable_id' => $pages->skip(2)->first()?->id ?? 3,
                'meta_title' => 'Open Energy Coop - Renewable Energy Cooperative',
                'meta_description' => 'Join our renewable energy cooperative. We generate clean and sustainable electricity for a better future. Participate in the energy transition.',
                'canonical_url' => 'https://openenergycoop.com/en',
                'robots' => 'index,follow',
                'og_title' => 'Open Energy Coop - Renewable Energy',
                'og_description' => 'Renewable energy cooperative committed to sustainability and energy transition.',
                'og_image_path' => 'https://picsum.photos/1200/630?random=seo8',
                'og_type' => 'website',
                'twitter_title' => 'Open Energy Coop - Renewable Energy',
                'twitter_description' => 'Renewable energy cooperative for a sustainable future.',
                'twitter_image_path' => 'https://picsum.photos/1200/630?random=seo8',
                'twitter_card' => 'summary_large_image',
                'structured_data' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'Organization',
                    'name' => 'Open Energy Coop',
                    'description' => 'Renewable energy cooperative',
                    'url' => 'https://openenergycoop.com/en',
                ],
                'focus_keyword' => 'renewable energy, energy cooperative, sustainability',
                'additional_meta' => [
                    ['name' => 'author', 'content' => 'Open Energy Coop'],
                ],
                'language' => 'en',
            ],
        ];

        foreach ($seoData as $seo) {
            SeoMetaData::create($seo);
        }

        $this->command->info('✅ SeoMetaDataSeeder ejecutado correctamente');
        $this->command->info('📊 Metadatos SEO creados: ' . count($seoData));
        $this->command->info('🎯 Tipos de contenido: Páginas, Artículos, Heroes, TextContent, Banners, Imágenes');
        $this->command->info('🌐 Idiomas: Español (7) e Inglés (1)');
        $this->command->info('📱 Redes sociales: Open Graph y Twitter Cards configurados');
        $this->command->info('🔍 SEO: Meta títulos, descripciones y palabras clave optimizadas');
        $this->command->info('📊 Structured Data: Schema.org para mejor indexación');
        $this->command->info('🤖 Robots: Configuración de indexación para motores de búsqueda');
    }
}
