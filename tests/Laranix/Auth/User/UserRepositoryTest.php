<?php
namespace Tests\Laranix\Auth\User;

use Laranix\Auth\User\User;
use Laranix\Auth\User\UserRepository;
use Tests\LaranixTestCase;

class UserRepositoryTest extends LaranixTestCase
{
    /**
     * @var bool
     */
    protected $runMigrations = true;

    /**
     * @var array
     */
    protected $factories = [
        User::class     => __DIR__ . '/../../../Factory/User',
    ];

    /**
     * @var \Laranix\Auth\User\UserRepository
     */
    protected $repository;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->createFactories();

        $this->repository = $this->createUserRepository();
    }

    /**
     * Test getting user by bad email
     */
    public function testGetUserByBadEmail()
    {
        $this->assertInstanceOf(User::class, $this->repository->getUser('notagoodmail'));
    }

    /**
     * Get model from null getUser
     */
    public function testGetModelByUser()
    {
        $this->assertInstanceOf(User::class, $this->repository->getUser());
    }

    /**
     * Test getting user by Id
     */
    public function testGetUserById()
    {
        $this->assertInstanceOf(User::class, $this->repository->getById(1));
        $this->assertInstanceOf(User::class, $this->repository->getUser(2));

        $this->assertNull($this->repository->getById(100));
        $this->assertNull($this->repository->getUser(100));
    }

    /**
     * Test get user by email
     */
    public function testGetUserByEmail()
    {
        $this->assertSame(1, $this->repository->getByEmail('foo@bar.com')->getKey());
        $this->assertSame(5, $this->repository->getUser('baz@foo.com')->getKey());

        $this->assertNull($this->repository->getByEmail('nomail@foo.com'));
        $this->assertNull($this->repository->getUser('alsonomail@bar.com'));
    }

    /**
     * Test getting user by remember token
     */
    public function testGetUserByRememberToken()
    {
        $this->assertInstanceOf(User::class, $this->repository->getByRememberToken(1, 'foo123'));
        $this->assertInstanceOf(User::class, $this->repository->getByToken(2, 'bar123', UserRepository::TOKEN_TYPE_REMEMBER));

        $this->assertNull($this->repository->getByRememberToken(100, 'nokey'));
        $this->assertNull($this->repository->getByToken(50, 'notoken', UserRepository::TOKEN_TYPE_REMEMBER));
    }

    /**
     * Test getting user by api token
     */
    public function testGetUserByApiToken()
    {
        $this->assertInstanceOf(User::class, $this->repository->getByApiToken(4, '123foo'));
        $this->assertInstanceOf(User::class, $this->repository->getByToken(5, '123bar', UserRepository::TOKEN_TYPE_API));

        $this->assertNull($this->repository->getByApiToken(75, 'nokey'));
        $this->assertNull($this->repository->getByToken(25, 'notoken', UserRepository::TOKEN_TYPE_API));
    }

    /**
     * Test getting by credentials
     */
    public function testGetByCredentials()
    {
        $this->assertSame(1, $this->repository->getByCredentials(['username' => 'foo', 'last_name' => 'Bar'])->getKey());

        $this->assertSame(2, $this->repository->getByCredentials(['company' => 'Bar Co', 'account_status' => 1])->getKey());

        $this->assertNull($this->repository->getByCredentials(['company' => 'None', 'username' => 'Also None']));
    }

    /**
     * Test get model
     */
    public function testGetModel()
    {
        $this->assertInstanceOf(User::class, $this->repository->getModel());
    }

    /**
     * Create repository
     *
     * @return UserRepository
     */
    protected function createUserRepository()
    {
        return new UserRepository();
    }
}
