<?php
namespace Laranix\Themer\Images;

use Laranix\Themer\ResourceSettings as BaseSettings;

class LocalSettings extends BaseSettings
{
    /**
     * @var array
     */
    protected $required = [
        'image' => 'string',
        'alt'   => 'string',
    ];

    /**
     * Images file
     *
     * @var string
     */
    public $image;

    /**
     * Alt tag for image
     *
     * @var string
     */
    public $alt;

    /**
     * Images title
     *
     * @var string
     */
    public $title;

    /**
     * Images width
     *
     * @var int
     */
    public $width;

    /**
     * Images height
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

    /**
     * @var string
     */
    public $url;
}
