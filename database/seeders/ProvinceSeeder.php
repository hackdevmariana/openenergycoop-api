<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Province;
use App\Models\Region;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $regions = Region::all();
        if ($regions->isEmpty()) {
            $this->command->warn('⚠️ No hay regiones disponibles. Ejecutando RegionSeeder primero...');
            $this->call(RegionSeeder::class);
            $regions = Region::all();
        }

        $provinces = [
            // Andalucía
            ['name' => 'Almería', 'region' => 'Andalucía'],
            ['name' => 'Cádiz', 'region' => 'Andalucía'],
            ['name' => 'Córdoba', 'region' => 'Andalucía'],
            ['name' => 'Granada', 'region' => 'Andalucía'],
            ['name' => 'Huelva', 'region' => 'Andalucía'],
            ['name' => 'Jaén', 'region' => 'Andalucía'],
            ['name' => 'Málaga', 'region' => 'Andalucía'],
            ['name' => 'Sevilla', 'region' => 'Andalucía'],
            
            // Aragón
            ['name' => 'Huesca', 'region' => 'Aragón'],
            ['name' => 'Teruel', 'region' => 'Aragón'],
            ['name' => 'Zaragoza', 'region' => 'Aragón'],
            
            // Asturias
            ['name' => 'Asturias', 'region' => 'Asturias'],
            
            // Islas Baleares
            ['name' => 'Islas Baleares', 'region' => 'Islas Baleares'],
            
            // Islas Canarias
            ['name' => 'Las Palmas', 'region' => 'Islas Canarias'],
            ['name' => 'Santa Cruz de Tenerife', 'region' => 'Islas Canarias'],
            
            // Cantabria
            ['name' => 'Cantabria', 'region' => 'Cantabria'],
            
            // Castilla-La Mancha
            ['name' => 'Albacete', 'region' => 'Castilla-La Mancha'],
            ['name' => 'Ciudad Real', 'region' => 'Castilla-La Mancha'],
            ['name' => 'Cuenca', 'region' => 'Castilla-La Mancha'],
            ['name' => 'Guadalajara', 'region' => 'Castilla-La Mancha'],
            ['name' => 'Toledo', 'region' => 'Castilla-La Mancha'],
            
            // Castilla y León
            ['name' => 'Ávila', 'region' => 'Castilla y León'],
            ['name' => 'Burgos', 'region' => 'Castilla y León'],
            ['name' => 'León', 'region' => 'Castilla y León'],
            ['name' => 'Palencia', 'region' => 'Castilla y León'],
            ['name' => 'Salamanca', 'region' => 'Castilla y León'],
            ['name' => 'Segovia', 'region' => 'Castilla y León'],
            ['name' => 'Soria', 'region' => 'Castilla y León'],
            ['name' => 'Valladolid', 'region' => 'Castilla y León'],
            ['name' => 'Zamora', 'region' => 'Castilla y León'],
            
            // Cataluña
            ['name' => 'Barcelona', 'region' => 'Cataluña'],
            ['name' => 'Girona', 'region' => 'Cataluña'],
            ['name' => 'Lleida', 'region' => 'Cataluña'],
            ['name' => 'Tarragona', 'region' => 'Cataluña'],
            
            // Comunidad Valenciana
            ['name' => 'Alicante', 'region' => 'Comunidad Valenciana'],
            ['name' => 'Castellón', 'region' => 'Comunidad Valenciana'],
            ['name' => 'Valencia', 'region' => 'Comunidad Valenciana'],
            
            // Extremadura
            ['name' => 'Badajoz', 'region' => 'Extremadura'],
            ['name' => 'Cáceres', 'region' => 'Extremadura'],
            
            // Galicia
            ['name' => 'A Coruña', 'region' => 'Galicia'],
            ['name' => 'Lugo', 'region' => 'Galicia'],
            ['name' => 'Ourense', 'region' => 'Galicia'],
            ['name' => 'Pontevedra', 'region' => 'Galicia'],
            
            // La Rioja
            ['name' => 'La Rioja', 'region' => 'La Rioja'],
            
            // Madrid
            ['name' => 'Madrid', 'region' => 'Madrid'],
            
            // Murcia
            ['name' => 'Murcia', 'region' => 'Murcia'],
            
            // Navarra
            ['name' => 'Navarra', 'region' => 'Navarra'],
            
            // País Vasco
            ['name' => 'Álava', 'region' => 'País Vasco'],
            ['name' => 'Guipúzcoa', 'region' => 'País Vasco'],
            ['name' => 'Vizcaya', 'region' => 'País Vasco'],
            
            // Ceuta
            ['name' => 'Ceuta', 'region' => 'Ceuta'],
            
            // Melilla
            ['name' => 'Melilla', 'region' => 'Melilla'],
        ];

        foreach ($provinces as $province) {
            $region = $regions->where('name', $province['region'])->first();
            if ($region) {
                Province::firstOrCreate(
                    ['slug' => \Illuminate\Support\Str::slug($province['name'])],
                    [
                        'name' => $province['name'],
                        'region_id' => $region->id,
                    ]
                );
            }
        }

        $this->command->info('✅ ProvinceSeeder ejecutado correctamente');
        $this->command->info('📊 Provincias creadas: ' . count($provinces));
        $this->command->info('🇪🇸 Todas las provincias de España incluidas');
        $this->command->info('📍 Provincias principales: Madrid, Barcelona, Valencia, Sevilla, Zaragoza, Málaga, Murcia, Palma, Las Palmas, Bilbao');
        $this->command->info('🏛️ Organizadas por comunidades autónomas');
    }
}
