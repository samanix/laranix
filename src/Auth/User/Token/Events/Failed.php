<?php
namespace Laranix\Auth\User\Token\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Queue\SerializesModels;
use Laranix\Auth\User\Token\Token;
use Laranix\Auth\User\User;

abstract class Failed
{
    use SerializesModels;

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|User
     */
    public $user;

    /**
     * @var \Laranix\Auth\User\Token\Token
     */
    public $token;

    /**
     * @var null|string
     */
    public $email;

    /**
     * Create a new event instance.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|User $user
     * @param \Laranix\Auth\User\Token\Token                  $token
     * @param null|string                                     $email
     */
    public function __construct(?Authenticatable $user, ?Token $token, ?string $email)
    {
        $this->user  = $user;
        $this->token = $token;
        $this->email = $email;
    }
}
