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
        Schema::create('consent_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('consent_type'); // 'terms_and_conditions', 'privacy_policy', etc.
            $table->timestamp('consented_at');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('version')->nullable(); // versión de la política
            $table->string('consent_document_url')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->json('consent_context')->nullable(); // contexto adicional
            $table->timestamps();
            
            // Índices para consultas frecuentes
            $table->index(['user_id', 'consent_type']);
            $table->index(['consent_type', 'consented_at']);
            $table->index('revoked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consent_logs');
    }
};
