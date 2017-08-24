<?php
namespace Laranix\Auth\User\Events;

use Illuminate\Queue\SerializesModels;
use Laranix\Auth\User\User;

class Updated
{
    use SerializesModels;

    /**
     * @var \Laranix\Auth\User\User
     */
    public $user;

    /**
     * @var \Laranix\Auth\User\User
     */
    public $olduser;

    /**
     * Create a new event instance.
     *
     * @param \Laranix\Auth\User\User $user
     * @param \Laranix\Auth\User\User $olduser
     */
    public function __construct(User $user, User $olduser)
    {
        $this->user = $user;
        $this->olduser = $olduser;
    }
}
