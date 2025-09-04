<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EnergyTradingOrder;
use App\Models\User;
use App\Models\EnergyPool;
use Carbon\Carbon;

class EnergyTradingOrderSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📈 Creando órdenes de trading energético...');

        $users = User::all();
        $energyPools = EnergyPool::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles.');
            return;
        }

        // Limpiar datos existentes
        EnergyTradingOrder::query()->delete();

        $this->command->info("👥 Usuarios disponibles: {$users->count()}");
        $this->command->info("🏊 Pools disponibles: {$energyPools->count()}");

        // Crear diferentes tipos de órdenes
        $this->createBuyOrders($users, $energyPools);
        $this->createSellOrders($users, $energyPools);
        $this->createMarketOrders($users, $energyPools);
        $this->createLimitOrders($users, $energyPools);
        $this->createStopOrders($users, $energyPools);
        $this->createBidAskOrders($users, $energyPools);

        $this->command->info('✅ EnergyTradingOrderSeeder completado. Se crearon ' . EnergyTradingOrder::count() . ' órdenes.');
    }

    private function createBuyOrders($users, $energyPools): void
    {
        $this->command->info('🟢 Creando órdenes de compra...');

        for ($i = 0; $i < 25; $i++) {
            $trader = $users->random();
            $pool = $energyPools->isEmpty() ? null : $energyPools->random();
            $counterparty = fake()->optional(0.4)->randomElement($users->pluck('id')->toArray());
            $approver = fake()->optional(0.7)->randomElement($users->pluck('id')->toArray());
            $executor = fake()->optional(0.3)->randomElement($users->pluck('id')->toArray());

            $quantity = fake()->randomFloat(2, 50, 500);
            $price = fake()->randomFloat(2, 60, 150);
            $totalValue = $quantity * $price;
            
            $filledQuantity = fake()->randomFloat(2, 0, $quantity);
            $remainingQuantity = $quantity - $filledQuantity;
            $filledValue = $filledQuantity * $price;
            $remainingValue = $remainingQuantity * $price;

            $validFrom = Carbon::now()->subDays(rand(1, 30));
            $validUntil = fake()->optional(0.6)->dateTimeBetween($validFrom, '+6 months');
            $executionTime = fake()->optional(0.2)->dateTimeBetween($validFrom, $validUntil);
            $expiryTime = fake()->optional(0.3)->dateTimeBetween($validFrom, '+1 year');

            $orderStatus = $this->getOrderStatusBasedOnFill($filledQuantity, $quantity);
            $approvedAt = $orderStatus !== 'pending' ? fake()->optional(0.8)->dateTimeBetween($validFrom, 'now') : null;
            $executedAt = $filledQuantity > 0 ? fake()->optional(0.6)->dateTimeBetween($validFrom, 'now') : null;

            EnergyTradingOrder::create([
                'order_number' => 'BUY-' . strtoupper(fake()->bothify('####-??')),
                'order_type' => 'buy',
                'order_status' => $orderStatus,
                'order_side' => 'buy',
                'trader_id' => $trader->id,
                'pool_id' => $pool?->id,
                'counterparty_id' => $counterparty,
                'quantity_mwh' => $quantity,
                'filled_quantity_mwh' => $filledQuantity,
                'remaining_quantity_mwh' => $remainingQuantity,
                'price_per_mwh' => $price,
                'total_value' => $totalValue,
                'filled_value' => $filledValue,
                'remaining_value' => $remainingValue,
                'price_type' => fake()->randomElement(['fixed', 'floating', 'indexed']),
                'price_index' => fake()->optional(0.3)->randomElement(['SPOT', 'FUTURES', 'FORWARD', 'INDEX-001']),
                'price_adjustment' => fake()->optional(0.4)->randomFloat(2, -15, 15),
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'execution_time' => $executionTime,
                'expiry_time' => $expiryTime,
                'execution_type' => fake()->randomElement(['immediate', 'good_till_cancelled', 'good_till_date', 'fill_or_kill']),
                'priority' => fake()->randomElement(['normal', 'high', 'urgent']),
                'is_negotiable' => fake()->boolean(60),
                'negotiation_terms' => fake()->optional(0.4)->sentence,
                'special_conditions' => fake()->optional(0.3)->sentence,
                'delivery_requirements' => fake()->optional(0.3)->sentence,
                'payment_terms' => fake()->optional(0.3)->sentence,
                'order_conditions' => json_encode(fake()->optional(0.5)->randomElements([
                    'Must be renewable energy',
                    'Peak hours only',
                    'Off-peak hours only',
                    'Weekdays only',
                    'Minimum delivery period: 1 month',
                    'Grid stability requirements',
                    'Environmental compliance required'
                ], fake()->numberBetween(1, 3))),
                'order_restrictions' => json_encode(fake()->optional(0.4)->randomElements([
                    'No nuclear energy',
                    'No fossil fuels',
                    'Geographic restrictions apply',
                    'Time-of-use restrictions',
                    'Capacity limitations'
                ], fake()->numberBetween(1, 2))),
                'order_metadata' => json_encode([
                    'market_segment' => fake()->randomElement(['wholesale', 'retail', 'industrial']),
                    'trading_session' => fake()->randomElement(['day_ahead', 'intraday', 'balancing']),
                    'grid_operator' => fake()->company(),
                    'energy_exchange' => fake()->randomElement(['OMIE', 'EPEX', 'Nord Pool']),
                    'settlement_currency' => 'EUR',
                    'credit_rating' => fake()->randomElement(['AAA', 'AA', 'A', 'BBB']),
                    'collateral_required' => fake()->boolean(30),
                    'margin_requirements' => fake()->optional(0.4)->randomFloat(2, 5, 20),
                    'credit_limit' => fake()->optional(0.4)->randomFloat(2, 10000, 500000)
                ]),
                'tags' => json_encode(fake()->randomElements([
                    'Peak Hours', 'Renewable', 'Clean Energy', 'Grid Stability',
                    'High Priority', 'Negotiable', 'Long Term', 'Baseload'
                ], fake()->numberBetween(2, 4))),
                'created_by' => $trader->id,
                'approved_by' => $approver,
                'approved_at' => $approvedAt,
                'executed_by' => $executor,
                'executed_at' => $executedAt,
                'notes' => fake()->optional(0.6)->sentence,
            ]);
        }
    }

    private function createSellOrders($users, $energyPools): void
    {
        $this->command->info('🔴 Creando órdenes de venta...');

        for ($i = 0; $i < 20; $i++) {
            $trader = $users->random();
            $pool = $energyPools->isEmpty() ? null : $energyPools->random();
            $counterparty = fake()->optional(0.5)->randomElement($users->pluck('id')->toArray());
            $approver = fake()->optional(0.8)->randomElement($users->pluck('id')->toArray());
            $executor = fake()->optional(0.4)->randomElement($users->pluck('id')->toArray());

            $quantity = fake()->randomFloat(2, 30, 400);
            $price = fake()->randomFloat(2, 70, 180);
            $totalValue = $quantity * $price;
            
            $filledQuantity = fake()->randomFloat(2, 0, $quantity);
            $remainingQuantity = $quantity - $filledQuantity;
            $filledValue = $filledQuantity * $price;
            $remainingValue = $remainingQuantity * $price;

            $validFrom = Carbon::now()->subDays(rand(1, 30));
            $validUntil = fake()->optional(0.7)->dateTimeBetween($validFrom, '+6 months');
            $executionTime = fake()->optional(0.3)->dateTimeBetween($validFrom, $validUntil);
            $expiryTime = fake()->optional(0.4)->dateTimeBetween($validFrom, '+1 year');

            $orderStatus = $this->getOrderStatusBasedOnFill($filledQuantity, $quantity);
            $approvedAt = $orderStatus !== 'pending' ? fake()->optional(0.9)->dateTimeBetween($validFrom, 'now') : null;
            $executedAt = $filledQuantity > 0 ? fake()->optional(0.7)->dateTimeBetween($validFrom, 'now') : null;

            EnergyTradingOrder::create([
                'order_number' => 'SELL-' . strtoupper(fake()->bothify('####-??')),
                'order_type' => 'sell',
                'order_status' => $orderStatus,
                'order_side' => 'sell',
                'trader_id' => $trader->id,
                'pool_id' => $pool?->id,
                'counterparty_id' => $counterparty,
                'quantity_mwh' => $quantity,
                'filled_quantity_mwh' => $filledQuantity,
                'remaining_quantity_mwh' => $remainingQuantity,
                'price_per_mwh' => $price,
                'total_value' => $totalValue,
                'filled_value' => $filledValue,
                'remaining_value' => $remainingValue,
                'price_type' => fake()->randomElement(['fixed', 'floating', 'indexed']),
                'price_index' => fake()->optional(0.4)->randomElement(['SPOT', 'FUTURES', 'FORWARD', 'INDEX-002']),
                'price_adjustment' => fake()->optional(0.3)->randomFloat(2, -10, 20),
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'execution_time' => $executionTime,
                'expiry_time' => $expiryTime,
                'execution_type' => fake()->randomElement(['immediate', 'good_till_cancelled', 'good_till_date', 'all_or_nothing']),
                'priority' => fake()->randomElement(['normal', 'high', 'urgent']),
                'is_negotiable' => fake()->boolean(70),
                'negotiation_terms' => fake()->optional(0.5)->sentence,
                'special_conditions' => fake()->optional(0.4)->sentence,
                'delivery_requirements' => fake()->optional(0.4)->sentence,
                'payment_terms' => fake()->optional(0.4)->sentence,
                'order_conditions' => json_encode(fake()->optional(0.6)->randomElements([
                    'Peak hours only',
                    'Off-peak hours only',
                    'Weekends only',
                    'Maximum delivery period: 12 months',
                    'Quality certification required',
                    'Environmental compliance required'
                ], fake()->numberBetween(1, 3))),
                'order_restrictions' => json_encode(fake()->optional(0.5)->randomElements([
                    'No hydroelectric during drought',
                    'No wind energy during low wind periods',
                    'No solar energy during night',
                    'Geographic restrictions apply',
                    'Voltage requirements'
                ], fake()->numberBetween(1, 2))),
                'order_metadata' => json_encode([
                    'market_segment' => fake()->randomElement(['wholesale', 'retail', 'commercial']),
                    'trading_session' => fake()->randomElement(['day_ahead', 'intraday', 'reserve']),
                    'grid_operator' => fake()->company(),
                    'energy_exchange' => fake()->randomElement(['OMIE', 'EPEX', 'PJM']),
                    'settlement_currency' => 'EUR',
                    'credit_rating' => fake()->randomElement(['AA', 'A', 'BBB', 'BB']),
                    'collateral_required' => fake()->boolean(40),
                    'margin_requirements' => fake()->optional(0.5)->randomFloat(2, 8, 25),
                    'credit_limit' => fake()->optional(0.5)->randomFloat(2, 5000, 300000)
                ]),
                'tags' => json_encode(fake()->randomElements([
                    'Off-Peak', 'Clean Energy', 'Sustainable', 'Eco-Friendly',
                    'High Priority', 'Negotiable', 'Short Term', 'Peaking'
                ], fake()->numberBetween(2, 4))),
                'created_by' => $trader->id,
                'approved_by' => $approver,
                'approved_at' => $approvedAt,
                'executed_by' => $executor,
                'executed_at' => $executedAt,
                'notes' => fake()->optional(0.7)->sentence,
            ]);
        }
    }

    private function createMarketOrders($users, $energyPools): void
    {
        $this->command->info('📊 Creando órdenes de mercado...');

        for ($i = 0; $i < 15; $i++) {
            $trader = $users->random();
            $pool = $energyPools->isEmpty() ? null : $energyPools->random();
            $counterparty = fake()->optional(0.6)->randomElement($users->pluck('id')->toArray());
            $approver = fake()->optional(0.6)->randomElement($users->pluck('id')->toArray());
            $executor = fake()->optional(0.5)->randomElement($users->pluck('id')->toArray());

            $quantity = fake()->randomFloat(2, 20, 300);
            $price = fake()->randomFloat(2, 50, 200);
            $totalValue = $quantity * $price;
            
            $filledQuantity = fake()->randomFloat(2, 0, $quantity);
            $remainingQuantity = $quantity - $filledQuantity;
            $filledValue = $filledQuantity * $price;
            $remainingValue = $remainingQuantity * $price;

            $validFrom = Carbon::now()->subDays(rand(1, 30));
            $validUntil = fake()->optional(0.5)->dateTimeBetween($validFrom, '+3 months');
            $executionTime = fake()->optional(0.4)->dateTimeBetween($validFrom, $validUntil);
            $expiryTime = fake()->optional(0.2)->dateTimeBetween($validFrom, '+6 months');

            $orderStatus = $this->getOrderStatusBasedOnFill($filledQuantity, $quantity);
            $approvedAt = $orderStatus !== 'pending' ? fake()->optional(0.7)->dateTimeBetween($validFrom, 'now') : null;
            $executedAt = $filledQuantity > 0 ? fake()->optional(0.8)->dateTimeBetween($validFrom, 'now') : null;

            EnergyTradingOrder::create([
                'order_number' => 'MKT-' . strtoupper(fake()->bothify('####-??')),
                'order_type' => 'market',
                'order_status' => $orderStatus,
                'order_side' => fake()->randomElement(['buy', 'sell']),
                'trader_id' => $trader->id,
                'pool_id' => $pool?->id,
                'counterparty_id' => $counterparty,
                'quantity_mwh' => $quantity,
                'filled_quantity_mwh' => $filledQuantity,
                'remaining_quantity_mwh' => $remainingQuantity,
                'price_per_mwh' => $price,
                'total_value' => $totalValue,
                'filled_value' => $filledValue,
                'remaining_value' => $remainingValue,
                'price_type' => 'floating',
                'price_index' => fake()->optional(0.5)->randomElement(['SPOT', 'FUTURES', 'INDEX-003']),
                'price_adjustment' => fake()->optional(0.3)->randomFloat(2, -5, 10),
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'execution_time' => $executionTime,
                'expiry_time' => $expiryTime,
                'execution_type' => 'immediate',
                'priority' => fake()->randomElement(['normal', 'high']),
                'is_negotiable' => fake()->boolean(40),
                'negotiation_terms' => fake()->optional(0.3)->sentence,
                'special_conditions' => fake()->optional(0.2)->sentence,
                'delivery_requirements' => fake()->optional(0.2)->sentence,
                'payment_terms' => fake()->optional(0.2)->sentence,
                'order_conditions' => json_encode(fake()->optional(0.4)->randomElements([
                    'Market price execution',
                    'Immediate settlement',
                    'Standard delivery terms'
                ], fake()->numberBetween(1, 2))),
                'order_restrictions' => json_encode(fake()->optional(0.3)->randomElements([
                    'Market hours only',
                    'Standard capacity limits'
                ], fake()->numberBetween(1, 1))),
                'order_metadata' => json_encode([
                    'market_segment' => 'wholesale',
                    'trading_session' => fake()->randomElement(['day_ahead', 'intraday']),
                    'grid_operator' => fake()->company(),
                    'energy_exchange' => fake()->randomElement(['OMIE', 'EPEX', 'ERCOT']),
                    'settlement_currency' => 'EUR',
                    'credit_rating' => fake()->randomElement(['A', 'BBB', 'BB']),
                    'collateral_required' => fake()->boolean(50),
                    'margin_requirements' => fake()->optional(0.6)->randomFloat(2, 10, 30),
                    'credit_limit' => fake()->optional(0.6)->randomFloat(2, 2000, 200000)
                ]),
                'tags' => json_encode(fake()->randomElements([
                    'Market Order', 'Immediate', 'Flexible', 'Standard',
                    'Quick Execution', 'Market Price'
                ], fake()->numberBetween(2, 3))),
                'created_by' => $trader->id,
                'approved_by' => $approver,
                'approved_at' => $approvedAt,
                'executed_by' => $executor,
                'executed_at' => $executedAt,
                'notes' => fake()->optional(0.5)->sentence,
            ]);
        }
    }

    private function createLimitOrders($users, $energyPools): void
    {
        $this->command->info('🎯 Creando órdenes límite...');

        for ($i = 0; $i < 18; $i++) {
            $trader = $users->random();
            $pool = $energyPools->isEmpty() ? null : $energyPools->random();
            $counterparty = fake()->optional(0.5)->randomElement($users->pluck('id')->toArray());
            $approver = fake()->optional(0.8)->randomElement($users->pluck('id')->toArray());
            $executor = fake()->optional(0.3)->randomElement($users->pluck('id')->toArray());

            $quantity = fake()->randomFloat(2, 40, 350);
            $price = fake()->randomFloat(2, 55, 170);
            $totalValue = $quantity * $price;
            
            $filledQuantity = fake()->randomFloat(2, 0, $quantity);
            $remainingQuantity = $quantity - $filledQuantity;
            $filledValue = $filledQuantity * $price;
            $remainingValue = $remainingQuantity * $price;

            $validFrom = Carbon::now()->subDays(rand(1, 30));
            $validUntil = fake()->optional(0.8)->dateTimeBetween($validFrom, '+6 months');
            $executionTime = fake()->optional(0.2)->dateTimeBetween($validFrom, $validUntil);
            $expiryTime = fake()->optional(0.5)->dateTimeBetween($validFrom, '+1 year');

            $orderStatus = $this->getOrderStatusBasedOnFill($filledQuantity, $quantity);
            $approvedAt = $orderStatus !== 'pending' ? fake()->optional(0.9)->dateTimeBetween($validFrom, 'now') : null;
            $executedAt = $filledQuantity > 0 ? fake()->optional(0.6)->dateTimeBetween($validFrom, 'now') : null;

            EnergyTradingOrder::create([
                'order_number' => 'LIM-' . strtoupper(fake()->bothify('####-??')),
                'order_type' => 'limit',
                'order_status' => $orderStatus,
                'order_side' => fake()->randomElement(['buy', 'sell']),
                'trader_id' => $trader->id,
                'pool_id' => $pool?->id,
                'counterparty_id' => $counterparty,
                'quantity_mwh' => $quantity,
                'filled_quantity_mwh' => $filledQuantity,
                'remaining_quantity_mwh' => $remainingQuantity,
                'price_per_mwh' => $price,
                'total_value' => $totalValue,
                'filled_value' => $filledValue,
                'remaining_value' => $remainingValue,
                'price_type' => 'fixed',
                'price_index' => fake()->optional(0.2)->randomElement(['SPOT', 'FUTURES']),
                'price_adjustment' => fake()->optional(0.2)->randomFloat(2, -8, 8),
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'execution_time' => $executionTime,
                'expiry_time' => $expiryTime,
                'execution_type' => fake()->randomElement(['good_till_cancelled', 'good_till_date', 'fill_or_kill']),
                'priority' => fake()->randomElement(['normal', 'high', 'urgent']),
                'is_negotiable' => fake()->boolean(50),
                'negotiation_terms' => fake()->optional(0.4)->sentence,
                'special_conditions' => fake()->optional(0.3)->sentence,
                'delivery_requirements' => fake()->optional(0.3)->sentence,
                'payment_terms' => fake()->optional(0.3)->sentence,
                'order_conditions' => json_encode(fake()->optional(0.6)->randomElements([
                    'Price limit enforced',
                    'Quantity must be exact',
                    'Time limit applies',
                    'Quality standards required',
                    'Delivery schedule fixed'
                ], fake()->numberBetween(1, 3))),
                'order_restrictions' => json_encode(fake()->optional(0.5)->randomElements([
                    'Price cannot exceed limit',
                    'Quantity cannot be partial',
                    'Time restrictions apply',
                    'Quality requirements strict'
                ], fake()->numberBetween(1, 2))),
                'order_metadata' => json_encode([
                    'market_segment' => fake()->randomElement(['wholesale', 'retail', 'industrial']),
                    'trading_session' => fake()->randomElement(['day_ahead', 'intraday', 'balancing']),
                    'grid_operator' => fake()->company(),
                    'energy_exchange' => fake()->randomElement(['OMIE', 'EPEX', 'Nord Pool']),
                    'settlement_currency' => 'EUR',
                    'credit_rating' => fake()->randomElement(['AAA', 'AA', 'A', 'BBB']),
                    'collateral_required' => fake()->boolean(60),
                    'margin_requirements' => fake()->optional(0.7)->randomFloat(2, 8, 25),
                    'credit_limit' => fake()->optional(0.7)->randomFloat(2, 5000, 400000)
                ]),
                'tags' => json_encode(fake()->randomElements([
                    'Limit Order', 'Fixed Price', 'Exact Quantity', 'Time Sensitive',
                    'Quality Required', 'Strict Terms'
                ], fake()->numberBetween(2, 4))),
                'created_by' => $trader->id,
                'approved_by' => $approver,
                'approved_at' => $approvedAt,
                'executed_by' => $executor,
                'executed_at' => $executedAt,
                'notes' => fake()->optional(0.6)->sentence,
            ]);
        }
    }

    private function createStopOrders($users, $energyPools): void
    {
        $this->command->info('🛑 Creando órdenes stop...');

        for ($i = 0; $i < 12; $i++) {
            $trader = $users->random();
            $pool = $energyPools->isEmpty() ? null : $energyPools->random();
            $counterparty = fake()->optional(0.4)->randomElement($users->pluck('id')->toArray());
            $approver = fake()->optional(0.7)->randomElement($users->pluck('id')->toArray());
            $executor = fake()->optional(0.2)->randomElement($users->pluck('id')->toArray());

            $quantity = fake()->randomFloat(2, 25, 250);
            $price = fake()->randomFloat(2, 45, 160);
            $totalValue = $quantity * $price;
            
            $filledQuantity = fake()->randomFloat(2, 0, $quantity);
            $remainingQuantity = $quantity - $filledQuantity;
            $filledValue = $filledQuantity * $price;
            $remainingValue = $remainingQuantity * $price;

            $validFrom = Carbon::now()->subDays(rand(1, 30));
            $validUntil = fake()->optional(0.6)->dateTimeBetween($validFrom, '+4 months');
            $executionTime = fake()->optional(0.1)->dateTimeBetween($validFrom, $validUntil);
            $expiryTime = fake()->optional(0.4)->dateTimeBetween($validFrom, '+8 months');

            $orderStatus = $this->getOrderStatusBasedOnFill($filledQuantity, $quantity);
            $approvedAt = $orderStatus !== 'pending' ? fake()->optional(0.8)->dateTimeBetween($validFrom, 'now') : null;
            $executedAt = $filledQuantity > 0 ? fake()->optional(0.4)->dateTimeBetween($validFrom, 'now') : null;

            EnergyTradingOrder::create([
                'order_number' => 'STP-' . strtoupper(fake()->bothify('####-??')),
                'order_type' => fake()->randomElement(['stop', 'stop_limit']),
                'order_status' => $orderStatus,
                'order_side' => fake()->randomElement(['buy', 'sell']),
                'trader_id' => $trader->id,
                'pool_id' => $pool?->id,
                'counterparty_id' => $counterparty,
                'quantity_mwh' => $quantity,
                'filled_quantity_mwh' => $filledQuantity,
                'remaining_quantity_mwh' => $remainingQuantity,
                'price_per_mwh' => $price,
                'total_value' => $totalValue,
                'filled_value' => $filledValue,
                'remaining_value' => $remainingValue,
                'price_type' => fake()->randomElement(['fixed', 'floating']),
                'price_index' => fake()->optional(0.3)->randomElement(['SPOT', 'FUTURES', 'INDEX-004']),
                'price_adjustment' => fake()->optional(0.3)->randomFloat(2, -12, 15),
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'execution_time' => $executionTime,
                'expiry_time' => $expiryTime,
                'execution_type' => fake()->randomElement(['good_till_cancelled', 'good_till_date', 'fill_or_kill']),
                'priority' => fake()->randomElement(['high', 'urgent', 'critical']),
                'is_negotiable' => fake()->boolean(30),
                'negotiation_terms' => fake()->optional(0.2)->sentence,
                'special_conditions' => fake()->optional(0.2)->sentence,
                'delivery_requirements' => fake()->optional(0.2)->sentence,
                'payment_terms' => fake()->optional(0.2)->sentence,
                'order_conditions' => json_encode(fake()->optional(0.7)->randomElements([
                    'Stop price triggered',
                    'Emergency execution',
                    'Risk management',
                    'Market volatility protection',
                    'Automatic execution required'
                ], fake()->numberBetween(1, 3))),
                'order_restrictions' => json_encode(fake()->optional(0.6)->randomElements([
                    'Stop price must be reached',
                    'No price negotiation',
                    'Immediate execution when triggered',
                    'Risk limits apply'
                ], fake()->numberBetween(1, 2))),
                'order_metadata' => json_encode([
                    'market_segment' => fake()->randomElement(['wholesale', 'industrial']),
                    'trading_session' => fake()->randomElement(['intraday', 'balancing', 'reserve']),
                    'grid_operator' => fake()->company(),
                    'energy_exchange' => fake()->randomElement(['OMIE', 'EPEX', 'PJM']),
                    'settlement_currency' => 'EUR',
                    'credit_rating' => fake()->randomElement(['AA', 'A', 'BBB']),
                    'collateral_required' => fake()->boolean(80),
                    'margin_requirements' => fake()->optional(0.8)->randomFloat(2, 15, 35),
                    'credit_limit' => fake()->optional(0.8)->randomFloat(2, 3000, 300000)
                ]),
                'tags' => json_encode(fake()->randomElements([
                    'Stop Order', 'Risk Management', 'Emergency', 'Volatility Protection',
                    'Automatic Execution', 'High Priority'
                ], fake()->numberBetween(2, 4))),
                'created_by' => $trader->id,
                'approved_by' => $approver,
                'approved_at' => $approvedAt,
                'executed_by' => $executor,
                'executed_at' => $executedAt,
                'notes' => fake()->optional(0.4)->sentence,
            ]);
        }
    }

    private function createBidAskOrders($users, $energyPools): void
    {
        $this->command->info('💰 Creando órdenes bid/ask...');

        for ($i = 0; $i < 10; $i++) {
            $trader = $users->random();
            $pool = $energyPools->isEmpty() ? null : $energyPools->random();
            $counterparty = fake()->optional(0.6)->randomElement($users->pluck('id')->toArray());
            $approver = fake()->optional(0.6)->randomElement($users->pluck('id')->toArray());
            $executor = fake()->optional(0.3)->randomElement($users->pluck('id')->toArray());

            $quantity = fake()->randomFloat(2, 15, 200);
            $price = fake()->randomFloat(2, 40, 140);
            $totalValue = $quantity * $price;
            
            $filledQuantity = fake()->randomFloat(2, 0, $quantity);
            $remainingQuantity = $quantity - $filledQuantity;
            $filledValue = $filledQuantity * $price;
            $remainingValue = $remainingQuantity * $price;

            $validFrom = Carbon::now()->subDays(rand(1, 30));
            $validUntil = fake()->optional(0.7)->dateTimeBetween($validFrom, '+5 months');
            $executionTime = fake()->optional(0.2)->dateTimeBetween($validFrom, $validUntil);
            $expiryTime = fake()->optional(0.3)->dateTimeBetween($validFrom, '+10 months');

            $orderStatus = $this->getOrderStatusBasedOnFill($filledQuantity, $quantity);
            $approvedAt = $orderStatus !== 'pending' ? fake()->optional(0.7)->dateTimeBetween($validFrom, 'now') : null;
            $executedAt = $filledQuantity > 0 ? fake()->optional(0.5)->dateTimeBetween($validFrom, 'now') : null;

            EnergyTradingOrder::create([
                'order_number' => 'BID-' . strtoupper(fake()->bothify('####-??')),
                'order_type' => 'bid',
                'order_status' => $orderStatus,
                'order_side' => 'buy',
                'trader_id' => $trader->id,
                'pool_id' => $pool?->id,
                'counterparty_id' => $counterparty,
                'quantity_mwh' => $quantity,
                'filled_quantity_mwh' => $filledQuantity,
                'remaining_quantity_mwh' => $remainingQuantity,
                'price_per_mwh' => $price,
                'total_value' => $totalValue,
                'filled_value' => $filledValue,
                'remaining_value' => $remainingValue,
                'price_type' => fake()->randomElement(['fixed', 'indexed']),
                'price_index' => fake()->optional(0.4)->randomElement(['SPOT', 'FUTURES', 'INDEX-005']),
                'price_adjustment' => fake()->optional(0.4)->randomFloat(2, -10, 12),
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'execution_time' => $executionTime,
                'expiry_time' => $expiryTime,
                'execution_type' => fake()->randomElement(['good_till_cancelled', 'good_till_date', 'fill_or_kill']),
                'priority' => fake()->randomElement(['normal', 'high']),
                'is_negotiable' => fake()->boolean(80),
                'negotiation_terms' => fake()->optional(0.6)->sentence,
                'special_conditions' => fake()->optional(0.4)->sentence,
                'delivery_requirements' => fake()->optional(0.4)->sentence,
                'payment_terms' => fake()->optional(0.4)->sentence,
                'order_conditions' => json_encode(fake()->optional(0.8)->randomElements([
                    'Bid price maximum',
                    'Negotiable terms',
                    'Flexible delivery',
                    'Quality standards negotiable',
                    'Payment terms flexible'
                ], fake()->numberBetween(1, 3))),
                'order_restrictions' => json_encode(fake()->optional(0.7)->randomElements([
                    'Bid price limit',
                    'Quantity negotiable',
                    'Time flexibility',
                    'Quality requirements flexible'
                ], fake()->numberBetween(1, 2))),
                'order_metadata' => json_encode([
                    'market_segment' => fake()->randomElement(['wholesale', 'retail', 'commercial']),
                    'trading_session' => fake()->randomElement(['day_ahead', 'intraday', 'balancing']),
                    'grid_operator' => fake()->company(),
                    'energy_exchange' => fake()->randomElement(['OMIE', 'EPEX', 'Nord Pool']),
                    'settlement_currency' => 'EUR',
                    'credit_rating' => fake()->randomElement(['A', 'BBB', 'BB']),
                    'collateral_required' => fake()->boolean(40),
                    'margin_requirements' => fake()->optional(0.5)->randomFloat(2, 5, 20),
                    'credit_limit' => fake()->optional(0.5)->randomFloat(2, 1000, 150000)
                ]),
                'tags' => json_encode(fake()->randomElements([
                    'Bid Order', 'Negotiable', 'Flexible', 'Best Price',
                    'Quality Negotiable', 'Terms Flexible'
                ], fake()->numberBetween(2, 4))),
                'created_by' => $trader->id,
                'approved_by' => $approver,
                'approved_at' => $approvedAt,
                'executed_by' => $executor,
                'executed_at' => $executedAt,
                'notes' => fake()->optional(0.7)->sentence,
            ]);
        }
    }

    private function getOrderStatusBasedOnFill($filledQuantity, $totalQuantity): string
    {
        if ($filledQuantity <= 0) {
            return fake()->randomElement(['pending', 'active']);
        } elseif ($filledQuantity >= $totalQuantity) {
            return fake()->randomElement(['filled', 'completed']);
        } else {
            return 'partially_filled';
        }
    }
}
