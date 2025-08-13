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
            $table->unsignedBigInteger('customer_profile_id'); // Sin foreign key
            $table->unsignedBigInteger('organization_id'); // Sin foreign key
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

            // Índices con nombres más cortos
            $table->index(['customer_profile_id', 'organization_id'], 'cpci_profile_org_idx');
            $table->index('province', 'cpci_province_idx');
            $table->index('valid_from', 'cpci_valid_from_idx');
            $table->index('valid_to', 'cpci_valid_to_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_profile_contact_infos');
    }
};
