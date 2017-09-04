<?php
namespace Laranix\Auth\User\Events;

use Illuminate\Queue\SerializesModels;
use Laranix\Auth\User\User;

class Created
{
    use SerializesModels;

    /**
     * @var \Laranix\Auth\User\User
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param \Laranix\Auth\User\User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
