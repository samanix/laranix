<?php
namespace Tests\Browser;

use Laranix\Auth\Password\Reset\Reset;
use Laranix\Auth\User\User;
use Laravel\Dusk\Browser;

class ResetTest extends BrowserTestCase
{
    /**
     * @var bool
     */
    protected $runMigrations = true;

    /**
     * @var array
     */
    protected $factories = [
        User::class     => __DIR__ . '/../Factory/User',
        Reset::class    => __DIR__ . '/../Factory/Password/Reset',
    ];

    /**
     * Test visit password reset
     *
     * @return void
     */
    public function testVisitPasswordReset()
    {
        $this->browse(function (Browser $browser, Browser $browser2, Browser $browser3) {
            $browser->visit('/password/reset')
                    ->assertSee('Password Reset');

            $browser2->visit('/password/reset?token=token123')
                     ->assertValue('input[name=token]', 'token123')
                     ->assertValue('input[name=email]', '');

            $browser3->visit('/password/reset?email=foo%40bar.com')
                     ->assertValue('input[name=token]', '')
                     ->assertValue('input[name=email]', 'foo@bar.com');
        });
    }

    /**
     * Test visiting password reset and entering bad input
     */
    public function testVisitPasswordResetAndFillOutFormWithBadInput()
    {
        $this->browse(function (Browser $browser, Browser $browser2) {
            $browser->visit('/password/reset')
                    ->click('#submit-pass-reset-form')
                    ->pause(2000)
                    ->assertSee('Invalid token')
                    ->assertSee('Please enter a valid email')
                    ->assertSee('Password must be at least 6 characters');

            $browser2->visit('/password/reset')
                     ->type('token', 'badvalue')
                     ->type('email', 'badmail')
                     ->type('password', 'secret')
                     ->type('password_confirmation', 'secret2')
                     ->click('#submit-pass-reset-form')
                     ->pause(5000)
                     ->assertSee('Invalid token')
                     ->assertSee('Please enter a valid email')
                     ->assertSee('Passwords do not match');
        });
    }

    /**
     * Test visiting password reset and entering valid input
     */
    public function testVisitPasswordResetAndFillOutForm()
    {
        $this->createFactories();

        $this->browse(function (Browser $browser) {
            $browser->visit('/password/reset')
                    ->type('token', hash('sha256', 'abc123'))
                    ->type('email', 'foo@foo.com')
                    ->type('password', 'secret')
                    ->type('password_confirmation', 'secret')
                    ->click('#submit-pass-reset-form')
                    ->assertPathIs('/login')
                    ->assertSee('Password Reset Successfully')
                    ->assertSee('Your password has been reset, you may now login using your new credentials');
        });
    }

    /**
     * Test visit password reset when token has expired or does not match existing records
     */
    public function testVisitPasswordResetWithNoValidRow()
    {
        $this->createFactories();

        $this->browse(function (Browser $browser, Browser $browser2, Browser $browser3) {
            $browser->visit('/password/reset?token=' . hash('sha256', 'abcfoo') . '&email=foobar%40foo.com')
                    ->type('password', 'secret')
                    ->type('password_confirmation', 'secret')
                    ->click('#submit-pass-reset-form')
                    ->assertPathIs('/password/reset/error')
                    ->assertSee('Password Reset Error')
                    ->assertSee('Your token has expired, please request a new one');

            $browser2->visit('/password/reset?token=' . hash('sha256', 'abc123'))
                     ->type('email', 'badmail@nomail.com')
                     ->type('password', 'secret')
                     ->type('password_confirmation', 'secret')
                     ->click('#submit-pass-reset-form')
                     ->assertPathIs('/password/reset/error')
                     ->assertSee('Password Reset Error')
                     ->assertSee('The provided information does not match our records');

            $browser3->visit('/password/reset')
                     ->type('token', hash('sha256', 'notoken'))
                     ->type('email', 'nomail@nomail.com')
                     ->type('password', 'secret')
                     ->type('password_confirmation', 'secret')
                     ->click('#submit-pass-reset-form')
                     ->assertPathIs('/password/reset/error')
                     ->assertSee('Password Reset Error')
                     ->assertSee('The provided information does not match our records');
        });
    }
}
