<?php

namespace App\Providers;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Foundation\Console\ServeCommand;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use PDO;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Carbon::setLocale('uk');

        $this->registerUtf8LowerForSqlite();

        // `php artisan serve` піднімає вбудований сервер окремим процесом і
        // лишає йому тільки змінні з цього списку. Без PHPRC локальний сервер
        // брав системний php.ini з upload_max_filesize=2M — і завантаження
        // фото в адмінці падало, хоча composer run dev передає свій ini.
        if (! \in_array('PHPRC', ServeCommand::$passthroughVariables, true)) {
            ServeCommand::$passthroughVariables[] = 'PHPRC';
        }

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        View::composer('*', function ($view) {
            try {
                $view->with('settings', Setting::allKeyed());
            } catch (\Throwable) {
                $view->with('settings', []);
            }
        });
    }

    /**
     * UTF-8 LOWER() для SQLite.
     *
     * Пошук порівнює рядки через LOWER(колонка) LIKE ?. На проді MariaDB з
     * utf8mb4_unicode_ci це працює для кирилиці, а вбудований LOWER() у
     * SQLite знає лише ASCII: LOWER('Крем') там лишається 'Крем', і тести
     * розходилися б з продом саме там, де це найважче помітити.
     *
     * SQLite дозволяє перекрити вбудовану функцію власною, тож підміняємо
     * LOWER на mb_strtolower. Діє лише для з'єднань sqlite (тести й локальна
     * розробка) — MariaDB цього коду не бачить.
     */
    private function registerUtf8LowerForSqlite(): void
    {
        Event::listen(ConnectionEstablished::class, static function (ConnectionEstablished $event): void {
            if ($event->connection->getDriverName() !== 'sqlite') {
                return;
            }

            $pdo = $event->connection->getPdo();

            if ($pdo instanceof PDO && method_exists($pdo, 'sqliteCreateFunction')) {
                $pdo->sqliteCreateFunction('lower', static fn ($value) => mb_strtolower((string) $value), 1);
            }
        });
    }
}
