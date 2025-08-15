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
        Schema::table('legal_documents', function (Blueprint $table) {
            $table->string('version')->default('1.0')->after('type');
            $table->text('notes')->nullable()->after('verifier_user_id');
            $table->timestamp('expires_at')->nullable()->after('notes');
            
            // Índices para consultas frecuentes
            $table->index(['customer_profile_id', 'type', 'version']);
            $table->index(['expires_at']);
            $table->index(['version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legal_documents', function (Blueprint $table) {
            $table->dropIndex(['customer_profile_id', 'type', 'version']);
            $table->dropIndex(['expires_at']);
            $table->dropIndex(['version']);
            
            $table->dropColumn(['version', 'notes', 'expires_at']);
        });
    }
};
