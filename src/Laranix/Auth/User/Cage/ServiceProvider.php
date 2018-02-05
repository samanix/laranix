<?php
namespace Laranix\Auth\User\Cage;

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
        $this->app->bind(Repository::class, CageRepository::class);
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [Repository::class];
    }
}
