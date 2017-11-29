<?php
namespace Laranix\Themer\Images;

class RemoteSettings extends LocalSettings
{
    /**
     * @var array
     */
    protected $required = [
        'url'   => 'string',
        'alt'   => 'string',
    ];
}
