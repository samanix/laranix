<?php
namespace Laranix\Tests\Laranix\Auth\User\Cage;

use Laranix\Auth\User\Cage\CageRepository;
use Laranix\Auth\User\Cage\Cage;
use Laranix\Auth\User\Cage\Repository;
use Laranix\Auth\User\User;
use Laranix\Tests\LaranixTestCase;

class CageRepositoryTest extends LaranixTestCase
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
     * @var \Laranix\Auth\User\Cage\CageRepository
     */
    protected $repository;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->createFactories();

        $this->repository = $this->createCageRepository();
    }

    /**
     * Test get cage by ID
     */
    public function testGetCageById()
    {
        $this->assertSame(2, $this->repository->getById(2)->cage_id);
        $this->assertSame(5, $this->repository->getById(5)->id);
    }

    /**
     * Test get by ID with no matching row
     */
    public function testGetCageByIdWhenIdDoesNotExist()
    {
        $this->assertNull($this->repository->getById(200));
    }

    /**
     * Get cage by target user
     */
    public function testGetCageByUser()
    {
        $this->assertCount(1, $this->repository->getByUser(3, Repository::DEFAULT));
        $this->assertCount(2, $this->repository->getByUser(3, Repository::WITH_DELETED));
        $this->assertCount(1, $this->repository->getByUser(3, Repository::DELETED_ONLY));
        $this->assertSame(10, $this->repository->getByUser(3, Repository::DEFAULT, 10)->perPage());
    }

    /**
     * Get cage by issuing user
     */
    public function testGetCageByIssuer()
    {
        $this->assertCount(3, $this->repository->getByIssuer(1, Repository::DEFAULT));
        $this->assertCount(4, $this->repository->getByIssuer(1, Repository::WITH_DELETED));
        $this->assertCount(1, $this->repository->getByIssuer(1, Repository::DELETED_ONLY));
        $this->assertSame(5, $this->repository->getByUser(1, Repository::DEFAULT, 5)->perPage());
    }

    /**
     * Get cage by cage area
     */
    public function testGetCageByArea()
    {
        $this->assertCount(1, $this->repository->getByArea('login', Repository::DEFAULT));
        $this->assertCount(2, $this->repository->getByArea('login', Repository::WITH_DELETED));
        $this->assertCount(1, $this->repository->getByArea('login', Repository::DELETED_ONLY));
        $this->assertSame(7, $this->repository->getByUser(1, Repository::DEFAULT, 7)->perPage());
    }

    /**
     * Get cage by attributes
     */
    public function testGetByAttributes()
    {
        $this->assertCount(1,
                           $this->repository->getByAttributes(['cage_level' => 10, 'user_id' => 3],
                                                              Repository::DEFAULT));

        $this->assertCount(2,
                           $this->repository->getByAttributes(['cage_level' => 10, 'user_id' => 3],
                                                              Repository::WITH_DELETED));

        $this->assertCount(1,
                           $this->repository->getByAttributes(['cage_level' => 10, 'user_id' => 3],
                                                              Repository::DELETED_ONLY));

        $this->assertSame(20,
                          $this->repository->getByAttributes(['cage_level' => 10, 'user_id' => 3],
                                                             Repository::DEFAULT, 20)->perPage());
    }

    /**
     * Test model creation
     */
    public function testGetModel()
    {
        $this->assertInstanceOf(Cage::class, $this->createCageRepository()->getModel());
    }

    /**
     * Create repository
     *
     * @return CageRepository
     */
    protected function createCageRepository()
    {
        return new CageRepository();
    }
}
