<?php
namespace Laranix\Tests\Http\Controllers\Auth;

use Laranix\Auth\Password\Events\Updated;
use Laranix\Auth\Password\Reset\Events\VerifyAttempt;
use Laranix\Auth\Password\Reset\Events\Failed;
use Laranix\Auth\Password\Reset\Events\Reset as ResetEvent;
use Laranix\Auth\User\Token\Token;
use Laranix\Auth\User\User;
use Laranix\Auth\Password\Reset\Reset;
use Laranix\Support\IO\Url\Url;
use Laranix\Tests\LaranixTestCase;
use Illuminate\Support\Facades\Event;

class ResetTest extends LaranixTestCase
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
        Reset::class   => __DIR__ . '/../../../../../Factory/Password/Reset',
    ];

    /**
     * Test get password reset with no data
     */
    public function testGetPasswordResetFormWithNoData()
    {
        $response = $this->get('password/reset');

        $response->assertStatus(200);

        $response->assertViewHas('token', function($value) {
           return $value === null;
        });

        $response->assertViewHas('email', '');

        $response->assertViewHas(['sequence', 'recaptcha']);
    }

    /**
     * Test get password reset form with token+email
     */
    public function testGetPasswordResetFormWithTokenAndEmail()
    {
        $token = hash('sha256', 'abc123');

        $response = $this->get('password/reset/?token=' . $token . '&email=' . rawurlencode('foo@bar.com'));

        $response->assertStatus(200);

        $response->assertViewHas('token', $token);

        $response->assertViewHas('email', 'foo@bar.com');
    }

    /**
     * Test post password forgot form
     */
    public function testPostPasswordResetFormWithNullRow()
    {
        $this->createFactories();

        $email = 'nofoo@foo.com';
        $token = hash('sha256', 'noabc123');

        $response = $this->post('password/reset', [
            'email' => $email,
            'token' => $token,
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ]);

        Event::assertDispatched(Failed::class, function ($event) use ($email) {
            return $event->email === $email && $event->token === null;
        });

        $response->assertStatus(302);

        $response->assertRedirect('password/reset/error');

        $response->assertSessionHas([
            'password_reset_error_message'   => 'The provided information does not match our records.',
        ]);
    }

    /**
     * Test post password forgot form
     */
    public function testPostPasswordResetFormWithInvalidEmailSupplied()
    {
        $this->createFactories();

        $email = 'nofoo@foo.com';
        $token = hash('sha256', 'foo123');

        $response = $this->post('password/reset', [
            'email' => $email,
            'token' => $token,
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ]);

        Event::assertDispatched(Failed::class, function ($event) use ($email) {
            return $event->email === $email && $event->token->status === Token::TOKEN_INVALID;
        });

        $response->assertStatus(302);

        $response->assertRedirect('password/reset/error');

        $response->assertSessionHas([
            'password_reset_error_message'   => 'The provided information does not match our records.',
        ]);
    }

        /**
     * Test post password forgot form
     */
    public function testPostPasswordResetFormWithExpiredToken()
    {
        $this->createFactories();

        $email = 'foobar@foo.com';
        $token = hash('sha256', 'abcfoo');

        $response = $this->post('password/reset', [
            'email' => $email,
            'token' => $token,
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ]);

        Event::assertDispatched(Failed::class, function ($event) use ($email) {
            return $event->email === $email && $event->token->status === Token::TOKEN_EXPIRED;
        });

        $response->assertStatus(302);

        $response->assertRedirect('password/reset/error');

        $response->assertSessionHas([
            'password_reset_error_message'   => 'Your token has expired, please <a href="' . Url::to('password/forgot') . '">request a new one</a>',
        ]);
    }

    /**
     * Test post password forgot form
     */
    public function testPostPasswordResetForm()
    {
        $this->createFactories();

        $email = 'foo@foo.com';
        $token = hash('sha256', 'abc123');

        $response = $this->post('password/reset', [
            'email' => $email,
            'token' => $token,
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ]);

        Event::assertDispatched(VerifyAttempt::class, function ($event) use ($email) {
            return $event->email === $email;
        });

        Event::assertDispatched(Updated::class, function ($event) {
            return $event->user->email === 'foo@bar.com';
        });

        Event::assertDispatched(ResetEvent::class, function ($event) {
            return $event->user->id === 1;
        });

        $response->assertStatus(302);

        $response->assertRedirect('login');

        $response->assertSessionHas([
            'login_notice'          => true,
            'login_notice_header'   => 'Password Reset Successfully',
            'login_notice_message'  => 'Your password has been reset, you may now login using your new credentials',
            'login_notice_is_error' => false,
        ]);
    }
}
