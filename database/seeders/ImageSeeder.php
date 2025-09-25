<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Image;
use App\Models\Category;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Str;

class ImageSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

        $organization = Organization::first();
        if (!$organization) {
            $organization = Organization::create([
                'name' => 'Open Energy Coop',
                'slug' => 'open-energy-coop',
                'description' => 'Organización principal',
                'is_active' => true,
            ]);
        }

        $categories = [
            Category::firstOrCreate(['name' => 'Energía Renovable'], [
                'slug' => 'energia-renovable',
                'description' => 'Imágenes relacionadas con energía renovable',
                'is_active' => true,
            ]),
            Category::firstOrCreate(['name' => 'Cooperativa'], [
                'slug' => 'cooperativa',
                'description' => 'Imágenes de la cooperativa y sus actividades',
                'is_active' => true,
            ]),
            Category::firstOrCreate(['name' => 'Tecnología'], [
                'slug' => 'tecnologia',
                'description' => 'Imágenes de tecnología y innovación',
                'is_active' => true,
            ]),
            Category::firstOrCreate(['name' => 'Sostenibilidad'], [
                'slug' => 'sostenibilidad',
                'description' => 'Imágenes sobre sostenibilidad y medio ambiente',
                'is_active' => true,
            ]),
        ];

        $imageUrls = [
            'https://picsum.photos/1920/1080?random=1',
            'https://picsum.photos/1920/1080?random=2',
            'https://picsum.photos/1920/1080?random=3',
            'https://picsum.photos/1920/1080?random=4',
            'https://picsum.photos/1920/1080?random=5',
            'https://picsum.photos/1920/1080?random=6',
        ];

        $images = [
            [
                'title' => 'Paneles Solares en Campo',
                'slug' => 'paneles-solares-campo',
                'description' => 'Instalación de paneles solares en un campo abierto.',
                'alt_text' => 'Paneles solares instalados en campo abierto',
                'filename' => 'paneles-solares-campo.jpg',
                'path' => 'images/energia-renovable/paneles-solares-campo.jpg',
                'url' => $imageUrls[0],
                'mime_type' => 'image/jpeg',
                'file_size' => 2048576,
                'width' => 1920,
                'height' => 1080,
                'metadata' => ['camera' => 'Canon EOS R5', 'iso' => 100],
                'category_id' => $categories[0]->id,
                'tags' => ['energía solar', 'paneles solares', 'energía renovable'],
                'organization_id' => $organization->id,
                'language' => 'es',
                'is_public' => true,
                'is_featured' => true,
                'status' => 'active',
                'seo_title' => 'Paneles Solares - Energía Renovable',
                'seo_description' => 'Imagen de paneles solares instalados en campo abierto.',
                'responsive_urls' => ['150x150' => $imageUrls[0], '300x300' => $imageUrls[0]],
                'download_count' => 45,
                'view_count' => 234,
                'last_used_at' => now()->subDays(2),
                'uploaded_by_user_id' => $user->id,
                'published_at' => now()->subDays(30),
            ],
            [
                'title' => 'Aerogeneradores en el Mar',
                'slug' => 'aerogeneradores-mar',
                'description' => 'Parque eólico marino con aerogeneradores.',
                'alt_text' => 'Aerogeneradores en parque eólico marino',
                'filename' => 'aerogeneradores-mar.jpg',
                'path' => 'images/energia-renovable/aerogeneradores-mar.jpg',
                'url' => $imageUrls[1],
                'mime_type' => 'image/jpeg',
                'file_size' => 1876543,
                'width' => 1920,
                'height' => 1080,
                'metadata' => ['camera' => 'Sony A7R IV', 'iso' => 200],
                'category_id' => $categories[0]->id,
                'tags' => ['energía eólica', 'aerogeneradores', 'parque eólico'],
                'organization_id' => $organization->id,
                'language' => 'es',
                'is_public' => true,
                'is_featured' => false,
                'status' => 'active',
                'seo_title' => 'Aerogeneradores Marinos - Energía Eólica',
                'seo_description' => 'Parque eólico marino con aerogeneradores.',
                'responsive_urls' => ['150x150' => $imageUrls[1], '300x300' => $imageUrls[1]],
                'download_count' => 32,
                'view_count' => 189,
                'last_used_at' => now()->subDays(5),
                'uploaded_by_user_id' => $user->id,
                'published_at' => now()->subDays(25),
            ],
            [
                'title' => 'Reunión de Cooperativa',
                'slug' => 'reunion-cooperativa',
                'description' => 'Miembros de la cooperativa reunidos en asamblea.',
                'alt_text' => 'Reunión de miembros de la cooperativa energética',
                'filename' => 'reunion-cooperativa.jpg',
                'path' => 'images/cooperativa/reunion-cooperativa.jpg',
                'url' => $imageUrls[2],
                'mime_type' => 'image/jpeg',
                'file_size' => 1654321,
                'width' => 1920,
                'height' => 1080,
                'metadata' => ['camera' => 'Nikon D850', 'iso' => 400],
                'category_id' => $categories[1]->id,
                'tags' => ['cooperativa', 'asamblea', 'democracia'],
                'organization_id' => $organization->id,
                'language' => 'es',
                'is_public' => true,
                'is_featured' => true,
                'status' => 'active',
                'seo_title' => 'Reunión Cooperativa Energética',
                'seo_description' => 'Asamblea de miembros de la cooperativa energética.',
                'responsive_urls' => ['150x150' => $imageUrls[2], '300x300' => $imageUrls[2]],
                'download_count' => 28,
                'view_count' => 156,
                'last_used_at' => now()->subDays(1),
                'uploaded_by_user_id' => $user->id,
                'published_at' => now()->subDays(20),
            ],
            [
                'title' => 'Smart Grid Tecnológico',
                'slug' => 'smart-grid-tecnologico',
                'description' => 'Red inteligente de distribución eléctrica.',
                'alt_text' => 'Sistema de red inteligente para distribución eléctrica',
                'filename' => 'smart-grid-tecnologico.jpg',
                'path' => 'images/tecnologia/smart-grid-tecnologico.jpg',
                'url' => $imageUrls[3],
                'mime_type' => 'image/jpeg',
                'file_size' => 2234567,
                'width' => 1920,
                'height' => 1080,
                'metadata' => ['camera' => 'Sony A7 III', 'iso' => 100],
                'category_id' => $categories[2]->id,
                'tags' => ['smart grid', 'tecnología', 'red eléctrica'],
                'organization_id' => $organization->id,
                'language' => 'es',
                'is_public' => true,
                'is_featured' => true,
                'status' => 'active',
                'seo_title' => 'Smart Grid - Red Eléctrica Inteligente',
                'seo_description' => 'Sistema de red inteligente para optimizar la distribución.',
                'responsive_urls' => ['150x150' => $imageUrls[3], '300x300' => $imageUrls[3]],
                'download_count' => 67,
                'view_count' => 312,
                'last_used_at' => now()->subHours(12),
                'uploaded_by_user_id' => $user->id,
                'published_at' => now()->subDays(10),
            ],
            [
                'title' => 'Bosque Sostenible',
                'slug' => 'bosque-sostenible',
                'description' => 'Bosque gestionado de forma sostenible.',
                'alt_text' => 'Bosque gestionado de forma sostenible',
                'filename' => 'bosque-sostenible.jpg',
                'path' => 'images/sostenibilidad/bosque-sostenible.jpg',
                'url' => $imageUrls[4],
                'mime_type' => 'image/jpeg',
                'file_size' => 2156789,
                'width' => 1920,
                'height' => 1080,
                'metadata' => ['camera' => 'Nikon D850', 'iso' => 100],
                'category_id' => $categories[3]->id,
                'tags' => ['bosque', 'sostenibilidad', 'medio ambiente'],
                'organization_id' => $organization->id,
                'language' => 'es',
                'is_public' => true,
                'is_featured' => true,
                'status' => 'active',
                'seo_title' => 'Bosque Sostenible - Conservación Ambiental',
                'seo_description' => 'Bosque gestionado de forma sostenible.',
                'responsive_urls' => ['150x150' => $imageUrls[4], '300x300' => $imageUrls[4]],
                'download_count' => 53,
                'view_count' => 267,
                'last_used_at' => now()->subDays(6),
                'uploaded_by_user_id' => $user->id,
                'published_at' => now()->subDays(12),
            ],
            [
                'title' => 'Planta Geotérmica',
                'slug' => 'planta-geotermica',
                'description' => 'Instalación geotérmica aprovechando el calor del subsuelo.',
                'alt_text' => 'Planta de energía geotérmica',
                'filename' => 'planta-geotermica.jpg',
                'path' => 'images/energia-renovable/planta-geotermica.jpg',
                'url' => $imageUrls[5],
                'mime_type' => 'image/jpeg',
                'file_size' => 1765432,
                'width' => 1920,
                'height' => 1080,
                'metadata' => ['camera' => 'Sony A7R IV', 'iso' => 100],
                'category_id' => $categories[0]->id,
                'tags' => ['geotérmica', 'energía renovable', 'subsuelo'],
                'organization_id' => $organization->id,
                'language' => 'es',
                'is_public' => false,
                'is_featured' => false,
                'status' => 'active',
                'seo_title' => 'Planta Geotérmica - Energía del Subsuelo',
                'seo_description' => 'Instalación geotérmica generando energía limpia.',
                'responsive_urls' => ['150x150' => $imageUrls[5], '300x300' => $imageUrls[5]],
                'download_count' => 0,
                'view_count' => 0,
                'last_used_at' => null,
                'uploaded_by_user_id' => $user->id,
                'published_at' => null,
            ],
        ];

        foreach ($images as $image) {
            Image::firstOrCreate(
                ['slug' => $image['slug']],
                $image
            );
        }

        $this->command->info('✅ ImageSeeder ejecutado correctamente');
        $this->command->info('📊 Imágenes creadas: ' . count($images));
        $this->command->info('📁 Categorías utilizadas: 4 (Energía Renovable, Cooperativa, Tecnología, Sostenibilidad)');
        $this->command->info('🎯 Tipos de imágenes: JPEG con metadatos completos');
        $this->command->info('📝 Estados: 5 públicas, 1 privada');
        $this->command->info('⭐ Destacadas: 4 imágenes marcadas como featured');
        $this->command->info('📊 Estadísticas: Diferentes contadores de descargas y visualizaciones');
        $this->command->info('🔧 Metadatos: Información de cámara y configuración');
        $this->command->info('📱 Responsive: URLs responsivas para diferentes tamaños');
    }
}
