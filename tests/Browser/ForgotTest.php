<?php
namespace Tests\Browser;

use Laravel\Dusk\Browser;

class ForgotTest extends BrowserTestCase
{
    /**
     * @var bool
     */
    protected $runMigrations = true;

    /**
     * Test visiting password forgot and entering bad input
     */
    public function testVisitPasswordForgotAndFillOutFormWithBadInput()
    {
        $this->browse(function (Browser $browser, Browser $browser2) {
            $browser->visit('/password/forgot')
                    ->click('#submit-pass-forgot-form')
                    ->pause(2000)
                    ->assertSee('Please enter a valid email');

            $browser2->visit('/password/forgot')
                    ->type('email', 'badmail')
                    ->click('#submit-pass-forgot-form')
                    ->pause(2000)
                    ->assertSee('Please enter a valid email');
        });
    }

    /**
     * Test visiting password forgot and entering valid input
     */
    public function testVisitVerifyAndFillOutForm()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/password/forgot')
                    ->type('email', 'foo@bar.com')
                    ->click('#submit-pass-forgot-form')
                    ->assertPathIs('/password/forgot')
                    ->assertSee('If the email is registered in our system, you will receive an email with instructions to reset your password shortly');
        });
    }
}
