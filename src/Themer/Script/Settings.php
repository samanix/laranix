<?php
namespace Laranix\Themer\Script;

use Laranix\Themer\ResourceSettings as BaseSettings;

class Settings extends BaseSettings
{
    /**
     * Load async
     *
     * @var bool
     */
    public $async = false;

    /**
     * Defer loading
     *
     * @var bool
     */
    public $defer = true;

    /**
     * Load scripts in head
     *
     * @var bool
     */
    public $head = true;

    /**
     * Set the CORS settings attribute
     *
     * @var string|null
     */
    public $crossorigin;
}
