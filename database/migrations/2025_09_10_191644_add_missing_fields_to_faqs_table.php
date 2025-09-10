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
        Schema::table('faqs', function (Blueprint $table) {
            // Agregar campo slug después de question (sin unique primero)
            $table->string('slug')->nullable()->after('question');
            
            // Agregar campo short_answer después de answer
            $table->text('short_answer')->nullable()->after('answer');
            
            // Agregar campo keywords después de tags
            $table->json('keywords')->nullable()->after('tags');
            
            // Agregar campo view_count (alias para views_count)
            $table->integer('view_count')->default(0)->after('views_count');
        });

        // Generar slugs para registros existentes
        $faqs = \App\Models\Faq::whereNull('slug')->get();
        foreach ($faqs as $faq) {
            $baseSlug = \Illuminate\Support\Str::slug($faq->question);
            $slug = $baseSlug;
            $counter = 1;
            
            // Asegurar que el slug sea único
            while (\App\Models\Faq::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $faq->update(['slug' => $slug]);
        }

        // Ahora agregar la restricción unique
        Schema::table('faqs', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            // Primero eliminar la restricción unique
            $table->dropUnique(['slug']);
            // Luego eliminar las columnas
            $table->dropColumn(['slug', 'short_answer', 'keywords', 'view_count']);
        });
    }
};
