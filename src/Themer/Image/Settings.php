<?php
namespace Laranix\Themer\Image;

use Laranix\Themer\ResourceSettings as BaseSettings;

class Settings extends BaseSettings
{
    /**
     * @var array
     */
    protected $required = [
        'filename'  => 'string',
        'alt'       => 'string',
    ];

    /**
     * Alt tag for image
     *
     * @var string
     */
    public $alt;

    /**
     * Image title
     *
     * @var string
     */
    public $title;

    /**
     * Image width
     *
     * @var int
     */
    public $width;

    /**
     * Image height
     *
     * @var int
     */
    public $height;

    /**
     * CSS classes to apply
     *
     * @var string|array
     */
    public $class;

    /**
     * Element Id
     *
     * @var string
     */
    public $id;

    /**
     * Extra params
     *
     * @var array
     */
    public $extra;

    /**
     * @var bool
     */
    public $default = false;

    /**
     * @var string
     */
    public $htmlstring;
}
