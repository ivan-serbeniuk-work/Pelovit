<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Вкладки блоку «Дослідження» на сторінці «Про нас». У макеті зліва
 * чотири пункти (Винахідник, Pelovit, Дослідження, Виробництво), але
 * в коді вони були посиланнями на «#»: клік нічого не робив, а праворуч
 * завжди висів один і той самий текст із фото.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('text')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_sections');
    }
};
