<?php
namespace Laranix\Tests\Http\Controllers\Auth;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laranix\Auth\Password\Reset\Events\ForgotAttempt;
use Laranix\Auth\Password\Reset\Mail as ResetMail;
use Laranix\Auth\Password\Reset\Events\Created;
use Laranix\Auth\User\User;
use Laranix\Tests\Http\HasSharedViewVariable;
use Laranix\Tests\LaranixTestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

class ForgotTest extends LaranixTestCase
{
    use DatabaseMigrations, HasSharedViewVariable;

    /**
     * @var bool
     */
    protected $runMigrations = true;

    /**
     * @var array
     */
    protected $factories = [
        User::class         => __DIR__ . '/../../../../../Factory/User',
    ];

    /**
     * Test get login page
     */
    public function testGetPasswordForgotForm()
    {
        $response = $this->get('password/forgot');

        $response->assertStatus(200);

        $response->assertViewHas('forgot_password_message');

        $this->assertTrue($this->hasSharedViewVariables('sequence', 'recaptcha'));
    }

    /**
     * Test post password forgot form
     */
    public function testPostPasswordForgotFormWithNullUser()
    {
        $this->createFactories();

        $response = $this->post('password/forgot', ['email' => 'nofoo@bar.com']);

        Event::assertDispatched(ForgotAttempt::class, function ($event) {
            return $event->email === 'nofoo@bar.com';
        });

        Mail::assertNotSent(ResetMail::class);

        Event::assertNotDispatched(Created::class);

        $response->assertStatus(302);

        $response->assertRedirect('password/forgot');

        $response->assertSessionHas('forgot_password_message',
                                    'If the email is registered in our system, you will receive an email with instructions to reset your password shortly');
    }

    /**
     * Test post password forgot form
     */
    public function testPostPasswordForgotForm()
    {
        $this->createFactories();

        $response = $this->post('password/forgot', ['email' => 'foo@bar.com']);

        Event::assertDispatched(ForgotAttempt::class, function ($event) {
            return $event->email === 'foo@bar.com';
        });

        Mail::assertQueued(ResetMail::class, function ($mail) {
            return $mail->hasTo('foo@bar.com');
        });

        Event::assertDispatched(Created::class, function ($event) {
            return $event->user->email === 'foo@bar.com';
        });

        $response->assertStatus(302);

        $response->assertRedirect('password/forgot');

        $response->assertSessionHas('forgot_password_message',
                                    'If the email is registered in our system, you will receive an email with instructions to reset your password shortly');
    }
}
