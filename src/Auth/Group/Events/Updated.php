<?php
namespace Laranix\Auth\Group\Events;

use Illuminate\Queue\SerializesModels;
use Laranix\Auth\Group\Group;

class Updated
{
    use SerializesModels;

    /**
     * @var \Laranix\Auth\Group\Group
     */
    public $group;

    /**
     * @var \Laranix\Auth\Group\Group
     */
    public $oldgroup;

    /**
     * Create a new event instance.
     *
     * @param \Laranix\Auth\Group\Group $group
     * @param \Laranix\Auth\Group\Group $oldgroup
     * @internal param \Laranix\Auth\Group\Group $cage
     */
    public function __construct(Group $group, Group $oldgroup)
    {
        $this->cage     = $group;
        $this->oldcage  = $oldgroup;
    }
}
