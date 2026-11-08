<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Блок «Слідкуйте за нами» на сторінці «Про нас». Досі це були чотири
 * захардкожені в about.vue картинки з каталогу, без підписів і посилань —
 * заголовок кликав стежити, а стежити не було за чим. Тепер плитки
 * редагуються в адмінці: фото, посилання на конкретний пост, порядок.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_social_shots', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            // Посилання на допис. Порожнє — плитка веде на профіль
            // із налаштувань (instagram_url), щоб клік не губився.
            $table->string('url')->nullable();
            // Підпис для alt: картинки без нього німі для читалок і пошуку.
            $table->string('alt')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_social_shots');
    }
};
