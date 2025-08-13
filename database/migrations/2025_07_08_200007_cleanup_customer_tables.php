<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Deshabilitar verificación de foreign keys temporalmente
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // Eliminar tablas en orden correcto (hijos primero)
        Schema::dropIfExists('legal_documents');
        Schema::dropIfExists('customer_profile_contact_infos');
        Schema::dropIfExists('customer_profiles');

        // Rehabilitar verificación de foreign keys
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function down(): void
    {
        // En el rollback, recrear las tablas
        // Esto es opcional, pero útil si quieres revertir
        
        // NOTA: No recreamos las tablas aquí porque queremos empezar limpio
        // Si necesitas rollback, puedes comentar todo el método down()
    }
};
