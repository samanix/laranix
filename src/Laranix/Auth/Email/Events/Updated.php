<?php
namespace Laranix\Auth\Email\Events;

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
     * @var string
     */
    public $oldemail;

    /**
     * Create a new event instance.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|User $user
     * @param string                                          $oldemail
     */
    public function __construct(Authenticatable $user, string $oldemail)
    {
        $this->user = $user;
        $this->oldemail = $oldemail;
    }
}
