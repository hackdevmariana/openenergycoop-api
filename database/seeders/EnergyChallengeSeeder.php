<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EnergyChallenge;
use App\Enums\AppEnums;
use Carbon\Carbon;

class EnergyChallengeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('⚡ Creando desafíos energéticos españoles para la cooperativa...');

        // 1. Desafíos Individuales
        $this->createIndividualChallenges();

        // 2. Desafíos Colectivos
        $this->createCollectiveChallenges();

        // 3. Desafíos de Equipo
        $this->createTeamChallenges();

        // 4. Desafíos de Organización
        $this->createOrganizationChallenges();

        // 5. Desafíos Especiales de Aragón
        $this->createAragonSpecialChallenges();

        $this->command->info('✅ EnergyChallengeSeeder completado. Se crearon ' . EnergyChallenge::count() . ' desafíos energéticos.');
    }

    /**
     * Crear desafíos individuales
     */
    private function createIndividualChallenges(): void
    {
        $this->command->info('👤 Creando desafíos individuales...');

        $individualChallenges = [
            [
                'title' => 'Mi Primer kWh Verde',
                'description' => 'Genera tu primer kilovatio-hora de energía renovable. ¡El comienzo de tu viaje hacia la sostenibilidad!',
                'type' => 'individual',
                'goal_kwh' => 1.0,
                'starts_at' => now()->subDays(5),
                'ends_at' => now()->addDays(25),
                'reward_type' => 'badge',
                'reward_details' => [
                    'points' => 100,
                    'badge_name' => 'Primer kWh Verde',
                    'description' => 'Insignia por generar tu primera energía renovable',
                ],
                'is_active' => true,
            ],
            [
                'title' => 'Generador Solar Doméstico',
                'description' => 'Alcanza los 50 kWh de energía solar generada en tu hogar. ¡El sol es tu mejor aliado!',
                'type' => 'individual',
                'goal_kwh' => 50.0,
                'starts_at' => now()->subDays(10),
                'ends_at' => now()->addDays(20),
                'reward_type' => 'energy_donation',
                'reward_details' => [
                    'points' => 250,
                    'energy_kwh' => 5,
                    'description' => '5 kWh de energía donados a la comunidad',
                ],
                'is_active' => true,
            ],
            [
                'title' => 'Maestro de la Eficiencia',
                'description' => 'Optimiza tu consumo y genera 100 kWh de energía renovable. ¡Eficiencia y sostenibilidad van de la mano!',
                'type' => 'individual',
                'goal_kwh' => 100.0,
                'starts_at' => now()->subDays(15),
                'ends_at' => now()->addDays(15),
                'reward_type' => 'symbolic',
                'reward_details' => [
                    'points' => 500,
                    'title' => 'Maestro de la Eficiencia',
                    'description' => 'Reconocimiento por tu compromiso con la eficiencia energética',
                ],
                'is_active' => true,
            ],
            [
                'title' => 'Productor Verde Elite',
                'description' => 'Genera 500 kWh de energía renovable. ¡Eres un productor verde de élite!',
                'type' => 'individual',
                'goal_kwh' => 500.0,
                'starts_at' => now()->subDays(20),
                'ends_at' => now()->addDays(10),
                'reward_type' => 'badge',
                'reward_details' => [
                    'points' => 1000,
                    'badge_name' => 'Productor Verde Elite',
                    'description' => 'Insignia de élite por tu producción sostenible',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($individualChallenges as $challenge) {
            EnergyChallenge::updateOrCreate(
                ['title' => $challenge['title']],
                $challenge
            );
        }
    }

    /**
     * Crear desafíos colectivos
     */
    private function createCollectiveChallenges(): void
    {
        $this->command->info('👥 Creando desafíos colectivos...');

        $collectiveChallenges = [
            [
                'title' => 'Comunidad Solar Aragonesa',
                'description' => 'Como comunidad, generemos 10 MWh de energía solar en Aragón. ¡Juntos somos más fuertes!',
                'type' => 'colectivo',
                'goal_kwh' => 10000.0,
                'starts_at' => now()->subDays(30),
                'ends_at' => now()->addDays(60),
                'reward_type' => 'energy_donation',
                'reward_details' => [
                    'points' => 2000,
                    'energy_kwh' => 100,
                    'description' => '100 kWh donados a familias vulnerables de Aragón',
                ],
                'is_active' => true,
            ],
            [
                'title' => 'Desafío del Valle del Ebro',
                'description' => 'Generemos 5 MWh de energía renovable en la cuenca del río Ebro. ¡El río más caudaloso de España!',
                'type' => 'colectivo',
                'goal_kwh' => 5000.0,
                'starts_at' => now()->subDays(25),
                'ends_at' => now()->addDays(35),
                'reward_type' => 'symbolic',
                'reward_details' => [
                    'points' => 1500,
                    'title' => 'Guardianes del Ebro',
                    'description' => 'Reconocimiento por proteger el río más importante de Aragón',
                ],
                'is_active' => true,
            ],
            [
                'title' => 'Cooperativa Verde Aragonesa',
                'description' => 'Alcanzemos 20 MWh de energía renovable como cooperativa. ¡El espíritu cooperativo aragonés!',
                'type' => 'colectivo',
                'goal_kwh' => 20000.0,
                'starts_at' => now()->subDays(40),
                'ends_at' => now()->addDays(80),
                'reward_type' => 'badge',
                'reward_details' => [
                    'points' => 3000,
                    'badge_name' => 'Cooperativa Verde Aragonesa',
                    'description' => 'Insignia de la cooperativa más verde de Aragón',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($collectiveChallenges as $challenge) {
            EnergyChallenge::updateOrCreate(
                ['title' => $challenge['title']],
                $challenge
            );
        }
    }

    /**
     * Crear desafíos de equipo
     */
    private function createTeamChallenges(): void
    {
        $this->command->info('🏆 Creando desafíos de equipo...');

        $teamChallenges = [
            [
                'title' => 'Equipo Solar de Zaragoza',
                'description' => 'Forma un equipo de 5 personas y generad 2 MWh de energía solar en Zaragoza.',
                'type' => 'colectivo',
                'goal_kwh' => 2000.0,
                'starts_at' => now()->subDays(20),
                'ends_at' => now()->addDays(40),
                'reward_type' => 'energy_donation',
                'reward_details' => [
                    'points' => 800,
                    'energy_kwh' => 20,
                    'description' => '20 kWh donados a escuelas de Zaragoza',
                ],
                'is_active' => true,
            ],
            [
                'title' => 'Equipo Eólico de Huesca',
                'description' => 'Forma un equipo de 3 personas y generad 1 MWh de energía eólica en Huesca.',
                'type' => 'colectivo',
                'goal_kwh' => 1000.0,
                'starts_at' => now()->subDays(15),
                'ends_at' => now()->addDays(30),
                'reward_type' => 'badge',
                'reward_details' => [
                    'points' => 600,
                    'badge_name' => 'Equipo Eólico de Huesca',
                    'description' => 'Insignia del equipo más eólico de los Pirineos',
                ],
                'is_active' => true,
            ],
            [
                'title' => 'Equipo Hidroeléctrico de Teruel',
                'description' => 'Forma un equipo de 4 personas y generad 1.5 MWh de energía hidroeléctrica en Teruel.',
                'type' => 'colectivo',
                'goal_kwh' => 1500.0,
                'starts_at' => now()->subDays(18),
                'ends_at' => now()->addDays(35),
                'reward_type' => 'symbolic',
                'reward_details' => [
                    'points' => 700,
                    'title' => 'Equipo Hidroeléctrico de Teruel',
                    'description' => 'Reconocimiento por aprovechar el agua de Teruel',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($teamChallenges as $challenge) {
            EnergyChallenge::updateOrCreate(
                ['title' => $challenge['title']],
                $challenge
            );
        }
    }

    /**
     * Crear desafíos de organización
     */
    private function createOrganizationChallenges(): void
    {
        $this->command->info('🏢 Creando desafíos de organización...');

        $organizationChallenges = [
            [
                'title' => 'Organización Sostenible Aragonesa',
                'description' => 'Como organización, generad 50 MWh de energía renovable en Aragón. ¡Liderad el cambio!',
                'type' => 'colectivo',
                'goal_kwh' => 50000.0,
                'starts_at' => now()->subDays(60),
                'ends_at' => now()->addDays(120),
                'reward_type' => 'badge',
                'reward_details' => [
                    'points' => 5000,
                    'badge_name' => 'Organización Sostenible Aragonesa',
                    'description' => 'Insignia de la organización más sostenible de Aragón',
                ],
                'is_active' => true,
            ],
            [
                'title' => 'Cooperativa del Futuro',
                'description' => 'Alcanzad 100 MWh de energía renovable como cooperativa. ¡Sois el futuro de la energía!',
                'type' => 'colectivo',
                'goal_kwh' => 100000.0,
                'starts_at' => now()->subDays(90),
                'ends_at' => now()->addDays(180),
                'reward_type' => 'energy_donation',
                'reward_details' => [
                    'points' => 10000,
                    'energy_kwh' => 500,
                    'description' => '500 kWh donados a proyectos sociales de Aragón',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($organizationChallenges as $challenge) {
            EnergyChallenge::updateOrCreate(
                ['title' => $challenge['title']],
                $challenge
            );
        }
    }

    /**
     * Crear desafíos especiales de Aragón
     */
    private function createAragonSpecialChallenges(): void
    {
        $this->command->info('🏔️ Creando desafíos especiales de Aragón...');

        $aragonSpecialChallenges = [
            [
                'title' => 'Desafío de los Monegros',
                'description' => 'Genera 500 kWh de energía solar en la región de Los Monegros. ¡El desierto aragonés se vuelve verde!',
                'type' => 'individual',
                'goal_kwh' => 500.0,
                'starts_at' => now()->subDays(12),
                'ends_at' => now()->addDays(18),
                'reward_type' => 'badge',
                'reward_details' => [
                    'points' => 750,
                    'badge_name' => 'Monegros Verde',
                    'description' => 'Insignia por hacer verde el desierto aragonés',
                ],
                'is_active' => true,
            ],
            [
                'title' => 'Desafío de los Pirineos',
                'description' => 'Genera 300 kWh de energía eólica en los Pirineos aragoneses. ¡Las montañas te dan su fuerza!',
                'type' => 'individual',
                'goal_kwh' => 300.0,
                'starts_at' => now()->subDays(8),
                'ends_at' => now()->addDays(22),
                'reward_type' => 'symbolic',
                'reward_details' => [
                    'points' => 450,
                    'title' => 'Pirineo Verde',
                    'description' => 'Reconocimiento por aprovechar la energía de las montañas',
                ],
                'is_active' => true,
            ],
            [
                'title' => 'Desafío del Ebro',
                'description' => 'Genera 200 kWh de energía hidroeléctrica en la cuenca del río Ebro. ¡El río más caudaloso de España!',
                'type' => 'individual',
                'goal_kwh' => 200.0,
                'starts_at' => now()->subDays(6),
                'ends_at' => now()->addDays(24),
                'reward_type' => 'energy_donation',
                'reward_details' => [
                    'points' => 300,
                    'energy_kwh' => 10,
                    'description' => '10 kWh donados a proyectos de conservación del Ebro',
                ],
                'is_active' => true,
            ],
            [
                'title' => 'Desafío de las Tres Provincias',
                'description' => 'Genera energía renovable en las tres provincias de Aragón: Zaragoza, Huesca y Teruel.',
                'type' => 'colectivo',
                'goal_kwh' => 3000.0,
                'starts_at' => now()->subDays(35),
                'ends_at' => now()->addDays(65),
                'reward_type' => 'badge',
                'reward_details' => [
                    'points' => 2500,
                    'badge_name' => 'Aragonés Universal',
                    'description' => 'Insignia por tu presencia en las tres provincias',
                ],
                'is_active' => true,
            ],
            [
                'title' => 'Desafío de la Capital del Ebro',
                'description' => 'Como comunidad de Zaragoza, generad 15 MWh de energía renovable. ¡La capital del Ebro es verde!',
                'type' => 'colectivo',
                'goal_kwh' => 15000.0,
                'starts_at' => now()->subDays(45),
                'ends_at' => now()->addDays(75),
                'reward_type' => 'symbolic',
                'reward_details' => [
                    'points' => 3500,
                    'title' => 'Capital Verde del Ebro',
                    'description' => 'Reconocimiento por hacer de Zaragoza la capital verde',
                ],
                'is_active' => true,
            ],
            [
                'title' => 'Desafío de la Montaña',
                'description' => 'Como comunidad de Huesca, generad 8 MWh de energía renovable. ¡Los Pirineos te dan su energía!',
                'type' => 'colectivo',
                'goal_kwh' => 8000.0,
                'starts_at' => now()->subDays(38),
                'ends_at' => now()->addDays(68),
                'reward_type' => 'badge',
                'reward_details' => [
                    'points' => 2000,
                    'badge_name' => 'Montaña Verde',
                    'description' => 'Insignia de la montaña más verde de Aragón',
                ],
                'is_active' => true,
            ],
            [
                'title' => 'Desafío del Sur',
                'description' => 'Como comunidad de Teruel, generad 6 MWh de energía renovable. ¡La energía del sur de Aragón!',
                'type' => 'colectivo',
                'goal_kwh' => 6000.0,
                'starts_at' => now()->subDays(42),
                'ends_at' => now()->addDays(72),
                'reward_type' => 'energy_donation',
                'reward_details' => [
                    'points' => 1800,
                    'energy_kwh' => 50,
                    'description' => '50 kWh donados a proyectos de desarrollo rural de Teruel',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($aragonSpecialChallenges as $challenge) {
            EnergyChallenge::updateOrCreate(
                ['title' => $challenge['title']],
                $challenge
            );
        }
    }
}
