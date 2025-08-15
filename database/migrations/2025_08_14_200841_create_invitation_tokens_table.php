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
        Schema::create('invitation_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique(); // UUID o hash seguro
            $table->string('email')->nullable(); // Si quieres predefinir el destinatario
            $table->foreignId('organization_role_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('invited_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('expires_at')->nullable(); // Tiempo de caducidad
            $table->timestamp('used_at')->nullable(); // Marca cuándo fue usado
            $table->enum('status', ['pending', 'used', 'expired', 'revoked'])->default('pending');
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index(['organization_id', 'status']);
            $table->index(['invited_by', 'created_at']);
            $table->index(['expires_at', 'status']);
            $table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitation_tokens');
    }
};
