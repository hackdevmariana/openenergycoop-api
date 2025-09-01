<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionRequest;
use App\Models\User;
use App\Models\Organization;
use Carbon\Carbon;

class SubscriptionRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📋 Creando solicitudes de suscripción para la cooperativa energética...');

        // Obtener usuarios y organizaciones disponibles
        $users = User::all();
        $organizations = Organization::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles. Ejecuta RolesAndAdminSeeder primero.');
            return;
        }

        if ($organizations->isEmpty()) {
            $this->command->error('❌ No hay organizaciones disponibles. Ejecuta otros seeders primero.');
            return;
        }

        // 1. Solicitudes Pendientes
        $this->createPendingRequests($users, $organizations);

        // 2. Solicitudes Aprobadas
        $this->createApprovedRequests($users, $organizations);

        // 3. Solicitudes Rechazadas
        $this->createRejectedRequests($users, $organizations);

        // 4. Solicitudes en Revisión
        $this->createInReviewRequests($users, $organizations);

        // 5. Solicitudes de Nueva Suscripción
        $this->createNewSubscriptionRequests($users, $organizations);

        // 6. Solicitudes de Cambio de Titularidad
        $this->createOwnershipChangeRequests($users, $organizations);

        // 7. Solicitudes de Arrendatarios
        $this->createTenantRequests($users, $organizations);

        // 8. Solicitudes para Instalaciones Solares
        $this->createSolarInstallationRequests($users, $organizations);

        // 9. Solicitudes Comerciales
        $this->createCommercialRequests($users, $organizations);

        // 10. Solicitudes Residenciales
        $this->createResidentialRequests($users, $organizations);

        $this->command->info('✅ SubscriptionRequestSeeder completado. Se crearon ' . SubscriptionRequest::count() . ' solicitudes de suscripción.');
    }

    /**
     * Crear solicitudes pendientes
     */
    private function createPendingRequests($users, $organizations): void
    {
        $this->command->info('⏳ Creando solicitudes pendientes...');

        $pendingRequests = [
            [
                'user_id' => $users->random()->id,
                'cooperative_id' => $organizations->random()->id,
                'status' => SubscriptionRequest::STATUS_PENDING,
                'type' => SubscriptionRequest::TYPE_NEW_SUBSCRIPTION,
                'submitted_at' => Carbon::now()->subDays(rand(1, 7)),
                'processed_at' => null,
                'notes' => 'Solicitud de alta para nueva instalación solar en vivienda unifamiliar. Documentación completa adjunta.',
            ],
            [
                'user_id' => $users->random()->id,
                'cooperative_id' => $organizations->random()->id,
                'status' => SubscriptionRequest::STATUS_PENDING,
                'type' => SubscriptionRequest::TYPE_OWNERSHIP_CHANGE,
                'submitted_at' => Carbon::now()->subDays(rand(1, 5)),
                'processed_at' => null,
                'notes' => 'Cambio de titularidad por venta de vivienda. Pendiente de verificación de documentación.',
            ],
            [
                'user_id' => $users->random()->id,
                'cooperative_id' => $organizations->random()->id,
                'status' => SubscriptionRequest::STATUS_PENDING,
                'type' => SubscriptionRequest::TYPE_TENANT_REQUEST,
                'submitted_at' => Carbon::now()->subDays(rand(1, 3)),
                'processed_at' => null,
                'notes' => 'Solicitud de arrendatario para instalación existente. Pendiente autorización del propietario.',
            ],
        ];

        foreach ($pendingRequests as $request) {
            $this->createRequest($request);
        }

        // Crear solicitudes adicionales
        for ($i = 0; $i < 12; $i++) {
            $request = SubscriptionRequest::create([
                'user_id' => $users->random()->id,
                'cooperative_id' => $organizations->random()->id,
                'status' => SubscriptionRequest::STATUS_PENDING,
                'type' => fake()->randomElement([
                    SubscriptionRequest::TYPE_NEW_SUBSCRIPTION,
                    SubscriptionRequest::TYPE_OWNERSHIP_CHANGE,
                    SubscriptionRequest::TYPE_TENANT_REQUEST,
                ]),
                'submitted_at' => Carbon::now()->subDays(rand(1, 7)),
                'processed_at' => null,
                'notes' => fake()->optional(0.7)->sentence() . ' - Solicitud pendiente de revisión.',
            ]);
            $this->command->line("   ✅ Creada solicitud pendiente: {$request->type} - {$request->status}");
        }
    }

    /**
     * Crear solicitudes aprobadas
     */
    private function createApprovedRequests($users, $organizations): void
    {
        $this->command->info('✅ Creando solicitudes aprobadas...');

        $approvedRequests = [
            [
                'user_id' => $users->random()->id,
                'cooperative_id' => $organizations->random()->id,
                'status' => SubscriptionRequest::STATUS_APPROVED,
                'type' => SubscriptionRequest::TYPE_NEW_SUBSCRIPTION,
                'submitted_at' => Carbon::now()->subDays(rand(10, 20)),
                'processed_at' => Carbon::now()->subDays(rand(1, 5)),
                'notes' => 'Solicitud aprobada. Instalación solar de 5kW para vivienda residencial. Cliente satisfecho con el proceso.',
            ],
            [
                'user_id' => $users->random()->id,
                'cooperative_id' => $organizations->random()->id,
                'status' => SubscriptionRequest::STATUS_APPROVED,
                'type' => SubscriptionRequest::TYPE_OWNERSHIP_CHANGE,
                'submitted_at' => Carbon::now()->subDays(rand(8, 15)),
                'processed_at' => Carbon::now()->subDays(rand(2, 7)),
                'notes' => 'Cambio de titularidad aprobado. Documentación verificada y proceso completado exitosamente.',
            ],
            [
                'user_id' => $users->random()->id,
                'cooperative_id' => $organizations->random()->id,
                'status' => SubscriptionRequest::STATUS_APPROVED,
                'type' => SubscriptionRequest::TYPE_TENANT_REQUEST,
                'submitted_at' => Carbon::now()->subDays(rand(5, 12)),
                'processed_at' => Carbon::now()->subDays(rand(1, 4)),
                'notes' => 'Solicitud de arrendatario aprobada. Propietario autorizó la transferencia del contrato.',
            ],
        ];

        foreach ($approvedRequests as $request) {
            $this->createRequest($request);
        }

        // Crear solicitudes adicionales
        for ($i = 0; $i < 15; $i++) {
            $request = SubscriptionRequest::create([
                'user_id' => $users->random()->id,
                'cooperative_id' => $organizations->random()->id,
                'status' => SubscriptionRequest::STATUS_APPROVED,
                'type' => fake()->randomElement([
                    SubscriptionRequest::TYPE_NEW_SUBSCRIPTION,
                    SubscriptionRequest::TYPE_OWNERSHIP_CHANGE,
                    SubscriptionRequest::TYPE_TENANT_REQUEST,
                ]),
                'submitted_at' => Carbon::now()->subDays(rand(10, 20)),
                'processed_at' => Carbon::now()->subDays(rand(1, 5)),
                'notes' => fake()->optional(0.8)->sentence() . ' - Solicitud aprobada exitosamente.',
            ]);
            $this->command->line("   ✅ Creada solicitud aprobada: {$request->type} - {$request->status}");
        }
    }

    /**
     * Crear solicitudes rechazadas
     */
    private function createRejectedRequests($users, $organizations): void
    {
        $this->command->info('❌ Creando solicitudes rechazadas...');

        $rejectedRequests = [
            [
                'user_id' => $users->random()->id,
                'cooperative_id' => $organizations->random()->id,
                'status' => SubscriptionRequest::STATUS_REJECTED,
                'type' => SubscriptionRequest::TYPE_NEW_SUBSCRIPTION,
                'submitted_at' => Carbon::now()->subDays(rand(15, 25)),
                'processed_at' => Carbon::now()->subDays(rand(5, 10)),
                'notes' => 'Solicitud rechazada - Documentación incompleta. Falta certificado de eficiencia energética y plano de la instalación.',
            ],
            [
                'user_id' => $users->random()->id,
                'cooperative_id' => $organizations->random()->id,
                'status' => SubscriptionRequest::STATUS_REJECTED,
                'type' => SubscriptionRequest::TYPE_OWNERSHIP_CHANGE,
                'submitted_at' => Carbon::now()->subDays(rand(12, 20)),
                'processed_at' => Carbon::now()->subDays(rand(3, 8)),
                'notes' => 'Cambio de titularidad rechazado - Escritura de compraventa no válida. Se requiere escritura pública notarial.',
            ],
            [
                'user_id' => $users->random()->id,
                'cooperative_id' => $organizations->random()->id,
                'status' => SubscriptionRequest::STATUS_REJECTED,
                'type' => SubscriptionRequest::TYPE_TENANT_REQUEST,
                'submitted_at' => Carbon::now()->subDays(rand(10, 18)),
                'processed_at' => Carbon::now()->subDays(rand(2, 6)),
                'notes' => 'Solicitud de arrendatario rechazada - Propietario no autorizó la transferencia del contrato.',
            ],
        ];

        foreach ($rejectedRequests as $request) {
            $this->createRequest($request);
        }

        // Crear solicitudes adicionales
        for ($i = 0; $i < 8; $i++) {
            $request = SubscriptionRequest::create([
                'user_id' => $users->random()->id,
                'cooperative_id' => $organizations->random()->id,
                'status' => SubscriptionRequest::STATUS_REJECTED,
                'type' => fake()->randomElement([
                    SubscriptionRequest::TYPE_NEW_SUBSCRIPTION,
                    SubscriptionRequest::TYPE_OWNERSHIP_CHANGE,
                    SubscriptionRequest::TYPE_TENANT_REQUEST,
                ]),
                'submitted_at' => Carbon::now()->subDays(rand(15, 25)),
                'processed_at' => Carbon::now()->subDays(rand(5, 10)),
                'notes' => fake()->sentence() . ' - Solicitud rechazada por documentación incompleta.',
            ]);
            $this->command->line("   ✅ Creada solicitud rechazada: {$request->type} - {$request->status}");
        }
    }

    /**
     * Crear solicitudes en revisión
     */
    private function createInReviewRequests($users, $organizations): void
    {
        $this->command->info('🔍 Creando solicitudes en revisión...');

        $inReviewRequests = [
            [
                'user_id' => $users->random()->id,
                'cooperative_id' => $organizations->random()->id,
                'status' => SubscriptionRequest::STATUS_IN_REVIEW,
                'type' => SubscriptionRequest::TYPE_NEW_SUBSCRIPTION,
                'submitted_at' => Carbon::now()->subDays(rand(3, 8)),
                'processed_at' => null,
                'notes' => 'Solicitud en revisión técnica - Instalación compleja de 10kW con baterías. Requiere estudio técnico adicional.',
            ],
            [
                'user_id' => $users->random()->id,
                'cooperative_id' => $organizations->random()->id,
                'status' => SubscriptionRequest::STATUS_IN_REVIEW,
                'type' => SubscriptionRequest::TYPE_OWNERSHIP_CHANGE,
                'submitted_at' => Carbon::now()->subDays(rand(2, 6)),
                'processed_at' => null,
                'notes' => 'Cambio de titularidad en revisión - Caso especial de herencia. Pendiente documentación adicional.',
            ],
            [
                'user_id' => $users->random()->id,
                'cooperative_id' => $organizations->random()->id,
                'status' => SubscriptionRequest::STATUS_IN_REVIEW,
                'type' => SubscriptionRequest::TYPE_TENANT_REQUEST,
                'submitted_at' => Carbon::now()->subDays(rand(1, 4)),
                'processed_at' => null,
                'notes' => 'Solicitud de arrendatario en revisión - Pendiente confirmación del propietario y verificación de contrato.',
            ],
        ];

        foreach ($inReviewRequests as $request) {
            $this->createRequest($request);
        }

        // Crear solicitudes adicionales
        for ($i = 0; $i < 6; $i++) {
            $request = SubscriptionRequest::create([
                'user_id' => $users->random()->id,
                'cooperative_id' => $organizations->random()->id,
                'status' => SubscriptionRequest::STATUS_IN_REVIEW,
                'type' => fake()->randomElement([
                    SubscriptionRequest::TYPE_NEW_SUBSCRIPTION,
                    SubscriptionRequest::TYPE_OWNERSHIP_CHANGE,
                    SubscriptionRequest::TYPE_TENANT_REQUEST,
                ]),
                'submitted_at' => Carbon::now()->subDays(rand(3, 8)),
                'processed_at' => null,
                'notes' => fake()->sentence() . ' - Solicitud en revisión técnica.',
            ]);
            $this->command->line("   ✅ Creada solicitud en revisión: {$request->type} - {$request->status}");
        }
    }

    /**
     * Crear solicitudes de nueva suscripción
     */
    private function createNewSubscriptionRequests($users, $organizations): void
    {
        $this->command->info('🆕 Creando solicitudes de nueva suscripción...');

        // Crear solicitudes
        for ($i = 0; $i < 10; $i++) {
            $request = SubscriptionRequest::create([
                'user_id' => $users->random()->id,
                'cooperative_id' => $organizations->random()->id,
                'status' => fake()->randomElement([
                    SubscriptionRequest::STATUS_PENDING,
                    SubscriptionRequest::STATUS_APPROVED,
                    SubscriptionRequest::STATUS_REJECTED,
                    SubscriptionRequest::STATUS_IN_REVIEW,
                ]),
                'type' => SubscriptionRequest::TYPE_NEW_SUBSCRIPTION,
                'submitted_at' => Carbon::now()->subDays(rand(1, 30)),
                'processed_at' => fake()->optional(0.7)->dateTimeBetween('-30 days', 'now'),
                'notes' => fake()->optional(0.8)->sentence() . ' - Solicitud de alta para nueva instalación.',
            ]);
            $this->command->line("   ✅ Creada solicitud nueva suscripción: {$request->type}");
        }
    }

    /**
     * Crear solicitudes de cambio de titularidad
     */
    private function createOwnershipChangeRequests($users, $organizations): void
    {
        $this->command->info('🔄 Creando solicitudes de cambio de titularidad...');

        // Crear solicitudes
        for ($i = 0; $i < 8; $i++) {
            $request = SubscriptionRequest::create([
                'user_id' => $users->random()->id,
                'cooperative_id' => $organizations->random()->id,
                'status' => fake()->randomElement([
                    SubscriptionRequest::STATUS_PENDING,
                    SubscriptionRequest::STATUS_APPROVED,
                    SubscriptionRequest::STATUS_REJECTED,
                    SubscriptionRequest::STATUS_IN_REVIEW,
                ]),
                'type' => SubscriptionRequest::TYPE_OWNERSHIP_CHANGE,
                'submitted_at' => Carbon::now()->subDays(rand(1, 30)),
                'processed_at' => fake()->optional(0.7)->dateTimeBetween('-30 days', 'now'),
                'notes' => fake()->optional(0.8)->sentence() . ' - Cambio de titularidad de la instalación.',
            ]);
            $this->command->line("   ✅ Creada solicitud cambio titularidad: {$request->type}");
        }
    }

    /**
     * Crear solicitudes de arrendatarios
     */
    private function createTenantRequests($users, $organizations): void
    {
        $this->command->info('🏠 Creando solicitudes de arrendatarios...');

        // Crear solicitudes
        for ($i = 0; $i < 6; $i++) {
            $request = SubscriptionRequest::create([
                'user_id' => $users->random()->id,
                'cooperative_id' => $organizations->random()->id,
                'status' => fake()->randomElement([
                    SubscriptionRequest::STATUS_PENDING,
                    SubscriptionRequest::STATUS_APPROVED,
                    SubscriptionRequest::STATUS_REJECTED,
                    SubscriptionRequest::STATUS_IN_REVIEW,
                ]),
                'type' => SubscriptionRequest::TYPE_TENANT_REQUEST,
                'submitted_at' => Carbon::now()->subDays(rand(1, 30)),
                'processed_at' => fake()->optional(0.7)->dateTimeBetween('-30 days', 'now'),
                'notes' => fake()->optional(0.8)->sentence() . ' - Solicitud de arrendatario.',
            ]);
            $this->command->line("   ✅ Creada solicitud arrendatario: {$request->type}");
        }
    }

    /**
     * Crear solicitudes para instalaciones solares
     */
    private function createSolarInstallationRequests($users, $organizations): void
    {
        $this->command->info('☀️ Creando solicitudes para instalaciones solares...');

        // Crear solicitudes
        for ($i = 0; $i < 12; $i++) {
            $request = SubscriptionRequest::create([
                'user_id' => $users->random()->id,
                'cooperative_id' => $organizations->random()->id,
                'status' => fake()->randomElement([
                    SubscriptionRequest::STATUS_PENDING,
                    SubscriptionRequest::STATUS_APPROVED,
                    SubscriptionRequest::STATUS_REJECTED,
                    SubscriptionRequest::STATUS_IN_REVIEW,
                ]),
                'type' => fake()->randomElement([
                    SubscriptionRequest::TYPE_NEW_SUBSCRIPTION,
                    SubscriptionRequest::TYPE_OWNERSHIP_CHANGE,
                    SubscriptionRequest::TYPE_TENANT_REQUEST,
                ]),
                'submitted_at' => Carbon::now()->subDays(rand(1, 30)),
                'processed_at' => fake()->optional(0.7)->dateTimeBetween('-30 days', 'now'),
                'notes' => fake()->optional(0.8)->sentence() . ' - Instalación solar fotovoltaica.',
            ]);
            $this->command->line("   ✅ Creada solicitud instalación solar: {$request->type}");
        }
    }

    /**
     * Crear solicitudes comerciales
     */
    private function createCommercialRequests($users, $organizations): void
    {
        $this->command->info('🏢 Creando solicitudes comerciales...');

        // Crear solicitudes
        for ($i = 0; $i < 10; $i++) {
            $request = SubscriptionRequest::create([
                'user_id' => $users->random()->id,
                'cooperative_id' => $organizations->random()->id,
                'status' => fake()->randomElement([
                    SubscriptionRequest::STATUS_PENDING,
                    SubscriptionRequest::STATUS_APPROVED,
                    SubscriptionRequest::STATUS_REJECTED,
                    SubscriptionRequest::STATUS_IN_REVIEW,
                ]),
                'type' => fake()->randomElement([
                    SubscriptionRequest::TYPE_NEW_SUBSCRIPTION,
                    SubscriptionRequest::TYPE_OWNERSHIP_CHANGE,
                    SubscriptionRequest::TYPE_TENANT_REQUEST,
                ]),
                'submitted_at' => Carbon::now()->subDays(rand(1, 30)),
                'processed_at' => fake()->optional(0.7)->dateTimeBetween('-30 days', 'now'),
                'notes' => fake()->optional(0.8)->sentence() . ' - Uso comercial/empresarial.',
            ]);
            $this->command->line("   ✅ Creada solicitud comercial: {$request->type}");
        }
    }

    /**
     * Crear solicitudes residenciales
     */
    private function createResidentialRequests($users, $organizations): void
    {
        $this->command->info('🏡 Creando solicitudes residenciales...');

        // Crear solicitudes
        for ($i = 0; $i < 15; $i++) {
            $request = SubscriptionRequest::create([
                'user_id' => $users->random()->id,
                'cooperative_id' => $organizations->random()->id,
                'status' => fake()->randomElement([
                    SubscriptionRequest::STATUS_PENDING,
                    SubscriptionRequest::STATUS_APPROVED,
                    SubscriptionRequest::STATUS_REJECTED,
                    SubscriptionRequest::STATUS_IN_REVIEW,
                ]),
                'type' => fake()->randomElement([
                    SubscriptionRequest::TYPE_NEW_SUBSCRIPTION,
                    SubscriptionRequest::TYPE_OWNERSHIP_CHANGE,
                    SubscriptionRequest::TYPE_TENANT_REQUEST,
                ]),
                'submitted_at' => Carbon::now()->subDays(rand(1, 30)),
                'processed_at' => fake()->optional(0.7)->dateTimeBetween('-30 days', 'now'),
                'notes' => fake()->optional(0.8)->sentence() . ' - Uso residencial.',
            ]);
            $this->command->line("   ✅ Creada solicitud residencial: {$request->type}");
        }
    }

    /**
     * Crear una solicitud individual
     */
    private function createRequest(array $data): void
    {
        $request = SubscriptionRequest::create($data);
        $this->command->line("   ✅ Creada solicitud: {$request->type} - {$request->status} (Usuario: {$request->user_id})");
    }
}
