<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Відео перестає бути тільки YouTube-посиланням: ролики можна заливати
 * файлом. У товару їх кілька (стоять у галереї поряд з фото), у статті —
 * одне головне, плюс довільна кількість вставок у тілі тексту.
 *
 * video_url у товарі лишається як було — це посилання на YouTube.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Масив об'єктів {file, poster, title}: постер необов'язковий,
            // без нього браузер показує перший кадр (preload="metadata").
            $table->json('videos')->nullable()->after('video_url');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->string('video_url')->nullable()->after('image');
            $table->string('video_file')->nullable()->after('video_url');
            $table->string('video_poster')->nullable()->after('video_file');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('videos');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['video_url', 'video_file', 'video_poster']);
        });
    }
};
