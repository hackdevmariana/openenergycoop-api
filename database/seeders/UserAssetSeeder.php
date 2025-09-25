<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserAsset;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;

class UserAssetSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏠 Creando activos de usuarios...');

        $users = User::all();
        $products = Product::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles.');
            return;
        }

        if ($products->isEmpty()) {
            $this->command->error('❌ No hay productos disponibles. Ejecuta ShopSeeder primero.');
            return;
        }

        // Limpiar activos existentes
        UserAsset::query()->delete();

        $this->command->info("👥 Usuarios disponibles: {$users->count()}");
        $this->command->info("🛒 Productos disponibles: {$products->count()}");

        // Crear diferentes tipos de activos
        $this->createPhysicalAssets($users, $products);
        $this->createEnergyAssets($users, $products);
        $this->createProductionRights($users, $products);
        $this->createStorageCapacity($users, $products);
        $this->createMiningAssets($users, $products);
        $this->createEnergyBonds($users, $products);

        $this->command->info('✅ UserAssetSeeder completado. Se crearon ' . UserAsset::count() . ' activos de usuario.');
    }

    private function createPhysicalAssets($users, $products): void
    {
        $this->command->info('🏠 Creando activos físicos...');
        $physicalProducts = $products->where('type', 'physical');

        if ($physicalProducts->isEmpty()) {
            $this->command->warn('   ⚠️ No hay productos físicos disponibles. Saltando...');
            return;
        }

        foreach ($users as $user) {
            for ($i = 0; $i < rand(1, 3); $i++) {
                $product = $physicalProducts->random();
                $this->createAsset($user, $product, 'purchase', [
                    'quantity' => rand(1, 5),
                    'purchase_price' => $product->base_purchase_price * rand(1, 3),
                    'current_value' => $product->base_sale_price * rand(1, 3),
                    'efficiency_rating' => fake()->randomFloat(2, 80, 98),
                    'maintenance_cost' => fake()->randomFloat(2, 50, 500),
                    'estimated_annual_return' => fake()->randomFloat(4, 0.05, 0.15),
                ]);
            }
        }
        $this->command->info("   ✅ Activos físicos creados");
    }

    private function createEnergyAssets($users, $products): void
    {
        $this->command->info('⚡ Creando activos de energía...');
        $energyProducts = $products->where('type', 'energy_kwh');

        foreach ($users as $user) {
            for ($i = 0; $i < rand(2, 5); $i++) {
                $product = $energyProducts->random();
                $this->createAsset($user, $product, 'purchase', [
                    'quantity' => fake()->randomFloat(4, 100, 5000),
                    'purchase_price' => $product->base_purchase_price * fake()->randomFloat(2, 0.8, 1.2),
                    'current_value' => $product->base_sale_price * fake()->randomFloat(2, 0.9, 1.1),
                    'daily_yield' => fake()->randomFloat(4, 0.1, 2.0),
                    'total_yield_generated' => fake()->randomFloat(4, 50, 1000),
                    'efficiency_rating' => fake()->randomFloat(2, 85, 99),
                    'estimated_annual_return' => fake()->randomFloat(4, 0.08, 0.20),
                ]);
            }
        }
        $this->command->info("   ✅ Activos de energía creados");
    }

    private function createProductionRights($users, $products): void
    {
        $this->command->info('🏭 Creando derechos de producción...');
        $productionProducts = $products->where('type', 'production_right');

        if ($productionProducts->isEmpty()) {
            $this->command->warn('   ⚠️ No hay productos de derechos de producción disponibles. Saltando...');
            return;
        }

        foreach ($users as $user) {
            for ($i = 0; $i < rand(1, 4); $i++) {
                $product = $productionProducts->random();
                $this->createAsset($user, $product, 'purchase', [
                    'quantity' => fake()->randomFloat(4, 0.1, 10.0),
                    'purchase_price' => $product->base_purchase_price * fake()->randomFloat(2, 0.5, 2.0),
                    'current_value' => $product->base_sale_price * fake()->randomFloat(2, 0.8, 1.5),
                    'daily_yield' => fake()->randomFloat(4, 0.5, 5.0),
                    'total_yield_generated' => fake()->randomFloat(4, 100, 2000),
                    'efficiency_rating' => fake()->randomFloat(2, 75, 95),
                    'maintenance_cost' => fake()->randomFloat(2, 100, 1000),
                    'estimated_annual_return' => fake()->randomFloat(4, 0.10, 0.25),
                    'auto_reinvest' => fake()->boolean(60),
                    'reinvest_threshold' => fake()->randomFloat(2, 100, 500),
                    'reinvest_percentage' => fake()->randomFloat(2, 20, 80),
                ]);
            }
        }
        $this->command->info("   ✅ Derechos de producción creados");
    }

    private function createStorageCapacity($users, $products): void
    {
        $this->command->info('🔋 Creando capacidad de almacenamiento...');
        $storageProducts = $products->where('type', 'storage_capacity');

        if ($storageProducts->isEmpty()) {
            $this->command->warn('   ⚠️ No hay productos de capacidad de almacenamiento disponibles. Saltando...');
            return;
        }

        foreach ($users as $user) {
            for ($i = 0; $i < rand(1, 3); $i++) {
                $product = $storageProducts->random();
                $this->createAsset($user, $product, 'purchase', [
                    'quantity' => fake()->randomFloat(4, 1, 100),
                    'purchase_price' => $product->base_purchase_price * fake()->randomFloat(2, 0.7, 1.3),
                    'current_value' => $product->base_sale_price * fake()->randomFloat(2, 0.8, 1.2),
                    'efficiency_rating' => fake()->randomFloat(2, 80, 98),
                    'maintenance_cost' => fake()->randomFloat(2, 200, 1500),
                    'estimated_annual_return' => fake()->randomFloat(4, 0.06, 0.18),
                    'is_transferable' => fake()->boolean(80),
                    'is_delegatable' => fake()->boolean(30),
                ]);
            }
        }
        $this->command->info("   ✅ Capacidad de almacenamiento creada");
    }

    private function createMiningAssets($users, $products): void
    {
        $this->command->info('⛏️ Creando activos de minería...');
        $miningProducts = $products->where('type', 'mining_ths');

        if ($miningProducts->isEmpty()) {
            $this->command->warn('   ⚠️ No hay productos de minería disponibles. Saltando...');
            return;
        }

        foreach ($users as $user) {
            for ($i = 0; $i < rand(1, 2); $i++) {
                $product = $miningProducts->random();
                $this->createAsset($user, $product, 'purchase', [
                    'quantity' => fake()->randomFloat(4, 0.1, 50.0),
                    'purchase_price' => $product->base_purchase_price * fake()->randomFloat(2, 0.5, 1.5),
                    'current_value' => $product->base_sale_price * fake()->randomFloat(2, 0.6, 1.4),
                    'daily_yield' => fake()->randomFloat(4, 0.01, 0.5),
                    'total_yield_generated' => fake()->randomFloat(4, 1, 100),
                    'efficiency_rating' => fake()->randomFloat(2, 70, 95),
                    'maintenance_cost' => fake()->randomFloat(2, 300, 2000),
                    'estimated_annual_return' => fake()->randomFloat(4, 0.05, 0.30),
                    'auto_reinvest' => fake()->boolean(70),
                    'reinvest_threshold' => fake()->randomFloat(2, 50, 200),
                    'reinvest_percentage' => fake()->randomFloat(2, 30, 90),
                ]);
            }
        }
        $this->command->info("   ✅ Activos de minería creados");
    }

    private function createEnergyBonds($users, $products): void
    {
        $this->command->info('💎 Creando bonos energéticos...');
        $bondProducts = $products->where('type', 'energy_bond');

        if ($bondProducts->isEmpty()) {
            $this->command->warn('   ⚠️ No hay productos de bonos energéticos disponibles. Saltando...');
            return;
        }

        foreach ($users as $user) {
            for ($i = 0; $i < rand(1, 3); $i++) {
                $product = $bondProducts->random();
                $this->createAsset($user, $product, 'purchase', [
                    'quantity' => fake()->randomFloat(4, 1, 20),
                    'purchase_price' => $product->base_purchase_price * fake()->randomFloat(2, 0.8, 1.2),
                    'current_value' => $product->base_sale_price * fake()->randomFloat(2, 0.9, 1.1),
                    'daily_yield' => fake()->randomFloat(4, 0.05, 0.3),
                    'total_yield_generated' => fake()->randomFloat(4, 10, 500),
                    'efficiency_rating' => fake()->randomFloat(2, 85, 99),
                    'estimated_annual_return' => fake()->randomFloat(4, 0.04, 0.12),
                    'is_transferable' => fake()->boolean(90),
                    'is_delegatable' => fake()->boolean(50),
                ]);
            }
        }
        $this->command->info("   ✅ Bonos energéticos creados");
    }

    private function createAsset($user, $product, $sourceType, $additionalData = []): void
    {
        $startDate = Carbon::now()->subDays(rand(1, 365));
        $endDate = fake()->optional(0.7)->dateTimeBetween($startDate, '+2 years')?->format('Y-m-d');
        
        $quantity = $additionalData['quantity'] ?? fake()->randomFloat(4, 1, 100);
        $purchasePrice = $additionalData['purchase_price'] ?? $product->base_purchase_price * $quantity;
        $currentValue = $additionalData['current_value'] ?? $product->base_sale_price * $quantity;

        $asset = UserAsset::create(array_merge([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate,
            'source_type' => $sourceType,
            'status' => fake()->randomElement(['active', 'active', 'active', 'expired', 'pending']),
            'current_value' => $currentValue,
            'purchase_price' => $purchasePrice,
            'daily_yield' => $additionalData['daily_yield'] ?? fake()->optional(0.6)->randomFloat(4, 0.01, 2.0),
            'total_yield_generated' => $additionalData['total_yield_generated'] ?? fake()->randomFloat(4, 0, 1000),
            'efficiency_rating' => $additionalData['efficiency_rating'] ?? fake()->optional(0.8)->randomFloat(2, 70, 99),
            'maintenance_cost' => $additionalData['maintenance_cost'] ?? fake()->optional(0.5)->randomFloat(2, 50, 2000),
            'last_maintenance_date' => fake()->optional(0.7)->dateTimeBetween('-6 months', '-1 week')?->format('Y-m-d'),
            'next_maintenance_date' => fake()->optional(0.6)->dateTimeBetween('+1 month', '+12 months')?->format('Y-m-d'),
            'auto_reinvest' => $additionalData['auto_reinvest'] ?? fake()->boolean(40),
            'reinvest_threshold' => $additionalData['reinvest_threshold'] ?? fake()->optional(0.3)->randomFloat(2, 50, 1000),
            'reinvest_percentage' => $additionalData['reinvest_percentage'] ?? fake()->optional(0.3)->randomFloat(2, 20, 90),
            'is_transferable' => $additionalData['is_transferable'] ?? fake()->boolean(80),
            'is_delegatable' => $additionalData['is_delegatable'] ?? fake()->boolean(20),
            'delegated_to_user_id' => fake()->optional(0.1)->randomElement(User::pluck('id')->toArray()),
            'metadata' => json_encode([
                'purchase_channel' => fake()->randomElement(['web', 'mobile', 'api', 'admin']),
                'payment_method' => fake()->randomElement(['credit_card', 'bank_transfer', 'crypto', 'wallet']),
                'warranty_period' => fake()->randomElement(['1_year', '2_years', '5_years', '10_years']),
                'installation_date' => fake()->optional(0.8)->dateTimeBetween($startDate, 'now')?->format('Y-m-d'),
                'certification' => fake()->optional(0.6)->randomElement(['ISO9001', 'CE', 'UL', 'IEC']),
                'performance_guarantee' => fake()->optional(0.7)->randomFloat(2, 80, 100),
            ]),
            'estimated_annual_return' => $additionalData['estimated_annual_return'] ?? fake()->optional(0.8)->randomFloat(4, 0.03, 0.25),
            'actual_annual_return' => fake()->optional(0.6)->randomFloat(4, 0.02, 0.30),
            'performance_history' => json_encode([
                'monthly_returns' => array_map(fn() => fake()->randomFloat(4, -0.05, 0.15), range(1, 12)),
                'volatility' => fake()->randomFloat(4, 0.05, 0.30),
                'sharpe_ratio' => fake()->randomFloat(4, 0.5, 2.5),
                'max_drawdown' => fake()->randomFloat(4, 0.02, 0.20),
            ]),
            'notifications_enabled' => fake()->boolean(85),
            'alert_preferences' => json_encode([
                'low_performance' => fake()->boolean(70),
                'maintenance_due' => fake()->boolean(90),
                'price_alerts' => fake()->boolean(60),
                'yield_updates' => fake()->boolean(80),
                'market_news' => fake()->boolean(40),
            ]),
            'created_at' => $startDate,
            'updated_at' => Carbon::now()->subDays(rand(0, 30)),
        ], $additionalData));

        $status = $asset->status === 'active' ? 'activo' : $asset->status;
        $value = number_format($asset->current_value, 2);
        $this->command->info("   ✅ Creado activo: {$product->name} - {$status} - €{$value}");
    }
}
