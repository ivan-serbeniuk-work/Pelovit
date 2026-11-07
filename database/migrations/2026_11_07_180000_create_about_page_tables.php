<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Контент сторінки «Про нас» переїжджає з коду в базу: цифри, цінності
 * й віхи історії раніше були захардкожені в about.vue, тож рік чи
 * показник можна було змінити лише правкою коду й деплоєм.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Показники в шапці: 50 працівників, 1000 одиниць на день тощо.
        // value — рядок, а не число: у макеті є «40+».
        Schema::create('about_stats', function (Blueprint $table) {
            $table->id();
            $table->string('value', 16);
            $table->string('label');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('about_values', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('text');
            // Інлайновий SVG: іконки намальовані під макет, набору готових немає.
            $table->text('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // placement — у макеті віхи йдуть двома рядами: у верхньому фото
        // зверху, у нижньому знизу.
        Schema::create('about_milestones', function (Blueprint $table) {
            $table->id();
            $table->string('year', 8);
            $table->text('text');
            $table->string('image')->nullable();
            $table->enum('placement', ['top', 'bottom'])->default('top');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_milestones');
        Schema::dropIfExists('about_values');
        Schema::dropIfExists('about_stats');
    }
};
