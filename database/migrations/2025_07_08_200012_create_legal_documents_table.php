<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_profile_id'); // Sin foreign key
            $table->unsignedBigInteger('organization_id'); // Sin foreign key
            $table->enum('type', ['dni', 'iban_receipt', 'contract', 'invoice', 'other']);
            $table->datetime('uploaded_at');
            $table->datetime('verified_at')->nullable();
            $table->unsignedBigInteger('verifier_user_id')->nullable(); // Sin foreign key
            $table->timestamps();

            // Índices
            $table->index(['customer_profile_id', 'organization_id'], 'ld_profile_org_idx');
            $table->index('type', 'ld_type_idx');
            $table->index('verified_at', 'ld_verified_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_documents');
    }
};
