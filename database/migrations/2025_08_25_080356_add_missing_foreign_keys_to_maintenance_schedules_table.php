<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_schedules', function (Blueprint $table) {
            // Añadir clave foránea para vendor_id -> vendors.id
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('restrict');
            
            // Añadir clave foránea para task_template_id -> task_templates.id
            $table->foreign('task_template_id')->references('id')->on('task_templates')->onDelete('restrict');
            
            // Añadir clave foránea para checklist_template_id -> checklist_templates.id
            $table->foreign('checklist_template_id')->references('id')->on('checklist_templates')->onDelete('restrict');
            
            // Añadir clave foránea para created_by -> users.id
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            
            // Añadir clave foránea para approved_by -> users.id
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_schedules', function (Blueprint $table) {
            // Eliminar claves foráneas en orden inverso
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['checklist_template_id']);
            $table->dropForeign(['task_template_id']);
            $table->dropForeign(['vendor_id']);
        });
    }
};
