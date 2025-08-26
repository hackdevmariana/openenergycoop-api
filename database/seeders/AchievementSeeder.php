<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Achievement;
use App\Enums\AppEnums;

class AchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏆 Creando achievements españoles para la cooperativa energética...');

        // 1. Achievements de Energía (Energéticos)
        $this->createEnergyAchievements();

        // 2. Achievements de Participación
        $this->createParticipationAchievements();

        // 3. Achievements de Comunidad
        $this->createCommunityAchievements();

        // 4. Achievements de Hitos (Milestones)
        $this->createMilestoneAchievements();

        // 5. Achievements Especiales de Aragón
        $this->createAragonSpecialAchievements();

        $this->command->info('✅ AchievementSeeder completado. Se crearon ' . Achievement::count() . ' achievements.');
    }

    /**
     * Crear achievements relacionados con la producción de energía
     */
    private function createEnergyAchievements(): void
    {
        $this->command->info('⚡ Creando achievements energéticos...');

        $energyAchievements = [
            [
                'name' => 'Primer kWh Verde',
                'description' => 'Has generado tu primer kilovatio-hora de energía renovable. ¡El comienzo de un futuro sostenible!',
                'icon' => '🌱',
                'type' => 'energy',
                'criteria' => ['type' => 'kwh_produced', 'value' => 1],
                'points_reward' => 50,
                'sort_order' => 1,
            ],
            [
                'name' => 'Generador Solar',
                'description' => 'Has alcanzado los 100 kWh de energía solar generada. ¡El sol es tu aliado!',
                'icon' => '☀️',
                'type' => 'energy',
                'criteria' => ['type' => 'kwh_produced', 'value' => 100],
                'points_reward' => 150,
                'sort_order' => 2,
            ],
            [
                'name' => 'Maestro de la Energía',
                'description' => 'Has generado 1 MWh de energía renovable. ¡Eres un verdadero maestro de la sostenibilidad!',
                'icon' => '⚡',
                'type' => 'energy',
                'criteria' => ['type' => 'kwh_produced', 'value' => 1000],
                'points_reward' => 500,
                'sort_order' => 3,
            ],
            [
                'name' => 'Productor Verde',
                'description' => 'Has generado 5 MWh de energía renovable. ¡Eres un productor verde de élite!',
                'icon' => '🌿',
                'type' => 'energy',
                'criteria' => ['type' => 'kwh_produced', 'value' => 5000],
                'points_reward' => 1000,
                'sort_order' => 4,
            ],
            [
                'name' => 'Leyenda Solar',
                'description' => 'Has generado 10 MWh de energía solar. ¡Eres una leyenda de la energía solar!',
                'icon' => '☀️',
                'type' => 'energy',
                'criteria' => ['type' => 'kwh_produced', 'value' => 10000],
                'points_reward' => 2000,
                'sort_order' => 5,
            ],
            [
                'name' => 'Neutralidad de Carbono',
                'description' => 'Has evitado 1 tonelada de CO2 con tu producción de energía renovable.',
                'icon' => '🌍',
                'type' => 'energy',
                'criteria' => ['type' => 'co2_avoided', 'value' => 1000],
                'points_reward' => 300,
                'sort_order' => 6,
            ],
        ];

        foreach ($energyAchievements as $achievement) {
            Achievement::updateOrCreate(
                ['name' => $achievement['name']],
                $achievement
            );
        }
    }

    /**
     * Crear achievements relacionados con la participación
     */
    private function createParticipationAchievements(): void
    {
        $this->command->info('👥 Creando achievements de participación...');

        $participationAchievements = [
            [
                'name' => 'Miembro Activo',
                'description' => 'Has estado activo en la cooperativa durante 30 días consecutivos.',
                'icon' => '👤',
                'type' => 'participation',
                'criteria' => ['type' => 'days_active', 'value' => 30],
                'points_reward' => 100,
                'sort_order' => 10,
            ],
            [
                'name' => 'Participante Comprometido',
                'description' => 'Has estado activo durante 100 días. ¡Tu compromiso es admirable!',
                'icon' => '💪',
                'type' => 'participation',
                'criteria' => ['type' => 'days_active', 'value' => 100],
                'points_reward' => 250,
                'sort_order' => 11,
            ],
            [
                'name' => 'Líder Comunitario',
                'description' => 'Has participado activamente durante 6 meses. ¡Eres un líder natural!',
                'icon' => '👑',
                'type' => 'participation',
                'criteria' => ['type' => 'days_active', 'value' => 180],
                'points_reward' => 500,
                'sort_order' => 12,
            ],
            [
                'name' => 'Embajador Verde',
                'description' => 'Has participado en 50 eventos de la cooperativa. ¡Eres un verdadero embajador!',
                'icon' => '🎯',
                'type' => 'participation',
                'criteria' => ['type' => 'events_attended', 'value' => 50],
                'points_reward' => 400,
                'sort_order' => 13,
            ],
        ];

        foreach ($participationAchievements as $achievement) {
            Achievement::updateOrCreate(
                ['name' => $achievement['name']],
                $achievement
            );
        }
    }

    /**
     * Crear achievements relacionados con la comunidad
     */
    private function createCommunityAchievements(): void
    {
        $this->command->info('🌍 Creando achievements de comunidad...');

        $communityAchievements = [
            [
                'name' => 'Constructor de Comunidad',
                'description' => 'Has invitado a 3 nuevos miembros a la cooperativa.',
                'icon' => '🏗️',
                'type' => 'community',
                'criteria' => ['type' => 'referrals', 'value' => 3],
                'points_reward' => 200,
                'sort_order' => 20,
            ],
            [
                'name' => 'Referidor Estrella',
                'description' => 'Has invitado a 10 nuevos miembros. ¡Eres una estrella del crecimiento!',
                'icon' => '⭐',
                'type' => 'community',
                'criteria' => ['type' => 'referrals', 'value' => 10],
                'points_reward' => 500,
                'sort_order' => 21,
            ],
            [
                'name' => 'Evangelista Verde',
                'description' => 'Has invitado a 25 nuevos miembros. ¡Eres un evangelista de la sostenibilidad!',
                'icon' => '📢',
                'type' => 'community',
                'criteria' => ['type' => 'referrals', 'value' => 25],
                'points_reward' => 1000,
                'sort_order' => 22,
            ],
            [
                'name' => 'Conector Social',
                'description' => 'Has ayudado a conectar a 5 miembros entre sí.',
                'icon' => '🔗',
                'type' => 'community',
                'criteria' => ['type' => 'connections_made', 'value' => 5],
                'points_reward' => 150,
                'sort_order' => 23,
            ],
            [
                'name' => 'Mentor Verde',
                'description' => 'Has ayudado a 3 nuevos miembros a entender el sistema.',
                'icon' => '🎓',
                'type' => 'community',
                'criteria' => ['type' => 'mentees_helped', 'value' => 3],
                'points_reward' => 300,
                'sort_order' => 24,
            ],
        ];

        foreach ($communityAchievements as $achievement) {
            Achievement::updateOrCreate(
                ['name' => $achievement['name']],
                $achievement
            );
        }
    }

    /**
     * Crear achievements de hitos importantes
     */
    private function createMilestoneAchievements(): void
    {
        $this->command->info('🏆 Creando achievements de hitos...');

        $milestoneAchievements = [
            [
                'name' => 'Primer Año Verde',
                'description' => 'Has sido miembro de la cooperativa durante un año completo.',
                'icon' => '🎉',
                'type' => 'milestone',
                'criteria' => ['type' => 'days_active', 'value' => 365],
                'points_reward' => 1000,
                'sort_order' => 30,
            ],
            [
                'name' => 'Veterano de la Cooperativa',
                'description' => 'Has sido miembro durante 3 años. ¡Eres un veterano experimentado!',
                'icon' => '🦅',
                'type' => 'milestone',
                'criteria' => ['type' => 'days_active', 'value' => 1095],
                'points_reward' => 2000,
                'sort_order' => 31,
            ],
            [
                'name' => 'Pionero Verde',
                'description' => 'Has sido miembro desde los primeros días de la cooperativa.',
                'icon' => '🚀',
                'type' => 'milestone',
                'criteria' => ['type' => 'days_active', 'value' => 1825],
                'points_reward' => 3000,
                'sort_order' => 32,
            ],
            [
                'name' => 'Leyenda Sostenible',
                'description' => 'Has sido miembro durante 5 años. ¡Eres una leyenda de la sostenibilidad!',
                'icon' => '👑',
                'type' => 'milestone',
                'criteria' => ['type' => 'days_active', 'value' => 1825],
                'points_reward' => 5000,
                'sort_order' => 33,
            ],
            [
                'name' => 'Centenario Verde',
                'description' => 'Has generado 100 MWh de energía renovable. ¡Eres centenario en energía verde!',
                'icon' => '💯',
                'type' => 'milestone',
                'criteria' => ['type' => 'kwh_produced', 'value' => 100000],
                'points_reward' => 10000,
                'sort_order' => 34,
            ],
        ];

        foreach ($milestoneAchievements as $achievement) {
            Achievement::updateOrCreate(
                ['name' => $achievement['name']],
                $achievement
            );
        }
    }

    /**
     * Crear achievements especiales de Aragón
     */
    private function createAragonSpecialAchievements(): void
    {
        $this->command->info('🏔️ Creando achievements especiales de Aragón...');

        $aragonAchievements = [
            [
                'name' => 'Hijo del Ebro',
                'description' => 'Has generado energía en la cuenca del río Ebro. ¡El río más caudaloso de España te bendice!',
                'icon' => '🌊',
                'type' => 'energy',
                'criteria' => ['type' => 'aragon_region', 'value' => 'ebro_basin'],
                'points_reward' => 300,
                'sort_order' => 40,
            ],
            [
                'name' => 'Pirineo Verde',
                'description' => 'Has generado energía en la región de los Pirineos aragoneses. ¡Las montañas te dan su fuerza!',
                'icon' => '🏔️',
                'type' => 'energy',
                'criteria' => ['type' => 'aragon_region', 'value' => 'pyrenees'],
                'points_reward' => 400,
                'sort_order' => 41,
            ],
            [
                'name' => 'Monegros Sostenible',
                'description' => 'Has generado energía en la región de los Monegros. ¡El desierto aragonés se vuelve verde!',
                'icon' => '🏜️',
                'type' => 'energy',
                'criteria' => ['type' => 'aragon_region', 'value' => 'monegros'],
                'points_reward' => 350,
                'sort_order' => 42,
            ],
            [
                'name' => 'Zaragozano Verde',
                'description' => 'Has generado energía en la provincia de Zaragoza. ¡La capital del Ebro es verde!',
                'icon' => '🏛️',
                'type' => 'energy',
                'criteria' => ['type' => 'aragon_province', 'value' => 'zaragoza'],
                'points_reward' => 250,
                'sort_order' => 43,
            ],
            [
                'name' => 'Huescano Renovable',
                'description' => 'Has generado energía en la provincia de Huesca. ¡Los Pirineos te dan su energía!',
                'icon' => '⛰️',
                'type' => 'energy',
                'criteria' => ['type' => 'aragon_province', 'value' => 'huesca'],
                'points_reward' => 250,
                'sort_order' => 44,
            ],
            [
                'name' => 'Turolense Sostenible',
                'description' => 'Has generado energía en la provincia de Teruel. ¡La energía del sur de Aragón!',
                'icon' => '🌅',
                'type' => 'energy',
                'criteria' => ['type' => 'aragon_province', 'value' => 'teruel'],
                'points_reward' => 250,
                'sort_order' => 45,
            ],
            [
                'name' => 'Cooperativista Aragonés',
                'description' => 'Has participado en 10 eventos de cooperativas aragonesas. ¡El espíritu cooperativo aragonés!',
                'icon' => '🤝',
                'type' => 'community',
                'criteria' => ['type' => 'aragon_events', 'value' => 10],
                'points_reward' => 400,
                'sort_order' => 46,
            ],
            [
                'name' => 'Embajador de Aragón',
                'description' => 'Has representado a la cooperativa en eventos aragoneses. ¡Eres un embajador de la región!',
                'icon' => '🎖️',
                'type' => 'community',
                'criteria' => ['type' => 'aragon_representation', 'value' => 5],
                'points_reward' => 600,
                'sort_order' => 47,
            ],
        ];

        foreach ($aragonAchievements as $achievement) {
            Achievement::updateOrCreate(
                ['name' => $achievement['name']],
                $achievement
            );
        }
    }
}
