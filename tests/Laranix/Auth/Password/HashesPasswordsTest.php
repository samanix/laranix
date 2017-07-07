<?php
namespace Tests\Laranix\Auth\Password;

use Laranix\Auth\Password\HashesPasswords;
use Laranix\Support\Exception\NullValueException;
use Tests\LaranixTestCase;

class HashesPasswordsTest extends LaranixTestCase
{
    /**
     * Test get cost
     */
    public function testGetCost()
    {
        $config = config();

        $hasher = $this->getMockForTrait(HashesPasswords::class);

        $this->assertSame(12, $hasher->getPasswordCost());
        $this->assertSame(20, $hasher->getPasswordCost(20));

        $config->set('laranixauth.password.cost', 15);

        $this->assertSame(15, $hasher->getPasswordCost());

        $config->set('laranixauth.password.cost', 12);
    }

    /**
     * Test hashing password
     */
    public function testHashPassword()
    {
        $hasher = $this->getMockForTrait(HashesPasswords::class);

        $this->assertTrue(password_verify('password123', $hasher->hashUserPassword('password123')));
        $this->assertTrue(password_verify('password123', $hasher->hashUserPassword('password123', 8)));
    }

    /**
     * Test hashing password property with no property set
     */
    public function testHashPasswordPropertyWithNoPropertySet()
    {
        $this->expectException(NullValueException::class);

        $hasher = $this->getMockForTrait(HashesPasswords::class);

        $hasher->hashUserPasswordProperty();
    }

    /**
     * Test hashing password property
     */
    public function testHashPasswordProperty()
    {
        $hasher = $this->getMockForTrait(HashesPasswords::class);

        $hasher->password = 'secret123';

        $this->assertTrue(password_verify('secret123', $hasher->hashUserPasswordProperty()));
        $this->assertTrue(password_verify('secret123', $hasher->hashUserPasswordProperty(8)));
    }
}
