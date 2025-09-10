<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener usuarios existentes
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('No hay usuarios en la base de datos. Creando notificaciones sin usuario asignado.');
            $users = collect([null]);
        }

        // Datos de notificaciones variadas
        $notifications = [
            // Notificaciones de éxito
            [
                'title' => 'Instalación Completada',
                'message' => 'Tu sistema de paneles solares ha sido instalado exitosamente. Ya puedes comenzar a generar energía renovable.',
                'type' => Notification::TYPE_SUCCESS,
                'read_at' => null,
                'delivered_at' => Carbon::now()->subHours(2),
            ],
            [
                'title' => 'Pago Procesado',
                'message' => 'Tu pago mensual de €45.50 ha sido procesado correctamente. Gracias por tu contribución a la energía sostenible.',
                'type' => Notification::TYPE_SUCCESS,
                'read_at' => Carbon::now()->subHours(1),
                'delivered_at' => Carbon::now()->subHours(3),
            ],
            [
                'title' => 'Meta Alcanzada',
                'message' => '¡Felicidades! Has alcanzado tu meta mensual de producción de energía. Has generado 850 kWh este mes.',
                'type' => Notification::TYPE_SUCCESS,
                'read_at' => Carbon::now()->subMinutes(30),
                'delivered_at' => Carbon::now()->subHours(1),
            ],

            // Notificaciones de información
            [
                'title' => 'Nuevo Evento Disponible',
                'message' => 'Te invitamos al próximo taller sobre eficiencia energética el próximo sábado a las 10:00 AM.',
                'type' => Notification::TYPE_INFO,
                'read_at' => null,
                'delivered_at' => Carbon::now()->subDays(1),
            ],
            [
                'title' => 'Actualización del Sistema',
                'message' => 'Hemos actualizado nuestra plataforma con nuevas funcionalidades. Descubre las mejoras en tu panel de control.',
                'type' => Notification::TYPE_INFO,
                'read_at' => Carbon::now()->subDays(2),
                'delivered_at' => Carbon::now()->subDays(2),
            ],
            [
                'title' => 'Reporte Mensual Disponible',
                'message' => 'Tu reporte de consumo y producción del mes pasado ya está disponible. Revisa tu progreso energético.',
                'type' => Notification::TYPE_INFO,
                'read_at' => null,
                'delivered_at' => Carbon::now()->subDays(3),
            ],

            // Notificaciones de advertencia
            [
                'title' => 'Consumo Elevado Detectado',
                'message' => 'Hemos detectado un consumo de energía superior al habitual en tu hogar. Revisa tus electrodomésticos.',
                'type' => Notification::TYPE_WARNING,
                'read_at' => Carbon::now()->subHours(4),
                'delivered_at' => Carbon::now()->subHours(5),
            ],
            [
                'title' => 'Mantenimiento Programado',
                'message' => 'Tu sistema de paneles solares requiere mantenimiento preventivo. Contacta con nuestro equipo técnico.',
                'type' => Notification::TYPE_WARNING,
                'read_at' => null,
                'delivered_at' => Carbon::now()->subDays(1),
            ],
            [
                'title' => 'Factura Pendiente',
                'message' => 'Tienes una factura pendiente de pago. Por favor, realiza el pago antes del vencimiento para evitar interrupciones.',
                'type' => Notification::TYPE_WARNING,
                'read_at' => Carbon::now()->subDays(1),
                'delivered_at' => Carbon::now()->subDays(2),
            ],

            // Notificaciones de alerta
            [
                'title' => 'Corte de Energía Detectado',
                'message' => 'Se ha detectado una interrupción en el suministro eléctrico en tu zona. Estamos trabajando para restablecer el servicio.',
                'type' => Notification::TYPE_ALERT,
                'read_at' => Carbon::now()->subMinutes(15),
                'delivered_at' => Carbon::now()->subMinutes(20),
            ],
            [
                'title' => 'Sistema en Modo de Respaldo',
                'message' => 'Tu sistema ha activado el modo de respaldo. La energía se está suministrando desde las baterías de almacenamiento.',
                'type' => Notification::TYPE_ALERT,
                'read_at' => null,
                'delivered_at' => Carbon::now()->subHours(1),
            ],
            [
                'title' => 'Alta Demanda de Energía',
                'message' => 'Se prevé una alta demanda de energía en las próximas horas. Considera reducir el consumo no esencial.',
                'type' => Notification::TYPE_ALERT,
                'read_at' => Carbon::now()->subHours(2),
                'delivered_at' => Carbon::now()->subHours(3),
            ],

            // Notificaciones de error
            [
                'title' => 'Error en el Sistema de Monitoreo',
                'message' => 'Hemos detectado un problema técnico en el sistema de monitoreo de tu instalación. Nuestro equipo técnico está investigando.',
                'type' => Notification::TYPE_ERROR,
                'read_at' => Carbon::now()->subHours(6),
                'delivered_at' => Carbon::now()->subHours(7),
            ],
            [
                'title' => 'Fallo en la Conexión',
                'message' => 'No se ha podido establecer conexión con tu medidor inteligente. Verifica la conexión a internet.',
                'type' => Notification::TYPE_ERROR,
                'read_at' => null,
                'delivered_at' => Carbon::now()->subDays(1),
            ],
            [
                'title' => 'Error en el Procesamiento de Pago',
                'message' => 'Ha ocurrido un error al procesar tu último pago. Por favor, verifica los datos de tu tarjeta y vuelve a intentar.',
                'type' => Notification::TYPE_ERROR,
                'read_at' => Carbon::now()->subDays(2),
                'delivered_at' => Carbon::now()->subDays(2),
            ],

            // Notificaciones adicionales
            [
                'title' => 'Bienvenido a la Cooperativa',
                'message' => '¡Bienvenido a Open Energy Coop! Estamos emocionados de tenerte como parte de nuestra comunidad de energía renovable.',
                'type' => Notification::TYPE_SUCCESS,
                'read_at' => Carbon::now()->subDays(7),
                'delivered_at' => Carbon::now()->subDays(7),
            ],
            [
                'title' => 'Nueva Funcionalidad: Trading de Energía',
                'message' => 'Ahora puedes vender el exceso de energía que generes a otros miembros de la cooperativa. ¡Descubre cómo funciona!',
                'type' => Notification::TYPE_INFO,
                'read_at' => null,
                'delivered_at' => Carbon::now()->subDays(5),
            ],
            [
                'title' => 'Recordatorio: Reunión de Miembros',
                'message' => 'No olvides la reunión trimestral de miembros el próximo viernes a las 18:00. Tu participación es importante.',
                'type' => Notification::TYPE_ALERT,
                'read_at' => Carbon::now()->subDays(1),
                'delivered_at' => Carbon::now()->subDays(2),
            ],
            [
                'title' => 'Certificado de Energía Renovable',
                'message' => 'Tu certificado de energía renovable del último trimestre está listo. Descárgalo desde tu panel de control.',
                'type' => Notification::TYPE_SUCCESS,
                'read_at' => Carbon::now()->subDays(3),
                'delivered_at' => Carbon::now()->subDays(4),
            ],
            [
                'title' => 'Actualización de Política de Privacidad',
                'message' => 'Hemos actualizado nuestra política de privacidad. Por favor, revisa los cambios en tu próxima visita.',
                'type' => Notification::TYPE_INFO,
                'read_at' => null,
                'delivered_at' => Carbon::now()->subDays(6),
            ],
        ];

        $this->command->info('Creando notificaciones...');

        $createdCount = 0;
        $totalNotifications = count($notifications);

        foreach ($notifications as $index => $notificationData) {
            // Asignar usuario aleatorio o null si no hay usuarios
            $user = $users->isNotEmpty() ? $users->random() : null;
            
            if ($user) {
                $notificationData['user_id'] = $user->id;
            }

            // Crear la notificación
            Notification::create($notificationData);
            $createdCount++;

            // Mostrar progreso cada 5 notificaciones
            if (($index + 1) % 5 === 0) {
                $this->command->info("Progreso: {$createdCount}/{$totalNotifications} notificaciones creadas");
            }
        }

        $this->command->info("✅ Se han creado {$createdCount} notificaciones exitosamente");

        // Mostrar estadísticas
        $this->showStatistics();
    }

    /**
     * Mostrar estadísticas de las notificaciones creadas
     */
    private function showStatistics(): void
    {
        $this->command->info("\n📊 Estadísticas de Notificaciones:");
        
        $total = Notification::count();
        $unread = Notification::unread()->count();
        $read = Notification::read()->count();
        $delivered = Notification::delivered()->count();
        $notDelivered = Notification::notDelivered()->count();

        $this->command->info("• Total: {$total}");
        $this->command->info("• No leídas: {$unread}");
        $this->command->info("• Leídas: {$read}");
        $this->command->info("• Entregadas: {$delivered}");
        $this->command->info("• No entregadas: {$notDelivered}");

        // Estadísticas por tipo
        $this->command->info("\n📈 Por tipo:");
        foreach (Notification::getTypes() as $type) {
            $count = Notification::byType($type)->count();
            $this->command->info("• {$type}: {$count}");
        }

        // Estadísticas por usuario
        $usersWithNotifications = Notification::selectRaw('user_id, COUNT(*) as count')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->get();

        if ($usersWithNotifications->isNotEmpty()) {
            $this->command->info("\n👥 Notificaciones por usuario:");
            foreach ($usersWithNotifications as $userNotification) {
                $user = User::find($userNotification->user_id);
                $userName = $user ? $user->name : "Usuario ID {$userNotification->user_id}";
                $this->command->info("• {$userName}: {$userNotification->count} notificaciones");
            }
        }
    }
}