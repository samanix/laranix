<?php
namespace Laranix\Tests\Laranix\Auth\User\Cage;

use Laranix\Auth\User\Cage\Settings;
use Laranix\Auth\User\Cage\CreatesUserCage;
use Laranix\Auth\User\User;
use Laranix\Tests\LaranixTestCase;
use Illuminate\Support\Facades\Event;
use Laranix\Auth\User\Cage\Events\Created;

class CreatesUserCageTest extends LaranixTestCase
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
     * Tear down
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Test creating group
     */
    public function testCreateUserCage()
    {
        $create = $this->getMockForTrait(CreatesUserCage::class);

        $create->createUserCage($this->createSettings());
        Event::assertDispatched(Created::class, function ($event) {
            return $event->cage->area === 'login' && $event->cage->level === 100;
        });

        $create->createUserCage($this->createSettingsFromArray());
        Event::assertDispatched(Created::class, function ($event) {
            return $event->cage->area === 'logout' && $event->cage->level === 200;
        });


        $this->assertDatabaseHas(config('laranixauth.cage.table'), [
            'level'             => 100,
            'area'              => 'login',
            'length'            => 30,
            'reason'            => '**foo**',
            'reason_rendered'   => '<p><strong>foo</strong></p>',
            'issuer_id'         => 1,
            'user_id'           => 3,
        ]);

        $this->assertDatabaseHas(config('laranixauth.cage.table'), [
            'level'             => 200,
            'area'              => 'logout',
            'length'            => 60,
            'reason'            => '_bar_',
            'reason_rendered'   => '<p><em>bar</em></p>',
            'issuer_id'         => 2,
            'user_id'           => 4,
        ]);
    }

    /**
     * @return \Laranix\Auth\User\Cage\Settings
     */
    protected function createSettings()
    {
        return new Settings([
            'level'     => 100,
            'area'      => 'login',
            'length'    => 30,
            'reason'    => '**foo**',
            'issuer'    => 1,
            'user'      => 3,
        ]);
    }

    /**
     * @return array
     */
    protected function createSettingsFromArray()
    {
        return [
            'level'     => 200,
            'area'      => 'logout',
            'length'    => 60,
            'reason'    => '_bar_',
            'issuer'    => 2,
            'user'      => 4,
        ];
    }
}
