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
