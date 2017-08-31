<?php
namespace Laranix\Auth\Group;

use Laranix\Support\Settings as BaseSettings;

class Settings extends BaseSettings
{
    /**
     * Names of required properties
     *
     * @var array|string
     */
    protected $required = [
        'name'  => 'string',
        'flags' => 'array',
    ];

    /**
     * Group name
     *
     * @var string
     */
    public $name;

    /**
     * Group colour
     *
     * @var string
     */
    public $color = null;

    /**
     * Group icon
     *
     * @var string
     */
    public $icon = null;

    /**
     * Group level
     *
     * @var int
     */
    public $level = 0;

    /**
     * Group flags
     *
     * @var array
     */
    public $flags = [];

    /**
     * True if group is hidden
     *
     * @var bool
     */
    public $hidden = false;
}
