<?php
namespace Laranix\Auth\Password\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Queue\SerializesModels;
use Laranix\Auth\User\User;

class Updated
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
