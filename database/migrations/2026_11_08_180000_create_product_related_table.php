<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ручний підбір супутніх товарів для блока «З цим товаром часто купують».
 *
 * Досі блок показував перші чотири активні товари тієї самої категорії
 * за id — тобто найстаріші, без жодного стосунку до «часто купують».
 * Тепер менеджер може задати добірку явно, а категорія лишається
 * запасним варіантом для товарів, яким нічого не вибрали.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_related', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('related_product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'related_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_related');
    }
};
