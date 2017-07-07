<?php
namespace Laranix\Themer\Style;

use Laranix\Themer\FileSettings as BaseSettings;

class Settings extends BaseSettings
{
    /**
     * Media type to load for
     *
     * @var string|array
     *
     * TODO Array option, brackets, and/not/or operators
     */
    public $media = 'all';
}
