<?php
namespace Laranix\Auth\Email\Verification;

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
        $this->app->singleton(Manager::class, function ($app) {
            $config = $app->make('config');

            return new Manager(
                $config,
                new Mailer($app->make('mailer'), $config)
            );
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
