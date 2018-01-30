<?php
namespace Laranix\Themer;

use Laranix\Support\Settings as SettingsBase;

abstract class ResourceSettings extends SettingsBase
{
    /**
     * @var array
     */
    protected $required = [
        'key'       => 'string',
        'filename'  => 'string',
        'order'     => 'int',
    ];

    /**
     * Unique key
     *
     * @var string
     */
    public $key;

    /**
     * File name
     *
     * @var string
     */
    public $filename;

    /**
     * URL of file if remote
     *
     * @var string
     */
    public $url;

    /**
     * Order to load file in
     *
     * @var int
     */
    public $order = -1;

    /**
     * Set to true to load from default theme if not found in given theme
     *
     * @var bool
     */
    public $defaultFallback = true;

    /**
     * If true, will search for .min files
     *
     * @var bool
     */
    public $automin = false;

    /**
     * Theme name for file
     *
     * @var string
     */
    public $themeName;

    /**
     * Set the CORS settings attribute
     *
     * @var string|null
     */
    public $crossorigin;

    /**
     * SRI Hash
     *
     * @var string|null
     */
    public $integrity;

    /**
     * If true, will merge with other files of same type
     *
     * @var bool
     */
    public $compile = true;

    // Values below are auto set

    /**
     * Theme in use for file (auto set)
     *
     * @var \Laranix\Themer\Theme
     */
    public $theme;

    /**
     * File path
     *
     * @var string
     */
    public $resourcePath;

    /**
     * Stores existence state of resource
     *
     * @var bool
     */
    public $exists = false;

    /**
     * Stores the modified file time (auto set)
     *
     * @var int
     */
    public $mtime = 0;

    /**
     * Repository key for file (auto set)
     *
     * @var string
     */
    public $repositoryKey;
}
