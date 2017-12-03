<?php
namespace Laranix\Tests\Laranix\Session;

use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Laranix\Auth\User\User;
use Laranix\Session\Handler;
use Laranix\Session\Session;
use Laranix\Tests\LaranixTestCase;
use Mockery as m;

class HandlerTest extends LaranixTestCase
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
     * Test open method
     */
    public function testOpen()
    {
        $this->assertTrue($this->createHandler()->open('/any/path', 'session'));
    }

    /**
     * Test close method
     */
    public function testClose()
    {
        $this->assertTrue($this->createHandler()->close());
    }

    /**
     * Test read is null when ID doesn't exist
     */
    public function testReadIsNullWhenIdDoesNotExist()
    {
        $handler = $this->createHandler('1.1.1.1');

        $this->assertNull($handler->read(100));
    }

    /**
     * Test reading when ID exists but IP does not match
     */
    public function testReadIsNullWhenIpDoesNotMatch()
    {
        $handler = $this->createHandler('1.1.1.1');

        $this->assertNull($handler->read(3));
        $this->assertNull($handler->read(4));
    }

    /**
     * Expired but exists
     */
    public function testReadWhenSessionExpired()
    {
        $this->assertNull($this->createHandler('1.1.1.2')->read(2));
        $this->assertNull($this->createHandler('1.1.1.5')->read(5));
    }

    /**
     * Expired but exists
     */
    public function testReadWhenSessionValid()
    {
        $data = $this->createHandler('1.1.1.1')->read(1);
        $data2 = $this->createHandler('1.1.1.4')->read(4);

        $this->assertSame(serialize(['foo' => 'bar', 'url' => 'http://foo.com']), $data);
        $this->assertSame(serialize(['bar' => 'baz', 'url' => 'http://foo.com/bar']), $data2);
    }

    /**
     * Test writing new data to existing session
     */
    public function testWriteUpdateSession()
    {
        $newData = ['foo' => 'baz', 'url' => 'http://bar.com'];

        $handler = $this->createHandler('1.1.1.1');

        $this->assertTrue($handler->write('1', $newData));

        $this->assertSame(serialize($newData), $handler->read('1'));
    }

    /**
     * Test writing data to new session
     */
    public function testWriteCreateSession()
    {
        $data = ['bar' => 'baz', 'url' => 'http://foobar.com'];

        $handler = $this->createHandler('1.1.1.6');

        $this->assertTrue($handler->write(6, $data));

        $this->assertDatabaseHas(config('session.table'), [
           'id'     => 6,
           'data'   => base64_encode(serialize($data)),
        ]);
    }

    /**
     * Test destroy
     */
    public function testDestroySession()
    {
        $this->createHandler('1.1.1.5')->destroy(5);

        $this->assertDatabaseMissing(config('session.table'), [
            'id' => 5,
        ]);
    }

    /**
     * Test GC removes old sessions
     */
    public function testSessionGC()
    {
        $table = config('session.table');

        $this->createHandler()->gc(config('session.lifetime', 120) * 60);

        $this->assertDatabaseMissing($table, [
           'id' => 2,
        ]);

        $this->assertDatabaseMissing($table, [
           'id' => 5,
        ]);

        $this->assertDatabaseHas($table, [
           'id' => 1,
        ]);

        $this->assertDatabaseHas($table, [
           'id' => 3,
        ]);
    }

    /**
     * Test get model
     */
    public function testGetModel()
    {
        $this->assertInstanceOf(Session::class, $this->createHandler()->getModel());
    }

    /**
     * Create handler
     *
     * @param string $ip
     * @return \Laranix\Session\Handler
     */
    protected function createHandler($ip = '1.1.1.1')
    {
        $request = m::mock(Request::class);

        $request->shouldReceive('getClientIp')->andReturn($ip);
        $request->shouldReceive('user')->andReturnNull();
        $request->shouldReceive('server')->withAnyArgs()->andReturn('agent');

        return new Handler(new Repository([
            'session' => [
                'lifetime' => 120,
            ],
        ]), $request);
    }
}
