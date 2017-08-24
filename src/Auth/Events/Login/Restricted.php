<?php
namespace Laranix\Auth\Events\Login;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Queue\SerializesModels;
use Laranix\Auth\User\User;

class Restricted
{
    use SerializesModels;

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|User
     */
    public $user;

    /**
     * @var string
     */
    public $message;

    /**
     * Create a new event instance.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|User $user
     * @param string                                          $message
     */
    public function __construct(Authenticatable $user, string $message)
    {
        $this->user     = $user;
        $this->message  = $message;
    }
}
