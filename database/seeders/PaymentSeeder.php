<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\User;
use App\Models\Invoice;
use App\Models\EnergyCooperative;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::take(10)->get();
        $invoices = Invoice::take(5)->get();
        $cooperatives = EnergyCooperative::take(3)->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. Saltando PaymentSeeder.');
            return;
        }

        $payments = [
            [
                'payment_code' => Payment::generatePaymentCode(),
                'external_id' => 'ext_' . uniqid(),
                'user_id' => $users->first()->id,
                'invoice_id' => $invoices->first()?->id,
                'type' => 'payment',
                'status' => 'completed',
                'method' => 'card',
                'amount' => 125.50,
                'fee' => 3.75,
                'net_amount' => 121.75,
                'currency' => 'EUR',
                'exchange_rate' => 1.0,
                'original_amount' => 125.50,
                'original_currency' => 'EUR',
                'gateway' => 'stripe',
                'gateway_transaction_id' => 'pi_' . uniqid(),
                'gateway_response' => ['status' => 'succeeded', 'charge_id' => 'ch_' . uniqid()],
                'gateway_metadata' => ['source' => 'card', 'brand' => 'visa'],
                'card_last_four' => '4242',
                'card_brand' => 'visa',
                'payment_method_id' => 'pm_' . uniqid(),
                'processed_at' => Carbon::now()->subDays(5),
                'authorized_at' => Carbon::now()->subDays(5)->addMinutes(2),
                'captured_at' => Carbon::now()->subDays(5)->addMinutes(5),
                'description' => 'Compra de energía solar - 150 kWh',
                'reference' => 'REF-' . uniqid(),
                'metadata' => ['energy_type' => 'solar', 'kwh_amount' => 150],
                'energy_cooperative_id' => $cooperatives->first()?->id,
                'energy_amount_kwh' => 150.00,
                'created_by_id' => $users->first()->id,
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'is_test' => false,
                'is_recurring' => false,
            ],
            [
                'payment_code' => Payment::generatePaymentCode(),
                'external_id' => 'ext_' . uniqid(),
                'user_id' => $users->skip(1)->first()->id,
                'invoice_id' => $invoices->skip(1)->first()?->id,
                'type' => 'payment',
                'status' => 'completed',
                'method' => 'bank_transfer',
                'amount' => 89.99,
                'fee' => 0.00,
                'net_amount' => 89.99,
                'currency' => 'EUR',
                'exchange_rate' => 1.0,
                'original_amount' => 89.99,
                'original_currency' => 'EUR',
                'gateway' => 'bank_transfer',
                'gateway_transaction_id' => 'bt_' . uniqid(),
                'gateway_response' => ['status' => 'completed', 'reference' => 'BT-' . uniqid()],
                'gateway_metadata' => ['bank' => 'BBVA', 'account_holder' => 'Juan Pérez'],
                'processed_at' => Carbon::now()->subDays(3),
                'authorized_at' => Carbon::now()->subDays(3),
                'captured_at' => Carbon::now()->subDays(3),
                'description' => 'Suscripción mensual - Plan Premium',
                'reference' => 'SUB-' . uniqid(),
                'metadata' => ['plan' => 'premium', 'billing_cycle' => 'monthly'],
                'created_by_id' => $users->skip(1)->first()->id,
                'ip_address' => '192.168.1.101',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'is_test' => false,
                'is_recurring' => true,
            ],
            [
                'payment_code' => Payment::generatePaymentCode(),
                'external_id' => 'ext_' . uniqid(),
                'user_id' => $users->skip(2)->first()->id,
                'invoice_id' => $invoices->skip(2)->first()?->id,
                'type' => 'payment',
                'status' => 'pending',
                'method' => 'paypal',
                'amount' => 75.25,
                'fee' => 2.25,
                'net_amount' => 73.00,
                'currency' => 'EUR',
                'exchange_rate' => 1.0,
                'original_amount' => 75.25,
                'original_currency' => 'EUR',
                'gateway' => 'paypal',
                'gateway_transaction_id' => 'pp_' . uniqid(),
                'gateway_response' => ['status' => 'pending', 'approval_url' => 'https://paypal.com/approve'],
                'gateway_metadata' => ['payer_id' => 'PAYER_' . uniqid()],
                'expires_at' => Carbon::now()->addHours(24),
                'description' => 'Compra de energía eólica - 100 kWh',
                'reference' => 'WIND-' . uniqid(),
                'metadata' => ['energy_type' => 'wind', 'kwh_amount' => 100],
                'energy_cooperative_id' => $cooperatives->skip(1)->first()?->id,
                'energy_amount_kwh' => 100.00,
                'created_by_id' => $users->skip(2)->first()->id,
                'ip_address' => '192.168.1.102',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
                'is_test' => false,
                'is_recurring' => false,
            ],
            [
                'payment_code' => Payment::generatePaymentCode(),
                'external_id' => 'ext_' . uniqid(),
                'user_id' => $users->skip(3)->first()->id,
                'invoice_id' => $invoices->skip(3)->first()?->id,
                'type' => 'credit',
                'status' => 'completed',
                'method' => 'wallet',
                'amount' => 200.00,
                'fee' => 0.00,
                'net_amount' => 200.00,
                'currency' => 'EUR',
                'exchange_rate' => 1.0,
                'original_amount' => 200.00,
                'original_currency' => 'EUR',
                'gateway' => 'internal',
                'gateway_transaction_id' => 'wallet_' . uniqid(),
                'gateway_response' => ['status' => 'completed', 'wallet_balance' => 200.00],
                'gateway_metadata' => ['wallet_id' => 'wallet_' . uniqid()],
                'processed_at' => Carbon::now()->subDays(1),
                'authorized_at' => Carbon::now()->subDays(1),
                'captured_at' => Carbon::now()->subDays(1),
                'description' => 'Recarga de wallet - €200',
                'reference' => 'WALLET-' . uniqid(),
                'metadata' => ['topup_amount' => 200, 'wallet_balance' => 200],
                'created_by_id' => $users->skip(3)->first()->id,
                'ip_address' => '192.168.1.103',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15',
                'is_test' => false,
                'is_recurring' => false,
            ],
            [
                'payment_code' => Payment::generatePaymentCode(),
                'external_id' => 'ext_' . uniqid(),
                'user_id' => $users->skip(4)->first()->id,
                'invoice_id' => $invoices->skip(4)->first()?->id,
                'type' => 'payment',
                'status' => 'failed',
                'method' => 'card',
                'amount' => 45.75,
                'fee' => 1.37,
                'net_amount' => 44.38,
                'currency' => 'EUR',
                'exchange_rate' => 1.0,
                'original_amount' => 45.75,
                'original_currency' => 'EUR',
                'gateway' => 'stripe',
                'gateway_transaction_id' => 'pi_' . uniqid(),
                'gateway_response' => ['status' => 'failed', 'error' => 'card_declined'],
                'gateway_metadata' => ['source' => 'card', 'brand' => 'mastercard'],
                'card_last_four' => '5555',
                'card_brand' => 'mastercard',
                'payment_method_id' => 'pm_' . uniqid(),
                'failed_at' => Carbon::now()->subDays(2),
                'description' => 'Compra de energía hidroeléctrica - 75 kWh',
                'reference' => 'HYDRO-' . uniqid(),
                'metadata' => ['energy_type' => 'hydro', 'kwh_amount' => 75],
                'failure_reason' => 'Tarjeta rechazada por el banco',
                'energy_cooperative_id' => $cooperatives->skip(2)->first()?->id,
                'energy_amount_kwh' => 75.00,
                'created_by_id' => $users->skip(4)->first()->id,
                'ip_address' => '192.168.1.104',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'is_test' => false,
                'is_recurring' => false,
            ],
            [
                'payment_code' => Payment::generatePaymentCode(),
                'external_id' => 'ext_' . uniqid(),
                'user_id' => $users->skip(5)->first()->id,
                'invoice_id' => null,
                'type' => 'credit',
                'status' => 'completed',
                'method' => 'energy_credits',
                'amount' => 0.00,
                'fee' => 0.00,
                'net_amount' => 0.00,
                'currency' => 'EUR',
                'exchange_rate' => 1.0,
                'original_amount' => 0.00,
                'original_currency' => 'EUR',
                'gateway' => 'internal',
                'gateway_transaction_id' => 'credits_' . uniqid(),
                'gateway_response' => ['status' => 'completed', 'credits_used' => 50],
                'gateway_metadata' => ['credit_type' => 'solar_bonus'],
                'processed_at' => Carbon::now()->subHours(6),
                'authorized_at' => Carbon::now()->subHours(6),
                'captured_at' => Carbon::now()->subHours(6),
                'description' => 'Uso de créditos energéticos - 50 kWh',
                'reference' => 'CREDITS-' . uniqid(),
                'metadata' => ['credits_used' => 50, 'credit_type' => 'solar_bonus'],
                'energy_cooperative_id' => $cooperatives->first()?->id,
                'energy_amount_kwh' => 50.00,
                'created_by_id' => $users->skip(5)->first()->id,
                'ip_address' => '192.168.1.105',
                'user_agent' => 'Mozilla/5.0 (Android 11; Mobile; rv:68.0) Gecko/68.0 Firefox/88.0',
                'is_test' => false,
                'is_recurring' => false,
            ],
        ];

        foreach ($payments as $payment) {
            Payment::firstOrCreate(
                ['payment_code' => $payment['payment_code']],
                $payment
            );
        }

        $this->command->info('✅ PaymentSeeder ejecutado correctamente');
        $this->command->info('📊 Pagos creados: ' . count($payments));
        $this->command->info('💳 Métodos de pago: Tarjeta, Transferencia bancaria, PayPal, Wallet, Créditos energéticos');
        $this->command->info('📈 Estados: Completado (4), Pendiente (1), Fallido (1)');
        $this->command->info('💰 Monto total: €' . number_format(collect($payments)->sum('amount'), 2));
        $this->command->info('⚡ Energía total: ' . number_format(collect($payments)->sum('energy_amount_kwh'), 2) . ' kWh');
        $this->command->info('🏦 Gateways: Stripe, PayPal, Transferencia bancaria, Interno');
        $this->command->info('🔄 Tipos: Compra de energía, Suscripción, Recarga de wallet, Créditos energéticos');
    }
}
