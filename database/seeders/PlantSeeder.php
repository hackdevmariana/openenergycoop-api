<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plant;

class PlantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Creando plantas españolas para la cooperativa energética...');

        // 1. Árboles Autóctonos de Aragón
        $this->createNativeTrees();

        // 2. Cultivos Tradicionales Aragoneses
        $this->createTraditionalCrops();

        // 3. Plantas de Jardín y Ornamentales
        $this->createGardenPlants();

        // 4. Plantas de Reforestación
        $this->createReforestationPlants();

        // 5. Plantas de Huerta Mediterránea
        $this->createMediterraneanGardenPlants();

        $this->command->info('✅ PlantSeeder completado. Se crearon ' . Plant::count() . ' plantas españolas.');
    }

    /**
     * Crear árboles autóctonos de Aragón
     */
    private function createNativeTrees(): void
    {
        $this->command->info('🌳 Creando árboles autóctonos de Aragón...');

        $nativeTrees = [
            [
                'name' => 'Pino Silvestre',
                'description' => 'El pino silvestre es una especie autóctona de los Pirineos aragoneses. Crece en altitudes de 1000 a 2000 metros y es fundamental para la biodiversidad de la región. Su madera es de alta calidad y proporciona refugio a numerosas especies animales.',
                'unit_label' => 'árbol',
                'co2_equivalent_per_unit_kg' => 25.5,
                'image' => 'plants/pino-silvestre.jpg',
            ],
            [
                'name' => 'Roble Melojo',
                'description' => 'El roble melojo es un árbol emblemático de Aragón, especialmente en las sierras de la provincia de Teruel. Sus bellotas son alimento fundamental para la fauna silvestre y su madera es muy apreciada en ebanistería.',
                'unit_label' => 'árbol',
                'co2_equivalent_per_unit_kg' => 28.3,
                'image' => 'plants/roble-melojo.jpg',
            ],
            [
                'name' => 'Haya',
                'description' => 'El haya es un árbol majestuoso que forma bosques impenetrables en los valles pirenaicos de Huesca. Su follaje denso crea un microclima húmedo ideal para otras especies vegetales y animales.',
                'unit_label' => 'árbol',
                'co2_equivalent_per_unit_kg' => 30.1,
                'image' => 'plants/haya.jpg',
            ],
            [
                'name' => 'Abeto',
                'description' => 'El abeto es el rey de los bosques pirenaicos aragoneses. Crece en las zonas más altas y frías, formando bosques de gran belleza y valor ecológico. Es fundamental para la conservación del suelo en las montañas.',
                'unit_label' => 'árbol',
                'co2_equivalent_per_unit_kg' => 32.7,
                'image' => 'plants/abeto.jpg',
            ],
            [
                'name' => 'Encina',
                'description' => 'La encina es el árbol más representativo de la España mediterránea y abunda en las tierras bajas de Aragón. Sus bellotas son la base de la alimentación del cerdo ibérico y su madera es excelente para leña y carbón.',
                'unit_label' => 'árbol',
                'co2_equivalent_per_unit_kg' => 22.8,
                'image' => 'plants/encina.jpg',
            ],
            [
                'name' => 'Pino Carrasco',
                'description' => 'El pino carrasco es una especie muy resistente que crece en las zonas más secas de Aragón, especialmente en Los Monegros. Es fundamental para la fijación de dunas y la lucha contra la desertificación.',
                'unit_label' => 'árbol',
                'co2_equivalent_per_unit_kg' => 20.4,
                'image' => 'plants/pino-carrasco.jpg',
            ],
        ];

        foreach ($nativeTrees as $tree) {
            Plant::updateOrCreate(
                ['name' => $tree['name']],
                array_merge($tree, ['is_active' => true])
            );
        }
    }

    /**
     * Crear cultivos tradicionales aragoneses
     */
    private function createTraditionalCrops(): void
    {
        $this->command->info('🌾 Creando cultivos tradicionales aragoneses...');

        $traditionalCrops = [
            [
                'name' => 'Olivo',
                'description' => 'El olivo es un cultivo milenario en Aragón, especialmente en las comarcas del Bajo Aragón. Los aceites de oliva aragoneses son reconocidos por su calidad y sabor único. Cada olivo centenario es un testigo de la historia agrícola de la región.',
                'unit_label' => 'árbol',
                'co2_equivalent_per_unit_kg' => 18.5,
                'image' => 'plants/olivo.jpg',
            ],
            [
                'name' => 'Almendro',
                'description' => 'El almendro es fundamental en la economía rural de Aragón, especialmente en las zonas de secano. Su floración temprana en febrero marca el inicio de la primavera y es un espectáculo natural de gran belleza.',
                'unit_label' => 'árbol',
                'co2_equivalent_per_unit_kg' => 16.2,
                'image' => 'plants/almendro.jpg',
            ],
            [
                'name' => 'Vid',
                'description' => 'La vid es un cultivo tradicional en Aragón, especialmente en las comarcas vitivinícolas como Cariñena, Calatayud y Campo de Borja. Los vinos aragoneses son reconocidos internacionalmente por su calidad y carácter único.',
                'unit_label' => 'planta',
                'co2_equivalent_per_unit_kg' => 12.8,
                'image' => 'plants/vid.jpg',
            ],
            [
                'name' => 'Cerezo',
                'description' => 'El cerezo es un cultivo importante en las zonas de montaña de Aragón, especialmente en el Valle del Jerte y las sierras de Huesca. La floración de los cerezos es un evento turístico de gran importancia en la región.',
                'unit_label' => 'árbol',
                'co2_equivalent_per_unit_kg' => 19.4,
                'image' => 'plants/cerezo.jpg',
            ],
            [
                'name' => 'Manzano',
                'description' => 'El manzano es un cultivo tradicional en las zonas de montaña de Aragón, especialmente en el Valle de Ansó y las sierras de Teruel. Las variedades autóctonas como la "Reineta" son muy apreciadas por su sabor.',
                'unit_label' => 'árbol',
                'co2_equivalent_per_unit_kg' => 17.6,
                'image' => 'plants/manzano.jpg',
            ],
            [
                'name' => 'Peral',
                'description' => 'El peral es un cultivo importante en las zonas de regadío de Aragón, especialmente en el Valle del Ebro. Las peras de Aragón son reconocidas por su dulzura y textura única.',
                'unit_label' => 'árbol',
                'co2_equivalent_per_unit_kg' => 18.9,
                'image' => 'plants/peral.jpg',
            ],
        ];

        foreach ($traditionalCrops as $crop) {
            Plant::updateOrCreate(
                ['name' => $crop['name']],
                array_merge($crop, ['is_active' => true])
            );
        }
    }

    /**
     * Crear plantas de jardín y ornamentales
     */
    private function createGardenPlants(): void
    {
        $this->command->info('🌺 Creando plantas de jardín y ornamentales...');

        $gardenPlants = [
            [
                'name' => 'Lavanda',
                'description' => 'La lavanda es una planta aromática muy apreciada en Aragón por su resistencia al clima seco y sus propiedades medicinales. Sus flores púrpuras crean paisajes de gran belleza en los jardines aragoneses.',
                'unit_label' => 'planta',
                'co2_equivalent_per_unit_kg' => 8.5,
                'image' => 'plants/lavanda.jpg',
            ],
            [
                'name' => 'Romero',
                'description' => 'El romero es una planta aromática fundamental en la cocina aragonesa y en la medicina tradicional. Crece de forma natural en las sierras de la región y es muy resistente a la sequía.',
                'unit_label' => 'planta',
                'co2_equivalent_per_unit_kg' => 7.2,
                'image' => 'plants/romero.jpg',
            ],
            [
                'name' => 'Tomillo',
                'description' => 'El tomillo es una planta aromática que crece abundantemente en las zonas secas de Aragón. Sus pequeñas flores rosadas atraen a numerosos insectos polinizadores, siendo fundamental para la biodiversidad.',
                'unit_label' => 'planta',
                'co2_equivalent_per_unit_kg' => 6.8,
                'image' => 'plants/tomillo.jpg',
            ],
            [
                'name' => 'Salvia',
                'description' => 'La salvia es una planta medicinal muy apreciada en Aragón por sus propiedades digestivas y antiinflamatorias. Sus flores azules son muy atractivas para las abejas y otros polinizadores.',
                'unit_label' => 'planta',
                'co2_equivalent_per_unit_kg' => 9.1,
                'image' => 'plants/salvia.jpg',
            ],
            [
                'name' => 'Jazmín',
                'description' => 'El jazmín es una planta trepadora muy apreciada en los jardines aragoneses por su fragancia intensa y sus flores blancas. Es ideal para cubrir pérgolas y muros, creando espacios de gran belleza.',
                'unit_label' => 'planta',
                'co2_equivalent_per_unit_kg' => 11.3,
                'image' => 'plants/jazmin.jpg',
            ],
        ];

        foreach ($gardenPlants as $plant) {
            Plant::updateOrCreate(
                ['name' => $plant['name']],
                array_merge($plant, ['is_active' => true])
            );
        }
    }

    /**
     * Crear plantas de reforestación
     */
    private function createReforestationPlants(): void
    {
        $this->command->info('🌿 Creando plantas de reforestación...');

        $reforestationPlants = [
            [
                'name' => 'Pino Piñonero',
                'description' => 'El pino piñonero es ideal para la reforestación de zonas degradadas en Aragón. Sus piñones son muy apreciados en la gastronomía y su madera es de buena calidad. Es muy resistente a la sequía.',
                'unit_label' => 'árbol',
                'co2_equivalent_per_unit_kg' => 23.7,
                'image' => 'plants/pino-pinonero.jpg',
            ],
            [
                'name' => 'Ciprés',
                'description' => 'El ciprés es una excelente opción para la reforestación en Aragón por su rápido crecimiento y resistencia. Es ideal para crear cortavientos y barreras naturales, protegiendo cultivos y suelos.',
                'unit_label' => 'árbol',
                'co2_equivalent_per_unit_kg' => 26.4,
                'image' => 'plants/cipres.jpg',
            ],
            [
                'name' => 'Arce',
                'description' => 'El arce es un árbol de hoja caduca ideal para la reforestación en zonas húmedas de Aragón. Sus hojas rojas en otoño crean paisajes de gran belleza y es muy apreciado por la fauna silvestre.',
                'unit_label' => 'árbol',
                'co2_equivalent_per_unit_kg' => 24.8,
                'image' => 'plants/arce.jpg',
            ],
            [
                'name' => 'Fresno',
                'description' => 'El fresno es un árbol muy resistente ideal para la reforestación en Aragón. Crece bien en suelos pobres y es fundamental para la estabilización de riberas y laderas.',
                'unit_label' => 'árbol',
                'co2_equivalent_per_unit_kg' => 21.9,
                'image' => 'plants/fresno.jpg',
            ],
        ];

        foreach ($reforestationPlants as $plant) {
            Plant::updateOrCreate(
                ['name' => $plant['name']],
                array_merge($plant, ['is_active' => true])
            );
        }
    }

    /**
     * Crear plantas de huerta mediterránea
     */
    private function createMediterraneanGardenPlants(): void
    {
        $this->command->info('🥬 Creando plantas de huerta mediterránea...');

        $mediterraneanGardenPlants = [
            [
                'name' => 'Albahaca',
                'description' => 'La albahaca es una hierba aromática fundamental en la cocina mediterránea aragonesa. Es fácil de cultivar en macetas y jardines, y sus hojas frescas son ideales para ensaladas y pesto.',
                'unit_label' => 'planta',
                'co2_equivalent_per_unit_kg' => 5.2,
                'image' => 'plants/albahaca.jpg',
            ],
            [
                'name' => 'Menta',
                'description' => 'La menta es una planta aromática muy apreciada en Aragón por su frescor y propiedades digestivas. Es ideal para infusiones y cócteles, y crece fácilmente en jardines húmedos.',
                'unit_label' => 'planta',
                'co2_equivalent_per_unit_kg' => 6.7,
                'image' => 'plants/menta.jpg',
            ],
            [
                'name' => 'Orégano',
                'description' => 'El orégano es una hierba aromática fundamental en la cocina aragonesa, especialmente en platos de cordero y embutidos. Crece de forma natural en las sierras y es muy resistente.',
                'unit_label' => 'planta',
                'co2_equivalent_per_unit_kg' => 7.8,
                'image' => 'plants/oregano.jpg',
            ],
            [
                'name' => 'Perejil',
                'description' => 'El perejil es una hierba aromática muy versátil en la cocina aragonesa. Es rico en vitaminas y minerales, y es fácil de cultivar en jardines y macetas.',
                'unit_label' => 'planta',
                'co2_equivalent_per_unit_kg' => 4.9,
                'image' => 'plants/perejil.jpg',
            ],
            [
                'name' => 'Cilantro',
                'description' => 'El cilantro es una hierba aromática muy apreciada en la cocina internacional que se cultiva cada vez más en Aragón. Sus hojas frescas son ideales para ensaladas y platos asiáticos.',
                'unit_label' => 'planta',
                'co2_equivalent_per_unit_kg' => 5.6,
                'image' => 'plants/cilantro.jpg',
            ],
        ];

        foreach ($mediterraneanGardenPlants as $plant) {
            Plant::updateOrCreate(
                ['name' => $plant['name']],
                array_merge($plant, ['is_active' => true])
            );
        }
    }
}
