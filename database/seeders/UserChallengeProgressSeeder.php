<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserChallengeProgress;
use App\Models\User;
use App\Models\EnergyChallenge;
use Carbon\Carbon;

class UserChallengeProgressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('⚡ Creando progresos de desafíos energéticos de usuarios españoles...');

        // Obtener usuarios y challenges disponibles
        $users = User::all();
        $challenges = EnergyChallenge::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles. Ejecuta RolesAndAdminSeeder primero.');
            return;
        }

        if ($challenges->isEmpty()) {
            $this->command->error('❌ No hay energy challenges disponibles. Ejecuta EnergyChallengeSeeder primero.');
            return;
        }

        // 1. Progresos Completados (usuarios que han terminado desafíos)
        $this->createCompletedProgresses($users, $challenges);

        // 2. Progresos en Curso (usuarios activamente participando)
        $this->createInProgressProgresses($users, $challenges);

        // 3. Progresos Cerca de Completar (usuarios casi terminando)
        $this->createNearCompletionProgresses($users, $challenges);

        // 4. Progresos Recién Iniciados (usuarios comenzando)
        $this->createJustStartedProgresses($users, $challenges);

        // 5. Progresos de Usuarios Especializados (por tipo de challenge)
        $this->createSpecializedProgresses($users, $challenges);

        $this->command->info('✅ UserChallengeProgressSeeder completado. Se crearon ' . UserChallengeProgress::count() . ' progresos de desafíos energéticos.');
    }

    /**
     * Crear progresos completados
     */
    private function createCompletedProgresses($users, $challenges): void
    {
        $this->command->info('🏆 Creando progresos completados...');

        // Seleccionar 12 usuarios que han completado desafíos (o todos si hay menos)
        $completedUsers = $users->count() >= 12 ? $users->random(12) : $users->take(12);

        foreach ($completedUsers as $user) {
            // Cada usuario completa entre 2-5 challenges
            $userChallenges = $challenges->random(rand(2, min(5, $challenges->count())));
            
            foreach ($userChallenges as $challenge) {
                $this->createUserChallengeProgress($user, $challenge, true);
            }
        }
    }

    /**
     * Crear progresos en curso
     */
    private function createInProgressProgresses($users, $challenges): void
    {
        $this->command->info('🚀 Creando progresos en curso...');

        // Usar usuarios que no están en completedUsers
        $remainingUsers = $users->diff($users->count() >= 12 ? $users->random(12) : $users->take(12));
        $activeUsers = $remainingUsers->count() >= 18 ? $remainingUsers->random(18) : $remainingUsers;

        foreach ($activeUsers as $user) {
            // Cada usuario participa en entre 3-6 challenges
            $userChallenges = $challenges->random(rand(3, min(6, $challenges->count())));
            
            foreach ($userChallenges as $challenge) {
                $this->createUserChallengeProgress($user, $challenge, false, 'active');
            }
        }
    }

    /**
     * Crear progresos cerca de completar
     */
    private function createNearCompletionProgresses($users, $challenges): void
    {
        $this->command->info('🎯 Creando progresos cerca de completar...');

        // Usar usuarios restantes
        $usedUsers = $users->count() >= 12 ? $users->random(12) : $users->take(12);
        $remainingUsers = $users->diff($usedUsers);
        $nearCompletionUsers = $remainingUsers->count() >= 8 ? $remainingUsers->random(8) : $remainingUsers;

        foreach ($nearCompletionUsers as $user) {
            // Cada usuario está cerca de completar entre 2-4 challenges
            $userChallenges = $challenges->random(rand(2, min(4, $challenges->count())));
            
            foreach ($userChallenges as $challenge) {
                $this->createUserChallengeProgress($user, $challenge, false, 'near');
            }
        }
    }

    /**
     * Crear progresos recién iniciados
     */
    private function createJustStartedProgresses($users, $challenges): void
    {
        $this->command->info('🌱 Creando progresos recién iniciados...');

        // Usar usuarios restantes
        $usedUsers = $users->count() >= 20 ? $users->random(20) : $users->take(20);
        $remainingUsers = $users->diff($usedUsers);
        $beginnerUsers = $remainingUsers->count() >= 4 ? $remainingUsers->random(4) : $remainingUsers;

        foreach ($beginnerUsers as $user) {
            // Cada usuario está comenzando entre 1-3 challenges
            $userChallenges = $challenges->random(rand(1, min(3, $challenges->count())));
            
            foreach ($userChallenges as $challenge) {
                $this->createUserChallengeProgress($user, $challenge, false, 'beginner');
            }
        }
    }

    /**
     * Crear progresos de usuarios especializados
     */
    private function createSpecializedProgresses($users, $challenges): void
    {
        $this->command->info('🎯 Creando progresos de usuarios especializados...');

        // Usar usuarios restantes
        $usedUsers = $users->count() >= 24 ? $users->random(24) : $users->take(24);
        $remainingUsers = $users->diff($usedUsers);
        $specializedUsers = $remainingUsers->count() >= 6 ? $remainingUsers->random(6) : $remainingUsers;

        // Especializaciones por tipo de challenge
        $specializations = [
            'individual' => ['Desafío Solar Personal', 'Eficiencia Energética Individual', 'Reducción de Consumo'],
            'colectivo' => ['Desafío Comunitario Solar', 'Cooperativa Verde', 'Barrio Sostenible'],
        ];

        foreach ($specializedUsers as $index => $user) {
            $specializationType = array_keys($specializations)[$index % count($specializations)];
            $specializedChallenges = $challenges->where('type', $specializationType)->take(3);
            
            foreach ($specializedChallenges as $challenge) {
                $this->createUserChallengeProgress($user, $challenge, false, 'specialized');
            }
        }
    }

    /**
     * Crear un progreso de challenge individual
     */
    private function createUserChallengeProgress($user, $challenge, $completed = false, $progressType = 'normal'): void
    {
        // Verificar si ya existe
        $existing = UserChallengeProgress::where('user_id', $user->id)
                                        ->where('challenge_id', $challenge->id)
                                        ->exists();

        if ($existing) {
            return;
        }

        // Generar progreso realista basado en el tipo
        $progressKwh = $this->generateRealisticProgress($challenge, $completed, $progressType);
        
        // Generar fecha de completado si corresponde
        $completedAt = $completed ? $this->generateCompletionDate($challenge) : null;

        // Crear el progreso
        UserChallengeProgress::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'progress_kwh' => $progressKwh,
            'completed_at' => $completedAt,
        ]);
    }

    /**
     * Generar progreso realista
     */
    private function generateRealisticProgress($challenge, $completed, $progressType): float
    {
        $goalKwh = $challenge->goal_kwh;

        if ($completed) {
            // Si está completado, el progreso debe ser >= al objetivo
            return $goalKwh + rand(0, 20); // Puede exceder ligeramente
        }

        switch ($progressType) {
            case 'near':
                // Cerca de completar: 80-95% del objetivo
                return $goalKwh * (rand(80, 95) / 100);
            
            case 'active':
                // En progreso activo: 30-70% del objetivo
                return $goalKwh * (rand(30, 70) / 100);
            
            case 'beginner':
                // Recién comenzando: 5-25% del objetivo
                return $goalKwh * (rand(5, 25) / 100);
            
            case 'specialized':
                // Usuario especializado: 40-80% del objetivo
                return $goalKwh * (rand(40, 80) / 100);
            
            default:
                // Progreso normal: 20-60% del objetivo
                return $goalKwh * (rand(20, 60) / 100);
        }
    }

    /**
     * Generar fecha de completado realista
     */
    private function generateCompletionDate($challenge): Carbon
    {
        $now = Carbon::now();
        $startDate = Carbon::parse($challenge->starts_at);
        $endDate = Carbon::parse($challenge->ends_at);

        // La fecha de completado debe estar entre la fecha de inicio y ahora
        // Con tendencia a completarse antes del final del challenge
        $completionRange = $endDate->diffInDays($startDate);
        $earlyCompletion = rand(0, min(30, $completionRange)); // Completar hasta 30 días antes del final

        return $endDate->copy()->subDays($earlyCompletion);
    }

    /**
     * Generar estadísticas del progreso
     */
    private function generateProgressStats(): array
    {
        $total = UserChallengeProgress::count();
        $completed = UserChallengeProgress::whereNotNull('completed_at')->count();
        $inProgress = UserChallengeProgress::whereNull('completed_at')->count();
        $nearCompletion = UserChallengeProgress::whereNull('completed_at')
                                              ->whereRaw('progress_kwh >= goal_kwh * 0.8')
                                              ->count();

        return [
            'total_progresses' => $total,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'near_completion' => $nearCompletion,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
        ];
    }
}
