<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Translation\Translator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Paginator::useBootstrap();

        Blade::directive('hasPermission', function ($permissions) {
            return "<?php if(Auth::user()->can({$permissions})): ?>";
        });

        Blade::directive('endhasPermission', function () {
            return '<?php endif; ?>';
        });

        $this->app->singleton('translation.loader', function ($app) {
            return new CustomTranslationLoader($app['files'], $app['path.lang']);
        });

        $this->app->singleton('translator', function ($app) {
            $loader = $app['translation.loader'];

            $locale = $app['config']['app.locale'];

            $trans = new Translator($loader, $locale);

            $trans->setFallback($app['config']['app.fallback_locale']);

            return $trans;
        });
        $dbConnectionStatus =dbConnectionStatus();
        if ($dbConnectionStatus && Schema::hasTable('settings') && file_exists(storage_path('installed')) ) {
            $timezone = Cache::rememberForever('settings.default_time_zone', function () {
                return DB::table('settings')->where('name', 'default_time_zone')->value('val') ?? 'UTC';
            });
    
            // Set the application timezone
            Config::set('app.timezone', $timezone);
            date_default_timezone_set($timezone);
        }
    }
}
