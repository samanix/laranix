<?php
namespace Laranix\Auth\User\Token\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Queue\SerializesModels;
use Laranix\Auth\User\User;

abstract class Completed
{
    use SerializesModels;

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|User
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|User $user
     */
    public function __construct(Authenticatable $user)
    {
        $this->user = $user;
    }
}
