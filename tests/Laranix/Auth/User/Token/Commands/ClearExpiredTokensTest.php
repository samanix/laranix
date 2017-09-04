<?php
namespace Laranix\Tests\Laranix\Auth\User\Token\Commands;

use Laranix\Auth\Password\Reset\Reset;
use Laranix\Auth\User\User;
use Laranix\Tests\LaranixTestCase;

class ClearExpiredTokensTest extends LaranixTestCase
{
    /**
     * @var bool
     */
    protected $runMigrations = true;

    /**
     * @var array
     */
    protected $factories = [
        User::class         => __DIR__ . '/../../../../../Factory/User',
        Reset::class        => __DIR__ . '/../../../../../Factory/Password/Reset',
        // Tests covered by Reset factory
        //Verification::class => __DIR__ . '/../../../../../Factory/Email/Verification'
    ];

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->createFactories();
    }

    /**
     * Test clears expired reset tokens
     */
    public function testClearsExpiredTokens()
    {
        $this->assertSame(5, Reset::count());

        \Artisan::call('laranix:tokens:clear');

        $this->assertSame(3, Reset::count());
    }

    /**
     * Test clears expired reset tokens
     */
    public function testClearsExpiredTokenCustomTime()
    {
        \Artisan::call('laranix:tokens:clear', [
            '--time' => 5,
        ]);

        $this->assertSame(2, Reset::count());
    }
}
