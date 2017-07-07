<?php

namespace Tests\Browser;

use Laranix\Auth\User\User;
use Laravel\Dusk\Browser;

class LoginTest extends BrowserTestCase
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
     * Test visiting login - JS removes/adds classes as needed
     */
    public function testVisitLogin()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->assertSourceHas('<input type="email" name="email" placeholder="my@email.com" value="" />')
                    ->assertSourceHas('<input type="password" name="password" placeholder="Password" />')
                    ->assertSourceHas('<input type="checkbox" name="remember" tabindex="0" id="remember-me" class="hidden" />');

        });
    }

    /**
     * Test logging in
     */
    public function testVisitLoginAndSubmit()
    {
        $this->createFactories();

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->type('email', 'foo@bar.com')
                    ->type('password', 'secret')
                    ->pause(2000)
                    ->click('#submit-login-form')
                    ->assertPathIs('/')
                    ->assertSee('Welcome back, foo')
                    ->assertSee('You have been logged in successfully');
        });
    }

    /**
     * Test logging in
     */
    public function testLoginWithBadInput()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->type('email', 'foo')
                    ->type('password', '1')
                    ->click('#submit-login-form')
                    ->pause(2000)
                    ->assertSee('Please enter a valid email')
                    ->assertSee('Password must be at least 6 characters');
        });
    }

    /**
     * Test visiting home and logging out
     */
    public function testLogout()
    {
        $this->createFactories();

        $this->browse(function (Browser $browser) {
           $browser->loginAs(User::find(1))
                   ->visit('/')
                   ->click('#account-dropdown')
                   ->pause(2000)
                   ->click('#submit-logout-form')
                   ->assertSee('See you soon, foo')
                   ->assertSee('You have been logged out')
                   ->assertPathIs('/login');
        });
    }
}
