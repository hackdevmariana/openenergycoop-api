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
            PlantSeeder::class, // Agregar el seeder de plantas
            PlantGroupSeeder::class, // Agregar el seeder de grupos de plantas
            UserAchievementSeeder::class, // Agregar el seeder de logros de usuarios
            UserChallengeProgressSeeder::class, // Agregar el seeder de progresos de desafíos
            EnergyCooperativeSeeder::class, // Agregar el seeder de cooperativas energéticas
            CooperativePlantConfigSeeder::class, // Agregar el seeder de configuraciones de plantas
            TeamSeeder::class, // Agregar el seeder de equipos
            ChallengeSeeder::class, // Agregar el seeder de desafíos
            TeamChallengeProgressSeeder::class, // Agregar el seeder de progresos de equipos en desafíos
            AffiliateSeeder::class, // Agregar el seeder de afiliados
            DiscountCodeSeeder::class, // Agregar el seeder de códigos de descuento
            SaleOrderSeeder::class, // Agregar el seeder de órdenes de venta
            PreSaleOfferSeeder::class, // Agregar el seeder de ofertas de preventa
            AutomationRuleSeeder::class, // Agregar el seeder de reglas de automatización
            OrganizationFeatureSeeder::class, // Agregar el seeder de características de organizaciones
            MaintenanceScheduleSeeder::class, // Agregar el seeder de horarios de mantenimiento
            OrganizationRoleSeeder::class, // Agregar el seeder de roles de organización
            UserOrganizationRoleSeeder::class, // Agregar el seeder de asignaciones de roles de usuario
            ApiClientSeeder::class, // Agregar el seeder de clientes API
            CollaboratorSeeder::class, // Agregar el seeder de colaboradores
            AuditLogSeeder::class, // Agregar el seeder de logs de auditoría
            FormSubmissionSeeder::class, // Agregar el seeder de envíos de formularios
            MessageSeeder::class, // Agregar el seeder de mensajes
            ContactSeeder::class, // Agregar el seeder de contactos
        ]);
    }
}
