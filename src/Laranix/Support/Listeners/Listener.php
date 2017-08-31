<?php
namespace Laranix\Support\Listeners;

use Laranix\Tracker\TrackWriter;
use Illuminate\Contracts\Auth\Authenticatable;

abstract class Listener
{
    /**
     * @var \Laranix\Tracker\TrackWriter
     */
    protected $writer;

    /**
     * @var string
     */
    protected $type = 'track';

    /**
     * Listener constructor.
     *
     * @param \Laranix\Tracker\TrackWriter $writer
     */
    public function __construct(TrackWriter $writer)
    {
        $this->writer = $writer;
    }

    /**
     * Get user ID
     *
     * @param int|\Illuminate\Contracts\Auth\Authenticatable|null $user
     * @return int|null
     */
    protected function getUserId($user) : ?int
    {
        if ($user === null) {
            return null;
        }

        if (is_int($user)) {
            return $user;
        }

        if ($user instanceof Authenticatable) {
            return $user->getAuthIdentifier();
        }

        return null;
    }

    /**
     * Track the event
     *
     * @param int                                                   $typeId
     * @param int                                                   $itemId
     * @param int                                                   $flag
     * @param string|array                                          $data
     * @param int|\Illuminate\Contracts\Auth\Authenticatable|null   $user
     */
    protected function track(?int $typeId = null, ?int $itemId = null, int $flag = 0, $data = null, $user = null)
    {
        // Login event
        // Add request url

        $this->writer->register([
            'user'          => $this->getUserId($user),
            'type'          => $this->type,
            'typeId'        => $typeId,
            'itemId'        => $itemId,
            'flagLevel'     => $flag,
            'data'          => $data,
        ]);
    }
}
