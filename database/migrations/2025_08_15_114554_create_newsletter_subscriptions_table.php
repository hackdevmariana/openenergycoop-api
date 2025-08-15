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
        Schema::create('newsletter_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email');
            $table->enum('status', ['pending', 'confirmed', 'unsubscribed', 'bounced', 'complained'])->default('pending');
            $table->string('subscription_source')->nullable(); // website, api, import, etc.
            $table->json('preferences')->nullable(); // Tipos de newsletter, frecuencia, etc.
            $table->json('tags')->nullable(); // Etiquetas para segmentación
            $table->string('language', 5)->default('es');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->string('confirmation_token')->nullable();
            $table->string('unsubscribe_token')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->integer('emails_sent')->default(0);
            $table->integer('emails_opened')->default(0);
            $table->integer('links_clicked')->default(0);
            $table->timestamp('last_email_sent_at')->nullable();
            $table->timestamp('last_email_opened_at')->nullable();
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Índices para performance
            $table->index(['organization_id', 'status']);
            $table->index(['email', 'organization_id']);
            $table->index(['status', 'language']);
            $table->index(['confirmation_token']);
            $table->index(['unsubscribe_token']);
            
            // Constraint único para email por organización
            $table->unique(['email', 'organization_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscriptions');
    }
};
