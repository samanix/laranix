<?php
namespace Laranix\Auth\User\Groups;

use Laranix\Support\Settings as BaseSettings;

class Settings extends BaseSettings
{
    /**
     * Names of required properties
     *
     * @var array|string
     */
    protected $required = [
        'user'  => 'int',
        'group' => 'int'
    ];

    /**
     * User Id
     *
     * @var int
     */
    public $user;

    /**
     * Group Id
     *
     * @var int
     */
    public $group;

    /**
     * Is users primary usergroup
     *
     * @var bool|int
     */
    public $primary = false;

    /**
     * If usergroup is hidden
     *
     * @var bool|int
     */
    public $hidden = false;
}
