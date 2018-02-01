<?php
namespace Laranix\Tests\Browser;

use Laranix\Auth\User\User;
use Laravel\Dusk\Browser;

class HomeTest extends BrowserTestCase
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
     * Test visit home as guest
     *
     * @return void
     */
    public function testVisitHomeAsGuest()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSeeIn('#content-wrapper', 'WELCOME')
                    ->assertSeeIn('.main-menu', 'Login');
        });
    }

    /**
     * Test visiting home as logged in user
     */
    public function testVisitHomeAsUser()
    {
        $this->createFactories();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit('/')
                    ->assertSeeIn('#account-dropdown', 'foo')
                    ->click('#account-dropdown')
                    ->assertInputValue('#submit-logout-form', 'Logout');
        });
    }

    /**
     * Test js has added/removed classes as required
     */
    public function testMenuHasActiveClassAndRemovedSimpleClass()
    {
        $this->createFactories();

        $this->browse(function (Browser $browser, Browser $browser2) {
            $browser->visit('/')
                    ->assertSourceHas('<a class="item active" href="http://homestead.test">Home</a>');

            $browser2->loginAs(1)
                     ->visit('/')
                     ->assertSourceHas('<div class="ui right dropdown item" id="account-dropdown" tabindex="0">');
        });
    }
}
