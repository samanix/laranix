<?php
namespace Laranix\Support\IO\Url;

use Laranix\Support\Settings;

class HrefSettings extends Settings
{
    /**
     * Required properties
     *
     * @var array
     */
    protected $required = [
        'content'   => 'string',
        'url'       => 'string|array|UrlSettings',
        'attributes'=> 'optional|array',
    ];

    /**
     * HTML output
     *
     * @var string
     */
    public $content;


    /**
     * Url output
     *
     * @var mixed
     */
    public $url;

    /**
     * HTML attributes
     *
     * @var array|null
     */
    public $attributes;
}
