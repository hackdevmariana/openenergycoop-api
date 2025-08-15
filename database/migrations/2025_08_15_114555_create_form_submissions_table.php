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
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('form_name'); // contact, newsletter, survey, etc.
            $table->json('fields'); // Respuestas del formulario
            $table->enum('status', ['pending', 'processed', 'archived', 'spam'])->default('pending');
            $table->string('source_url')->nullable(); // URL donde se envió el formulario
            $table->string('referrer')->nullable(); // De dónde vino el usuario
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('processing_notes')->nullable();
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Índices para performance
            $table->index(['organization_id', 'form_name']);
            $table->index(['status', 'created_at']);
            $table->index(['form_name', 'status']);
            $table->index(['processed_at']);
            $table->index(['ip_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
