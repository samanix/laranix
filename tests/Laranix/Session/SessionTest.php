<?php
namespace Laranix\Tests\Laranix\Session;

use Laranix\Auth\User\User;
use Laranix\Session\Session;
use Laranix\Tests\LaranixTestCase;
use Illuminate\Http\Request;
use Laranix\Session\Handler;
use Illuminate\Config\Repository;
use Mockery as m;

class SessionTest extends LaranixTestCase
{
    /**
     * @var bool
     */
    protected $runMigrations = true;

    /**
     * @var array
     */
    protected $factories = [
        User::class     => __DIR__ . '/../../Factory/User',
        Session::class  => __DIR__ . '/../../Factory/Session',
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
        $this->assertSame(1, Session::where('id', 1)->first()->user->getKey());
        $this->assertSame(5, Session::where('id', 5)->first()->user->getKey());
    }

    /**
     * Get null user
     */
    public function testGetNullUserFromRelationship()
    {
        $data = ['bar' => 'baz', 'url' => 'http://foobar.com'];
        $request = m::mock(Request::class);

        $request->shouldReceive('getClientIp')->withNoArgs()->andReturn('1.1.1.6');
        $request->shouldReceive('user')->withNoArgs()->andReturnNull();
        $request->shouldReceive('server')->withAnyArgs()->andReturn('agent');

        $handler = new Handler(new Repository([
            'session' => [
                'lifetime' => 120,
            ],
        ]), $request);

        $handler->write(6, $data);

        $this->assertNull(Session::where('id', 6)->first()->user);
    }

    /**
     * Test get ipv4 attribute
     */
    public function testGetIpv4Attribute()
    {
        $this->assertSame('1.1.1.1', Session::where('id', 1)->first()->ipv4);
        $this->assertSame('1.1.1.5', Session::where('id', 5)->first()->ipv4);
    }
}
