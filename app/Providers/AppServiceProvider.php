<?php

namespace App\Providers;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Foundation\Console\ServeCommand;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
}
