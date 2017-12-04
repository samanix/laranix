<?php
namespace Laranix\Tests\Laranix\Auth\Group;

use Laranix\Auth\Group\Group;
use Laranix\Auth\Group\GroupRepository;
use Laranix\Tests\LaranixTestCase;

class GroupRepositoryTest extends LaranixTestCase
{
    /**
     * @var bool
     */
    protected $runMigrations = true;

    /**
     * @var array
     */
    protected $factories = [
        Group::class =>   __DIR__ . '/../../../Factory/Group',
    ];

    /**
     * @var \Laranix\Auth\Group\GroupRepository
     */
    protected $repository;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->createFactories();

        $this->repository = $this->createGroupRepository();
    }

    /**
     * Tear down
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Test getting group by ID
     */
    public function testGetGroupById()
    {
        $this->assertSame(1, $this->repository->getById(1)->id);
        $this->assertSame(5, $this->repository->getById(5)->getKey());
    }

    /**
     * Test getting group by name
     */
    public function testGetGroupByName()
    {
        $this->assertSame(1, $this->repository->getByName('Admin')->id);
        $this->assertSame(4, $this->repository->getByName('Subadmin')->id);
    }

    /**
     * Test getting group by no attributes
     */
    public function testGetGroupByNoAttributes()
    {
        $this->assertNull($this->repository->getByAttributes([]));
    }

    /**
     * Test get by attributes
     */
    public function testGetGroupByAttributes()
    {
        $this->assertSame(1, $this->repository->getByAttributes(['flags' => json_encode(['a', 'b', 'c'])])->getKey());
        $this->assertSame(5, $this->repository->getByAttributes(['color' => 'purple', 'icon' => 'manager.jpg'])->getKey());
        $this->assertSame(3, $this->repository->getByAttributes(['color' => 'orange', 'level' => '10'])->getKey());
    }

    /**
     * Get by no matching attributes
     */
    public function testGetGroupByNoMatchingAttributes()
    {
        $this->assertNull($this->repository->getByAttributes(['flags' => 'a,b,c,d']));
        $this->assertNull($this->repository->getByAttributes(['color' => 'red', 'icon' => 'manager.jpg']));
        $this->assertNull($this->repository->getByAttributes(['color' => 'orange', 'level' => '50']));
    }

    /**
     * Test model creation
     */
    public function testGetModel()
    {
        $this->assertInstanceOf(Group::class, $this->createGroupRepository()->getModel());
    }

    /**
     * Create repository
     *
     * @return GroupRepository
     */
    protected function createGroupRepository()
    {
        return new GroupRepository();
    }
}
