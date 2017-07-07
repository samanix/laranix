<?php
namespace Laranix\Auth\Group\Events;

use Illuminate\Queue\SerializesModels;
use Laranix\Auth\Group\Group;

class Created
{
    use SerializesModels;

    /**
     * @var \Laranix\Auth\Group\Group
     */
    public $group;

    /**
     * Create a new event instance.
     *
     * @param \Laranix\Auth\Group\Group $group
     */
    public function __construct(Group $group)
    {
        $this->group = $group;
    }
}
