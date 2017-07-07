<?php
namespace Laranix\Tracker\Events;

class BatchCreated
{
    /**
     * @var array
     */
    public $count;

    /**
     * Create a new event instance.
     *
     * @param int $count
     */
    public function __construct(int $count)
    {
        $this->count = $count;
    }
}
