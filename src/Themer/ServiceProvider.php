<?php
namespace Laranix\Themer;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Laranix\Themer\Image\Image;
use Laranix\Themer\Script\Script;
use Laranix\Themer\Style\Style;

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

        $this->app->singleton(Style::class, function ($app) {
            return new Style($app->make(Themer::class), $app->make('config'), $app->make('log'));
        });

        $this->app->singleton(Script::class, function ($app) {
            return new Script($app->make(Themer::class), $app->make('config'), $app->make('log'));
        });

        $this->app->singleton(Image::class, function ($app) {
            return new Image($app->make(Themer::class), $app->make('config'), $app->make('log'));
        });
    }

    /**
     * Return provided services.
     *
     * @return array
     */
    public function provides()
    {
        return [Repository::class, Themer::class, Style::class, Script::class, Image::class];
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
