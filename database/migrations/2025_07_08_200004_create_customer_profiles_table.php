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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->enum('profile_type', ['individual', 'tenant', 'company', 'ownership_change']);
            $table->enum('legal_id_type', ['dni', 'nie', 'passport', 'cif']);
            $table->string('legal_id_number');
            $table->string('legal_name');
            $table->enum('contract_type', ['own', 'tenant', 'company', 'ownership_change']);
            $table->timestamps();

            // Índices
            $table->index(['user_id', 'organization_id']);
            $table->index('profile_type');
            $table->index('legal_id_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_profiles');
    }
};
