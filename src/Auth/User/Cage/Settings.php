<?php
namespace Laranix\Auth\User\Cage;

use Laranix\Support\Settings as BaseSettings;

class Settings extends BaseSettings
{
    /**
     * Names of required properties
     *
     * @var array|string
     */
    protected $required = [
        'level'     => 'int',
        'area'      => 'string',
        'time'      => 'int',
        'reason'    => 'string',
        'issuer'    => 'int',
        'user'      => 'int',
    ];

    /**
     * Cage level.
     *
     * @var int
     */
    public $level = 100;

    /**
     * Area to build cage.
     *
     * @var string
     */
    public $area = 'all';

    /**
     * Time in minutes for cage to be in place.
     *
     * Set to 0 for permanent
     *
     * @var int
     */
    public $time = 30;

    /**
     * Reason for cage.
     *
     * @var string
     */
    public $reason = 'Admin issued';

    /**
     * User ID issuing cage.
     *
     * @var int
     */
    public $issuer;

    /**
     * User ID receiving cage.
     *
     * @var int
     */
    public $user;

    /**
     * Cage status.
     *
     * @var int
     */
    public $status = Cage::CAGE_ACTIVE;
}
