<?php
namespace Laranix\Tests\Laranix\Auth\User\Cage;

use Carbon\Carbon;
use Laranix\Auth\User\User;
use Laranix\Tests\LaranixTestCase;
use Laranix\Auth\User\Cage\Cage;

class CageTest extends LaranixTestCase
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
        Cage::class     => __DIR__ . '/../../../../Factory/User/Cage',
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
     * Test get id attribute
     */
    public function testGetIdAttribute()
    {
        $this->assertSame(2, Cage::find(2)->id);
        $this->assertSame(5, Cage::find(5)->id);
    }

    /**
     * Test relationship returns correct user
     */
    public function testGetExpiryAttribute()
    {
        $this->assertInstanceOf(Carbon::class, Cage::find(2)->expiry);
        $this->assertInstanceOf(Carbon::class, Cage::find(5)->expiry);
    }

    /**
     * Test get level attribute
     */
    public function testGetLevelAttribute()
    {
        $this->assertSame(10, Cage::find(2)->level);
        $this->assertSame(25, Cage::find(5)->level);
    }

    /**
     * Test get level attribute
     */
    public function testGetAreaAttribute()
    {
        $this->assertSame('foo', Cage::find(2)->area);
        $this->assertSame('bar', Cage::find(3)->area);
    }

    /**
     * Test get time attribute
     */
    public function testGetTimeAttribute()
    {
        $this->assertSame(1440, Cage::find(4)->time);
        $this->assertSame(0, Cage::find(5)->time);
    }

    /**
     * Test get cage reason
     */
    public function testGetReasonAttribute()
    {
        $this->assertSame('foo', Cage::find(4)->reason);
        $this->assertSame('**foobar**', Cage::find(2)->reason);
    }

    /**
     * Test get rendered cage reason
     */
    public function testGetReasonRenderedAttribute()
    {
        $this->assertSame('<p><strong>foo</strong> <em>bar</em></p>', Cage::find(5)->renderedReason);
        $this->assertSame('<p><em>foo</em></p>', Cage::withTrashed()->find(1)->rendered_reason);
    }

        /**
     * Test save rendered data
     */
    public function testSaveRenderedReason()
    {
        $this->assertNull(Cage::find(5)->saveRenderedReason());

        $cage = Cage::find(2);

        $this->assertSame('<p><strong>foobar</strong></p>', $cage->renderedReason);

        $cage->setAttribute('cage_reason', '_bar_');

        $cage->saveRenderedReason();

        $this->assertSame('<p><em>bar</em></p>', $cage->renderedReason);
    }

    /**
     * Test active scope
     */
    public function testActiveScope()
    {
        $this->assertCount(3, Cage::active()->get());
        $this->assertCount(1, Cage::where('issuer_id', 2)->active()->get());
        $this->assertCount(0, Cage::where('cage_id', 1)->active()->get());
    }

    /**
     * Test get issuer relationship
     */
    public function testGetIssuerRelationship()
    {
        $this->assertSame(1, Cage::find(4)->issuer->user_id);
        $this->assertSame(2, Cage::find(5)->issuer->getKey());
    }

    /**
     * Test get issuer relationship
     */
    public function testGetUserRelationship()
    {
        $this->assertSame(3, Cage::find(2)->user->user_id);
        $this->assertSame(4, Cage::find(3)->user->getKey());
    }

    /**
     * Test get ip attribute
     */
    public function testGetIpv4Attribute()
    {
        $this->assertSame('1.1.1.1', Cage::find(2)->ipv4);
        $this->assertSame('1.1.1.5', Cage::find(5)->ipv4);
    }

    /**
     * Test get raw ip attribute
     */
    public function testGetRawIpv4Attribute()
    {
        $this->assertSame(ip2long('1.1.1.3'), Cage::find(3)->rawIpv4);
        $this->assertSame(ip2long('1.1.1.5'), Cage::find(5)->rawIpv4);
    }

    /**
     * Test isExpired function
     */
    public function testIsExpired()
    {
        $this->assertFalse(Cage::withTrashed()->find(1)->isExpired());
        $this->assertTrue(Cage::find(3)->isExpired());
        $this->assertFalse(Cage::find(5)->isExpired());
    }

    /**
     * Test is cage is removed
     */
    public function testIsRemoved()
    {
        $this->assertTrue(Cage::withTrashed()->find(1)->isRemoved());
        $this->assertFalse(Cage::withTrashed()->find(3)->isRemoved());
        $this->assertFalse(Cage::find(5)->isRemoved());
    }
}
