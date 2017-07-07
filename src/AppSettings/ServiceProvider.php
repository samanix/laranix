<?php
namespace Laranix\AppSettings;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register service providers.
     */
    public function register()
    {
        $this->app->singleton(AppSettings::class, function ($app) {
            return new AppSettings($app->make('config'));
        });
    }

    /**
     * Return provided services.
     *
     * @return array
     */
    public function provides()
    {
        return [AppSettings::class];
    }

    /**
     * Bootstrap app events.
     */
    public function boot()
    {
        $configFile = __DIR__.'/config/appsettings.php';

        $this->mergeConfigFrom($configFile, 'appsettings');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $configFile => config_path('appsettings.php'),
            ], 'laranix-configs');
        }
    }
}
