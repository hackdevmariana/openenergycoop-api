<?php

namespace Database\Factories;

use App\Models\Municipality;
use App\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Municipality>
 */
class MunicipalityFactory extends Factory
{
    protected $model = Municipality::class;

    /**
     * Some example Spanish municipalities
     */
    private static $spanishMunicipalities = [
        // Madrid
        'Madrid' => ['Madrid', 'Alcalá de Henares', 'Fuenlabrada', 'Móstoles', 'Alcorcón', 'Leganés', 'Getafe', 'Alcobendas', 'Torrejón de Ardoz', 'Parla'],
        
        // Barcelona
        'Barcelona' => ['Barcelona', 'L\'Hospitalet de Llobregat', 'Badalona', 'Terrassa', 'Sabadell', 'Mataró', 'Santa Coloma de Gramenet', 'Cornellà de Llobregat', 'Granollers', 'Vilanova i la Geltrú'],
        
        // Valencia
        'Valencia' => ['Valencia', 'Gandia', 'Torrent', 'Sagunto', 'Paterna', 'Alzira', 'Xàtiva', 'Cullera', 'Dénia', 'Elche'],
        
        // Sevilla
        'Sevilla' => ['Sevilla', 'Dos Hermanas', 'Alcalá de Guadaíra', 'Utrera', 'Mairena del Aljarafe', 'Écija', 'La Rinconada', 'Coria del Río', 'Lebrija', 'Morón de la Frontera'],
        
        // Málaga
        'Málaga' => ['Málaga', 'Marbella', 'Jerez de la Frontera', 'Algeciras', 'Fuengirola', 'Torremolinos', 'Benalmádena', 'Estepona', 'Ronda', 'Antequera'],
        
        // Zaragoza
        'Zaragoza' => ['Zaragoza', 'Huesca', 'Teruel', 'Calatayud', 'Utebo', 'Ejea de los Caballeros', 'Barbastro', 'Monzón', 'Jaca', 'Alcañiz'],
        
        // Bizkaia
        'Bizkaia' => ['Bilbao', 'Getxo', 'Portugalete', 'Santurtzi', 'Barakaldo', 'Leioa', 'Basauri', 'Galdakao', 'Durango', 'Erandio'],
    ];

    public function definition(): array
    {
        $province = Province::factory()->create();
        $provinceName = $province->name;
        
        // Si la provincia tiene municipios definidos, usar uno aleatorio
        if (isset(self::$spanishMunicipalities[$provinceName])) {
            $municipalityName = $this->faker->randomElement(self::$spanishMunicipalities[$provinceName]);
        } else {
            $municipalityName = $this->faker->city();
        }

        $slug = Str::slug($municipalityName . '-' . $provinceName);

        return [
            'name' => $municipalityName,
            'slug' => $slug,
            'text' => $this->faker->optional(0.7)->paragraph(3), // 70% tienen descripción de operación
            'province_id' => $province->id,
        ];
    }

    /**
     * Create municipality for specific province
     */
    public function forProvince(Province $province): static
    {
        return $this->state(function (array $attributes) use ($province) {
            $provinceName = $province->name;
            
            if (isset(self::$spanishMunicipalities[$provinceName])) {
                $municipalityName = $this->faker->randomElement(self::$spanishMunicipalities[$provinceName]);
            } else {
                $municipalityName = $this->faker->city();
            }

            $slug = Str::slug($municipalityName . '-' . $provinceName);

            return [
                'name' => $municipalityName,
                'slug' => $slug,
                'province_id' => $province->id,
            ];
        });
    }

    /**
     * Municipality where the cooperative operates (has description)
     */
    public function operating(): static
    {
        return $this->state(fn (array $attributes) => [
            'text' => $this->faker->paragraphs(2, true) . ' La cooperativa energética opera en este municipio ofreciendo energía renovable y servicios de eficiencia energética a la comunidad local.',
        ]);
    }

    /**
     * Municipality where cooperative doesn't operate yet
     */
    public function notOperating(): static
    {
        return $this->state(fn (array $attributes) => [
            'text' => null,
        ]);
    }

    /**
     * Municipality with solar potential information
     */
    public function withSolarPotential(): static
    {
        return $this->state(fn (array $attributes) => [
            'text' => 'Este municipio cuenta con excelente potencial solar con más de ' . 
                     $this->faker->numberBetween(2800, 3200) . ' horas de sol al año. ' .
                     'La cooperativa ha identificado oportunidades para instalaciones fotovoltaicas ' .
                     'tanto en tejados residenciales como en proyectos comunitarios.',
        ]);
    }

    /**
     * Specific Spanish municipalities
     */
    public function madrid(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Madrid',
            'slug' => 'madrid-madrid',
            'text' => 'Capital de España y centro principal de operaciones de la cooperativa energética. Cuenta con múltiples proyectos de energía solar comunitaria y programas de eficiencia energética.',
        ]);
    }

    public function barcelona(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Barcelona',
            'slug' => 'barcelona-barcelona',
            'text' => 'Importante ciudad mediterránea con gran potencial solar. La cooperativa desarrolla proyectos innovadores de energía renovable urbana.',
        ]);
    }

    public function valencia(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Valencia',
            'slug' => 'valencia-valencia',
            'text' => 'Ciudad costera con excelente radiación solar. Proyectos piloto de energía solar comunitaria y almacenamiento.',
        ]);
    }

    public function sevilla(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Sevilla',
            'slug' => 'sevilla-sevilla',
            'text' => 'Una de las ciudades con mayor potencial solar de Europa. Múltiples instalaciones fotovoltaicas gestionadas por la cooperativa.',
        ]);
    }

    public function bilbao(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Bilbao',
            'slug' => 'bilbao-bizkaia',
            'text' => 'Ciudad industrial en transición hacia la sostenibilidad. Proyectos de renovables adaptados al clima atlántico.',
        ]);
    }

    /**
     * Create municipalities with weather monitoring
     */
    public function withWeatherMonitoring(): static
    {
        return $this->state(fn (array $attributes) => [
            'text' => $attributes['text'] . ' Este municipio cuenta con estación meteorológica propia para optimizar la producción de energía renovable.',
        ]);
    }

    /**
     * Create rural municipality (typically smaller, more solar potential)
     */
    public function rural(): static
    {
        $ruralNames = ['Villanueva de la Sierra', 'Puebla de Montalbán', 'Casas de Miravete', 'Valdecaballeros', 'Almonacid de Toledo'];
        
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement($ruralNames),
            'text' => 'Municipio rural con gran potencial para energía solar y eólica. La cooperativa trabaja con la comunidad local para desarrollar proyectos de autoconsumo colectivo.',
        ]);
    }

    /**
     * Create coastal municipality (wind + solar potential)
     */
    public function coastal(): static
    {
        $coastalNames = ['Puerto de Santa María', 'Sanxenxo', 'Calafell', 'Peñíscola', 'Castro Urdiales'];
        
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement($coastalNames),
            'text' => 'Municipio costero con excelente potencial eólico marino y solar. Proyectos híbridos de energía renovable.',
        ]);
    }
}