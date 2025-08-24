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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('actor_type')->nullable(); // Tipo de actor (user, system, api, etc.)
            $table->string('actor_identifier')->nullable(); // Identificador del actor
            $table->string('action'); // Acción realizada
            $table->text('description')->nullable(); // Descripción de la acción
            $table->string('auditable_type')->nullable(); // Tipo de modelo auditado
            $table->unsignedBigInteger('auditable_id')->nullable(); // ID del modelo auditado
            $table->json('old_values')->nullable(); // Valores anteriores
            $table->json('new_values')->nullable(); // Valores nuevos
            $table->string('ip_address')->nullable(); // Dirección IP
            $table->text('user_agent')->nullable(); // User agent del navegador
            $table->string('url')->nullable(); // URL de la acción
            $table->string('method')->nullable(); // Método HTTP
            $table->json('request_data')->nullable(); // Datos de la petición
            $table->json('response_data')->nullable(); // Datos de la respuesta
            $table->integer('response_code')->nullable(); // Código de respuesta HTTP
            $table->string('session_id')->nullable(); // ID de sesión
            $table->string('request_id')->nullable(); // ID único de la petición
            $table->json('metadata')->nullable(); // Metadatos adicionales
            $table->timestamps();

            // Índices
            $table->index(['user_id', 'created_at']);
            $table->index(['actor_type', 'actor_identifier']);
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('action');
            $table->index('created_at');
            $table->index('ip_address');
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
