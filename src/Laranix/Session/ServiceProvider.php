<?php
namespace Laranix\Session;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Support\Facades\Session as SessionFacade;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register providers.
     */
    public function register()
    {
        //
    }

    /**
     * Boot service
     */
    public function boot()
    {
        SessionFacade::extend('laranix', function ($app) {
            return new Handler($app->make('config'), $app->make('request'));
        });

        if ($this->app->runningInConsole()) {
            $migrations = __DIR__ . '/migrations';

            $this->publishes([
                $migrations => database_path('migrations'),
            ], 'laranix-migrations');
        }
    }
}
