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
            $table->foreignId('customer_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['dni', 'iban_receipt', 'contract', 'invoice', 'other']);
            $table->datetime('uploaded_at');
            $table->datetime('verified_at')->nullable();
            $table->foreignId('verifier_user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();

            // Índices
            $table->index(['customer_profile_id', 'organization_id']);
            $table->index('type');
            $table->index('verified_at');
            $table->index('verifier_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_documents');
    }
};
