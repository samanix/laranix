<?php
namespace Laranix\Tests;

use Illuminate\Database\Eloquent\Factory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Console\Kernel;

trait LaranixTestSuite
{
    use DatabaseMigrations;

    /**
     * @var bool
     */
    protected $runMigrations = false;

    /**
     * @var array
     */
    protected $factories = [];

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        if (!defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }

        Event::fake();
        Mail::fake();
    }

    /**
     * Create factories
     *
     * @param int $times
     */
    protected function createFactories(int $times = 5)
    {
        foreach ($this->factories as $class => $path) {
            if (is_array($path)) {
                (Factory::construct(app(\Faker\Generator::class), realpath($path[0])))->of($class)
                                                                                      ->times((int) $path[1])
                                                                                      ->create();

            } else {
                (Factory::construct(app(\Faker\Generator::class), realpath($path)))->of($class)
                                                                                   ->times($times)
                                                                                   ->create();
            }
        }
    }

    /**
     * Define hooks to migrate the database before and after each test.
     *
     * @return void
     */
    public function runDatabaseMigrations()
    {
        if (!$this->runMigrations) {
            return;
        }

        $this->artisan('migrate:fresh');

        $this->app[Kernel::class]->setArtisan(null);

        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:rollback');
        });
    }

    /**
     * Enable migrations
     *
     * @return bool
     */
    protected function enableMigrations()
    {
        return $this->runMigrations = true;
    }

    /**
     * Disable migrations
     *
     * @return bool
     */
    protected function disableMigrations()
    {
        return $this->runMigrations = false;
    }
}
