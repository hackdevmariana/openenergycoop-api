<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Challenge;
use App\Models\Organization;
use Carbon\Carbon;

class ChallengeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏆 Creando desafíos energéticos españoles para la cooperativa...');

        // Obtener organizaciones disponibles
        $organizations = Organization::all();

        if ($organizations->isEmpty()) {
            $this->command->error('❌ No hay organizaciones disponibles. Ejecuta AppSettingSeeder primero.');
            return;
        }

        // 1. Desafíos de Energía Renovable
        $this->createRenewableEnergyChallenges($organizations);

        // 2. Desafíos por Provincia
        $this->createProvincialChallenges($organizations);

        // 3. Desafíos Especializados
        $this->createSpecializedChallenges($organizations);

        // 4. Desafíos Estacionales
        $this->createSeasonalChallenges($organizations);

        // 5. Desafíos de Competencia
        $this->createCompetitiveChallenges($organizations);

        // 6. Desafíos Comunitarios
        $this->createCommunityChallenges($organizations);

        $this->command->info('✅ ChallengeSeeder completado. Se crearon ' . Challenge::count() . ' desafíos energéticos españoles.');
    }

    /**
     * Crear desafíos de energía renovable
     */
    private function createRenewableEnergyChallenges($organizations): void
    {
        $this->command->info('⚡ Creando desafíos de energía renovable...');

        $renewableChallenges = [
            [
                'name' => 'Desafío Solar del Valle del Ebro',
                'description' => 'Genera la máxima energía solar posible en el Valle del Ebro durante los meses de mayor radiación. Aprovecha la excelente ubicación geográfica para la energía fotovoltaica.',
                'type' => 'team',
                'target_kwh' => 5000.00,
                'points_reward' => 800,
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->addMonths(3),
                'is_active' => true,
                'criteria' => [
                    'min_team_size' => 5,
                    'max_team_size' => 25,
                    'requires_verification' => true,
                    'solar_focus' => true,
                    'valle_ebro_bonus' => 1.2,
                    'peak_hours_only' => true,
                ],
                'icon' => 'solar-panel',
            ],
            [
                'name' => 'Desafío Eólico de los Monegros',
                'description' => 'Maximiza la generación eólica en la región de los Monegros. Aprovecha los vientos constantes de la zona para generar energía limpia y renovable.',
                'type' => 'team',
                'target_kwh' => 3000.00,
                'points_reward' => 600,
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->addMonths(2),
                'is_active' => true,
                'criteria' => [
                    'min_team_size' => 3,
                    'max_team_size' => 20,
                    'requires_verification' => true,
                    'wind_focus' => true,
                    'monegros_bonus' => 1.3,
                    'consecutive_days' => 14,
                ],
                'icon' => 'wind-turbine',
            ],
            [
                'name' => 'Desafío Hidroeléctrico del Pirineo',
                'description' => 'Desarrolla proyectos de microcentrales hidroeléctricas en el Pirineo aragonés. Aprovecha los recursos hídricos de la montaña para generar energía sostenible.',
                'type' => 'organization',
                'target_kwh' => 10000.00,
                'points_reward' => 1500,
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->addMonths(6),
                'is_active' => true,
                'criteria' => [
                    'min_team_size' => 8,
                    'max_team_size' => 30,
                    'requires_verification' => true,
                    'hydro_focus' => true,
                    'pirineo_bonus' => 1.4,
                    'technology_type' => 'hydro',
                ],
                'icon' => 'water-drop',
            ],
        ];

        foreach ($renewableChallenges as $challengeData) {
            $organization = $organizations->random();

            Challenge::updateOrCreate(
                ['name' => $challengeData['name']],
                array_merge($challengeData, [
                    'organization_id' => $organization->id,
                ])
            );
        }
    }

    /**
     * Crear desafíos por provincia
     */
    private function createProvincialChallenges($organizations): void
    {
        $this->command->info('🏘️ Creando desafíos por provincia...');

        $provincialChallenges = [
            [
                'name' => 'Desafío Verde de Zaragoza',
                'description' => 'Transforma Zaragoza en una ciudad más sostenible. Enfócate en proyectos urbanos de eficiencia energética y energía renovable.',
                'type' => 'team',
                'target_kwh' => 2500.00,
                'points_reward' => 500,
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->addMonths(4),
                'is_active' => true,
                'criteria' => [
                    'min_team_size' => 4,
                    'max_team_size' => 20,
                    'requires_verification' => false,
                    'urban_focus' => true,
                    'zaragoza_bonus' => 1.1,
                    'weekend_bonus' => 1.2,
                ],
                'icon' => 'leaf',
            ],
            [
                'name' => 'Desafío de Montaña de Huesca',
                'description' => 'Desarrolla proyectos de energía renovable en las zonas de montaña de Huesca. Aprovecha los recursos naturales únicos de la zona.',
                'type' => 'team',
                'target_kwh' => 2000.00,
                'points_reward' => 400,
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->addMonths(3),
                'is_active' => true,
                'criteria' => [
                    'min_team_size' => 3,
                    'max_team_size' => 15,
                    'requires_verification' => false,
                    'mountain_focus' => true,
                    'huesca_bonus' => 1.2,
                    'region_specific' => 'rural',
                ],
                'icon' => 'mountain',
            ],
            [
                'name' => 'Desafío Sostenible de Teruel',
                'description' => 'Promueve la sostenibilidad energética en Teruel. Enfócate en proyectos de desarrollo rural y energía renovable.',
                'type' => 'team',
                'target_kwh' => 1800.00,
                'points_reward' => 350,
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->addMonths(3),
                'is_active' => true,
                'criteria' => [
                    'min_team_size' => 3,
                    'max_team_size' => 15,
                    'requires_verification' => false,
                    'rural_focus' => true,
                    'teruel_bonus' => 1.15,
                    'region_specific' => 'rural',
                ],
                'icon' => 'recycle',
            ],
        ];

        foreach ($provincialChallenges as $challengeData) {
            $organization = $organizations->random();

            Challenge::updateOrCreate(
                ['name' => $challengeData['name']],
                array_merge($challengeData, [
                    'organization_id' => $organization->id,
                ])
            );
        }
    }

    /**
     * Crear desafíos especializados
     */
    private function createSpecializedChallenges($organizations): void
    {
        $this->command->info('🎯 Creando desafíos especializados...');

        $specializedChallenges = [
            [
                'name' => 'Desafío de Autoconsumo Solar',
                'description' => 'Maximiza tu independencia energética con sistemas de autoconsumo solar. Instala paneles solares y baterías para reducir tu dependencia de la red.',
                'type' => 'individual',
                'target_kwh' => 500.00,
                'points_reward' => 300,
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->addMonths(2),
                'is_active' => true,
                'criteria' => [
                    'requires_verification' => true,
                    'autoconsumo_focus' => true,
                    'battery_bonus' => 1.3,
                    'solar_installation' => true,
                ],
                'icon' => 'battery',
            ],
            [
                'name' => 'Desafío de Eficiencia Energética',
                'description' => 'Reduce tu consumo energético mediante auditorías y optimizaciones. Identifica y elimina el desperdicio de energía en tu hogar o negocio.',
                'type' => 'individual',
                'target_kwh' => 200.00,
                'points_reward' => 250,
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->addMonths(2),
                'is_active' => true,
                'criteria' => [
                    'requires_verification' => true,
                    'efficiency_focus' => true,
                    'audit_required' => true,
                    'savings_bonus' => 1.4,
                ],
                'icon' => 'lightning',
            ],
            [
                'name' => 'Desafío de Movilidad Eléctrica',
                'description' => 'Adopta la movilidad eléctrica y reduce tu huella de carbono. Instala puntos de carga y utiliza vehículos eléctricos.',
                'type' => 'team',
                'target_kwh' => 800.00,
                'points_reward' => 400,
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->addMonths(3),
                'is_active' => true,
                'criteria' => [
                    'min_team_size' => 2,
                    'max_team_size' => 10,
                    'requires_verification' => true,
                    'ev_focus' => true,
                    'charging_station_bonus' => 1.5,
                ],
                'icon' => 'car',
            ],
            [
                'name' => 'Desafío de Agricultura Sostenible',
                'description' => 'Implementa soluciones de energía renovable en el sector agrícola. Instala bombas solares y sistemas de riego eficiente.',
                'type' => 'team',
                'target_kwh' => 1200.00,
                'points_reward' => 450,
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->addMonths(4),
                'is_active' => true,
                'criteria' => [
                    'min_team_size' => 3,
                    'max_team_size' => 12,
                    'requires_verification' => true,
                    'agriculture_focus' => true,
                    'irrigation_bonus' => 1.3,
                    'region_specific' => 'rural',
                ],
                'icon' => 'seedling',
            ],
        ];

        foreach ($specializedChallenges as $challengeData) {
            $organization = $organizations->random();

            Challenge::updateOrCreate(
                ['name' => $challengeData['name']],
                array_merge($challengeData, [
                    'organization_id' => $organization->id,
                ])
            );
        }
    }

    /**
     * Crear desafíos estacionales
     */
    private function createSeasonalChallenges($organizations): void
    {
        $this->command->info('🌤️ Creando desafíos estacionales...');

        $seasonalChallenges = [
            [
                'name' => 'Desafío de Verano Solar',
                'description' => 'Aprovecha al máximo la energía solar durante los meses de verano. Genera la mayor cantidad de energía fotovoltaica posible.',
                'type' => 'team',
                'target_kwh' => 4000.00,
                'points_reward' => 700,
                'start_date' => Carbon::create(2025, 6, 1),
                'end_date' => Carbon::create(2025, 8, 31),
                'is_active' => false, // Futuro
                'criteria' => [
                    'min_team_size' => 4,
                    'max_team_size' => 20,
                    'requires_verification' => true,
                    'summer_focus' => true,
                    'peak_summer_bonus' => 1.5,
                    'solar_focus' => true,
                ],
                'icon' => 'solar-panel',
            ],
            [
                'name' => 'Desafío de Invierno Eólico',
                'description' => 'Genera energía eólica durante los meses de mayor viento. Aprovecha las condiciones meteorológicas del invierno.',
                'type' => 'team',
                'target_kwh' => 2500.00,
                'points_reward' => 500,
                'start_date' => Carbon::create(2025, 12, 1),
                'end_date' => Carbon::create(2026, 2, 28),
                'is_active' => false, // Futuro
                'criteria' => [
                    'min_team_size' => 3,
                    'max_team_size' => 15,
                    'requires_verification' => true,
                    'winter_focus' => true,
                    'winter_wind_bonus' => 1.3,
                    'wind_focus' => true,
                ],
                'icon' => 'wind-turbine',
            ],
            [
                'name' => 'Desafío de Primavera Verde',
                'description' => 'Celebra la renovación de la naturaleza con energía limpia. Enfócate en proyectos de sostenibilidad y renovación.',
                'type' => 'team',
                'target_kwh' => 1500.00,
                'points_reward' => 350,
                'start_date' => Carbon::create(2025, 3, 1),
                'end_date' => Carbon::create(2025, 5, 31),
                'is_active' => false, // Pasado
                'criteria' => [
                    'min_team_size' => 3,
                    'max_team_size' => 18,
                    'requires_verification' => false,
                    'spring_focus' => true,
                    'spring_growth_bonus' => 1.2,
                    'renewal_focus' => true,
                ],
                'icon' => 'leaf',
            ],
            [
                'name' => 'Desafío de Otoño Eficiente',
                'description' => 'Optimiza tu consumo energético durante el otoño. Reduce el desperdicio y mejora la eficiencia.',
                'type' => 'individual',
                'target_kwh' => 300.00,
                'points_reward' => 200,
                'start_date' => Carbon::create(2024, 9, 1),
                'end_date' => Carbon::create(2024, 11, 30),
                'is_active' => false, // Pasado
                'criteria' => [
                    'requires_verification' => true,
                    'autumn_focus' => true,
                    'efficiency_focus' => true,
                    'autumn_savings_bonus' => 1.4,
                ],
                'icon' => 'recycle',
            ],
        ];

        foreach ($seasonalChallenges as $challengeData) {
            $organization = $organizations->random();

            Challenge::updateOrCreate(
                ['name' => $challengeData['name']],
                array_merge($challengeData, [
                    'organization_id' => $organization->id,
                ])
            );
        }
    }

    /**
     * Crear desafíos de competencia
     */
    private function createCompetitiveChallenges($organizations): void
    {
        $this->command->info('🏆 Creando desafíos de competencia...');

        $competitiveChallenges = [
            [
                'name' => 'Desafío Competitivo Solar',
                'description' => 'Competición de alto nivel para equipos solares expertos. Demuestra tu dominio en energía fotovoltaica y gana premios especiales.',
                'type' => 'team',
                'target_kwh' => 8000.00,
                'points_reward' => 1200,
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->addMonths(3),
                'is_active' => true,
                'criteria' => [
                    'min_team_size' => 5,
                    'max_team_size' => 15,
                    'requires_verification' => true,
                    'competitive_level' => true,
                    'expert_level' => true,
                    'min_experience_months' => 12,
                    'solar_expertise' => true,
                ],
                'icon' => 'solar-panel',
            ],
            [
                'name' => 'Desafío de Desafíos Energéticos',
                'description' => 'Competición general de energía renovable. Participa en múltiples desafíos y acumula puntos para ganar el título de campeón.',
                'type' => 'team',
                'target_kwh' => 15000.00,
                'points_reward' => 2000,
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->addMonths(6),
                'is_active' => true,
                'criteria' => [
                    'min_team_size' => 6,
                    'max_team_size' => 20,
                    'requires_verification' => true,
                    'competitive_level' => true,
                    'multi_challenge' => true,
                    'champion_title' => true,
                    'bonus_multiplier' => 1.5,
                ],
                'icon' => 'trophy',
            ],
            [
                'name' => 'Desafío de Innovación Verde',
                'description' => 'Desarrolla nuevas tecnologías y soluciones energéticas. Este desafío premia la creatividad y la innovación en energía renovable.',
                'type' => 'organization',
                'target_kwh' => 20000.00,
                'points_reward' => 3000,
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->addMonths(8),
                'is_active' => true,
                'criteria' => [
                    'min_team_size' => 8,
                    'max_team_size' => 30,
                    'requires_verification' => true,
                    'innovation_focus' => true,
                    'research_required' => true,
                    'patent_bonus' => 2.0,
                    'expert_level' => true,
                ],
                'icon' => 'lightbulb',
            ],
        ];

        foreach ($competitiveChallenges as $challengeData) {
            $organization = $organizations->random();

            Challenge::updateOrCreate(
                ['name' => $challengeData['name']],
                array_merge($challengeData, [
                    'organization_id' => $organization->id,
                ])
            );
        }
    }

    /**
     * Crear desafíos comunitarios
     */
    private function createCommunityChallenges($organizations): void
    {
        $this->command->info('🤝 Creando desafíos comunitarios...');

        $communityChallenges = [
            [
                'name' => 'Desafío del Barrio Verde',
                'description' => 'Transforma tu barrio en un ejemplo de sostenibilidad energética. Trabaja con tus vecinos para crear un barrio más verde.',
                'type' => 'team',
                'target_kwh' => 1000.00,
                'points_reward' => 400,
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->addMonths(3),
                'is_active' => true,
                'criteria' => [
                    'min_team_size' => 3,
                    'max_team_size' => 25,
                    'requires_verification' => false,
                    'community_focus' => true,
                    'neighborhood_bonus' => 1.3,
                    'participation_bonus' => 1.2,
                ],
                'icon' => 'house',
            ],
            [
                'name' => 'Desafío Rural Sostenible',
                'description' => 'Promueve la sostenibilidad energética en comunidades rurales. Desarrolla proyectos que beneficien a toda la comunidad.',
                'type' => 'team',
                'target_kwh' => 800.00,
                'points_reward' => 300,
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->addMonths(4),
                'is_active' => true,
                'criteria' => [
                    'min_team_size' => 2,
                    'max_team_size' => 15,
                    'requires_verification' => false,
                    'rural_focus' => true,
                    'community_benefit' => true,
                    'rural_bonus' => 1.25,
                    'region_specific' => 'rural',
                ],
                'icon' => 'tree',
            ],
            [
                'name' => 'Desafío de Jóvenes Verdes',
                'description' => 'Inspira a los jóvenes a participar en proyectos de energía renovable. Este desafío está diseñado específicamente para equipos de jóvenes.',
                'type' => 'team',
                'target_kwh' => 600.00,
                'points_reward' => 250,
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->addMonths(2),
                'is_active' => true,
                'criteria' => [
                    'min_team_size' => 2,
                    'max_team_size' => 20,
                    'requires_verification' => false,
                    'youth_focus' => true,
                    'education_bonus' => 1.2,
                    'max_age' => 25,
                    'learning_focus' => true,
                ],
                'icon' => 'graduation-cap',
            ],
            [
                'name' => 'Desafío de Mujeres en Energía',
                'description' => 'Promueve la participación de las mujeres en el sector energético. Empodera y apoya el liderazgo femenino en energía renovable.',
                'type' => 'team',
                'target_kwh' => 700.00,
                'points_reward' => 300,
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->addMonths(3),
                'is_active' => true,
                'criteria' => [
                    'min_team_size' => 2,
                    'max_team_size' => 15,
                    'requires_verification' => false,
                    'women_focus' => true,
                    'leadership_bonus' => 1.3,
                    'empowerment_focus' => true,
                    'gender_equality' => true,
                ],
                'icon' => 'venus',
            ],
        ];

        foreach ($communityChallenges as $challengeData) {
            $organization = $organizations->random();

            Challenge::updateOrCreate(
                ['name' => $challengeData['name']],
                array_merge($challengeData, [
                    'organization_id' => $organization->id,
                ])
            );
        }
    }

    /**
     * Generar estadísticas de desafíos
     */
    private function generateChallengeStats(): array
    {
        $total = Challenge::count();
        $active = Challenge::where('is_active', true)->count();
        $inactive = Challenge::where('is_active', false)->count();
        $team = Challenge::where('type', 'team')->count();
        $individual = Challenge::where('type', 'individual')->count();
        $organization = Challenge::where('type', 'organization')->count();

        return [
            'total_challenges' => $total,
            'active_challenges' => $active,
            'inactive_challenges' => $inactive,
            'team_challenges' => $team,
            'individual_challenges' => $individual,
            'organization_challenges' => $organization,
        ];
    }
}
