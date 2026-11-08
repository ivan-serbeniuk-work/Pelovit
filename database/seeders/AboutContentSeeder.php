<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AboutFounder;
use App\Models\AboutMilestone;
use App\Models\AboutSection;
use App\Models\AboutSocialShot;
use App\Models\AboutStat;
use App\Models\AboutValue;
use Illuminate\Database\Seeder;

/**
 * Контент сторінки «Про нас», який раніше був захардкожений в about.vue.
 * Ключі updateOrCreate — природні (підпис, назва, рік), тож повторний
 * запуск не дублює записи й не затирає ручні правки порядку.
 */
class AboutContentSeeder extends Seeder
{
    public function run(): void
    {
        $stats = [
            ['value' => '50', 'label' => 'Працівників'],
            ['value' => '1000', 'label' => 'Одиниць виробляємо в день'],
            ['value' => '88', 'label' => 'Сертифікованих засобів'],
            ['value' => '40+', 'label' => 'Років досвіду'],
        ];

        foreach ($stats as $i => $item) {
            AboutStat::updateOrCreate(
                ['label' => $item['label']],
                [...$item, 'sort_order' => $i + 1, 'is_active' => true],
            );
        }

        // Картка засновника. Ім'я в макеті — заглушка «Ім'я Прізвище»,
        // тож поле лишається порожнім (заголовок тоді не виводиться),
        // а сама картка увімкнена: блок зліва від цінностей має бути.
        AboutFounder::updateOrCreate(
            ['id' => 1],
            [
                'name' => '',
                'role' => 'засновник компанії',
                'quote' => 'Коли ми починали роботу над Pelovit, головною ідеєю було поєднати унікальні властивості української природи з сучасними науковими розробками. Ми хотіли створювати продукти, яким можна довіряти щодня: безпечні, ефективні та доступні.',
                'photo' => 'images/skin_portrait.png',
                'is_active' => true,
            ],
        );

        // Вкладки блоку «Дослідження» — усі чотири, як у макеті. Тексти
        // зібрані з фактів, що вже є на сайті (історія ОРАН, пелоїди
        // Куяльника, виробництво з 1985 року). Для «Винахідника» бракує
        // імені, фото й біографії — їх має дати клієнт.
        $sections = [
            [
                'title' => 'Винахідник',
                'text' => 'Формулу PELOVIT розробили науковці Одеської регіональної академії наук. Завдання було непростим: зберегти лікувальну дію пелоїду Куяльницького лиману поза бальнеологічною процедурою, у засобі для щоденного домашнього застосування. Результат — запатентовані рецептури, за якими підприємство працює й сьогодні.',
                'is_active' => true,
            ],
            [
                'title' => 'Pelovit',
                'text' => 'PELOVIT — лінійка засобів на основі екстракту лікувальних пелоїдів Куяльницького лиману. Формула передає дію грязьової процедури без самої грязі: мінерали та біологічно активні речовини пелоїду в зручних щоденних формах — гелях, бальзамах, розчинах для полоскання. Рецептури запатентовані, а кожен засіб проходить сертифікацію — сьогодні їх уже 88.',
                'image' => 'images/page_pelovit.jpg',
                'is_active' => true,
            ],
            [
                'title' => 'Дослідження',
                'text' => 'Одеська регіональна академія наук (ОРАН) — одна з найстаріших організацій України, що займається біологією і медициною. Після аварії на Чорнобильській АЕС академія отримала держзамовлення на створення нового препарату з сильним лікувальним і відновлювальним ефектом.',
                'image' => 'images/science_in_laboratory.png',
                'is_active' => true,
            ],
            [
                'title' => 'Виробництво',
                'text' => 'Власне виробництво в Одесі працює з 1985 року — від підготовки пелоїдної сировини до розливу й пакування готового засобу. Щодня випускаємо близько 1000 одиниць продукції, у штаті 50 працівників. Підприємство має європейську сертифікацію GMP ISO 22716, тому поруч із власними лініями виготовляє косметику під брендами партнерів.',
                'image' => 'images/pexel_arnempodrez.jpg',
                'is_active' => true,
            ],
        ];

        foreach ($sections as $i => $item) {
            AboutSection::updateOrCreate(
                ['title' => $item['title']],
                [...$item, 'sort_order' => $i + 1],
            );
        }

        $values = [
            [
                'title' => 'Інноваційність',
                'text' => 'Поєднуємо науку та природу для створення дієвих рішень.',
                'icon' => <<<'SVG'
<svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M26.6654 13.3333L13.332 19.9999V36.6666L26.6654 43.3333L39.9987 36.6666V19.9999L26.6654 13.3333Z" stroke="#5E3F3E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M40 36.6666L53.3333 43.3333L66.6667 36.6666V19.9999L53.3333 13.3333L40 19.9999" stroke="#5E3F3E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M26.668 43.3333V59.9999L40.0013 66.6666L53.3346 59.9999V43.3333" stroke="#5E3F3E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
SVG,
            ],
            [
                'title' => 'Якість та надійність',
                'text' => 'Кожен продукт проходить контроль і випробування.',
                'icon' => <<<'SVG'
<svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M40 50C40 52.6522 41.0536 55.1957 42.9289 57.0711C44.8043 58.9464 47.3478 60 50 60C52.6522 60 55.1957 58.9464 57.0711 57.0711C58.9464 55.1957 60 52.6522 60 50C60 47.3478 58.9464 44.8043 57.0711 42.9289C55.1957 41.0536 52.6522 40 50 40C47.3478 40 44.8043 41.0536 42.9289 42.9289C41.0536 44.8043 40 47.3478 40 50Z" stroke="#5E3F3E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M43.332 58.3333V73.3333L49.9987 68.3333L56.6654 73.3333V58.3333" stroke="#5E3F3E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M33.3333 63.3334H16.6667C14.8986 63.3334 13.2029 62.631 11.9526 61.3808C10.7024 60.1306 10 58.4349 10 56.6668V23.3334C10 19.6667 13 16.6667 16.6667 16.6667H63.3333C65.1014 16.6667 66.7971 17.3691 68.0474 18.6194C69.2976 19.8696 70 21.5653 70 23.3334V56.6668C69.9988 57.8358 69.6902 58.9841 69.1051 59.9962C68.5201 61.0084 67.6791 61.8489 66.6667 62.4334" stroke="#5E3F3E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M20 30H60" stroke="#5E3F3E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M20 40H30" stroke="#5E3F3E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M20 50H26.6667" stroke="#5E3F3E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
SVG,
            ],
            [
                'title' => 'Прозорість і чесність',
                'text' => 'Ми відкриті у складі та діях наших засобів.',
                'icon' => <<<'SVG'
<svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M25.0047 64.7434C33.6781 71.7601 46.3214 71.7601 54.9914 64.7434C63.6647 57.7268 65.8647 45.7168 60.2114 36.2568L43.9114 12.0568C42.5114 9.97342 39.6214 9.38008 37.4581 10.7334C36.915 11.0742 36.4495 11.525 36.0914 12.0568L19.7814 36.2568C14.1314 45.7168 16.3314 57.7268 25.0047 64.7434Z" stroke="#5E3F3E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
SVG,
            ],
            [
                'title' => 'Орієнтація на клієнта',
                'text' => 'Створюємо продукти, які дійсно працюють для вас.',
                'icon' => <<<'SVG'
<svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M26.668 23.3333C26.668 26.8696 28.0727 30.2609 30.5732 32.7614C33.0737 35.2619 36.4651 36.6667 40.0013 36.6667C43.5375 36.6667 46.9289 35.2619 49.4294 32.7614C51.9299 30.2609 53.3346 26.8696 53.3346 23.3333C53.3346 19.7971 51.9299 16.4057 49.4294 13.9052C46.9289 11.4048 43.5375 10 40.0013 10C36.4651 10 33.0737 11.4048 30.5732 13.9052C28.0727 16.4057 26.668 19.7971 26.668 23.3333Z" stroke="#5E3F3E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M20 70V63.3333C20 59.7971 21.4048 56.4057 23.9052 53.9052C26.4057 51.4048 29.7971 50 33.3333 50H46.6667C50.2029 50 53.5943 51.4048 56.0948 53.9052C58.5952 56.4057 60 59.7971 60 63.3333V70" stroke="#5E3F3E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
SVG,
            ],
        ];

        foreach ($values as $i => $item) {
            AboutValue::updateOrCreate(
                ['title' => $item['title']],
                [...$item, 'sort_order' => $i + 1, 'is_active' => true],
            );
        }

        // Плитки «Слідкуйте за нами». Раніше це був захардкожений у
        // about.vue масив із чотирьох картинок каталогу — переносимо їх
        // як є, щоб блок не спорожнів, але вже з alt і без посилань:
        // справжні кадри з інстаграму має дати клієнт.
        $shots = [
            ['image' => 'images/catname4.png', 'alt' => 'Догляд за шкірою Pelovit'],
            ['image' => 'images/face_washing.png', 'alt' => 'Очищення шкіри обличчя'],
            ['image' => 'images/catname5.png', 'alt' => 'Засоби Pelovit'],
            ['image' => 'images/catname3.png', 'alt' => 'Косметика на основі пелоїдів'],
        ];

        foreach ($shots as $i => $item) {
            AboutSocialShot::updateOrCreate(
                ['image' => $item['image']],
                [...$item, 'sort_order' => $i + 1, 'is_active' => true],
            );
        }

        $milestones = [
            ['year' => '2010', 'text' => 'Заснування бренду, початок виробництва спа-косметики.', 'placement' => 'top'],
            ['year' => '2016', 'text' => 'Початок контрактного виробництва для сторонніх брендів.', 'placement' => 'top'],
            ['year' => '2022', 'text' => 'Запуск нових антивікових та універсальних косметичних ліній.', 'placement' => 'top'],
            ['year' => '2013', 'text' => 'Впровадження лікувальних комплексів для застосування вдома.', 'placement' => 'bottom'],
            ['year' => '2019', 'text' => 'Відкриття мережі дистрибуції по Україні та онлайн-продажів.', 'placement' => 'bottom'],
        ];

        foreach ($milestones as $i => $item) {
            AboutMilestone::updateOrCreate(
                ['year' => $item['year']],
                [...$item, 'image' => 'images/handwash3.jpg', 'sort_order' => $i + 1, 'is_active' => true],
            );
        }
    }
}
