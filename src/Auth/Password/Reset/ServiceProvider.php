<?php
namespace Laranix\Auth\Password\Reset;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Laranix\AppSettings\AppSettings;

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
        $this->app->singleton(Manager::class, function ($app) {
           return new Manager($app->make('config'), $app->make('mailer'), $app->make(AppSettings::class));
        });
    }

    /**
     * Return provided services.
     *
     * @return array
     */
    public function provides()
    {
        return [Manager::class];
    }
}
