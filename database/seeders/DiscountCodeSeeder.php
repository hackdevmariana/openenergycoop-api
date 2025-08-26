<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DiscountCode;
use App\Models\User;
use Carbon\Carbon;

class DiscountCodeSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🎫 Creando códigos de descuento españoles para la cooperativa energética...');
        
        // Limpiar códigos existentes
        DiscountCode::query()->delete();
        
        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. Creando códigos sin usuario creador.');
            $users = collect([null]);
        }
        
        $this->createWelcomeDiscounts($users);
        $this->createSeasonalDiscounts($users);
        $this->createEnergySpecificDiscounts($users);
        $this->createLoyaltyDiscounts($users);
        $this->createFlashSaleDiscounts($users);
        $this->createCorporateDiscounts($users);
        $this->createReferralDiscounts($users);
        $this->createHolidayDiscounts($users);
        
        $this->command->info('✅ DiscountCodeSeeder completado. Se crearon ' . DiscountCode::count() . ' códigos de descuento españoles.');
    }
    
    private function createWelcomeDiscounts($users): void
    {
        $this->command->info('🎁 Creando códigos de bienvenida...');
        
        $welcomeDiscounts = [
            [
                'code' => 'BIENVENIDA20',
                'name' => 'Bienvenida 20%',
                'description' => 'Descuento especial de bienvenida para nuevos clientes de la cooperativa energética',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'minimum_purchase_amount' => 50.00,
                'maximum_discount_amount' => 100.00,
                'status' => 'active',
                'start_date' => now()->subDays(30),
                'end_date' => now()->addMonths(6),
                'usage_limit' => 500,
                'usage_count' => 127,
                'per_user_limit' => 1,
                'is_first_time_only' => true,
                'is_new_customer_only' => true,
                'can_be_combined' => false,
                'tags' => ['bienvenida', 'nuevo-cliente', 'energia-verde'],
                'terms_conditions' => 'Válido solo para la primera compra. No combinable con otras ofertas.',
                'usage_instructions' => 'Introduce el código al finalizar tu compra. Descuento aplicado automáticamente.',
            ],
            [
                'code' => 'PRIMERA15',
                'name' => 'Primera Compra 15%',
                'description' => 'Descuento para tu primera compra en productos energéticos sostenibles',
                'discount_type' => 'percentage',
                'discount_value' => 15.00,
                'minimum_purchase_amount' => 30.00,
                'maximum_discount_amount' => 75.00,
                'status' => 'active',
                'start_date' => now()->subDays(45),
                'end_date' => now()->addMonths(4),
                'usage_limit' => 300,
                'usage_count' => 89,
                'per_user_limit' => 1,
                'is_first_time_only' => true,
                'is_new_customer_only' => true,
                'can_be_combined' => false,
                'tags' => ['primera-compra', 'nuevo-cliente', 'sostenible'],
                'terms_conditions' => 'Exclusivo para nuevos clientes. Mínimo de compra €30.',
                'usage_instructions' => 'Aplica el código en el carrito de compras antes de pagar.',
            ],
        ];
        
        foreach ($welcomeDiscounts as $discountData) {
            $user = $users->random();
            DiscountCode::create(array_merge($discountData, [
                'created_by' => $user ? $user->id : 1,
                'approved_by' => $user ? $user->id : 1,
                'approved_at' => now()->subDays(rand(1, 30)),
            ]));
        }
    }
    
    private function createSeasonalDiscounts($users): void
    {
        $this->command->info('🌞 Creando descuentos estacionales...');
        
        $seasonalDiscounts = [
            [
                'code' => 'VERANO25',
                'name' => 'Descuento de Verano 25%',
                'description' => 'Aprovecha el sol del verano con descuentos en energía solar',
                'discount_type' => 'percentage',
                'discount_value' => 25.00,
                'minimum_purchase_amount' => 100.00,
                'maximum_discount_amount' => 200.00,
                'status' => 'active',
                'start_date' => now()->subDays(15),
                'end_date' => now()->addMonths(2),
                'usage_limit' => 200,
                'usage_count' => 67,
                'per_user_limit' => 2,
                'is_first_time_only' => false,
                'is_new_customer_only' => false,
                'can_be_combined' => true,
                'tags' => ['verano', 'energia-solar', 'estacional'],
                'terms_conditions' => 'Válido hasta el 31 de agosto. Combinable con otros descuentos.',
                'usage_instructions' => 'Aplica el código en productos de energía solar y renovable.',
            ],
            [
                'code' => 'OTOÑO20',
                'name' => 'Oferta de Otoño 20%',
                'description' => 'Prepara tu hogar para el otoño con descuentos en eficiencia energética',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'minimum_purchase_amount' => 75.00,
                'maximum_discount_amount' => 150.00,
                'status' => 'active',
                'start_date' => now()->subDays(5),
                'end_date' => now()->addMonths(3),
                'usage_limit' => 150,
                'usage_count' => 23,
                'per_user_limit' => 1,
                'is_first_time_only' => false,
                'is_new_customer_only' => false,
                'can_be_combined' => false,
                'tags' => ['otoño', 'eficiencia-energetica', 'hogar'],
                'terms_conditions' => 'Válido para productos de eficiencia energética. No combinable.',
                'usage_instructions' => 'Usa el código en productos de aislamiento y eficiencia.',
            ],
        ];
        
        foreach ($seasonalDiscounts as $discountData) {
            $user = $users->random();
            DiscountCode::create(array_merge($discountData, [
                'created_by' => $user ? $user->id : 1,
                'approved_by' => $user ? $user->id : 1,
                'approved_at' => now()->subDays(rand(1, 20)),
            ]));
        }
    }
    
    private function createEnergySpecificDiscounts($users): void
    {
        $this->command->info('⚡ Creando descuentos específicos de energía...');
        
        $energyDiscounts = [
            [
                'code' => 'SOLAR30',
                'name' => 'Energía Solar 30%',
                'description' => 'Descuento especial en todos los productos de energía solar',
                'discount_type' => 'percentage',
                'discount_value' => 30.00,
                'minimum_purchase_amount' => 200.00,
                'maximum_discount_amount' => 500.00,
                'status' => 'active',
                'start_date' => now()->subDays(60),
                'end_date' => now()->addMonths(8),
                'usage_limit' => 100,
                'usage_count' => 45,
                'per_user_limit' => 1,
                'is_first_time_only' => false,
                'is_new_customer_only' => false,
                'can_be_combined' => false,
                'applicable_categories' => ['energia-solar', 'paneles-solares', 'baterias-solares'],
                'tags' => ['solar', 'renovable', 'energia-verde'],
                'terms_conditions' => 'Válido solo para productos de energía solar. Descuento máximo €500.',
                'usage_instructions' => 'Aplica en productos de la categoría energía solar.',
            ],
            [
                'code' => 'EOLICA25',
                'name' => 'Energía Eólica 25%',
                'description' => 'Descuento en productos de energía eólica y aerogeneradores',
                'discount_type' => 'percentage',
                'discount_value' => 25.00,
                'minimum_purchase_amount' => 150.00,
                'maximum_discount_amount' => 300.00,
                'status' => 'active',
                'start_date' => now()->subDays(40),
                'end_date' => now()->addMonths(6),
                'usage_limit' => 80,
                'usage_count' => 32,
                'per_user_limit' => 1,
                'is_first_time_only' => false,
                'is_new_customer_only' => false,
                'can_be_combined' => false,
                'applicable_categories' => ['energia-eolica', 'aerogeneradores', 'turbinas'],
                'tags' => ['eolica', 'renovable', 'viento'],
                'terms_conditions' => 'Exclusivo para productos de energía eólica. Mínimo €150.',
                'usage_instructions' => 'Usa en productos de la categoría energía eólica.',
            ],
            [
                'code' => 'EFICIENCIA20',
                'name' => 'Eficiencia Energética 20%',
                'description' => 'Descuento en productos de eficiencia y ahorro energético',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'minimum_purchase_amount' => 50.00,
                'maximum_discount_amount' => 100.00,
                'status' => 'active',
                'start_date' => now()->subDays(25),
                'end_date' => now()->addMonths(5),
                'usage_limit' => 250,
                'usage_count' => 156,
                'per_user_limit' => 2,
                'is_first_time_only' => false,
                'is_new_customer_only' => false,
                'can_be_combined' => true,
                'applicable_categories' => ['eficiencia-energetica', 'aislamiento', 'termostatos'],
                'tags' => ['eficiencia', 'ahorro', 'hogar'],
                'terms_conditions' => 'Válido para productos de eficiencia energética. Combinable.',
                'usage_instructions' => 'Aplica en productos de eficiencia y ahorro energético.',
            ],
        ];
        
        foreach ($energyDiscounts as $discountData) {
            $user = $users->random();
            DiscountCode::create(array_merge($discountData, [
                'created_by' => $user ? $user->id : 1,
                'approved_by' => $user ? $user->id : 1,
                'approved_at' => now()->subDays(rand(1, 30)),
            ]));
        }
    }
    
    private function createLoyaltyDiscounts($users): void
    {
        $this->command->info('👑 Creando descuentos de fidelidad...');
        
        $loyaltyDiscounts = [
            [
                'code' => 'FIDELIDAD15',
                'name' => 'Cliente Fiel 15%',
                'description' => 'Descuento especial para clientes que han realizado más de 3 compras',
                'discount_type' => 'percentage',
                'discount_value' => 15.00,
                'minimum_purchase_amount' => 100.00,
                'maximum_discount_amount' => 150.00,
                'status' => 'active',
                'start_date' => now()->subDays(90),
                'end_date' => now()->addMonths(12),
                'usage_limit' => 1000,
                'usage_count' => 234,
                'per_user_limit' => 3,
                'is_first_time_only' => false,
                'is_new_customer_only' => false,
                'can_be_combined' => true,
                'tags' => ['fidelidad', 'cliente-fiel', 'recompensa'],
                'terms_conditions' => 'Para clientes con más de 3 compras. Válido todo el año.',
                'usage_instructions' => 'Código automático para clientes fieles. Usa hasta 3 veces.',
            ],
            [
                'code' => 'VIP20',
                'name' => 'Cliente VIP 20%',
                'description' => 'Descuento exclusivo para clientes VIP de la cooperativa',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'minimum_purchase_amount' => 200.00,
                'maximum_discount_amount' => 400.00,
                'status' => 'active',
                'start_date' => now()->subDays(120),
                'end_date' => now()->addMonths(18),
                'usage_limit' => 500,
                'usage_count' => 89,
                'per_user_limit' => 5,
                'is_first_time_only' => false,
                'is_new_customer_only' => false,
                'can_be_combined' => true,
                'tags' => ['vip', 'exclusivo', 'premium'],
                'terms_conditions' => 'Exclusivo para clientes VIP. Descuento máximo €400.',
                'usage_instructions' => 'Código VIP personal. Usa hasta 5 veces al año.',
            ],
        ];
        
        foreach ($loyaltyDiscounts as $discountData) {
            $user = $users->random();
            DiscountCode::create(array_merge($discountData, [
                'created_by' => $user ? $user->id : 1,
                'approved_by' => $user ? $user->id : 1,
                'approved_at' => now()->subDays(rand(1, 60)),
            ]));
        }
    }
    
    private function createFlashSaleDiscounts($users): void
    {
        $this->command->info('⚡ Creando ofertas flash...');
        
        $flashDiscounts = [
            [
                'code' => 'FLASH50',
                'name' => 'Flash Sale 50%',
                'description' => '¡Oferta flash! 50% de descuento en productos seleccionados por tiempo limitado',
                'discount_type' => 'percentage',
                'discount_value' => 50.00,
                'minimum_purchase_amount' => 25.00,
                'maximum_discount_amount' => 200.00,
                'status' => 'active',
                'start_date' => now()->subDays(2),
                'end_date' => now()->addDays(3),
                'usage_limit' => 100,
                'usage_count' => 78,
                'per_user_limit' => 1,
                'is_first_time_only' => false,
                'is_new_customer_only' => false,
                'can_be_combined' => false,
                'tags' => ['flash-sale', 'tiempo-limitado', 'oferta-especial'],
                'terms_conditions' => 'Oferta flash válida solo 5 días. ¡No te la pierdas!',
                'usage_instructions' => 'Código válido solo hasta el final de la semana.',
            ],
            [
                'code' => '24H30',
                'name' => '24 Horas 30%',
                'description' => 'Descuento del 30% válido solo por 24 horas',
                'discount_type' => 'percentage',
                'discount_value' => 30.00,
                'minimum_purchase_amount' => 50.00,
                'maximum_discount_amount' => 100.00,
                'status' => 'active',
                'start_date' => now()->subHours(12),
                'end_date' => now()->addHours(12),
                'usage_limit' => 50,
                'usage_count' => 34,
                'per_user_limit' => 1,
                'is_first_time_only' => false,
                'is_new_customer_only' => false,
                'can_be_combined' => false,
                'tags' => ['24h', 'tiempo-limitado', 'oferta-rapida'],
                'terms_conditions' => 'Válido solo por 24 horas. ¡Actúa rápido!',
                'usage_instructions' => 'Código válido solo por 24 horas desde su activación.',
            ],
        ];
        
        foreach ($flashDiscounts as $discountData) {
            $user = $users->random();
            DiscountCode::create(array_merge($discountData, [
                'created_by' => $user ? $user->id : 1,
                'approved_by' => $user ? $user->id : 1,
                'approved_at' => now()->subDays(rand(1, 5)),
            ]));
        }
    }
    
    private function createCorporateDiscounts($users): void
    {
        $this->command->info('🏢 Creando descuentos corporativos...');
        
        $corporateDiscounts = [
            [
                'code' => 'EMPRESA25',
                'name' => 'Descuento Empresa 25%',
                'description' => 'Descuento especial para empresas y organizaciones',
                'discount_type' => 'percentage',
                'discount_value' => 25.00,
                'minimum_purchase_amount' => 500.00,
                'maximum_discount_amount' => 1000.00,
                'status' => 'active',
                'start_date' => now()->subDays(180),
                'end_date' => now()->addMonths(24),
                'usage_limit' => 200,
                'usage_count' => 67,
                'per_user_limit' => 10,
                'is_first_time_only' => false,
                'is_new_customer_only' => false,
                'can_be_combined' => true,
                'applicable_user_groups' => ['empresas', 'organizaciones', 'instituciones'],
                'tags' => ['empresa', 'corporativo', 'b2b'],
                'terms_conditions' => 'Exclusivo para empresas. Mínimo de compra €500. Descuento máximo €1000.',
                'usage_instructions' => 'Código corporativo. Usa hasta 10 veces por empresa.',
            ],
            [
                'code' => 'BULK20',
                'name' => 'Compra al Por Mayor 20%',
                'description' => 'Descuento para compras al por mayor y grandes volúmenes',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'minimum_purchase_amount' => 1000.00,
                'maximum_discount_amount' => 2000.00,
                'status' => 'active',
                'start_date' => now()->subDays(150),
                'end_date' => now()->addMonths(18),
                'usage_limit' => 100,
                'usage_count' => 23,
                'per_user_limit' => 5,
                'is_first_time_only' => false,
                'is_new_customer_only' => false,
                'can_be_combined' => false,
                'tags' => ['por-mayor', 'volumen', 'empresa'],
                'terms_conditions' => 'Para compras de €1000 o más. Descuento máximo €2000.',
                'usage_instructions' => 'Aplica en compras de gran volumen. No combinable.',
            ],
        ];
        
        foreach ($corporateDiscounts as $discountData) {
            $user = $users->random();
            DiscountCode::create(array_merge($discountData, [
                'created_by' => $user ? $user->id : 1,
                'approved_by' => $user ? $user->id : 1,
                'approved_at' => now()->subDays(rand(1, 90)),
            ]));
        }
    }
    
    private function createReferralDiscounts($users): void
    {
        $this->command->info('🤝 Creando descuentos de referidos...');
        
        $referralDiscounts = [
            [
                'code' => 'REFERIDO10',
                'name' => 'Referido 10%',
                'description' => 'Descuento por referir a un amigo a la cooperativa energética',
                'discount_type' => 'percentage',
                'discount_value' => 10.00,
                'minimum_purchase_amount' => 25.00,
                'maximum_discount_amount' => 50.00,
                'status' => 'active',
                'start_date' => now()->subDays(365),
                'end_date' => now()->addMonths(60),
                'usage_limit' => 2000,
                'usage_count' => 456,
                'per_user_limit' => 5,
                'is_first_time_only' => false,
                'is_new_customer_only' => false,
                'can_be_combined' => true,
                'tags' => ['referido', 'amigo', 'recompensa'],
                'terms_conditions' => 'Válido por referir amigos. Usa hasta 5 veces por usuario.',
                'usage_instructions' => 'Código de referido personal. Compártelo con amigos.',
            ],
            [
                'code' => 'FAMILIA15',
                'name' => 'Familia 15%',
                'description' => 'Descuento especial para familias que se unen a la cooperativa',
                'discount_type' => 'percentage',
                'discount_value' => 15.00,
                'minimum_purchase_amount' => 100.00,
                'maximum_discount_amount' => 150.00,
                'status' => 'active',
                'start_date' => now()->subDays(200),
                'end_date' => now()->addMonths(36),
                'usage_limit' => 500,
                'usage_count' => 123,
                'per_user_limit' => 3,
                'is_first_time_only' => false,
                'is_new_customer_only' => false,
                'can_be_combined' => false,
                'tags' => ['familia', 'hogar', 'cooperativa'],
                'terms_conditions' => 'Para familias de 3 o más miembros. No combinable.',
                'usage_instructions' => 'Código familiar. Usa hasta 3 veces por familia.',
            ],
        ];
        
        foreach ($referralDiscounts as $discountData) {
            $user = $users->random();
            DiscountCode::create(array_merge($discountData, [
                'created_by' => $user ? $user->id : 1,
                'approved_by' => $user ? $user->id : 1,
                'approved_at' => now()->subDays(rand(1, 120)),
            ]));
        }
    }
    
    private function createHolidayDiscounts($users): void
    {
        $this->command->info('🎄 Creando descuentos de festividades...');
        
        $holidayDiscounts = [
            [
                'code' => 'NAVIDAD25',
                'name' => 'Navidad Verde 25%',
                'description' => 'Celebra la Navidad con descuentos en energía sostenible',
                'discount_type' => 'percentage',
                'discount_value' => 25.00,
                'minimum_purchase_amount' => 75.00,
                'maximum_discount_amount' => 200.00,
                'status' => 'active',
                'start_date' => now()->subDays(30),
                'end_date' => now()->addDays(15),
                'usage_limit' => 300,
                'usage_count' => 234,
                'per_user_limit' => 2,
                'is_first_time_only' => false,
                'is_new_customer_only' => false,
                'can_be_combined' => true,
                'tags' => ['navidad', 'festividad', 'energia-verde'],
                'terms_conditions' => 'Válido hasta el 6 de enero. Combinable con otros descuentos.',
                'usage_instructions' => 'Código navideño. Usa hasta 2 veces durante las fiestas.',
            ],
            [
                'code' => 'REYES20',
                'name' => 'Reyes Magos 20%',
                'description' => 'Descuento especial para el Día de Reyes',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'minimum_purchase_amount' => 50.00,
                'maximum_discount_amount' => 100.00,
                'status' => 'active',
                'start_date' => now()->subDays(20),
                'end_date' => now()->addDays(8),
                'usage_limit' => 200,
                'usage_count' => 89,
                'per_user_limit' => 1,
                'is_first_time_only' => false,
                'is_new_customer_only' => false,
                'can_be_combined' => false,
                'tags' => ['reyes', 'festividad', 'energia'],
                'terms_conditions' => 'Válido solo el 6 de enero. No combinable.',
                'usage_instructions' => 'Código especial para el Día de Reyes. ¡Úsalo hoy!',
            ],
        ];
        
        foreach ($holidayDiscounts as $discountData) {
            $user = $users->random();
            DiscountCode::create(array_merge($discountData, [
                'created_by' => $user ? $user->id : 1,
                'approved_by' => $user ? $user->id : 1,
                'approved_at' => now()->subDays(rand(1, 30)),
            ]));
        }
    }
}
