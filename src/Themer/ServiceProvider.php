<?php
namespace Laranix\Themer;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Laranix\Themer\Image\Images;
use Laranix\Themer\Script\Scripts;
use Laranix\Themer\Style\Styles;

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
        $this->app->bind(Repository::class, ThemeRepository::class);

        $this->app->singleton(Themer::class, function ($app) {
            return new Themer($app->make('config'), $app->make('request'), $app->make(ThemeRepository::class));
        });

        $this->app->singleton(Styles::class, function ($app) {
            return new Styles($app->make(Themer::class), $app->make('config'), $app->make('log'));
        });

        $this->app->singleton(Scripts::class, function ($app) {
            return new Scripts($app->make(Themer::class), $app->make('config'), $app->make('log'));
        });

        $this->app->singleton(Images::class, function ($app) {
            return new Images($app->make(Themer::class), $app->make('config'), $app->make('log'));
        });
    }

    /**
     * Return provided services.
     *
     * @return array
     */
    public function provides()
    {
        return [Repository::class, Themer::class, Styles::class, Scripts::class, Images::class];
    }

    /**
     * Bootstrap app events.
     */
    public function boot()
    {
        $configFile = __DIR__.'/config/themer.php';

        $this->mergeConfigFrom($configFile, 'themer');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $configFile => config_path('themer.php'),
            ], 'laranix-configs');
        }
    }
}
