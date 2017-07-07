<?php
namespace Tests\Http\Controllers\Auth\Email\Verification;

use Laranix\Auth\Email\Events\Updated;
use Laranix\Auth\Email\Verification\Events\VerifyAttempt;
use Laranix\Auth\Email\Verification\Events\Updated as TokenUpdated;
use Laranix\Auth\Email\Verification\Events\Failed;
use Laranix\Auth\Email\Verification\Events\RefreshAttempt;
use Laranix\Auth\Email\Verification\Events\Verified;
use Laranix\Auth\Email\Verification\Verification;
use Laranix\Auth\User\Token\Token;
use Laranix\Auth\User\User;
use Tests\LaranixTestCase;
use Laranix\Support\IO\Url\Url;
use Illuminate\Support\Facades\Mail;
use Laranix\Auth\Email\Verification\Mail as VerificationMail;
use Illuminate\Support\Facades\Event;

class VerificationTest extends LaranixTestCase
{
    /**
     * @var bool
     */
    protected $runMigrations = true;

    /**
     * @var array
     */
    protected $factories = [
        User::class         => __DIR__ . '/../../../../../Factory/User',
        Verification::class => __DIR__ . '/../../../../../Factory/Email/Verification',
    ];

    /**
     * Test when accessed with no data
     */
    public function testGetVerifyShowsFormWhenAccessedWithNoParams()
    {
        $response = $this->get('email/verify');

        $response->assertStatus(200);

        $response->assertViewHas(['token' => null, 'email' => null]);
    }

    /**
     * Test when accessed with token only
     */
    public function testGetVerifyShowsFormWhenMissingEmail()
    {
        $token = hash('sha256', str_random());

        $response = $this->get('email/verify?token=' . $token);

        $response->assertStatus(200);

        $response->assertViewHas(['token' => $token, 'email' => null]);
    }

    /**
     * Test when accessed with email only
     */
    public function testGetVerifyShowsFormWhenMissingToken()
    {
        $email = 'foo@bar.com';

        $response = $this->get('email/verify' . '?email=' . rawurlencode($email));

        $response->assertStatus(200);

        $response->assertViewHas(['token' => null, 'email' => $email]);
    }

    /**
     * Test when email is given but invalid
     */
    public function testGetVerifyShowsFormWhenEmailIsInvalid()
    {
        $email = 'notanemail';
        $token = hash('sha256', str_random());

        $response = $this->get('email/verify?token=' . $token . '&email=' . rawurlencode($email));

        $response->assertStatus(200);

        $response->assertViewHas(['token' => $token, 'email' => $email]);
    }

    /**
     * Test get verify when row does not exists
     */
    public function testGetVerifyRedirectsWithNullRow()
    {
        $email = 'not@email.com';
        $token = hash('sha256', str_random());

        $response = $this->get('email/verify?token=' . $token . '&email=' . rawurlencode($email));

        $response->assertStatus(302);

        $response->assertRedirect('email/verify/result');

        $response->assertSessionHas([
            'verification_notice_header'   => 'Email Verification Error',
            'verification_notice_message'  => 'The provided information does not match our records',
            'verification_notice_is_error' => true,
        ]);

        Event::assertDispatched(VerifyAttempt::class, function ($event) use ($email) {
           return $event->email === $email;
        });

        Event::assertDispatched(Failed::class, function ($event) use ($email) {
            return $event->user === null && $event->token === null && $event->email === $email;
        });
    }

    /**
     * Test get verify when row does not exists
     */
    public function testGetVerifyRedirectsWithInvalidEmailSupplied()
    {
        $this->createFactories();

        $email = 'nofoo@foo.com';
        $token = hash('sha256', 'foo123');

        $response = $this->get('email/verify?token=' . $token . '&email=' . rawurlencode($email));

        Event::assertDispatched(Failed::class, function ($event) use ($email) {
            return $event->email === $email && $event->token->status === Token::TOKEN_INVALID;
        });

        $response->assertStatus(302);

        $response->assertRedirect('email/verify/result');

        $response->assertSessionHas([
            'verification_notice_header'   => 'Email Verification Error',
            'verification_notice_message'  => 'The provided information does not match our records',
            'verification_notice_is_error' => true,
        ]);
    }

    /**
     * Test successful verification when guest
     */
    public function testGetVerifyRedirectsWithValidToken()
    {
        $this->createFactories();

        $email = 'foo2@bar.com';
        $token = hash('sha256', 'abc123');

        $response = $this->get('email/verify?token=' . $token . '&email=' . rawurlencode($email));

        $response->assertStatus(302);

        $response->assertRedirect('email/verify/result');

        $response->assertSessionHas([
            'verification_notice_header'   => 'Email Verified',
            'verification_notice_message'  => 'Your email has been verified, you may now <a href="' . Url::to('login') . '">login</a>',
            'verification_notice_is_error' => false,
        ]);

        Event::assertDispatched(VerifyAttempt::class, function ($event) use ($email) {
           return $event->email === $email;
        });

        Event::assertDispatched(Updated::class, function ($event) {
           return $event->user->id === 1 && $event->user->email === 'foo2@bar.com';
        });

        Event::assertDispatched(Verified::class, function ($event) {
            return $event->user->id === 1 && $event->user->email === 'foo2@bar.com';
        });
    }

