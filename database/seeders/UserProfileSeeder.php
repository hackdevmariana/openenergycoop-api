<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserProfile;
use App\Models\User;
use App\Models\Organization;
use App\Models\Municipality;
use Carbon\Carbon;

class UserProfileSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::take(10)->get();
        $organizations = Organization::take(5)->get();
        $municipalities = Municipality::take(10)->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. Saltando UserProfileSeeder.');
            return;
        }

        if ($organizations->isEmpty()) {
            $this->command->warn('⚠️ No hay organizaciones disponibles. Saltando UserProfileSeeder.');
            return;
        }

        $profiles = [];

        foreach ($users as $index => $user) {
            $organization = $organizations->random();
            $municipality = $municipalities->random();
            
            $profiles[] = [
                'user_id' => $user->id,
                'avatar' => null,
                'bio' => fake()->optional(0.7)->sentence(15),
                'municipality_id' => $municipality->id,
                'join_date' => Carbon::now()->subDays(rand(30, 365)),
                'role_in_cooperative' => fake()->randomElement(['Miembro', 'Colaborador', 'Voluntario', 'Líder de Proyecto', 'Coordinador']),
                'profile_completed' => fake()->boolean(80),
                'newsletter_opt_in' => fake()->boolean(60),
                'show_in_rankings' => fake()->boolean(70),
                'co2_avoided_total' => fake()->randomFloat(2, 100, 5000),
                'kwh_produced_total' => fake()->randomFloat(2, 500, 25000),
                'points_total' => fake()->numberBetween(100, 5000),
                'badges_earned' => json_encode(fake()->randomElements([
                    'Primer Paso', 'Energía Verde', 'Ahorrador', 'Colaborador', 'Líder',
                    'Innovador', 'Sostenible', 'Comunidad', 'Eficiencia', 'Renovable'
                ], rand(1, 5))),
                'birth_date' => fake()->optional(0.8)->dateTimeBetween('-70 years', '-18 years'),
                'organization_id' => $organization->id,
                'team_id' => null,
            ];
        }

        foreach ($profiles as $profile) {
            UserProfile::create($profile);
        }

        $this->command->info('✅ UserProfileSeeder ejecutado correctamente');
        $this->command->info('📊 Perfiles de usuario creados: ' . count($profiles));
        $this->command->info('👥 Usuarios con perfiles: ' . $users->count());
        $this->command->info('🏢 Organizaciones utilizadas: ' . $organizations->count());
        $this->command->info('🏘️ Municipios utilizados: ' . $municipalities->count());
        $this->command->info('📈 Perfiles completados: ' . collect($profiles)->where('profile_completed', true)->count());
        $this->command->info('📧 Suscripciones newsletter: ' . collect($profiles)->where('newsletter_opt_in', true)->count());
        $this->command->info('🏆 Puntos totales: ' . collect($profiles)->sum('points_total'));
        $this->command->info('🌱 CO₂ evitado total: ' . number_format(collect($profiles)->sum('co2_avoided_total'), 2) . ' kg');
        $this->command->info('⚡ kWh producidos total: ' . number_format(collect($profiles)->sum('kwh_produced_total'), 2) . ' kWh');
    }
}