<?php
namespace Laranix\Recaptcha;

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
        $this->app->singleton(Recaptcha::class, function ($app) {
            return new Recaptcha($app->make('config'), $app->make('request'), $app->make('view'));
        });
    }

    /**
     * Return provided services.
     *
     * @return array
     */
    public function provides()
    {
        return [Recaptcha::class];
    }

    /**
     * Bootstrap app events.
     */
    public function boot()
    {
        $configFile = __DIR__ . '/config/recaptcha.php';

        $this->mergeConfigFrom($configFile, 'recaptcha');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $configFile => config_path('recaptcha.php'),
            ], 'laranix-configs');
        }
    }
}
