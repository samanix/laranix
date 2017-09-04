<?php
namespace Laranix\Themer\Styles;

use Laranix\Themer\ResourceSettings as BaseSettings;

class Settings extends BaseSettings
{
    /**
     * Media type to load for
     *
     * @var string|array
     *
     * TODO Array option, brackets, and/not/or operators - perhaps an array of a subsettings file?
     */
    public $media = 'all';
}
