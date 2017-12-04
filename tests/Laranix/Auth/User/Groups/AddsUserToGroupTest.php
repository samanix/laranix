<?php
namespace Laranix\Tests\Laranix\Auth\User\Groups;

use Laranix\Auth\Group\Group;
use Laranix\Auth\User\Groups\Events\Added;
use Laranix\Auth\User\Groups\Settings;
use Laranix\Auth\User\Groups\AddsUserToGroup;
use Laranix\Auth\User\User;
use Laranix\Tests\LaranixTestCase;
use Illuminate\Support\Facades\Event;

class AddsUserToGroupTest extends LaranixTestCase
{
    /**
     * @var bool
     */
    protected $runMigrations = true;

    /**
     * @var array
     */
    protected $factories = [
        User::class     => __DIR__ . '/../../../../Factory/User',
        Group::class    => __DIR__ . '/../../../../Factory/Group',
    ];

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->createFactories();
    }

    /**
     * Test creating group
     */
    public function testCreateGroup()
    {
        $create = $this->getMockForTrait(AddsUserToGroup::class);

        $create->addUserToGroup($this->createSettings());
        Event::assertDispatched(Added::class, function ($event) {
            return $event->usergroup->user_id === 1 && $event->usergroup->group_id === 2;
        });

        $create->addUserToGroup($this->createSettingsFromArray());
        Event::assertDispatched(Added::class, function ($event) {
            return $event->usergroup->user_id === 2 && $event->usergroup->group_id === 1;
        });

        $this->assertDatabaseHas(config('laranixauth.usergroups.table'), [
            'user_id'   => 1,
            'group_id'  => 2,
            'primary'   => 1,
            'hidden'    => 0,
        ]);

        $this->assertDatabaseHas(config('laranixauth.usergroups.table'), [
            'user_id'   => 2,
            'group_id'  => 1,
            'primary'   => 0,
            'hidden'    => 1,
        ]);
    }

    /**
     * @return \Laranix\Auth\User\Groups\Settings
     */
    protected function createSettings()
    {
        return new Settings([
            'user'       => 1,
            'group'      => 2,
            'primary'    => true,
            'hidden'     => false,
        ]);
    }

    /**
     * @return array
     */
    protected function createSettingsFromArray()
    {
        return [
            'user'       => 2,
            'group'      => 1,
            'primary'    => false,
            'hidden'     => true,
        ];
    }
}
