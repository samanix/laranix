<?php
namespace Laranix\Foundation\Support;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Laranix\Support\IO\Url\Href;
use Laranix\Support\IO\Url\Url;

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
        $appUrl = $this->app->make('config')->get('app.url');

        $this->app->singleton(Url::class, function ($app) use ($appUrl) {
            return new Url($appUrl);
        });

        $this->app->singleton(Href::class, function ($app) use ($appUrl) {
            return new Href($appUrl, $app->make(Url::class));
        });
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [Url::class, Href::class];
    }
}
