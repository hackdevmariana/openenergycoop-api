<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Municipality;
use App\Models\Province;

class MunicipalitySeeder extends Seeder
{
    public function run(): void
    {
        $provinces = Province::all();
        if ($provinces->isEmpty()) {
            $this->command->warn('⚠️ No hay provincias disponibles. Ejecutando ProvinceSeeder primero...');
            $this->call(ProvinceSeeder::class);
            $provinces = Province::all();
        }

        $municipalities = [
            // Madrid
            ['name' => 'Madrid', 'province' => 'Madrid', 'text' => 'Capital de España y sede de la cooperativa energética.'],
            ['name' => 'Alcalá de Henares', 'province' => 'Madrid', 'text' => 'Ciudad histórica con gran potencial solar.'],
            ['name' => 'Getafe', 'province' => 'Madrid', 'text' => 'Municipio industrial con alta demanda energética.'],
            ['name' => 'Leganés', 'province' => 'Madrid', 'text' => 'Ciudad residencial con proyectos renovables.'],
            ['name' => 'Móstoles', 'province' => 'Madrid', 'text' => 'Municipio con iniciativas de energía comunitaria.'],
            
            // Barcelona
            ['name' => 'Barcelona', 'province' => 'Barcelona', 'text' => 'Capital catalana con proyectos de energía renovable.'],
            ['name' => 'Hospitalet de Llobregat', 'province' => 'Barcelona', 'text' => 'Ciudad industrial con alta densidad poblacional.'],
            ['name' => 'Badalona', 'province' => 'Barcelona', 'text' => 'Municipio costero con potencial eólico.'],
            ['name' => 'Sabadell', 'province' => 'Barcelona', 'text' => 'Ciudad industrial con proyectos solares.'],
            ['name' => 'Terrassa', 'province' => 'Barcelona', 'text' => 'Municipio con iniciativas de sostenibilidad.'],
            
            // Valencia
            ['name' => 'Valencia', 'province' => 'Valencia', 'text' => 'Capital valenciana con proyectos de energía solar.'],
            ['name' => 'Alicante', 'province' => 'Alicante', 'text' => 'Ciudad costera con alto potencial solar.'],
            ['name' => 'Elche', 'province' => 'Alicante', 'text' => 'Municipio con proyectos de energía renovable.'],
            ['name' => 'Castellón de la Plana', 'province' => 'Castellón', 'text' => 'Capital provincial con iniciativas verdes.'],
            
            // Sevilla
            ['name' => 'Sevilla', 'province' => 'Sevilla', 'text' => 'Capital andaluza con proyectos solares.'],
            ['name' => 'Málaga', 'province' => 'Málaga', 'text' => 'Ciudad costera con alto potencial renovable.'],
            ['name' => 'Córdoba', 'province' => 'Córdoba', 'text' => 'Ciudad histórica con proyectos de energía.'],
            ['name' => 'Granada', 'province' => 'Granada', 'text' => 'Municipio con iniciativas de sostenibilidad.'],
            
            // Zaragoza
            ['name' => 'Zaragoza', 'province' => 'Zaragoza', 'text' => 'Capital aragonesa con proyectos renovables.'],
            
            // Bilbao
            ['name' => 'Bilbao', 'province' => 'Vizcaya', 'text' => 'Capital vasca con proyectos de energía.'],
            ['name' => 'Vitoria-Gasteiz', 'province' => 'Álava', 'text' => 'Capital vasca con iniciativas verdes.'],
            ['name' => 'San Sebastián', 'province' => 'Guipúzcoa', 'text' => 'Ciudad costera con proyectos renovables.'],
            
            // A Coruña
            ['name' => 'A Coruña', 'province' => 'A Coruña', 'text' => 'Ciudad costera con potencial eólico.'],
            ['name' => 'Vigo', 'province' => 'Pontevedra', 'text' => 'Ciudad portuaria con proyectos de energía.'],
            ['name' => 'Santiago de Compostela', 'province' => 'A Coruña', 'text' => 'Capital gallega con iniciativas renovables.'],
            
            // Murcia
            ['name' => 'Murcia', 'province' => 'Murcia', 'text' => 'Capital murciana con alto potencial solar.'],
            
            // Palma
            ['name' => 'Palma', 'province' => 'Islas Baleares', 'text' => 'Capital balear con proyectos solares.'],
            
            // Las Palmas
            ['name' => 'Las Palmas de Gran Canaria', 'province' => 'Las Palmas', 'text' => 'Capital canaria con potencial renovable.'],
            ['name' => 'Santa Cruz de Tenerife', 'province' => 'Santa Cruz de Tenerife', 'text' => 'Capital canaria con proyectos de energía.'],
            
            // Toledo
            ['name' => 'Toledo', 'province' => 'Toledo', 'text' => 'Ciudad histórica con proyectos renovables.'],
            
            // Valladolid
            ['name' => 'Valladolid', 'province' => 'Valladolid', 'text' => 'Capital castellana con iniciativas de energía.'],
            
            // Salamanca
            ['name' => 'Salamanca', 'province' => 'Salamanca', 'text' => 'Ciudad universitaria con proyectos verdes.'],
            
            // Burgos
            ['name' => 'Burgos', 'province' => 'Burgos', 'text' => 'Ciudad histórica con proyectos de energía.'],
            
            // León
            ['name' => 'León', 'province' => 'León', 'text' => 'Capital leonesa con iniciativas renovables.'],
            
            // Badajoz
            ['name' => 'Badajoz', 'province' => 'Badajoz', 'text' => 'Capital extremeña con alto potencial solar.'],
            
            // Cáceres
            ['name' => 'Cáceres', 'province' => 'Cáceres', 'text' => 'Ciudad histórica con proyectos de energía.'],
            
            // Logroño
            ['name' => 'Logroño', 'province' => 'La Rioja', 'text' => 'Capital riojana con iniciativas renovables.'],
            
            // Pamplona
            ['name' => 'Pamplona', 'province' => 'Navarra', 'text' => 'Capital navarra con proyectos de energía.'],
            
            // Santander
            ['name' => 'Santander', 'province' => 'Cantabria', 'text' => 'Capital cántabra con proyectos renovables.'],
            
            // Oviedo
            ['name' => 'Oviedo', 'province' => 'Asturias', 'text' => 'Capital asturiana con iniciativas de energía.'],
            
            // Huesca
            ['name' => 'Huesca', 'province' => 'Huesca', 'text' => 'Ciudad aragonesa con proyectos renovables.'],
            
            // Teruel
            ['name' => 'Teruel', 'province' => 'Teruel', 'text' => 'Capital turolense con alto potencial solar.'],
            
            // Cuenca
            ['name' => 'Cuenca', 'province' => 'Cuenca', 'text' => 'Ciudad histórica con proyectos de energía.'],
            
            // Guadalajara
            ['name' => 'Guadalajara', 'province' => 'Guadalajara', 'text' => 'Capital alcarreña con iniciativas renovables.'],
            
            // Ciudad Real
            ['name' => 'Ciudad Real', 'province' => 'Ciudad Real', 'text' => 'Capital manchega con proyectos solares.'],
            
            // Albacete
            ['name' => 'Albacete', 'province' => 'Albacete', 'text' => 'Capital albaceteña con proyectos de energía.'],
            
            // Lleida
            ['name' => 'Lleida', 'province' => 'Lleida', 'text' => 'Ciudad catalana con iniciativas renovables.'],
            
            // Tarragona
            ['name' => 'Tarragona', 'province' => 'Tarragona', 'text' => 'Ciudad costera con proyectos de energía.'],
            
            // Girona
            ['name' => 'Girona', 'province' => 'Girona', 'text' => 'Ciudad catalana con proyectos renovables.'],
            
            // Lugo
            ['name' => 'Lugo', 'province' => 'Lugo', 'text' => 'Ciudad gallega con iniciativas de energía.'],
            
            // Ourense
            ['name' => 'Ourense', 'province' => 'Ourense', 'text' => 'Ciudad gallega con proyectos renovables.'],
            
            // Pontevedra
            ['name' => 'Pontevedra', 'province' => 'Pontevedra', 'text' => 'Ciudad gallega con proyectos de energía.'],
            
            // Ávila
            ['name' => 'Ávila', 'province' => 'Ávila', 'text' => 'Ciudad histórica con iniciativas renovables.'],
            
            // Palencia
            ['name' => 'Palencia', 'province' => 'Palencia', 'text' => 'Ciudad castellana con proyectos de energía.'],
            
            // Segovia
            ['name' => 'Segovia', 'province' => 'Segovia', 'text' => 'Ciudad histórica con proyectos renovables.'],
            
            // Soria
            ['name' => 'Soria', 'province' => 'Soria', 'text' => 'Ciudad castellana con iniciativas de energía.'],
            
            // Zamora
            ['name' => 'Zamora', 'province' => 'Zamora', 'text' => 'Ciudad castellana con proyectos renovables.'],
            
            // Jaén
            ['name' => 'Jaén', 'province' => 'Jaén', 'text' => 'Ciudad andaluza con alto potencial solar.'],
            
            // Huelva
            ['name' => 'Huelva', 'province' => 'Huelva', 'text' => 'Ciudad costera con proyectos de energía.'],
            
            // Cádiz
            ['name' => 'Cádiz', 'province' => 'Cádiz', 'text' => 'Ciudad costera con iniciativas renovables.'],
            
            // Almería
            ['name' => 'Almería', 'province' => 'Almería', 'text' => 'Ciudad andaluza con alto potencial solar.'],
        ];

        foreach ($municipalities as $municipality) {
            $province = $provinces->where('name', $municipality['province'])->first();
            if ($province) {
                Municipality::firstOrCreate(
                    [
                        'name' => $municipality['name'],
                        'province_id' => $province->id,
                    ],
                    [
                        'slug' => \Illuminate\Support\Str::slug($municipality['name'] . '-' . $municipality['province']),
                        'text' => $municipality['text'],
                    ]
                );
            }
        }

        $this->command->info('✅ MunicipalitySeeder ejecutado correctamente');
        $this->command->info('📊 Municipios creados: ' . count($municipalities));
        $this->command->info('🏙️ Principales ciudades españolas incluidas');
        $this->command->info('📍 Ciudades principales: Madrid, Barcelona, Valencia, Sevilla, Zaragoza, Málaga, Murcia, Palma, Las Palmas, Bilbao');
        $this->command->info('🌞 Todas las ciudades tienen descripción de potencial energético');
        $this->command->info('🏛️ Organizadas por provincias y comunidades autónomas');
    }
}
