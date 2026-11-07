<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Коли завантажений файл більший за post_max_size, PHP викидає весь POST —
 * разом із CSRF-токеном. Для користувача це виглядало як «Page Expired»
 * у відповідь на звичайне збереження форми.
 *
 * Мідлвар ловить саме цей випадок (тіло запиту прийшло, а розібраних полів
 * немає) і віддає 413 зі зрозумілою сторінкою замість 419.
 */
class HandleOversizedUpload
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isTruncatedUpload($request)) {
            abort(413);
        }

        return $next($request);
    }

    private function isTruncatedUpload(Request $request): bool
    {
        if (! $request->isMethod('POST')) {
            return false;
        }

        $length = (int) $request->server('CONTENT_LENGTH', 0);
        $limit = $this->toBytes((string) ini_get('post_max_size'));

        return $limit > 0
            && $length > $limit
            && $request->post() === []
            && $request->allFiles() === [];
    }

    /**
     * «20M» → 20971520. Нуль означає «без обмежень» — тоді й перевіряти нічого.
     */
    private function toBytes(string $value): int
    {
        $value = trim($value);

        if ($value === '') {
            return 0;
        }

        $number = (int) $value;

        return match (strtolower(substr($value, -1))) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
