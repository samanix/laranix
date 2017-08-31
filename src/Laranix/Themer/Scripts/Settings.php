<?php
namespace Laranix\Themer\Scripts;

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
}
