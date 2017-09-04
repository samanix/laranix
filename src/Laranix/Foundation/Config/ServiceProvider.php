<?php
namespace Laranix\Foundation\Config;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register service providers.
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
        $configs = [
            'appsettings'       => 'appsettings.php',
            'globalviewvars'    => 'globalviewvars.php',
            'themerdefaultfiles'=> 'themerdefaultfiles.php',
            'socialmedia'       => 'socialmedia.php',
            'defaultusergroups' => 'defaultusergroups.php',
        ];

        $publishes = [];

        foreach ($configs as $key => $config) {
            $configFile = __DIR__ . '/config/' . $config;
            $this->mergeConfigFrom($configFile, $key);

            $publishes[$configFile] = config_path($config);
        }

        if ($this->app->runningInConsole()) {
            $this->publishes($publishes, 'laranix-configs');
        }
    }
}
