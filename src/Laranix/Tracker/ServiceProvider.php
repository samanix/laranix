<?php
namespace Laranix\Tracker;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = true;

    /**
     * Register providers.
     */
    public function register()
    {
        $this->app->bind(Repository::class, function($app) {
            return new TrackerRepository($app->make('config'));
        });

        $this->app->singleton(TrackWriter::class, function ($app) {
            return new Writer($app->make('config'), $app->make('request'));
        });
    }

    /**
     * Return provided services.
     *
     * @return array
     */
    public function provides()
    {
        return [Repository::class, TrackWriter::class];
    }

    /**
     * Boot method
     */
    public function boot()
    {
        $configFile = __DIR__ . '/config/tracker.php';

        $this->mergeConfigFrom($configFile, 'tracker');

        if ($this->app->runningInConsole()) {
            $migrations = __DIR__ . '/migrations';

            $this->publishes([
                $configFile => config_path('tracker.php'),
            ], 'laranix-configs');


            $this->publishes([
                $migrations => database_path('migrations'),
            ], 'laranix-migrations');
        }
    }
}
