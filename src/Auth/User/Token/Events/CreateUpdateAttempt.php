<?php
namespace Laranix\Auth\User\Token\Events;

abstract class CreateUpdateAttempt
{
    /**
     * @var string|null
     */
    public $email;

    /**
     * Create a new event instance.
     *
     * @param null|string $email
     */
    public function __construct(?string $email)
    {
        $this->email = $email;
    }
}
