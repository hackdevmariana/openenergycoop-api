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
        Schema::create('api_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('token', 64)->unique(); // API key hash seguro
            $table->json('scopes'); // Permisos del cliente
            $table->timestamp('last_used_at')->nullable(); // Último uso
            $table->enum('status', ['active', 'revoked', 'suspended'])->default('active');
            $table->json('allowed_ips')->nullable(); // IPs permitidas
            $table->string('callback_url')->nullable(); // URL de callback
            $table->timestamp('expires_at')->nullable(); // Fecha de expiración
            $table->timestamp('revoked_at')->nullable(); // Fecha de revocación
            $table->string('description')->nullable(); // Descripción del cliente
            $table->json('rate_limits')->nullable(); // Límites de tasa
            $table->json('webhook_config')->nullable(); // Configuración de webhooks
            $table->json('permissions')->nullable(); // Permisos específicos
            $table->string('version')->default('1.0'); // Versión de la API
            $table->json('metadata')->nullable(); // Metadatos adicionales
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['organization_id', 'status']);
            $table->index('status');
            $table->index('token');
            $table->index('last_used_at');
            $table->index('expires_at');
            $table->index('revoked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_clients');
    }
};
