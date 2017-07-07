<?php
namespace Tests\Laranix\Auth\User;

use Laranix\Auth\User\CreatesUsers;
use Laranix\Auth\User\Events\Created;
use Laranix\Auth\User\User;
use Tests\LaranixTestCase;
use Laranix\Auth\User\Settings;
use Illuminate\Support\Facades\Event;

class CreatesUsersTest extends LaranixTestCase
{
    /**
     * @var bool
     */
    protected $runMigrations = true;

    /**
     * Test creating group
     */
    public function testCreateUser()
    {
        $create = $this->getMockForTrait(CreatesUsers::class);

        $create->createUser($this->createSettings());
        Event::assertDispatched(Created::class, function ($event) {
            return $event->user->email === 'foo@bar.com' && $event->user->username === 'foo';
        });

        $create->createUser($this->createSettingsFromArray());
        Event::assertDispatched(Created::class, function ($event) {
            return $event->user->email === 'bar@baz.com' && $event->user->username === 'bar';
        });

        $this->assertDatabaseHas(config('laranixauth.users.table'), [
            'email'             => 'foo@bar.com',
            'username'          => 'foo',
            'avatar'            => 'pic.png',
            'first_name'        => 'foo',
            'last_name'         => 'bar',
            'company'           => 'Foo Co',
            'timezone'          => 'UTC',
            'account_status'    => User::USER_ACTIVE,
        ]);

        $this->assertDatabaseHas(config('laranixauth.users.table'), [
            'email'             => 'bar@baz.com',
            'username'          => 'bar',
            'avatar'            => 'pic.jpg',
            'first_name'        => 'bar',
            'last_name'         => 'baz',
            'company'           => 'Bar Co',
            'timezone'          => 'PDT',
            'account_status'    => User::USER_UNVERIFIED,
        ]);
    }

    /**
     * @return \Laranix\Auth\User\Settings
     */
    protected function createSettings()
    {
        return new Settings([
            'email'         => 'foo@bar.com',
            'username'      => 'foo',
            'avatar'        => 'pic.png',
            'first_name'    => 'foo',
            'last_name'     => 'bar',
            'password'      => 'secret',
            'company'       => 'Foo Co',
            'timezone'      => 'UTC',
            'status'        => User::USER_ACTIVE,
        ]);
    }

    /**
     * @return array
     */
    protected function createSettingsFromArray()
    {
        return [
            'email'         => 'bar@baz.com',
            'username'      => 'bar',
            'avatar'        => 'pic.jpg',
            'first_name'    => 'bar',
            'last_name'     => 'baz',
            'password'      => 'secret2',
            'company'       => 'Bar Co',
            'timezone'      => 'PDT',
            'status'        => User::USER_UNVERIFIED,
        ];
    }
}
