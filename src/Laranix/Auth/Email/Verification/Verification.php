<?php
namespace Laranix\Auth\Email\Verification;

use Laranix\Auth\User\Token\Token;

class Verification extends Token
{
    /**
     * EmailVerification constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = $this->config->get('laranixauth.verification.table');
    }
}
