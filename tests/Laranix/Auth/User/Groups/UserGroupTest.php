<?php
namespace Laranix\Tests\Laranix\Auth\User\Groups;

use Laranix\Auth\User\Groups\UserGroup;
use Laranix\Tests\LaranixTestCase;
use Laranix\Auth\Group\Group;
use Laranix\Auth\User\User;

class UserGroupTest extends LaranixTestCase
{
    /**
     * @var bool
     */
    protected $runMigrations = true;

    /**
     * @var array
     */
    protected $factories = [
        User::class      => __DIR__ . '/../../../../Factory/User',
        Group::class     => __DIR__ . '/../../../../Factory/Group',
        UserGroup::class => [__DIR__ . '/../../../../Factory/User/Groups', 10],
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
     * Test group relationship
     */
    public function testGetGroupRelationship()
    {
        $this->assertSame('Admin', UserGroup::where(['user_id' => 1, 'group_id' => 1])->first()->group->name);
        $this->assertSame('Manager', UserGroup::where(['user_id' => 5, 'group_id' => 5])->first()->group->name);
    }

    /**
     * Test user relationship
     */
    public function testGetUserRelationship()
    {
        $this->assertSame(1, UserGroup::where('user_id', 1)->first()->user->getKey());
        $this->assertSame(3, UserGroup::where('user_id', 3)->first()->user->getKey());
        $this->assertSame(5, UserGroup::where('user_id', 5)->first()->user->getKey());
    }

    /**
     * Test relationship returns correct user
     */
    public function testGetPrimaryAttribute()
    {
        $this->assertTrue(UserGroup::where(['user_id' => 1, 'group_id' => 1])->first()->primary);
        $this->assertFalse(UserGroup::where(['user_id' => 2, 'group_id' => 3])->first()->primary);
    }

    /**
     * Test relationship returns correct user
     */
    public function testGetHiddenAttribute()
    {
        $this->assertTrue(UserGroup::where(['user_id' => 3, 'group_id' => 4])->first()->hidden);
        $this->assertFalse(UserGroup::where(['user_id' => 5, 'group_id' => 1])->first()->hidden);
    }
}
