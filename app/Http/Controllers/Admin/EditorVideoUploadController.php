<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\MoonShine\Concerns\ValidatesUploadedVideos;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Завантаження ролика з редактора статті.
 *
 * Плагін media в TinyMCE сам файли не вантажить — він лише просить URL.
 * Цей маршрут приймає файл і повертає {"location": "/videos/<ім'я>"},
 * саме такої відповіді чекає file_picker_callback (див. callbacks у
 * config/moonshine_tinymce.php).
 *
 * Правила ті самі, що й для поля «Відеофайл» у формі: інакше через
 * редактор можна було б залити те, що форма не пропускає.
 */
class EditorVideoUploadController extends Controller
{
    use ValidatesUploadedVideos;

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file' => [...$this->videoRules(), 'required'],
        ]);

        $path = $data['file']->store('videos', 'public_root');

        return response()->json(['location' => '/'.ltrim((string) $path, '/')]);
    }
}
