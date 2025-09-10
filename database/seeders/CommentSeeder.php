<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\User;
use App\Models\Article;
use App\Models\Page;
use Carbon\Carbon;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener usuarios y modelos existentes
        $users = User::all();
        $articles = Article::all();
        $pages = Page::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('No hay usuarios en la base de datos. Creando comentarios sin usuario asignado.');
            $users = collect([null]);
        }

        if ($articles->isEmpty() && $pages->isEmpty()) {
            $this->command->warn('No hay artículos ni páginas en la base de datos. Creando comentarios sin modelo asignado.');
            $articles = collect([null]);
            $pages = collect([null]);
        }

        // Datos de comentarios variados
        $comments = [
            // Comentarios aprobados en artículos
            [
                'commentable_type' => Article::class,
                'commentable_id' => $articles->isNotEmpty() ? $articles->random()->id : null,
                'user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'author_name' => 'María García',
                'author_email' => 'maria.garcia@email.com',
                'content' => 'Excelente artículo sobre energía renovable. Me ha ayudado mucho a entender cómo funcionan los paneles solares. ¿Podrían publicar más información sobre las subvenciones disponibles?',
                'status' => 'approved',
                'parent_id' => null,
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'likes_count' => 5,
                'dislikes_count' => 0,
                'is_pinned' => false,
                'approved_at' => Carbon::now()->subDays(2),
                'approved_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
            ],
            [
                'commentable_type' => Article::class,
                'commentable_id' => $articles->isNotEmpty() ? $articles->random()->id : null,
                'user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'author_name' => 'Carlos López',
                'author_email' => 'carlos.lopez@email.com',
                'content' => 'Muy interesante la información sobre las comunidades energéticas. ¿Hay alguna en mi zona? Vivo en Madrid y me gustaría participar.',
                'status' => 'approved',
                'parent_id' => null,
                'ip_address' => '192.168.1.101',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'likes_count' => 3,
                'dislikes_count' => 1,
                'is_pinned' => false,
                'approved_at' => Carbon::now()->subDays(1),
                'approved_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
            ],
            [
                'commentable_type' => Article::class,
                'commentable_id' => $articles->isNotEmpty() ? $articles->random()->id : null,
                'user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'author_name' => 'Ana Martínez',
                'author_email' => 'ana.martinez@email.com',
                'content' => 'Gracias por compartir esta información tan valiosa. He estado pensando en instalar paneles solares en mi casa y este artículo me ha dado la información que necesitaba.',
                'status' => 'approved',
                'parent_id' => null,
                'ip_address' => '192.168.1.102',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
                'likes_count' => 7,
                'dislikes_count' => 0,
                'is_pinned' => true,
                'approved_at' => Carbon::now()->subHours(12),
                'approved_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
            ],

            // Respuestas a comentarios (comentarios anidados)
            [
                'commentable_type' => Article::class,
                'commentable_id' => $articles->isNotEmpty() ? $articles->random()->id : null,
                'user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'author_name' => 'Equipo Open Energy Coop',
                'author_email' => 'info@openenergycoop.com',
                'content' => 'Hola María, gracias por tu comentario. Próximamente publicaremos una guía completa sobre subvenciones disponibles en España. ¡Mantente atenta a nuestras publicaciones!',
                'status' => 'approved',
                'parent_id' => null, // Se establecerá después
                'ip_address' => '192.168.1.200',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'likes_count' => 2,
                'dislikes_count' => 0,
                'is_pinned' => false,
                'approved_at' => Carbon::now()->subDays(1),
                'approved_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
            ],
            [
                'commentable_type' => Article::class,
                'commentable_id' => $articles->isNotEmpty() ? $articles->random()->id : null,
                'user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'author_name' => 'Roberto Sánchez',
                'author_email' => 'roberto.sanchez@email.com',
                'content' => 'Carlos, hay varias comunidades energéticas en Madrid. Te recomiendo contactar con la cooperativa local de tu distrito. ¡Es una excelente iniciativa!',
                'status' => 'approved',
                'parent_id' => null, // Se establecerá después
                'ip_address' => '192.168.1.103',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15',
                'likes_count' => 4,
                'dislikes_count' => 0,
                'is_pinned' => false,
                'approved_at' => Carbon::now()->subHours(6),
                'approved_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
            ],

            // Comentarios pendientes
            [
                'commentable_type' => Article::class,
                'commentable_id' => $articles->isNotEmpty() ? $articles->random()->id : null,
                'user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'author_name' => 'Laura Fernández',
                'author_email' => 'laura.fernandez@email.com',
                'content' => 'Me gustaría saber más sobre los costes de mantenimiento de los paneles solares. ¿Cuánto cuesta aproximadamente el mantenimiento anual?',
                'status' => 'pending',
                'parent_id' => null,
                'ip_address' => '192.168.1.104',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'likes_count' => 0,
                'dislikes_count' => 0,
                'is_pinned' => false,
                'approved_at' => null,
                'approved_by_user_id' => null,
            ],
            [
                'commentable_type' => Page::class,
                'commentable_id' => $pages->isNotEmpty() ? $pages->random()->id : null,
                'user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'author_name' => 'Miguel Torres',
                'author_email' => 'miguel.torres@email.com',
                'content' => 'Excelente página web. La información está muy bien organizada y es fácil de navegar. ¿Planean añadir más funcionalidades en el futuro?',
                'status' => 'pending',
                'parent_id' => null,
                'ip_address' => '192.168.1.105',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'likes_count' => 0,
                'dislikes_count' => 0,
                'is_pinned' => false,
                'approved_at' => null,
                'approved_by_user_id' => null,
            ],

            // Comentarios rechazados
            [
                'commentable_type' => Article::class,
                'commentable_id' => $articles->isNotEmpty() ? $articles->random()->id : null,
                'user_id' => null,
                'author_name' => 'Usuario Anónimo',
                'author_email' => 'spam@fake.com',
                'content' => 'Este es un comentario spam con enlaces no deseados: http://spam-site.com',
                'status' => 'rejected',
                'parent_id' => null,
                'ip_address' => '192.168.1.999',
                'user_agent' => 'SpamBot/1.0',
                'likes_count' => 0,
                'dislikes_count' => 5,
                'is_pinned' => false,
                'approved_at' => null,
                'approved_by_user_id' => null,
            ],
            [
                'commentable_type' => Article::class,
                'commentable_id' => $articles->isNotEmpty() ? $articles->random()->id : null,
                'user_id' => null,
                'author_name' => 'Comentario Inapropiado',
                'author_email' => 'inappropriate@email.com',
                'content' => 'Contenido inapropiado que no cumple con las normas de la comunidad.',
                'status' => 'rejected',
                'parent_id' => null,
                'ip_address' => '192.168.1.998',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'likes_count' => 0,
                'dislikes_count' => 3,
                'is_pinned' => false,
                'approved_at' => null,
                'approved_by_user_id' => null,
            ],

            // Comentarios marcados como spam
            [
                'commentable_type' => Article::class,
                'commentable_id' => $articles->isNotEmpty() ? $articles->random()->id : null,
                'user_id' => null,
                'author_name' => 'Promoción Fake',
                'author_email' => 'promo@fake-promo.com',
                'content' => '¡Oferta especial! Compre paneles solares con 90% de descuento. ¡No se lo pierda! http://fake-promo.com',
                'status' => 'spam',
                'parent_id' => null,
                'ip_address' => '192.168.1.997',
                'user_agent' => 'SpamBot/2.0',
                'likes_count' => 0,
                'dislikes_count' => 8,
                'is_pinned' => false,
                'approved_at' => null,
                'approved_by_user_id' => null,
            ],

            // Comentarios en páginas
            [
                'commentable_type' => Page::class,
                'commentable_id' => $pages->isNotEmpty() ? $pages->random()->id : null,
                'user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'author_name' => 'Elena Ruiz',
                'author_email' => 'elena.ruiz@email.com',
                'content' => 'Me encanta la nueva sección de la página. La información sobre la cooperativa está muy clara y bien explicada.',
                'status' => 'approved',
                'parent_id' => null,
                'ip_address' => '192.168.1.106',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
                'likes_count' => 2,
                'dislikes_count' => 0,
                'is_pinned' => false,
                'approved_at' => Carbon::now()->subHours(3),
                'approved_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
            ],
            [
                'commentable_type' => Page::class,
                'commentable_id' => $pages->isNotEmpty() ? $pages->random()->id : null,
                'user_id' => $users->isNotEmpty() ? $users->random()->id : null,
                'author_name' => 'David Moreno',
                'author_email' => 'david.moreno@email.com',
                'content' => '¿Podrían añadir más información sobre los beneficios ambientales de unirse a la cooperativa? Sería muy útil para convencer a más personas.',
                'status' => 'approved',
                'parent_id' => null,
                'ip_address' => '192.168.1.107',
                'user_agent' => 'Mozilla/5.0 (Android 11; Mobile; rv:68.0) Gecko/68.0 Firefox/88.0',
                'likes_count' => 6,
                'dislikes_count' => 0,
                'is_pinned' => false,
                'approved_at' => Carbon::now()->subHours(1),
                'approved_by_user_id' => $users->isNotEmpty() ? $users->random()->id : null,
            ],
        ];

        $this->command->info('Creando comentarios...');

        $createdCount = 0;
        $totalComments = count($comments);

        foreach ($comments as $index => $commentData) {
            // Crear el comentario
            $comment = Comment::create($commentData);
            $createdCount++;

            // Mostrar progreso cada 3 comentarios
            if (($index + 1) % 3 === 0) {
                $this->command->info("Progreso: {$createdCount}/{$totalComments} comentarios creados");
            }
        }

        // Establecer relaciones padre-hijo para comentarios anidados
        $this->establishParentChildRelationships();

        $this->command->info("✅ Se han creado {$createdCount} comentarios exitosamente");

        // Mostrar estadísticas
        $this->showStatistics();
    }

    /**
     * Establecer relaciones padre-hijo para comentarios anidados
     */
    private function establishParentChildRelationships(): void
    {
        // Buscar comentarios que necesitan ser respuestas
        $mariaComment = Comment::where('author_name', 'María García')->first();
        $carlosComment = Comment::where('author_name', 'Carlos López')->first();
        
        if ($mariaComment) {
            // Establecer respuesta del equipo como hijo del comentario de María
            $equipoResponse = Comment::where('author_name', 'Equipo Open Energy Coop')->first();
            if ($equipoResponse) {
                $equipoResponse->update(['parent_id' => $mariaComment->id]);
            }
        }

        if ($carlosComment) {
            // Establecer respuesta de Roberto como hijo del comentario de Carlos
            $robertoResponse = Comment::where('author_name', 'Roberto Sánchez')->first();
            if ($robertoResponse) {
                $robertoResponse->update(['parent_id' => $carlosComment->id]);
            }
        }
    }

    /**
     * Mostrar estadísticas de los comentarios creados
     */
    private function showStatistics(): void
    {
        $this->command->info("\n📊 Estadísticas de Comentarios:");
        
        $total = Comment::count();
        $approved = Comment::where('status', 'approved')->count();
        $pending = Comment::where('status', 'pending')->count();
        $rejected = Comment::where('status', 'rejected')->count();
        $spam = Comment::where('status', 'spam')->count();
        $pinned = Comment::where('is_pinned', true)->count();
        $replies = Comment::whereNotNull('parent_id')->count();

        $this->command->info("• Total: {$total}");
        $this->command->info("• Aprobados: {$approved}");
        $this->command->info("• Pendientes: {$pending}");
        $this->command->info("• Rechazados: {$rejected}");
        $this->command->info("• Spam: {$spam}");
        $this->command->info("• Fijados: {$pinned}");
        $this->command->info("• Respuestas: {$replies}");

        // Estadísticas por estado
        $this->command->info("\n📈 Por estado:");
        $statuses = Comment::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        foreach ($statuses as $status => $count) {
            $this->command->info("• {$status}: {$count}");
        }

        // Estadísticas por tipo de modelo
        $this->command->info("\n📝 Por tipo de contenido:");
        $contentTypes = Comment::selectRaw('commentable_type, COUNT(*) as count')
            ->groupBy('commentable_type')
            ->pluck('count', 'commentable_type')
            ->toArray();

        foreach ($contentTypes as $type => $count) {
            $typeName = class_basename($type);
            $this->command->info("• {$typeName}: {$count}");
        }

        // Estadísticas por usuario
        $usersWithComments = Comment::selectRaw('user_id, COUNT(*) as count')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->get();

        if ($usersWithComments->isNotEmpty()) {
            $this->command->info("\n👥 Comentarios por usuario:");
            foreach ($usersWithComments as $userComment) {
                $user = User::find($userComment->user_id);
                $userName = $user ? $user->name : "Usuario ID {$userComment->user_id}";
                $this->command->info("• {$userName}: {$userComment->count} comentarios");
            }
        }
    }
}
