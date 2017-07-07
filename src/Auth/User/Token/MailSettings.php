<?php
namespace Laranix\Auth\User\Token;

use Laranix\Support\Mail\MailSettings as MailSettingsBase;

class MailSettings extends MailSettingsBase
{
    /**
     * Names of required properties
     *
     * @var array|string
     */
    protected $required = [
        'to'            => 'email|array',
        'view'          => 'string',
        'subject'       => 'string',
        'username'      => 'string',
        'token'         => 'string',
        'url'           => 'url',
        'baseurl'       => 'url',
        'expiry'        => 'string',
        'humanExpiry'   => 'string',
    ];

    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $baseurl;

    /**
     * @var string
     */
    public $expiry;

    /**
     * @var string
     */
    public $humanExpiry;
}
