<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Товар, який показується карткою в слайді героя.
 *
 * Досі картка була одна на всю карусель і вибиралася кодом: перший
 * «обраний» товар, а як таких немає — просто перший активний. Тепер
 * картка живе всередині слайда, тож і товар логічно задавати на слайд.
 * Порожнє поле лишає стару поведінку — автовибір.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('link')
                ->constrained('products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
        });
    }
};
