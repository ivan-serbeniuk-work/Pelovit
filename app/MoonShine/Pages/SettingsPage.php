<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\Setting;
use App\Support\CalculatorExplainer;
use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\FlexibleRender;
use MoonShine\UI\Components\Layout\Box;

class SettingsPage extends Page
{
    public function getBreadcrumbs(): array
    {
        return ['#' => $this->getTitle()];
    }

    public function getTitle(): string
    {
        return 'Налаштування';
    }

    protected function components(): iterable
    {
        $s = Setting::allKeyed();
        $action = route('admin.settings.save');
        $csrf = csrf_token();
        $success = session('success') ? "<div class='ms-alert ms-alert-success' style='margin-bottom:16px;padding:10px 16px;background:#d1fae5;border-radius:8px;color:#065f46;font-size:13px'>".session('success').'</div>' : '';

        $field = fn (string $label, string $key, string $type = 'text', string $hint = '') => "<div style='margin-bottom:16px'>
                <label style='display:block;font-size:12px;font-weight:600;color:var(--color-base-text);opacity:.7;margin-bottom:6px'>{$label}</label>
                <input type='{$type}' name='{$key}' value='".htmlspecialchars($s[$key] ?? '')."'
                    style='width:100%;padding:8px 12px;border:1px solid var(--color-base-stroke);border-radius:8px;background:var(--color-body);color:var(--color-base-text);font-size:13px;outline:none'
                    class='ms-settings-input'>
                ".($hint ? "<span style='font-size:11px;color:var(--color-base-text);opacity:.45;margin-top:4px;display:block'>{$hint}</span>" : '').'
            </div>';

        $contactsHtml = $field('Телефон 1', 'phone').
            $field('Телефон 2', 'phone_2').
            $field('Телефон 3', 'phone_3').
            $field('Email', 'email', 'email');

        $socialHtml = $field('Instagram', 'instagram_url', 'url').
            $field('Facebook', 'facebook_url', 'url').
            $field('Telegram', 'telegram_url', 'url').
            $field('Viber', 'viber_url', 'url').
            $field('YouTube', 'youtube_url', 'url');

        $appHtml = $field('App Store (iOS)', 'app_store_url', 'url', 'Порожньо — у футері показується «Незабаром в App Store».').
            $field('Google Play (Android)', 'google_play_url', 'url', 'Порожньо — у футері показується «Незабаром в Google Play».');

        $shopHtml = $field('Мінімальна сума для безкоштовної доставки (₴)', 'min_free_shipping', 'number', 'Залиште порожнім щоб вимкнути').
            $field('Текст банера / оголошення', 'banner_text', 'text', 'Відображається у шапці сайту. Порожньо — банер прихований.');

        $calcHtml = $field('Мінімальна сума партії (₴)', 'calc_min_batch_total', 'number', 'Калькулятор попередить, якщо партія виходить меншою, і підкаже потрібний тираж.').
            $field('Термін виготовлення (днів)', 'calc_production_days', 'number', 'Показується в результаті розрахунку.').
            $field('Розкид ціни, ±%', 'calc_spread_percent', 'number', 'Ціна показується вилкою: 12 означає ±12% від розрахунку.');

        $paymentHtml = $field('Номер картки для оплати', 'payment_card_number', 'text', 'Показується клієнту при виборі "Оплата на карту" — на сторінці замовлення, сторінці успіху та в листі-підтвердженні.').
            $field('Одержувач (ПІБ)', 'payment_card_holder', 'text', 'Необовʼязково. Показується поруч із номером картки.');

        $textarea = fn (string $label, string $key, string $hint = '') => "<div style='margin-bottom:16px'>
                <label style='display:block;font-size:12px;font-weight:600;color:var(--color-base-text);opacity:.7;margin-bottom:6px'>{$label}</label>
                <textarea name='{$key}' rows='3'
                    style='width:100%;padding:8px 12px;border:1px solid var(--color-base-stroke);border-radius:8px;background:var(--color-body);color:var(--color-base-text);font-size:13px;outline:none;resize:vertical'>".htmlspecialchars($s[$key] ?? '').'</textarea>
                '.($hint ? "<span style='font-size:11px;color:var(--color-base-text);opacity:.45;margin-top:4px;display:block'>{$hint}</span>" : '').'
            </div>';

        // Тексти сторінки «Про нас». Повторювані блоки (показники, цінності,
        // історія) живуть у власних розділах — тут лише вільний текст.
        $aboutHtml =
            $textarea('Вступний абзац', 'about_intro', 'Перший абзац під заголовком «Про нас».').
            $field('Заголовок блоку з нагородами', 'about_awards_title').
            $field('Заголовок блоку про сертифікацію', 'about_cert_title');

        $globalSeoHtml =
            $field('Google Analytics ID', 'google_analytics_id', 'text', 'Формат: G-XXXXXXXXXX або UA-XXXXXXXX-X').
            $field('Дефолтне OG Image (URL)', 'default_og_image', 'url', 'Зображення для сторінок без власного OG Image. Повна URL-адреса.').
            $textarea('Дефолтний опис сайту', 'site_description', 'Використовується як fallback description для сторінок без власного опису.');

        // Список сторінок — у Setting::SEO_PAGES, разом з білим списком ключів
        // у routes/web.php: два окремі списки вже одного разу розійшлися.
        $pageLabels = Setting::SEO_PAGES;

        $pageSeoHtml = '';
        foreach ($pageLabels as $pageKey => $pageLabel) {
            $pageSeoHtml .= "<details style='margin-bottom:16px;border:1px solid var(--color-base-stroke);border-radius:8px;overflow:hidden'>
                <summary style='padding:12px 16px;font-size:13px;font-weight:600;cursor:pointer;background:var(--color-body2,var(--color-body));list-style:none;display:flex;align-items:center;gap:8px'>
                    <svg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><path d='M9 18l6-6-6-6'/></svg>
                    {$pageLabel}
                </summary>
                <div style='padding:16px'>
                    {$field('SEO Title', "seo_{$pageKey}_title", 'text', '50–60 символів. Якщо порожньо — береться хардкодний заголовок.')}
                    {$textarea('SEO Description', "seo_{$pageKey}_description", '120–160 символів.')}
                    {$field('OG Image (URL)', "seo_{$pageKey}_og_image", 'url', 'Повна URL-адреса зображення для соцмереж. Залиште порожнім — буде дефолтне.')}
                </div>
            </details>";
        }

        // Три поля нижче впливають на результат разом зі ставками з інших
        // розділів, тому поруч лежить та сама пояснювалка, що й там.
        $calcExplainer = app(CalculatorExplainer::class)->render();

        $formHtml = "
        {$success}
        <form method='POST' action='{$action}'>
            <input type='hidden' name='_token' value='{$csrf}'>
            <div style='display:grid;grid-template-columns:1fr 1fr;gap:24px'>
                <div>
                    <div style='font-size:12px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--color-base-text);opacity:.45;margin-bottom:16px'>Контакти</div>
                    {$contactsHtml}
                </div>
                <div>
                    <div style='font-size:12px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--color-base-text);opacity:.45;margin-bottom:16px'>Соціальні мережі</div>
                    {$socialHtml}
                </div>
            </div>
            <div style='margin-top:8px'>
                <div style='font-size:12px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--color-base-text);opacity:.45;margin-bottom:16px'>Мобільний застосунок</div>
                {$appHtml}
            </div>
            <div style='margin-top:8px'>
                <div style='font-size:12px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--color-base-text);opacity:.45;margin-bottom:16px'>Магазин</div>
                {$shopHtml}
            </div>
            <div style='margin-top:8px'>
                <div style='font-size:12px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--color-base-text);opacity:.45;margin-bottom:16px'>Оплата</div>
                {$paymentHtml}
            </div>
            <div style='margin-top:8px'>
                <div style='font-size:12px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--color-base-text);opacity:.45;margin-bottom:16px'>Калькулятор контрактного виробництва</div>
                <p style='font-size:12px;color:var(--color-base-text);opacity:.55;margin-bottom:16px'>Ставки за продукти, тару та знижки за тираж редагуються в розділах «Калькулятор: опції» та «Калькулятор: знижки за тираж».</p>
                <details style='margin-bottom:16px;border:1px solid var(--color-base-stroke);border-radius:8px;overflow:hidden'>
                    <summary style='padding:12px 16px;font-size:13px;font-weight:600;cursor:pointer;background:var(--color-body2,var(--color-body));list-style:none'>Як це вважається — формула і приклад</summary>
                    <div style='padding:16px'>{$calcExplainer}</div>
                </details>
                {$calcHtml}
            </div>
            <div style='margin-top:8px'>
                <div style='font-size:12px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--color-base-text);opacity:.45;margin-bottom:16px'>Сторінка «Про нас»</div>
                <p style='font-size:12px;color:var(--color-base-text);opacity:.55;margin-bottom:16px'>Показники, цінності та віхи історії редагуються в розділах «Сторінка «Про нас»». Блок «Дослідження» редагується в розділі «Вкладки». Порожнє поле — на сайті показується текст за замовчуванням.</p>
                {$aboutHtml}
            </div>
            <div style='margin-top:8px'>
                <div style='font-size:12px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--color-base-text);opacity:.45;margin-bottom:16px'>Глобальне SEO</div>
                {$globalSeoHtml}
            </div>
            <div style='margin-top:8px'>
                <div style='font-size:12px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--color-base-text);opacity:.45;margin-bottom:16px'>SEO сторінок</div>
                <p style='font-size:12px;color:var(--color-base-text);opacity:.55;margin-bottom:16px'>Натисніть на сторінку щоб розкрити SEO-поля. Порожні поля — використовується хардкодний текст із сайту.</p>
                {$pageSeoHtml}
            </div>
            <button type='submit' style='padding:10px 28px;background:#422928;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer'>Зберегти</button>
        </form>";

        return [
            Box::make([FlexibleRender::make($formHtml)]),
        ];
    }
}
