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
        Schema::table('consent_logs', function (Blueprint $table) {
            // Add fields that the controller expects
            $table->boolean('consent_given')->default(true)->after('consent_type');
            $table->string('purpose', 500)->nullable()->after('version');
            $table->string('legal_basis', 200)->nullable()->after('purpose');
            $table->json('data_categories')->nullable()->after('legal_basis');
            $table->string('retention_period', 100)->nullable()->after('data_categories');
            $table->json('third_parties')->nullable()->after('retention_period');
            $table->string('withdrawal_method', 200)->nullable()->after('third_parties');
            $table->string('revocation_reason', 500)->nullable()->after('revoked_at');
            $table->json('metadata')->nullable()->after('consent_context'); // Additional field used in resource
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consent_logs', function (Blueprint $table) {
            $table->dropColumn([
                'consent_given',
                'purpose',
                'legal_basis',
                'data_categories',
                'retention_period',
                'third_parties',
                'withdrawal_method',
                'revocation_reason',
                'metadata'
            ]);
        });
    }
};