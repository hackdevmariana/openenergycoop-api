<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EventAttendance;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;

class EventAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📅 Creando asistencias a eventos...');

        $events = Event::all();
        $users = User::all();

        if ($events->isEmpty()) {
            $this->command->warn('⚠️ No hay eventos disponibles. Saltando asistencias...');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. Saltando asistencias...');
            return;
        }

        // No limpiar asistencias existentes para evitar conflictos con foreign keys
        // Usaremos firstOrCreate para evitar duplicados

        $this->command->info("🎪 Eventos disponibles: {$events->count()}");
        $this->command->info("👥 Usuarios disponibles: {$users->count()}");

        // Crear asistencias de forma simple
        $this->createSimpleAttendances($events, $users);

        $this->command->info('✅ EventAttendanceSeeder completado. Se crearon ' . EventAttendance::count() . ' asistencias.');
    }

    private function createSimpleAttendances($events, $users): void
    {
        $this->command->info('📝 Creando asistencias a eventos...');
        
        $attendanceCount = 0;
        
        // Crear solo 50 asistencias para evitar problemas de duplicados
        for ($i = 0; $i < 50; $i++) {
            $event = $events->random();
            $user = $users->random();
            
            $status = fake()->randomElement([
                EventAttendance::STATUS_REGISTERED,
                EventAttendance::STATUS_ATTENDED,
                EventAttendance::STATUS_CANCELLED,
                EventAttendance::STATUS_NO_SHOW,
            ]);
            
            $registeredAt = Carbon::now()->subDays(rand(1, 30));
            $data = [
                'status' => $status,
                'registered_at' => $registeredAt,
                'checkin_token' => bin2hex(random_bytes(16)),
                'notes' => fake()->optional(0.3)->sentence(),
                'created_at' => $registeredAt,
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),
            ];
            
            if ($status === EventAttendance::STATUS_ATTENDED) {
                $data['checked_in_at'] = $event->date->copy()->addMinutes(rand(-30, 30));
            } elseif ($status === EventAttendance::STATUS_CANCELLED) {
                $data['cancellation_reason'] = fake()->randomElement([
                    'Conflicto de horario',
                    'Emergencia familiar',
                    'Problemas de salud',
                    'Cambio de planes',
                    'Problemas de transporte',
                    'Trabajo inesperado',
                ]);
            } elseif ($status === EventAttendance::STATUS_NO_SHOW) {
                $data['notes'] = fake()->optional(0.3)->randomElement([
                    'No se presentó sin avisar',
                    'Problemas de transporte',
                    'Emergencia familiar',
                    'Conflicto de horario',
                    'Olvidó el evento',
                ]);
            }
            
            $attendance = EventAttendance::updateOrCreate(
                [
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                ],
                $data
            );
            
            if ($attendance->wasRecentlyCreated) {
                $attendanceCount++;
            }
        }
        
        $this->command->info("   ✅ Se crearon {$attendanceCount} asistencias únicas");
    }
}