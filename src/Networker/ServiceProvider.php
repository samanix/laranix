<?php
namespace Laranix\Networker;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register providers.
     */
    public function register()
    {
        $this->app->singleton(Networker::class, function ($app) {
            return new Networker($app->make('config'));
        });
    }

    /**
     * Return provided services.
     *
     * @return array
     */
    public function provides()
    {
        return [Networker::class];
    }

    /**
     * Bootstrap app events.
     */
    public function boot()
    {
        $configFile = __DIR__.'/config/networker.php';

        $this->mergeConfigFrom($configFile, 'networker');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $configFile => config_path('networker.php'),
            ], 'laranix-configs');
        }
    }
}
