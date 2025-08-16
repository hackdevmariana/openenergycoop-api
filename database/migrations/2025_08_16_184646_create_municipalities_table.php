<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('municipalities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('text')->nullable(); // Información sobre la operación en este municipio
            $table->foreignId('province_id')->constrained('provinces')->onDelete('cascade');
            $table->timestamps();

            // Índices
            $table->index('slug');
            $table->index('name');
            $table->index(['province_id', 'name']);
            
            // Un municipio puede tener el mismo nombre en diferentes provincias
            $table->unique(['name', 'province_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('municipalities');
    }
};