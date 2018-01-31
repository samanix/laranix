<?php
namespace Laranix\Tests\Laranix\Auth\Password\Reset;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Mail\Mailer;
use Laranix\Auth\Password\Events\Updated;
use Laranix\Auth\Password\Reset\Events\Created;
use Laranix\Auth\Password\Reset\Events\Failed;
use Laranix\Auth\Password\Reset\Manager;
use Laranix\Auth\Password\Reset\Reset;
use Laranix\Auth\User\Token\Token;
use Laranix\Auth\User\User;
use Laranix\Support\Exception\NullValueException;
use Laranix\Tests\LaranixTestCase;
use Mockery as m;
use Illuminate\Support\Facades\Event;
use Laranix\Auth\Password\Reset\Events\Reset as ResetEvent;
use Laranix\Auth\Password\Reset\Events\Updated as UpdatedEvent;


/**
 * @see \Laranix\Tests\Laranix\Auth\Email\Verification\ManagerTest
 * @see \Laranix\Tests\Laranix\Auth\User\Token\ManagerTest
 */
class ManagerTest extends LaranixTestCase
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
        Reset::class    => __DIR__ . '/../../../../Factory/Password/Reset'
    ];

    /**
     * @var \Laranix\Auth\Password\Reset\Manager
     */
    protected $manager;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        list($config, $mailer) = $this->getMocks();

        $this->manager = new Manager($config, $mailer);

        $this->createFactories();
    }

    /**
     * Test getting model
     */
    public function testGetModel()
    {
        $this->assertInstanceOf(Reset::class, $this->manager->getModel());
    }

    /**
     * Test creating a token when user is null
     */
    public function testCreateTokenWithNullUser()
    {
        $this->expectException(NullValueException::class);

        $this->manager->createToken(null);
    }

    /**
     * Test creating a token
     */
    public function testCreateToken()
    {
        Reset::destroy(1);

        $this->manager->createToken($this->getUserMock());

        Event::assertDispatched(Created::class, function ($event) {
            return $event->user->id === 1 && $event->token->email === 'foo@bar.com';
        });

        $this->assertDatabaseHas(config('laranixauth.password.table'), [
            'user_id'   => 1,
            'email'     => 'foo@bar.com',
        ]);
    }

    /**
     * Test sending mail with null user
     */
    public function testSendMailWithNullUser()
    {
        $this->expectException(NullValueException::class);

        $this->manager->sendMail(null, $this->getTokenMock());
    }

    /**
     * Test sending mail with unset mail
     */
    public function testSendMailWithUnsetEmail()
    {
        $this->expectException(NullValueException::class);

        $token = $this->getTokenMock();

        unset($token->email);

        $this->manager->sendMail($this->getUserMock(), $token);
    }

    /**
     * Test sending mail with unset mail
     */
    public function testSendMailWithInvalidEmail()
    {
        $this->expectException(NullValueException::class);

        $token = $this->getTokenMock();

        $token->email = 'notanemail';

        $this->manager->sendMail($this->getUserMock(), $token);
    }

    /**
     * Test sending mail
     */
    public function testSendMail()
    {
        list($config, $mailer) = $this->getMocks();

        $mailer->shouldReceive('send')->andReturnSelf();

        $manager = new Manager($config, $mailer);

        $user = $this->getUserMock();
        $token = $this->getTokenMock();

        $data = $manager->sendMail($user, $token);

        $this->assertSame($token->email, $data->to[0]['email']);
        $this->assertSame($token->token, $data->token);
        $this->assertSame('password_reset', $data->view);
        $this->assertSame(1, $data->userId);
        $this->assertSame(config('app.url') . '/password/reset?token=' . $this->hashToken('token123') . '&email=foo%40bar.com', $data->url);
        $this->assertSame(config('app.url') . '/password/reset', $data->baseurl);
    }

    /**
     * Test verify token with no row
     */
    public function testProcessTokenWithNoRow()
    {
        $this->assertSame(Token::TOKEN_INVALID, $this->manager->processToken('notoken123', 'foo@bar.com'));

        Event::assertDispatched(Failed::class, function ($event) {
             return $event->user === null && $event->token === null && $event->email === 'foo@bar.com';
        });
    }

    /**
     * Test verify valid token
     *
     * TODO Test listener for deleting session
     */
    public function testVerifyTokenWithValidToken()
    {
        $this->assertSame(
            Token::TOKEN_VALID,
            $this->manager->processToken($this->hashToken('foo123'), 'bar@bar.com', 'secret2')
        );

        Event::assertDispatched(Updated::class, function ($event) {
            return $event->user->id === 2 && password_verify('secret2', $event->user->password);
        });

        Event::assertDispatched(ResetEvent::class, function ($event) {
            return $event->user->id === 2;
        });
    }

    /**
     * Test verify valid token with wrong email
     */
    public function testVerifyTokenWithValidTokenButWrongEmail()
    {
        $this->assertSame(Token::TOKEN_INVALID, $this->manager->processToken($this->hashToken('abc123'), 'wrongmail@foo.com', 'secret2'));

        Event::assertDispatched(Failed::class, function ($event) {
             return $event->user->id === 1 && $event->token->status === Token::TOKEN_INVALID && $event->email === 'wrongmail@foo.com';
        });
    }


    /**
     * Test verify valid token with wrong email
     */
    public function testVerifyTokenWithExpiredToken()
    {
        $this->assertSame(Token::TOKEN_EXPIRED, $this->manager->processToken($this->hashToken('abcfoo'), 'foobar@foo.com', 'secret2'));

        Event::assertDispatched(Failed::class, function ($event) {
             return $event->user->id === 4 && $event->token->status === Token::TOKEN_EXPIRED && $event->email === 'foobar@foo.com';
        });
    }

    /**
     * Test renewing token
     */
    public function testRenewToken()
    {
        $this->assertNotSame($this->hashToken('abc123'), $this->manager->renewToken(Reset::find(1))->token);

        Event::assertDispatched(UpdatedEvent::class, function ($event) {
            return $event->user->id === 1 && $event->token->email === 'foo@foo.com';
        });
    }

        /**
     * Test fetching token with bad token
     */
    public function testFetchTokenWithNoMatching()
    {
        $this->assertNull($this->manager->fetchToken('nothere'));
    }

    /**
     * Test fetch token
     */
    public function testFetchToken()
    {
        $this->assertNotNull($this->manager->fetchToken($this->hashToken('abc123')));
    }

        /**
     * Test fetching token with bad token
     */
    public function testFetchTokenByEmailWithNoMatching()
    {
        $this->assertNull($this->manager->fetchTokenByEmail('noemail'));
    }

    /**
     * Test fetch token
     */
    public function testFetchTokenByEmail()
    {
        $this->assertNotNull($this->manager->fetchTokenByEmail('foo@foo.com'));
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
        $user->id = 1;
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

        $token->token = $this->hashToken('token123');
        $token->email = 'foo@bar.com';
        $token->user = $this->getUserMock();

        return $token;
    }

    /**
     * @param string $token
     * @return string
     */
    protected function hashToken(string $token)
    {
        return hash('sha256', $token);
    }
}
