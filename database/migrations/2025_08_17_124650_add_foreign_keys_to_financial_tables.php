<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregar foreign keys a payments
        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
        });

        // Agregar foreign keys a transactions
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('set null');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
        });

        // Agregar foreign keys a wallet_transactions
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('set null');
        });

        // Agregar foreign keys a refunds
        Schema::table('refunds', function (Blueprint $table) {
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover foreign keys de refunds
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropForeign(['payment_id']);
            $table->dropForeign(['invoice_id']);
            $table->dropForeign(['transaction_id']);
        });

        // Remover foreign keys de wallet_transactions
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
        });

        // Remover foreign keys de transactions
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['payment_id']);
            $table->dropForeign(['invoice_id']);
        });

        // Remover foreign keys de payments
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
        });
    }
};