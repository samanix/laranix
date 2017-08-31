<?php
namespace Laranix\Tracker\Events;

use Illuminate\Queue\SerializesModels;
use Laranix\Tracker\Tracker;

class Created
{
    use SerializesModels;

    /**
     * @var \Laranix\Tracker\Settings
     */
    public $track;

    /**
     * Create a new event instance.
     *
     * @param \Laranix\Tracker\Tracker $track
     */
    public function __construct(Tracker $track)
    {
        $this->track = $track;
    }
}
