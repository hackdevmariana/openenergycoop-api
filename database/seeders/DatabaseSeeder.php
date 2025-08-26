<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'david@aragon.es'],
            [
                'name' => 'David Fernández Moreno',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'remember_token' => \Illuminate\Support\Str::random(10),
            ]
        );

        $this->call([
            RolesAndPermissionsSeeder::class, // Agregar este seeder primero
            RolesAndAdminSeeder::class,
            AppSettingSeeder::class,
            ShopSeeder::class,
            NotificationSettingsSeeder::class,
            EventSeeder::class,
            SurveySeeder::class,
            CustomerProfileSeeder::class, // Agregar el seeder de perfiles de cliente
            AchievementSeeder::class, // Agregar el seeder de achievements
            EnergyChallengeSeeder::class, // Agregar el seeder de desafíos energéticos
        ]);
    }
}
