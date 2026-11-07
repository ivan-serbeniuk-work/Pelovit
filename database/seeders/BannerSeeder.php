<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

/**
 * Слайди героя на головній. Раніше вони існували лише в локальній базі —
 * на проді таблиця лишалася порожньою, і герой показував запасний слайд,
 * зашитий в index.vue: одна статична картинка замість каруселі.
 *
 * Тексти взято з макета; редагуються в адмінці (MoonShine → Банери).
 * Картинки вже лежать у public/images і віддаються на проді.
 */
class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'title' => 'Краса починається зі здорової шкіри.',
                'subtitle' => 'Відкрийте для себе догляд, що поєднує ефективність сучасних формул і силу натуральних компонентів.',
                'image' => 'images/gl_face.png',
                'button_text' => 'Дивитися товари',
                'link' => '/catalog',
            ],
            [
                'title' => 'Сила мінералів Куяльницького лиману',
                'subtitle' => 'Лікувальна грязь і ропа — у засобах для обличчя, тіла та загального оздоровлення.',
                'image' => 'images/science_in_laboratory.png',
                'button_text' => 'Переглянути лінійку',
                'link' => '/catalog',
            ],
            [
                'title' => 'Контрактне виробництво під ваш бренд',
                'subtitle' => 'Повний цикл — від розробки формули до готової продукції.',
                'image' => 'images/cosmetology2.png',
                'button_text' => 'Дізнатися більше',
                'link' => '/kontractne-vyrobnyctvo',
            ],
        ];

        // Ключ — заголовок: повторний запуск не дублює слайди й не затирає
        // порядок, якщо його змінили в адмінці.
        foreach ($banners as $i => $item) {
            Banner::updateOrCreate(
                ['title' => $item['title']],
                [...$item, 'sort_order' => $i + 1, 'is_active' => true],
            );
        }
    }
}
