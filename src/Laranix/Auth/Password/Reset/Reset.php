<?php
namespace Laranix\Auth\Password\Reset;

use Laranix\Auth\User\Token\Token;

class Reset extends Token
{
    /**
     * Password Reset constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = $this->config->get('laranixauth.password.table');
    }
}
