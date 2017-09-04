<?php
namespace Laranix\Installer;

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
