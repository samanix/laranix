<?php
namespace Laranix\Auth\Group;

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
        $this->app->bind(Repository::class, function () {
            return new GroupRepository;
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
