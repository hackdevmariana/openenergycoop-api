<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Crear tabla vendors si no existe
        if (!Schema::hasTable('vendors')) {
            Schema::create('vendors', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('contact_person')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->string('website')->nullable();
                $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
                $table->json('services')->nullable();
                $table->json('certifications')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Crear tabla task_templates si no existe
        if (!Schema::hasTable('task_templates')) {
            Schema::create('task_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->enum('task_type', ['inspection', 'repair', 'replacement', 'calibration', 'cleaning', 'lubrication', 'testing', 'upgrade', 'installation', 'demolition']);
                $table->json('checklist_items')->nullable();
                $table->json('required_tools')->nullable();
                $table->json('required_parts')->nullable();
                $table->decimal('estimated_hours', 8, 2)->nullable();
                $table->decimal('estimated_cost', 15, 2)->nullable();
                $table->text('safety_notes')->nullable();
                $table->text('technical_notes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Crear tabla checklist_templates si no existe
        if (!Schema::hasTable('checklist_templates')) {
            Schema::create('checklist_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->enum('category', ['safety', 'quality', 'operational', 'compliance', 'maintenance'])->default('maintenance');
                $table->json('checklist_items')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        // Eliminar tablas en orden inverso
        Schema::dropIfExists('checklist_templates');
        Schema::dropIfExists('task_templates');
        Schema::dropIfExists('vendors');
    }
};
