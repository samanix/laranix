<?php
namespace Laranix\Tracker\Events;

use Illuminate\Queue\SerializesModels;
use Laranix\Tracker\Tracker;

class Updated
{
    use SerializesModels;

    /**
     * @var \Laranix\Tracker\Tracker
     */
    public $track;

    /**
     * @var \Laranix\Tracker\Tracker
     */
    public $oldtrack;

    /**
     * Create a new event instance.
     *
     * @param \Laranix\Tracker\Tracker $tracker
     * @param \Laranix\Tracker\Tracker $oldtracker
     */
    public function __construct(Tracker $tracker, Tracker $oldtracker)
    {
        $this->track = $tracker;
        $this->oldtrack = $oldtracker;
    }
}
