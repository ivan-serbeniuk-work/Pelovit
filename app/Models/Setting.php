<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['key', 'value'];

    /**
     * Сторінки, у яких SEO правиться з адмінки: ключ → підпис у формі.
     * Ключ мусить збігатися з pageKey у composable useSeoPage на вітрині,
     * інакше поле в адмінці нікуди не підключене.
     *
     * Єдине джерело для форми налаштувань і для білого списку ключів у
     * routes/web.php: раніше списки жили окремо й розійшлися — /promotions
     * і /obmin-ta-povernennya мали ключі в коді вітрини, але не мали полів
     * в адмінці, і правилися лише деплоєм.
     */
    public const SEO_PAGES = [
        'home' => 'Головна (/)',
        'catalog' => 'Каталог (/catalog)',
        'journal' => 'Меджурнал (/journal)',
        'promotions' => 'Акції (/promotions)',
        'contacts' => 'Контакти (/contacts)',
        'about' => 'Про нас (/about)',
        'opt' => 'Опт (/opt)',
        'masters' => 'Майстрам (/masters)',
        'contract' => 'Контрактне виробництво (/kontractne-vyrobnyctvo)',
        'returns' => 'Обмін та повернення (/obmin-ta-povernennya)',
    ];

    /**
     * Ключі налаштувань SEO всіх сторінок: seo_{page}_{title|description|og_image}.
     *
     * @return list<string>
     */
    public static function seoPageKeys(): array
    {
        $keys = [];

        foreach (array_keys(self::SEO_PAGES) as $page) {
            foreach (['title', 'description', 'og_image'] as $field) {
                $keys[] = "seo_{$page}_{$field}";
            }
        }

        return $keys;
    }

    /**
     * Кеш вічний, тому його треба скидати на будь-якій зміні моделі, а не лише
     * в set(): правка чи видалення запису повз set() (адмінка, tinker, сідер)
     * інакше назавжди лишає сайт зі старим значенням.
     */
    protected static function booted(): void
    {
        static::saved(static fn (self $setting) => static::flushCache($setting->key));
        static::deleted(static fn (self $setting) => static::flushCache($setting->key));
    }

    public static function flushCache(?string $key = null): void
    {
        if ($key !== null) {
            Cache::forget("setting_{$key}");
        }

        Cache::forget('settings_all');
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting_{$key}", function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        // Кеш скидає хук saved() у booted().
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function allKeyed(): array
    {
        return Cache::rememberForever('settings_all', function () {
            return static::pluck('value', 'key')->toArray();
        });
    }
}
