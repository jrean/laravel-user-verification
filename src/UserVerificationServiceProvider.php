<?php
/**
 * This file is part of Jrean\UserVerification package.
 *
 * (c) Jean Ragouin <go@askjong.com> <www.askjong.com>
 */
namespace Jrean\UserVerification;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class UserVerificationServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // routes
        if (! $this->app->routesAreCached()) {
            require __DIR__ . '/routes.php';
        }

        // views
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'laravel-user-verification');

        $this->publishes([
                __DIR__ . '/resources/views' => resource_path('views/vendor/laravel-user-verification'),
            ], 'views');

        // translations
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'laravel-user-verification');

        $this->publishes([
            __DIR__ . '/resources/lang' => resource_path('lang/vendor/laravel-user-verification'),
        ], 'translations');

        // migrations
        $this->publishes([
            __DIR__ . '/resources/migrations/' => database_path('migrations')
        ], 'migrations');

        // config
        $this->publishes([
            __DIR__ . '/config/user-verification.php' => config_path('user-verification.php')
        ], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerUserVerification($this->app);

        // configurations
        $this->mergeConfigFrom(
            __DIR__ . '/config/user-verification.php', 'user-verification'
        );
    }

    /**
     * Register the user verification.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    protected function registerUserVerification(Application $app)
    {
        $app->bind('user.verification', function ($app) {
            return new UserVerification(
                $app->make('mailer'),
                $app->make('db')->connection()->getSchemaBuilder()
            );
        });

        $app->alias('user.verification', UserVerification::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'user.verification',
        ];
    }
}
