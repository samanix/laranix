<?php
namespace Laranix\Tests\Http\Controllers\Auth;

use Illuminate\Auth\Events\Registered;
use Laranix\Auth\Group\Group;
use Laranix\Auth\User\Events\Created;
use Laranix\Auth\User\Groups\Events\Added;
use Laranix\Auth\User\User;
use Laranix\Tests\Http\HasSharedViewVariable;
use Laranix\Tests\LaranixTestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Laranix\Auth\Email\Verification\Mail as VerificationMail;

class RegisterTest extends LaranixTestCase
{
    use HasSharedViewVariable;

    /**
     * @var bool
     */
    protected $runMigrations = true;

    /**
     * @var array
     */
    protected $factories = [
        Group::class         => __DIR__ . '/../../../Factory/Group',
    ];

    /**
     * Test get login page
     */
    public function testGetRegister()
    {
        $response = $this->get('register');

        $response->assertStatus(200);

        $this->assertTrue($this->hasSharedViewVariables('sequence', 'recaptcha'));
    }

    /**
     * Get register success page with a null value
     */
    public function testGetRegisterSuccessWithNullValue()
    {
        $data = [
            'registered_username' => 'foo',
            'registered_email' => null,
            'token_expiry' => '2nd July 2017 12:00:00PM GMT',
            'token_valid_for' => '60 minutes',
        ];

        $response = $this->withSession($data)->get('register/success');

        $response->assertStatus(302);

        $response->assertRedirect('register');
    }

    /**
     * Test post register form
     */
    public function testPostRegister()
    {
        $this->createFactories();

        $data = [
            'first_name'            => 'foo',
            'last_name'             => 'bar',
            'email'                 => 'foo@bar.com',
            'email_confirmation'    => 'foo@bar.com',
            'company'               => 'Foo Co',
            'username'              => 'baz',
            'password'              => 'secret',
            'password_confirmation' => 'secret',
            'terms'                 => true,
        ];

        $response = $this->post('register', $data);
        $response->assertStatus(302);
        $response->assertRedirect('register/success');

        $user = User::where('username', 'baz')->first();

        $this->assertNotNull($user);

        $response->assertSessionHas([
            'registered_username'   => $user->username,
            'registered_email'      => $user->email,
            'token_valid_for'       => '1 hour',
        ]);

        Event::assertDispatched(Created::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });

        Event::assertDispatched(Added::class, function ($event) use ($user) {
            return $event->usergroup->group_id === 3 && $event->usergroup->user_id === $user->id;
        });

        Event::assertDispatched(Registered::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });

        Mail::assertSent(VerificationMail::class, function ($mail) {
            return $mail->hasTo('foo@bar.com');
        });
    }

    /**
     * Get register success page
     */
    public function testGetRegisterSuccess()
    {
        $data = [
            'registered_username'   => 'foo',
            'registered_email'      => 'foo@bar.com',
            'token_expiry'          => '2nd July 2017 12:00:00PM GMT',
            'token_valid_for'       => '60 minutes',
        ];

        $response = $this->withSession($data)->get('register/success');

        $response->assertStatus(200);

        $response->assertViewHas($data);
    }
}
