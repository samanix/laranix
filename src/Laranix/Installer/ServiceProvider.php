<?php
namespace Laranix\Installer;

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
        //
    }

    /**
     * Bootstrap app events.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallLaranixCommand::class,
            ]);
        }
    }
}
