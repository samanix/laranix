<?php
namespace Tests\Laranix\Tracker;

use Illuminate\Config\Repository;
use Laranix\Auth\User\User;
use Laranix\Tracker\Settings;
use Laranix\Tracker\Tracker;
use Laranix\Tracker\TrackerRepository;
use Tests\LaranixTestCase;

class TrackerRepositoryTest extends LaranixTestCase
{
    /**
     * @var bool
     */
    protected $runMigrations = true;

    /**
     * @var array
     */
    protected $factories = [
        User::class     => __DIR__ . '/../../Factory/User',
        Tracker::class  => __DIR__ . '/../../Factory/Tracker',
    ];

    /**
     * @var TrackerRepository
     */
    protected $repository;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->createFactories();

        $this->repository = $this->createTrackerRepository();
    }

    /**
     * Test getting track by ID
     */
    public function testGetTrackById()
    {
        $this->assertSame(1, $this->repository->getById(1)->tracker_id);
        $this->assertSame(5, $this->repository->getById(5)->getKey());
    }

    /**
     * Test getting by User
     */
    public function testGetTrackByUser()
    {
        $this->assertCount(3, $this->repository->getByUser(1)->items());
        $this->assertCount(2, $this->repository->getByUser(1, Tracker::TRACKER_TRAIL)->items());
        $this->assertSame(2, $this->repository->getByUser('1', Tracker::TRACKER_ANY, 2)->perPage());
    }

    /**
     * Test getting by IP
     */
    public function testGetTrackByIpv4()
    {
        $this->assertSame(1, $this->repository->getByIpv4('1.1.1.1')->items()[0]->tracker_id);
        $this->assertSame(5, $this->repository->getByIpv4(16843013)->items()[0]->getKey());
    }

    /**
     * Get by IP with no row matching
     */
    public function testGetTrackByIpv4WhenNoMatchingRow()
    {
        $this->assertCount(0, $this->repository->getByIpv4('1.1.1.3', Tracker::TRACKER_TRAIL));
    }

    /**
     * Test getting by IP range
     */
    public function testGetTrackByIpv4Range()
    {
        $this->assertCount(5, $this->repository->getByIpv4Range('0.0.0.0'));

        $this->assertCount(2, $this->repository->getByIpv4Range('1.1.1.1', '1.1.1.2'));

        $this->assertCount(3, $this->repository->getByIpv4Range('1.1.1.1', '1.1.1.5', Tracker::TRACKER_TRAIL));

        $this->assertSame(2, $this->repository->getByIpv4Range('1.1.1.1', '1.1.1.5', Tracker::TRACKER_TRAIL, 2)->perPage());
    }

    /**
     * Test getting track
     */
    public function testGetTrack()
    {
        $this->assertCount(1, $this->repository->getTrack('login', 1));
        $this->assertCount(1, $this->repository->getTrack('login', null, 1));
        $this->assertCount(1, $this->repository->getTrack('login', 1, 2, 10));
        $this->assertSame(25, $this->repository->getTrack('login', null, 1, null, 25)->perPage());

        $this->assertEmpty($this->repository->getTrack('login:fake'));

        // Using alias
        $this->assertCount(1, $this->repository->getTrack('l', 1));

        $this->assertCount(1, $this->repository->getTrack('l', 1, Tracker::TRACKER_ANY, 300));
    }

    /**
     * Test getting track by type
     */
    public function testGetTrackByType()
    {
        $this->assertCount(3, $this->repository->getByType('login'));
        $this->assertCount(2, $this->repository->getByType('login', 2));
        $this->assertCount(2, $this->repository->getByType('login', 1, 15));
        $this->assertSame(25, $this->repository->getByType('login', 4, 1, 25)->perPage());

        $this->assertEmpty($this->repository->getByType('login:fake'));

        // Using alias
        $this->assertCount(3, $this->repository->getByType('l'));

        $this->assertCount(1, $this->repository->getByType('l', 1, 5, Tracker::TRACKER_ANY));
    }

    /**
     * Test get tracks by flag level
     */
    public function testGetTrackByFlagLevel()
    {
        $this->assertCount(2, $this->repository->getByFlagLevel(10));

        $this->assertCount(1, $this->repository->getByFlagLevel(100, Tracker::TRACKER_LIVE));

        $this->assertSame(2, $this->repository->getByFlagLevel(10, Tracker::TRACKER_ANY, 2)->perPage());

        $this->assertEmpty($this->repository->getByFlagLevel(50));
    }

    /**
     * Test getting by flag range
     */
    public function testGetTrackByFlagRange()
    {
        $this->assertCount(5, $this->repository->getByFlagRange(1));

        $this->assertCount(4, $this->repository->getByFlagRange(0, 25));

        $this->assertSame(3, $this->repository->getByFlagRange(0, Settings::FLAG_MAX, Tracker::TRACKER_ANY, 3)->perPage());

        $this->assertCount(2, $this->repository->getByFlagRange(20, 100, Tracker::TRACKER_LIVE));

        $this->assertCount(1, $this->repository->getByFlagRange(20, 100, Tracker::TRACKER_TRAIL));
    }

    /**
     * Test deleting tracks
     */
    public function testDeleteTracks()
    {
        $this->assertSame(1, $this->repository->deleteTracks('l', 1, 0, Tracker::TRACKER_TRAIL));

        $this->assertSame(0, $this->repository->deleteTracks('login:fake', 100, Tracker::TRACKER_ANY));

        $this->tearDown();
        $this->setUp();

        $table = config('tracker.table');

        $this->assertSame(0, $this->repository->deleteTracks('lf', 4, 0, Tracker::TRACKER_TRAIL));

        $this->assertSame(1, $this->repository->deleteTracks('login', null, 200, Tracker::TRACKER_TRAIL));

        $this->assertDatabaseHas($table, [
           'tracker_id' =>  1,
        ]);

        $this->assertDatabaseMissing($table, [
           'tracker_id' =>  5,
        ]);

        $this->assertSame(2, $this->repository->deleteTracks('login', -1, 0, 1));
    }

    /**
     * Test getting model
     */
    public function testGetModel()
    {
        $this->assertInstanceOf(Tracker::class, $this->repository->getModel());
    }

    /**
     * Create repository
     *
     * @return \Laranix\Tracker\TrackerRepository
     */
    protected function createTrackerRepository()
    {
        return new TrackerRepository(new Repository([
            'laranixauth' => [
                'users' => [
                    'table' => 'users',
                ],
            ],
            'tracker' => [
                'table'   => 'tracker',
                'aliases'   => [
                    'l'     => 'login',
                ],
            ]
        ]));
    }
}
