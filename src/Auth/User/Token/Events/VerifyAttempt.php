<?php
namespace Laranix\Auth\User\Token\Events;

abstract class VerifyAttempt
{
    /**
     * @var string|null
     */
    public $email;

    /**
     * Create a new event instance.
     *
     * @param string|null $email
     */
    public function __construct(?string $email)
    {
        $this->email = $email;
    }
}
