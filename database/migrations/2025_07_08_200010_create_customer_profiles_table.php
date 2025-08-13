<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Sin foreign key
            $table->unsignedBigInteger('organization_id'); // Sin foreign key
            $table->enum('profile_type', ['individual', 'tenant', 'company', 'ownership_change']);
            $table->enum('legal_id_type', ['dni', 'nie', 'passport', 'cif']);
            $table->string('legal_id_number');
            $table->string('legal_name');
            $table->enum('contract_type', ['own', 'tenant', 'company', 'ownership_change']);
            $table->timestamps();

            // Índices con nombres más cortos
            $table->index(['user_id', 'organization_id'], 'cp_user_org_idx');
            $table->index('profile_type', 'cp_profile_type_idx');
            $table->index('legal_id_type', 'cp_legal_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_profiles');
    }
};
