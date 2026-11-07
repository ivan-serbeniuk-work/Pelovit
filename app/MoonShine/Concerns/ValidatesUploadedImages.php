<?php

declare(strict_types=1);

namespace App\MoonShine\Concerns;

/**
 * Спільні правила для полів-картинок у формах адмінки.
 *
 * Без них завелике фото не давало зрозумілої відповіді: файл долітав
 * побитим і падав із 500 десь у надрах збереження. Тепер це звичайна
 * помилка валідації під полем.
 *
 * 20 МБ — те саме число, що в nginx (client_max_body_size) і в php.ini
 * контейнера, щоб ланцюг не мав вужчої ланки.
 */
trait ValidatesUploadedImages
{
    /**
     * @return list<string>
     */
    protected function imageRules(): array
    {
        return ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:20480'];
    }
}
