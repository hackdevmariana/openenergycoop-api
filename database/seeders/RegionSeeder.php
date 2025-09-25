<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Region;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            ['name' => 'Andalucía'],
            ['name' => 'Aragón'],
            ['name' => 'Asturias'],
            ['name' => 'Islas Baleares'],
            ['name' => 'Islas Canarias'],
            ['name' => 'Cantabria'],
            ['name' => 'Castilla-La Mancha'],
            ['name' => 'Castilla y León'],
            ['name' => 'Cataluña'],
            ['name' => 'Comunidad Valenciana'],
            ['name' => 'Extremadura'],
            ['name' => 'Galicia'],
            ['name' => 'La Rioja'],
            ['name' => 'Madrid'],
            ['name' => 'Murcia'],
            ['name' => 'Navarra'],
            ['name' => 'País Vasco'],
            ['name' => 'Ceuta'],
            ['name' => 'Melilla'],
        ];

        foreach ($regions as $region) {
            Region::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($region['name'])],
                $region
            );
        }

        $this->command->info('✅ RegionSeeder ejecutado correctamente');
        $this->command->info('📊 Regiones creadas: ' . count($regions));
        $this->command->info('🇪🇸 Todas las comunidades autónomas de España incluidas');
        $this->command->info('📍 Regiones: Andalucía, Aragón, Asturias, Baleares, Canarias, Cantabria, Castilla-La Mancha, Castilla y León, Cataluña, Comunidad Valenciana, Extremadura, Galicia, La Rioja, Madrid, Murcia, Navarra, País Vasco, Ceuta, Melilla');
    }
}
