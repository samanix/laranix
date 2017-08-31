<?php
namespace Laranix\Auth\User;

use Laranix\Auth\Password\Hasher;
use Laranix\Auth\Password\HashesPasswords;
use Laranix\Support\Settings as BaseSettings;

class Settings extends BaseSettings implements Hasher
{
    use HashesPasswords;

    /**
     * Names of required properties
     *
     * @var array|string
     */
    protected $required = [
        'email'         => 'email',
        'username'      => 'string',
        'first_name'    => 'string',
        'last_name'     => 'string',
        'password'      => 'string',
        'status'        => 'int',
    ];

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $avatar;

    /**
     * @var string
     */
    public $first_name;

    /**
     * @var string
     */
    public $last_name;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $company;

    /**
     * @var string
     */
    public $timezone = 'UTC';

    /**
     * @var int
     */
    public $status = User::USER_UNVERIFIED;
}
