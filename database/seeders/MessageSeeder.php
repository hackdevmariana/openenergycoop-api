<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Message;
use App\Models\User;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('💬 Creando mensajes para el sistema de comunicación...');
        
        // Limpiar mensajes existentes
        Message::query()->delete();
        
        // Obtener usuarios y organizaciones disponibles
        $users = User::all();
        $organizations = Organization::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. No se pueden crear mensajes.');
            return;
        }
        
        $this->command->info("👥 Usuarios disponibles: {$users->count()}");
        $this->command->info("🏢 Organizaciones disponibles: {$organizations->count()}");
        
        // Crear mensajes para diferentes tipos y escenarios
        $this->createContactMessages($users, $organizations);
        $this->createSupportMessages($users, $organizations);
        $this->createComplaintMessages($users, $organizations);
        $this->createSuggestionMessages($users, $organizations);
        $this->createUrgentMessages($users, $organizations);
        $this->createSpamMessages($users, $organizations);
        
        $this->command->info('✅ MessageSeeder completado. Se crearon ' . Message::count() . ' mensajes.');
    }
    
    private function createContactMessages($users, $organizations): void
    {
        $this->command->info('📞 Creando mensajes de contacto...');
        
        $contactSubjects = [
            'Consulta sobre servicios de energía renovable',
            'Información sobre membresía en la cooperativa',
            'Solicitud de visita técnica',
            'Consulta sobre facturación',
            'Información sobre eventos y talleres',
            'Solicitud de documentación',
            'Consulta sobre proyectos comunitarios',
            'Información sobre inversiones en energía verde'
        ];
        
        foreach ($contactSubjects as $subject) {
            for ($i = 0; $i < rand(2, 5); $i++) {
                $organization = $organizations->random();
                $user = $users->random();
                
                $message = $this->generateContactMessage($subject);
                $priority = $this->getPriorityForSubject($subject);
                $status = $this->getStatusForPriority($priority);
                
                $messageData = [
                    'name' => fake()->name(),
                    'email' => fake()->safeEmail(),
                    'phone' => fake()->optional(0.8)->phoneNumber(),
                    'subject' => $subject,
                    'message' => $message,
                    'status' => $status,
                    'priority' => $priority,
                    'message_type' => 'contact',
                    'ip_address' => $this->generateRandomIP(),
                    'user_agent' => $this->generateRandomUserAgent(),
                    'organization_id' => $organization->id,
                    'created_at' => Carbon::now()->subDays(rand(0, 30)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 30))
                ];
                
                // Agregar campos adicionales según el estado
                if ($status === 'read') {
                    $messageData['read_at'] = Carbon::now()->subDays(rand(1, 7));
                }
                
                if ($status === 'replied') {
                    $messageData['read_at'] = Carbon::now()->subDays(rand(3, 10));
                    $messageData['replied_at'] = Carbon::now()->subDays(rand(1, 5));
                    $messageData['replied_by_user_id'] = $users->random()->id;
                    $messageData['internal_notes'] = fake()->optional(0.7)->sentence();
                }
                
                if ($status === 'archived') {
                    $messageData['read_at'] = Carbon::now()->subDays(rand(10, 20));
                    $messageData['replied_at'] = Carbon::now()->subDays(rand(5, 15));
                    $messageData['replied_by_user_id'] = $users->random()->id;
                    $messageData['internal_notes'] = 'Mensaje procesado y archivado';
                }
                
                // Asignar a un usuario en algunos casos
                if (rand(1, 10) <= 4) {
                    $messageData['assigned_to_user_id'] = $users->random()->id;
                }
                
                Message::create($messageData);
            }
        }
        
        $this->command->info("📞 Mensajes de contacto creados");
    }
    
    private function createSupportMessages($users, $organizations): void
    {
        $this->command->info('🆘 Creando mensajes de soporte...');
        
        $supportSubjects = [
            'Problema con el portal de cliente',
            'Error en la facturación mensual',
            'No puedo acceder a mi cuenta',
            'Problema con la app móvil',
            'Error en el sistema de monitoreo',
            'Problema con el medidor inteligente',
            'No recibo notificaciones por email',
            'Problema con el panel de control'
        ];
        
        foreach ($supportSubjects as $subject) {
            for ($i = 0; $i < rand(1, 4); $i++) {
                $organization = $organizations->random();
                $user = $users->random();
                
                $message = $this->generateSupportMessage($subject);
                $priority = $this->getPriorityForSupport($subject);
                $status = $this->getStatusForPriority($priority);
                
                $messageData = [
                    'name' => fake()->name(),
                    'email' => fake()->safeEmail(),
                    'phone' => fake()->phoneNumber(),
                    'subject' => $subject,
                    'message' => $message,
                    'status' => $status,
                    'priority' => $priority,
                    'message_type' => 'support',
                    'ip_address' => $this->generateRandomIP(),
                    'user_agent' => $this->generateRandomUserAgent(),
                    'organization_id' => $organization->id,
                    'created_at' => Carbon::now()->subDays(rand(0, 14)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 14))
                ];
                
                // Agregar campos adicionales según el estado
                if ($status === 'read') {
                    $messageData['read_at'] = Carbon::now()->subDays(rand(1, 3));
                }
                
                if ($status === 'replied') {
                    $messageData['read_at'] = Carbon::now()->subDays(rand(2, 5));
                    $messageData['replied_at'] = Carbon::now()->subDays(rand(1, 2));
                    $messageData['replied_by_user_id'] = $users->random()->id;
                    $messageData['internal_notes'] = fake()->sentence();
                }
                
                // Asignar a un usuario técnico
                $messageData['assigned_to_user_id'] = $users->random()->id;
                
                Message::create($messageData);
            }
        }
        
        $this->command->info("🆘 Mensajes de soporte creados");
    }
    
    private function createComplaintMessages($users, $organizations): void
    {
        $this->command->info('😠 Creando mensajes de quejas...');
        
        $complaintSubjects = [
            'Factura incorrecta recibida',
            'Mala atención al cliente',
            'Retraso en la resolución de problemas',
            'Información confusa en el portal',
            'Problema no resuelto después de varios contactos',
            'Cobro duplicado en mi cuenta',
            'Falta de transparencia en los precios',
            'Problema con el servicio técnico'
        ];
        
        foreach ($complaintSubjects as $subject) {
            for ($i = 0; $i < rand(1, 3); $i++) {
                $organization = $organizations->random();
                $user = $users->random();
                
                $message = $this->generateComplaintMessage($subject);
                $priority = $this->getPriorityForComplaint($subject);
                $status = $this->getStatusForPriority($priority);
                
                $messageData = [
                    'name' => fake()->name(),
                    'email' => fake()->safeEmail(),
                    'phone' => fake()->phoneNumber(),
                    'subject' => $subject,
                    'message' => $message,
                    'status' => $status,
                    'priority' => $priority,
                    'message_type' => 'complaint',
                    'ip_address' => $this->generateRandomIP(),
                    'user_agent' => $this->generateRandomUserAgent(),
                    'organization_id' => $organization->id,
                    'created_at' => Carbon::now()->subDays(rand(0, 21)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 21))
                ];
                
                // Agregar campos adicionales según el estado
                if ($status === 'read') {
                    $messageData['read_at'] = Carbon::now()->subDays(rand(1, 5));
                }
                
                if ($status === 'replied') {
                    $messageData['read_at'] = Carbon::now()->subDays(rand(2, 7));
                    $messageData['replied_at'] = Carbon::now()->subDays(rand(1, 3));
                    $messageData['replied_by_user_id'] = $users->random()->id;
                    $messageData['internal_notes'] = 'Queja procesada y respuesta enviada';
                }
                
                // Asignar a un usuario senior para manejo
                $messageData['assigned_to_user_id'] = $users->random()->id;
                
                Message::create($messageData);
            }
        }
        
        $this->command->info("😠 Mensajes de quejas creados");
    }
    
    private function createSuggestionMessages($users, $organizations): void
    {
        $this->command->info('💡 Creando mensajes de sugerencias...');
        
        $suggestionSubjects = [
            'Mejora en el portal de cliente',
            'Nueva funcionalidad para la app móvil',
            'Sugerencia para el sistema de facturación',
            'Mejora en las notificaciones',
            'Nueva característica para el monitoreo',
            'Sugerencia para eventos comunitarios',
            'Mejora en la comunicación',
            'Nueva funcionalidad para el panel de control'
        ];
        
        foreach ($suggestionSubjects as $subject) {
            for ($i = 0; $i < rand(1, 3); $i++) {
                $organization = $organizations->random();
                $user = $users->random();
                
                $message = $this->generateSuggestionMessage($subject);
                $priority = 'normal'; // Las sugerencias suelen ser prioridad normal
                $status = $this->getStatusForPriority($priority);
                
                $messageData = [
                    'name' => fake()->name(),
                    'email' => fake()->safeEmail(),
                    'phone' => fake()->optional(0.6)->phoneNumber(),
                    'subject' => $subject,
                    'message' => $message,
                    'status' => $status,
                    'priority' => $priority,
                    'message_type' => 'suggestion',
                    'ip_address' => $this->generateRandomIP(),
                    'user_agent' => $this->generateRandomUserAgent(),
                    'organization_id' => $organization->id,
                    'created_at' => Carbon::now()->subDays(rand(0, 45)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 45))
                ];
                
                // Agregar campos adicionales según el estado
                if ($status === 'read') {
                    $messageData['read_at'] = Carbon::now()->subDays(rand(1, 10));
                }
                
                if ($status === 'replied') {
                    $messageData['read_at'] = Carbon::now()->subDays(rand(5, 15));
                    $messageData['replied_at'] = Carbon::now()->subDays(rand(1, 7));
                    $messageData['replied_by_user_id'] = $users->random()->id;
                    $messageData['internal_notes'] = 'Sugerencia evaluada y respuesta enviada';
                }
                
                Message::create($messageData);
            }
        }
        
        $this->command->info("💡 Mensajes de sugerencias creados");
    }
    
    private function createUrgentMessages($users, $organizations): void
    {
        $this->command->info('🚨 Creando mensajes urgentes...');
        
        $urgentSubjects = [
            'Corte de energía no programado',
            'Problema de seguridad en el sistema',
            'Fallo crítico en el portal',
            'Problema de facturación masivo',
            'Error en el sistema de monitoreo',
            'Problema de acceso a cuentas',
            'Fallo en la app móvil',
            'Problema de comunicación del sistema'
        ];
        
        foreach ($urgentSubjects as $subject) {
            for ($i = 0; $i < rand(1, 2); $i++) {
                $organization = $organizations->random();
                $user = $users->random();
                
                $message = $this->generateUrgentMessage($subject);
                $priority = 'urgent';
                $status = $this->getStatusForPriority($priority);
                
                $messageData = [
                    'name' => fake()->name(),
                    'email' => fake()->safeEmail(),
                    'phone' => fake()->phoneNumber(),
                    'subject' => $subject,
                    'message' => $message,
                    'status' => $status,
                    'priority' => $priority,
                    'message_type' => 'support',
                    'ip_address' => $this->generateRandomIP(),
                    'user_agent' => $this->generateRandomUserAgent(),
                    'organization_id' => $organization->id,
                    'created_at' => Carbon::now()->subDays(rand(0, 3)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 3))
                ];
                
                // Los mensajes urgentes suelen ser leídos y respondidos rápidamente
                if ($status === 'read') {
                    $messageData['read_at'] = Carbon::now()->subDays(rand(0, 1));
                }
                
                if ($status === 'replied') {
                    $messageData['read_at'] = Carbon::now()->subDays(rand(0, 1));
                    $messageData['replied_at'] = Carbon::now()->subDays(rand(0, 1));
                    $messageData['replied_by_user_id'] = $users->random()->id;
                    $messageData['internal_notes'] = 'Problema urgente resuelto';
                }
                
                // Asignar inmediatamente a un usuario técnico
                $messageData['assigned_to_user_id'] = $users->random()->id;
                
                Message::create($messageData);
            }
        }
        
        $this->command->info("🚨 Mensajes urgentes creados");
    }
    
    private function createSpamMessages($users, $organizations): void
    {
        $this->command->info('🚫 Creando mensajes de spam...');
        
        $spamSubjects = [
            'Buy cheap viagra now!',
            'Make money fast from home!',
            'Free gift card! Click here!',
            'You won a prize! Claim now!',
            'Investment opportunity! Send money!',
            'Lose weight fast! Miracle pill!',
            'Free iPhone! Click to claim!',
            'Make $1000 daily! Join now!'
        ];
        
        foreach ($spamSubjects as $subject) {
            for ($i = 0; $i < rand(1, 2); $i++) {
                $organization = $organizations->random();
                $user = $users->random();
                
                $message = $this->generateSpamMessage($subject);
                $priority = 'low';
                $status = 'spam';
                
                $messageData = [
                    'name' => fake()->randomElement(['Spam User', 'Fake Name', 'Bot User', 'Test User']),
                    'email' => fake()->randomElement(['spam@fake.com', 'bot@test.com', 'fake@spam.com', 'test@bot.com']),
                    'phone' => fake()->optional(0.3)->phoneNumber(),
                    'subject' => $subject,
                    'message' => $message,
                    'status' => $status,
                    'priority' => $priority,
                    'message_type' => 'contact',
                    'ip_address' => $this->generateRandomIP(),
                    'user_agent' => fake()->randomElement([
                        'Mozilla/5.0 (compatible; SpamBot/1.0)',
                        'Bot/1.0 (Spam)',
                        'Fake User Agent',
                        'Spam Crawler'
                    ]),
                    'organization_id' => $organization->id,
                    'read_at' => Carbon::now()->subDays(rand(1, 7)),
                    'internal_notes' => 'Marcado como spam debido a contenido sospechoso',
                    'created_at' => Carbon::now()->subDays(rand(0, 14)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 14))
                ];
                
                Message::create($messageData);
            }
        }
        
        $this->command->info("🚫 Mensajes de spam creados");
    }
    
    private function generateContactMessage(string $subject): string
    {
        $templates = [
            "Hola, me gustaría obtener más información sobre {$subject}. ¿Podrían contactarme para discutir las opciones disponibles?",
            "Buenos días, estoy interesado en {$subject} y me gustaría saber más detalles. ¿Cuándo podrían agendar una cita?",
            "Hola, tengo algunas preguntas sobre {$subject}. ¿Podrían proporcionarme más información al respecto?",
            "Estimados, me interesa mucho {$subject} y quisiera conocer más sobre los servicios que ofrecen.",
            "Buenas tardes, estoy buscando información sobre {$subject}. ¿Podrían enviarme material informativo?"
        ];
        
        return $templates[array_rand($templates)];
    }
    
    private function generateSupportMessage(string $subject): string
    {
        $templates = [
            "Hola, estoy experimentando problemas con {$subject}. He intentado varias soluciones pero no funciona. Necesito ayuda urgente.",
            "Buenos días, tengo un problema técnico con {$subject}. El error que aparece es: [descripción del error]. ¿Podrían ayudarme?",
            "Hola, {$subject} no está funcionando correctamente desde hace 2 días. ¿Podrían revisar qué está pasando?",
            "Estimados, tengo un problema con {$subject} que me está afectando en mi trabajo diario. Necesito que lo resuelvan pronto.",
            "Buenas tardes, {$subject} presenta un comportamiento extraño. ¿Podrían investigar qué está causando este problema?"
        ];
        
        return $templates[array_rand($templates)];
    }
    
    private function generateComplaintMessage(string $subject): string
    {
        $templates = [
            "Estoy muy molesto con {$subject}. He contactado varias veces y nadie me ha dado una solución. Esto es inaceptable.",
            "Hola, quiero presentar una queja formal sobre {$subject}. El servicio ha sido pésimo y necesito que se resuelva inmediatamente.",
            "Estimados, estoy completamente insatisfecho con {$subject}. He sido cliente por años y nunca había tenido tan mal servicio.",
            "Buenos días, quiero quejarme formalmente sobre {$subject}. Esto me está causando problemas y pérdidas económicas.",
            "Hola, tengo una queja muy seria sobre {$subject}. El trato que he recibido ha sido deplorable y exijo una explicación."
        ];
        
        return $templates[array_rand($templates)];
    }
    
    private function generateSuggestionMessage(string $subject): string
    {
        $templates = [
            "Hola, tengo una sugerencia para mejorar {$subject}. Creo que implementando [idea] podrían mejorar significativamente la experiencia del usuario.",
            "Buenos días, me gustaría sugerir una mejora para {$subject}. He notado que [observación] y creo que podrían optimizarlo.",
            "Estimados, tengo una propuesta para {$subject}. Como usuario frecuente, creo que [sugerencia] sería muy beneficioso.",
            "Hola, quiero compartir una idea para {$subject}. Creo que [propuesta] haría el servicio mucho más eficiente.",
            "Buenas tardes, tengo una sugerencia constructiva para {$subject}. Creo que [mejora] beneficiaría a todos los usuarios."
        ];
        
        return $templates[array_rand($templates)];
    }
    
    private function generateUrgentMessage(string $subject): string
    {
        $templates = [
            "URGENTE: {$subject} está causando problemas críticos. Necesito asistencia inmediata antes de que se agrave la situación.",
            "EMERGENCIA: {$subject} ha fallado completamente. Esto está afectando mi negocio y necesito que lo resuelvan YA.",
            "CRÍTICO: {$subject} presenta un fallo que requiere atención inmediata. No puedo continuar trabajando sin esto.",
            "URGENTE: {$subject} está fuera de servicio. Necesito que lo restauren lo antes posible.",
            "EMERGENCIA: {$subject} ha dejado de funcionar. Esto es crítico para mis operaciones diarias."
        ];
        
        return $templates[array_rand($templates)];
    }
    
    private function generateSpamMessage(string $subject): string
    {
        $templates = [
            "{$subject} Click here: http://spam.com and http://fake.com for amazing deals!",
            "{$subject} Visit: http://scam.com and make money fast!",
            "{$subject} Free gift at: http://fake.com and http://malicious.com",
            "{$subject} Amazing opportunity at: http://spam.com and http://scam.com",
            "{$subject} Don't miss out! Click: http://fake.com and http://spam.com"
        ];
        
        return $templates[array_rand($templates)];
    }
    
    private function getPriorityForSubject(string $subject): string
    {
        $highPriorityKeywords = ['urgente', 'crítico', 'emergencia', 'inmediato', 'problema'];
        $normalPriorityKeywords = ['consulta', 'información', 'solicitud', 'evento', 'taller'];
        
        foreach ($highPriorityKeywords as $keyword) {
            if (stripos($subject, $keyword) !== false) {
                return 'high';
            }
        }
        
        foreach ($normalPriorityKeywords as $keyword) {
            if (stripos($subject, $keyword) !== false) {
                return 'normal';
            }
        }
        
        return 'low';
    }
    
    private function getPriorityForSupport(string $subject): string
    {
        $urgentKeywords = ['error', 'problema', 'fallo', 'no funciona', 'acceso'];
        $highKeywords = ['portal', 'app', 'sistema', 'monitoreo'];
        
        foreach ($urgentKeywords as $keyword) {
            if (stripos($subject, $keyword) !== false) {
                return 'urgent';
            }
        }
        
        foreach ($highKeywords as $keyword) {
            if (stripos($subject, $keyword) !== false) {
                return 'high';
            }
        }
        
        return 'normal';
    }
    
    private function getPriorityForComplaint(string $subject): string
    {
        $urgentKeywords = ['factura', 'cobro', 'duplicado', 'crítico'];
        $highKeywords = ['atención', 'resolución', 'transparencia', 'servicio'];
        
        foreach ($urgentKeywords as $keyword) {
            if (stripos($subject, $keyword) !== false) {
                return 'urgent';
            }
        }
        
        foreach ($highKeywords as $keyword) {
            if (stripos($subject, $keyword) !== false) {
                return 'high';
            }
        }
        
        return 'normal';
    }
    
    private function getStatusForPriority(string $priority): string
    {
        $statuses = [
            'urgent' => ['pending', 'read', 'replied'],
            'high' => ['pending', 'read', 'replied', 'archived'],
            'normal' => ['pending', 'read', 'replied', 'archived'],
            'low' => ['pending', 'read', 'archived']
        ];
        
        $weights = [
            'urgent' => [20, 30, 50], // 50% respondidos
            'high' => [25, 35, 30, 10], // 30% respondidos
            'normal' => [30, 40, 20, 10], // 20% respondidos
            'low' => [40, 40, 20] // 20% archivados
        ];
        
        $statusList = $statuses[$priority];
        $weightList = $weights[$priority];
        
        return $statusList[array_rand($statusList)];
    }
    
    private function generateRandomIP(): string
    {
        $ips = [
            '192.168.1.' . rand(1, 254),
            '10.0.0.' . rand(1, 254),
            '172.16.' . rand(0, 31) . '.' . rand(1, 254),
            '203.0.113.' . rand(1, 254),
            '198.51.100.' . rand(1, 254),
            '127.0.0.' . rand(1, 254)
        ];
        
        return $ips[array_rand($ips)];
    }
    
    private function generateRandomUserAgent(): string
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Android 14; Mobile; rv:120.0) Gecko/120.0 Firefox/120.0'
        ];
        
        return $userAgents[array_rand($userAgents)];
    }
}
