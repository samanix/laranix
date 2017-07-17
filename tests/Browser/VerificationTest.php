<?php
namespace Laranix\Tests\Browser;

use Laranix\Auth\Email\Verification\Verification;
use Laranix\Auth\User\User;
use Laravel\Dusk\Browser;

class VerificationTest extends BrowserTestCase
{
    /**
     * @var bool
     */
    protected $runMigrations = true;

    /**
     * @var array
     */
    protected $factories = [
        User::class         => __DIR__ . '/../Factory/User',
        Verification::class => __DIR__ . '/../Factory/Email/Verification',
    ];

    /**
     * Test visit verify
     *
     * @return void
     */
    public function testVisitVerify()
    {
        $this->browse(function (Browser $browser, Browser $browser2, Browser $browser3) {
            $browser->visit('/email/verify')
                    ->assertSee('Verify Email');

            $browser2->visit('/email/verify?token=token123')
                     ->assertValue('input[name=token]', 'token123')
                     ->assertValue('input[name=email]', '');

            $browser3->visit('/email/verify?email=foo%40bar.com')
                     ->assertValue('input[name=token]', '')
                     ->assertValue('input[name=email]', 'foo@bar.com');
        });
    }

    /**
     * Test visiting verify with params set in url
     */
    public function testVisitVerifyWithParamsSet()
    {
        $this->createFactories();

        $this->browse(function (Browser $browser, Browser $browser2) {
            $browser->visit('/email/verify?token=' . hash('sha256', 'foobar') . '&email=foo2%40baz.com')
                    ->assertPathIs('/email/verify/result')
                    ->assertSee('Email Verified')
                    ->assertSee('Your email has been verified, you may now login');

            $browser2->loginAs(User::find(2))
                     ->visit('/email/verify?token=' . hash('sha256', 'foo123') . '&email=bar2%40baz.com')
                     ->assertPathIs('/email/verify/result')
                     ->assertSee('Email Verified')
                     ->assertSee('Your email has been updated');
        });
    }

    /**
     * Test visiting verify and entering bad input
     */
    public function testVisitVerifyAndFillOutFormWithBadInput()
    {
        $this->browse(function (Browser $browser, Browser $browser2) {
            $browser->visit('/email/verify/')
                    ->click('#submit-verify-email-form')
                    ->pause(2000)
                    ->assertSee('Invalid token')
                    ->assertSee('Please enter a valid email');

            $browser2->visit('/email/verify')
                     ->type('token', 'badvaluie')
                     ->type('email', 'badmail')
                     ->click('#submit-verify-email-form')
                     ->pause(2000)
                     ->assertSee('Invalid token')
                     ->assertSee('Please enter a valid email');
        });
    }

    /**
     * Test visiting verify and entering bad input
     */
    public function testVisitVerifyAndFillOutForm()
    {
        $this->createFactories();

        $this->browse(function (Browser $browser, Browser $browser2) {
            $browser->loginAs(User::find(2))
                    ->visit('/email/verify')
                    ->type('token', hash('sha256', 'foo123'))
                    ->type('email', 'bar2@baz.com')
                    ->click('#submit-verify-email-form')
                    ->assertPathIs('/email/verify/result')
                    ->assertSee('Email Verified')
                    ->assertSee('Your email has been updated');

            $browser2->visit('/email/verify')
                     ->type('token', hash('sha256', 'foobar'))
                     ->type('email', 'foo2@baz.com')
                     ->click('#submit-verify-email-form')
                     ->assertPathIs('/email/verify/result')
                     ->assertSee('Email Verified')
                     ->assertSee('Your email has been verified, you may now login');
        });
    }

    /**
     * Test visit verify when token has expired or does not match existing records
     */
    public function testVisitVerifyWithNoValidRow()
    {
        $this->createFactories();

        $this->browse(function (Browser $browser, Browser $browser2, Browser $browser3) {
            $browser->visit('/email/verify?token=' . hash('sha256', 'abcfoo') . '&email=baz2%40bar.com')
                    ->assertPathIs('/email/verify/result')
                    ->assertSee('Email Verification Error')
                    ->assertSee('Your token has expired, please request a new one');

            $browser2->visit('/email/verify?token=' . hash('sha256', 'abc123') . '&email=badmail@nomail.com')
                     ->assertPathIs('/email/verify/result')
                     ->assertSee('Email Verification Error')
                     ->assertSee('The provided information does not match our records');

            $browser3->visit('/email/verify?token=' . hash('sha256', 'notoken') . '&email=nomail@nomail.com')
                     ->assertPathIs('/email/verify/result')
                     ->assertSee('Email Verification Error')
                     ->assertSee('The provided information does not match our records');
        });
    }

    /**
     * Test visit verification refresh form with bad input
     */
    public function testVisitVerifyRefreshWithBadInput()
    {
        $this->browse(function (Browser $browser, Browser $browser2) {
            $browser->visit('/email/verify/refresh')
                    ->click('#submit-verify-email-refresh-form')
                    ->pause(2000)
                    ->assertSee('Please enter a valid email');

            $browser2->visit('/email/verify/refresh')
                     ->type('email', 'badmail')
                     ->click('#submit-verify-email-refresh-form')
                     ->pause(2000)
                     ->assertSee('Please enter a valid email');
        });
    }

    /**
     * Test visit verify refresh
     */
    public function testVisitVerifyRefresh()
    {
        $this->browse(function (Browser $browser, Browser $browser2) {
            $browser->visit('/email/verify/refresh')
                    ->type('email', 'baz2@foo.com')
                    ->click('#submit-verify-email-refresh-form')
                    ->assertPathIs('/email/verify/refresh')
                    ->assertSee('If the email is registered in our system, you will receive an email with a new verification code shortly');
        });
    }
}
