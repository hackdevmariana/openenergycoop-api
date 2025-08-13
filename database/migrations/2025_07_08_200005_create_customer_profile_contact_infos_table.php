<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_profile_contact_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('billing_email')->nullable();
            $table->string('technical_email')->nullable();
            $table->text('address');
            $table->string('postal_code');
            $table->string('city');
            $table->string('province');
            $table->string('iban')->nullable();
            $table->string('cups')->nullable();
            $table->datetime('valid_from');
            $table->datetime('valid_to')->nullable();
            $table->timestamps();

            // Índices
            $table->index(['customer_profile_id', 'organization_id']);
            $table->index('province');
            $table->index('valid_from');
            $table->index('valid_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_profile_contact_infos');
    }
};
