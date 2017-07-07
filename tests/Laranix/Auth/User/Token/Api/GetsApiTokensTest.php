<?php
namespace Tests\Auth\User\Token\Api;

use Tests\LaranixTestCase;
use Laranix\Auth\User\Token\Api\GetsApiTokens;

class GetsApiTokensTest extends LaranixTestCase
{
    /**
     * Test creating group
     */
    public function testGetApiTokenName()
    {
        $this->assertSame('api_token', $this->createTokenProvider()->getApiTokenName());
    }

    /**
     * Test get api token value
     */
    public function testGetApiTokenValue()
    {
        $this->assertSame('token123', $this->createTokenProvider()->getApiToken());
        $this->assertSame('footoken', $this->createTokenProvider('footoken')->getApiToken());
    }

    /**
     * Test get api token value when property not set
     */
    public function testGetApiTokenValueWithNoProperty()
    {
        $token = $this->getMockForTrait(GetsApiTokens::class, [], '', true, true, true, ['getAttributeFromArray'], true);

        $token->method('getAttributeFromArray')->will($this->returnCallback(function() use ($token) {
            return null;
        }));

        $this->assertNull($token->getApiToken());
    }

    /**
     * Create token mock
     *
     * @param string $apiToken
     * @return \Laranix\Auth\User\Token\Api\GetsApiTokens|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createTokenProvider(string $apiToken = 'token123')
    {
        $token = $this->getMockForTrait(GetsApiTokens::class, [], '', true, true, true, ['getAttributeFromArray'], true);

        $token->api_token = $apiToken;

        $token->method('getAttributeFromArray')->will($this->returnCallback(function() use ($token) {
            return $token->api_token;
        }));

        return $token;
    }
}
