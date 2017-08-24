<?php
namespace Laranix\Tests\Auth\User\Token;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Mail\Mailer;
use Laranix\Auth\User\Token\Token;
use Laranix\Support\Exception\NullValueException;
use Laranix\Tests\LaranixTestCase;
use Mockery as m;
use Laranix\Tests\Laranix\Auth\User\Stubs\Token\Stubs\{ManagerNoConfig, ManagerNoMailTemplate, ManagerNoModel};

/**
 * @see \Laranix\Tests\Laranix\Auth\Password\Reset\ManagerTest
 * @see \Laranix\Tests\Laranix\Auth\Email\Verification\ManagerTest
 */
class ManagerTest extends LaranixTestCase
{
    /**
     * Test with no config key
     */
    public function testThrowsExceptionWhenConfigKeyNull()
    {
        $this->expectException(NullValueException::class);

        list($config, $mailer) = $this->getMocks();

        new ManagerNoConfig($config, $mailer);
    }

    /**
     * Test with no model
     */
    public function testThrowsExceptionWhenModelNull()
    {
        $this->expectException(NullValueException::class);

        list($config, $mailer) = $this->getMocks();

        new ManagerNoModel($config, $mailer);
    }

    /**
     * Test with no mail template
     */
    public function testThrowsExceptionWhenNoMailTemplate()
    {
        $this->expectException(NullValueException::class);

        list($config, $mailer) = $this->getMocks();

        $mailer->shouldReceive('send')->andReturnSelf();

        (new ManagerNoMailTemplate($config, $mailer))->sendMail($this->getUserMock(), $this->getTokenMock());
    }

    /**
     * @return array
     */
    protected function getMocks()
    {
        return [
            new Repository([
                'app' => [
                    'key' => 'foo',
                ],
                'laranixauth' => [
                    'password'  => [
                        'table'     => 'password_reset',
                        'route'     => 'password.reset',
                        'mail'      => [
                            'view'      => 'password_reset',
                            'subject'   => 'Password Reset Mail',
                            'markdown'  => false,
                        ],
                    ],
                ],
            ]),
            m::mock(Mailer::class),
        ];
    }

    /**
     * @return \Mockery\MockInterface
     */
    protected function getUserMock()
    {
        $user = m::mock(Authenticatable::class);

        $user->shouldReceive('getAuthIdentifier')->andReturn(1);
        $user->email = 'foo@bar.com';
        $user->username = 'foo';
        $user->first_name = 'foo';
        $user->last_name = 'bar';
        $user->full_name = 'foo bar';

        return $user;
    }

    /**
     * @return \Mockery\MockInterface
     */
    protected function getTokenMock()
    {
        $token = $this->getMockForAbstractClass(Token::class);

        $token->token = 'token123';

        return $token;
    }

}
