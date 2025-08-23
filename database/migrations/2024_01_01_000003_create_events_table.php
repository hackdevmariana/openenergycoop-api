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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->dateTime('date');
            $table->string('location');
            $table->boolean('public')->default(true);
            $table->string('language', 10)->default('es');
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->boolean('is_draft')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Índices para mejorar el rendimiento
            $table->index(['date', 'public']);
            $table->index(['organization_id', 'date']);
            $table->index(['language', 'public']);
            $table->index(['is_draft', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
