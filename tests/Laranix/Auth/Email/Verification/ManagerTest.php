<?php
namespace Laranix\Tests\Laranix\Auth\Email\Verification;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Mail\Mailer;
use Laranix\Auth\Email\Events\Updated;
use Laranix\Auth\Email\Verification\Events\Created;
use Laranix\Auth\Email\Verification\Events\Failed;
use Laranix\Auth\Email\Verification\Events\Verified;
use Laranix\Auth\Email\Verification\Manager;
use Laranix\Auth\Email\Verification\Verification;
use Laranix\Auth\User\Token\Token;
use Laranix\Auth\User\User;
use Laranix\Support\Exception\EmailExistsException;
use Laranix\Support\Exception\NullValueException;
use Laranix\Tests\LaranixTestCase;
use Mockery as m;
use Illuminate\Support\Facades\Event;
use Laranix\Auth\Email\Verification\Events\Updated as UpdatedEvent;


/**
 * @see \Laranix\Tests\Laranix\Auth\Password\Reset\ManagerTest
 * @see \Laranix\Tests\Laranix\Auth\User\Token\ManagerTest
 */
class ManagerTest extends LaranixTestCase
{
    /**
     * @var \Laranix\Auth\Password\Reset\Manager
     */
    protected $manager;

    /**
     * @var bool
     */
    protected $runMigrations = true;

    /**
     * @var array
     */
    protected $factories = [
        User::class         => __DIR__ . '/../../../../Factory/User',
        Verification::class =>__DIR__ . '/../../../../Factory/Email/Verification'
    ];

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
        $this->assertInstanceOf(Verification::class, $this->manager->getModel());
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
        Verification::destroy(1);

        $this->manager->createToken($this->getUserMock());

        Event::assertDispatched(Created::class, function ($event) {
            return $event->user->user_id === 1 && $event->token->email === 'foo@bar.com';
        });

        $this->assertDatabaseHas(config('laranixauth.verification.table'), [
            'user_id'   => 1,
            'email'     => 'foo@bar.com',
        ]);
    }

    /**
     * Test create token when email for token exists in user table
     */
    public function testCreateTokenWithDuplicateUserEmail()
    {
        $this->expectException(EmailExistsException::class);

        Verification::destroy(1);

        $this->manager->createToken($this->getUserMock(), 'bar@baz.com');
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
        $this->assertSame('verification', $data->view);
        $this->assertSame(1, $data->userId);
        $this->assertSame(config('app.url') . '/email/verify?token=' . $this->hashToken('token123') . '&email=foo2%40bar.com', $data->url);
        $this->assertSame(config('app.url') . '/email/verify', $data->baseurl);
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
     */
    public function testVerifyTokenWithValidToken()
    {
        $this->assertSame(Token::TOKEN_VALID, $this->manager->processToken($this->hashToken('foo123'), 'bar2@baz.com'));

        Event::assertDispatched(Updated::class, function ($event) {
            return $event->user->user_id === 2 && $event->user->email === 'bar2@baz.com';
        });

        Event::assertDispatched(Verified::class, function ($event) {
            return $event->user->user_id === 2 && $event->user->email === 'bar2@baz.com';
        });
    }

    /**
     * Test verify valid token with wrong email
     */
    public function testVerifyTokenWithValidTokenButWrongEmail()
    {
        $this->assertSame(Token::TOKEN_INVALID, $this->manager->processToken($this->hashToken('abc123'), 'wrongmail@foo.com'));

        Event::assertDispatched(Failed::class, function ($event) {
             return $event->user->id === 1 && $event->token->status === Token::TOKEN_INVALID && $event->email === 'wrongmail@foo.com';
        });
    }


    /**
     * Test verify valid token with wrong email
     */
    public function testVerifyTokenWithExpiredToken()
    {
        $this->assertSame(Token::TOKEN_EXPIRED, $this->manager->processToken($this->hashToken('abcfoo'), 'baz2@bar.com', 'secret2'));

        Event::assertDispatched(Failed::class, function ($event) {
             return $event->user->user_id === 4 && $event->token->status === Token::TOKEN_EXPIRED && $event->email === 'baz2@bar.com';
        });
    }

    /**
     * Test renewing token
     */
    public function testRenewToken()
    {
        $this->assertNotSame($this->hashToken('abc123'), $this->manager->renewToken(Verification::find(1))->token);

        Event::assertDispatched(UpdatedEvent::class, function ($event) {
            return $event->user->user_id === 1 && $event->token->email === 'foo2@bar.com';
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
        $this->assertNotNull($this->manager->fetchTokenByEmail('foo2@bar.com'));
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
                    'verification'  => [
                        'table'     => 'email_verification',
                        'route'     => 'email.verify',    // Route name to verify token
                        'expiry'    => 60,          // Time in minutes before token expires

                        'mail'      => [
                            'view'      => 'verification',
                            'subject'   => 'Laranix Email Verification',
                            'markdown'  => true,
                        ],

                        'views'     => [
                            'verify_form'       => 'auth.verify.verify',
                            'verify_refresh'    => 'auth.verify.refresh',
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
        $user->user_id = 1;
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
        $token->email = 'foo2@bar.com';
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
