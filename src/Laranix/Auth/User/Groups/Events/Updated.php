<?php
namespace Laranix\Auth\User\Groups\Events;

use Illuminate\Queue\SerializesModels;
use Laranix\Auth\User\Groups\UserGroup;

class Updated
{
    use SerializesModels;

    /**
     * @var \Laranix\Auth\User\Groups\UserGroup
     */
    public $usergroup;

    /**
     * @var \Laranix\Auth\User\Groups\UserGroup
     */
    public $oldusergroup;

    /**
     * Create a new event instance.
     *
     * @param \Laranix\Auth\User\Groups\UserGroup $usergroup
     * @param \Laranix\Auth\User\Groups\UserGroup $oldusergroup
     */
    public function __construct(UserGroup $usergroup, UserGroup $oldusergroup)
    {
        $this->usergroup = $usergroup;
        $this->oldUsergroup = $oldusergroup;
    }
}
