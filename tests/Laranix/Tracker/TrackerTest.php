<?php
namespace Laranix\Tests\Laranix\Tracker;

use Laranix\Auth\User\User;
use Laranix\Tracker\Tracker;
use Laranix\Tests\LaranixTestCase;

class TrackerTest extends LaranixTestCase
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
        Tracker::class  => __DIR__ . '/../../Factory/Tracker',
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
        $this->assertSame(1, Tracker::find(3)->user->id);
        $this->assertSame(4, Tracker::find(4)->user->getKey());
    }

    /**
     * Test get user from relationship
     */
    public function testGetNullUserFromRelationship()
    {
        Tracker::createNew([
            'user_id'           => null,
            'ipv4'              => ip2long('2.2.2.2'),
            'user_agent'        => 'Browser',
            'request_method'    => 'GET',
            'request_url'       => '/foo/bar',
            'type_id'           => 1,
            'type'              => 'track',
            'level'             => 100,
            'trackable_type'    => 1,
            'data'              => 'foo',
        ]);

        $this->assertNull(Tracker::find(6)->user);
    }

    /**
     * Test get ip attribute
     */
    public function testGetIpv4Attribute()
    {
        $this->assertSame('1.1.1.2', Tracker::find(2)->ipv4);
        $this->assertSame('1.1.1.5', Tracker::find(5)->ipv4);
    }

    /**
     * Test get raw ip attribute
     */
    public function testGetRawIpv4Attribute()
    {
        $this->assertSame(ip2long('1.1.1.2'), Tracker::find(2)->rawIpv4);
        $this->assertSame(ip2long('1.1.1.5'), Tracker::find(5)->rawIpv4);
    }

    /**
     * Test get user agent attribute
     */
    public function testGetAgentAttribute()
    {
        $this->assertNotNull(Tracker::find(1)->agent);
        $this->assertNotNull(Tracker::find(2)->agent);
    }

    /**
     * Test get method attribute
     */
    public function testGetMethodAttribute()
    {
        $this->assertSame('GET', Tracker::find(1)->method);
        $this->assertSame('POST', Tracker::find(5)->method);
    }

    /**
     * Test get url attribute
     */
    public function testGetUrlAttribute()
    {
        $this->assertSame('/bar/baz', Tracker::find(3)->url);
        $this->assertSame('/foo/bar', Tracker::find(4)->url);
    }

    /**
     * Test get type attribute
     */
    public function testGetTypeAttribute()
    {
        $this->assertSame('login', Tracker::find(3)->type);
        $this->assertSame('login', Tracker::find(5)->type);
    }

    /**
     * Test get rendered tracker data attribute
     */
    public function testGetTrackerDataRenderedAttribute()
    {
        $this->assertSame('<p><strong>foo</strong></p>', Tracker::find(2)->renderedData);
        $this->assertSame('<p><em>hello world</em></p>', Tracker::find(3)->rendered_data);
        $this->assertNull(Tracker::find(5)->renderedData);
    }

    /**
     * Test save rendered data
     */
    public function testSaveRenderedData()
    {
        $this->assertNull(Tracker::find(5)->saveRenderedData());

        $this->assertNull(Tracker::find(1)->saveRenderedData());

        $tracker = Tracker::find(2);

        $this->assertSame('<p><strong>foo</strong></p>', $tracker->renderedData);

        $tracker->setAttribute('data', '_bar_');

        $tracker->saveRenderedData();

        $this->assertSame('<p><em>bar</em></p>', $tracker->renderedData);
    }
}
