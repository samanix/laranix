<?php
namespace Laranix\Tests\Browser;

use Laranix\Auth\User\User;
use Laravel\Dusk\Browser;

class RegisterTest extends BrowserTestCase
{
    /**
     * @var bool
     */
    protected $runMigrations = true;

    /**
     * @var array
     */
    protected $factories = [
        User::class => __DIR__ . '/../Factory/User',
    ];

    /**
     * Test visit register as user
     *
     * @return void
     */
    public function testVisitRegisterAsUser()
    {
        $this->createFactories();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit('/register')
                    ->assertPathIs('/home');
        });
    }

    /**
     * Test registering
     */
    public function testVisitRegisterAsGuest()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->type('first_name', 'Sam')
                    ->type('last_name', 'Anix')
                    ->type('email', 'foo@bar.com')
                    ->type('email_confirmation' ,'foo@bar.com')
                    ->type('company', 'Samanix')
                    ->type('username', 'Samanix')
                    ->type('password', 'secret')
                    ->type('password_confirmation', 'secret')
                    ->click('.ui.checkbox > label')
                    ->assertChecked('terms')
                    ->click('#submit-register-form')
                    ->assertPathIs('/register/success')
                    ->assertSee('Thanks, Samanix')
                    ->assertSee('You have registered successfully.');
        });
    }

    /**
     * Test register with no input
     */
    public function testVisitRegisterAsGuestWithNoInput()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->click('#submit-register-form')
                    ->pause(2000)
                    ->assertSee('Please enter your first name')
                    ->assertSee('Please enter your last name')
                    ->assertSee('Please enter a valid email')
                    ->assertSee('Please enter a valid username')
                    ->assertSee('Password must be at least 6 characters')
                    ->assertSee('You must accept the terms if you wish to register');
        });
    }

    /**
     * Test register with bad input
     */
    public function testVisitRegisterAsGuestWithBadInput()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->type('email', 'foo')
                    ->type('email_confirmation', 'bar')
                    ->type('username', 'Invalid$')
                    ->type('password', 'sec')
                    ->type('password_confirmation', 'secret')
                    ->click('#submit-register-form')
                    ->pause(2000)
                    ->assertSee('Please enter a valid email')
                    ->assertSee('Email does not match')
                    ->assertSee('Please enter a valid username')
                    ->assertSee('Password must be at least 6 characters')
                    ->assertSee('Passwords do not match');
        });
    }
}