    /**
     * Test successful verification when logged in
     */
    public function testGetVerifyRedirectsWithValidTokenWhenLoggedIn()
    {
        $this->createFactories();

        $email = 'foo2@bar.com';
        $token = hash('sha256', 'abc123');

        $response = $this->actingAs(new User(['user_id' => 1]))->get('email/verify?token=' . $token . '&email=' . rawurlencode($email));

        $response->assertStatus(302);

        $response->assertRedirect('email/verify/result');

        $response->assertSessionHas([
            'verification_notice_header'   => 'Email Verified',
            'verification_notice_message'  => 'Your email has been updated',
            'verification_notice_is_error' => false,
        ]);

        $this->assertDatabaseMissing(config('laranixauth.verification.table', 'email_verification'), [
            'email' => $email,
            'token' => $token,
        ]);

        Event::assertDispatched(VerifyAttempt::class, function ($event) use ($email) {
           return $event->email === $email;
        });

         Event::assertDispatched(Updated::class, function ($event) {
           return $event->user->id === 1 && $event->user->email === 'foo2@bar.com';
        });

        Event::assertDispatched(Verified::class, function ($event) {
            return $event->user->id === 1 && $event->user->email === 'foo2@bar.com';
        });
    }

    /**
     * Test successful verification when logged in
     */
    public function testGetVerifyRedirectsWithExpiredValidToken()
    {
        $this->createFactories();

        $email = 'baz2@bar.com';
        $token = hash('sha256', 'abcfoo');

        $response = $this->get('email/verify?token=' . $token . '&email=' . rawurlencode($email));

        $response->assertStatus(302);

        $response->assertRedirect('email/verify/result');

        $response->assertSessionHas([
            'verification_notice_header'   => 'Email Verification Error',
            'verification_notice_message'  => 'Your token has expired, please <a href="' . Url::to('email/verify/refresh') . '">request a new one</a>',
            'verification_notice_is_error' => true,
        ]);

        Event::assertDispatched(VerifyAttempt::class, function ($event) use ($email) {
           return $event->email === $email;
        });

        Event::assertDispatched(Failed::class, function ($event) use ($email, $token) {
            return $event->user->id === 4 && $event->token->token === $token &&
                $event->token->status === Token::TOKEN_EXPIRED && $event->email === $email;
        });
    }

    /**
     * Covered by other tests
     */
    public function testPostVerify()
    {
        return true;
    }

    /**
     * Test get verify result with missing session data
     */
    public function testGetVerifyResultWithMissingSessionData()
    {
        $response = $this->get('email/verify/result');

        $response->assertStatus(403);
    }

    /**
     * Test get verify result with missing session data
     */
    public function testGetVerifyResultWithMissingHeader()
    {
        $response = $this->withSession([
            'verification_notice_message'   => 'bar',
            'verification_notice_is_error'  => true,
        ])->get('email/verify/result');

        $response->assertStatus(403);
    }

    /**
     * Test get verify result with missing session data
     */
    public function testGetVerifyResultWithMissingMessage()
    {
        $response = $this->withSession([
            'verification_notice_header'    => 'foo',
            'verification_notice_is_error'  => true,
        ])->get('email/verify/result');

        $response->assertStatus(403);
    }

    /**
     * Test get verify result with missing session data
     */
    public function testGetVerifyResultWithMissingIsError()
    {
        $response = $this->withSession([
            'verification_notice_header'    => 'foo',
            'verification_notice_message'   => 'bar',
        ])->get('email/verify/result');

        $response->assertStatus(403);
    }

    /**
     * Test get verify result
     */
    public function testGetVerifyResult()
    {
        $response = $this->withSession([
            'verification_notice_header'    => 'foo',
            'verification_notice_message'   => 'bar',
            'verification_notice_is_error'  => true,
        ])->get('email/verify/result');

        $response->assertStatus(200);

        $response->assertViewHas([
            'page_title'    => 'foo',
            'header'        => 'foo',
            'message'       => 'bar'
        ]);
    }

    /**
     * Test get verification refresh form
     */
    public function testGetVerificationRefreshForm()
    {
        $response = $this->get('email/verify/refresh');

        $response->assertStatus(200);

        $response->assertViewHas([
            'verify_refresh_message'    => null,
        ]);
    }

    /**
     * Test with invalid user
     */
    public function testPostVerificationRefreshFormWithInvalidUser()
    {
        $this->createFactories();

        $response = $this->post('email/verify/refresh', ['email' => 'not@here.com'], ['HTTP_REFERER' => Url::to('email/verify/refresh')]);

        $response->assertStatus(302);

        $response->assertRedirect('email/verify/refresh');

        $response->assertSessionHas([
            'verify_refresh_message' => 'If the email is registered in our system, you will receive an email with a new verification code shortly',
        ]);

        Event::assertDispatched(RefreshAttempt::class, function ($event) {
           return $event->email === 'not@here.com';
        });

        Mail::assertNotSent(VerificationMail::class);
    }

    /**
     * Test with valid user
     */
    public function testPostVerificationRefreshFormWithValidUser()
    {
        $this->withoutMiddleware();

        $this->createFactories();

        $response = $this->post('email/verify/refresh', ['email' => 'foo2@bar.com'], ['HTTP_REFERER' => Url::to('email/verify/refresh')]);

        $response->assertStatus(302);

        $response->assertRedirect('email/verify/refresh');

        $response->assertSessionHas([
            'verify_refresh_message' => 'If the email is registered in our system, you will receive an email with a new verification code shortly',
        ]);

        Event::assertDispatched(RefreshAttempt::class, function ($event) {
           return $event->email === 'foo2@bar.com';
        });

        Event::assertDispatched(TokenUpdated::class, function ($event) {
            return $event->user->id == 1 && $event->token->email === 'foo2@bar.com';
        });

        Mail::assertSent(VerificationMail::class, function ($mail) {
            return $mail->hasTo('foo2@bar.com');
        });
    }
}
