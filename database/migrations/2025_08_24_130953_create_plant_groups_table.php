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
        Schema::create('plant_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('name'); // ej. "Mi viña solar"
            $table->foreignId('plant_id')->constrained('plants')->onDelete('cascade');
            $table->integer('number_of_plants')->default(0);
            $table->decimal('co2_avoided_total', 10, 4)->default(0); // kg de CO2 evitado total
            $table->string('custom_label')->nullable(); // opcional, para mostrar "Mi huerta solar", "Pinar cooperativo"...
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('user_id');
            $table->index('plant_id');
            $table->index('is_active');
            $table->index('co2_avoided_total');
            $table->index(['user_id', 'plant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plant_groups');
    }
};
