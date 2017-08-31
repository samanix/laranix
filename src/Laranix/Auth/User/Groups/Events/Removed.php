<?php
namespace Laranix\Auth\User\Groups\Events;

use Illuminate\Queue\SerializesModels;
use Laranix\Auth\User\Groups\UserGroup;

class Removed
{
    use SerializesModels;

    /**
     * @var \Laranix\Auth\User\Groups\UserGroup
     */
    public $usergroup;

    /**
     * Create a new event instance.
     *
     * @param \Laranix\Auth\User\Groups\UserGroup $usergroup
     */
    public function __construct(UserGroup $usergroup)
    {
        $this->usergroup = $usergroup;
    }
}
