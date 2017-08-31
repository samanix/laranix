<?php
namespace Laranix\AntiSpam;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Laranix\AntiSpam\Sequence\Sequence;
use Laranix\AntiSpam\Recaptcha\Recaptcha;

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
        $this->app->singleton(Sequence::class, function ($app) {
            return new Sequence($app->make('config'), $app->make('request'), $app->make('view'));
        });

        $this->app->singleton(Recaptcha::class, function ($app) {
            return new Recaptcha($app->make('config'), $app->make('request'), $app->make('view'), new GuzzleClient());
        });
    }

    /**
     * Return provided services.
     *
     * @return array
     */
    public function provides()
    {
        return [Sequence::class, Recaptcha::class];
    }

    /**
     * Bootstrap app events.
     */
    public function boot()
    {
        $configFile = __DIR__ . '/config/antispam.php';

        $this->mergeConfigFrom($configFile, 'antispam');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $configFile => config_path('antispam.php'),
            ], 'laranix-configs');
        }
    }
}
