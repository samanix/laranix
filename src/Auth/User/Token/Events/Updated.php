<?php
namespace Laranix\Auth\User\Token\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Queue\SerializesModels;
use Laranix\Auth\User\Token\Token;
use Laranix\Auth\User\User;

abstract class Updated
{
    use SerializesModels;

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|User
     */
    public $user;

    /**
     * @var \Laranix\Auth\User\Token\Token|null
     */
    public $token;

    /**
     * Create a new event instance.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|User|null $user
     * @param \Laranix\Auth\User\Token\Token|null                  $token
     */
    public function __construct(Authenticatable $user, Token $token)
    {
        $this->user = $user;
        $this->token = $token;
    }
}
