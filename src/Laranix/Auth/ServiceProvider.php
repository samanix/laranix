<?php
namespace Laranix\Auth;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

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
        $configFile = __DIR__ . '/config/laranixauth.php';

        $this->mergeConfigFrom($configFile, 'laranixauth');

        if ($this->app->runningInConsole()) {
            $migrations = __DIR__ . '/migrations';

            $this->publishes([
                $configFile => config_path('laranixauth.php'),
            ], 'laranix-configs');


            $this->publishes([
                $migrations => database_path('migrations'),
            ], 'laranix-migrations');
        }
    }
}
