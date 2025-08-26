<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TeamChallengeProgress;
use App\Models\Team;
use App\Models\Challenge;
use Carbon\Carbon;

class TeamChallengeProgressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏆 Creando progresos de equipos en desafíos energéticos españoles...');

        // Obtener equipos y desafíos disponibles
        $teams = Team::all();
        $challenges = Challenge::all();

        if ($teams->isEmpty()) {
            $this->command->error('❌ No hay equipos disponibles. Ejecuta TeamSeeder primero.');
            return;
        }

        if ($challenges->isEmpty()) {
            $this->command->error('❌ No hay desafíos disponibles. Ejecuta ChallengeSeeder primero.');
            return;
        }

        // Limpiar progresos existentes
        TeamChallengeProgress::query()->delete();

        // 1. Progresos Completados
        $this->createCompletedProgresses($teams, $challenges);

        // 2. Progresos en Curso
        $this->createInProgressProgresses($teams, $challenges);

        // 3. Progresos Cerca de Completar
        $this->createNearCompletionProgresses($teams, $challenges);

        // 4. Progresos Recién Iniciados
        $this->createJustStartedProgresses($teams, $challenges);

        // 5. Progresos Competitivos
        $this->createCompetitiveProgresses($teams, $challenges);

        // 6. Progresos de Líderes
        $this->createLeaderProgresses($teams, $challenges);

        $this->command->info('✅ TeamChallengeProgressSeeder completado. Se crearon ' . TeamChallengeProgress::count() . ' progresos de equipos en desafíos.');
    }

    /**
     * Crear progresos completados
     */
    private function createCompletedProgresses($teams, $challenges): void
    {
        $this->command->info('🏆 Creando progresos completados...');

        // Seleccionar equipos y desafíos para completar
        $selectedTeams = $teams->take(8);
        $selectedChallenges = $challenges->where('is_active', true)->take(6);

        foreach ($selectedTeams as $team) {
            foreach ($selectedChallenges as $challenge) {
                $this->createTeamChallengeProgress($team, $challenge, true);
            }
        }
    }

    /**
     * Crear progresos en curso
     */
    private function createInProgressProgresses($teams, $challenges): void
    {
        $this->command->info('🚀 Creando progresos en curso...');

        // Seleccionar equipos y desafíos para progreso en curso
        $selectedTeams = $teams->take(12);
        $selectedChallenges = $challenges->where('is_active', true)->take(8);

        foreach ($selectedTeams as $team) {
            foreach ($selectedChallenges as $challenge) {
                $this->createTeamChallengeProgress($team, $challenge, false, 'in_progress');
            }
        }
    }

    /**
     * Crear progresos cerca de completar
     */
    private function createNearCompletionProgresses($teams, $challenges): void
    {
        $this->command->info('🎯 Creando progresos cerca de completar...');

        // Seleccionar equipos y desafíos para progreso cercano a completar
        $selectedTeams = $teams->take(6);
        $selectedChallenges = $challenges->where('is_active', true)->take(5);

        foreach ($selectedTeams as $team) {
            foreach ($selectedChallenges as $challenge) {
                $this->createTeamChallengeProgress($team, $challenge, false, 'near_completion');
            }
        }
    }

    /**
     * Crear progresos recién iniciados
     */
    private function createJustStartedProgresses($teams, $challenges): void
    {
        $this->command->info('🌱 Creando progresos recién iniciados...');

        // Seleccionar equipos y desafíos para progreso recién iniciado
        $selectedTeams = $teams->take(10);
        $selectedChallenges = $challenges->where('is_active', true)->take(7);

        foreach ($selectedTeams as $team) {
            foreach ($selectedChallenges as $challenge) {
                $this->createTeamChallengeProgress($team, $challenge, false, 'just_started');
            }
        }
    }

    /**
     * Crear progresos competitivos
     */
    private function createCompetitiveProgresses($teams, $challenges): void
    {
        $this->command->info('🏅 Creando progresos competitivos...');

        // Seleccionar equipos y desafíos para progreso competitivo
        $selectedTeams = $teams->take(5);
        $selectedChallenges = $challenges->where('type', 'team')->where('is_active', true)->take(4);

        foreach ($selectedTeams as $team) {
            foreach ($selectedChallenges as $challenge) {
                $this->createTeamChallengeProgress($team, $challenge, false, 'competitive');
            }
        }
    }

    /**
     * Crear progresos de líderes
     */
    private function createLeaderProgresses($teams, $challenges): void
    {
        $this->command->info('👑 Creando progresos de líderes...');

        // Seleccionar equipos líderes y desafíos principales
        $selectedTeams = $teams->take(3);
        $selectedChallenges = $challenges->where('is_active', true)->take(3);

        foreach ($selectedTeams as $team) {
            foreach ($selectedChallenges as $challenge) {
                $this->createTeamChallengeProgress($team, $challenge, false, 'leader');
            }
        }
    }

    /**
     * Crear un progreso de equipo en desafío
     */
    private function createTeamChallengeProgress($team, $challenge, $completed = false, $progressType = 'normal'): void
    {
        // Verificar si ya existe un progreso para este equipo y desafío
        $existing = TeamChallengeProgress::where('team_id', $team->id)
                                       ->where('challenge_id', $challenge->id)
                                       ->exists();
        
        if ($existing) {
            return;
        }

        $progressKwh = $this->generateRealisticProgress($challenge, $completed, $progressType);
        $completedAt = $completed ? $this->generateCompletionDate($challenge) : null;

        TeamChallengeProgress::create([
            'team_id' => $team->id,
            'challenge_id' => $challenge->id,
            'progress_kwh' => $progressKwh,
            'completed_at' => $completedAt,
        ]);
    }

    /**
     * Generar progreso realista basado en el tipo
     */
    private function generateRealisticProgress($challenge, $completed, $progressType): float
    {
        $targetKwh = $challenge->target_kwh;

        switch ($progressType) {
            case 'completed':
                // Progreso completado: entre 100% y 150% del objetivo
                return round($targetKwh * rand(100, 150) / 100, 2);

            case 'in_progress':
                // Progreso en curso: entre 20% y 80% del objetivo
                return round($targetKwh * rand(20, 80) / 100, 2);

            case 'near_completion':
                // Progreso cercano a completar: entre 80% y 99% del objetivo
                return round($targetKwh * rand(80, 99) / 100, 2);

            case 'just_started':
                // Progreso recién iniciado: entre 5% y 25% del objetivo
                return round($targetKwh * rand(5, 25) / 100, 2);

            case 'competitive':
                // Progreso competitivo: entre 60% y 120% del objetivo
                return round($targetKwh * rand(60, 120) / 100, 2);

            case 'leader':
                // Progreso de líder: entre 90% y 140% del objetivo
                return round($targetKwh * rand(90, 140) / 100, 2);

            default:
                // Progreso normal: entre 10% y 90% del objetivo
                return round($targetKwh * rand(10, 90) / 100, 2);
        }
    }

    /**
     * Generar fecha de finalización realista
     */
    private function generateCompletionDate($challenge): Carbon
    {
        $startDate = $challenge->start_date;
        $endDate = $challenge->end_date;
        
        // Generar fecha entre el inicio y el final del desafío
        $daysRange = $startDate->diffInDays($endDate);
        $randomDays = rand(0, $daysRange);
        
        return $startDate->copy()->addDays($randomDays);
    }

    /**
     * Generar estadísticas de progreso
     */
    private function generateProgressStats(): array
    {
        $total = TeamChallengeProgress::count();
        $completed = TeamChallengeProgress::whereNotNull('completed_at')->count();
        $inProgress = TeamChallengeProgress::whereNull('completed_at')->count();
        $totalKwh = TeamChallengeProgress::sum('progress_kwh');

        return [
            'total_progress_records' => $total,
            'completed_challenges' => $completed,
            'in_progress_challenges' => $inProgress,
            'total_kwh_progress' => $totalKwh,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
        ];
    }
}
