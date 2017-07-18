<?php
namespace Laranix\Tests\Laranix\Auth\User\Cage;

use Laranix\Auth\Group\Group;
use Laranix\Auth\User\Groups\UserGroup;
use Laranix\Auth\User\Groups\UserGroupRepository;
use Laranix\Auth\User\User;
use Laranix\Tests\LaranixTestCase;

class UserGroupRepositoryTest extends LaranixTestCase
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
     * @var \Laranix\Auth\User\Groups\UserGroupRepository
     */
    protected $repository;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->createFactories();

        $this->repository = $this->createUserGroupRepository();
    }

    /**
     * Test getting by user and group
     */
    public function testGetByUserGroup()
    {
        $this->assertNotNull($this->repository->getByUserGroup(1, 1));
        $this->assertNull($this->repository->getByUserGroup(1, 5));
    }

    /**
     * Test getting by group Id
     */
    public function testGetByGroupId()
    {
        $this->assertCount(2, $this->repository->getByGroupId(1));
        $this->assertCount(4, $this->repository->getByGroupId(3));

        $this->assertCount(0, $this->repository->getByGroupId(7));

        $this->assertSame(20, $this->repository->getByGroupId(1, 20)->perPage());
    }

    /**
     * Test getting by group Id
     */
    public function testGetByUserId()
    {
        $this->assertCount(3, $this->repository->getByUserId(1));
        $this->assertCount(1, $this->repository->getByGroupId(4));

        $this->assertCount(0, $this->repository->getByGroupId(10));

        $this->assertSame(5, $this->repository->getByGroupId(1, 5)->perPage());
    }

    /**
     * Test model creation
     */
    public function testGetModel()
    {
        $this->assertInstanceOf(UserGroup::class, $this->createUserGroupRepository()->getModel());
    }

    /**
     * Create repository
     *
     * @return UserGroupRepository
     */
    protected function createUserGroupRepository()
    {
        return new UserGroupRepository();
    }
}
