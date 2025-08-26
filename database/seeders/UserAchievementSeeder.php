<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserAchievement;
use App\Models\User;
use App\Models\Achievement;
use Carbon\Carbon;

class UserAchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏆 Creando logros de usuarios españoles para la cooperativa energética...');

        // Obtener usuarios y achievements disponibles
        $users = User::all();
        $achievements = Achievement::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles. Ejecuta RolesAndAdminSeeder primero.');
            return;
        }

        if ($achievements->isEmpty()) {
            $this->command->error('❌ No hay achievements disponibles. Ejecuta AchievementSeeder primero.');
            return;
        }

        // 1. Logros de Usuarios Activos (usuarios que han ganado varios achievements)
        $this->createActiveUserAchievements($users, $achievements);

        // 2. Logros de Usuarios Principiantes (usuarios con pocos achievements)
        $this->createBeginnerUserAchievements($users, $achievements);

        // 3. Logros de Usuarios Especializados (usuarios con achievements específicos)
        $this->createSpecializedUserAchievements($users, $achievements);

        // 4. Logros de Usuarios de la Comunidad (achievements comunitarios)
        $this->createCommunityUserAchievements($users, $achievements);

        // 5. Logros de Usuarios con Hitos (milestones importantes)
        $this->createMilestoneUserAchievements($users, $achievements);

        $this->command->info('✅ UserAchievementSeeder completado. Se crearon ' . UserAchievement::count() . ' logros de usuarios españoles.');
    }

    /**
     * Crear logros de usuarios activos
     */
    private function createActiveUserAchievements($users, $achievements): void
    {
        $this->command->info('👑 Creando logros de usuarios activos...');

        // Seleccionar 8 usuarios que serán muy activos
        $activeUsers = $users->random(8);

        foreach ($activeUsers as $user) {
            // Cada usuario activo gana entre 8-15 achievements
            $userAchievements = $achievements->random(rand(8, 15));
            
            foreach ($userAchievements as $achievement) {
                $this->createUserAchievement($user, $achievement, true);
            }
        }
    }

    /**
     * Crear logros de usuarios principiantes
     */
    private function createBeginnerUserAchievements($users, $achievements): void
    {
        $this->command->info('🌱 Creando logros de usuarios principiantes...');

        // Seleccionar 15 usuarios que serán principiantes
        $beginnerUsers = $users->diff($users->random(8))->random(15);

        foreach ($beginnerUsers as $user) {
            // Cada usuario principiante gana entre 2-5 achievements
            $userAchievements = $achievements->random(rand(2, 5));
            
            foreach ($userAchievements as $achievement) {
                $this->createUserAchievement($user, $achievement, false);
            }
        }
    }

    /**
     * Crear logros de usuarios especializados
     */
    private function createSpecializedUserAchievements($users, $achievements): void
    {
        $this->command->info('🎯 Creando logros de usuarios especializados...');

        // Seleccionar 10 usuarios para especializaciones
        $specializedUsers = $users->diff($users->random(23))->random(10);

        // Especializaciones por tipo
        $specializations = [
            'energy' => ['Pionero Solar', 'Maestro de la Eficiencia', 'Generador Verde'],
            'participation' => ['Miembro Activo', 'Colaborador Frecuente', 'Participante Destacado'],
            'community' => ['Embajador Verde', 'Divulgador Ambiental', 'Constructor de Comunidad'],
            'milestone' => ['Primer Hito', 'Hito Intermedio', 'Hito Avanzado'],
        ];

        foreach ($specializedUsers as $index => $user) {
            $specializationType = array_keys($specializations)[$index % count($specializations)];
            $specializedAchievements = $achievements->where('type', $specializationType)->take(3);
            
            foreach ($specializedAchievements as $achievement) {
                $this->createUserAchievement($user, $achievement, true, true);
            }
        }
    }

    /**
     * Crear logros de usuarios de la comunidad
     */
    private function createCommunityUserAchievements($users, $achievements): void
    {
        $this->command->info('🤝 Creando logros de usuarios de la comunidad...');

        // Seleccionar 6 usuarios para achievements comunitarios
        $communityUsers = $users->diff($users->random(33))->random(6);

        // Achievements comunitarios específicos
        $communityAchievements = $achievements->where('type', 'community')->take(4);

        foreach ($communityUsers as $user) {
            foreach ($communityAchievements as $achievement) {
                $this->createUserAchievement($user, $achievement, true, false, true);
            }
        }
    }

    /**
     * Crear logros de usuarios con hitos
     */
    private function createMilestoneUserAchievements($users, $achievements): void
    {
        $this->command->info('🎖️ Creando logros de usuarios con hitos...');

        // Seleccionar 3 usuarios para milestones importantes
        $milestoneUsers = $users->diff($users->random(39))->random(3);

        // Achievements de milestones
        $milestoneAchievements = $achievements->where('type', 'milestone')->take(3);

        foreach ($milestoneUsers as $user) {
            foreach ($milestoneAchievements as $achievement) {
                $this->createUserAchievement($user, $achievement, true, false, false, true);
            }
        }
    }

    /**
     * Crear un logro de usuario individual
     */
    private function createUserAchievement($user, $achievement, $rewardGranted = false, $specialized = false, $community = false, $milestone = false): void
    {
        // Verificar si ya existe
        $existing = UserAchievement::where('user_id', $user->id)
                                  ->where('achievement_id', $achievement->id)
                                  ->exists();

        if ($existing) {
            return;
        }

        // Generar fecha de obtención realista
        $earnedAt = $this->generateRealisticEarnedDate($achievement, $specialized, $community, $milestone);

        // Generar mensaje personalizado
        $customMessage = $this->generateCustomMessage($achievement, $user, $specialized, $community, $milestone);

        // Crear el logro
        UserAchievement::create([
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
            'earned_at' => $earnedAt,
            'custom_message' => $customMessage,
            'reward_granted' => $rewardGranted,
        ]);
    }

    /**
     * Generar fecha de obtención realista
     */
    private function generateRealisticEarnedDate($achievement, $specialized, $community, $milestone): Carbon
    {
        $now = Carbon::now();

        if ($milestone) {
            // Los milestones son recientes (últimos 3 meses)
            return $now->copy()->subDays(rand(1, 90));
        }

        if ($community) {
            // Los achievements comunitarios son de los últimos 6 meses
            return $now->copy()->subDays(rand(30, 180));
        }

        if ($specialized) {
            // Los achievements especializados son de los últimos 4 meses
            return $now->copy()->subDays(rand(15, 120));
        }

        // Achievements normales pueden ser de hasta 1 año
        return $now->copy()->subDays(rand(1, 365));
    }

    /**
     * Generar mensaje personalizado
     */
    private function generateCustomMessage($achievement, $user, $specialized, $community, $milestone): ?string
    {
        // 70% de probabilidad de tener mensaje personalizado
        if (rand(1, 100) > 70) {
            return null;
        }

        $userName = explode(' ', $user->name)[0]; // Primer nombre

        $messages = [
            'energy' => [
                "¡Felicidades {$userName}! Has demostrado un compromiso excepcional con la energía sostenible.",
                "¡Excelente trabajo {$userName}! Tu contribución a la transición energética es inspiradora.",
                "¡Sigue así {$userName}! Cada kWh que generas hace la diferencia.",
            ],
            'participation' => [
                "¡Gracias {$userName}! Tu participación activa fortalece nuestra cooperativa.",
                "¡Eres un ejemplo {$userName}! Tu compromiso motiva a otros miembros.",
                "¡Excelente dedicación {$userName}! La cooperativa crece gracias a ti.",
            ],
            'community' => [
                "¡Eres un embajador excepcional {$userName}! Gracias por difundir nuestra misión.",
                "¡Tu trabajo comunitario es invaluable {$userName}! Sigues construyendo un futuro mejor.",
                "¡Gracias {$userName}! Estás ayudando a crear una comunidad más fuerte.",
            ],
            'milestone' => [
                "¡Felicidades {$userName}! Has alcanzado un hito importante en tu viaje sostenible.",
                "¡Este es solo el comienzo {$userName}! Tu dedicación te llevará lejos.",
                "¡Celebramos contigo {$userName}! Este logro marca un momento especial.",
            ],
        ];

        $type = $achievement->type;
        if (isset($messages[$type])) {
            return $messages[$type][array_rand($messages[$type])];
        }

        // Mensaje genérico
        $genericMessages = [
            "¡Felicidades {$userName}! Has desbloqueado un nuevo logro.",
            "¡Excelente trabajo {$userName}! Sigues progresando en tu camino sostenible.",
            "¡Gracias {$userName}! Tu esfuerzo es reconocido por la cooperativa.",
            "¡Sigue así {$userName}! Cada logro te acerca a un futuro más verde.",
        ];

        return $genericMessages[array_rand($genericMessages)];
    }
}
