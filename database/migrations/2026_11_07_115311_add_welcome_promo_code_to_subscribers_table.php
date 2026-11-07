<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Запам'ятовуємо виданий вітальний промокод прямо на підписнику: так
 * повторна підписка тією ж поштою не штампує новий код, і видно, кому
 * який код пішов.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->string('welcome_promo_code')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropColumn('welcome_promo_code');
        });
    }
};
