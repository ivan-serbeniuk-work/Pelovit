<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Support\SearchTerms;
use Illuminate\Database\Eloquent\Builder;

/**
 * Пошук по текстових колонках моделі.
 *
 * Модель перелічує колонки у порядку важливості:
 *
 *     protected static array $searchColumns = ['name', 'brand', 'description'];
 *
 * Збіг: І між словами запиту, АБО між колонками й варіантами написання
 * (розкладка, транслітерація — див. SearchTerms). Тобто товар підходить,
 * якщо кожне слово знайшлося хоч десь.
 *
 * Регістр зводиться в SQL через LOWER(): на проді це MariaDB, у тестах —
 * SQLite з підміненою UTF-8 реалізацією (AppServiceProvider), тож поведінка
 * однакова. ESCAPE задаємо явно й символом «!» — чому не бекслешем, див.
 * SearchTerms::LIKE_ESCAPE.
 */
trait Searchable
{
    public function scopeSearch(Builder $query, ?string $raw): Builder
    {
        $tokens = SearchTerms::tokens($raw);

        if ($tokens === []) {
            return $query;
        }

        $columns = static::$searchColumns ?? [];

        foreach ($tokens as $token) {
            $query->where(function (Builder $q) use ($token, $columns): void {
                // Умова має бути непорожньою за будь-яких вхідних даних:
                // порожній where() означав би «підходить усе».
                foreach (SearchTerms::variants($token) ?: [$token] as $variant) {
                    $pattern = '%'.SearchTerms::escapeLike($variant).'%';

                    foreach ($columns as $column) {
                        $q->orWhereRaw(static::likeExpression($column), [$pattern]);
                    }
                }
            });
        }

        return $query;
    }

    /**
     * Порядок видачі без повнотекстового індексу: точний збіг назви вище за
     * «починається з», той — вище за збіг усередині, і найнижче — коли слово
     * знайшлося лише в описі. Далі «підняті» товари, далі стабільний id,
     * щоб сторінки пагінації не перемішувалися між запитами.
     */
    public function scopeOrderByRelevance(Builder $query, ?string $raw): Builder
    {
        $tokens = SearchTerms::tokens($raw);

        if ($tokens === []) {
            return $query;
        }

        $columns = static::$searchColumns ?? [];
        $primary = $columns[0] ?? null;

        if ($primary !== null) {
            // Варіанти фрази, а не лише те, що ввели: інакше запит «rhtv»
            // нікуди не потрапляв би за тирами (жодна назва не містить
            // «rhtv»), усі рядки ставали рівними — і зверху опинявся
            // просто «піднятий» товар замість того, що справді схожий.
            $phrases = SearchTerms::variants(trim((string) $raw));

            $exact = [];
            $starts = [];
            $inside = [];

            foreach ($phrases as $phrase) {
                $escaped = SearchTerms::escapeLike($phrase);

                $exact[] = $phrase;
                $starts[] = $escaped.'%';
                $inside[] = '%'.$escaped.'%';
            }

            $equals = static::anyOf(static::lowerExpression($primary).' = ?', \count($exact));
            $like = static::anyOf(static::likeExpression($primary), \count($phrases));

            $query->orderByRaw(
                'CASE'
                ." WHEN {$equals} THEN 0"
                ." WHEN {$like} THEN 1"
                ." WHEN {$like} THEN 2"
                .' ELSE 3 END',
                [...$exact, ...$starts, ...$inside],
            );
        }

        if (\in_array('is_featured', static::searchTieBreakers(), true)) {
            $query->orderByDesc('is_featured');
        }

        return $query->orderBy($query->getModel()->getQualifiedKeyName());
    }

    /**
     * Колонки-«розбивачі нічиїх», які є не в кожної моделі.
     *
     * @return list<string>
     */
    protected static function searchTieBreakers(): array
    {
        return ['is_featured'];
    }

    /** «a OR a OR a» — по одному шматку на кожен варіант написання. */
    private static function anyOf(string $expression, int $times): string
    {
        return '('.implode(' OR ', array_fill(0, max($times, 1), $expression)).')';
    }

    private static function lowerExpression(string $column): string
    {
        // Колонки задає сама модель у $searchColumns, із запиту сюди нічого
        // не потрапляє — тож інтерполяція безпечна.
        return 'LOWER('.$column.')';
    }

    private static function likeExpression(string $column): string
    {
        return static::lowerExpression($column)." LIKE ? ESCAPE '".SearchTerms::LIKE_ESCAPE."'";
    }
}
