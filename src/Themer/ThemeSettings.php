<?php
namespace Laranix\Themer;

use Laranix\Support\Settings as BaseSettings;

class ThemeSettings extends BaseSettings
{
    /**
     * Names of required properties
     *
     * @var array|string
     */
    protected $required = [
        'key'       => 'string',
        'name'      => 'string',
        'path'      => 'string',
        'webPath'   => 'string',
        'enabled'   => 'bool',
        'default'   => 'bool',
        'override'  => 'bool',
    ];

    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $webPath;

    /**
     * @var bool
     */
    public $enabled = true;

    /**
     * @var bool
     */
    public $default = false;

    /**
     * @var bool
     */
    public $override = false;
}
