<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Розбір пошукового запиту: слова, варіанти написання, екранування.
 *
 * Чиста логіка без бази — щоб її можна було перевірити юніт-тестом і не
 * тягнути в кожну модель. Користується трейт App\Models\Concerns\Searchable.
 */
final class SearchTerms
{
    /** Більше слів у запиті до нічого доброго не ведуть, лише розганяють SQL. */
    private const MAX_TOKENS = 5;

    /** Односимвольні «слова» дають забагато випадкових збігів. */
    private const MIN_TOKEN_LENGTH = 2;

    /** Довший «токен» — це вже не слово, а спроба покласти базу. */
    private const MAX_TOKEN_LENGTH = 40;

    /** qwerty → йцукен: «rhtv» і є «крем», просто забули перемкнути мову. */
    private const LAYOUT = [
        'q' => 'й', 'w' => 'ц', 'e' => 'у', 'r' => 'к', 't' => 'е', 'y' => 'н',
        'u' => 'г', 'i' => 'ш', 'o' => 'щ', 'p' => 'з', '[' => 'х', ']' => 'ї',
        'a' => 'ф', 's' => 'і', 'd' => 'в', 'f' => 'а', 'g' => 'п', 'h' => 'р',
        'j' => 'о', 'k' => 'л', 'l' => 'д', ';' => 'ж', "'" => 'є',
        'z' => 'я', 'x' => 'ч', 'c' => 'с', 'v' => 'м', 'b' => 'и', 'n' => 'т',
        'm' => 'ь', ',' => 'б', '.' => 'ю',
    ];

    /** Латиниця → кирилиця: найчастіший варіант для кожної послідовності. */
    private const DETRANSLIT = [
        'shch' => 'щ', 'zh' => 'ж', 'kh' => 'х', 'ts' => 'ц', 'ch' => 'ч', 'sh' => 'ш',
        'iu' => 'ю', 'ia' => 'я', 'ie' => 'є',
        'a' => 'а', 'b' => 'б', 'v' => 'в', 'h' => 'г', 'g' => 'ґ', 'd' => 'д',
        'e' => 'е', 'z' => 'з', 'y' => 'и', 'i' => 'і', 'j' => 'й', 'k' => 'к',
        'l' => 'л', 'm' => 'м', 'n' => 'н', 'o' => 'о', 'p' => 'п', 'r' => 'р',
        's' => 'с', 't' => 'т', 'u' => 'у', 'f' => 'ф',
    ];

    /** Кирилиця → латиниця за українською офіційною таблицею. */
    private const TRANSLIT = [
        'ж' => 'zh', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch',
        'ю' => 'iu', 'я' => 'ia', 'є' => 'ie', 'ї' => 'i', 'й' => 'i',
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'h', 'ґ' => 'g', 'д' => 'd',
        'е' => 'e', 'з' => 'z', 'и' => 'y', 'і' => 'i', 'к' => 'k', 'л' => 'l',
        'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's',
        'т' => 't', 'у' => 'u', 'ф' => 'f', 'ь' => '', "'" => '',
    ];

    /**
     * Слова запиту: у нижньому регістрі, без зайвих пробілів і дрібниці.
     *
     * @return list<string>
     */
    public static function tokens(?string $raw): array
    {
        $normalized = mb_strtolower(trim((string) $raw));

        if ($normalized === '') {
            return [];
        }

        $tokens = preg_split('/\s+/u', $normalized) ?: [];

        $tokens = array_values(array_filter(
            array_map(
                static fn (string $token): string => mb_substr($token, 0, self::MAX_TOKEN_LENGTH),
                $tokens,
            ),
            static fn (string $token): bool => mb_strlen($token) >= self::MIN_TOKEN_LENGTH,
        ));

        // Запит на кшталт «%» або однієї літери не дає жодного токена. Якщо
        // просто повернути порожній масив, пошук «вимкнеться» і вітрина
        // покаже весь каталог під заголовком «Результати: «%»». Чесніше
        // шукати рядок як є — тоді «%» не знайде нічого.
        if ($tokens === []) {
            return [mb_substr($normalized, 0, self::MAX_TOKEN_LENGTH)];
        }

        return \array_slice($tokens, 0, self::MAX_TOKENS);
    }

    /**
     * Варіанти написання слова: як ввели, іншою розкладкою і транслітерацією
     * в обидва боки. Усі — у нижньому регістрі й без дублікатів.
     *
     * @return list<string>
     */
    public static function variants(string $token): array
    {
        $token = mb_strtolower($token);

        // Сам токен лишається завжди, навіть якщо він коротший за поріг:
        // інакше для запиту «%» варіантів не лишалося б зовсім, умова
        // пошуку виходила порожньою — і LIKE-шаблон «зникав» разом із нею,
        // повертаючи весь каталог.
        $derived = array_filter(
            [
                self::fromLatinLayout($token),
                self::toLatinLayout($token),
                self::transliterate($token),
                self::detransliterate($token),
            ],
            static fn (string $value): bool => mb_strlen($value) >= self::MIN_TOKEN_LENGTH,
        );

        return array_values(array_unique([$token, ...$derived]));
    }

    /**
     * Символ екранування для LIKE. Саме «!», а не звичний бекслеш: у MySQL
     * бекслеш усередині рядкового літерала сам є екрануванням, тож там
     * потрібно ESCAPE '\\', а SQLite на такий літерал відповідає
     * «ESCAPE expression must be a single character». «!» в обох базах
     * звичайний символ, і SQL виходить однаковий.
     */
    public const LIKE_ESCAPE = '!';

    /** Екранує символи, які LIKE вважає шаблоном: інакше «%» шукає геть усе. */
    public static function escapeLike(string $value): string
    {
        return str_replace(
            [self::LIKE_ESCAPE, '%', '_'],
            [self::LIKE_ESCAPE.self::LIKE_ESCAPE, self::LIKE_ESCAPE.'%', self::LIKE_ESCAPE.'_'],
            $value,
        );
    }

    /** «rhtv» → «крем». */
    private static function fromLatinLayout(string $token): string
    {
        return strtr($token, self::LAYOUT);
    }

    /** «крем» → «rhtv»: той самий словник навпаки. */
    private static function toLatinLayout(string $token): string
    {
        return strtr($token, array_flip(self::LAYOUT));
    }

    /** «крем» → «krem». */
    private static function transliterate(string $token): string
    {
        return strtr($token, self::TRANSLIT);
    }

    /**
     * «krem» → «крем». Таблиця саме окрема, а не перевернута TRANSLIT:
     * зворотне відображення неоднозначне (i — це і «і», і «ї», і «й»), і
     * автоматичний переворот давав «щїтка» замість «щітка». Тут обрано
     * найчастіший варіант для кожної послідовності.
     *
     * strtr сам пробує найдовші ключі першими, тож «shch» не розпадеться
     * на «sh» + «ch».
     */
    private static function detransliterate(string $token): string
    {
        return strtr($token, self::DETRANSLIT);
    }
}
