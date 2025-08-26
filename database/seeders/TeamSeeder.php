<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\User;
use App\Models\Organization;
use Carbon\Carbon;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👥 Creando equipos españoles para la cooperativa energética...');

        // Obtener usuarios y organizaciones disponibles
        $users = User::all();
        $organizations = Organization::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles. Ejecuta RolesAndAdminSeeder primero.');
            return;
        }

        if ($organizations->isEmpty()) {
            $this->command->error('❌ No hay organizaciones disponibles. Ejecuta AppSettingSeeder primero.');
            return;
        }

        // 1. Equipos de Energía Renovable
        $this->createRenewableEnergyTeams($users, $organizations);

        // 2. Equipos por Provincia
        $this->createProvincialTeams($users, $organizations);

        // 3. Equipos Especializados
        $this->createSpecializedTeams($users, $organizations);

        // 4. Equipos Comunitarios
        $this->createCommunityTeams($users, $organizations);

        // 5. Equipos de Competencia
        $this->createCompetitiveTeams($users, $organizations);

        $this->command->info('✅ TeamSeeder completado. Se crearon ' . Team::count() . ' equipos españoles.');
    }

    /**
     * Crear equipos de energía renovable
     */
    private function createRenewableEnergyTeams($users, $organizations): void
    {
        $this->command->info('⚡ Creando equipos de energía renovable...');

        $renewableTeams = [
            [
                'name' => 'Equipo Solar del Ebro',
                'description' => 'Equipo especializado en proyectos solares del Valle del Ebro. Nos enfocamos en instalaciones fotovoltaicas residenciales y comerciales.',
                'is_open' => true,
                'max_members' => 25,
                'logo_path' => 'teams/solar-ebro.png',
            ],
            [
                'name' => 'Equipo Eólico de los Monegros',
                'description' => 'Equipo dedicado a la energía eólica en la región de los Monegros. Trabajamos en proyectos de parques eólicos y autoconsumo.',
                'is_open' => true,
                'max_members' => 20,
                'logo_path' => 'teams/eolico-monegros.png',
            ],
            [
                'name' => 'Equipo Hidroeléctrico del Pirineo',
                'description' => 'Equipo especializado en energía hidroeléctrica del Pirineo aragonés. Desarrollamos proyectos de microcentrales y aprovechamiento hidráulico.',
                'is_open' => true,
                'max_members' => 18,
                'logo_path' => 'teams/hidro-pirineo.png',
            ],
        ];

        foreach ($renewableTeams as $teamData) {
            $creator = $users->random();
            $organization = $organizations->random();

            Team::updateOrCreate(
                ['name' => $teamData['name']],
                array_merge($teamData, [
                    'created_by_user_id' => $creator->id,
                    'organization_id' => $organization->id,
                ])
            );
        }
    }

    /**
     * Crear equipos por provincia
     */
    private function createProvincialTeams($users, $organizations): void
    {
        $this->command->info('🏘️ Creando equipos por provincia...');

        $provincialTeams = [
            [
                'name' => 'Equipo Verde de Zaragoza',
                'description' => 'Equipo de la capital aragonesa enfocado en proyectos urbanos de sostenibilidad energética y eficiencia energética.',
                'is_open' => true,
                'max_members' => 30,
                'logo_path' => 'teams/verde-zaragoza.png',
            ],
            [
                'name' => 'Equipo de Montaña de Huesca',
                'description' => 'Equipo de la provincia de Huesca especializado en proyectos de energía renovable en zonas de montaña y rurales.',
                'is_open' => true,
                'max_members' => 22,
                'logo_path' => 'teams/montana-huesca.png',
            ],
            [
                'name' => 'Equipo Sostenible de Teruel',
                'description' => 'Equipo de la provincia de Teruel dedicado a proyectos de energía sostenible y desarrollo rural.',
                'is_open' => true,
                'max_members' => 20,
                'logo_path' => 'teams/sostenible-teruel.png',
            ],
        ];

        foreach ($provincialTeams as $teamData) {
            $creator = $users->random();
            $organization = $organizations->random();

            Team::updateOrCreate(
                ['name' => $teamData['name']],
                array_merge($teamData, [
                    'created_by_user_id' => $creator->id,
                    'organization_id' => $organization->id,
                ])
            );
        }
    }

    /**
     * Crear equipos especializados
     */
    private function createSpecializedTeams($users, $organizations): void
    {
        $this->command->info('🎯 Creando equipos especializados...');

        $specializedTeams = [
            [
                'name' => 'Equipo de Autoconsumo',
                'description' => 'Equipo especializado en sistemas de autoconsumo solar y baterías. Ayudamos a los usuarios a maximizar su independencia energética.',
                'is_open' => true,
                'max_members' => 15,
                'logo_path' => 'teams/autoconsumo.png',
            ],
            [
                'name' => 'Equipo de Eficiencia Energética',
                'description' => 'Equipo enfocado en auditorías energéticas y optimización del consumo. Reducimos costes y huella de carbono.',
                'is_open' => true,
                'max_members' => 18,
                'logo_path' => 'teams/eficiencia.png',
            ],
            [
                'name' => 'Equipo de Movilidad Eléctrica',
                'description' => 'Equipo dedicado a la transición hacia la movilidad eléctrica. Instalamos puntos de carga y asesoramos en vehículos eléctricos.',
                'is_open' => true,
                'max_members' => 12,
                'logo_path' => 'teams/movilidad.png',
            ],
            [
                'name' => 'Equipo de Agricultura Sostenible',
                'description' => 'Equipo especializado en proyectos de energía renovable para el sector agrícola. Bombas solares, riego eficiente y más.',
                'is_open' => true,
                'max_members' => 16,
                'logo_path' => 'teams/agricultura.png',
            ],
        ];

        foreach ($specializedTeams as $teamData) {
            $creator = $users->random();
            $organization = $organizations->random();

            Team::updateOrCreate(
                ['name' => $teamData['name']],
                array_merge($teamData, [
                    'created_by_user_id' => $creator->id,
                    'organization_id' => $organization->id,
                ])
            );
        }
    }

    /**
     * Crear equipos comunitarios
     */
    private function createCommunityTeams($users, $organizations): void
    {
        $this->command->info('🤝 Creando equipos comunitarios...');

        $communityTeams = [
            [
                'name' => 'Equipo del Barrio Verde',
                'description' => 'Equipo comunitario del barrio que promueve la energía verde a nivel local. Proyectos de barrio y participación ciudadana.',
                'is_open' => true,
                'max_members' => null, // Sin límite
                'logo_path' => 'teams/barrio-verde.png',
            ],
            [
                'name' => 'Equipo Rural Sostenible',
                'description' => 'Equipo que une a comunidades rurales en proyectos energéticos sostenibles. Desarrollo rural y energía renovable.',
                'is_open' => true,
                'max_members' => null, // Sin límite
                'logo_path' => 'teams/rural-sostenible.png',
            ],
            [
                'name' => 'Equipo de Jóvenes Verdes',
                'description' => 'Equipo de jóvenes comprometidos con la sostenibilidad energética. Educación ambiental y proyectos innovadores.',
                'is_open' => true,
                'max_members' => 35,
                'logo_path' => 'teams/jovenes-verdes.png',
            ],
            [
                'name' => 'Equipo de Mujeres en Energía',
                'description' => 'Equipo que promueve la participación de las mujeres en el sector energético. Empoderamiento y liderazgo femenino.',
                'is_open' => true,
                'max_members' => 25,
                'logo_path' => 'teams/mujeres-energia.png',
            ],
        ];

        foreach ($communityTeams as $teamData) {
            $creator = $users->random();
            $organization = $organizations->random();

            Team::updateOrCreate(
                ['name' => $teamData['name']],
                array_merge($teamData, [
                    'created_by_user_id' => $creator->id,
                    'organization_id' => $organization->id,
                ])
            );
        }
    }

    /**
     * Crear equipos de competencia
     */
    private function createCompetitiveTeams($users, $organizations): void
    {
        $this->command->info('🏆 Creando equipos de competencia...');

        $competitiveTeams = [
            [
                'name' => 'Equipo Competitivo Solar',
                'description' => 'Equipo competitivo enfocado en alcanzar los mejores resultados en desafíos solares. Rendimiento máximo y innovación.',
                'is_open' => false, // Solo por invitación
                'max_members' => 12,
                'logo_path' => 'teams/competitivo-solar.png',
            ],
            [
                'name' => 'Equipo de Desafíos Energéticos',
                'description' => 'Equipo especializado en competencias y desafíos energéticos. Participamos en todas las competiciones de la cooperativa.',
                'is_open' => false, // Solo por invitación
                'max_members' => 15,
                'logo_path' => 'teams/desafios-energeticos.png',
            ],
            [
                'name' => 'Equipo de Innovación Verde',
                'description' => 'Equipo de innovación que desarrolla nuevas tecnologías y soluciones energéticas. Investigación y desarrollo sostenible.',
                'is_open' => false, // Solo por invitación
                'max_members' => 10,
                'logo_path' => 'teams/innovacion-verde.png',
            ],
        ];

        foreach ($competitiveTeams as $teamData) {
            $creator = $users->random();
            $organization = $organizations->random();

            Team::updateOrCreate(
                ['name' => $teamData['name']],
                array_merge($teamData, [
                    'created_by_user_id' => $creator->id,
                    'organization_id' => $organization->id,
                ])
            );
        }
    }

    /**
     * Generar estadísticas de equipos
     */
    private function generateTeamStats(): array
    {
        $total = Team::count();
        $open = Team::where('is_open', true)->count();
        $closed = Team::where('is_open', false)->count();
        $withLimit = Team::whereNotNull('max_members')->count();
        $withoutLimit = Team::whereNull('max_members')->count();

        return [
            'total_teams' => $total,
            'open_teams' => $open,
            'closed_teams' => $closed,
            'teams_with_limit' => $withLimit,
            'teams_without_limit' => $withoutLimit,
        ];
    }
}
