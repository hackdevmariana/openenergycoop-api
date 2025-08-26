<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlantGroup;
use App\Models\Plant;
use App\Models\User;
use Carbon\Carbon;

class PlantGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌿 Creando grupos taxonómicos de plantas españoles para la cooperativa energética...');

        // Obtener plantas y usuarios disponibles
        $plants = Plant::all();
        $users = User::all();

        if ($plants->isEmpty()) {
            $this->command->error('❌ No hay plantas disponibles. Ejecuta PlantSeeder primero.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles. Ejecuta RolesAndAdminSeeder primero.');
            return;
        }

        // 1. Grupos de Árboles Autóctonos
        $this->createNativeTreeGroups($plants, $users);

        // 2. Grupos de Cultivos Tradicionales
        $this->createTraditionalCropGroups($plants, $users);

        // 3. Grupos de Plantas Aromáticas
        $this->createAromaticPlantGroups($plants, $users);

        // 4. Grupos de Reforestación
        $this->createReforestationGroups($plants, $users);

        // 5. Grupos de Huerta Mediterránea
        $this->createMediterraneanGardenGroups($plants, $users);

        $this->command->info('✅ PlantGroupSeeder completado. Se crearon ' . PlantGroup::count() . ' grupos taxonómicos de plantas españoles.');
    }

    /**
     * Crear grupos de árboles autóctonos
     */
    private function createNativeTreeGroups($plants, $users): void
    {
        $this->command->info('🌳 Creando grupos de árboles autóctonos...');

        $nativeTreeGroups = [
            [
                'name' => 'Robledal',
                'custom_label' => 'Grupo de robles melojos',
                'plant_type' => 'Roble Melojo',
                'min_plants' => 50,
                'max_plants' => 200,
            ],
            [
                'name' => 'Hayedo',
                'custom_label' => 'Grupo de hayas',
                'plant_type' => 'Haya',
                'min_plants' => 30,
                'max_plants' => 150,
            ],
            [
                'name' => 'Abetal',
                'custom_label' => 'Grupo de abetos',
                'plant_type' => 'Abeto',
                'min_plants' => 40,
                'max_plants' => 180,
            ],
            [
                'name' => 'Encinal',
                'custom_label' => 'Grupo de encinas',
                'plant_type' => 'Encina',
                'min_plants' => 60,
                'max_plants' => 250,
            ],
            [
                'name' => 'Pinar Silvestre',
                'custom_label' => 'Grupo de pinos silvestres',
                'plant_type' => 'Pino Silvestre',
                'min_plants' => 80,
                'max_plants' => 300,
            ],
            [
                'name' => 'Pinar Carrasco',
                'custom_label' => 'Grupo de pinos carrascos',
                'plant_type' => 'Pino Carrasco',
                'min_plants' => 100,
                'max_plants' => 400,
            ],
        ];

        foreach ($nativeTreeGroups as $groupData) {
            $plant = $plants->where('name', $groupData['plant_type'])->first();
            if (!$plant) continue;

            $user = $users->random();
            $numberOfPlants = rand($groupData['min_plants'], $groupData['max_plants']);
            $co2Avoided = $plant->calculateCo2Avoided($numberOfPlants);

            PlantGroup::updateOrCreate(
                [
                    'name' => $groupData['name'],
                    'plant_id' => $plant->id,
                ],
                [
                    'user_id' => $user->id,
                    'number_of_plants' => $numberOfPlants,
                    'co2_avoided_total' => $co2Avoided,
                    'custom_label' => $groupData['custom_label'],
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Crear grupos de cultivos tradicionales
     */
    private function createTraditionalCropGroups($plants, $users): void
    {
        $this->command->info('🌾 Creando grupos de cultivos tradicionales...');

        $traditionalCropGroups = [
            [
                'name' => 'Olivar',
                'custom_label' => 'Grupo de olivos',
                'plant_type' => 'Olivo',
                'min_plants' => 20,
                'max_plants' => 100,
            ],
            [
                'name' => 'Almendral',
                'custom_label' => 'Grupo de almendros',
                'plant_type' => 'Almendro',
                'min_plants' => 25,
                'max_plants' => 120,
            ],
            [
                'name' => 'Viña',
                'custom_label' => 'Grupo de vides',
                'plant_type' => 'Vid',
                'min_plants' => 100,
                'max_plants' => 500,
            ],
            [
                'name' => 'Cerezal',
                'custom_label' => 'Grupo de cerezos',
                'plant_type' => 'Cerezo',
                'min_plants' => 15,
                'max_plants' => 80,
            ],
            [
                'name' => 'Manzanal',
                'custom_label' => 'Grupo de manzanos',
                'plant_type' => 'Manzano',
                'min_plants' => 12,
                'max_plants' => 60,
            ],
            [
                'name' => 'Peral',
                'custom_label' => 'Grupo de perales',
                'plant_type' => 'Peral',
                'min_plants' => 10,
                'max_plants' => 50,
            ],
        ];

        foreach ($traditionalCropGroups as $groupData) {
            $plant = $plants->where('name', $groupData['plant_type'])->first();
            if (!$plant) continue;

            $user = $users->random();
            $numberOfPlants = rand($groupData['min_plants'], $groupData['max_plants']);
            $co2Avoided = $plant->calculateCo2Avoided($numberOfPlants);

            PlantGroup::updateOrCreate(
                [
                    'name' => $groupData['name'],
                    'plant_id' => $plant->id,
                ],
                [
                    'user_id' => $user->id,
                    'number_of_plants' => $numberOfPlants,
                    'co2_avoided_total' => $co2Avoided,
                    'custom_label' => $groupData['custom_label'],
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Crear grupos de plantas aromáticas
     */
    private function createAromaticPlantGroups($plants, $users): void
    {
        $this->command->info('🌺 Creando grupos de plantas aromáticas...');

        $aromaticPlantGroups = [
            [
                'name' => 'Lavandario',
                'custom_label' => 'Grupo de lavandas',
                'plant_type' => 'Lavanda',
                'min_plants' => 200,
                'max_plants' => 800,
            ],
            [
                'name' => 'Romeral',
                'custom_label' => 'Grupo de romeros',
                'plant_type' => 'Romero',
                'min_plants' => 150,
                'max_plants' => 600,
            ],
            [
                'name' => 'Tomillar',
                'custom_label' => 'Grupo de tomillos',
                'plant_type' => 'Tomillo',
                'min_plants' => 300,
                'max_plants' => 1000,
            ],
            [
                'name' => 'Salvial',
                'custom_label' => 'Grupo de salvias',
                'plant_type' => 'Salvia',
                'min_plants' => 120,
                'max_plants' => 400,
            ],
            [
                'name' => 'Jazminar',
                'custom_label' => 'Grupo de jazmines',
                'plant_type' => 'Jazmín',
                'min_plants' => 80,
                'max_plants' => 250,
            ],
        ];

        foreach ($aromaticPlantGroups as $groupData) {
            $plant = $plants->where('name', $groupData['plant_type'])->first();
            if (!$plant) continue;

            $user = $users->random();
            $numberOfPlants = rand($groupData['min_plants'], $groupData['max_plants']);
            $co2Avoided = $plant->calculateCo2Avoided($numberOfPlants);

            PlantGroup::updateOrCreate(
                [
                    'name' => $groupData['name'],
                    'plant_id' => $plant->id,
                ],
                [
                    'user_id' => $user->id,
                    'number_of_plants' => $numberOfPlants,
                    'co2_avoided_total' => $co2Avoided,
                    'custom_label' => $groupData['custom_label'],
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Crear grupos de reforestación
     */
    private function createReforestationGroups($plants, $users): void
    {
        $this->command->info('🌿 Creando grupos de reforestación...');

        $reforestationGroups = [
            [
                'name' => 'Pinar Piñonero',
                'custom_label' => 'Grupo de pinos piñoneros',
                'plant_type' => 'Pino Piñonero',
                'min_plants' => 150,
                'max_plants' => 500,
            ],
            [
                'name' => 'Cipresal',
                'custom_label' => 'Grupo de cipreses',
                'plant_type' => 'Ciprés',
                'min_plants' => 80,
                'max_plants' => 300,
            ],
            [
                'name' => 'Arcedo',
                'custom_label' => 'Grupo de arces',
                'plant_type' => 'Arce',
                'min_plants' => 60,
                'max_plants' => 200,
            ],
            [
                'name' => 'Fresneda',
                'custom_label' => 'Grupo de fresnos',
                'plant_type' => 'Fresno',
                'min_plants' => 100,
                'max_plants' => 350,
            ],
        ];

        foreach ($reforestationGroups as $groupData) {
            $plant = $plants->where('name', $groupData['plant_type'])->first();
            if (!$plant) continue;

            $user = $users->random();
            $numberOfPlants = rand($groupData['min_plants'], $groupData['max_plants']);
            $co2Avoided = $plant->calculateCo2Avoided($numberOfPlants);

            PlantGroup::updateOrCreate(
                [
                    'name' => $groupData['name'],
                    'plant_id' => $plant->id,
                ],
                [
                    'user_id' => $user->id,
                    'number_of_plants' => $numberOfPlants,
                    'co2_avoided_total' => $co2Avoided,
                    'custom_label' => $groupData['custom_label'],
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Crear grupos de huerta mediterránea
     */
    private function createMediterraneanGardenGroups($plants, $users): void
    {
        $this->command->info('🥬 Creando grupos de huerta mediterránea...');

        $mediterraneanGardenGroups = [
            [
                'name' => 'Albahacar',
                'custom_label' => 'Grupo de albahacas',
                'plant_type' => 'Albahaca',
                'min_plants' => 500,
                'max_plants' => 1500,
            ],
            [
                'name' => 'Mentar',
                'custom_label' => 'Grupo de mentas',
                'plant_type' => 'Menta',
                'min_plants' => 400,
                'max_plants' => 1200,
            ],
            [
                'name' => 'Oreganal',
                'custom_label' => 'Grupo de oréganos',
                'plant_type' => 'Orégano',
                'min_plants' => 300,
                'max_plants' => 800,
            ],
            [
                'name' => 'Perejilar',
                'custom_label' => 'Grupo de perejiles',
                'plant_type' => 'Perejil',
                'min_plants' => 600,
                'max_plants' => 1800,
            ],
            [
                'name' => 'Cilantral',
                'custom_label' => 'Grupo de cilantros',
                'plant_type' => 'Cilantro',
                'min_plants' => 350,
                'max_plants' => 1000,
            ],
        ];

        foreach ($mediterraneanGardenGroups as $groupData) {
            $plant = $plants->where('name', $groupData['plant_type'])->first();
            if (!$plant) continue;

            $user = $users->random();
            $numberOfPlants = rand($groupData['min_plants'], $groupData['max_plants']);
            $co2Avoided = $plant->calculateCo2Avoided($numberOfPlants);

            PlantGroup::updateOrCreate(
                [
                    'name' => $groupData['name'],
                    'plant_id' => $plant->id,
                ],
                [
                    'user_id' => $user->id,
                    'number_of_plants' => $numberOfPlants,
                    'co2_avoided_total' => $co2Avoided,
                    'custom_label' => $groupData['custom_label'],
                    'is_active' => true,
                ]
            );
        }
    }
}
