<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Document;
use App\Models\User;
use App\Models\Category;
use Carbon\Carbon;

class DocumentSeeder extends Seeder
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
            $this->command->warn('No hay usuarios en la base de datos. Creando documentos sin usuario asignado.');
            $users = collect([null]);
        }

        if ($categories->isEmpty()) {
            $this->command->warn('No hay categorías en la base de datos. Creando documentos sin categoría asignada.');
            $categories = collect([null]);
        }

        // Datos de documentos variados
        $documents = [
            // Documentos visibles
            [
                'title' => 'Manual de Usuario - Sistema de Gestión Energética',
                'description' => 'Guía completa para el uso del sistema de gestión energética de la cooperativa.',
                'file_path' => 'documents/manual-usuario-sistema-gestion-energetica.pdf',
                'file_type' => 'pdf',
                'file_size' => 2048576, // 2MB
                'mime_type' => 'application/pdf',
                'checksum' => 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6',
                'visible' => true,
                'category_id' => $categories->isNotEmpty() ? $categories->random()->id : null,
                'uploaded_by' => $users->isNotEmpty() ? $users->random()->id : null,
                'uploaded_at' => Carbon::now()->subDays(10),
                'download_count' => 45,
                'number_of_views' => 120,
                'version' => '1.2',
                'expires_at' => null,
                'requires_auth' => false,
                'allowed_roles' => null,
                'thumbnail_path' => 'thumbnails/manual-usuario-thumb.jpg',
                'language' => 'es',
                'is_draft' => false,
                'published_at' => Carbon::now()->subDays(10),
            ],
            [
                'title' => 'Política de Privacidad y Protección de Datos',
                'description' => 'Documento que describe cómo la cooperativa maneja y protege los datos personales de sus miembros.',
                'file_path' => 'documents/politica-privacidad-proteccion-datos.pdf',
                'file_type' => 'pdf',
                'file_size' => 1024768, // 1MB
                'mime_type' => 'application/pdf',
                'checksum' => 'b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7',
                'visible' => true,
                'category_id' => $categories->isNotEmpty() ? $categories->random()->id : null,
                'uploaded_by' => $users->isNotEmpty() ? $users->random()->id : null,
                'uploaded_at' => Carbon::now()->subDays(15),
                'download_count' => 23,
                'number_of_views' => 67,
                'version' => '2.0',
                'expires_at' => null,
                'requires_auth' => false,
                'allowed_roles' => null,
                'thumbnail_path' => 'thumbnails/politica-privacidad-thumb.jpg',
                'language' => 'es',
                'is_draft' => false,
                'published_at' => Carbon::now()->subDays(15),
            ],
            [
                'title' => 'Estadísticas de Producción Energética 2024',
                'description' => 'Reporte detallado de la producción energética de la cooperativa durante el año 2024.',
                'file_path' => 'documents/estadisticas-produccion-energetica-2024.xlsx',
                'file_type' => 'xlsx',
                'file_size' => 512384, // 500KB
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'checksum' => 'c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8',
                'visible' => true,
                'category_id' => $categories->isNotEmpty() ? $categories->random()->id : null,
                'uploaded_by' => $users->isNotEmpty() ? $users->random()->id : null,
                'uploaded_at' => Carbon::now()->subDays(5),
                'download_count' => 89,
                'number_of_views' => 234,
                'version' => '1.0',
                'expires_at' => null,
                'requires_auth' => true,
                'allowed_roles' => '["member", "admin"]',
                'thumbnail_path' => 'thumbnails/estadisticas-2024-thumb.jpg',
                'language' => 'es',
                'is_draft' => false,
                'published_at' => Carbon::now()->subDays(5),
            ],
            [
                'title' => 'Guía de Instalación de Paneles Solares',
                'description' => 'Manual técnico para la instalación correcta de sistemas de paneles solares residenciales.',
                'file_path' => 'documents/guia-instalacion-paneles-solares.pdf',
                'file_type' => 'pdf',
                'file_size' => 3072000, // 3MB
                'mime_type' => 'application/pdf',
                'checksum' => 'd4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9',
                'visible' => true,
                'category_id' => $categories->isNotEmpty() ? $categories->random()->id : null,
                'uploaded_by' => $users->isNotEmpty() ? $users->random()->id : null,
                'uploaded_at' => Carbon::now()->subDays(20),
                'download_count' => 156,
                'number_of_views' => 445,
                'version' => '1.5',
                'expires_at' => null,
                'requires_auth' => false,
                'allowed_roles' => null,
                'thumbnail_path' => 'thumbnails/guia-instalacion-thumb.jpg',
                'language' => 'es',
                'is_draft' => false,
                'published_at' => Carbon::now()->subDays(20),
            ],

            // Documentos no visibles
            [
                'title' => 'Borrador - Reglamento Interno de la Cooperativa',
                'description' => 'Borrador del reglamento interno que está siendo revisado por el comité legal.',
                'file_path' => 'documents/borrador-reglamento-interno-cooperativa.pdf',
                'file_type' => 'pdf',
                'file_size' => 1536000, // 1.5MB
                'mime_type' => 'application/pdf',
                'checksum' => 'e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0',
                'visible' => false,
                'category_id' => $categories->isNotEmpty() ? $categories->random()->id : null,
                'uploaded_by' => $users->isNotEmpty() ? $users->random()->id : null,
                'uploaded_at' => Carbon::now()->subDays(3),
                'download_count' => 0,
                'number_of_views' => 5,
                'version' => '0.8',
                'expires_at' => null,
                'requires_auth' => true,
                'allowed_roles' => '["admin", "legal"]',
                'thumbnail_path' => 'thumbnails/borrador-reglamento-thumb.jpg',
                'language' => 'es',
                'is_draft' => true,
                'published_at' => null,
            ],
            [
                'title' => 'Documento Confidencial - Estrategia Comercial 2025',
                'description' => 'Documento confidencial que contiene la estrategia comercial para el próximo año.',
                'file_path' => 'documents/confidencial-estrategia-comercial-2025.pdf',
                'file_type' => 'pdf',
                'file_size' => 768000, // 750KB
                'mime_type' => 'application/pdf',
                'checksum' => 'f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1',
                'visible' => false,
                'category_id' => $categories->isNotEmpty() ? $categories->random()->id : null,
                'uploaded_by' => $users->isNotEmpty() ? $users->random()->id : null,
                'uploaded_at' => Carbon::now()->subDays(1),
                'download_count' => 0,
                'number_of_views' => 2,
                'version' => '1.0',
                'expires_at' => Carbon::now()->addDays(30),
                'requires_auth' => true,
                'allowed_roles' => '["admin", "management"]',
                'thumbnail_path' => 'thumbnails/confidencial-estrategia-thumb.jpg',
                'language' => 'es',
                'is_draft' => true,
                'published_at' => null,
            ],

            // Documentos con expiración
            [
                'title' => 'Certificado de Calidad ISO 9001',
                'description' => 'Certificado de calidad ISO 9001 que expira en 6 meses.',
                'file_path' => 'documents/certificado-calidad-iso-9001.pdf',
                'file_type' => 'pdf',
                'file_size' => 256000, // 250KB
                'mime_type' => 'application/pdf',
                'checksum' => 'g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2',
                'visible' => true,
                'category_id' => $categories->isNotEmpty() ? $categories->random()->id : null,
                'uploaded_by' => $users->isNotEmpty() ? $users->random()->id : null,
                'uploaded_at' => Carbon::now()->subDays(30),
                'download_count' => 12,
                'number_of_views' => 34,
                'version' => '1.0',
                'expires_at' => Carbon::now()->addDays(180),
                'requires_auth' => false,
                'allowed_roles' => null,
                'thumbnail_path' => 'thumbnails/certificado-iso-thumb.jpg',
                'language' => 'es',
                'is_draft' => false,
                'published_at' => Carbon::now()->subDays(30),
            ],
            [
                'title' => 'Contrato de Servicio - Proveedor Energético',
                'description' => 'Contrato temporal con proveedor energético que expira en 3 meses.',
                'file_path' => 'documents/contrato-servicio-proveedor-energetico.pdf',
                'file_type' => 'pdf',
                'file_size' => 896000, // 875KB
                'mime_type' => 'application/pdf',
                'checksum' => 'h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3',
                'visible' => true,
                'category_id' => $categories->isNotEmpty() ? $categories->random()->id : null,
                'uploaded_by' => $users->isNotEmpty() ? $users->random()->id : null,
                'uploaded_at' => Carbon::now()->subDays(60),
                'download_count' => 8,
                'number_of_views' => 23,
                'version' => '1.0',
                'expires_at' => Carbon::now()->addDays(90),
                'requires_auth' => true,
                'allowed_roles' => '["admin", "legal"]',
                'thumbnail_path' => 'thumbnails/contrato-proveedor-thumb.jpg',
                'language' => 'es',
                'is_draft' => false,
                'published_at' => Carbon::now()->subDays(60),
            ],
        ];

        $this->command->info('Creando documentos...');

        $createdCount = 0;
        $totalDocuments = count($documents);

        foreach ($documents as $index => $documentData) {
            // Crear el documento
            Document::create($documentData);
            $createdCount++;

            // Mostrar progreso cada 2 documentos
            if (($index + 1) % 2 === 0) {
                $this->command->info("Progreso: {$createdCount}/{$totalDocuments} documentos creados");
            }
        }

        $this->command->info("✅ Se han creado {$createdCount} documentos exitosamente");

        // Mostrar estadísticas
        $this->showStatistics();
    }

    /**
     * Mostrar estadísticas de los documentos creados
     */
    private function showStatistics(): void
    {
        $this->command->info("\n📊 Estadísticas de Documentos:");
        
        $total = Document::count();
        $visible = Document::where('visible', true)->count();
        $hidden = Document::where('visible', false)->count();
        $published = Document::published()->count();
        $draft = Document::where('is_draft', true)->count();
        $expiring = Document::where('expires_at', '<=', Carbon::now()->addDays(30))->count();

        $this->command->info("• Total: {$total}");
        $this->command->info("• Visibles: {$visible}");
        $this->command->info("• Ocultos: {$hidden}");
        $this->command->info("• Publicados: {$published}");
        $this->command->info("• Borradores: {$draft}");
        $this->command->info("• Expiran en 30 días: {$expiring}");

        // Estadísticas por tipo de archivo
        $this->command->info("\n📈 Por tipo de archivo:");
        $fileTypes = Document::selectRaw('file_type, COUNT(*) as count')
            ->groupBy('file_type')
            ->pluck('count', 'file_type')
            ->toArray();

        foreach ($fileTypes as $fileType => $count) {
            $this->command->info("• {$fileType}: {$count}");
        }

        // Estadísticas por usuario
        $usersWithDocuments = Document::selectRaw('uploaded_by, COUNT(*) as count')
            ->whereNotNull('uploaded_by')
            ->groupBy('uploaded_by')
            ->get();

        if ($usersWithDocuments->isNotEmpty()) {
            $this->command->info("\n👥 Documentos por usuario:");
            foreach ($usersWithDocuments as $userDocument) {
                $user = User::find($userDocument->uploaded_by);
                $userName = $user ? $user->name : "Usuario ID {$userDocument->uploaded_by}";
                $this->command->info("• {$userName}: {$userDocument->count} documentos");
            }
        }
    }
}
