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
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerUserVerification($this->app);
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
