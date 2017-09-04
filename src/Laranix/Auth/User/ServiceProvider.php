<?php
namespace Laranix\Auth\User;

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
        $this->app->bind(Repository::class, function() {
            return new UserRepository;
        });
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [Repository::class];
    }
}
