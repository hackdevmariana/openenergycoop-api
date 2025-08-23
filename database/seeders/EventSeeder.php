<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener organizaciones existentes o crear algunas
        $organizations = Organization::all();
        if ($organizations->isEmpty()) {
            $organizations = Organization::factory(3)->create();
        }

        // Crear eventos de ejemplo
        $this->createSampleEvents($organizations);
    }

    /**
     * Crear eventos de ejemplo
     */
    private function createSampleEvents($organizations): void
    {
        // Eventos de conferencia
        Event::factory()
            ->conference()
            ->public()
            ->published()
            ->upcoming()
            ->spanish()
            ->count(5)
            ->create([
                'organization_id' => $organizations->random()->id,
            ]);

        // Eventos de taller
        Event::factory()
            ->workshop()
            ->public()
            ->published()
            ->upcoming()
            ->spanish()
            ->count(3)
            ->create([
                'organization_id' => $organizations->random()->id,
            ]);

        // Eventos de networking
        Event::factory()
            ->networking()
            ->public()
            ->published()
            ->upcoming()
            ->spanish()
            ->count(2)
            ->create([
                'organization_id' => $organizations->random()->id,
            ]);

        // Eventos de presentación
        Event::factory()
            ->presentation()
            ->public()
            ->published()
            ->upcoming()
            ->spanish()
            ->count(3)
            ->create([
                'organization_id' => $organizations->random()->id,
            ]);

        // Eventos de formación
        Event::factory()
            ->training()
            ->public()
            ->published()
            ->upcoming()
            ->spanish()
            ->count(4)
            ->create([
                'organization_id' => $organizations->random()->id,
            ]);

        // Eventos privados
        Event::factory()
            ->conference()
            ->private()
            ->published()
            ->upcoming()
            ->spanish()
            ->count(2)
            ->create([
                'organization_id' => $organizations->random()->id,
            ]);

        // Eventos en otros idiomas
        Event::factory()
            ->conference()
            ->public()
            ->published()
            ->upcoming()
            ->english()
            ->count(2)
            ->create([
                'organization_id' => $organizations->random()->id,
            ]);

        Event::factory()
            ->workshop()
            ->public()
            ->published()
            ->upcoming()
            ->catalan()
            ->count(1)
            ->create([
                'organization_id' => $organizations->random()->id,
            ]);

        // Eventos pasados
        Event::factory()
            ->conference()
            ->public()
            ->published()
            ->past()
            ->spanish()
            ->count(3)
            ->create([
                'organization_id' => $organizations->random()->id,
            ]);

        // Eventos de hoy
        Event::factory()
            ->workshop()
            ->public()
            ->published()
            ->today()
            ->spanish()
            ->count(1)
            ->create([
                'organization_id' => $organizations->random()->id,
            ]);

        // Eventos borradores
        Event::factory()
            ->conference()
            ->public()
            ->draft()
            ->upcoming()
            ->spanish()
            ->count(2)
            ->create([
                'organization_id' => $organizations->random()->id,
            ]);

        $this->command->info('✅ Eventos de ejemplo creados exitosamente');
    }
}
