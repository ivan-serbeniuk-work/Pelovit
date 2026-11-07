<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Картка засновника на сторінці «Про нас». У коді вона була заглушкою
 * з макета — «Ім'я Прізвище», — і ця заглушка висіла на живому сайті.
 *
 * Запис один, але тримаємо таблицею, а не ключами в налаштуваннях:
 * потрібне завантаження фото, а форма налаштувань — рукописний HTML
 * із простими input, файли вона не приймає.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_founder', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role')->nullable();
            $table->text('quote')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_founder');
    }
};
