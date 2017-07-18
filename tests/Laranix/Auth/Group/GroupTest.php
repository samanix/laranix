<?php
namespace Laranix\Tests\Auth\Group;

use Laranix\Auth\Group\Group;
use Laranix\Auth\User\Groups\UserGroup;
use Laranix\Auth\User\User;
use Laranix\Tests\LaranixTestCase;

class GroupTest extends LaranixTestCase
{
    /**
     * @var bool
     */
    protected $runMigrations = true;

    /**
     * @var array
     */
    protected $factories = [
        User::class         => __DIR__ . '/../../../Factory/User',
        Group::class        => __DIR__ . '/../../../Factory/Group',
        UserGroup::class    => [__DIR__ . '/../../../Factory/User/Groups', 10],
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
     * Test getting attribute
     */
    public function testGetFlagAttribute()
    {
        $this->assertSame(['a', 'b', 'c'], Group::find(1)->flags);
        $this->assertEmpty(Group::find(3)->flags);
    }

    /**
     * Test user groups relationship
     */
    public function testGetUserGroupsRelationship()
    {
        $this->assertSame(2, Group::find(1)->usergroups->count());
        $this->assertSame(4, Group::find(3)->usergroups->count());
        $this->assertSame(1, Group::find(4)->usergroups->count());
    }

    /**
     * Test get group id attribute
     */
    public function testGetGroupIdAttribute()
    {
        $this->assertSame(1, Group::find(1)->id);
        $this->assertSame(5, Group::find(5)->id);
    }

    /**
     * Test get group name attribute
     */
    public function testGetGroupNameAttribute()
    {
        $this->assertSame('Mod', Group::find(2)->name);
        $this->assertSame('User', Group::find(3)->name);
    }

    /**
     * Test get hidden attribute
     */
    public function testGetHiddenAttribute()
    {
        $this->assertTrue(Group::find(4)->hidden);
        $this->assertFalse(Group::find(2)->hidden);
    }
}
