<?php
namespace Laranix\Tests\Laranix\Auth\Password\Reset;

use Laranix\Auth\Password\Reset\Reset;
use Laranix\Auth\User\User;
use Laranix\Tests\LaranixTestCase;

/**
 * @see \Laranix\Tests\Laranix\Auth\Email\Verification\VerificationTest
 * @see \Laranix\Tests\Laranix\Auth\User\Token\TokenTest
 */
class ResetTest extends LaranixTestCase
{
    /**
     * @var bool
     */
    protected $runMigrations = true;

    /**
     * @var array
     */
    protected $factories = [
        User::class     => __DIR__ . '/../../../../Factory/User',
        Reset::class    => __DIR__ . '/../../../../Factory/Password/Reset',
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
     * Test relationship returns correct user
     */
    public function testGetUserFromRelationship()
    {
        $this->assertSame(3, Reset::find(3)->user->user_id);
        $this->assertSame(4, Reset::find(4)->user->getKey());
    }

    /**
     * Test get token status
     */
    public function testGetStatusAttribute()
    {
        $this->assertSame(2, (new Reset())->status);
    }
}
