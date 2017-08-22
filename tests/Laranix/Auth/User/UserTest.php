<?php
namespace Laranix\Tests\Laranix\Auth\User;

use Carbon\Carbon;
use Laranix\Auth\Group\Group;
use Laranix\Auth\User\Cage\Cage;
use Laranix\Auth\User\Groups\UserGroup;
use Laranix\Auth\User\User;
use Laranix\Tests\LaranixTestCase;

class UserTest extends LaranixTestCase
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
        Cage::class         => __DIR__ . '/../../../Factory/User/Cage',
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
     * Test get auth identifier
     */
    public function testGetAuthIdentifierName()
    {
        $this->assertSame('user_id', (new User())->getAuthIdentifierName());
    }

    /**
     * Test casting field
     */
    public function testLastLoginCasted()
    {
        $this->assertInstanceOf(Carbon::class, User::find(1)->last_login);
    }

    /**
     * Test user groups relationship
     */
    public function testUserGroupsRelationship()
    {
        $this->assertCount(3, User::find(1)->usergroups);
        $this->assertCount(2, User::find(5)->usergroups);
    }

    /**
     * Test get cages relationship
     */
    public function testCageRelationship()
    {
        $this->assertCount(2, User::find(3)->allCages()->withTrashed()->getResults());
        $this->assertCount(1, User::find(5)->allCages);
    }

    /**
     * Test active cages
     */
    public function testActiveCageRelationship()
    {
        $this->assertCount(1, User::find(3)->activeCages);
        $this->assertCount(0, User::find(1)->activeCages);
    }

    /**
     * Test get primary group
     */
    public function testGetPrimaryGroup()
    {
        $this->assertSame(3, User::find(4)->primaryGroup()->group_id);
        $this->assertSame(1, User::find(5)->primaryGroup()->group_id);
    }

    /**
     * Test get active cages not using active scope
     */
    public function testGetActiveCagesNotFromScope()
    {
        $this->assertCount(1, User::find(3)->getActiveCages());
        $this->assertCount(0, User::find(1)->getActiveCages());
    }

    /**
     * Test get user flags
     */
    public function testGetUserFlags()
    {
        $this->assertSame(array_flip(['a', 'b', 'c', 'd', 'e', 'f']), User::with('usergroups.group')->find(1)->getUserFlags());

        $this->assertEmpty(User::with('usergroups.group')->find(4)->getUserFlags());

        $this->assertSame(array_flip(['a', 'b', 'c', 'post', 'delete']), User::with('usergroups.group')->find(5)->getUserFlags());
    }

    /**
     * Test user has flag
     */
    public function testUserHasFlag()
    {
        $user = User::with('usergroups.group')->find(5);

        $this->assertTrue($user->hasFlag('a'));
        $this->assertTrue($user->hasFlag('post'));

        $this->assertFalse($user->hasFlag('noflag'));
    }

    /**
     * Test user has flag(s)
     */
    public function testUserHasFlagInArray()
    {
        $user = User::with('usergroups.group')->find(5);

        $this->assertTrue($user->hasFlags(['a', 'b', 'c']));
        $this->assertTrue($user->hasFlags(['post', 'a']));

        $this->assertTrue($user->hasFlags(['b', 'c', 'delete'], true));

        $this->assertFalse($user->hasFlags(['a', 'b', 'x'], true));
    }

    /**
     * Test user has group
     */
    public function testUserHasGroup()
    {
        $user = User::with('usergroups')->find(1);

        $grp1 = new Group();
        $grp1->group_id = 1;

        $grp2 = new Group();
        $grp2->group_id = 3;

        $grp3 = new Group();
        $grp3->group_id = 100;

        $this->assertTrue($user->hasGroup($grp1));
        $this->assertTrue($user->hasGroup($grp2));
        $this->assertFalse($user->hasGroup($grp3));
    }

    /**
     * Test user has group Id
     */
    public function testUserHasGroupId()
    {
        $user = User::with('usergroups')->find(1);

        $this->assertTrue($user->hasGroupId(2));
        $this->assertTrue($user->hasGroupId(3));
        $this->assertFalse($user->hasGroupId(50));
    }

    /**
     * Test user has cage
     */
    public function testUserHasCage()
    {
        $user = User::find([3, 4]);

        $this->assertSame(2, $user[0]->hasCage('foo')->cage_id);
        $this->assertSame(3, $user[1]->hasCage('bar', 0, false)->cage_id);

        // Exists but not active
        $this->assertNull($user[1]->hasCage('bar'));

        $this->assertNull($user[0]->hasCage('foo', 100));
        $this->assertNull($user[0]->hasCage('nothing'));
    }

    /**
     * Test get id attribute
     */
    public function testGetIdAttribute()
    {
        $this->assertSame(1, User::find(1)->id);
        $this->assertSame(5, User::find(5)->id);
    }

    /**
     * Test get full name attribute
     */
    public function testGetFullNameAttribute()
    {
        $this->assertSame('Foo Bar', User::find(1)->fullName);
        $this->assertSame('Santa Claus', User::find(4)->fullName);
    }
}
