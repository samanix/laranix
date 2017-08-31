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
        'ipv4'      => 'optional|int',
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
     * User IP where cage triggered
     *
     * @var int
     */
    public $ipv4;

    /**
     * Settings constructor.
     *
     * @param array                    $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->parseDefaults();
    }

    /**
     * Parse default values
     */
    protected function parseDefaults()
    {
        if (isset($this->ipv4) && !is_int($this->ipv4)) {
            $this->ipv4 = ip2long($this->ipv4);
        }
    }
}
